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
 * Filename Mapper maps source file name(s) to target file name(s).
 *
 * Built-in mappers can be accessed by specifying they "type" attribute:
 * <code>
 * <mapper type="glob" from="*.php" to="*.php.bak"/>
 * </code>
 * Custom mappers can be specified by providing a dot-path to a include_path-relative
 * class:
 * <code>
 * <mapper classname="myapp.mappers.DevToProdMapper" from="*.php" to="*.php"/>
 * <!-- maps all PHP files from development server to production server, for example -->
 * </code>
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 * @package phing.types
 */
class Mapper extends DataType
{
    protected $type;
    protected $classname;
    protected $from;
    protected $to;

    /**
     * @var Path $classpath
     */
    protected $classpath;
    protected $classpathId;

    /**
     * @var ContainerMapper $container
     */
    private $container = null;

    /**
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        parent::__construct();
        $this->project = $project;
    }

    /**
     * Set the classpath to be used when searching for component being defined
     *
     * @param Path $classpath An Path object containing the classpath.
     *
     * @return void
     *
     * @throws BuildException
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     */
    public function setClasspath(Path $classpath): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if ($this->classpath === null) {
            $this->classpath = $classpath;
        } else {
            $this->classpath->append($classpath);
        }
    }

    /**
     * Create the classpath to be used when searching for component being defined
     *
     * @return Path
     *
     * @throws Exception
     */
    public function createClasspath(): Path
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if ($this->classpath === null) {
            $this->classpath = new Path($this->project);
        }

        return $this->classpath->createPath();
    }

    /**
     * Reference to a classpath to use when loading the files.
     *
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
        $this->classpathId = $r->getRefId();
        $this->createClasspath()->setRefid($r);
    }

    /**
     * Set the type of FileNameMapper to use.
     *
     * @param string $type
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setType(string $type): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->type = $type;
    }

    /**
     * Add a nested <code>FileNameMapper</code>.
     *
     * @param FileNameMapper $fileNameMapper the <code>FileNameMapper</code> to add.
     *
     * @return void
     *
     * @throws ConfigurationException
     * @throws BuildException
     */
    public function add(FileNameMapper $fileNameMapper): void
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }
        if ($this->container == null) {
            if ($this->type == null && $this->classname == null) {
                $this->container = new CompositeMapper();
            } else {
                $m = $this->getImplementation();
                if ($m instanceof ContainerMapper) {
                    $this->container = $m;
                } else {
                    throw new BuildException($m . ' mapper implementation does not support nested mappers!');
                }
            }
        }
        $this->container->add($fileNameMapper);
        $this->checked = false;
    }

    /**
     * Add a Mapper
     *
     * @param FileNameMapper $mapper the mapper to add
     *
     * @return void
     *
     * @throws ConfigurationException
     */
    public function addMapper(FileNameMapper $mapper): void
    {
        $this->add($mapper);
    }

    /**
     * Set the class name of the FileNameMapper to use.
     *
     * @param string $classname
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setClassname(string $classname): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->classname = $classname;
    }

    /**
     * Set the argument to FileNameMapper.setFrom
     *
     * @param string $from
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setFrom($from): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->from = $from;
    }

    /**
     * Set the argument to FileNameMapper.setTo
     *
     * @param string $to
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setTo(string $to): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->to = $to;
    }

    /**
     * Make this Mapper instance a reference to another Mapper.
     *
     * You must not set any other attribute if you make it a reference.
     *
     * @param Reference $r
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setRefid(Reference $r): void
    {
        if ($this->type !== null || $this->from !== null || $this->to !== null) {
            throw DataType::tooManyAttributes();
        }
        parent::setRefid($r);
    }

    /**
     * Factory, returns inmplementation of file name mapper as new instance
     *
     * @return ContainerMapper|FileNameMapper
     *
     * @throws ConfigurationException
     */
    public function getImplementation()
    {
        if ($this->isReference()) {
            $o = $this->getRef();
            if ($o instanceof FileNameMapper) {
                return $o;
            }
            if ($o instanceof Mapper) {
                return $o->getImplementation();
            }

            $od = $o == null ? 'null' : get_class($o);
            throw new BuildException($od . " at reference '" . $this->getRefId() . "' is not a valid mapper reference.");
        }

        if ($this->type === null && $this->classname === null && $this->container == null) {
            throw new BuildException('either type or classname attribute must be set for <mapper>');
        }

        if ($this->container != null) {
            return $this->container;
        }

        if ($this->type !== null) {
            switch ($this->type) {
                case 'chained':
                    $this->classname = 'phing.mappers.ChainedMapper';
                    break;
                case 'composite':
                    $this->classname = 'phing.mappers.CompositeMapper';
                    break;
                case 'cutdirs':
                    $this->classname = 'phing.mappers.CutDirsMapper';
                    break;
                case 'identity':
                    $this->classname = 'phing.mappers.IdentityMapper';
                    break;
                case 'firstmatch':
                    $this->classname = 'phing.mappers.FirstMatchMapper';
                    break;
                case 'flatten':
                    $this->classname = 'phing.mappers.FlattenMapper';
                    break;
                case 'glob':
                    $this->classname = 'phing.mappers.GlobMapper';
                    break;
                case 'regexp':
                case 'regex':
                    $this->classname = 'phing.mappers.RegexpMapper';
                    break;
                case 'merge':
                    $this->classname = 'phing.mappers.MergeMapper';
                    break;
                default:
                    throw new BuildException(sprintf('Mapper type %s not known', $this->type));
                    break;
            }
        }

        // get the implementing class
        $cls = Phing::import($this->classname, $this->classpath);

        $m = new $cls();
        $m->setFrom($this->from);
        $m->setTo($this->to);

        return $m;
    }

    /**
     * Performs the check for circular references and returns the referenced Mapper.
     *
     * @return mixed
     */
    private function getRef()
    {
        $dataTypeName = StringHelper::substring(self::class, strrpos(self::class, '\\') + 1);
        return $this->getCheckedRef(self::class, $dataTypeName);
    }
}
