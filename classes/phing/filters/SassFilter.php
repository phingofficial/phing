<?php

/*
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

include_once 'phing/filters/BaseParamFilterReader.php';
include_once 'phing/filters/ChainableReader.php';

/**
 * Filter that compiles Sass files in Sass, Scss and CSS format.
 *
 * @author Jost Baron <j.baron@netzkoenig.de>
 */
class SassFilter extends BaseParamFilterReader implements ChainableReader {

	/**
	 * The base directory is used for resolving imported files.
	 * @var string
	 */
	protected $baseDirectory;

	/**
	 * Indicates if the filter is already initialized.
	 * @var boolean
	 */
	protected $initialized = false;

	/**
	 * Returns the base directory.
	 * @return mixed The base directory as string or PhingFile.
	 */
	public function getBaseDirectory() {
		return $this->baseDirectory;
	}

	/**
	 * [Optional] The base directory for resolving @import rules.
	 * @param mixed $baseDirectory Set the base directory. Might either be a
	 * string or a PhingFile.
	 * @throws BuildException if the parameter is neither a string nor a
	 * PhingFile.
	 */
	public function setBaseDirectory($baseDirectory) {
		if (!($baseDirectory instanceof PhingFile)
		    || !is_string($baseDirectory)) {
			throw new BuildException("Given baseDirectory is of wrong type.");
		}

		$this->baseDirectory = $baseDirectory;
	}

	/**
	 * Returns if the filter is initialized.
	 * @return boolean
	 */
	public function getInitialized() {
		return $this->initialized && parent::getInitialized();
	}

	/**
	 * Constructor
	 */

	/**
	 * Reads the input stream and applies the filter on it.
	 * @param string $len Number of characters to read. If null is given, stream
	 * is read until EOF is found.
	 */
	public function read($len = null) {
		if (!$this->getInitialized()) {
			$this->initialize();
		}

		// Completely read the input stream.
		$input = null;

		$data = $this->in->read($len);
		while ($data !== -1) {
			$input .= $data;
			$data = $this->in->read($len);
		}

		// Abort if EOF is read.
		if (is_null($input)) {
			return -1;
		}

		// Now build a SassParser
		$sassParser = new SassParser(
			array(
				'load_paths' => array(
					$this->baseDirectory
				)
			)
		);

		// And let it transform the file
		$output = $sassParser->toCss($input, false);

		return $output;
	}

    /**
     * Creates a new SassFilter using the passed in
     * Reader for instantiation.
     *
     * @param Reader A Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     *
     * @return Reader A new filter based on this configuration, but filtering
     *         the specified reader
     */
    function chain(Reader $reader) {
        $newFilter = new SassFilter($reader);
        $newFilter->setProject($this->getProject());
		$newFilter->initialize();

        return $newFilter;
    }

	/**
	 * Initialization of the filter.
	 *
	 * @throws BuildException if 'SassParser.php' can not be found.
	 */
	public function initialize() {
		if (!class_exists('SassParser')) {
			if (stream_resolve_include_path ('SassParser.php')) {
				require_once 'SassParser.php';
			}
			else {
				throw new BuildException(
						'To use SassTask, you need to have a version of PHPSass ' .
						'(http://www.phpsass.com) in your include path.'
				);
			}

			if (!class_exists('SassParser')) {
				throw new BuildException(
						'A file \'SassParser.php\' was found and loaded, but the ' .
						'class \'SassParser\' can not be found.'
				);
			}
		}

		$this->initialized = true;
	}
}

?>