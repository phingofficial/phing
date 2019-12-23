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
 * FilterChain may contain a chained set of filter readers.
 *
 * @author  Yannick Lecaillez <yl@seasonfive.com>
 * @package phing.types
 */
class FilterChain extends DataType
{
    private $filterReaders = [];

    /**
     * @param Project|null $project
     */
    public function __construct(?Project $project = null)
    {
        parent::__construct();

        if ($project) {
            $this->project = $project;
        }
    }

    /**
     * @return array
     */
    public function getFilterReaders(): array
    {
        return $this->filterReaders;
    }

    /**
     * @param ConcatFilter $o
     *
     * @return void
     */
    public function addConcatFilter(ConcatFilter $o): void
    {
        $this->add($o);
    }

    /**
     * @param ExpandProperties $o
     *
     * @return void
     */
    public function addExpandProperties(ExpandProperties $o): void
    {
        $this->add($o);
    }

    /**
     * @param TranslateGettext $o
     *
     * @return void
     */
    public function addGettext(TranslateGettext $o): void
    {
        $this->add($o);
    }

    /**
     * @param HeadFilter $o
     *
     * @return void
     */
    public function addHeadFilter(HeadFilter $o): void
    {
        $this->add($o);
    }

    /**
     * @param IconvFilter $o
     *
     * @return void
     */
    public function addIconvFilter(IconvFilter $o): void
    {
        $this->add($o);
    }

    /**
     * @param TailFilter $o
     *
     * @return void
     */
    public function addTailFilter(TailFilter $o): void
    {
        $this->add($o);
    }

    /**
     * @param LineContains $o
     *
     * @return void
     */
    public function addLineContains(LineContains $o): void
    {
        $this->add($o);
    }

    /**
     * @param LineContainsRegexp $o
     *
     * @return void
     */
    public function addLineContainsRegExp(LineContainsRegexp $o): void
    {
        $this->add($o);
    }

    /**
     * @param PrefixLines $o
     *
     * @return void
     */
    public function addPrefixLines(PrefixLines $o): void
    {
        $this->add($o);
    }

    /**
     * @param SuffixLines $o
     *
     * @return void
     */
    public function addSuffixLines(SuffixLines $o): void
    {
        $this->add($o);
    }

    /**
     * @param EscapeUnicode $o
     *
     * @return void
     */
    public function addEscapeUnicode(EscapeUnicode $o): void
    {
        $this->add($o);
    }

    /**
     * @param PhpArrayMapLines $o
     *
     * @return void
     */
    public function addPhpArrayMapLines(PhpArrayMapLines $o): void
    {
        $this->add($o);
    }

    /**
     * @param ReplaceTokens $o
     *
     * @return void
     */
    public function addReplaceTokens(ReplaceTokens $o): void
    {
        $this->add($o);
    }

    /**
     * @param ReplaceTokensWithFile $o
     *
     * @return void
     */
    public function addReplaceTokensWithFile(ReplaceTokensWithFile $o): void
    {
        $this->add($o);
    }

    /**
     * @param ReplaceRegexp $o
     *
     * @return void
     */
    public function addReplaceRegexp(ReplaceRegexp $o): void
    {
        $this->add($o);
    }

    /**
     * @param StripPhpComments $o
     *
     * @return void
     */
    public function addStripPhpComments(StripPhpComments $o): void
    {
        $this->add($o);
    }

    /**
     * @param StripLineBreaks $o
     *
     * @return void
     */
    public function addStripLineBreaks(StripLineBreaks $o): void
    {
        $this->add($o);
    }

    /**
     * @param StripLineComments $o
     *
     * @return void
     */
    public function addStripLineComments(StripLineComments $o): void
    {
        $this->add($o);
    }

    /**
     * @param StripWhitespace $o
     *
     * @return void
     */
    public function addStripWhitespace(StripWhitespace $o): void
    {
        $this->add($o);
    }

    /**
     * @param TidyFilter $o
     *
     * @return void
     */
    public function addTidyFilter(TidyFilter $o): void
    {
        $this->add($o);
    }

    /**
     * @param TabToSpaces $o
     *
     * @return void
     */
    public function addTabToSpaces(TabToSpaces $o): void
    {
        $this->add($o);
    }

    /**
     * @param XincludeFilter $o
     *
     * @return void
     */
    public function addXincludeFilter(XincludeFilter $o): void
    {
        $this->add($o);
    }

    /**
     * @param XsltFilter $o
     *
     * @return void
     */
    public function addXsltFilter(XsltFilter $o): void
    {
        $this->add($o);
    }

    /**
     * @param PhingFilterReader $o
     *
     * @return void
     */
    public function addFilterReader(PhingFilterReader $o): void
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param SortFilter $o
     *
     * @return void
     */
    public function addSortFilter(SortFilter $o): void
    {
        $this->add($o);
    }

    /**
     * @param BaseFilterReader $o
     *
     * @return void
     */
    private function add(BaseFilterReader $o): void
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /*
     * Makes this instance in effect a reference to another FilterChain
     * instance.
     *
     * <p>You must not set another attribute or nest elements inside
     * this element if you make it a reference.</p>
     *
     * @param Reference $r The reference to which this instance is associated
     *
     * @throws BuildException If this instance already has been configured.
     * @return void
     */
    public function setRefid(Reference $r): void
    {
        if (count($this->filterReaders) !== 0) {
            throw $this->tooManyAttributes();
        }

        // change this to get the objects from the other reference
        $o = $r->getReferencedObject($this->getProject());
        if ($o instanceof FilterChain) {
            $this->filterReaders = $o->getFilterReaders();
        } else {
            throw new BuildException($r->getRefId() . " doesn't refer to a FilterChain");
        }
        parent::setRefid($r);
    }
}
