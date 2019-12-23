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
 * This is the interface for selectors that can contain other selectors.
 *
 * @author <a href="mailto:bruce@callenish.com">Bruce Atherton</a>
 * @package phing.types.selectors
 */
interface SelectorContainer
{
    /**
     * Indicates whether there are any selectors here.
     *
     * @return bool whether any selectors are in this container
     */
    public function hasSelectors(): bool;

    /**
     * Gives the count of the number of selectors in this container
     *
     * @return int the number of selectors in this container
     */
    public function count(): int;

    /**
     * Returns a *copy* of the set of selectors as an array.
     *
     * @param Project $p
     *
     * @return BaseSelectorContainer[] an array of selectors in this container
     */
    public function getSelectors(Project $p): array;

    /**
     * Returns an array for accessing the set of selectors.
     *
     * @return BaseSelectorContainer[] an enumerator that goes through each of the selectors
     */
    public function selectorElements(): array;

    /**
     * Add a new selector into this container.
     *
     * @param FileSelector $selector the new selector to add
     *
     * @return void
     */
    public function appendSelector(FileSelector $selector): void;

    /* Methods below all add specific selectors */

    /**
     * add a "Select" selector entry on the selector list
     *
     * @param SelectSelector $selector
     *
     * @return void
     */
    public function addSelector(SelectSelector $selector): void;

    /**
     * add an "And" selector entry on the selector list
     *
     * @param AndSelector $selector
     *
     * @return void
     */
    public function addAnd(AndSelector $selector): void;

    /**
     * add an "Or" selector entry on the selector list
     *
     * @param OrSelector $selector
     *
     * @return void
     */
    public function addOr(OrSelector $selector): void;

    /**
     * add a "Not" selector entry on the selector list
     *
     * @param NotSelector $selector
     *
     * @return void
     */
    public function addNot(NotSelector $selector): void;

    /**
     * add a "None" selector entry on the selector list
     *
     * @param NoneSelector $selector
     *
     * @return void
     */
    public function addNone(NoneSelector $selector): void;

    /**
     * add a majority selector entry on the selector list
     *
     * @param MajoritySelector $selector
     *
     * @return void
     */
    public function addMajority(MajoritySelector $selector): void;

    /**
     * add a selector date entry on the selector list
     *
     * @param DateSelector $selector
     *
     * @return void
     */
    public function addDate(DateSelector $selector): void;

    /**
     * add a selector size entry on the selector list
     *
     * @param SizeSelector $selector
     *
     * @return void
     */
    public function addSize(SizeSelector $selector): void;

    /**
     * add a selector filename entry on the selector list
     *
     * @param FilenameSelector $selector
     *
     * @return void
     */
    public function addFilename(FilenameSelector $selector): void;

    /**
     * add an extended selector entry on the selector list
     *
     * @param ExtendSelector $selector
     *
     * @return void
     */
    public function addCustom(ExtendSelector $selector): void;

    /**
     * add a contains selector entry on the selector list
     *
     * @param ContainsSelector $selector
     *
     * @return void
     */
    public function addContains(ContainsSelector $selector): void;

    /**
     * add a contains selector entry on the selector list
     *
     * @param ContainsRegexpSelector $selector
     *
     * @return void
     */
    public function addContainsRegexp(ContainsRegexpSelector $selector): void;

    /**
     * add a present selector entry on the selector list
     *
     * @param PresentSelector $selector
     *
     * @return void
     */
    public function addPresent(PresentSelector $selector): void;

    /**
     * add a depth selector entry on the selector list
     *
     * @param DepthSelector $selector
     *
     * @return void
     */
    public function addDepth(DepthSelector $selector): void;

    /**
     * add a depends selector entry on the selector list
     *
     * @param DependSelector $selector
     *
     * @return void
     */
    public function addDepend(DependSelector $selector): void;

    /**
     * add a different selector entry on the selector list
     *
     * @param DifferentSelector $selector
     *
     * @return void
     */
    public function addDifferent(DifferentSelector $selector): void;

    /**
     * add a type selector entry on the selector list
     *
     * @param TypeSelector $selector
     *
     * @return void
     */
    public function addType(TypeSelector $selector): void;

    /**
     * add a executable selector entry on the selector list
     *
     * @param ExecutableSelector $selector
     *
     * @return void
     */
    public function addExecutable(ExecutableSelector $selector): void;

    /**
     * add a readable selector entry on the selector list
     *
     * @param ReadableSelector $selector
     *
     * @return void
     */
    public function addReadable(ReadableSelector $selector): void;

    /**
     * add a writable selector entry on the selector list
     *
     * @param WritableSelector $selector
     *
     * @return void
     */
    public function addWritable(WritableSelector $selector): void;

    /**
     * add a symlink selector entry on the selector list
     *
     * @param SymlinkSelector $selector
     *
     * @return void
     */
    public function addSymlink(SymlinkSelector $selector): void;
}
