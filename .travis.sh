#!/bin/bash
#-----------------------------------------------------------
#
# Purpose: Run phing in a travis environment
#
# Target system: travis-ci
#-----------------------------------------------------------

installPearTask ()
{
    sudo apt-get update -qq
#    sudo apt-get install -qq php5-xdebug
    
    echo -e "\nAuto-discover pear channels and upgrade ..."
    pear config-set auto_discover 1
    pear -qq channel-update pear.php.net
    pear -qq upgrade
    pear -qq channel-discover pear.phing.info
    echo "... OK"

    echo -e "\nInstalling / upgrading phpcpd ... "
    which phpcpd >/dev/null                      &&
        pear upgrade pear.phpunit.de/phpcpd ||
        pear install pear.phpunit.de/phpcpd
    phpenv rehash

    echo -e "\nInstalling / upgrading phploc ... "
    which phploc >/dev/null                      &&
        pear upgrade pear.phpunit.de/phploc ||
        pear install pear.phpunit.de/phploc
    phpenv rehash

    echo -e "\nInstalling / upgrading phpdepend ... "
    if [[ $TRAVIS_PHP_VERSION < 5.3 ]]; then
        which pdepend >/dev/null                      &&
            pear upgrade pear.pdepend.org/PHP_Depend-1.1.0 ||
            pear install pear.pdepend.org/PHP_Depend-1.1.0
    else
        which pdepend >/dev/null                      &&
            pear upgrade pear.pdepend.org/PHP_Depend-beta ||
            pear install pear.pdepend.org/PHP_Depend-beta
    fi
    phpenv rehash

    echo -e "\nInstalling / upgrading phpcs ... "
    which phpcs >/dev/null                             &&
        pear upgrade pear.php.net/PHP_CodeSniffer ||
        pear install pear.php.net/PHP_CodeSniffer
    phpenv rehash
    # re-test for phpcs:
    phpcs --version 2>&1 >/dev/null   &&
        echo "... OK"           ||
        return 1

    sudo apt-get install python-docutils
    pear install VersionControl_Git-alpha
    pear install VersionControl_SVN-alpha
    pear install pear/XML_Serializer-beta
    pear install --alldeps PEAR_PackageFileManager
    pear install --alldeps PEAR_PackageFileManager2
    pear install Net_Growl
    pear install HTTP_Request2

    # update paths
    phpenv rehash
}


#-----------------------------------------------------------

    installPearTask &&
        echo -e "\nSUCCESS - PHP ENVIRONMENT READY." ||
        ( echo "=== FAILED."; exit 1 )

    if [[ $TRAVIS_PHP_VERSION < 5.3 ]]; then
    	pear install -f phpunit/File_Iterator-1.3.2
    	pear install -f phpunit/PHP_TokenStream-1.1.4
    	pear install -f phpunit/PHP_Timer-1.0.3
    	pear install -f phpunit/Text_Template-1.1.1
        pear upgrade pecl.php.net/Phar ||
            pear install pecl.php.net/Phar
        phpenv rehash
    else
    	composer selfupdate --quiet
        composer install
    fi

#    echo "=== BUILDING PHING ==="
#    cd build
#    phing -Dversion=2.0.0b1

    phpenv config-add .travis.php.ini

    echo "=== SETTING GIT IDENTITY ==="
    git config --global user.email "travis-ci-build@phing.info"
    git config --global user.name "Phing Travis Builder"

    echo "=== TESTING PHING ==="
    cd test
    ../bin/phing


#------------------------------------------------------- eof
