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
 * This is the base class for selectors that can contain other selectors.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.types.selectors
 */
abstract class AbstractSelectorContainer extends DataType implements SelectorContainer
{
    use SelectorAware;

    /**
     * Performs the check for circular references and returns the
     * referenced FileSet.
     *
     * @param Project $p
     *
     * @return FileSet
     *
     * @throws BuildException
     */
    public function getRef(Project $p)
    {
        $dataTypeName = StringHelper::substring(self::class, strrpos(self::class, '\\') + 1);
        return $this->getCheckedRef(self::class, $dataTypeName);
    }

    /**
     * Indicates whether there are any selectors here.
     *
     * @return bool Whether any selectors are in this container
     *
     * @throws ReflectionException
     */
    public function hasSelectors(): bool
    {
        if ($this->isReference() && $this->getProject() !== null) {
            return $this->getRef($this->getProject())->hasSelectors();
        }

        return !empty($this->selectorsList);
    }

    /**
     * Convert the Selectors within this container to a string. This will
     * just be a helper class for the subclasses that put their own name
     * around the contents listed here.
     *
     * @return string comma separated list of Selectors contained in this one
     */
    public function __toString(): string
    {
        try {
            return implode(', ', $this->selectorElements());
        } catch (ReflectionException $e) {
            return '';
        }
    }

    /**
     * <p>
     * This validates each contained selector
     * provided that the selector implements the validate interface.
     * </p>
     * <p>Ordinarily, this will validate all the elements of a selector
     * container even if the isSelected() method of some elements is
     * never called. This has two effects:</p>
     * <ul>
     * <li>Validation will often occur twice.
     * <li>Since it is not required that selectors derive from
     * BaseSelector, there could be selectors in the container whose
     * error conditions are not detected if their isSelected() call
     * is never made.
     * </ul>
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function validate(): void
    {
        if ($this->isReference()) {
            $dataTypeName = StringHelper::substring(self::class, strrpos(self::class, '\\') + 1);
            $this->getCheckedRef(self::class, $dataTypeName)->validate();
        }
        $selectorElements = $this->selectorElements();
        $this->dieOnCircularReference($selectorElements, $this->getProject());
        foreach ($selectorElements as $o) {
            if ($o instanceof BaseSelector) {
                $o->validate();
            }
        }
    }

    /**
     * Gives the count of the number of selectors in this container
     *
     * @return int The number of selectors in this container
     *
     * @throws Exception
     */
    public function count(): int
    {
        if ($this->isReference() && $this->getProject() !== null) {
            try {
                return $this->getRef($this->getProject())->count();
            } catch (Throwable $e) {
                throw $e;
            }
        }

        return count($this->selectorsList);
    }

    /**
     * Returns the set of selectors as an array.
     *
     * @param Project $p
     *
     * @return BaseSelectorContainer[] of selectors in this container
     *
     * @throws ReflectionException
     * @throws BuildException
     */
    public function getSelectors(Project $p): array
    {
        if ($this->isReference()) {
            return $this->getRef($p)->getSelectors($p);
        }

// *copy* selectors
        $result = [];
        for ($i = 0, $size = count($this->selectorsList); $i < $size; $i++) {
            $result[] = clone $this->selectorsList[$i];
        }

        return $result;
    }

    /**
     * Returns an array for accessing the set of selectors.
     *
     * @return BaseSelectorContainer[] The array of selectors
     *
     * @throws ReflectionException
     */
    public function selectorElements(): array
    {
        if ($this->isReference() && $this->getProject() !== null) {
            return $this->getRef($this->getProject())->selectorElements();
        }

        return $this->selectorsList;
    }

    /**
     * Add a new selector into this container.
     *
     * @param FileSelector $selector new selector to add
     *
     * @return void
     *
     * @throws BuildException
     */
    public function appendSelector(FileSelector $selector): void
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }
        $this->selectorsList[] = $selector;
    }

    /**
     * @param array        $stk
     * @param Project|null $p
     *
     * @return void
     */
    public function dieOnCircularReference(array &$stk, ?Project $p = null): void
    {
        if ($this->checked) {
            return;
        }

        if ($this->isReference()) {
            parent::dieOnCircularReference($stk, $p);
        } else {
            foreach ($this->selectorsList as $fileSelector) {
                if ($fileSelector instanceof DataType) {
                    self::pushAndInvokeCircularReferenceCheck($fileSelector, $stk, $p);
                }
            }
            $this->checked = true;
        }
    }
}
