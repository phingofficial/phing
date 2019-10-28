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

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage Reference refOne not found.
     */
    public function testGetReferencedObjectThrowsExceptionIfReferenceNotSet()
    {
        $project = new Project();
        $reference = new Reference($project, "refOne");
        $referenced = $reference->getReferencedObject(null);
    }

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage No reference specified
     */
    public function testGetReferencedObjectThrowsExceptionIfNoReferenceIsGiven()
    {
        $project = new Project();
        $reference = new Reference($project);
        $referenced = $reference->getReferencedObject(null);
    }
}
