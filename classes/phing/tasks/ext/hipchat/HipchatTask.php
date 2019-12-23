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

/**
 * HipchatTask
 * Sends a simple hipchat notification.
 *
 * (Yeah, HipChat API has lots of more awesome features than sending a lousy text notification
 *  but I refuse implementing more as long as the chat client lacks the most basic feature of
 *  sorting contacts. If you share my opinion then please upvote this feature request:
 *  https://jira.atlassian.com/browse/HCPUB-363 )
 *
 * <hipchat room="1337" authToken="********" color="red" notify="true" format="html">
 *     Hello &lt;i&gt;World&lt;/i&gt;!
 * </hipchat>
 *
 * @author  Suat Özgür <suat.oezguer@mindgeek.com>
 * @package phing.tasks.ext
 */
class HipchatTask extends Task
{
    /**
     * @var string
     */
    private $domain = 'api.hipchat.com';

    /**
     * @var string|null
     */
    private $room = null;

    /**
     * @var string|null
     */
    private $authToken = null;

    /**
     * @var string
     */
    private $color = 'yellow';

    /**
     * @var bool
     */
    private $notify = false;

    /**
     * @var string|null
     */
    private $message = null;

    /**
     * @var string
     */
    private $format = 'text';

    /**
     * @return void
     *
     * @throws Exception
     */
    public function main(): void
    {
        if (null === $this->getRoom()) {
            throw new BuildException('(HipChat) room is not defined');
        }

        if (null === $this->getAuthToken()) {
            throw new BuildException('(HipChat) authToken is not defined');
        }

        $url =
            'https://' .
            $this->getDomain() .
            '/v2/room/' .
            $this->getRoom() .
            '/notification?auth_token=' .
            $this->getAuthToken();

        $data = [
            'color' => $this->getColor(),
            'message' => $this->getMessage(),
            'notify' => $this->isNotify(),
            'message_format' => $this->getFormat(),
        ];

        $result = $this->executeApiCall($url, $data);
        if ($result !== true) {
            $this->log($result, Project::MSG_WARN);
        } else {
            $this->log('HipChat notification sent.');
        }
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     *
     * @return void
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param string $format
     *
     * @return void
     */
    public function setFormat(string $format): void
    {
        $format       = $format != 'text' && $format != 'html' ? 'text' : $format;
        $this->format = $format;
    }

    /**
     * @return string|null
     */
    public function getRoom(): ?string
    {
        return $this->room;
    }

    /**
     * @param string $room
     *
     * @return void
     */
    public function setRoom(string $room): void
    {
        $this->room = $room;
    }

    /**
     * @return string|null
     */
    public function getAuthToken(): ?string
    {
        return $this->authToken;
    }

    /**
     * @param string $authToken
     *
     * @return void
     */
    public function setAuthToken(string $authToken): void
    {
        $this->authToken = $authToken;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @param string $color
     *
     * @return void
     */
    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function isNotify(): bool
    {
        return $this->notify;
    }

    /**
     * @param bool $notify
     *
     * @return void
     */
    public function setNotify(bool $notify): void
    {
        $this->notify = $notify;
    }

    /**
     * @param string $message
     *
     * @return void
     */
    public function addText(string $message): void
    {
        $this->message = trim($message);
    }

    /**
     * @param string $url
     * @param mixed  $data
     *
     * @return bool|string
     */
    private function executeApiCall(string $url, $data)
    {
        $postData = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        $response = curl_exec($ch);
        if ($response !== '') {
            $result = json_decode($response, 1);
            return $result['error']['message'] . ' (' . $result['error']['code'] . ')';
        }
        return true;
    }
}
