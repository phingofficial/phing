<?php
/**
 * $Id$
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

require_once 'PHPUnit/Util/Log/JUnit.php';
require_once 'PHPUnit/Util/Log/CodeCoverage/XML/Clover.php';
require_once 'phing/tasks/ext/phpunit/formatter/PHPUnitResultFormatter.php';

/**
 * Prints Clover XML output of the test
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id$
 * @package phing.tasks.ext.formatter
 * @since 2.4.0
 */
class CloverPHPUnitResultFormatter extends PHPUnitResultFormatter
{
    /**
     * @var PHPUnit_Util_Log_CodeCoverage_XML_Clover
     */
    private $clover = NULL;
    
    /**
     * @var PHPUnit_Framework_TestResult
     */
    private $result = NULL;

    public function __construct(Task $parentTask)
    {
        parent::__construct($parentTask);
        
        $this->clover = new PHPUnit_Util_Log_CodeCoverage_XML_Clover(null);
    }

    public function getExtension()
    {
        return ".xml";
    }

    public function getPreferredOutfile()
    {
        return "clover-coverage";
    }

    public function processResult(PHPUnit_Framework_TestResult $result)
    {
        $this->result = $result;
    }

    public function endTestRun()
    {
        ob_start();
        $this->clover->process($this->result);
        $contents = ob_get_contents();
        ob_end_clean();

        if ($this->out)
        {
            $this->out->write($contents);
            $this->out->close();
        }

        parent::endTestRun();
    }
}
