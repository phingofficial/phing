P     H     I     N     G
=========================


Dec. 22, 2016 - Phing 2.16.0
----------------------------

This release contains the following new or improved functionality:

 * Append, Property, Sleep, Sonar and Truncate tasks
 * Improved PHP 7.1 compatibility
 * Various typo and bug fixes, documentation updates

This release will most likely be the last minor update in the 2.x series. Phing 3.x will drop support for PHP < 5.6.

The following issues were closed in this release:

 * phing should get a strict mode \(Trac \#918\) [\#554](https://github.com/phingofficial/phing/issues/554)
 * Can not delete git folders on windows \(Trac \#956\) [\#556](https://github.com/phingofficial/phing/issues/556)
 * Relative symlinks \(Trac \#1124\) [\#567](https://github.com/phingofficial/phing/issues/567)
 * Tests fail under windows \(Trac \#1215\)  [\#578](https://github.com/phingofficial/phing/issues/578)
 * stripphpcomments matches links in html \(Trac \#1219\) [\#579](https://github.com/phingofficial/phing/issues/579)
 * OS detection fails on OSX \(Trac \#1227\) [\#581](https://github.com/phingofficial/phing/issues/581)
 * JsHintTask fails when reporter attribute is not set \(Trac \#1230\) [\#582](https://github.com/phingofficial/phing/issues/582)
 * An issue with 'file' attribute of 'append' task \(v2.15.1\) [\#595](https://github.com/phingofficial/phing/issues/595)
 * An issue with 'append' task when adding a list of files in a directory \(v2.15.1\) [\#597](https://github.com/phingofficial/phing/issues/597)
 * Git auto modified file with phing vendor [\#613](https://github.com/phingofficial/phing/issues/613)
 * phar file not working - \Symfony\Component\Yaml\Parser' not found [\#614](https://github.com/phingofficial/phing/issues/614)
 * JSHint — Support of specific config file path [\#615](https://github.com/phingofficial/phing/issues/615)
 * PHP notice on 7.1: A non well formed numeric value encountered [\#622](https://github.com/phingofficial/phing/issues/622)
 * Sass task fails when PEAR is not installed [\#624](https://github.com/phingofficial/phing/issues/624)
 * sha-512 hash for phing-latest.phar [\#629](https://github.com/phingofficial/phing/issues/629)

Oct. 13, 2016 - Phing 2.15.2
----------------------------

This release fixes a regression introduced in 2.15.1:

 * #593 - Changed behavior in <fileset/> filtering in 2.15.1

Oct. 11, 2016 - Phing 2.15.1
----------------------------

This release fixes a missing include and two bugs:

 * [https://www.phing.info/trac/ticket/1264] delete fileset /foo.php deletes /baz.foo.php
 * [https://www.phing.info/trac/ticket/1038] PhingFile getPathWithoutBase does not work for files outside basedir

Sep. 14, 2016 - Phing 2.15.0
----------------------------

This release contains the following new or improved functionality:

 * PHP 7.0 compatibility was improved
 * Phing grammar was updated
 * Tasks to work with Mercurial were added
 * Various typo and bug fixes, documentation updates

The following tickets were closed in this release:

 * [https://www.phing.info/trac/ticket/1263] Error in SassTask on PHP 7
 * [https://www.phing.info/trac/ticket/1262] Fatal error in SassTask when Sass gem is not installed
 * [https://www.phing.info/trac/ticket/1259] PHP_CLASSPATH Enviroment Variable
 * [https://www.phing.info/trac/ticket/1258] ApigenTask issue
 * [https://www.phing.info/trac/ticket/1257] The phpunit-code-coverage version 4.x breaks the phing-tasks-phpunit component
 * [https://www.phing.info/trac/ticket/1254] ftpdeploy : [PHP Error] require_once(PEAR.php): failed to open stream: No such file or directory [line 251 of site\vendor\phing\phing\src\Task\Ext\FtpDeploy.php]
 * [https://www.phing.info/trac/ticket/1253] Phing gitlog task not return last commit when committer's system time is set forward
 * [https://www.phing.info/trac/ticket/1249] First tstamp task is generating wrong timestamp
 * [https://www.phing.info/trac/ticket/1247] IsProperty(True/False)Condition doesn't support the 'name' attribute
 * [https://www.phing.info/trac/ticket/1246] FailTask with nested condition always fails
 * [https://www.phing.info/trac/ticket/1243] Command line argument with "|" character must be quoted
 * [https://www.phing.info/trac/ticket/1238] Add documentation for Smarty and ReplaceRegexp tasks
 * [https://www.phing.info/trac/ticket/566] Add Mercurial support

Mar. 10, 2016 - Phing 2.14.0
----------------------------

This release contains the following new or improved functionality:

 * Phing can now emit a specific status code on exit after failing
 * Added IsPropertyTrue/IsPropertyFalse conditions
 * Added IsWritable / IsReadable selectors
 * Added GitDescribe task
 * Added CutDirs mapper
 * Line breaks in property files on Windows machines fixed
 * FileSync task now supports excluding multiple files/directories
 * Various typo and bug fixes, documentation updates

The following tickets were closed in this release:

 * [https://www.phing.info/trac/ticket/1245] ExecTask documentation has incorrect escape attribute default value
 * [https://www.phing.info/trac/ticket/1244] phpunit task -- problem when listener depends on bootstrap
 * [https://www.phing.info/trac/ticket/1242] symfonyConsoleTask does not quote path to console
 * [https://www.phing.info/trac/ticket/1241] SymfonyConsoleTask's checkreturn / propertyname are not documented
 * [https://www.phing.info/trac/ticket/1239] ResolvePath just concatenates if "dir" attribute is present
 * [https://www.phing.info/trac/ticket/1237] HttpGetTask should catch HTTP_Request2_Exception, throw BuildException
 * [https://www.phing.info/trac/ticket/1236] version-compare condition typo in documentation
 * [https://www.phing.info/trac/ticket/1235] misworded sentence in documentation
 * [https://www.phing.info/trac/ticket/1234] IsFailure condition always evaluates to TRUE
 * [https://www.phing.info/trac/ticket/1231] JsHintTask fails when filename contains double quotes
 * [https://www.phing.info/trac/ticket/1198] PropertyTask resolving UTF-8 special chars in file attribute
 * [https://www.phing.info/trac/ticket/1194] Update relax-ng schema
 * [https://www.phing.info/trac/ticket/1132] Provide SHA512 sum of all generated archives for a release
 * [https://www.phing.info/trac/ticket/1131] Verification of changelog file fails when your file is in a directory added in your classpathref
 * [https://www.phing.info/trac/ticket/1046] ReplaceTokensWithFile doesn't support begintoken/endtokens with / in them

Dec. 4, 2015 - Phing 2.13.0
---------------------------

This release contains the following new or improved functionality:

 * '-listener' command line argument
 * SSL connections in FtpDeploy task
 * IsFailure condition
 * Crap4J PHPUnit formatter
 * FirstMatch mapper
 * PhpArrayMapLines filter
 * NotifySend, Attrib tasks
 * Json and Xml command line loggers
 * Property parser now supports YAML files
 * PHPUnit 5.x supported
 * PHP 7 fixes
 * Updated Apigen support
 * PhpCodeSniffer task can now populate a property with used sniffs
 * PHPMD and PhpCodeSniffer task can now cache results to speed up
   subsequent runs
 * Various typo and bug fixes, documentation updates

The following tickets were closed in this release:

 * [https://www.phing.info/trac/ticket/1224] JSHint and space in the path of the workspace (Windows 7)
 * [https://www.phing.info/trac/ticket/1221] Case insensitive switch doesn't work
 * [https://www.phing.info/trac/ticket/1217] Add ability to ignore symlinks in zip task
 * [https://www.phing.info/trac/ticket/1212] Add support for formatters for PhpLoc task
 * [https://www.phing.info/trac/ticket/1187] Disable compression of phing.phar to make it work on hhvm

Aug. 24, 2015 - Phing 2.12.0
----------------------------

This release contains the following new or improved functionality:

 * Retry, Tempfile, Inifile tasks
 * 'keepgoing' command line mode
 * Fileset support in the Import task
 * EscapeUnicode, Concat filters
 * Profile logger
 * Composite mapper
 * Various typo and bug fixes

The following tickets were closed in this release:

 * [https://www.phing.info/trac/ticket/1208] When UntarTask fails to extract an archive it should tell why
 * [https://www.phing.info/trac/ticket/1207] PackageAsPath Task exists in 2.11, but not in documentation
 * [https://www.phing.info/trac/ticket/1206] WaitFor task has maxwaitunit attribute, not WaitUnit
 * [https://www.phing.info/trac/ticket/1205] Triple "B.37.1 Supported Nested Tags" header
 * [https://www.phing.info/trac/ticket/1204] Wrong type of record task loglevel attribute
 * [https://www.phing.info/trac/ticket/1203] Duplicated doc for Apply task, spawn attribute
 * [https://www.phing.info/trac/ticket/1199] PHPUnitReport task: package name detection no longer works
 * [https://www.phing.info/trac/ticket/1196] Target 'phing.listener.AnsiColorLogger' does not exist in this project.
 * [https://www.phing.info/trac/ticket/1193] There is no native method for manipulating .ini files.
 * [https://www.phing.info/trac/ticket/1191] phing parallel task should handle workers dying unexpectedly
 * [https://www.phing.info/trac/ticket/1190] RegexTask processes backslashes incorrectly
 * [https://www.phing.info/trac/ticket/1189] Coverage Report broken for Jenkins PHP Clover
 * [https://www.phing.info/trac/ticket/1178] Parameter getValue is null when parameter is equal to 0
 * [https://www.phing.info/trac/ticket/1148] phpdoc2 via phar

May 20, 2015 - Phing 2.11.0
---------------------------

This release contains the following new or improved functionality:

 * PharData and EchoProperties tasks
 * 'silent' and 'emacs' command line modes
 * Improvements to FileHash and FtpDeploy tasks
 * SuffixLines and Sort filters

The following tickets were closed in this release:

 * [https://www.phing.info/trac/ticket/1186] Implement pharLocation attribute for PHP_Depend task
 * [https://www.phing.info/trac/ticket/1185] Implement pharLocation attribute for PHPMD task
 * [https://www.phing.info/trac/ticket/1183] Fatal error in PHPMDTask
 * [https://www.phing.info/trac/ticket/1176] Showwarnings doesn't work
 * [https://www.phing.info/trac/ticket/1170] Allow more than one code standard review for PHP_CodeSniffer.
 * [https://www.phing.info/trac/ticket/1169] Allow for fuzzy parameter for phpcpdPHPCPD
 * [https://www.phing.info/trac/ticket/1162] add depth param to GitCloneTask
 * [https://www.phing.info/trac/ticket/1161] Update phpcpd & phploc tasks to work with phar versions
 * [https://www.phing.info/trac/ticket/1134] Phar version did not provide colorized output
 * [https://www.phing.info/trac/ticket/462] Incremental uploads in ftp deploy task

Feb. 19, 2015 - Phing 2.10.1
----------------------------

This release fixes the following tickets:

 * [https://www.phing.info/trac/ticket/1174] Phing can't work PHPUnit(PHAR)
 * [https://www.phing.info/trac/ticket/1173] [PHP Error] include_once(PHP/PPMD/Renderer/XMLRenderer.php): failed to open stream: No such file or directory
 * [https://www.phing.info/trac/ticket/1171] Socket condition does not work

Feb. 9, 2015 - Phing 2.10.0
---------------------------

This release contains the following new or improved functionality:

 * 'user.home' property on Windows fixed
 * Various documentation updates
 * Added support for listeners configured via phpunit.xml config
 * Basename task
 * Dirname task
 * Diagnostics task
 * FilesMatch condition
 * HasFreeSpace condition
 * PathToFileSet task
 * PhingVersion task/condition
 * PropertyRegex task
 * Recorder task
 * Socket condition
 * Xor condition

The following tickets were closed in this release:

 * [https://www.phing.info/trac/ticket/1168] PhpCodeSnifferTask incompatible with PHP_CS 2.2.0
 * [https://www.phing.info/trac/ticket/1167] include task can't really have mode
 * [https://www.phing.info/trac/ticket/1163] Phing and PHPMD via composer both
 * [https://www.phing.info/trac/ticket/1160] Documentation lists covereage-report styledir as required.
 * [https://www.phing.info/trac/ticket/1159] phpunit task ignores excludeGroups, groups attributes
 * [https://www.phing.info/trac/ticket/1152] Add socket condition
 * [https://www.phing.info/trac/ticket/1127] Removing .phar from the phar file makes it crash
 * [https://www.phing.info/trac/ticket/1120] Phing 2.8.1 does not support PDepend 2.0
 * [https://www.phing.info/trac/ticket/856]  ZPK Packaging for zend server
 * [https://www.phing.info/trac/ticket/250]  recorder task

Dec. 3, 2014 - Phing 2.9.1
--------------------------

This releases fixes a Windows regression and adds the following new functionality:

 * Http condition
 * Switch task
 * Throw task

The following tickets were closed in this release:

 * [https://www.phing.info/trac/ticket/1158] Phing fails to call itself with Exec task
 * [https://www.phing.info/trac/ticket/1157] ZIP task ignores ${phing.dir}
 * [https://www.phing.info/trac/ticket/1156] phing+windows copy file path
 * [https://www.phing.info/trac/ticket/1155] Add http condition
 * [https://www.phing.info/trac/ticket/1154] Can't read version information file
 * [https://www.phing.info/trac/ticket/1147] Resetting Phing::$msgOutputLevel

Nov. 25, 2014 - Phing 2.9.0
---------------------------

This release contains the following new or improved functionality:

 * Phing now supports HHVM
 * Stopwatch task added
 * Unit test coverage increased
 * Source code formatted to PSR-2
 * Various bugs and documentation errors fixed

Additionally, the following Trac tickets (see www.phing.info) were fixed in this release:

 * [https://www.phing.info/trac/ticket/1151] PHPMD Task does not support the format tag
 * [https://www.phing.info/trac/ticket/1149] Exclude extra files from composer package
 * [https://www.phing.info/trac/ticket/1144] Reduce PhingCall/Foreach log messages
 * [https://www.phing.info/trac/ticket/1140] DefaultLogger is not default logger
 * [https://www.phing.info/trac/ticket/1138] ParallelTask - error in subtask should fail build
 * [https://www.phing.info/trac/ticket/1135] obfuscation-key option for IoncubeEncoderTask does not work
 * [https://www.phing.info/trac/ticket/1133] copytask haltonerror = "false" function failure when source dir not exists
 * [https://www.phing.info/trac/ticket/1130] Add documentation for Manifest task
 * [https://www.phing.info/trac/ticket/1129] ManifestTask md5 hash vs FileHashTask md5 hash not the same
 * [https://www.phing.info/trac/ticket/1128] Imported target won't run until there is one with the same name in main build.xml
 * [https://www.phing.info/trac/ticket/1123] ApplyTask outputProperty doesn't append
 * [https://www.phing.info/trac/ticket/1122] Untar task does not preserve file permissions
 * [https://www.phing.info/trac/ticket/1121] Please fix the syntax error in PHP Lint
 * [https://www.phing.info/trac/ticket/1104] ArchiveComment Parameter for ZipTask
 * [https://www.phing.info/trac/ticket/1095] ReferenceExistsCondition returns true for all UnknownElements
 * [https://www.phing.info/trac/ticket/1089] phing -l is listing imported targets twice
 * [https://www.phing.info/trac/ticket/1086] Support for running on HHVM
 * [https://www.phing.info/trac/ticket/1084] pdepend task does not find dependencies when installed by composer
 * [https://www.phing.info/trac/ticket/1069] PHPUnitTask formatter does not create directory if specified "todir" does not exist
 * [https://www.phing.info/trac/ticket/1068] Phingcall and Import issues
 * [https://www.phing.info/trac/ticket/1040] Composer task has no documentation
 * [https://www.phing.info/trac/ticket/1012] SymlinkTaks overwrite fails if target doesn't exist
 * [https://www.phing.info/trac/ticket/965] includePathTask: Allow appending and replacing
 * [https://www.phing.info/trac/ticket/945] several phpunit task problems
 * [https://www.phing.info/trac/ticket/930] Attribute logoutput to property task
 * [https://www.phing.info/trac/ticket/796] Can't delete all subdirectories without directory itself
 * [https://www.phing.info/trac/ticket/441] Reformat Phing source code to PSR-2

Jul. 18, 2014 - Phing 2.8.2
---------------------------

This patch release fixes two regressions.

 * [https://www.phing.info/trac/ticket/1119] #1111 breaks PHPLint task
 * [https://www.phing.info/trac/ticket/1118] Property "X" was circularly defined.

Jul. 1, 2014 - Phing 2.8.1
--------------------------

This patch release fixes a regression preventing Phing from
being used on machines where PEAR is not installed, as well
as another (unrelated) issue.

 * [https://www.phing.info/trac/ticket/1114] PHP Fatal Error using Phing on machines without PEAR
 * [https://www.phing.info/trac/ticket/1111] setting PhpLintTask interpreter

Jun. 30, 2014 - Phing 2.8.0
---------------------------

New or improved functionality:

 * The rsync task can now handle remote connections without specifying a username
 * The rsync task now creates remote directories as needed by default
 * Support for PHP MD 2.*
 * Various tasks now support dependencies loaded through composer
 * AutoloaderTask added
 * Various bugs and documentation errors fixed

Additionally, the following Trac tickets (see www.phing.info) were fixed in this release:

 * [https://www.phing.info/trac/ticket/1108] pdosqlexec doesn't throw exception for the non-first SQL instruction
 * [https://www.phing.info/trac/ticket/1106] Add .git and associated files to defaultexcludes attribute
 * [https://www.phing.info/trac/ticket/1105] PHPUnitTask: attributes 'groups' and 'excludeGroups' not documented
 * [https://www.phing.info/trac/ticket/1102] Phing is not compatible with PHPMD 2.0.0 beta
 * [https://www.phing.info/trac/ticket/1101] Add (optional) external deps to suggest section in composer.json
 * [https://www.phing.info/trac/ticket/1100] Add composer / PHAR installation instructions to README & web pages
 * [https://www.phing.info/trac/ticket/1099] Allow loading of externals through composer [meta ticket]
 * [https://www.phing.info/trac/ticket/1091] Phing is not compatible with PHPUnit 4.x
 * [https://www.phing.info/trac/ticket/1090] PearPackageFileSet copies files with baseinstalldir incorrectly
 * [https://www.phing.info/trac/ticket/1085] Conditions section (5.8) does not correctly link to mentioned tasks
 * [https://www.phing.info/trac/ticket/1084] pdepend task does not find dependencies when installed by composer
 * [https://www.phing.info/trac/ticket/980] Support for .dist files
 * [https://www.phing.info/trac/ticket/975] Included JSmin has non-free license
 * [https://www.phing.info/trac/ticket/964] includePathTask: talk about appending/prepending

Feb. 13, 2014 - Phing 2.7.0
---------------------------

New or improved functionality:

 * Support for PHP CodeSniffer 1.5, PHP Copy&Paste Detector 2.0 and PHPLOC 2.0
 * Composer support for PHPCPD and PhpDocumentor tasks
 * Fixed / improved error handling in various places
 * More unit / regression tests added
 * Various bugs and documentation errors fixed

Additionally, the following Trac tickets (see www.phing.info) were fixed in this release:

 * [https://www.phing.info/trac/ticket/1083] PhpDocumentor2Task: add support for default package name
 * [https://www.phing.info/trac/ticket/1082] Tasks in root target are executed twice
 * [https://www.phing.info/trac/ticket/1081] Documentation of AvailableTask does not link to conditions page
 * [https://www.phing.info/trac/ticket/1078] IoncubeEncoderTask does not support PHP 5.4
 * [https://www.phing.info/trac/ticket/1073] Phing silently died, when cant read build.xml
 * [https://www.phing.info/trac/ticket/1070] PHPCS 1.5.0 breaks PHPCodeSniffer Task
 * [https://www.phing.info/trac/ticket/1064] Formatter 'brief' not implemented when using Unittest task
 * [https://www.phing.info/trac/ticket/1063] PHPCPD 2.0 breaks PHPCPD Task
 * [https://www.phing.info/trac/ticket/1062] AvailableTask throws exception when filepath contains duplicates
 * [https://www.phing.info/trac/ticket/1059] phing exits with return code 0 when there is unknown argument
 * [https://www.phing.info/trac/ticket/1057] pdo exception thrown from pdosqlexec not properly handled
 * [https://www.phing.info/trac/ticket/1056] filesyncTask: problem (error?) with verbose (-v) option
 * [https://www.phing.info/trac/ticket/1054] Missing or erroneous definition in phing-grammar.rng
 * [https://www.phing.info/trac/ticket/1053] Add composer support for phpdoc2 task
 * [https://www.phing.info/trac/ticket/1051] phing 2.6.1 - impossible upgrade
 * [https://www.phing.info/trac/ticket/1045] PHPLocTask broken with recent phploc updates
 * [https://www.phing.info/trac/ticket/1044] Using fileset in echo does not list subdirectories
 * [https://www.phing.info/trac/ticket/1042] Fix UnknownElement wrapping and configuring
 * [https://www.phing.info/trac/ticket/1035] phpcpd tasks does not find dependencies when installed by composer
 * [https://www.phing.info/trac/ticket/1034] Improving debuggability of errors in custom code
 * [https://www.phing.info/trac/ticket/1032] FileSync Port
 * [https://www.phing.info/trac/ticket/1030] JsMin task creates directories with 0700 permissions
 * [https://www.phing.info/trac/ticket/1028] Change visibility of FailTask variables
 * [https://www.phing.info/trac/ticket/1021] MailTask backend configuration
 * [https://www.phing.info/trac/ticket/1010] Invalid error about refid attribute when specifying multiple targets
 * [https://www.phing.info/trac/ticket/1009] certain liquibase tasks (rollback, tag and update) do not check return value
 * [https://www.phing.info/trac/ticket/994] Clarify pdoexec autocommit/transactions
 * [https://www.phing.info/trac/ticket/991] GitCommit: add fileset support
 * [https://www.phing.info/trac/ticket/984] Improve documentation about including custom tasks
 * [https://www.phing.info/trac/ticket/983] Selenium with PHPUnit: browser configurations are not processed
 * [https://www.phing.info/trac/ticket/978] svn switches: recursive
 * [https://www.phing.info/trac/ticket/976] phpunitreport: broken html for test suite names containing "/"
 * [https://www.phing.info/trac/ticket/650] Namespace support for extensions (PSR0 support)

Aug. 27, 2013 - Phing 2.6.1
---------------------------

This patch release fixes a regression when setting properties
in then/else blocks.

Note: the fix currently disables support for custom conditions,
full support will be restored in Phing 2.7.0.

 * [https://www.phing.info/trac/ticket/1041] Properties within then/else blocks are not expanded

Aug. 21, 2013 - Phing 2.6.0
---------------------------

New or improved functionality:

 * Docbook5 documentation is now the main documentation; output targets
   are 'hlhtml', 'chunkhtml', 'hlpdf', 'epub' and 'webhelp'
 * HttpRequest task supports POST request
 * PharPackage task supports PKCS#12 certificate stores
 * WikiPublish task was added
 * Smarty task is now compatible with Smarty 3
 * A new logger 'TargetLogger' was added, displaying the execution time for each target
 * Composer task and package were updated
 * More unit / regression tests added
 * Various bugs and documentation errors fixed

Additionally, the following Trac tickets (see www.phing.info) were fixed in this release:

 * [https://www.phing.info/trac/ticket/1037] PropertyTask docs is wrong
 * [https://www.phing.info/trac/ticket/1036] Error in ApplyTask->executeCommand()
 * [https://www.phing.info/trac/ticket/1029] PhpDocumentor2 task broken with latest phpdoc version
 * [https://www.phing.info/trac/ticket/1027] RegexpMapper uses deprecated PREG_REPLACE_EVAL
 * [https://www.phing.info/trac/ticket/1025] PHPLocTask fails when installed via composer
 * [https://www.phing.info/trac/ticket/1023] Argument 1 passed to IniFileTokenReader::setFile() must be an instance of PhingFile
 * [https://www.phing.info/trac/ticket/1020] [PHP Error] Illegal string offset 'filename' [line 149 of /usr/share/pear/phing/tasks/ext/ExtractBaseTask.php]
 * [https://www.phing.info/trac/ticket/1015] phing does not allow phpunit to echo
 * [https://www.phing.info/trac/ticket/1011] Problem with spaces in output redirection path
 * [https://www.phing.info/trac/ticket/1004] <gitcommit .../> does not work because task definition is missing in defaults.properties + another bug
 * [https://www.phing.info/trac/ticket/1003] 2 php syntax bugs in GitCommitTask
 * [https://www.phing.info/trac/ticket/1000] Make phing.phar work out of the box
 * [https://www.phing.info/trac/ticket/999]  phing-2.5.0.phar Can't load default task list
 * [https://www.phing.info/trac/ticket/993]  passthru will redirect stderr
 * [https://www.phing.info/trac/ticket/990]  Prompting for a property value when it is not set results in a repeated input message
 * [https://www.phing.info/trac/ticket/985]  Git Commit Task missing from docs
 * [https://www.phing.info/trac/ticket/981]  FileUtil::copyFile(): $preserveLastModified causes empty symlink target file
 * [https://www.phing.info/trac/ticket/970]  FileSyncTask missing from docbook5 documentation
 * [https://www.phing.info/trac/ticket/966]  phing unit tests nice on all platforms
 * [https://www.phing.info/trac/ticket/920]  Load phpdepend dependency only when they are used
 * [https://www.phing.info/trac/ticket/906]  Move to docbook5 documentation
 * [https://www.phing.info/trac/ticket/438]  pdosqlexec: add delimiterType=none (default), clarify delimiter documentation (was: pdosqlexec triggers segmentation fault)

Feb. 16, 2013 - Phing 2.5.0
---------------------------

This release addresses the following issues:

 * [https://www.phing.info/trac/ticket/979] svncommit: invalid switch ignoreexternals
 * [https://www.phing.info/trac/ticket/977] phpunit Task doesn't support @codeCoverageIgnore[...] comments
 * [https://www.phing.info/trac/ticket/972] SvnCopyTask: remove "force" from documentation
 * [https://www.phing.info/trac/ticket/971] TokenSource does not work
 * [https://www.phing.info/trac/ticket/969] PHPUnit task does not report diffs for failed assertions
 * [https://www.phing.info/trac/ticket/968] Proper handling of STDOUT and STDERR
 * [https://www.phing.info/trac/ticket/963] XSLT task fails with fatal error on PHP 5.4
 * [https://www.phing.info/trac/ticket/962] DbDeploy: infinite loop in case if directory not found
 * [https://www.phing.info/trac/ticket/961] DbDeploy: checkall output isn't informative
 * [https://www.phing.info/trac/ticket/960] Documentation of Dbdeploy task
 * [https://www.phing.info/trac/ticket/959] Bug in SvnListTask Version 2.4.14
 * [https://www.phing.info/trac/ticket/958] Property wrapped in if/then structure is not substituted by it's value
 * [https://www.phing.info/trac/ticket/954] Paths becoming part of S3 file names on Windows
 * [https://www.phing.info/trac/ticket/953] Add PHP extension check to Available Task
 * [https://www.phing.info/trac/ticket/952] Properly document how to load environment variables as properties
 * [https://www.phing.info/trac/ticket/951] S3Put throws "Source is not set" exception
 * [https://www.phing.info/trac/ticket/949] SymfonyConsoleTask improvements: checkreturn and output of command
 * [https://www.phing.info/trac/ticket/947] AvailableTask does not work on unix domain sockets
 * [https://www.phing.info/trac/ticket/946] <target hidden="true> is undocumented
 * [https://www.phing.info/trac/ticket/941] ZendGuardEncode under Windows 7
 * [https://www.phing.info/trac/ticket/937] DbDeployTask applied_by username is hardcoded and cannot be changed
 * [https://www.phing.info/trac/ticket/935] phpcodesniffertask does not work on CSS and JS files
 * [https://www.phing.info/trac/ticket/932] SshTask Methods Options
 * [https://www.phing.info/trac/ticket/921] JSL Lint Task - Halt on warning
 * [https://www.phing.info/trac/ticket/910] Add preservepermissions flag to copy task
 * [https://www.phing.info/trac/ticket/898] Add ApplyTask
 * [https://www.phing.info/trac/ticket/838] -D option doesn't work with a space after it
 * [https://www.phing.info/trac/ticket/599] Phar package does not work on Windows platforms

Nov. 29, 2012 - Phing 2.4.14
----------------------------

This release addresses the following issues:

  * [https://www.phing.info/trac/ticket/944] phing/phingdocs bad md5sum
  * [https://www.phing.info/trac/ticket/943] If task with "equals" directly in "project" tag does not work
  * [https://www.phing.info/trac/ticket/942] Typo in tasks/ext/dbdeploy/DbmsSyntaxOracle.php
  * [https://www.phing.info/trac/ticket/939] Add username/password to svn info/lastrevision/list/log task docs
  * [https://www.phing.info/trac/ticket/938] XSLT filter fails when libxslt security present in php

Starting from this version, Phing releases and release numbers will follow
the Semantic Versioning (www.semver.org) principle.

Nov. 20, 2012 - Phing 2.4.13
----------------------------

This release updates the composer package, adds a phploc task and improved
support for phpDocumentor 2 and IonCube 7, improves the unit tests,
clarifies the documentation in a number of places, and addresses
the following issues:

  * [https://www.phing.info/trac/ticket/933] PHPLoc 1.7 broken
  * [https://www.phing.info/trac/ticket/931] PHP_CodeSniffer throws errors with CodeSniffer 1.4.0
  * [https://www.phing.info/trac/ticket/929] Can not pass empty string (enclosed in double quotes) as exec task argument
  * [https://www.phing.info/trac/ticket/928] Fatal error with ZipTask when zip extension is not loaded
  * [https://www.phing.info/trac/ticket/927] PHPCPD upgrade breaks PHPCPD task
  * [https://www.phing.info/trac/ticket/926] FtpDeployTask: Missing features and patch for them (chmod and only change if different)
  * [https://www.phing.info/trac/ticket/925] Problem with spaces in error redirection path.
  * [https://www.phing.info/trac/ticket/924] Update to PEAR::VersionControl_SVN 0.5.0
  * [https://www.phing.info/trac/ticket/922] Introduce build file property that contains the build file's directory
  * [https://www.phing.info/trac/ticket/915] path with special characters does not delete
  * [https://www.phing.info/trac/ticket/909] Replace __DIR__
  * [https://www.phing.info/trac/ticket/905] Add filterchain support to the property task
  * [https://www.phing.info/trac/ticket/904] TarTask should raise error if zlib extension not installed
  * [https://www.phing.info/trac/ticket/903] Cannot redeclare class phpDocumentor\Bootstrap
  * [https://www.phing.info/trac/ticket/902] SvnBaseTask and subversion 1.7
  * [https://www.phing.info/trac/ticket/901] phpunitreport create html's classes files in wrong folder
  * [https://www.phing.info/trac/ticket/900] phpdoc2 example has error
  * [https://www.phing.info/trac/ticket/895] error in includepath when calling more than once
  * [https://www.phing.info/trac/ticket/893] Phing will run bootstrap before first task but clean up autoloader before second task
  * [https://www.phing.info/trac/ticket/892] Concatenate property lines ending with backslash
  * [https://www.phing.info/trac/ticket/891] Symfony console task: space within the arguments, not working on windows
  * [https://www.phing.info/trac/ticket/890] Allow custom child elements
  * [https://www.phing.info/trac/ticket/888] Documentation error for CvsTask setfailonerror
  * [https://www.phing.info/trac/ticket/886] Error throwing in PDOSQLExecTask breaking trycatch
  * [https://www.phing.info/trac/ticket/884] svnlist fails on empty directories
  * [https://www.phing.info/trac/ticket/882] Dbdeploy does not retrieve changelog number with oracle
  * [https://www.phing.info/trac/ticket/881] Silent fail on delete tasks
  * [https://www.phing.info/trac/ticket/880] Add phploc task
  * [https://www.phing.info/trac/ticket/867] phpcpd task should check external dep in main()
  * [https://www.phing.info/trac/ticket/866] Code coverage not showing "not executed" lines
  * [https://www.phing.info/trac/ticket/863] MoveTask ignores fileset
  * [https://www.phing.info/trac/ticket/845] GrowlNotifyTask to be notified on long-task when they are finished
  * [https://www.phing.info/trac/ticket/813] Allow custom conditions
  * [https://www.phing.info/trac/ticket/751] Allow loading of phpunit.xml in phpunit task
  * [https://www.phing.info/trac/ticket/208] ReplaceRegexp problem with newline as replace string

Apr. 6, 2012 - Phing 2.4.12
---------------------------

  * [https://www.phing.info/trac/ticket/877] Add 'level' attribute to resolvepath task
  * [https://www.phing.info/trac/ticket/876] JslLint Task is_executable() broken
  * [https://www.phing.info/trac/ticket/874] ParallelTask.php is not PHP 5.2 compatible
  * [https://www.phing.info/trac/ticket/860] SvnBaseTask: getRecursive
  * [https://www.phing.info/trac/ticket/539] Custom build log mailer
  * [https://www.phing.info/trac/ticket/406] an ability to turn phpLint verbose ON and OFF

Apr. 4, 2012 - Phing 2.4.11
---------------------------

  * [https://www.phing.info/trac/ticket/870] Can't find ParallelTask.php

Apr. 3, 2012 - Phing 2.4.10
---------------------------

  * [https://www.phing.info/trac/ticket/872] ReplaceTokens can't work with '/' char
  * [https://www.phing.info/trac/ticket/870] Can't find ParallelTask.php
  * [https://www.phing.info/trac/ticket/868] Git Clone clones into wrong directory
  * [https://www.phing.info/trac/ticket/865] static call to a non-static function PhingFile.php::getTempdir()
  * [https://www.phing.info/trac/ticket/854] PropertyTask with file. Can't use a comment delimiter in the value.
  * [https://www.phing.info/trac/ticket/853] PHP Error with HttpGetTask
  * [https://www.phing.info/trac/ticket/852] Several minor errors in documentation of core tasks
  * [https://www.phing.info/trac/ticket/851] RNG grammar hasn't been updated to current version
  * [https://www.phing.info/trac/ticket/850] Typo in documentation - required attributes for project
  * [https://www.phing.info/trac/ticket/849] Symfony 2 Console Task
  * [https://www.phing.info/trac/ticket/847] Add support for RNG grammar in task XmlLint
  * [https://www.phing.info/trac/ticket/846] RNG grammar is wrong for task 'foreach'
  * [https://www.phing.info/trac/ticket/844] symlink task - overwrite not working
  * [https://www.phing.info/trac/ticket/843] "verbose" option should print fileset/filelist filenames before execution, not afterwards
  * [https://www.phing.info/trac/ticket/840] Prevent weird bugs: raise warning when a target tag contains no ending tag
  * [https://www.phing.info/trac/ticket/835] JSL-Check faulty
  * [https://www.phing.info/trac/ticket/834] ExecTask documentation has incorrect escape attribute default value
  * [https://www.phing.info/trac/ticket/833] Exec task args with special characters cannot be escaped
  * [https://www.phing.info/trac/ticket/828] SelectorUtils::matchPath matches **/._* matches dir/file._name
  * [https://www.phing.info/trac/ticket/820] Type selector should treat symlinks to directories as such
  * [https://www.phing.info/trac/ticket/790] Make it easy to add new inherited types to phing: Use addFileset instead of createFileset
  * [https://www.phing.info/trac/ticket/772] Support for filelist in UpToDateTask
  * [https://www.phing.info/trac/ticket/671] fix CvsTask documentation
  * [https://www.phing.info/trac/ticket/587] More detailed backtrace in debug mode (patch)
  * [https://www.phing.info/trac/ticket/519] Extend mail task to include attachments
  * [https://www.phing.info/trac/ticket/419] schema file for editors and validation
  * [https://www.phing.info/trac/ticket/334] Run a task on BuildException

Dec. 29, 2011 - Phing 2.4.9
---------------------------

  * [https://www.phing.info/trac/ticket/837] PHPMDTask should check external dep in main()
  * [https://www.phing.info/trac/ticket/836] DocBlox task breaks with version 0.17.0: function getThemesPath not found
  * [https://www.phing.info/trac/ticket/831] dbdeploy undo script SQL is not formatted correctly
  * [https://www.phing.info/trac/ticket/822] rSTTask: add debug statement when creating target directory
  * [https://www.phing.info/trac/ticket/821] phingcall using a lot of memory
  * [https://www.phing.info/trac/ticket/819] Documentation for SvnUpdateTask is outdated
  * [https://www.phing.info/trac/ticket/818] [patch] Add overwrite option to Symlink task
  * [https://www.phing.info/trac/ticket/817] Adding the "trust-server-cert" option to SVN tasks
  * [https://www.phing.info/trac/ticket/816] Fix notice in SimpleTestXmlResultFormatter
  * [https://www.phing.info/trac/ticket/811] phpunitreport path fails on linux
  * [https://www.phing.info/trac/ticket/810] AvailableTask resolving symbolic links
  * [https://www.phing.info/trac/ticket/807] SVN tasks do not always show error message
  * [https://www.phing.info/trac/ticket/795] Untar : allow overwriting of newer files when extracting
  * [https://www.phing.info/trac/ticket/782] PharTask is very slow for big project
  * [https://www.phing.info/trac/ticket/776] Add waitFor task
  * [https://www.phing.info/trac/ticket/736] Incompatibility when copying from Windows to Linux on ScpTask
  * [https://www.phing.info/trac/ticket/709] talk about invalid property values
  * [https://www.phing.info/trac/ticket/697] More descriptive error messages in PharPackageTask
  * [https://www.phing.info/trac/ticket/674] Properties: global or local in tasks?
  * [https://www.phing.info/trac/ticket/653] Allow ChownTask to change only group
  * [https://www.phing.info/trac/ticket/619] verbose level in ExpandPropertiesFilter

Nov. 2, 2011 - Phing 2.4.8
--------------------------

  * [https://www.phing.info/trac/ticket/814] Class 'PHPCPD_Log_XML' not found in /home/m/www/elvis/vendor/phpcpd/PHPCPD/Log/XML/PMD.php on line 55
  * [https://www.phing.info/trac/ticket/812] Fix PHPUnit 3.6 / PHP_CodeCoverage 1.1.0 compatibility
  * [https://www.phing.info/trac/ticket/808] Bad example for the <or> selector
  * [https://www.phing.info/trac/ticket/805] phing executable has bug in ENV/PHP_COMMAND
  * [https://www.phing.info/trac/ticket/804] PhpUnitTask overwrites autoload stack
  * [https://www.phing.info/trac/ticket/801] PhpCodeSnifferTask doesn't pass files encoding to PHP_CodeSniffer
  * [https://www.phing.info/trac/ticket/800] CoverageReportTask fails with "runtime error" on PHP 5.4.0beta1
  * [https://www.phing.info/trac/ticket/799] DbDeploy does not support pdo-dblib
  * [https://www.phing.info/trac/ticket/798] ReplaceTokensWithFile - postfix attribute ignored
  * [https://www.phing.info/trac/ticket/797] PhpLintTask performance improvement
  * [https://www.phing.info/trac/ticket/794] Fix rSTTask to avoid the need of PEAR every time
  * [https://www.phing.info/trac/ticket/793] Corrected spelling of name
  * [https://www.phing.info/trac/ticket/792] EchoTask: Fileset support
  * [https://www.phing.info/trac/ticket/789] rSTTask unittests fix
  * [https://www.phing.info/trac/ticket/788] rSTTask documentation: fix examples
  * [https://www.phing.info/trac/ticket/787] Add pearPackageFileSet type
  * [https://www.phing.info/trac/ticket/785] method execute doesn't exists in CvsTask.php
  * [https://www.phing.info/trac/ticket/784] Refactor DocBlox task to work with DocBlox 0.14+
  * [https://www.phing.info/trac/ticket/783] SvnExportTask impossible to export current version from working copy
  * [https://www.phing.info/trac/ticket/779] phplint task error summary doesn't display the errors
  * [https://www.phing.info/trac/ticket/775] ScpTask: mis-leading error message if 'host' attribute is not set
  * [https://www.phing.info/trac/ticket/772] Support for filelist in UpToDateTask
  * [https://www.phing.info/trac/ticket/770] Keep the RelaxNG grammar in sync with the code/doc
  * [https://www.phing.info/trac/ticket/707] Writing Tasks/class properties: taskname not correctly used
  * [https://www.phing.info/trac/ticket/655] PlainPHPUnitResultFormatter does not display errors if @dataProvider was used
  * [https://www.phing.info/trac/ticket/578] [PATCH] Add mapper support to ForeachTask
  * [https://www.phing.info/trac/ticket/552] 2 validargs to input task does not display defaults correctly

Aug. 19, 2011 - Phing 2.4.7.1
-----------------------------

This is a hotfix release.

  * [https://www.phing.info/trac/ticket/774] Fix PHP 5.3 dependency in CoverageReportTask
  * [https://www.phing.info/trac/ticket/773] Fix for Ticket #744 breaks PHPCodeSnifferTask's nested formatters

Aug. 18, 2011 - Phing 2.4.7
---------------------------

This release fixes and improves several tasks (particularly the DocBlox
task), adds OCI/ODBC support to the dbdeploy task and introduces
a task to render reStructuredText.

  * [https://www.phing.info/trac/ticket/771] Undefined offset: 1 [line 204 of /usr/share/php/phing/tasks/ext/JslLintTask.php]
  * [https://www.phing.info/trac/ticket/767] PharPackageTask: metadata should not be required
  * [https://www.phing.info/trac/ticket/766] The DocBlox task does not load the markdown library.
  * [https://www.phing.info/trac/ticket/765] CoverageReportTask incorrectly considers dead code to be unexecuted
  * [https://www.phing.info/trac/ticket/762] Gratuitous unit test failures on Windows
  * [https://www.phing.info/trac/ticket/760] SelectorUtils::matchPath() directory matching broken
  * [https://www.phing.info/trac/ticket/759] DocBloxTask throws an error when using DocBlox 0.12.2
  * [https://www.phing.info/trac/ticket/757] Grammar error in ChmodTask documentation
  * [https://www.phing.info/trac/ticket/755] PharPackageTask Web/Cli stub path is incorrect
  * [https://www.phing.info/trac/ticket/754] ExecTask: <arg> support
  * [https://www.phing.info/trac/ticket/753] ExecTask: Unit tests and refactoring
  * [https://www.phing.info/trac/ticket/752] Declaration of Win32FileSystem::compare()
  * [https://www.phing.info/trac/ticket/750] Enable process isolation support in the PHPUnit task
  * [https://www.phing.info/trac/ticket/747] Improve "can't load default task list" message
  * [https://www.phing.info/trac/ticket/745] MkdirTask mode param mistake
  * [https://www.phing.info/trac/ticket/744] PHP_CodeSniffer formatter doesn't work with summary
  * [https://www.phing.info/trac/ticket/742] ExecTask docs: link os.name in os attribute
  * [https://www.phing.info/trac/ticket/741] ExecTask: missing docs for "output", "error" and "level"
  * [https://www.phing.info/trac/ticket/740] PHPMDTask: "InvalidArgumentException" with no globbed files.
  * [https://www.phing.info/trac/ticket/739] Making the jsMin suffix optional
  * [https://www.phing.info/trac/ticket/737] PHPCPDTask: omitting 'outfile' attribute with 'useFIle="false"'
  * [https://www.phing.info/trac/ticket/735] CopyTask can't copy broken symlinks when included in fileset
  * [https://www.phing.info/trac/ticket/733] DeleteTask cannot delete dangling symlinks
  * [https://www.phing.info/trac/ticket/731] Implement filepath support in Available Task
  * [https://www.phing.info/trac/ticket/720] rSTTask to render reStructuredText
  * [https://www.phing.info/trac/ticket/658] Add support to Oracle (OCI) in DbDeployTask
  * [https://www.phing.info/trac/ticket/580] ODBC in DbDeployTask
  * [https://www.phing.info/trac/ticket/553] copy task bails on symbolic links (filemtime)
  * [https://www.phing.info/trac/ticket/499] PDO cannot handle PL/Perl function creation statements in PostgreSQL

Jul. 12, 2011 - Phing 2.4.6
---------------------------

This release fixes a large number of issues, improves a number of tasks
and adds several new tasks (SVN log/list, DocBlox and LoadFile). 

  * [https://www.phing.info/trac/ticket/732] execTask fails to chdir if the chdir parameter is a symlink to a dir
  * [https://www.phing.info/trac/ticket/730] phpunitreport: styledir not required
  * [https://www.phing.info/trac/ticket/729] CopyTask fails when todir="" does not exist
  * [https://www.phing.info/trac/ticket/725] Clarify documentation for using AvailableTask as a condition
  * [https://www.phing.info/trac/ticket/723] setIni() fails with memory_limit not set in Megabytes
  * [https://www.phing.info/trac/ticket/719] TouchTask: file not required?
  * [https://www.phing.info/trac/ticket/718] mkdir: are parent directories created?
  * [https://www.phing.info/trac/ticket/715] Fix for mail task documentation
  * [https://www.phing.info/trac/ticket/712] expectSpecificBuildException fails to detect wrong exception message
  * [https://www.phing.info/trac/ticket/708] typo in docs: "No you can set"
  * [https://www.phing.info/trac/ticket/706] Advanced task example missing
  * [https://www.phing.info/trac/ticket/705] Missing links in Writing Tasks: Summary
  * [https://www.phing.info/trac/ticket/704] Case problem in "Writing Tasks" with setMessage
  * [https://www.phing.info/trac/ticket/703] missing links in "Package Imports"
  * [https://www.phing.info/trac/ticket/701] Setting more then two properties in command line not possible on windows
  * [https://www.phing.info/trac/ticket/699] Add loadfile task
  * [https://www.phing.info/trac/ticket/698] Add documentation for patternset element to user guide
  * [https://www.phing.info/trac/ticket/696] CoverageReportTask doesn't recognize UTF-8 source code
  * [https://www.phing.info/trac/ticket/695] phpunit Task doesn't support @codeCoverageIgnore[...] comments
  * [https://www.phing.info/trac/ticket/692] Class 'GroupTest' not found in /usr/share/php/phing/tasks/ext/simpletest/SimpleTestTask.php on line 158
  * [https://www.phing.info/trac/ticket/691] foreach doesn't work with filelists
  * [https://www.phing.info/trac/ticket/690] Support DocBlox
  * [https://www.phing.info/trac/ticket/689] Improve documentation about selectors
  * [https://www.phing.info/trac/ticket/688] SshTask Adding (+propertysetter, +displaysetter)
  * [https://www.phing.info/trac/ticket/685] SvnLogTask and SvnListTask
  * [https://www.phing.info/trac/ticket/682] Loading custom tasks should use the autoloading mechanism
  * [https://www.phing.info/trac/ticket/681] phpunit report does not work with a single testcase
  * [https://www.phing.info/trac/ticket/680] phpunitreport: make tables sortable
  * [https://www.phing.info/trac/ticket/679] IoncubeEncoderTask improved
  * [https://www.phing.info/trac/ticket/673] new listener HtmlColorLogger
  * [https://www.phing.info/trac/ticket/672] DbDeployTask::getDeltasFilesArray has undefined variable
  * [https://www.phing.info/trac/ticket/671] fix CvsTask documentation
  * [https://www.phing.info/trac/ticket/670] DirectoryScanner: add darcs to default excludes
  * [https://www.phing.info/trac/ticket/668] Empty Default Value Behaves Like the Value is not set
  * [https://www.phing.info/trac/ticket/667] Document how symbolic links and hidden files are treated in copy task
  * [https://www.phing.info/trac/ticket/663] __toString for register slots
  * [https://www.phing.info/trac/ticket/662] Hiding the command that is executed with "ExecTask"
  * [https://www.phing.info/trac/ticket/659] optionally skip version check in codesniffer task
  * [https://www.phing.info/trac/ticket/654] fileset not selecting folders
  * [https://www.phing.info/trac/ticket/652] PDOSQLExec task doesn't close the DB connection before throw an exception or at the end of the task.
  * [https://www.phing.info/trac/ticket/642] ERROR: option "-o" not known with phpcs version 1.3.0RC2 and phing/phpcodesniffer 2.4.4
  * [https://www.phing.info/trac/ticket/639] Add verbose mode for SCPTask
  * [https://www.phing.info/trac/ticket/635] ignored autocommit="false" in PDOTask?
  * [https://www.phing.info/trac/ticket/632] CoverageThresholdTask needs exclusion option/attribute
  * [https://www.phing.info/trac/ticket/626] Coverage threshold message is too detailed...
  * [https://www.phing.info/trac/ticket/616] PhpDocumentor prematurely checks for executable
  * [https://www.phing.info/trac/ticket/613] Would be nice to have -properties=<file> CLI option
  * [https://www.phing.info/trac/ticket/611] Attribute "title" is wanted in CoverageReportTask
  * [https://www.phing.info/trac/ticket/608] Tweak test failure message from PHPUnitTask
  * [https://www.phing.info/trac/ticket/591] PhpLintTask don't log all errors for each file
  * [https://www.phing.info/trac/ticket/563] Make PatchTask silent on FreeBSD
  * [https://www.phing.info/trac/ticket/546] Support of filelist in CodeCoverageTask
  * [https://www.phing.info/trac/ticket/527] pearpkg2: unable to specify different file roles
  * [https://www.phing.info/trac/ticket/521] jslint warning logger

Mar. 3, 2011 - Phing 2.4.5
--------------------------

This release fixes several issues, and reverts the changes
that introduced the ComponentHelper class.

  * [https://www.phing.info/trac/ticket/657] Wrong example of creating task in stable documentation.
  * [https://www.phing.info/trac/ticket/656] Many erratas on the "Getting Started"-page.
  * [https://www.phing.info/trac/ticket/651] Messages of ReplaceTokens should be verbose
  * [https://www.phing.info/trac/ticket/641] 2.4.4 packages contains .rej and .orig files in release tarball
  * [https://www.phing.info/trac/ticket/640] "phing -q" does not work: "Unknown argument: -q"
  * [https://www.phing.info/trac/ticket/634] php print() statement outputting to stdout
  * [https://www.phing.info/trac/ticket/624] PDOSQLExec fails with Fatal error: Class 'LogWriter' not found in [...]/PDOSQLExecFormatterElement
  * [https://www.phing.info/trac/ticket/623] 2.4.5RC1 requires PHPUnit erroneously
  * [https://www.phing.info/trac/ticket/621] PhpLintTask outputs all messages (info and errors) to same loglevel
  * [https://www.phing.info/trac/ticket/614] phpcodesniffer task changes Phing build working directory
  * [https://www.phing.info/trac/ticket/610] BUG: AdhocTaskdefTask fails when creating a task that extends from an existing task
  * [https://www.phing.info/trac/ticket/607] v 2.4.4 broke taskdef for tasks following PEAR naming standard
  * [https://www.phing.info/trac/ticket/603] Add support to PostgreSQL in DbDeployTask
  * [https://www.phing.info/trac/ticket/601] Add HTTP_Request2 to optional dependencies
  * [https://www.phing.info/trac/ticket/600] typo in ReplaceRegexpTask
  * [https://www.phing.info/trac/ticket/598] Wrong version for optional Services_Amazon_S3 dependency
  * [https://www.phing.info/trac/ticket/596] PhpDependTask no more compatible with PDepend since 0.10RC1
  * [https://www.phing.info/trac/ticket/593] Ssh/scp task: Move ssh2_connect checking from init to main
  * [https://www.phing.info/trac/ticket/564] command line "-D" switch not handled correctly under windows
  * [https://www.phing.info/trac/ticket/544] Wrong file set when exclude test/**/** is used

Dec. 2, 2010 - Phing 2.4.4
--------------------------

This release fixes several issues.

  * [https://www.phing.info/trac/ticket/595] FilterChain without ReplaceTokensWithFile creator
  * [https://www.phing.info/trac/ticket/594] Taskdef in phing 2.4.3 was broken!
  * [https://www.phing.info/trac/ticket/590] PhpLintTask don't flag files that can't be parsed as bad files
  * [https://www.phing.info/trac/ticket/589] Mail Task don't show recipients list on log
  * [https://www.phing.info/trac/ticket/588] Add (optional) dependency to VersionControl_Git and Services_Amazon_S3 packages
  * [https://www.phing.info/trac/ticket/585] Same line comments in property files are included in the property value
  * [https://www.phing.info/trac/ticket/570] XmlLintTask - check well-formedness only
  * [https://www.phing.info/trac/ticket/568] Boolean properties get incorrectly expanded
  * [https://www.phing.info/trac/ticket/544] Wrong file set when exclude test/**/** is used
  * [https://www.phing.info/trac/ticket/536] DbDeployTask: Undo script wrongly generated

Nov. 12, 2010 - Phing 2.4.3
---------------------------

This release adds tasks to interface with Git and Amazon S3, adds support for PHPUnit 3.5,
and fixes numerous issues.

  * [https://www.phing.info/trac/ticket/583] UnixFileSystem::compare() is broken
  * [https://www.phing.info/trac/ticket/582] Add haltonerror attribute to copy/move tasks
  * [https://www.phing.info/trac/ticket/581] XmlProperty creating wrong properties
  * [https://www.phing.info/trac/ticket/577] SVN commands fail on Windows XP
  * [https://www.phing.info/trac/ticket/575] xmlproperty - misplaced xml attributes
  * [https://www.phing.info/trac/ticket/574] Task "phpcodesniffer" broken, no output
  * [https://www.phing.info/trac/ticket/572] ImportTask don't skipp file if optional is set to true
  * [https://www.phing.info/trac/ticket/560] [PATCH] Compatibility with PHPUnit 3.5.
  * [https://www.phing.info/trac/ticket/559] UpToDate not override value of property when target is called by phingcall
  * [https://www.phing.info/trac/ticket/555] STRICT Declaration of UnixFileSystem::getBooleanAttributes() should be compatible with that of FileSystem::getBooleanAttributes()
  * [https://www.phing.info/trac/ticket/554] Patch to force PhpDocumentor to log using phing
  * [https://www.phing.info/trac/ticket/551] SVN Switch Task
  * [https://www.phing.info/trac/ticket/550] Ability to convert encoding of files
  * [https://www.phing.info/trac/ticket/549] ScpTask doesn't finish the transfer properly
  * [https://www.phing.info/trac/ticket/547] The new attribute version does not work
  * [https://www.phing.info/trac/ticket/543] d51PearPkg2Task: Docs link wrong
  * [https://www.phing.info/trac/ticket/542] JslLintTask: wrap conf parameter with escapeshellarg
  * [https://www.phing.info/trac/ticket/537] Install documentation incorrect/incomplete
  * [https://www.phing.info/trac/ticket/536] DbDeployTask: Undo script wrongly generated
  * [https://www.phing.info/trac/ticket/534] Task for downloading a file through HTTP
  * [https://www.phing.info/trac/ticket/531] cachefile parameter of PhpLintTask also caches erroneous files
  * [https://www.phing.info/trac/ticket/530] XmlLintTask does not stop buid process when schema validation fails
  * [https://www.phing.info/trac/ticket/529] d51pearpkg2: setOptions() call does not check return value
  * [https://www.phing.info/trac/ticket/526] pearpkg2: extdeps and replacements mappings not documented
  * [https://www.phing.info/trac/ticket/525] pearpkg2: minimal version on dependency automatically set max and recommended
  * [https://www.phing.info/trac/ticket/524] pearpkg2: maintainers mapping does not support "active" tag
  * [https://www.phing.info/trac/ticket/520] Need SvnLastChangedRevisionTask to grab the last changed revision for the current working directory
  * [https://www.phing.info/trac/ticket/518] [PHP Error] file_put_contents(): Filename cannot be empty in phpcpdesniffer task
  * [https://www.phing.info/trac/ticket/513] Version tag doesn't increment bugfix portion of the version
  * [https://www.phing.info/trac/ticket/511] Properties not being set on subsequent sets.
  * [https://www.phing.info/trac/ticket/510] to show test name when testing fails
  * [https://www.phing.info/trac/ticket/501] formatter type "clover" of task "phpunit" doesn't generate coverage according to task "coverage-setup"
  * [https://www.phing.info/trac/ticket/488] FtpDeployTask is very silent, error messages are not clear
  * [https://www.phing.info/trac/ticket/455] Should be able to ignore a task when listing them from CLI
  * [https://www.phing.info/trac/ticket/369] Add Git Support

Jul. 28, 2010 - Phing 2.4.2
---------------------------

  * [https://www.phing.info/trac/ticket/509] Phing.php setIni() does not honor -1 as unlimited
  * [https://www.phing.info/trac/ticket/506] Patch to allow -D<option> with no "=<value>"
  * [https://www.phing.info/trac/ticket/503] PHP Documentor Task not correctly documented
  * [https://www.phing.info/trac/ticket/502] Add repository url support to SvnLastRevisionTask
  * [https://www.phing.info/trac/ticket/500] static function call in PHPCPDTask
  * [https://www.phing.info/trac/ticket/498] References to Core types page are broken
  * [https://www.phing.info/trac/ticket/496] __autoload not being called
  * [https://www.phing.info/trac/ticket/492] Add executable attribute in JslLint task
  * [https://www.phing.info/trac/ticket/489] PearPackage Task fatal error trying to process Fileset options
  * [https://www.phing.info/trac/ticket/487] Allow files in subdirectories in ReplaceTokensWithFile filter
  * [https://www.phing.info/trac/ticket/486] PHP Errors in PDOSQLExecTask
  * [https://www.phing.info/trac/ticket/485] ReplaceTokensWithFile filter does not allow HTML translation to be switched off
  * [https://www.phing.info/trac/ticket/484] Make handling of incomplete tests when logging XML configurable
  * [https://www.phing.info/trac/ticket/483] Bug in FileUtils::copyFile() on Linux - when using FilterChains, doesn't preserve attributes
  * [https://www.phing.info/trac/ticket/482] Bug in ChownTask with verbose set to false
  * [https://www.phing.info/trac/ticket/480] ExportPropertiesTask does not export all the initialized properties
  * [https://www.phing.info/trac/ticket/477] HttpRequestTask should NOT validate output if regex is not provided
  * [https://www.phing.info/trac/ticket/474] Bad Comparisons in FilenameSelector (possibly others)
  * [https://www.phing.info/trac/ticket/473] CPanel can't read Phing's Zip Files
  * [https://www.phing.info/trac/ticket/472] Add a multiline option to regex replace filter
  * [https://www.phing.info/trac/ticket/471] ChownTask throws exception if group is given
  * [https://www.phing.info/trac/ticket/468] CopyTask does not accept a FileList as only source of files
  * [https://www.phing.info/trac/ticket/467] coverage of abstract class/method is always ZERO
  * [https://www.phing.info/trac/ticket/466] incomplete logging in coverage-threshold
  * [https://www.phing.info/trac/ticket/465] PatchTask should support more options
  * [https://www.phing.info/trac/ticket/463] Broken Links in coverage report
  * [https://www.phing.info/trac/ticket/461] version tag in project node

Mar. 10, 2010 - Phing 2.4.1
---------------------------

  * [https://www.phing.info/trac/ticket/460] FtpDeployTask error
  * [https://www.phing.info/trac/ticket/458] PHPCodeSniffer Task throws Exceptions
  * [https://www.phing.info/trac/ticket/456] Fileset's dir should honor expandsymboliclinks
  * [https://www.phing.info/trac/ticket/449] ZipTask creates ZIP file but doesn't set file/dir attributes
  * [https://www.phing.info/trac/ticket/448] PatchTask
  * [https://www.phing.info/trac/ticket/447] SVNCopy task is not documented
  * [https://www.phing.info/trac/ticket/446] Add documentation describing phpdocext
  * [https://www.phing.info/trac/ticket/444] PhpCodeSnifferTask fails to generate a checkstyle-like output
  * [https://www.phing.info/trac/ticket/443] HttpRequestTask is very desirable
  * [https://www.phing.info/trac/ticket/442] public key support for scp and ssh tasks
  * [https://www.phing.info/trac/ticket/436] Windows phing.bat can't handle PHP paths with spaces
  * [https://www.phing.info/trac/ticket/435] Phing download link broken in bibliography
  * [https://www.phing.info/trac/ticket/433] Error in Documentation in Book under Writing a simple Buildfile
  * [https://www.phing.info/trac/ticket/432] would be nice to create CoverateThresholdTask
  * [https://www.phing.info/trac/ticket/431] integrate Phing with PHP Mess Detector and PHP_Depend
  * [https://www.phing.info/trac/ticket/430] FtpDeployTask is extremely un-verbose...
  * [https://www.phing.info/trac/ticket/428] Ability to specify the default build listener in build file
  * [https://www.phing.info/trac/ticket/426] SvnExport task documentation does not mention "revision" property
  * [https://www.phing.info/trac/ticket/421] ExportProperties class incorrectly named
  * [https://www.phing.info/trac/ticket/420] Typo in setExcludeGroups function of PHPUnitTask
  * [https://www.phing.info/trac/ticket/418] Minor improvement for PhpLintTask

Jan. 17, 2010 - Phing 2.4.0
---------------------------

  * [https://www.phing.info/trac/ticket/414] PhpLintTask: retrieving bad files
  * [https://www.phing.info/trac/ticket/413] PDOSQLExecTask does not recognize "delimiter" command
  * [https://www.phing.info/trac/ticket/411] PhpEvalTask calculation should not always returns anything
  * [https://www.phing.info/trac/ticket/410] Allow setting alias for Phar files as well as a custom stub
  * [https://www.phing.info/trac/ticket/384] Delete directories fails on '[0]' name

Dec. 17, 2009 - Phing 2.4.0 RC3
-------------------------------

  * [https://www.phing.info/trac/ticket/407] some error with svn info
  * [https://www.phing.info/trac/ticket/406] an ability to turn phpLint verbose ON and OFF
  * [https://www.phing.info/trac/ticket/405] I can't get a new version of Phing through PEAR
  * [https://www.phing.info/trac/ticket/402] Add fileset/filelist support to scp tasks
  * [https://www.phing.info/trac/ticket/401] PHPUnitTask 'summary' formatter produces a long list of results
  * [https://www.phing.info/trac/ticket/400] Support for Clover coverage XML
  * [https://www.phing.info/trac/ticket/399] PhpDocumentorExternal stops in method constructArguments
  * [https://www.phing.info/trac/ticket/398] Error using ResolvePath on Windows
  * [https://www.phing.info/trac/ticket/397] DbDeployTask only looks for -- //@UNDO (requires space)
  * [https://www.phing.info/trac/ticket/396] PDOSQLExecTask requires both fileset and filelist, rather than either or
  * [https://www.phing.info/trac/ticket/395] PharPackageTask fails to compress files
  * [https://www.phing.info/trac/ticket/394] Fix differences in zip and tar tasks
  * [https://www.phing.info/trac/ticket/393] prefix parameter for tar task
  * [https://www.phing.info/trac/ticket/391] Docs: PharPackageTask 'compress' attribute wrong
  * [https://www.phing.info/trac/ticket/389] Code coverage shows incorrect results Part2
  * [https://www.phing.info/trac/ticket/388] Beautify directory names in zip archives
  * [https://www.phing.info/trac/ticket/387] IoncubeEncoderTask noshortopentags
  * [https://www.phing.info/trac/ticket/386] PhpCpd output to screen
  * [https://www.phing.info/trac/ticket/385] Directory ignored in PhpCpdTask.php
  * [https://www.phing.info/trac/ticket/382] Add prefix parameter to ZipTask
  * [https://www.phing.info/trac/ticket/381] FtpDeployTask: invalid default transfer mode
  * [https://www.phing.info/trac/ticket/380] How to use PhpDocumentorExternalTask
  * [https://www.phing.info/trac/ticket/379] PHPUnit error handler issue
  * [https://www.phing.info/trac/ticket/378] PHPUnit task bootstrap file included too late
  * [https://www.phing.info/trac/ticket/377] Code coverage shows incorrect results
  * [https://www.phing.info/trac/ticket/376] ReplaceToken boolean problems
  * [https://www.phing.info/trac/ticket/375] error in docs for echo task
  * [https://www.phing.info/trac/ticket/373] grammar errors
  * [https://www.phing.info/trac/ticket/372] Use E_DEPRECATED
  * [https://www.phing.info/trac/ticket/367] Can't build simple build.xml file
  * [https://www.phing.info/trac/ticket/361] Bug in PHPCodeSnifferTask
  * [https://www.phing.info/trac/ticket/360] &amp;&amp; transfers into & in new created task
  * [https://www.phing.info/trac/ticket/309] startdir and 'current directory' not the same when build.xml not in current directory
  * [https://www.phing.info/trac/ticket/268] Patch - xmlproperties Task
  * [https://www.phing.info/trac/ticket/204] Resolve task class names with PEAR/ZEND/etc. naming convention
  * [https://www.phing.info/trac/ticket/137] Excluded files may be included in Zip/Tar tasks

Oct. 20, 2009 - Phing 2.4.0 RC2
-------------------------------

  * [https://www.phing.info/trac/ticket/370] Fatal error: Cannot redeclare class PHPUnit_Framework_TestSuite
  * [https://www.phing.info/trac/ticket/366] Broken link in "Getting Started/More Complex Buildfile"
  * [https://www.phing.info/trac/ticket/365] Phing 2.4rc1 via pear is not usable
  * [https://www.phing.info/trac/ticket/364] 2.4.0-rc1 download links broken
  * [https://www.phing.info/trac/ticket/363] PHPUnit task fails with formatter type 'xml'
  * [https://www.phing.info/trac/ticket/359] 403 for Documentation (User Guide) Phing HEAD
  * [https://www.phing.info/trac/ticket/355] PDOSQLExecTask should accept filelist subelement
  * [https://www.phing.info/trac/ticket/352] Add API documentation

Sep. 14, 2009 - Phing 2.4.0 RC1
-------------------------------

  * [https://www.phing.info/trac/ticket/362] Can't get phpunit code coverage to export as XML
  * [https://www.phing.info/trac/ticket/361] Bug in PHPCodeSnifferTask
  * [https://www.phing.info/trac/ticket/357] SvnLastRevisionTask fails when locale != EN
  * [https://www.phing.info/trac/ticket/356] Documentation for tasks Chmod and Chown
  * [https://www.phing.info/trac/ticket/349] JslLint task fails to escape shell argument
  * [https://www.phing.info/trac/ticket/347] PHPUnit / Coverage tasks do not deal with bootstrap code
  * [https://www.phing.info/trac/ticket/344] Phing ignores public static array named $browsers in Selenium tests
  * [https://www.phing.info/trac/ticket/342] custom-made re-engine in SelectorUtils is awful slow
  * [https://www.phing.info/trac/ticket/339] PHAR signature setting
  * [https://www.phing.info/trac/ticket/336] Use intval to loop through files
  * [https://www.phing.info/trac/ticket/333] XmlLogger doesn't ensure proper ut8 encoding of log messages
  * [https://www.phing.info/trac/ticket/332] Conditions: uptodate does not work
  * [https://www.phing.info/trac/ticket/331] UpToDateTask documentation says that nested FileSet tags are allowed
  * [https://www.phing.info/trac/ticket/330] "DirectoryScanner cannot find a folder/file named ""0"" (zero)"
  * [https://www.phing.info/trac/ticket/326] Add revision to svncheckout and svnupdate
  * [https://www.phing.info/trac/ticket/325] "<filterchain id=""xxx""> and <filterchain refid=""xxx""> don't work"
  * [https://www.phing.info/trac/ticket/322] phpdoc task not parsing and including  RIC files in documentation output
  * [https://www.phing.info/trac/ticket/319] Simpletest sometimes reports an undefined variable
  * [https://www.phing.info/trac/ticket/317] PhpCodeSnifferTask lacks of haltonerror and haltonwarning attributes
  * [https://www.phing.info/trac/ticket/316] Make haltonfailure attribute for ZendCodeAnalyzerTask
  * [https://www.phing.info/trac/ticket/312] SimpleTestXMLResultFormatter
  * [https://www.phing.info/trac/ticket/311] Fileset support for the TouchTask?
  * [https://www.phing.info/trac/ticket/307] Replaceregexp filter works in Copy task but not Move task
  * [https://www.phing.info/trac/ticket/306] Command-line option to output the <target> description attribute text
  * [https://www.phing.info/trac/ticket/303] Documentation of Task Tag SimpleTest
  * [https://www.phing.info/trac/ticket/300] ExecTask should return command output as a property (different from passthru)
  * [https://www.phing.info/trac/ticket/299] PhingCall crashes if an AdhocTask is defined
  * [https://www.phing.info/trac/ticket/292] Svn copy task
  * [https://www.phing.info/trac/ticket/290] Add facility for setting resolveExternals property of DomDocument object in XML related tasks
  * [https://www.phing.info/trac/ticket/289] Undefined property in XincludeFilter class
  * [https://www.phing.info/trac/ticket/282] Import Task fix/improvement
  * [https://www.phing.info/trac/ticket/280] Add Phar support (task) to Phing
  * [https://www.phing.info/trac/ticket/279] Add documentation to PHK package task
  * [https://www.phing.info/trac/ticket/278] Add PHK package task
  * [https://www.phing.info/trac/ticket/277] PhpCodeSnifferTask has mis-named class, patch included
  * [https://www.phing.info/trac/ticket/273] PHPUnit 3.3RC1 error in phpunit task adding files to filter
  * [https://www.phing.info/trac/ticket/270] [patch] ReplaceRegExp
  * [https://www.phing.info/trac/ticket/269] Allow properties to be recursively named.
  * [https://www.phing.info/trac/ticket/263] phpunit code coverage file format change
  * [https://www.phing.info/trac/ticket/262] Archive_Zip fails to extract on Windows
  * [https://www.phing.info/trac/ticket/261] UnZip task reports success on failure on Windows
  * [https://www.phing.info/trac/ticket/259] Unneeded warning in Untar task
  * [https://www.phing.info/trac/ticket/256] Ignore dead code in code coverage
  * [https://www.phing.info/trac/ticket/254] Add extra debug resultformatter to the simpletest task
  * [https://www.phing.info/trac/ticket/252] foreach on a fileset
  * [https://www.phing.info/trac/ticket/248] Extend taskdef task to allow property file style imports
  * [https://www.phing.info/trac/ticket/247] New task: Import
  * [https://www.phing.info/trac/ticket/246] Phing test brocken but no failure entry if test case class has no test method
  * [https://www.phing.info/trac/ticket/245] TAR task
  * [https://www.phing.info/trac/ticket/243] Delete task won't delete all files
  * [https://www.phing.info/trac/ticket/240] phing test successful while phpunit test is broken
  * [https://www.phing.info/trac/ticket/233] Separate docs from phing package
  * [https://www.phing.info/trac/ticket/231] File::exists() returns false on *existing* but broken symlinks
  * [https://www.phing.info/trac/ticket/229] CopyTask shoul accept filelist subelement
  * [https://www.phing.info/trac/ticket/226] <move> task doesn't support filters
  * [https://www.phing.info/trac/ticket/222] Terminal output disappears and/or changes color
  * [https://www.phing.info/trac/ticket/221] Support for copying symlinks as is
  * [https://www.phing.info/trac/ticket/212] Make file perms configurable in copy task
  * [https://www.phing.info/trac/ticket/209] Cache the results of PHPLintTask so as to not check unmodified files
  * [https://www.phing.info/trac/ticket/187] "ExecTask attribute ""passthru"" to make use of the PHP function ""passthru"""
  * [https://www.phing.info/trac/ticket/21] svn tasks doesn't work

Dec. 8, 2008 - Phing 2.3.3
--------------------------

  * [https://www.phing.info/trac/ticket/314] <phpunit> task does not work
  * [https://www.phing.info/trac/ticket/313] Incorrect PhpDoc package of SimpleTestResultFormatter
  * [https://www.phing.info/trac/ticket/302] Incorrect error detecting in XSLT filter
  * [https://www.phing.info/trac/ticket/293] Contains condition fails on case-insensitive checks
  * [https://www.phing.info/trac/ticket/291] The release package is not the one as the version(2.3.2) suppose to be

Oct. 16, 2008 - Phing 2.3.2
---------------------------

  * [https://www.phing.info/trac/ticket/296] Problem with the Phing plugin with Hudson CI Tool
  * [https://www.phing.info/trac/ticket/288] Comment syntax for dbdeploy violates standard

Oct. 16, 2008 - Phing 2.3.1
---------------------------

  * [https://www.phing.info/trac/ticket/287] DateSelector.php bug
  * [https://www.phing.info/trac/ticket/286] dbdeploy failes with MySQL strict mode
  * [https://www.phing.info/trac/ticket/285] Syntax error in dbdeploy task
  * [https://www.phing.info/trac/ticket/284] XSL Errors in coverage-report task
  * [https://www.phing.info/trac/ticket/275] AnsiColorLogger should not be final
  * [https://www.phing.info/trac/ticket/274] PHPUnit 3.3RC1 incompatibility with code coverage
  * [https://www.phing.info/trac/ticket/272] Using CDATA with ReplaceTokens values
  * [https://www.phing.info/trac/ticket/271] Warning on iterating over empty keys
  * [https://www.phing.info/trac/ticket/264] Illeal use of max() with empty array
  * [https://www.phing.info/trac/ticket/260] Error processing reults: SQLSTATE [HY000]: General error: 2053 when executing inserts or create statements.
  * [https://www.phing.info/trac/ticket/258] getPhingVersion + printVersion should be public static
  * [https://www.phing.info/trac/ticket/255] Timestamp in Phing Properties for Echo etc
  * [https://www.phing.info/trac/ticket/253] CCS nav bug on PHING.info site
  * [https://www.phing.info/trac/ticket/251] debug statement in Path datatype for DirSet
  * [https://www.phing.info/trac/ticket/249] See failed tests in console
  * [https://www.phing.info/trac/ticket/244] Phing pear install nor working
  * [https://www.phing.info/trac/ticket/242] Log incomplete and skipped tests for phpunit3
  * [https://www.phing.info/trac/ticket/241] FtpDeployTask reports FTP port as FTP server on error
  * [https://www.phing.info/trac/ticket/239] ExecTask shows no output from running command
  * [https://www.phing.info/trac/ticket/238] Bug in SummaryPHPUnit3ResultFormatter
  * [https://www.phing.info/trac/ticket/237] Several PHP errors in XSLTProcessor
  * [https://www.phing.info/trac/ticket/236] Do not show passwords for svn in log
  * [https://www.phing.info/trac/ticket/234] typo in foreach task documentation
  * [https://www.phing.info/trac/ticket/230] Fatal error: Call to undefined method PHPUnit2_Framework_TestResult::skippedCount() in /usr/local/lib/php/phing/tasks/ext/phpunit/PHPUnitTestRunner.php on line 120
  * [https://www.phing.info/trac/ticket/227] simpletestformaterelement bad require
  * [https://www.phing.info/trac/ticket/225] Missing Software Dependence in documentation
  * [https://www.phing.info/trac/ticket/224] Path class duplicates absolute path on subsequent path includes
  * [https://www.phing.info/trac/ticket/220] AnsiColorLogger colors cannot be changed by build.properties
  * [https://www.phing.info/trac/ticket/219] Add new chown task
  * [https://www.phing.info/trac/ticket/218] Clear support of PHPUnit versions
  * [https://www.phing.info/trac/ticket/217] Memory limit in phpdoc
  * [https://www.phing.info/trac/ticket/216] output messages about errors and warnings in JslLint task
  * [https://www.phing.info/trac/ticket/215] boolean attributes of task PhpCodeSniffer are wrong
  * [https://www.phing.info/trac/ticket/214] PhpCodeSnifferTask should be able to output file
  * [https://www.phing.info/trac/ticket/213] Error in documentation task related to copy task
  * [https://www.phing.info/trac/ticket/211] XSLT does not handle multiple testcase nodes for the same test method
  * [https://www.phing.info/trac/ticket/210] Reworked PhpDocumentorExternalTask
  * [https://www.phing.info/trac/ticket/208] ReplaceRegexp problem with newline as replace string
  * [https://www.phing.info/trac/ticket/207] PhpLintTask: optional use a different PHP interpreter
  * [https://www.phing.info/trac/ticket/206] Installation guide out of date (phing fails to run)
  * [https://www.phing.info/trac/ticket/205] AvailableTask::_checkResource ends up with an exception if resource isn't found.
  * [https://www.phing.info/trac/ticket/203] ExecTask returnProperty
  * [https://www.phing.info/trac/ticket/202] Add PHP_CodeSniffer task
  * [https://www.phing.info/trac/ticket/201] "Improve Phing's ability to work as an ""embedded"" process"
  * [https://www.phing.info/trac/ticket/200] Additional attribute for SvnUpdateTask
  * [https://www.phing.info/trac/ticket/199] Invalid error message in delete task when deleting directory fails.
  * [https://www.phing.info/trac/ticket/198] PDO SQL exec task unable to handle multi-line statements
  * [https://www.phing.info/trac/ticket/197] phing delete task sometimes fails to delete file that could be deleted
  * [https://www.phing.info/trac/ticket/195] SvnLastRevisionTask fails if Subversion is localized (Spanish)
  * [https://www.phing.info/trac/ticket/194] haltonincomplete attribute for phpunit task
  * [https://www.phing.info/trac/ticket/193] Manifest Task
  * [https://www.phing.info/trac/ticket/192] Error when skip test
  * [https://www.phing.info/trac/ticket/191] Akismet says content is spam
  * [https://www.phing.info/trac/ticket/190] Add test name in printsummary in PHPUnit task
  * [https://www.phing.info/trac/ticket/185] PHPUnit_MAIN_METHOD defined more than once
  * [https://www.phing.info/trac/ticket/184] PlainPHPUnit3ResultFormatter filteres test in stack trace
  * [https://www.phing.info/trac/ticket/183] Undefined variable in PhingTask.php
  * [https://www.phing.info/trac/ticket/182] Undefined variable in  SummaryPHPUnit3ResultFormatter
  * [https://www.phing.info/trac/ticket/181] PhingCallTask should call setHaltOnFailure
  * [https://www.phing.info/trac/ticket/179] Add documentation for TidyFilter
  * [https://www.phing.info/trac/ticket/178] printsummary doens work in PHP Unit task
  * [https://www.phing.info/trac/ticket/177] Only write ConfigurationExceptions to stdout
  * [https://www.phing.info/trac/ticket/176] Cleanup installation documentation.
  * [https://www.phing.info/trac/ticket/175] passing aarguments to phing
  * [https://www.phing.info/trac/ticket/169] Spurious PHP Error from XSLT Filter
  * [https://www.phing.info/trac/ticket/150] unable to include phpdocumentor.ini in PHPDoc-Task
  * [https://www.phing.info/trac/ticket/15] FTP upload task

Nov. 3, 2007 - Phing 2.3.0
--------------------------

  * [https://www.phing.info/trac/ticket/174] Add differentiation for build loggers that require explicit streams to be set
  * [https://www.phing.info/trac/ticket/173] Add 'value' alias to XSLTParam type.
  * [https://www.phing.info/trac/ticket/172] broken phpunit2-frames.xsl
  * [https://www.phing.info/trac/ticket/171] Allow results from selector to be loosely type matched to true/false
  * [https://www.phing.info/trac/ticket/170] SvnLastRevisionTask cannot get SVN revision number on single file
  * [https://www.phing.info/trac/ticket/168] XincludeFilter PHP Error
  * [https://www.phing.info/trac/ticket/167] Add new formatter support for PDOSQLExecTask
  * [https://www.phing.info/trac/ticket/166] Change CreoleTask to use <creole> tagname instead of <sql>
  * [https://www.phing.info/trac/ticket/165] Add support for PHPUnit_Framework_TestSuite subclasses in fileset of test classes
  * [https://www.phing.info/trac/ticket/164] Failed build results in empty log.xml
  * [https://www.phing.info/trac/ticket/163] Add stripwhitespace filter
  * [https://www.phing.info/trac/ticket/162] Add @pattern alias for @name in <fileset>
  * [https://www.phing.info/trac/ticket/161] phing/etc directory missing (breaking PHPUnit)
  * [https://www.phing.info/trac/ticket/157] Fatal error in PDOSQLExecTask when using filesets
  * [https://www.phing.info/trac/ticket/155] <delete> fails when it encounters symlink pointing to non-writable file
  * [https://www.phing.info/trac/ticket/154] Suggestion to add attribute to PDOSQLExecTask for fetch_style
  * [https://www.phing.info/trac/ticket/153] sqlite select failure
  * [https://www.phing.info/trac/ticket/152] result of PHP-Unit seems to be incorrect
  * [https://www.phing.info/trac/ticket/151] add group-option to PHPUnit-Task
  * [https://www.phing.info/trac/ticket/149] using TestSuites in fileset of PHPUnit-Task
  * [https://www.phing.info/trac/ticket/148] remove dependency to PEAR in PHPUnit-Task
  * [https://www.phing.info/trac/ticket/146] Illegal offset type PHP notice in CopyTask
  * [https://www.phing.info/trac/ticket/143] Example for PhpDocumentor task has typographical errors and a wrong attribute.
  * [https://www.phing.info/trac/ticket/142] SvnCheckout task only makes non-recursive checkouts.
  * [https://www.phing.info/trac/ticket/141] Add 'recursive' attribute to svncheckout task.
  * [https://www.phing.info/trac/ticket/136] Attribute os of ExecTask is not working
  * [https://www.phing.info/trac/ticket/135] add source file attribute for code coverage xml report
  * [https://www.phing.info/trac/ticket/133] Error in documenation: AppendTask
  * [https://www.phing.info/trac/ticket/129] Typo in documentation
  * [https://www.phing.info/trac/ticket/128] <pearpkg2> is missing in the doc completely
  * [https://www.phing.info/trac/ticket/127] Error in documentation
  * [https://www.phing.info/trac/ticket/126] Typo in documentation
  * [https://www.phing.info/trac/ticket/122] PearPackage2Task Replacements don't seem to work
  * [https://www.phing.info/trac/ticket/121] BUILD FAILED use JsLintTask
  * [https://www.phing.info/trac/ticket/119] PhpDocumentorTask fails when trying to use parsePrivate attribute.
  * [https://www.phing.info/trac/ticket/118] custom tasks have this->project == null
  * [https://www.phing.info/trac/ticket/117] CoverageSetupTask and autoloaders
  * [https://www.phing.info/trac/ticket/116] Test unit don't report notice or strict warnings
  * [https://www.phing.info/trac/ticket/110] "Add ""errorproperty"" attribute to PhpLintTask"
  * [https://www.phing.info/trac/ticket/107] SvnLastRevisionTask doesn't work with repositoryUrl
  * [https://www.phing.info/trac/ticket/106] "document ""haltonfailure"" attribute for phplint task"
  * [https://www.phing.info/trac/ticket/105] FileSystemUnix::normalize method: Improve handling
  * [https://www.phing.info/trac/ticket/97] delete dir and mkdir are incompatible
  * [https://www.phing.info/trac/ticket/92] Inconsistent newlines in PHP files
  * [https://www.phing.info/trac/ticket/91] Improve detection for PHPUnit3
  * [https://www.phing.info/trac/ticket/83] "XmlLogger improperly handling ""non-traditional"" buildfile execution paths"
  * [https://www.phing.info/trac/ticket/82] Error when use markTestIncomplete in test
  * [https://www.phing.info/trac/ticket/79] Allow escaped dots in classpaths
  * [https://www.phing.info/trac/ticket/78] (SVN doc) ${phing.version} and ${php.version} are different!
  * [https://www.phing.info/trac/ticket/77] taskdef doesn't support fileset
  * [https://www.phing.info/trac/ticket/76] Overhaul PhpDocumentor task
  * [https://www.phing.info/trac/ticket/75] files excluded by fileset end up in .tgz but not .zip
  * [https://www.phing.info/trac/ticket/74] Phing commandline args don't support quoting / spaces
  * [https://www.phing.info/trac/ticket/73] Semantical error in PhingFile::getParent()
  * [https://www.phing.info/trac/ticket/72] "Remove use of getProperty(""line.separator"") in favor of PHP_EOL"
  * [https://www.phing.info/trac/ticket/71] "Add ""-p"" alias for project help"
  * [https://www.phing.info/trac/ticket/70] Create Project class constants for log levels (replacing PROJECT_MSG_*)
  * [https://www.phing.info/trac/ticket/69] mkdir and delete tasks don't work properly together
  * [https://www.phing.info/trac/ticket/68] Xinclude filter
  * [https://www.phing.info/trac/ticket/67] Add PDO SQL execution task
  * [https://www.phing.info/trac/ticket/66] Incorrectly set PHP_CLASSPATH in phing.bat
  * [https://www.phing.info/trac/ticket/65] Convert all loggers/listeners to use streams
  * [https://www.phing.info/trac/ticket/64] Build listeners currently not working
  * [https://www.phing.info/trac/ticket/63] Configured -logger can get overridden
  * [https://www.phing.info/trac/ticket/62] phing.buildfile.dirname built-in property
  * [https://www.phing.info/trac/ticket/58] Path::listPaths() broken for DirSet objects.
  * [https://www.phing.info/trac/ticket/57] FileList.getListFile method references undefined variable
  * [https://www.phing.info/trac/ticket/56] TaskHandler passing incorrect param to ProjectConfigurator->configureId()
  * [https://www.phing.info/trac/ticket/53] _makeCircularException seems to have an infinite loop
  * [https://www.phing.info/trac/ticket/52] \<match>-syntax does not work correctly with preg_*()
  * [https://www.phing.info/trac/ticket/51] Cannot get phing to work with PHPUnit 3
  * [https://www.phing.info/trac/ticket/48] Supported PHPUnit2_Framework_TestSuite and PHPUnit2_Extensions_TestSetup sub-classes for the PHPUnit2Task and CoverageReportTask tasks
  * [https://www.phing.info/trac/ticket/33] Implement changes to use PHPUnit2 3.0 code coverage information
  * [https://www.phing.info/trac/ticket/22] Description about integrating into CruiseControl

Aug. 21, 2006 - Phing 2.2.0
---------------------------

  * Refactored parser to support many tags as children of base <project> tag (HL)
  * Added new IfTask (HL)
  * Added "spawn" attribute to ExecTask (only applies to *nix)
  * Several bugfixes & behavior imporvements to ExecTask (HL, MR, Ben Gollmer)
  * Bugfixes & refactoring for SVNLastRevisionTask (MR, Knut Urdalen)
  * Fixed reference copy bug (HL, Matthias Pigulla)
  * Added SvnExportTask (MR)
  * Added support for FileList in DeleteTask. (HL)
  * Added support for using setting Properties using CDATA value of <property> tag. (HL)
  * Added ReferenceExistsCondition (Matthias Pigulla)
  * Added Phing::log() static method & integrated PHP error handling with Phing logging (HL)
  * Added new task to run the ionCube Encoder (MR)
  * Added new HTML Tidy filter (HL)
  * Added PhpLintTask (Knut Urdalen)
  * Added XmlLintTask (Knut Urdalen)
  * Added ZendCodeAnalyzerTask (Knut Urdalen)
  * Removed CoverageFormatter class (MR). NOTE: This changes the usage of the collection of PHPUnit2 code coverage reports, see the updated documentation for the CoverageSetupTask
  * Added Unzip and Untar tasks contributed by Joakim Bodin
  * [https://www.phing.info/trac/ticket/8], [49] Fixed bugs in TarTask related to including empty directories (HL)
  * [https://www.phing.info/trac/ticket/44] Fixed bug related to copying empty dirs. (HL)
  * [https://www.phing.info/trac/ticket/32] Fixed PHPUnit2 tasks to work with PHPUnit2-3.0.0 (MR)
  * [https://www.phing.info/trac/ticket/31] Fixed bug with using PHPDocumentor 1.3.0RC6 (MR)
  * [https://www.phing.info/trac/ticket/43] Fixed top-level (no target) IfTask behavior (Matthias Pigulla)
  * [https://www.phing.info/trac/ticket/41] Removed some lingering E_STRICT errors, bugs with 5.1.x and PHP >= 5.0.5 (HL)
  * [https://www.phing.info/trac/ticket/25] Fixed 'phing' script to also run on non-bash unix /bin/sh 
  * Numerous documentation improvements by many members of the community (Thanks!)
  
Sept. 18, 2005 - Phing 2.1.1
----------------------------

  * Added support for specifying 4-char mask (e.g. 1777) to ChmodTask. (Hans Lellelid)
  * Added .svn files to default excludes in DirectoryScanner.
  * Updated PHPUnit2 BatchTest to use class detection and non-dot-path loader. (Michiel Rook)
  * Added support for importing non dot-path files (Michiel Rook)
  * Add better error message when build fails with exception (Hans Lellelid)
  * Fixed runtime error when errors were encountered in AppendTask (Hans Lellelid)

June 17, 2005 - Phing 2.1.0
---------------------------

  * Renamed File -> PhingFile to avoid namespace collisions (Michiel Rook)
  * Add ZipTask to create .zip files (Michiel Rook)
  * Removed redudant logging of build errors in Phing::start() (Michiel Rook)
  * Added tasks to execute PHPUnit2 testsuites and generate coverage and test reports. (Michiel Rook, Sebastian Bergmann)
  * Added SvnLastRevisionTask that stores the number of the last revision of a workingcopy in a property. (Michiel Rook)
  * Added MailTask that sends a message by mail() (Michiel Rook, contributed by Francois Harvey)
  * New IncludePathTask (<includepath/>) for adding values to PHP's include_path. (Hans Lellelid)
  * Fix to Phing::import() to *not* attempt to invoke __autoload() in class_exists() check. (Hans Lellelid)
  * Fixed AppendTask to allow use of only <fileset> as source. (Hans Lellelid)
  * Removed dependency on posix, by changing posix_uname to php_uname if needed. (Christian Stocker)
  * Fixed issues: (Michiel Rook)
  * [https://www.phing.info/trac/ticket/11] ExtendedFileStream does not work on Windows
  * [https://www.phing.info/trac/ticket/12] CoverageFormatter problem on Windows
  * [https://www.phing.info/trac/ticket/13] DOMElement warnings in PHPUnit2 tasks
  * [https://www.phing.info/trac/ticket/14] RuntimeException conflicts with SPL class
  * [https://www.phing.info/trac/ticket/15] It is not possible to execute it with PHP5.1
  * [https://www.phing.info/trac/ticket/16] Add Passthru option to ExecTask
  * [https://www.phing.info/trac/ticket/17] Blank list on foreach task will loop once
  * [https://www.phing.info/trac/ticket/19] Problem with <formatter outfile="...">
  * [https://www.phing.info/trac/ticket/20] Phpunit2report missing XSL stylesheets
  * [https://www.phing.info/trac/ticket/21] Warnings when output dir does not exist in PHPUnit2Report

Oct 16, 2004 - Phing 2.0.0
--------------------------

  * Minor fixes to make Phing run under E_STRICT/PHP5.
  * Fix to global/system properties not being set in project. (Matt Zandstra)
  * Fixes to deprecated return by reference issues w/ PHP5.0.0

June 8, 2004 - Phing 2.0.0b3
----------------------------

  * Brought up-to-date w/ PHP5.0.0RC3
  * Fixed several bugs in ForeachTask
  * Fixed runtime errors and incomplete inheriting of properties in PhingTask
  * Added <fileset> support to AppendTask

March 19, 2004 - Phing 2.0.0b2
------------------------------

  * Brought up-to-date w/ PHP5.0.0RC1 (Hans)
  * Fixed bug in seting XSLT params using XSLTask (Hans, Jeff Moss)
  * Fixed PHPUnit test framework for PHPUnit-2.0.0alpha3
  * Added "Adhoc" tasks, which allow for defining PHP task or type classes within the buildfile. (Hans)
  * Added PhpEvalTask which allows property values to be set to simple PHP evaluations or the results of function/method calls. (Hans)
  * Added new phing.listener.PearLogger listener (logger).  Also, the -logfile arg is now supported. (Hans)
  * Fixed broken ForeachTask task.  (Manuel)

Dec 24, 2003 - Phing 2.0.0b1
----------------------------

  * Added PEAR installation framework & ability to build Phing into PEAR package.
  * Added TarTask using PEAR Archive_Tar
  * Added PearPackageTask which creates a PEAR package.xml (using PEAR_PackageFileManager).
  * Added ResolvePathTask which converts relative paths into absolute paths.
  * Removed System class, due to namespace collision w/ PEAR.
  * Basic "working?" tests performed with all selectors.
  * Added selectors:  TypeSelector, ContainsRegexpSelector
  * CreoleSQLExec task is now operational.
  * Corrected non-fatal bugs in: DeleteTask, ReflexiveTask
  * All core Phing classes now in PHP5 syntax (no "var" used, etc.)
  * CopyTask will not stop build execution if a file cannot be copied (will log and continue to next file).
  * New abstract MatchingTask task makes it easier to create your own tasks that use selectors.
  * Removed redundant calls in DirectoryScanner (<fileset> scanning now much faster).
  * Fixed fatal errors in File::equals()

Nov 24, 2003 - Phing 2.0.0a2
----------------------------

  * Fixed ReplaceTokens filter to correctly replace matched tokens
  * Changed "project.basedir" property to be absolute path of basedir
  * Made IntrospectionHelper more tollerant of add*() and addConfigured*() signatures
  * New CvsTask and CvsPassTask for working with CVS repositories
  * New TranslateGettext filter substitutes _("hello!") with "hola!" / "bonjour!" / etc.
  * More consistent use of classhints to enable auto-casting by IntrospectionHelper
  * Fixed infinite loop bug in FileUtils::normalize() for paths containing "/./"
  * Fixed bug in CopyFile/fileset that caused termination of copy operation on encounter of unreadable file

Nov 6, 20003 - Phing 2.0.0a1
----------------------------

  * First release of Phing 2, an extensive rewrite and upgrade.
  * Refactored much of codebase, using new PHP5 features (e.g. Interfaces, Exceptions!)
  * Many, many, many bugfixes to existing functionality
  * Restructuring for more intuitive directory layout, change the parser class names.
  * Introduction of new tasks: AppendTask, ReflexiveTask, ExitTask, Input, PropertyPrompt
  * Introduction of new types: Path, FileList, DirSet, selectors, conditions
  * Introduction of new filters: ReplaceRegexp
  * Introduction of new logger: AnsiColorLogger
  * Many features from ANT 1.5 added to existing Tasks/Types
  * New "Register Slot" functionality allows for tracking "inner" dynamic variables.
