#!/bin/bash
#-----------------------------------------------------------
#
# Purpose: Run phing in a travis environment
#
# Target system: travis-ci
#-----------------------------------------------------------

installPearTask ()
{
    echo -e "\nAuto-discover pear channels and upgrade ..."
    pear config-set auto_discover 1
    pear -qq channel-update pear.php.net
    pear -qq upgrade
    echo "... OK"

    echo -e "\nInstalling / upgrading phing ... "
    which phing >/dev/null                      &&
        pear upgrade pear.phing.info/phing ||
        pear install --alldeps pear.phing.info/phing

    # update paths
    phpenv rehash

    # re-test for phing:
    phing -v 2>&1 >/dev/null    &&
        echo "... OK"           ||
        return 1

    sudo apt-get install python-docutils
    pear install VersionControl_Git-alpha
    pear install PEAR_PackageFileManager
    pear install Net_Growl

    # update paths
    phpenv rehash
}


#-----------------------------------------------------------

    installPearTask &&
        echo -e "\nSUCCESS - PHP ENVIRONMENT READY." ||
        ( echo "=== FAILED."; exit 1 )

    echo "=== BUILDING PHING ==="
    cd build
    phing -Dversion=2.0.0b1

    echo "=== TESTING PHING ==="
    cd ../test
    ../bin/phing


#------------------------------------------------------- eof
