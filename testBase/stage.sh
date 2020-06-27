#!/bin/sh

set -e # Abort script on any error
set -u # Abort script on dereference of undefined variable
set -o pipefail # Treat error within pipeline as a script-level error
set -x # Trace what the script is doing

# Install and verify PHPUnit for our testing, since we'll have PHP available anyway.

curl --verbose --location --output phive.phar https://phar.io/releases/phive.phar
curl --verbose --location --output phive.phar.asc https://phar.io/releases/phive.phar.asc

apk add gnupg

gpg --keyserver pool.sks-keyservers.net --recv-keys 0x9D8A98B29B2D5D79
gpg --verify phive.phar.asc phive.phar
chmod +x phive.phar

apk add ncurses # Needed for tput, needed by phive.

# Sebastian Bergmann's signing key
# -- 0x4AA394086372C20A
# If phive ever adds a way to accept keys iff there's a fingerprint match, let me know?
./phive.phar install phpunit --trust-gpg-keys 4AA394086372C20A
