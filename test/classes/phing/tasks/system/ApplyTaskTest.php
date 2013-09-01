<?php
/*
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once 'phing/BuildFileTest.php';


/**
 * Tests the Apply Task
 *
 * @author  Utsav Handa <handautsav at hotmail dot com>
 * @package phing.tasks.system
 */
class ApplyTaskTest extends BuildFileTest
{
   /**
    * Whether test is being run on windows
    * @var bool
    */
    protected $windows;


    /**
     * Setup the test 
     */
    public function setUp() {
        if (version_compare(PHP_VERSION, '5.3.2') < 0) {
            $this->markTestSkipped(
                'Need at least PHP version 5.3.2 to run this unit test'
            );
        }

        // Tests definitions
        $this->configureProject( PHING_TEST_BASE . '/etc/tasks/system/ApplyTest.xml' );

        // Identifying the running environment
        $this->windows = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN';
    }


    /**********************************************************************************/
    /************************************** T E S T S *********************************/
    /**********************************************************************************/


    /**
     * Tests the OS configuration setting 
     */
    public function testPropertySetOs() {
      return $this->assertAttributeIsSetTo('os', 'linux');
    }

    /**
     * Tests the dir configuration setting 
     */
    public function testPropertySetDir() {
      return $this->assertAttributeIsSetTo('dir', new PhingFile('/tmp/'));
    }

    /**
     * Tests the escape configuration setting 
     */
    public function testPropertySetEscape() {
      return $this->assertAttributeIsSetTo('escape', true);
    }

    /**
     * Tests the pass-thru configuration setting 
     */
    public function testPropertySetPassthru() {
      return $this->assertAttributeIsSetTo('passthru', true);
    }

    /**
     * Tests the spawn configuration setting 
     */
    public function testPropertySetSpawn() {
      return $this->assertAttributeIsSetTo('spawn', true);
    }

    /**
     * Tests the returnProperty configuration setting 
     */
    public function testPropertySetReturnProperty() {
      return $this->assertAttributeIsSetTo('returnProperty', 'retval');
    }

    /**
     * Tests the outputProperty configuration setting 
     */
    public function testPropertySetOutputProperty() {
      return $this->assertAttributeIsSetTo('outputProperty', 'outval');
    }

    /**
     * Tests the checkReturn/failonerror configuration setting 
     */
    public function testPropertySetCheckReturn() {
      return $this->assertAttributeIsSetTo('checkreturn', true, 'failonerror');
    }

    /**
     * Tests the output configuration setting 
     */
    public function testPropertySetOutput() {
      return $this->assertAttributeIsSetTo('output', new PhingFile('/tmp/outputfilename'));
    }

    /**
     * Tests the error configuration setting 
     */
    public function testPropertySetError() {
      return $this->assertAttributeIsSetTo('error', new PhingFile('/tmp/errorfilename'));
    }

    /**
     * Tests the append configuration setting 
     */
    public function testPropertySetAppend() {
      return $this->assertAttributeIsSetTo('append', true, 'appendoutput');
    }

    /**
     * Tests the parallel configuration setting 
     */
    public function testPropertySetParallel() {
      return $this->assertAttributeIsSetTo('parallel', false);
    }

    /**
     * Tests the addsourcefile configuration setting 
     */
    public function testPropertySetAddsourcefile() {
      return $this->assertAttributeIsSetTo('addsourcefile', false);
    }

    /**
     * Tests the relative configuration setting 
     */
    public function testPropertySetRelative() {
      return $this->assertAttributeIsSetTo('relative', false);
    }

    /**
     * Tests the forwardslash configuration setting 
     */
    public function testPropertySetForwardslash() {
      return $this->assertAttributeIsSetTo('forwardslash', true);
    }

    /**
     * Tests the maxparallel configuration setting 
     */
    public function testPropertySetMaxparallel() {
      return $this->assertAttributeIsSetTo('maxparallel', 10);
    }

