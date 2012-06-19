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
}


#-----------------------------------------------------------

    installPearTask &&
        echo -e "\nSUCCESS - PHP ENVIRONMENT READY." ||
        ( echo "=== FAILED."; exit 1 )

    phing $*

#------------------------------------------------------- eof
