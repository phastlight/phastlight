echo "Starting phastlight installation..."

# where will the phastlight be installed to?
phastlight_dir=/usr/local/phastlight 
# the php version that is tied to phastlight 
phpversion="5.5.9" 
phastlight_version="v0.2.4"
# the path of the phastlight executable
phastlight_executable_path=/usr/local/bin

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

sudo mkdir -p $phastlight_dir
sudo mkdir -p $phastlight_executable_path 

echo "Installing php..."
CURDIR=$(pwd)
cd $phastlight_dir
# Download php src and enable sockets extension
wget http://us2.php.net/get/php-$phpversion.tar.gz/from/this/mirror -O php-$phpversion.tar.gz 
tar xvf php-$phpversion.tar.gz 
cd php-$phpversion
sudo ./configure --enable-sockets --with-openssl --enable-mbstring --prefix=$phastlight_dir 
sudo make 
sudo make install
cd ..

# get the current $PATH 
OLDPATH=$PATH 
# override path so we can switch to phastlight's php 
export PATH=$phastlight_dir/bin:$PATH

echo "Installing php-uv..."
# Install php-uv
git clone https://github.com/chobie/php-uv.git --recursive
cd php-uv
sudo make -C libuv CFLAGS=-fPIC
sudo phpize
./configure 
sudo make
sudo make install

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
echo "Installing composer...\n"
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

# generate phastlight executable  
echo "Generating phastlight binary"

cat > $phastlight_dir/bin/phastlight <<EOF 
#!/bin/bash
if [ -f \$1 ]
then 
    $phastlight_dir/bin/php -c $phastlight_dir/php.ini \$1
else
    $phastlight_dir/bin/php -c $phastlight_dir/php.ini $phastlight_dir/bin/run.php \$*
fi 
EOF

sudo chmod u+x $phastlight_dir/bin/phastlight 
sudo rm -f $phastlight_executable_path/phastlight
sudo ln -s $phastlight_dir/bin/phastlight $phastlight_executable_path/phastlight


if [ $($phastlight_executable_path/phastlight -m | grep uv | wc -l) -eq 1 ]; then
    echo "Installation completed, you can start phastlight with"
    echo "phastlight [Your sever file]"
else
    echo "Fail installing uv.so extension to $phastlight_dir/bin/php"
fi 

#back to the previous cur path 
cd $CURDIR