    /**
     * Tests the OS execution for the unspecified OS 
     */
    public function testDoNotExecuteOnWrongOs() {

      // Process
      $this->executeTarget(__FUNCTION__);
      $this->assertInLogs('Not found in unknownos');

      return $this->assertNotContains('this should not be executed', $this->getOutput());
    }

    /**
     * Tests the OS execution for the specified OS list
     */
    public function testExecuteOnCorrectOs() {
      $this->executeTarget(__FUNCTION__);
      return $this->assertInLogs('this should be executed');
    }

    /**
     * Tests the dir changing on a non-existent directory
     */
    public function testFailOnNonExistingDir() {
      return $this->expectBuildExceptionContaining(__FUNCTION__, __FUNCTION__, "'/tmp/non/existent/dir' is not a valid directory");
    }

    /**
     * Tests the dir changing on an existent directory
     */
    public function testChangeToDir() {
      
      // Validating the OS platform
      if ($this->windows) {
        return $this->markTestSkipped("Windows does not have 'ls'");
      }

      $this->executeTarget(__FUNCTION__);
      return $this->assertInLogs('Working directory change successful');
    }


    /**
     * Tests the failonerror/checkreturn value for 'true'
     */
    public function testCheckreturnTrue() {

      // Validating the OS platform
      if ($this->windows) {
        return $this->markTestSkipped("Windows does not have '/bin/true'");
      }

      $this->executeTarget(__FUNCTION__);
      return $this->assertTrue(true);
    }

    /**
     * Tests the failonerror/checkreturn value for 'false'
     */
    public function testCheckreturnFalse() {
      
      // Validating the OS platform
      if ($this->windows) {
        return $this->markTestSkipped("Windows does not have '/bin/false'");
      }
      
      return $this->expectBuildExceptionContaining(__FUNCTION__, __FUNCTION__, 'Task exited with code (1)');
    }
    
    /**
     * Tests the outputProperty setting
     */
    public function testOutputProperty() {
      $this->executeTarget(__FUNCTION__);
      return $this->assertInLogs('The output property\'s value is: "foo"');
    }
    
    /**
     * Tests the returnProperty setting
     */
    public function testReturnProperty() {
      $this->executeTarget(__FUNCTION__);
      return $this->assertInLogs('The return property\'s value is: "1"');
    }

    /**
     * Tests the command escaping for execution
     */
    public function testEscape() {
      $this->executeTarget(__FUNCTION__);
      return $this->assertInLogs( $this->windows ? ("'echo' 'foo'  '|'  'cat'") : ("'echo' 'foo' '|' 'cat'") );
    }

    /**
     * Tests the command execution with 'passthru' function
     */
    public function testPassThru() {
      $this->executeTarget(__FUNCTION__);
      return $this->assertInLogs('Command execution : (passthru)');
    }

    /**
     * Tests the output file functionality
     */
    public function testOutput() {

      // Getting a temp. file
      $tempfile = tempnam(sys_get_temp_dir(), 'phing-exectest-');

      // Setting the property
      $this->project->setProperty('execTmpFile', $tempfile);
      $this->executeTarget(__FUNCTION__);

      // Validating the output
      $output = @file_get_contents($tempfile);
      @unlink($tempfile);
      return $this->assertEquals('outfoo', rtrim($output));
    }

    /**
     * Tests the error file functionality
     */
    public function testError() {

      // Validating the OS platform
      if ($this->windows) {
        return $this->markTestSkipped("The script is unlikely to run on MS Windows");
      }

      // Getting a temp. file
      $tempfile = tempnam(sys_get_temp_dir(), 'phing-exectest-');

      // Setting the property
      $this->project->setProperty('execTmpFile', $tempfile);
      $this->executeTarget(__FUNCTION__);

      // Validating the output
      $output = @file_get_contents($tempfile);
      @unlink($tempfile);
      return $this->assertEquals("errfoo", rtrim($output));
    }

