#!/bin/bash
#-----------------------------------------------------------
#
# Purpose: Run phing in a travis environment
#
# Target system: travis-ci
#-----------------------------------------------------------

    if [[ $TRAVIS_PHP_VERSION != 'hhvm-nightly' && $TRAVIS_PHP_VERSION != 'hhvm' ]]; then
        echo -e "\nAuto-discover pear channels and upgrade ..."
        pear config-set auto_discover 1
        pear -qq channel-update pear.php.net
        pear -qq channel-discover pear.phing.info
        echo "... OK"
    fi
    
    echo -e "\nInstalling composer packages ... "
    composer selfupdate --quiet
    composer install -o --no-progress --prefer-dist

    if [[ $TRAVIS_PHP_VERSION != 'hhvm-nightly' && $TRAVIS_PHP_VERSION != 'hhvm' ]]; then
        phpenv config-add .travis.php.ini
    else
        echo "hhvm.libxml.ext_entity_whitelist = file" >> /etc/hhvm/php.ini
        phpenv rehash
        echo "hhvm.libxml.ext_entity_whitelist = file" >> /etc/hhvm/php.ini
    fi
    
    phpenv rehash

    echo "=== SETTING GIT IDENTITY ==="
    git config --global user.email "travis-ci-build@phing.info"
    git config --global user.name "Phing Travis Builder"

    echo "=== TESTING PHING ==="
    cd test
    ../bin/phing


#------------------------------------------------------- eof
