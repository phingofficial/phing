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

/**
 * A task for compiling Sass, and Scss and CSS files. May also be used for
 * CSS concatenation and minification.
 *
 * The task needs at least PHP 5.3.2.
 *
 * This is implemented using PHPSass by Richard Lyon, Sebastian Siemssen,
 * and Sam Richards. To make this task work, you need to seperately download
 * and install PHPSass and put it into your include path.
 *
 * @see http://sass-lang.com/
 * @see http://www.phpsass.com/
 * @author Jost Baron <j.baron@netzkoenig.de>
 */

require_once 'phing/tasks/system/CopyTask.php';
include_once 'phing/filters/SassFilter.php';

class SassTask extends CopyTask {

	/**
	 * SassFilter used for the transformation.
	 */
	protected $sassFilter;

	/**
	 * Initialization of the task.
	 */
	public function init() {
		$this->sassFilter = new SassFilter();

        $chain = $this->createFilterChain($this->getProject());
        $chain->addSassFilter($this->sassFilter);

		// Force rerun of Sass every time. This is necessary if the Sass-File
		// includes other files, since the CopyTask does not consider them when
		// checking file modification times.
		$this->setOverwrite(true);
	}
}

?>