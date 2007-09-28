<?php
/*
 *  $Id: DbDeployTask.php 59 2006-04-28 14:49:47Z lcrouch $
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
 
require_once 'phing/Task.php' ;

/**
 *  Generate SQL script for db using dbdeploy schema version table and delta scripts
 *
 *  <dbdeploy configfile="dbdeploy/dev.ini" /> 
 * 
 *  @author   Luke Crouch at SourceForge (http://sourceforge.net)
 *  @version  $Revision: 1.1 $
 *  @package  phing.tasks.ext.dbdeploy
 */

class DbDeployTask extends Task {
	
	protected $configFile ;
	
	function main () {
		try {
			/**
			 * Ugly hack to check for DbDeploy PEAR package
			 */
			$config = new PEAR_Config();
			$registry = new PEAR_Registry($config->get('php_dir'));
			$pkg_info = $registry->_packageInfo("Db_Deploy", null, "pear.php.net");

			if ($pkg_info != NULL){
				@include_once 'DB/Deploy.php';
				$dbdeploy = new Db_Deploy();
				$dbdeploy->setConfigFile($this->configFile);
				$dbdeploy->fire();
			} else {
				throw new BuildException("dbdeploy task depends on PEAR DbDeploy package being installed.", $this->getLocation());
			}
		} catch ( Exception $e ) {
			throw new BuildException ( $e ) ;
		}
	}
	
	public function setConfigFile($configFile){
		$this->configFile = $configFile;
	}
}

?>