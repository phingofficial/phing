<?php

/*
 * $Id$
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
 * Includes a PHP file.
 *
 * <code>
 *   <phpinclude file="include/file/here"/>
 * </code>
 *
 * @author    Joshua Spence <josh@joshuaspence.com>
 * @version   $Id$
 * @package   phing.tasks.system
 */
class PhpIncludeTask extends Task {
    /** File of the path to be included. */
    private $file;

    public function setFile($file) {
        $this->file = (string) $file;
    }

    /** Main entry point */
    public function main() {
        // Apparently casting to (string) no longer invokes __toString() automatically.
        if ($this->file === null) {
            throw new BuildException("File is required.");
        }

        $this->log("Including file: " . $this->file, Project::MSG_VERBOSE);
        @include_once($this->file);
    }
}
