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
 * Send an e-mail message
 *
 * <mail tolist="user@example.org" subject="build complete">The build process is a success...</mail>
 *
 * @author  Michiel Rook <mrook@php.net>
 * @author  Francois Harvey at SecuriWeb (http://www.securiweb.net)
 * @package phing.tasks.ext
 */
class MailTask extends Task
{
    use FileSetAware;

    /**
     * @var string|null
     */
    protected $tolist = null;

    /**
     * @var string|null
     */
    protected $subject = null;

    /**
     * @var string|null
     */
    protected $msg = null;

    /**
     * @var string|null
     */
    protected $from = null;

    /**
     * @var string
     */
    protected $backend = 'mail';

    /**
     * @var array
     */
    protected $backendParams = [];

    /**
     * @return void
     *
     * @throws Exception
     */
    public function main(): void
    {
        if (empty($this->from)) {
            throw new BuildException('Missing "from" attribute');
        }

        $this->log('Sending mail to ' . $this->tolist);

        if (!empty($this->filesets)) {
            $this->sendFilesets();

            return;
        }

        mail($this->tolist, $this->subject, $this->msg, sprintf("From: %s\n", $this->from));
    }

    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     */
    protected function sendFilesets(): void
    {
        @include_once 'Mail.php';
        @include_once 'Mail/mime.php';

        if (!class_exists('Mail_mime')) {
            throw new BuildException('Need the PEAR Mail_mime package to send attachments');
        }

        $mime = new Mail_mime(['text_charset' => 'UTF-8']);
        $hdrs = [
            'From' => $this->from,
            'Subject' => $this->subject,
        ];
        $mime->setTXTBody($this->msg);

        foreach ($this->filesets as $fs) {
            $ds       = $fs->getDirectoryScanner($this->project);
            $fromDir  = $fs->getDir($this->project);
            $srcFiles = $ds->getIncludedFiles();

            foreach ($srcFiles as $file) {
                $mime->addAttachment($fromDir . DIRECTORY_SEPARATOR . $file, 'application/octet-stream');
            }
        }

        $body = $mime->get();
        $hdrs = $mime->headers($hdrs);

        $mail = Mail::factory($this->backend, $this->backendParams);
        $mail->send($this->tolist, $hdrs, $body);
    }

    /**
     * Setter for message
     *
     * @param string $msg
     *
     * @return void
     */
    public function setMsg(string $msg)
    {
        $this->setMessage($msg);
    }

    /**
     * Alias setter
     *
     * @param string $msg
     *
     * @return void
     */
    public function setMessage(string $msg): void
    {
        $this->msg = (string) $msg;
    }

    /**
     * Setter for subject
     *
     * @param string $subject
     *
     * @return void
     */
    public function setSubject(string $subject): void
    {
        $this->subject = (string) $subject;
    }

    /**
     * Setter for tolist
     *
     * @param string $tolist
     *
     * @return void
     */
    public function setToList(string $tolist): void
    {
        $this->tolist = $tolist;
    }

    /**
     * Alias for (deprecated) recipient
     *
     * @param string $recipient
     *
     * @return void
     */
    public function setRecipient(string $recipient): void
    {
        $this->tolist = (string) $recipient;
    }

    /**
     * Alias for to
     *
     * @param string $to
     *
     * @return void
     */
    public function setTo(string $to): void
    {
        $this->tolist = (string) $to;
    }

    /**
     * Supports the <mail>Message</mail> syntax.
     *
     * @param string $msg
     *
     * @return void
     */
    public function addText(string $msg): void
    {
        $this->msg = (string) $msg;
    }

    /**
     * Sets email address of sender
     *
     * @param string $from
     *
     * @return void
     */
    public function setFrom(string $from): void
    {
        $this->from = $from;
    }

    /**
     * Sets PEAR Mail backend to use
     *
     * @param string $backend
     *
     * @return void
     */
    public function setBackend(string $backend): void
    {
        $this->backend = $backend;
    }

    /**
     * Sets PEAR Mail backend params to use
     *
     * @param string $backendParams
     *
     * @return void
     */
    public function setBackendParams(string $backendParams): void
    {
        $params = explode(',', $backendParams);

        foreach ($params as $param) {
            $values = explode('=', $param);

            if (count($values) < 1) {
                continue;
            }

            if (count($values) == 1) {
                $this->backendParams[] = $values[0];
            } else {
                $key                       = $values[0];
                $value                     = $values[1];
                $this->backendParams[$key] = $value;
            }
        }
    }
}
