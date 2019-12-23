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
 *  Wrapper class that holds the attributes of a Task (or elements
 *  nested below that level) and takes care of configuring that element
 *  at runtime.
 *
 *  <strong>SMART-UP INLINE DOCS</strong>
 *
 * @author  Andreas Aderhold <andi@binarycloud.com>
 * @author  Hans Lellelid <hans@xmpl.org>
 * @package phing
 */
class RuntimeConfigurable
{
    private $elementTag = null;

    /**
     * @var array
     */
    private $children = [];

    /**
     * @var object|Task
     */
    private $wrappedObject = null;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var string
     */
    private $characters = '';

    /**
     * @var bool
     */
    private $proxyConfigured = false;

    /**
     * @param Task|object $proxy
     * @param mixed       $elementTag The element to wrap.
     */
    public function __construct($proxy, $elementTag)
    {
        $this->wrappedObject = $proxy;
        $this->elementTag    = $elementTag;

        if ($proxy instanceof Task) {
            $proxy->setRuntimeConfigurableWrapper($this);
        }
    }

    /**
     * @return object|Task
     */
    public function getProxy()
    {
        return $this->wrappedObject;
    }

    /**
     * @param object|Task $proxy
     *
     * @return void
     */
    public function setProxy($proxy): void
    {
        $this->wrappedObject   = $proxy;
        $this->proxyConfigured = false;
    }

    /**
     * Set's the attributes for the wrapped element.
     *
     * @param array $attributes
     *
     * @return void
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * Returns the AttributeList of the wrapped element.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Adds child elements to the wrapped element.
     *
     * @param RuntimeConfigurable $child
     *
     * @return void
     */
    public function addChild(RuntimeConfigurable $child): void
    {
        $this->children[] = $child;
    }

    /**
     * Returns the child with index
     *
     * @param int $index
     *
     * @return RuntimeConfigurable
     */
    public function getChild(int $index): RuntimeConfigurable
    {
        return $this->children[(int) $index];
    }

    /**
     * Add characters from #PCDATA areas to the wrapped element.
     *
     * @param string $data
     *
     * @return void
     */
    public function addText(string $data): void
    {
        $this->characters .= (string) $data;
    }

    /**
     * Get the text content of this element. Various text chunks are
     * concatenated, there is no way (currently) of keeping track of
     * multiple fragments.
     *
     * @return string the text content of this element.
     */
    public function getText()
    {
        return (string) $this->characters;
    }

    /**
     * @return mixed
     */
    public function getElementTag()
    {
        return $this->elementTag;
    }

    /**
     * Configure the wrapped element and all children.
     *
     * @param Project $project
     *
     * @return void
     *
     * @throws BuildException
     * @throws Exception
     */
    public function maybeConfigure(Project $project): void
    {
        if ($this->proxyConfigured) {
            return;
        }

        $id = null;

        // DataType configured in ProjectConfigurator
        //        if ( is_a($this->wrappedObject, "DataType") )
        //            return;

        if ($this->attributes || (isset($this->characters) && $this->characters != '')) {
            ProjectConfigurator::configure($this->wrappedObject, $this->attributes, $project);

            if (isset($this->attributes['id'])) {
                $id = $this->attributes['id'];
            }

            if (isset($this->characters) && $this->characters != '') {
                ProjectConfigurator::addText($project, $this->wrappedObject, (string) $this->characters);
            }
            if ($id !== null) {
                $project->addReference($id, $this->wrappedObject);
            }
        }

        /*if ( is_array($this->children) && !empty($this->children) ) {
            // Configure all child of this object ...
            foreach ($this->children as $child) {
                $child->maybeConfigure($project);
                ProjectConfigurator::storeChild($project, $this->wrappedObject, $child->wrappedObject, strtolower($child->getElementTag()));
            }
        }*/

        $this->proxyConfigured = true;
    }
}
