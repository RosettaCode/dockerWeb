#!/bin/sh

set -euxo pipefail

function cleanup() {
    if ! [ -z ${tail_pid} ] ; then
        kill $tail_pid
    fi
}

trap cleanup EXIT

apk add iproute2

# Regen the autoload script before we run tests.
php composer.phar install

mkfifo /usr/local/var/log/error_log

>&2 tail -f /usr/local/var/log/error_log&
tail_pid=$!

nohup sh -c "timeout 60 php-fpm > /usr/local/var/log/error_log 2>&1 "

vendor/bin/phpunit --testdox tests

kill $tail_pid
