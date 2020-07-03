    #!/bin/sh

    set -e # Abort script on any error
    set -u # Abort script on dereference of undefined variable
    set -o pipefail # Treat error within pipeline as a script-level error
    set -x # Trace what the script is doing

    # Helper function for getting random numbers. Used for wiggling the exponential backoff.
    # Returns a random number from 0 to 255.
    get_rand() {
        head -c1 /dev/urandom | hexdump -e '"%u"'
    }

    # Helper function for retrieving objects over HTTP, as requests from inside GHA timeout on connect as often as not.
    # We use an exponential backoff function that incorporates a random component to spread requests around.
    # I don't know that anyone will use the code but me, but if they do, and the code starts getting used at scale, I'd rather avoid contributing to thundering herds when systemic glitches cause a whole lot of requests to temporarily fail at around the same time. Maybe caches on a remote end somewhere need an opportunity to warm up or whatever, but if things break, treat the world gently, try not to make it worse.
    get() {
        filename=${1}
        url=${2}
        max_time=${3:-300} # Max time spent sleeping.

        slept_time=0
        sleep_time=1
        rc=1
        while [ $((sleep_time + slept_time)) -lt ${max_time} ] ; do

            # Only sleep if we had a failure.
            if [ ${rc} -ne 0 ]; then
                # We sleep first, then calculate sleep time, so that the 'while' loop knows how much we'll sleep next time.
                # sleep ${sleep_time}
                
                # Record how long we slept
                slept_time=$((slept_time + sleep_time))
                echo slept: $slept_time

                # Wiggle will be one of -2, -1, 0, 1 or 2.
                wiggle=$(( ( ( $(get_rand) + $(get_rand) + $(get_rand) + $(get_rand) + 4 ) / 255 ) - 2 ))

                # Exponential backoff with a little wiggle
                sleep_time=$((sleep_time * 2 + wiggle))

                # Make sure we didn't wiggle negative, make sure we sleep a little.
                if [ ${sleep_time} -le 0 ]; then
                    sleep_time=1
                fi
            fi

            rc=1
            curl --location --output "${filename}" "${url}" ; rc=$?

            if [ ${rc} -eq 0 ] ; then
                break
            fi
        done

        return ${rc}
    }

    # Install and verify Composer, to use to install the rest of the dependencies.
    # I'd prefer to use phive, as it can use GPG signatures for validation. However:
    # 
    #  - Not all the dependencies I want (I.e. adoy/PHP-FastCGI-Client ) provide these signatures anyway
    #  - Composer has better tooling integration, most notably with Github's dependency mapping
    #  - I'd like to use Github's dependency mapping to trigger auto-rebuilds as listed dependencies change

    get "expected_composure_signature.sig" "https://composer.github.io/installer.sig"
    expected_signature=$(cat expected_composure_signature.sig)

    get "composer-setup.php" "https://getcomposer.org/installer"
    actual_signature=$(php -r "echo hash_file('SHA384', 'composer-setup.php');")

    [ "${expected_signature}" == "${actual_signature}" ]

    php composer-setup.php

    # Because composer will complain. We'll re-run in a later stage, we just don't want to regen this layer all the time.
    mkdir -p src
    php composer.phar install
