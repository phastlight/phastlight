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
sudo make clean
sudo make 
sudo make install
cd ..

# Install php-uv
export CFLAGS='-fPIC' 
git clone https://github.com/chobie/php-uv.git --recursive
cd php-uv/libuv 
sudo make clean
sudo make 
cd ..
$dir/bin/phpize
sudo ./configure --with-php-config=$dir/bin/php-config
sudo make
sudo make install

cd $dir

# write php.ini file

extension_dir=$($dir/bin/php-config --extension-dir)

echo "extension_dir=$extension_dir\n" > php.ini
echo "extension=uv.so\n" >> php.ini 

if [ $($dir/bin/php -c /usr/local/phastlight/php.ini -m | grep uv | wc -l) -eq 1 ]; then
    echo "Installation completed, you can start php with\n"
    echo "$dir/bin/php -c $dir/php.ini\n"
else
    echo "Fail installing uv.so extension to $dir/bin/php\n"
fi
