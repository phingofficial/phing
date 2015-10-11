<?php
/**
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

require_once 'phing/listener/DefaultLogger.php';
include_once 'phing/system/util/Properties.php';
include_once 'phing/util/StringHelper.php';

/**
 * Uses PEAR Mail package to send the build log to one or
 * more recipients.
 *
 * @author     Michiel Rook <mrook@php.net>
 * @package    phing.listener
 * @version    $Id$
 */
class MailLogger extends DefaultLogger
{
    private $mailMessage = '';

    private $from = 'phing@phing.info';

    private $tolist;

    /**
     * Construct new MailLogger
     */
    public function __construct()
    {
        parent::__construct();

        @require_once 'Mail.php';

        if (!class_exists('Mail')) {
            throw new BuildException('Need the PEAR Mail package to send logs');
        }

        $tolist  = Phing::getDefinedProperty('phing.log.mail.recipients');

        if (!empty($tolist)) {
            $this->tolist = $tolist;
        }
    }

    /**
     * @see DefaultLogger::printMessage
     * @param string $message
     * @param OutputStream $stream
     * @param int $priority
     */
    final protected function printMessage($message, OutputStream $stream, $priority)
    {
        if ($message !== null) {
            $this->mailMessage .= $message . "\n";
        }
    }

    /**
     * Sends the mail
     *
     * @see DefaultLogger#buildFinished
     * @param BuildEvent $event
     */
    public function buildFinished(BuildEvent $event)
    {
        parent::buildFinished($event);

        $project = $event->getProject();
        $properties = $project->getProperties();

        $filename = $properties['phing.log.mail.properties.file'];

        // overlay specified properties file (if any), which overrides project
        // settings
        $fileProperties = new Properties();
        $file = new PhingFile($filename);

        try {
            $fileProperties->load($file);
        } catch (IOException $ioe) {
            // ignore because properties file is not required
        }

        foreach ($fileProperties as $key => $value) {
            $properties['key'] = $project->replaceProperties($value);
        }

        $success = $event->getException() === null;
        $prefix = $success ? 'success' : 'failure';

        try {
            $notify = StringHelper::booleanValue($this->getValue($properties, $prefix . '.notify', 'on'));
            if (!$notify) {
                return;
            }

            if (is_string(Phing::getDefinedProperty('phing.log.mail.subject'))) {
                $defaultSubject = Phing::getDefinedProperty('phing.log.mail.subject');
            } else {
                $defaultSubject = ($success) ? 'Build Success' : 'Build Failure';
            }
            $hdrs = array();
            $hdrs['From']     = $this->getValue($properties, 'from', $this->from);
            $hdrs['Reply-To'] = $this->getValue($properties, 'replyto', '');
            $hdrs['Cc']       = $this->getValue($properties, $prefix . '.cc', '');
            $hdrs['Bcc']      = $this->getValue($properties, $prefix . '.bcc', '');
            $hdrs['Body']     = $this->getValue($properties, $prefix . '.body', '');
            $hdrs['Subject']  = $this->getValue($properties, $prefix . '.subject', $defaultSubject);
            $tolist           = $this->getValue($properties, $prefix . '.to', $this->tolist);
        } catch (BadMethodCallException $e) {
            $project->log($e->getMessage(), Project::MSG_WARN);
        }

        if (empty($tolist)) {
            return;
        }

        $mail = Mail::factory('mail');
        $mail->send($tolist, $hdrs, $this->mailMessage);
    }

    /**
     * @param array $properties
     * @param string $name
     * @param mixed $defaultValue
     *
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    private function getValue(array $properties, $name, $defaultValue)
    {
        $propertyName = 'phing.log.mail.' . $name;
        $value = $properties[$propertyName];
        if ($value === null) {
            $value = $defaultValue;

        }
        if ($value === null) {
            throw new BadMethodCallException('Missing required parameter: ' . $propertyName);

        }
        return $value;
    }
}
