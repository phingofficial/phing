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

use PHPUnit\Framework\TestCase;

class ReferenceTest extends TestCase
{
    /**
     * Test getProject method
     *
     * Test that getProject method works conclusively by setting random
     * description and checking for that as the description of the retrieved
     * project - e g not a default/hardcoded description.
     *
     * @return void
     */
    public function testGetProject(): void
    {
        $project     = new Project();
        $description = 'desc' . rand();
        $project->setDescription($description);
        $reference = new Reference($project);
        $retrieved = $reference->getProject();
        $this->assertEquals($retrieved->getDescription(), $description);
    }

    /**
     * @return void
     */
    public function testGetReferencedObjectThrowsExceptionIfReferenceNotSet(): void
    {
        $project   = new Project();
        $reference = new Reference($project, 'refOne');

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Reference refOne not found.');

        $reference->getReferencedObject(null);
    }

    /**
     * @return void
     */
    public function testGetReferencedObjectThrowsExceptionIfNoReferenceIsGiven(): void
    {
        $project   = new Project();
        $reference = new Reference($project);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('No reference specified');

        $reference->getReferencedObject(null);
    }
}
