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
 * This selector just holds one other selector and forwards all
 * requests to it. It exists so that there is a single selector
 * type that can exist outside of any targets, as an element of
 * project. It overrides all of the reference stuff so that it
 * works as expected. Note that this is the only selector you
 * can reference.
 *
 * @author  Hans Lellelid <hans@xmpl.org> (Phing)
 * @author  Bruce Atherton <bruce@callenish.com> (Ant)
 * @package phing.types.selectors
 */
class SelectSelector extends AndSelector
{
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->hasSelectors() ? sprintf('{select: %s}', parent::__toString()) : '';
    }

    /**
     * Performs the check for circular references and returns the
     * referenced Selector.
     *
     * @return mixed
     */
    private function getRef()
    {
        return $this->getCheckedRef(static::class, 'SelectSelector');
    }

    /**
     * Indicates whether there are any selectors here.
     *
     * @return bool
     */
    public function hasSelectors(): bool
    {
        return $this->isReference() ? $this->getRef()->hasSelectors() : parent::hasSelectors();
    }

    /**
     * Gives the count of the number of selectors in this container
     *
     * @return int
     */
    public function count(): int
    {
        if ($this->isReference()) {
            return count($this->getRef());
        }

        return parent::count();
    }

    /**
     * Returns the set of selectors as an array.
     *
     * @param Project $p
     *
     * @return BaseSelectorContainer[]
     */
    public function getSelectors(Project $p): array
    {
        return $this->isReference() ? $this->getRef()->getSelectors($p) : parent::getSelectors($p);
    }

    /**
     * Returns an enumerator for accessing the set of selectors.
     *
     * @return BaseSelectorContainer[]
     */
    public function selectorElements(): array
    {
        return $this->isReference() ? $this->getRef()->selectorElements() : parent::selectorElements();
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
        parent::appendSelector($selector);
    }

    /**
     * Makes sure that there is only one entry, sets an error message if
     * not.
     *
     * @return void
     */
    public function verifySettings(): void
    {
        if ($this->count() != 1) {
            $this->setError(
                'One and only one selector is allowed within the '
                . '<selector> tag'
            );
        }
    }
}
