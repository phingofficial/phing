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

class Ticket309RegressionTest extends BuildFileTest
{
    /**
     * The project.basedir property denotes the project root directory.
     * This root directory can be set by the "basedir" attribute on the
     * <project> tag. It denotes the path to the project root, relative to
     * the buildfile.
     *
     * The default is ".", meaning that the build.xml file is locate in the
     * project root.
     *
     * This test uses several buildfiles that reference the /etc/regression/309
     * directory as their project root in various ways.
     */
    public function testPhingCallTask()
    {
        $testBasedir = str_replace('/', DIRECTORY_SEPARATOR, PHING_TEST_BASE . "/etc/regression/309");

        foreach (['basedir-dot.xml', 'basedir-default.xml', 'sub/basedir-dotdot.xml'] as $buildfile) {
            $this->configureProject("$testBasedir/$buildfile");
            $this->executeTarget("main");
            $this->assertInLogs("project.basedir: $testBasedir");
        }
    }
}
