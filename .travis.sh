#!/bin/bash
#-----------------------------------------------------------
#
# Purpose: Run phing in a travis environment
#
# Target system: travis-ci
#-----------------------------------------------------------

    sudo apt-get update -qq
    
    echo -e "\nAuto-discover pear channels and upgrade ..."
    pear config-set auto_discover 1
    pear -qq channel-update pear.php.net
    pear -qq channel-discover pear.phing.info
    echo "... OK"


    sudo apt-get install python-docutils

    if [[ $TRAVIS_PHP_VERSION < 5.3 ]]; then
    	pear install -f phpunit/File_Iterator-1.3.2
    	pear install -f phpunit/PHP_TokenStream-1.1.4
    	pear install -f phpunit/PHP_Timer-1.0.3
    	pear install -f phpunit/Text_Template-1.1.1
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
        echo -e "\nInstalling / upgrading phpcpd ... "
        which phpcpd >/dev/null                      &&
            pear upgrade pear.phpunit.de/phpcpd-1.3.5 ||
            pear install pear.phpunit.de/phpcpd-1.3.5

        echo -e "\nInstalling / upgrading phploc ... "
        which phploc >/dev/null                      &&
            pear upgrade pear.phpunit.de/phploc-1.6.4 ||
            pear install pear.phpunit.de/phploc-1.6.4
            
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
        composer install -o --no-progress
    fi

    phpenv config-add .travis.php.ini
    phpenv rehash

    echo "=== SETTING GIT IDENTITY ==="
    git config --global user.email "travis-ci-build@phing.info"
    git config --global user.name "Phing Travis Builder"

    echo "=== TESTING PHING ==="
    cd test
    ../bin/phing


#------------------------------------------------------- eof
