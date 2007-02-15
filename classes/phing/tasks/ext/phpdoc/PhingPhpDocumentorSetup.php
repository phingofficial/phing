<?php
/**
 * $Id: PHPDocumentorTask.php 144 2007-02-05 15:19:00Z hans $
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

/**
 * Phing subclass of the phpDocumentor_setup class provided with PhpDocumentor to work around limitations in PhpDocumentor API.
 * 
 * This class is necessary because phpDocumentor_setup does not expose a complete API for setting configuration options.  Because
 * this class must directly modify some "private" GLOBAL(!) configuration variables, it is liable to break if the PhpDocumentor
 * internal implementation changes.  Obviously this is far from ideal, but there's also no solution given the inflexibility of the
 * PhpDocumentor design. 
 * 
 * @author Hans Lellelid <hans@xmpl.org>@author hans
 * @version $Id$
 * @package phing.tasks.ext.phpdoc
 */
class PhingPhpDocumentorSetup extends phpDocumentor_setup {
	
	/**
	 * Constructs a new PhingPhpDocumentorSetup.
	 *
	 */
	public function __construct()
	{
		global $_phpDocumentor_cvsphpfile_exts, $_phpDocumentor_setting;
		
		$this->setup = new Io();
		$this->render = new phpDocumentor_IntermediateParser("Default Title");
		
        $this->parseIni();
		
		if (tokenizer_ext)
        {
            phpDocumentor_out("using tokenizer Parser\n");
            $this->parse = new phpDocumentorTParser();
        } else
        {
            phpDocumentor_out("using default (slower) Parser - get PHP 4.3.0+
and load the tokenizer extension for faster parsing (your version is ".phpversion()."\n");
            $this->parse = new Parser();
        }
	}
	
	/**
	 * Set whether to generate sourcecode for each file parsed.
	 *
	 * This method exists as a hack because there is no API exposed for this in PhpDocumentor.
	 * Note that because we are setting a "private" GLOBAL(!!) config var with this value, this
	 * is subject to break if PhpDocumentor internals changes.  
	 * 
	 * @param bool $b
	 */
	public function setGenerateSourcecode($b)
	{
		global $_phpDocumentor_setting;
		$_phpDocumentor_setting['sourcecode'] = (boolean) $b;
	}
	
}