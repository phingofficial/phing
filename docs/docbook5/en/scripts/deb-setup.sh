#!/bin/sh
# =========================================================================
# Setup script to install all necessary dependencies for a Debian 7 system
# in order to create DocBook5 rendered output for the Phing project. 
#
# This script should be run as root
#
# After a fresh install of Debian 7 this script is all you
# need to run in order to get a basic development environment for Phing
# which will allow you to both develop and render all documentation.
#
# The script does the following:
#
#   1. Installs all debian XML dependencies
#   2. Installs Phing
#   3. Downloads and installs the 1.78.1 DocBook5 XSL stylesheets
#   4. Adjusts some paths for stylesheets
#
# The last section is commented out by default. It clones a remote git
# (git-hub) repository and could be changed to your own repo to get it
# setup as well. After cloning it tests the system by building all the
# supported output formats of the documentation.
# =========================================================================

# Make sure the indexes are up to date
apt-get update

# First get all basic dependencies after a fresh Deb 7 install 
apt-get -y -qq install git 
apt-get -y -qq install docbook5-xml
apt-get -y -qq install libxslthl-java
apt-get -y -qq install fop
apt-get -y -qq install libservlet2.5-java
apt-get -y -qq install libxml2-utils
apt-get -y -qq install xsltproc
apt-get -y -qq install ant
apt-get -y -qq install ruby
apt-get -y -qq install zip
apt-get -y -qq install php5-dev
apt-get -y -qq install php5
apt-get -y -qq install php-pear

# Uncomment the following line to get some nice but not strictly necessary packages.
# apt-get -y -qq install emacs ncftp pure-ftpd

# Install Phing with all deps
pear channel-discover pear.phing.info
pear config-set preferred_state alpha
pear install --alldeps phing/phing
pear config-set preferred_state stable

# Adjust paths to make saxon 6 appear as saxon6.jar
cd /usr/share/java
ln -s saxon.jar saxon6.jar

# Setup latest Docbook5 stylesheets (we use the namespace version)
mkdir -p /usr/share/xml/docbook/stylesheet/nwalsh5
cd /usr/share/xml/docbook/stylesheet/nwalsh5
wget -q https://sourceforge.net/projects/docbook/files/docbook-xsl-ns/1.78.1/docbook-xsl-ns-1.78.1.tar.bz2
tar xjf docbook-xsl-ns-1.78.1.tar.bz2
ln -s docbook-xsl-ns-1.78.1 current
rm docbook-xsl-ns-1.78.1.tar.bz2

# Make dbtoepub command executable to be able to run it
chmod +x /usr/share/xml/docbook/stylesheet/nwalsh5/current/epub/bin/dbtoepub

echo "#=========================================================="
echo "# DONE. Development system configured!"
echo "#=========================================================="

# Uncomment and update the following section to have your repo cloned
# automatically
#cd ~ 
#mkdir dev-phing 
#cd dev-phing
#git clone https://github.com/YOUR_USER_HERE/phing.git
#cd phing/docs/docbook5/en
#phing clean
#time phing all




