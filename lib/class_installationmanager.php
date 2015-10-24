<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * 
 * Uses a meta data file stored in the registry to keep track of installation version and files.
 */
defined('DIAMONDMVC') or die();

final class InstallationManager {
	
	private function __construct(){}
	
	
	/**
	 * Installs the given ZIP archive. Files are automatically added to the list of files of this
	 * installation.
	 * @param string $__file Source ZIP file containing the files to install.
	 */
	static public function install( $__file ) {
		if( !file_exists($__file) ) {
			throw new FileNotFoundException($__file);
		}
		
		// Contains all the extracted files for future management purposes.
		$__files = array();
		// Contains all files which could not be extracted for one reason or another.
		$__failedFiles = array();
		
		// Stores the installation meta data.
		$__meta = array();
		
		// Installation event handlers
		$__onBeforeInstall = '';
		$__onAfterInstall  = '';
		$__onInstallError  = '';
		
		// Attempt to open the ZIP file.
		$__zip = zip_open($__file);
		if( is_int($__zip) ) {
			throw new ZipOpenException($__file, $__zip);
		}
		
		// If an exception happens anywhere down the road, we'll catch it to make sure we can possibly rollback changes.
		try {
			// First find the installation handlers and temporarily extract them giving them a random name to allow parallel installations.
			do {
				$__entry = zip_read($__zip);
				if( is_int($__entry) ) {
					throw new ZipReadException($__files, $__entry);
				}
				
				if( $__entry ) {
					// Get the name of the ZIP entry to see if it's the installation handler.
					$name = zip_entry_name($__entry);
					if( is_int($name) ) {
						throw new ZipReadException($__file, $name);
					}
					
					// Extract the script
					switch( $name ) {
					case 'onbeforeinstall.php':
					case 'onafterinstall.php':
					case 'oninstallerror.php':
						$contents = self::getZipEntryContents($__file, $__zip, $__entry);
						do {
							$tmpName = DIAMONDMVC_ROOT . '/tmp/' . generateRandomName('A-Za-z0-9', 20) . '.php';
						} while( file_exists($tmpName) );
						if( file_put_contents($tmpName, $contents) === false ) {
							throw new InstallationException('Failed to extract installation event script into /tmp directory');
						}
					}
					
					// Store the extracted script's temporary name
					switch( $name ) {
					case 'onbeforeinstall.php':
						$__onBeforeInstall = $tmpName;
						break;
					case 'onafterinstall.php':
						$__onAfterInstall = $tmpName;
						break;
					case 'oninstallerror.php':
						$__onInstallError = $tmpName;
						break;
					}
				}
			} while( $__entry );
			
			// Re-open the zip to reset the entry pointer
			zip_close($__zip);
			$__zip = zip_open($__file);
			
			// If the onBeforeInstall script exists, execute it. It can be used to enforce preconditions by returning false.
			$__continue = true;
			if( !empty($__onBeforeInstall) ) {
				$__continue = include($__onBeforeInstall);
			}
			
			if( !$__continue ) {
				logMsg('InstallationManager: installation aborted by onBeforeInstall script', 9, 5);
			}
			else {
				// Loop through the entries of the ZIP.
				do {
					// Read the next entry in the ZIP.
					$__entry = zip_read($__zip);
					if( is_int($__entry) ) {
						throw new ZipReadException($__files, $__entry);
					}
					
					if( $__entry ) {
						// Get the name of the ZIP entry
						$name = zip_entry_name($__entry);
						if( is_int($name) ) {
							throw new ZipReadException($__file, $name);
						}
						
						// Skip installation script files as they are handled in the previous loop
						if( !(strToLower($name) === 'onbeforeinstall.php' or strToLower($name) === 'onafterinstall.php' or strToLower($name) === 'oninstallerror.php') ) {
							// Meta data is treated differently from all other files
							if( $name === 'meta.json' ) {
								$__meta = json_decode(self::getZipEntryContents($__file, $__zip, $__entry), true);
							}
							// Directories are created and files extracted
							else {
								// Based on the name determine whether it's a directory or an actual file
								if( endsWith($name, '/') ) {
									// Create the given path if necessary
									mkdirs(DIAMONDMVC_ROOT . DS . $name);
								}
								else {
									// Extract the entry
									$__files[] = $name;
									self::extractZipEntry($__file, $__zip, $__entry);
								}
							}
						}
					}
				} while( $__entry );
				
				if( !empty($__onAfterInstall) ) {
					include($__onAfterInstall);
				}
			}
		}
		catch( Exception $ex ) {
			self::rollback($__files);
			if( !empty($__onInstallError) ) {
				include($__onInstallError);
			}
			throw $ex;
		}
		
		zip_close($__zip);
		
		// Generate the installation meta data
		if( empty($__meta) ) {
			logMsg("InstallationManager: No meta data found for installation $__file", 9, false);
			$__meta['protocol_version'] = '1.0';
		}
		
		$__meta = Installation::getInstallation($__meta);
		$__meta->setFiles($__files);
		
		// Brute force finding a free to use random file name
		do {
			$__metaname = generateRandomName('A-Za-z0-9', 20);
		} while( file_exists(DIAMONDMVC_ROOT . "/registry/$__metaname.json") );
		$__meta->save(DIAMONDMVC_ROOT . "/registry/$__metaname.json");
		return $__metaname;
	}
	
