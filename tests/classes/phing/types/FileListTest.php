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

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Project;
use PHPUnit\Framework\TestCase;

class FileListTest extends TestCase
{
    /**
     * @var Project
     */
    private $project;

    public function setUp(): void
    {
        $this->project = new Project();
        $this->project->setBasedir(PHING_TEST_BASE);
    }

    public function testGetFilesWithEmptyDir()
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('No directory specified for filelist.');

        $f = new FileList();
        $f->getFiles($this->project);
    }

    public function testGetFilesWithNoFilenames()
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('No files specified for filelist.');

        $f = new FileList();
        $f->setDir(new File("."));
        $f->getFiles($this->project);
    }
    public function testSetRefidWithDirSet()
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage("You must not specify more than one attribute when using refid");

        $f = new FileList();
        $f->setDir(new File("."));
        $project = new Project();
        $project->setBasedir(__DIR__);
        $f->setRefid(new Reference($this->project, "dummy"));
    }

    public function testSetRefidWithFileListSet()
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage("You must not specify more than one attribute when using refid");

        $f = new FileList();
        $f->setFiles('foo.php');
        $project = new Project();
        $project->setBasedir(__DIR__);
        $f->setRefid(new Reference($this->project, "dummy"));
    }
    public function testSetListfile()
    {
        $f = new FileList();
        $f->setListFile("foo.php");
        $project = new Project();
        $project->setBasedir(__DIR__);
        $l = $f->getListFile($project);
        $this->assertEquals($l->getPath(), "foo.php");
    }
}
