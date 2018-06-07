all: install cs test

install:
	composer install

cs:
	php vendor/bin/phpcs

csfix:
	php vendor/bin/phpcbf

test:
	php vendor/bin/phpunit
