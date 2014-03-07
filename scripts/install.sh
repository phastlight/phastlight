echo "Starting phastlight installation..."

# where will the phastlight be installed to?
phastlight_dir=/usr/local/phastlight 
# the php version that is tied to phastlight 
phpversion="5.5.10" 

# by default find out the lastest phastlight release
phastlight_version=$(curl -s https://github.com/phastlight/phastlight/releases | grep tag | grep "tag-name" | sed -e "s/>/ /g" | sed -e "s/</ /g" | awk '{print $3}' | head -n 1)
# the path of the phastlight executable
phastlight_executable_path=/usr/local/bin

CURUSER=$(whoami)

export PHP_AUTOCONF=$(which autoconf)

for i in "$@"
do 
    case $i in 
        --phastlight-dir=*)
            comps=(${i//=/ })
            phastlight_dir=${comps[1]}
            ;;
        --phpversion=*)
            comps=(${i//=/ })
            phpversion=${comps[1]}
            ;;
        --phastlight_version=*)
            comps=(${i//=/ })
            phastlight_version=${comps[1]}
            ;;
        --phastlight_executable_path=*)
            comps=(${i//=/ })
            phastlight_executable_path=${comps[1]}
            ;;
    esac
done 

phastlight_dir=$(eval echo $phastlight_dir)
phastlight_executable_path=$(eval echo $phastlight_executable_path)

mkdir -p $phastlight_dir
mkdir -p $phastlight_executable_path 

echo "Installing php..."
CURDIR=$(pwd)
cd $phastlight_dir 

# Download php src and enable sockets extension
wget http://us2.php.net/get/php-$phpversion.tar.gz/from/this/mirror -O php-$phpversion.tar.gz 
tar xvf php-$phpversion.tar.gz 
cd php-$phpversion
./configure --enable-sockets --with-openssl --enable-mbstring --enable-pcntl --prefix=$phastlight_dir 
make clean
make 
make install
cd ..

# get the current $PATH 
OLDPATH=$PATH 
# override path so we can switch to phastlight's php 
export PATH=$phastlight_dir/bin:$PATH

echo "Installing php-uv..."
# Install php-uv
git clone https://github.com/chobie/php-uv.git --recursive
cd php-uv
make -C libuv CFLAGS=-fPIC
phpize
./configure 
make
make install

cd $phastlight_dir

# resume old path 
export PATH=$OLDPATH

# write php.ini file

extension_dir=$($phastlight_dir/bin/php-config --extension-dir)

echo "extension_dir=$extension_dir" > php.ini 
nl=$'\n'
echo $nl >> php.ini
echo "extension=uv.so" >> php.ini

# installing composer 
echo "Installing composer..."
curl -sS https://getcomposer.org/installer | $phastlight_dir/bin/php -- --install-dir=$phastlight_dir/bin

# tmperarily set composer home 
OLD_COMPOSER_HOME=$COMPOSER_HOME
export COMPOSER_HOME=$phastlight_dir
$phastlight_dir/bin/composer.phar global require "phastlight/phastlight=$phastlight_version"
# set back the composer home 
COMPOSER_HOME=$OLD_COMPOSER_HOME

# generate a run file 
echo "Generating run file"
cat > $phastlight_dir/bin/run.php <<EOF 
<?php 
require_once "$phastlight_dir/vendor/autoload.php";
\$target_file = \$argv[1];
require_once \$target_file;
EOF

# generate phastlightpm 
echo "Generating phastlight package manager"
cat > $phastlight_dir/bin/phastlightpm << EOF 
#!/bin/bash 
OLD_COMPOSER_HOME=\$COMPOSER_HOME 
# set new composer home 
export COMPOSER_HOME=$phastlight_dir 
if [ "\$1" = "install" ]
then
    $phastlight_dir/bin/composer.phar global require "\$2=\$3"
fi
# set back the composer home 
COMPOSER_HOME=\$OLD_COMPOSER_HOME
EOF

# generate phastlight executable  
echo "Generating phastlight binary"

cat > $phastlight_dir/bin/phastlight <<EOF 
#!/bin/bash 

if [ "\$#" -eq 0 ]
then 
    $phastlight_dir/bin/php -c /usr/local/phastlight/php.ini \$*
fi

if [ -f \$1 ]
then 
    $phastlight_dir/bin/php -c $phastlight_dir/php.ini $phastlight_dir/bin/run.php \$1
else
    $phastlight_dir/bin/php -c $phastlight_dir/php.ini \$*
fi 
EOF

chmod u+x $phastlight_dir/bin/phastlight 
rm -f $phastlight_executable_path/phastlight
ln -s $phastlight_dir/bin/phastlight $phastlight_executable_path/phastlight

chmod u+x $phastlight_dir/bin/phastlightpm
rm -f $phastlight_executable_path/phastlightpm
ln -s $phastlight_dir/bin/phastlightpm $phastlight_executable_path/phastlightpm

if [ $($phastlight_executable_path/phastlight -m | grep uv | wc -l) -eq 1 ]; then
    echo "Installation completed, you can start phastlight with"
    echo "$phastlight_executable_path/phastlight [Your sever file]"
else
    echo "Fail installing uv.so extension to $phastlight_dir/bin/php"
fi 

#back to the previous cur path 
cd $CURDIR
