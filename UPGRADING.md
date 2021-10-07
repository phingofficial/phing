Upgrading from Phing 2.x to 3.0
===============================

Phing 3 is a significant update with some breaking changes compared to Phing 2.

This document aims to summarize all those  breaking changes and noteworthy things
that you might stumble across when upgrading from Phing 2 to 3.

* Phing now requires at least PHP 7.3.
* All Phing code is now namespaced. This means that existing references to classes
  that existed in earlier Phing versions will no longer work. For example, the
  class `PhingFile` has been moved to `Phing\Io\File`. If you are used to providing
  a specific logger or listener when running Phing (such as `phing.listener.DefaultLogger`),
  you will need to adjust this (to `Phing\Listener\DefaultLogger`, for example).
  The documentation has been modified to reflect this.
* Support for dot-path classnames (i.e., `foo.bar.FooBar`) has been removed. The
  associated `PackageAsPath` task was also removed.
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
* The Zend Guard tasks were removed (Zend Guard is no longer supported).
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
* The signature from `\Phing\Listener\DefaultLogger::formatTime` has been changed. Therefore, if you have written a
  logger that overrides this method, you will need to update its signature accordingly.
* The way how Phing handles file sizes has been normalized, this is explained in documentation.
    * FileSizeTask: `unit` attribute can be an IEC or SI suffix.
    * HasFreeSpace condition: `needed` attribute can include an IEC or SI suffix.
    * Size selector: `units` attribute has been removed, `value` attribute can include an IEC or SI suffix.
    * TruncateTask: `length` attribute can include an IEC or SI suffix.
* The way how Phing handles boolean values has been normalized. Therefore `t` is not a valid `true` value any longer. For a list of effected components follow https://github.com/phingofficial/phing/search?p=1&q=booleanValue
* Obsolete `ExportPropertiesTask` was removed in favor of the `EchoPropertiesTask`
    ```xml
    <exportproperties targetfile="output.props" />
    <-- is the same as -->
    <echoproperties destfile="output.props" regex="/^((?!host\.)(?!os\.)(?!env\.)(?!phing\.)(?!php\.)(?!line\.)(?!user\.)[\s\S])*$/"/>
    ```
* `FileHashTask` creates now a file by default
* Deprecated `scpsend` alias for the `ScpTask` was removed
