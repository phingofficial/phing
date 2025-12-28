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

namespace Phing\Listener;

use Phing\Util\Regexp;

/**
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class DisguiseLogger extends DefaultLogger
{
    public function messageLogged(BuildEvent $event)
    {
        $this->maskUriPassword($event);
        $this->maskGitHubTokens($event);
        parent::messageLogged($event);
    }

    public function buildStarted(BuildEvent $event)
    {
    }

    public function buildFinished(BuildEvent $event)
    {
    }

    public function targetStarted(BuildEvent $event)
    {
    }

    public function targetFinished(BuildEvent $event)
    {
    }

    public function taskStarted(BuildEvent $event)
    {
    }

    public function taskFinished(BuildEvent $event)
    {
    }

    protected function maskUriPassword(BuildEvent $event): void
    {
        $event->setMessage(
            preg_replace(
                '!://(.*):(.*)@!',
                '://$1:*****@',
                $event->getMessage()
            ),
            $event->getPriority()
        );
    }

    /**
     * Mask GitHub tokens.
     *
     * @see https://learn.microsoft.com/en-us/purview/sit-defn-github-personal-access-token#pattern
     */
    protected function maskGitHubTokens(BuildEvent $event): void
    {
        $regex = new Regexp();
        $regex->setPattern('(ghp_|gho_|ghu_|ghs_|ghr_)\w{1,36}');
        $regex->setReplace('$1*****');
        $maskedMessage = $regex->replace($event->getMessage());
        $event->setMessage($maskedMessage, $event->getPriority());
    }
}
