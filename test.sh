rm -rf vendor/
composer install
echo "// --------------------------------------------------------------------------------------------";
php ./vendor/bin/phpunit