    /**
     * Tests the execution with the background process spawning
     */
    public function testSpawn() {

      // Process
      $start = time();
      $this->executeTarget(__FUNCTION__);
      $end = time();
      return $this->assertLessThan(4, ($end - $start), 'Execution time should be lower than 4 seconds, otherwise spawning did not work');
    }

    /**
     * Tests the nested arguments specified for the execution
     */
    public function testNestedArgs() {
      $this->executeTarget(__FUNCTION__);
      return $this->assertInLogs('echo Hello World');
    }

    /**
     * Tests the missing/unspecified executable information
     */
    public function testMissingExecutable() {
      return $this->expectBuildExceptionContaining(__FUNCTION__, __FUNCTION__, 'Please provide "executable" information');
    }

    /**
     * Tests the escape functionality for special characters in argument
     */
    public function testEscapedArg() {
      $this->executeTarget(__FUNCTION__);
      return $this->assertPropertyEquals('outval', 'abc$b3!SB');
    }

    /**
     * Tests the relative source filenames functionality
     */
    public function testRelativeSourceFilenames() {
      $this->executeTarget(__FUNCTION__);
      return $this->assertNotInLogs('/etc/');
    }

    /**
     * Tests the source filename addition functionality
     */
    public function testSourceFilename() {

      // Validating the OS platform
      if ($this->windows) {
        return $this->markTestSkipped("Windows does not have 'ls'");
      }

      $this->executeTarget(__FUNCTION__);
      // As the addsourcefilename is 'off', only the executable should be processed in the execution
      return $this->assertInLogs(': ls :');
    }

    /**
     * Tests the output file append functionality
     */
    public function testOutputAppend() {

      // Getting a temp. file
      $tempfile = tempnam(sys_get_temp_dir(), 'phing-exectest-');

      // Setting the property
      $this->project->setProperty('execTmpFile', $tempfile);
      $this->executeTarget(__FUNCTION__);

      // Validating the output
      $output = @file_get_contents($tempfile);
      @unlink($tempfile);
      return $this->assertEquals("Append OK\nAppend OK", rtrim($output));
    }

    /**
     * Tests the parallel configuration
     */
    public function testParallel() {
      $this->executeTarget(__FUNCTION__);
      return $this->assertEquals(1, substr_count(implode("\n", $this->logBuffer), 'Command execution :'));
    }


    



    /**********************************************************************************/
    /************************** H E L P E R  M E T H O D S ****************************/
    /**********************************************************************************/
    

    protected function getTargetByName($name) {

      foreach ($this->project->getTargets() as $target) {
        if ($target->getName() == $name) {
          return $target;
        }
      }
      throw new Exception(sprintf('Target "%s" not found', $name));
    }
    
    protected function getTaskFromTarget($target, $taskname, $pos = 0) {
      
      $rchildren = new ReflectionProperty(get_class($target), 'children');
      $rchildren->setAccessible(true);
      $n = -1;
      foreach ($rchildren->getValue($target) as $child) {
        if ($child instanceof Task && ++$n == $pos) {
          return $child;
        }
      }

      throw new Exception( sprintf('%s #%d not found in task', $taskname, $pos) );
    }
    
    protected function getConfiguredTask($target, $task, $pos = 0) {
      $target = $this->getTargetByName($target);
      $task = $this->getTaskFromTarget($target, $task);
      $task->maybeConfigure();
      
      if ($task instanceof UnknownElement) {
          return $task->getRuntimeConfigurableWrapper()->getProxy();
      }
      
      return $task;
    }
    
    protected function assertAttributeIsSetTo($property, $value, $propertyName = null) {
      
      $task = $this->getConfiguredTask( 'testPropertySet' . ucfirst($property), 'ApplyTask' );
      
      $propertyName = ($propertyName === null) ? $property : $propertyName;
      $rprop = new ReflectionProperty('ApplyTask', $propertyName);
      $rprop->setAccessible(true);
      return $this->assertEquals($value, $rprop->getValue($task));
    }
    
    


}
