<?php
class ReferenceTest extends \PHPUnit\Framework\TestCase
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
    public function testGetProject()
    {
        $project = new Project();
        $description = "desc" . rand();
        $project->setDescription($description);
        $reference = new Reference($project);
        $retrieved = $reference->getProject();
        $this->assertEquals($retrieved->getDescription(), $description);
    }

    public function testGetReferencedObjectThrowsExceptionIfReferenceNotSet()
    {
        $project = new Project();
        $reference = new Reference($project, "refOne");

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Reference refOne not found.');

        $reference->getReferencedObject(null);
    }

    public function testGetReferencedObjectThrowsExceptionIfNoReferenceIsGiven()
    {
        $project = new Project();
        $reference = new Reference($project);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('No reference specified');

        $reference->getReferencedObject(null);
    }
}
