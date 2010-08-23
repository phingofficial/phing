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
 
require_once 'phing/Task.php';

/**
 * Base class for Git tasks
 *
 * @author Victor Farazdagi <simple.square@gmail.com>
 * @version $Id$
 * @package phing.tasks.ext.git
 * @see VersionControl_Git
 * @since 2.4.3
 */
abstract class GitBaseTask extends Task
{
    private $gitPath = '/usr/bin/git';

    /**
     * Initialize Task.
     * Check and include necessary libraries.
     */
    public function init() 
    {
        require_once 'VersionControl/Git.php';
        if (false == class_exists('VersionControl_Git')) {
            throw new Exception("The Git tasks depend on PEAR\'s " 
                              . "VersionControl_Git package.");
        }
    }

    /**
     * Set path to git executable
     * @param string $gitPath New path to git repository
     * @return GitBaseTask
     */
    public function setGitPath($gitPath)
    {
        $this->gitPath = $gitPath;
        return $this;
    }

    /**
     * Get path to git executable
     *
     * @return string
     */
    public function getGitPath()
    {
        return $this->gitPath;
    }
}



