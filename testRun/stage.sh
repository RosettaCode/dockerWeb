#!/bin/sh

# Regen the autoload script before we run tests.
php composer.phar install

vendor/bin/phpunit --testdox tests
