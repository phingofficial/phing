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
 * @author  Siad A6rdroumli <siad.ardroumli@gmail.com>
 * @package phing.filters
 */
class SortFilterTest extends BuildFileTest
{
    protected $fu;

    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/filters/sortfilter.xml");
        $this->fu = new FileUtils();
    }

    public function tearDown(): void
    {
        $this->executeTarget("cleanup");
    }

    public function testSortFilter()
    {
        $this->executeTarget("testSortFilter");

        $expected = $this->getProject()->resolveFile("expected/sortfilter.test");
        $result   = $this->getProject()->resolveFile("result/sortfilter.test");
        $this->assertTrue($this->fu->contentEquals($expected, $result), "Files don't match!");
    }
}
