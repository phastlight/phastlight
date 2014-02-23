if [ $# -lt 1 ]
then
  dir=/usr/local/phastlight
else
  dir=$1
fi

if [ $# -lt 2 ]
then
  phpversion="5.5.9"
else
  phpversion=$2
fi

if [ ! -d $dir ]; 
then
  mkdir $dir
fi
cd $dir

# Download php src and enable sockets extension
wget http://us2.php.net/get/php-$phpversion.tar.gz/from/this/mirror -O php-$phpversion.tar.gz
tar xvf php-$phpversion.tar.gz
cd php-$phpversion
sudo ./configure --enable-sockets --prefix=$dir 
sudo make 
sudo make install
cd ..

# get the current $PATH 
OLDPATH=$PATH 
# override path so we can switch to phastlight's php 
export PATH=$dir/bin:$PATH

# Install php-uv
git clone https://github.com/chobie/php-uv.git --recursive
cd php-uv
sudo make -C libuv CFLAGS=-fPIC
sudo phpize
./configure 
sudo make
sudo make install

cd $dir

# resume old path 
export PATH=$OLDPATH

# write php.ini file

extension_dir=$($dir/bin/php-config --extension-dir)

echo -e "extension_dir=$extension_dir\n" > php.ini
echo -e "extension=uv.so\n" >> php.ini 

# generate phastlight executable  
echo "Generating phastlight binary at /usr/local/bin"
echo "$dir/bin/php -c $dir/php.ini $*" > /usr/local/bin/phastlight 
sudo chmod u+x /usr/local/bin/phastlight

if [ $(phastlight -m | grep uv | wc -l) -eq 1 ]; then
    echo "Installation completed, you can start phastlight with\n"
    echo "phastlight [Your sever file]"
else
    echo "Fail installing uv.so extension to $dir/bin/php\n"
fi
