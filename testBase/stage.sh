#!/bin/sh

set -e # Abort script on any error
set -u # Abort script on dereference of undefined variable
set -o pipefail # Treat error within pipeline as a script-level error
set -x # Trace what the script is doing

# Install and verify Composer, to use to install the rest of the dependencies.
# I'd prefer to use phive, as it can use GPG signatures for validation. However:
# 
#  - Not all the dependencies I want (I.e. adoy/PHP-FastCGI-Client ) provide these signatures anyway
#  - Composer has better tooling integration, most notably with Github's dependency mapping
#  - I'd like to use Github's dependency mapping to trigger auto-rebuilds as listed dependencies change

curl --verbose --location --output expected_composure_signature.sig https://composer.github.io/installer.sig
expected_signature=$(cat expected_composure_signature.sig)

curl --verbose --location --output composer-setup.php https://getcomposer.org/installer
actual_signature=$(php -r "echo hash_file('SHA384', 'composer-setup.php');")

[ "${expected_signature}" == "${actual_signature}" ]

php composer-setup.php

php composer.phar install

vendor/bin/phpunit tests
