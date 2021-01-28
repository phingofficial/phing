Upgrading from Phing 2.x to 3.0
===============================

Phing 3 is a significant update with some breaking changes compared to Phing 2.

This document aims to summarize all those  breaking changes and noteworthy things
that you might stumble across when upgrading from Phing 2 to 3.

* Phing now requires at least PHP 7.3.
* All Phing code is now namespaced. This means that existing references to classes
  that existed in earlier Phing versions will no longer work. For example, the
  class `PhingFile` has been moved to `Phing\Io\File`.
* Omitting the `basedir` property in the root `project` tag now means "." instead
  of the current working directory. This effectively reverts the change made in 
  http://www.phing.info/trac/ticket/309 ([dfdb0bc](https://github.com/phingofficial/phing/commit/dfdb0bc8095db18284de364b421d320be3c1b6fb))
* The behavior of `MkdirTask` has changed to be same as of `mkdir` Linux command:
  * When using `MkdirTask` to create a nested directory including its parents
    (eg. `<mkdir dir="a/b/c" />`), the task now creates the parent directories
    with default permissions and not the permissions specified in `mode` attribute.
    Only the last directory in the created hierarchy (ie. "c" in "a/b/c") should
    have permissions corresponding to the specified `mode` attribute. 
    Previously, `MkdirTask` used to apply the same `mode` to all the directories
    in the hierarchy including the last one.
  * When using `MkdirTask` with `mode` attribute, the newly created directory
    now has exact the permissions specified in `mode` attribute. If any parent
    directories are created, they have default permissions affected by umask
    setting. Previously, `MkdirTask` used to mask the permissions of the last
    directory in the hierarchy according to umask too.
  * These changes are also important for POSIX Access Control Lists (ACL) to work
    properly. When using ACL, the mask used to determine the effective pemissions
    corresponds to the standard group permissions. The ACL mask of a newly
    created directory should be inherited from the default ACL mask its parent
    directory. However, previously `MkdirTask` without `mode` attribute used
    mask the group permissions of newly created directories according to umask 
    setting which resulted in lower than expected permissions. This should not
    happen when using ACL. Now, `MkdirTask` respects ACL settings.
* The tasks to generate PEAR packages \(including supporting code\) have been removed from Phing.
* [AppendTask] The default behavior of `append` attribute was changed to `true`
* [MoveTask] The default behavior of `overwrite` attribute was changed to `true`
* [PHPUnitTask] Support for PHPUnit 9.
* [PhpCodeSnifferTask] was removed in favor of [PhpCSTask].
* A number of tasks (or group of tasks) were moved to their own repositories, but are automatically
  pulled in / installed when you install Phing:
  * ApiGen
  * Code coverage
  * FtpDeploy
  * Git
  * Hg
  * IniFile
  * Ioncube
  * JsHint
  * JsMin
  * Liquibase
  * PhkPackage
  * PhpDoc
  * Smarty
  * SSH
  * Visualizer
  * ZendCodeAnalyser
  * ZendServerDevelopmentTools
