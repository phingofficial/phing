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

namespace Phing\Test;

use Phing\Project;
use Phing\PropertyHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class PropertyHelperTest extends TestCase
{
    public function testUndefinedPropertyShouldNotBeReplaced(): void
    {
        $project = new Project();
        $helper = PropertyHelper::getPropertyHelper($project);

        $value = $helper->replaceProperties('${undefined.property}', []);

        $this->assertEquals('${undefined.property}', $value);
    }

    public function testDefinedPropertyShouldBeReplacedWithPropertyValue(): void
    {
        $project = new Project();
        $helper = PropertyHelper::getPropertyHelper($project);

        $value = $helper->replaceProperties('${defined.property}', ['defined.property' => 'abc123']);

        $this->assertEquals('abc123', $value);
    }
}
