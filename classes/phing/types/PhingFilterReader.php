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

declare(strict_types=1);

/**
 * A PhingFilterReader is a wrapper class that encloses the className
 * and configuration of a Configurable FilterReader.
 *
 * @see     FilterReader
 *
 * @author  Yannick Lecaillez <yl@seasonfive.com>
 * @package phing.types
 */
class PhingFilterReader extends DataType
{
    private $className;
    private $parameters = [];

    /** @var Path */
    private $classPath;

    /**
     * @param string $className
     *
     * @return void
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Set the classpath to load the FilterReader through (attribute).
     *
     * @param Path $classpath
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws BuildException
     */
    public function setClasspath(Path $classpath): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if ($this->classPath === null) {
            $this->classPath = $classpath;
        } else {
            $this->classPath->append($classpath);
        }
    }

    /*
     * Set the classpath to load the FilterReader through (nested element).
    */

    /**
     * @return Path
     *
     * @throws BuildException
     * @throws Exception
     */
    public function createClasspath(): Path
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }
        if ($this->classPath === null) {
            $this->classPath = new Path($this->project);
        }

        return $this->classPath->createPath();
    }

    /**
     * @return Path|null
     */
    public function getClasspath(): ?Path
    {
        return $this->classPath;
    }

    /**
     * @param Reference $r
     *
     * @return void
     *
     * @throws Exception
     * @throws BuildException
     */
    public function setClasspathRef(Reference $r): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $o = $this->createClasspath();
        $o->setRefid($r);
    }

    /**
     * @param Parameter $param
     *
     * @return void
     */
    public function addParam(Parameter $param): void
    {
        $this->parameters[] = $param;
    }

    /**
     * @return Parameter
     */
    public function createParam(): Parameter
    {
        $num = array_push($this->parameters, new Parameter());

        return $this->parameters[$num - 1];
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        // We return a COPY
        $ret = [];
        for ($i = 0, $size = count($this->parameters); $i < $size; $i++) {
            $ret[] = clone $this->parameters[$i];
        }

        return $ret;
    }

    /*
     * Makes this instance in effect a reference to another PhingFilterReader
     * instance.
     *
     * <p>You must not set another attribute or nest elements inside
     * this element if you make it a reference.</p>
     *
     * @param Reference $r the reference to which this instance is associated
     * @throws BuildException if this instance already has been configured.
     * @return void
     */
    public function setRefid(Reference $r): void
    {
        if ((count($this->parameters) !== 0) || ($this->className !== null)) {
            throw $this->tooManyAttributes();
        }
        $o = $r->getReferencedObject($this->getProject());
        if ($o instanceof PhingFilterReader) {
            $this->setClassName($o->getClassName());
            $this->setClasspath($o->getClasspath());
            foreach ($o->getParams() as $p) {
                $this->addParam($p);
            }
        } else {
            $msg = $r->getRefId() . " doesn\'t refer to a PhingFilterReader";
            throw new BuildException($msg);
        }

        parent::setRefid($r);
    }
}
