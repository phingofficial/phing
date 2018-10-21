#!/bin/bash
#-----------------------------------------------------------
#
# Purpose: Run phing in a travis environment
#
# Target system: travis-ci
#-----------------------------------------------------------
    set -e

    pear config-set php_dir $(php -r 'echo substr(get_include_path(),2);')

    echo -e "\nAuto-discover pear channels and upgrade ..."
    pear config-set auto_discover 1
    pear -qq channel-update pear.php.net
    pear -qq channel-discover pear.phing.info
    echo "... OK"

    echo -e "\nInstalling composer packages ... "
    composer selfupdate --quiet
    composer install -o --no-progress --prefer-dist

    phpenv config-add .travis.php.ini
    phpenv rehash

    echo "=== SETTING GIT IDENTITY ==="
    git config --global user.email "travis-ci-build@phing.info"
    git config --global user.name "Phing Travis Builder"

    echo "=== TESTING PHING ==="
    cd test
    ../bin/phing -Dtests.codecoverage=true
    cd ..
    
#    echo "=== BUILDING PHING ==="
#    cd build
#    ../bin/phing
#    cd ..

    if [[ "$TRAVIS_BRANCH" == "master" ]]; then
      bash <(curl -s https://codecov.io/bash)
    fi

#------------------------------------------------------- eof
