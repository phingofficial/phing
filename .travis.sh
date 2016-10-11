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
    
    if [[ $TRAVIS_PHP_VERSION < 5.3 ]]; then
        pear upgrade pecl.php.net/Phar ||
            pear install pecl.php.net/Phar

        echo -e "\nInstalling / upgrading phpcs ... "
        which phpcs >/dev/null                             &&
            pear upgrade pear.php.net/PHP_CodeSniffer ||
            pear install pear.php.net/PHP_CodeSniffer
        phpenv rehash
        # re-test for phpcs:
        phpcs --version 2>&1 >/dev/null   &&
            echo "... OK"
            
        echo -e "\nInstalling / upgrading phpdepend ... "
        which pdepend >/dev/null                      &&
            pear upgrade pear.pdepend.org/PHP_Depend-1.1.0 ||
            pear install pear.pdepend.org/PHP_Depend-1.1.0
        
        echo -e "\nInstalling PEAR packages ... "
        pear install pear/XML_Serializer-beta
        pear install --alldeps PEAR_PackageFileManager
        pear install --alldeps PEAR_PackageFileManager2
        pear install Net_Growl
        pear install HTTP_Request2
        pear install VersionControl_SVN-alpha
        pear install VersionControl_Git-alpha
        
        mkdir vendor
        touch vendor/autoload.php
    else
    	echo -e "\nInstalling composer packages ... "
    	composer selfupdate --quiet
        composer install -o --no-progress --prefer-dist --ignore-platform-reqs
    fi

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