	/**
	 * Uses installation meta data to uninstall an installation.
	 * @param string $meta Path to an installation meta data file - relative to the "/registry" directory and optionally excluding the .json file extension
	 */
	static public function uninstall( $meta ) {
		$meta = self::getMetaPath($meta);
		if( empty($meta) ) {
			throw new InvalidArgumentException('Parameter $meta must be a path to an installation meta data file');
		}
		
		// TODO: Run an uninstallation script
		
		$inst = Installation::getInstallation(json_decode(file_get_contents($meta), true));
		
		self::rollback($inst->getFiles());
		unlink($meta);
	}
	
	/**
	 * Checks if the installation associated with the meta file or Installation object for
	 * updates, if an update URL is specified.
	 * @param string|Installation $meta
	 * @return boolean
	 */
	static public function hasUpdate( $meta ) {
		if( $meta instanceof Installation ) {
			$inst = $meta;
		}
		else {
			$meta = self::getMetaPath($meta);
			if( empty($meta) ) {
				throw new InvalidArgumentException('Parameter $meta must be a path to an installation meta data file');
			}
			
			$inst = Installation::getInstallation(json_decode(file_get_contents($meta), true));
		}
		
		$updt = self::getUpdateData($inst);
		
		if( empty($updt) ) {
			return false;
		}
		
		$localVersion  = $inst->getVersion();
		$remoteVersion = Version::parse($updt['version']);
		
		if( !$localVersion->lessThan($remoteVersion) ) {
			return false;
		}
		return true;
	}
	
	/**
	 * Uses installation meta data to download and install the updated installation files.
	 * @param string $origMeta Unique ID of the meta data file containing installation information
	 */
	static public function update( $origMeta ) {
		$__meta = self::getMetaPath($origMeta);
		if( empty($__meta) ) {
			throw new InvalidArgumentException('Parameter $__meta must be a path to an installation meta data file');
		}
		
		$inst = Installation::getInstallation(json_decode(file_get_contents($__meta), true));
		$updt = self::getUpdateData($inst);
		
		if( empty($updt) ) {
			throw new InstallationException("Failed to download update data");
		}
		
		if( !is_url($updt['url']) ) {
			throw new InstallationException("No download URL specified in update data");
		}
		
		// Already throws exceptions
		$__zip = self::download($updt['url']);
		
		// Simply install the update.
		$id = self::install($__zip);
		
		// Remove the temporarily downloaded files.
		unlink($__zip);
		
		// Remove the old meta file
		unlink(jailpath(DIAMONDMVC_ROOT . DS . 'registry', $origMeta));
		
		// If we're updating the Diamond itself, we'll need to rename the newly saved meta data file respectively.
		if( $origMeta === 'diamondmvc.json' ) {
			rename(DIAMONDMVC_ROOT . "/registry/$id.json", DIAMONDMVC_ROOT . "/registry/diamondmvc.json");
			$id = 'diamondmvc';
		}
		
		return $id;
	}
	
	
	/**
	 * Reads the contents of the ZIP entry.
	 * @param  string   $__file  Path to the ZIP file
	 * @param  resource $__zip   ZIP resource
	 * @param  resource $__entry ZIP entry resource to get the contents of
	 * @return string          Contents of the ZIP entry.
	 */
	static protected function getZipEntryContents( $__file, $__zip, $__entry ) {
		if( !zip_entry_open($__zip, $__entry) ) {
			throw new ZipException("Failed to open ZIP entry for reading in ZIP at $__file");
		}
		
		$size     = zip_entry_filesize($__entry);
		$contents = zip_entry_read($__entry, $size);
		if( $contents === false ) {
			throw new ZipException("Failed to read contents from ZIP entry in ZIP at $__file");
		}
		
		if( !zip_entry_close($__entry) ) {
			throw new ZipException("Failed to close ZIP entry in ZIP at $__file");
		}
		return $contents;
	}
	
