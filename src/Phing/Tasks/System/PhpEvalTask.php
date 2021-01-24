<?php
/**
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

namespace Phing\Tasks\System;

use Phing\Exception\BuildException;
use Phing\Phing;
use Phing\Project;
use Phing\Task;
use Phing\Type\Parameter;
use Phing\Util\StringHelper;

/**
 * Executes PHP function or evaluates expression and sets return value to a property.
 *
 *    WARNING:
 *        This task can, of course, be abused with devastating effects.  E.g. do not
 *        modify internal Phing classes unless you know what you are doing.
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 * @package phing.tasks.system
 *
 * @todo Add support for evaluating expressions
 */
class PhpEvalTask extends Task
{
    use LogLevelAware;

    protected $expression; // Expression to evaluate
    protected $function; // Function to execute
    protected $class; // Class containing function to execute
    protected $returnProperty = null; // name of property to set to return value
    protected $params = []; // parameters for function calls

    public function init()
    {
        $this->logLevel = Project::MSG_INFO;
    }

    /**
     * Main entry point.
     */
    public function main()
    {
        if ($this->function === null && $this->expression === null) {
            throw new BuildException(
                "You must specify a function to execute or PHP expression to evalute.",
                $this->getLocation()
            );
        }

        if ($this->function !== null && $this->expression !== null) {
            throw new BuildException("You can specify function or expression, but not both.", $this->getLocation());
        }

        if ($this->expression !== null && !empty($this->params)) {
            throw new BuildException(
                "You cannot use nested <param> tags when evaluationg a PHP expression.",
                $this->getLocation()
            );
        }

        if ($this->function !== null) {
            $this->callFunction();
        } elseif ($this->expression !== null) {
            $this->evalExpression();
        }
    }

    /**
     * Calls function and returns results.
     *
     * @return mixed
     */
    protected function callFunction()
    {
        if ($this->class !== null) {
            // import the classname & unqualify it, if necessary
            $this->class = Phing::import($this->class);

            $user_func = [$this->class, $this->function];
            $h_func = $this->class . '::' . $this->function; // human-readable (for log)
        } else {
            $user_func = $this->function;
            $h_func = $user_func; // human-readable (for log)
        }

        // put parameters into simple array
        $params = [];
        foreach ($this->params as $p) {
            if ($p instanceof Parameter) {
                $value = $p->getParams();
                array_walk_recursive(
                    $value,
                    function (&$item) {
                        if ($item instanceof Parameter) {
                            $item = $item->getValue();
                        }
                    }
                );
                if ($value === null) {
                    continue;
                }
                $params[] = $value;
            } else {
                $params[] = $p->getValue();
            }
        }

        $this->log("Calling PHP function: " . $h_func . "()", $this->logLevel);
        foreach ($params as $p) {
            $this->log("  param: " . print_r($p, true), Project::MSG_VERBOSE);
        }

        $return = call_user_func_array($user_func, $params);

        if ($this->returnProperty !== null) {
            $this->project->setProperty($this->returnProperty, $return);
        }
    }

    private function resolveParams(&$iterator)
    {
        foreach ($iterator as &$value) {
            if (is_string($value)) {
                continue;
            }
            if (is_array($value->getValue())) {
                $this->resolveParams($value->getValue());
            }
            $value = is_string($value) ? $value : $value->getValue();
        }

        return $iterator;
    }

    /**
     * Evaluates expression and returns resulting value.
     *
     * @return mixed
     */
    protected function evalExpression()
    {
        $this->log("Evaluating PHP expression: " . $this->expression, $this->logLevel);
        if (!StringHelper::endsWith(';', trim($this->expression))) {
            $this->expression .= ';';
        }

        if ($this->returnProperty !== null) {
            $retval = null;
            eval('$retval = ' . $this->expression);
            $this->project->setProperty($this->returnProperty, $retval);
        } else {
            eval($this->expression);
        }
    }

    /**
     * Set function to execute
     *
     * @param $f
     */
    public function setFunction($f)
    {
        $this->function = $f;
    }

    /**
     * Set [static] class which contains function to execute
     *
     * @param $c
     */
    public function setClass($c)
    {
        $this->class = $c;
    }

    /**
     * Sets property name to set with return value of function or expression.
     *
     * @param $r
     */
    public function setReturnProperty($r)
    {
        $this->returnProperty = $r;
    }

    /**
     * Set PHP expression to evaluate.
     *
     * @param $expression
     */
    public function addText($expression)
    {
        $this->expression = $expression;
    }

    /**
     * Set PHP expression to evaluate.
     *
     * @param $expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    /**
     * Add a nested <param> tag.
     */
    public function addParam(Parameter $p)
    {
        $this->params[] = $p;
    }
}
