<?php

/*
 *  $Id$
 *
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


require_once 'PHPUnit/Framework/TestCase.php';
include_once 'phing/system/io/FileSystem.php';

/**
 * Unit test for FileSystem
 *
 * @package phing.system.io
 */
class FileSystemTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider fileSystemMappingsDataProvider
     */
    public function testGetFileSystemReturnsCorrect($expectedFileSystemClass, $fsTypeKey)
    {
        Phing::setProperty('host.fstype', $fsTypeKey);
        
        $system = FileSystem::getFileSystem();
        
        $this->assertInstanceOf($expectedFileSystemClass, $system);
    }
    
    public function getFileSystemWithUnknownTypeKeyThrowsException()
    {
        $this->setExpectedException('IOException');
        
        Phing::setProperty('host.fstype', 'UNRECOGNISED');
        
        FileSystem::getFileSystem();
    }
    
    public function fileSystemMappingsDataProvider()
    {
        return array(
            array('UnixFileSystem', 'UNIX'),
            array('Win32FileSystem', 'WIN32'),
            array('WinNTFileSystem', 'WINNT')
        );
    }
}