	/**
	 * Extracts the contents of the zip entry to the local file system (using the given directory hierarchy).
	 * @param  string   $__file  Path to the ZIP file
	 * @param  resource $__zip   ZIP resource
	 * @param  resource $__entry ZIP entry resource to extract
	 * @return boolean         True if the file was successfully extracted, otherwise false.
	 */
	static protected function extractZipEntry( $__file, $__zip, $__entry ) {
		// Use the ZIP name as path
		$name = zip_entry_name($__entry);
		if( is_int($name) ) {
			throw new ZipReadException($__file, $name);
		}
		if( file_put_contents(DIAMONDMVC_ROOT . DS . $name, self::getZipEntryContents($__file, $__zip, $__entry)) === false ) {
			throw new InstallationException("Failed to write contents of ZIP entry in ZIP at $__file to disk");
		}
	}
	
	/**
	 * Removes files listed in $__files. These usually have been successfully extracted from a ZIP before an error
	 * occurred.
	 * @param  array $__files
	 * @return boolean True if all $__files were successfully removed, otherwise false.
	 */
	static protected function rollback( $__files ) {
		$failures = array();
		
		foreach( $__files as $__file ) {
			if( !@unlink($__file) ) {
				$failures[] = $__file;
			}
			// Not necessary for this method to fail.
			self::cleanpath(dirname($__file));
		}
		
		if( !empty($failures) ) {
			logMsg("InstallationManager: failed to rollback files " . implode(', ', $failures), 8, false);
			return false;
		}
		return true;
	}
	
	/**
	 * Recursively checks if the directories within the given path are empty and removes them if so.
	 * @param  string  $path to clean
	 * @return boolean       True if the entire path was successfully cleaned of empty directories, otherwise false.
	 */
	static protected function cleanpath( $path ) {
		if( is_dir_empty($path) ) {
			if( @rmdir($path) ) {
				return self::cleanpath(dirname($path));
			}
			else {
				logMsg("InstallationManager: failed to remove directory $path", 4, false);
				return false;
			}
		}
		return true;
	}
	
	
	
	/**
	 * Gets the real path to the meta file. If the file does not exist, returns an empty string.
	 * @param  string $__meta Path relative to the /registry directory.
	 * @return string       Absolute path to the meta file or an empty string if the file does not exist.
	 */
	static protected function getMetaPath( $meta ) {
		if( !endsWith($meta, '.json') ) {
			$meta .= '.json';
		}
		return jailpath(DIAMONDMVC_ROOT . DS . 'registry', $meta);
	}
	
	/**
	 * Gets the update data from the update URL provided by the given {@link Installation}.
	 * @param  Installation $inst
	 * @return array        JSON decoded update data or an empty array if the data could not be successfully read.
	 */
	static protected function getUpdateData( $inst ) {
		$url = $inst->getUpdateUrl();
		if( !is_url($url) ) {
			return array();
		}
		
		$updateData = @file_get_contents($url);
		if( $updateData === false ) {
			return array();
		}
		
		$updateData = @json_decode($updateData, true);
		if( $updateData === null ) {
			return array();
		}
		
		return $updateData;
	}
	
	/**
	 * Downloads an installation ZIP from the given URL. The file MUST have the .zip file extension as an added
	 * pseudo-safety measurement.
	 * @param string $url
	 * @return string Path to the downloaded ZIP file.
	 */
	static protected function download( $url ) {
		// Attempt to download the file.
		$contents = @file_get_contents($url);
		if( $contents === false ) {
			throw new InstallationException("Failed to download the installation ZIP from $url");
		}
		
		// Brute force first free name to use
		do {
			$name = generateRandomName('A-Za-z0-9', 20);
		} while( file_exists(DIAMONDMVC_ROOT . "/tmp/$name.zip") );
		
		// Attempt to save the file.
		if( file_put_contents(DIAMONDMVC_ROOT . "/tmp/$name.zip", $contents) === false ) {
			throw new InstallationException("Failed to save downloaded installation ZIP from $url");
		}
		
		// Successfully saved. Return its path.
		return DIAMONDMVC_ROOT . "/tmp/$name.zip";
	}
	
}
