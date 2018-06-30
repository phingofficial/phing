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

/**
 * This is the interface for selectors that can contain other selectors.
 *
 * @author <a href="mailto:bruce@callenish.com">Bruce Atherton</a>
 *
 * @package phing.types.selectors
 */
interface SelectorContainer
{
    /**
     * Indicates whether there are any selectors here.
     *
     * @return bool whether any selectors are in this container
     */
    public function hasSelectors();

    /**
     * Gives the count of the number of selectors in this container
     *
     * @return int the number of selectors in this container
     */
    public function count();

    /**
     * Returns a *copy* of the set of selectors as an array.
     *
     * @param Project $p
     *
     * @return BaseSelectorContainer[] an array of selectors in this container
     */
    public function getSelectors(Project $p);

    /**
     * Returns an array for accessing the set of selectors.
     *
     * @return BaseSelectorContainer[] an enumerator that goes through each of the selectors
     */
    public function selectorElements();

    /**
     * Add a new selector into this container.
     *
     * @param FileSelector $selector the new selector to add
     *
     * @return FileSelector the selector that was added
     */
    public function appendSelector(FileSelector $selector);

    /* Methods below all add specific selectors */

    /**
     * add a "Select" selector entry on the selector list
     * @param SelectSelector $selector
     */
    public function addSelector(SelectSelector $selector);

    /**
     * add an "And" selector entry on the selector list
     * @param AndSelector $selector
     */
    public function addAnd(AndSelector $selector);

    /**
     * add an "Or" selector entry on the selector list
     * @param OrSelector $selector
     */
    public function addOr(OrSelector $selector);

    /**
     * add a "Not" selector entry on the selector list
     * @param NotSelector $selector
     */
    public function addNot(NotSelector $selector);

    /**
     * add a "None" selector entry on the selector list
     * @param NoneSelector $selector
     */
    public function addNone(NoneSelector $selector);

    /**
     * add a majority selector entry on the selector list
     * @param MajoritySelector $selector
     */
    public function addMajority(MajoritySelector $selector);

    /**
     * add a selector date entry on the selector list
     * @param DateSelector $selector
     */
    public function addDate(DateSelector $selector);

    /**
     * add a selector size entry on the selector list
     * @param SizeSelector $selector
     */
    public function addSize(SizeSelector $selector);

    /**
     * add a selector filename entry on the selector list
     * @param FilenameSelector $selector
     */
    public function addFilename(FilenameSelector $selector);

    /**
     * add an extended selector entry on the selector list
     * @param ExtendSelector $selector
     */
    public function addCustom(ExtendSelector $selector);

    /**
     * add a contains selector entry on the selector list
     * @param ContainsSelector $selector
     */
    public function addContains(ContainsSelector $selector);

    /**
     * add a contains selector entry on the selector list
     * @param ContainsRegexpSelector $selector
     */
    public function addContainsRegexp(ContainsRegexpSelector $selector);

    /**
     * add a present selector entry on the selector list
     * @param PresentSelector $selector
     */
    public function addPresent(PresentSelector $selector);

    /**
     * add a depth selector entry on the selector list
     * @param DepthSelector $selector
     */
    public function addDepth(DepthSelector $selector);

    /**
     * add a depends selector entry on the selector list
     * @param DependSelector $selector
     */
    public function addDepend(DependSelector $selector);

    /**
     * add a different selector entry on the selector list
     * @param DifferentSelector $selector
     */
    public function addDifferent(DifferentSelector $selector);

    /**
     * add a type selector entry on the selector list
     * @param TypeSelector $selector
     */
    public function addType(TypeSelector $selector);

    /**
     * add a executable selector entry on the selector list
     * @param ExecutableSelector $selector
     */
    public function addExecutable(ExecutableSelector $selector);

    /**
     * add a readable selector entry on the selector list
     * @param ReadableSelector $selector
     */
    public function addReadable(ReadableSelector $selector);

    /**
     * add a writable selector entry on the selector list
     * @param WritableSelector $selector
     */
    public function addWritable(WritableSelector $selector);

    /**
     * add a symlink selector entry on the selector list
     * @param SymlinkSelector $selector
     */
    public function addSymlink(SymlinkSelector $selector);
}
