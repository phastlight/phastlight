if [ $# -lt 1 ]
then
  dir=/usr/local/phastlight
else
  dir=$1
fi

if [ $# -lt 2 ]
then
  phpversion="5.4.7"
else
  phpversion=$2
fi

if [ ! -d $dir ]; 
then
  mkdir $dir
fi
cd $dir

# Download php src and enable sockets extension
if [ ! -f php-$phpversion.tar.gz ];
then
  wget http://us2.php.net/get/php-$phpversion.tar.gz/from/us.php.net/mirror -O php-$phpversion.tar.gz
fi
tar xvf php-$phpversion.tar.gz
cd php-$phpversion
./configure --enable-sockets --prefix=$dir
make
make install
cd ..

# Install php-uv
export CFLAGS='-fPIC' 
git clone https://github.com/chobie/php-uv.git --recursive
cd php-uv/libuv
make && cp uv.a libuv.a
cd ..
$dir/bin/phpize
./configure --with-php-config=$dir/bin/php-config
make && make install

# Install httpparser
git clone https://github.com/chobie/php-httpparser.git --recursive
cd php-httpparser
$dir/bin/phpize
./configure --with-php-config=$dir/bin/php-config
make && make install

cd $dir

# write php.ini file

extension_dir=$($dir/bin/php-config --extension-dir)

echo "extension_dir=$extension_dir\n" > php.ini
echo "extension=uv.so\nextension=httpparser.so" >> php.ini
