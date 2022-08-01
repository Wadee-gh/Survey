mkdir -p tmp/runtime tmp/assets
chmod 777 tmp/runtime/
chmod 777 tmp/assets/
sudo apt install php
sudo apt install php-mbstring 
sudo apt install php-xml 
sudo apt install php-mysql
sudo apt install php-gd
sudo service apache2 restart
