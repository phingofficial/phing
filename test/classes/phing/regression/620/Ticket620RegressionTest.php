<?php

use Phing\Test\AbstractBuildFileTest;

class Ticket620RegressionTest extends AbstractBuildFileTest
{

    public function testCallingImportedBuildfileUsesOwnTarget()
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/regression/620/subdir/imported.xml");
        $this->executeTarget("test");
        $this->assertInLogs("imported.xml::some-target");
    }

    public function testCallingImportingBuildfileOverridesTarget()
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/regression/620/importing.xml");
        $this->executeTarget("test"); // defined in the "imported.xml" buildfile
        $this->assertInLogs("importing.xml::some-target"); // "some-target" overridden by importing.xml
    }

    public function testOverriddenTargetAvailableUnderAliasName()
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/regression/620/importing.xml");
        $this->executeTarget("use-aliased-target"); // this target depends on "imported.some-target"
        $this->assertInLogs("imported.xml::some-target");
    }

    public function testProperties()
    {
        /*
         * The properties phing.file, phing.dir and project.basedir point to the
         * buildfile, directory containing the buildfile and project basedir referenced
         * by the "root" (executed) buildfile, respectively.
         *
         * These properties are also set with a ".[projectname]" suffix for the
         * root and all imported buildfiles, where "[projectname]" is the <project> @name
         * attribute for each respective buildfile.
         *
         * Note that subdir/imported.xml refers to ".." as basedir.
         */
        $this->configureProject(PHING_TEST_BASE . "/etc/regression/620/importing.xml");
        $this->executeTarget("dump-properties");

        // The "main" buildfile
        $this->assertInLogs("project.basedir = ".PHING_TEST_BASE."/etc/regression/620");
        $this->assertInLogs("phing.file = ".PHING_TEST_BASE."/etc/regression/620/importing.xml");
        $this->assertInLogs("phing.dir = ".PHING_TEST_BASE."/etc/regression/620");

        // Same as for the "main" buildfile, but under its projectname
        $this->assertInLogs("project.basedir.importing = ".PHING_TEST_BASE."/etc/regression/620");
        $this->assertInLogs("phing.file.importing = ".PHING_TEST_BASE."/etc/regression/620/importing.xml");
        $this->assertInLogs("phing.dir.importing = ".PHING_TEST_BASE."/etc/regression/620");

        // Values for the "imported" buildfile
        $this->assertInLogs("project.basedir.imported = ".PHING_TEST_BASE."/etc/regression/620");
        $this->assertInLogs("phing.file.imported = ".PHING_TEST_BASE."/etc/regression/620/subdir/imported.xml");
        $this->assertInLogs("phing.dir.imported = ".PHING_TEST_BASE."/etc/regression/620/subdir");
    }

}
