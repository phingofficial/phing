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
 
include_once 'phing/Task.php';

/**
 *  Send a message by mail() 
 *
 *  <mail to="user@example.org" subject="build complete">The build process is a success...</mail> 
 * 
 *  @author   Francois Harvey at SecuriWeb (http://www.securiweb.net)
 *  @version  $Id$
 *  @package  phing.tasks.ext
 */
class MailTask extends Task {

    protected $recipient;
      
    protected $subject;
    
    protected $msg;

    public function main() {
        $this->log('Sending mail to ' . $this->recipient );    
        mail($this->recipient, $this->subject, $this->msg);
    }

    /** setter for message */
    public function setMsg($msg) {
        $this->setMessage($msg);
    }

    /** alias setter */
    public function setMessage($msg) {
        $this->msg = (string) $msg;
    }
    
    /** setter for subject **/
    public function setSubject($subject) {
        $this->subject = (string) $subject;    
    }

    /** setter for recipient **/
    public function setRecipient($recipient) {
        $this->recipient = (string) $recipient;
    }

    /** alias for recipient **/
    public function setTo($recipient) {
        $this->recipient = (string) $recipient;
    }
        
    /** Supporting the <mail>Message</mail> syntax. */
    public function addText($msg)
    {
        $this->msg = (string) $msg;
    }
}

