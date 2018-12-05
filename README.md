# P     H     I     N     G

  [![Build Status](https://travis-ci.org/phingofficial/phing.svg?branch=master)](https://travis-ci.org/phingofficial/phing) [![codecov](https://codecov.io/gh/phingofficial/phing/branch/master/graph/badge.svg)](https://codecov.io/gh/phingofficial/phing) [![Build status](https://ci.appveyor.com/api/projects/status/ws2xv6tuu36sere7/branch/master?svg=true)](https://ci.appveyor.com/project/mrook/phing/branch/master)

  Thank you for using PHING!

  (PH)ing (I)s (N)ot (G)NU make; it's a PHP project build system or build tool based on Apache Ant. You can do anything with it that you could do with a traditional build system like GNU make, and its use of simple XML build files and extensible PHP "task" classes make it an easy-to-use and highly flexible build framework.

  Features include running PHPUnit unit tests (including test result and coverage reports), file transformations (e.g. token replacement, XSLT transformation, template transformations), file system operations, interactive build support, SQL execution, Git/Subversion operations, tools for creating PEAR packages, documentation generation (PhpDocumentor, ApiGen) and much, much more.

  If you find yourself writing custom scripts to handle the packaging, deploying, or testing of your applications, then we suggest looking at Phing. Pre-packaged with numerous out-of-the-box operation modules (tasks), and an easy-to-use OO model to extend or add your own custom tasks.

  Phing provides the following features:

  * Simple XML buildfiles
  * Rich set of provided tasks
  * Easily extendable via PHP classes
  * Works on Linux, Mac & Windows
  * No required external dependencies
  * Runs great on PHP 7

## The Latest Version

  Details of the latest version can be found on the Phing homepage
  <https://www.phing.info/>.

## Supported PHP versions

  Phing 3.x is compatible with PHP 7.1 and higher.

## Installation

  1. **Composer**

  The preferred method to install Phing is through [Composer](https://getcomposer.org/).
  Add [phing/phing](https://packagist.org/packages/phing/phing) to the
  require-dev or require section of your project's `composer.json`
  configuration file, and run 'composer install':

         {
             "require-dev": {
                 "phing/phing": "3.0.x-dev"
             }
         }

  2. **Phar**

  Download the [Phar archive](https://www.phing.info/get/phing-latest.phar).
  The archive can then be executed by running:

         $ php phing-latest.phar

  3. **Docker** (experimental)

  The official Phing Docker image can be found on [Docker Hub](https://hub.docker.com/r/phing/phing/).

  To execute Phing inside a container and execute `build.xml` located in `/home/user`, run the following:

         $ docker run --rm phing/phing:3.0 -v /home/foo:/opt -f /opt/build.xml

## Documentation

  Documentation is available in various formats in the *docs/docbook5/en/output*
  directory (generated from DocBook sources located in *docs/docbook5/en/source*).

  For online documentation, you can also visit the Phing website: https://www.phing.info/

## Contact

  * Twitter: [@phingofficial](https://twitter.com/phingofficial)
  * Website: [https://www.phing.info](https://www.phing.info)
  * Slack:   [https://www.phing.info/slack/](https://slack.phing.info)
  * IRC:     Freenode, #phing
  * GitHub:  [https://www.github.com/phingofficial/phing](https://www.github.com/phingofficial/phing)

## Donations

Developing and maintaining Phing has cost many hours over the years. If you want to show your appreciation, you can use one of the following methods to donate something to the project maintainer, Michiel Rook:

  * Become a patron on [Patreon](https://www.patreon.com/michielrook)
  * [Flattr](https://flattr.com/thing/1350991/The-Phing-Project) Phing
  * Send money via [PayPal](https://www.paypal.me/MichielRook)
  * Choose something from the [Amazon Wishlist](https://www.amazon.com/hz/wishlist/ls/10DZLPG9U429I)

Thank you!

## Contributing

We love contributions!

### Help us spot & fix bugs

We greatly appreciate it when users report issues or come up with feature requests. However, there are a few guidelines you should observe before submitting a new issue:

  * Make sure the issue has not already been submitted, by searching through the list of (closed) issues.
  * Support and installation questions should be asked on Twitter, Slack or IRC, not filed as issues.
  * Give a good description of the problem, this also includes the necessary steps to reproduce the problem!
  * If you have a solution - please tell us! This doesn't have to be code. We appreciate any snippets, thoughts, ideas, etc that can help us resolve the issue.

Issues can be reported on [GitHub](https://github.com/phingofficial/phing/issues).

### Pull requests

The best way to submit code to Phing is to [make a Pull Request on GitHub](https://help.github.com/articles/creating-a-pull-request).
Please help us merge your contribution quickly and keep your pull requests clean and concise: squash commits and don't introduce unnecessary (whitespace) changes.

Phing's source code is formatted according to the PSR-2 standard.

### Running the (unit) tests

If you'd like to contribute code to Phing, please make sure you run the tests before submitting your pull request. To successfully run all Phing tests, the following conditions have to be met:

  * PEAR installed, channel "pear.phing.info" discovered
  * Packages "python-docutils" and "subversion" installed
  * php.ini setting "phar.readonly" set to "Off"

Then, perform the following steps (on a clone/fork of Phing):

         $ composer install
         $ cd test
         $ ../bin/phing

## Licensing

  This software is licensed under the terms you may find in the file
  named "LICENSE" in this directory.

Proud to use:

[![PhpStorm Logo](http://www.jetbrains.com/phpstorm/documentation/phpstorm_banners/phpstorm1/phpstorm468x60_violet.gif "Proud to use")](http://www.jetbrains.com/phpstorm)
