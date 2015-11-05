/**
 * @package DiamondMVC
 * @author  Zyr <zyrius@live.com>
 * @version 1.0
 * @license CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * 
 * DiamondMVC primitive build system. To be executed with node in the directory of this script.
 */

var fs       = require('fs'),
	exec     = require('child_process').exec,
	Promise  = require('promise'),
	archiver = require('archiver'),
	glob     = Promise.denodeify(require('glob')),
	globopts = {
		nodir: true,
	},
	buildopts = require('./options.json');


(function main(){
	var command, args = [], target = help;
	
	if( !process.argv.length ) {
		command = 'help';
	}
	else {
		args    = [].concat(process.argv);
		args.shift();
		args.shift();
		command = args.shift();
	}
	
	if( command === 'build' ) {
		target = build;
	}
	else if( command === 'commit' ) {
		target = commit;
	}
	else if( command === 'push' ) {
		target = push;
	}
	else if( command === 'deploy' ) {
		target = deploy;
	}
	else {
		target = help;
	}
	
	var result = target.apply(this, args);
	if( result !== undefined ) {
		console.log(result);
	}
})();


function help( on ) {
	if( !on || !on.trim().length ) {
		console.log(' --- DiamondMVC Build Help ---');
		console.log('Available sub commands:');
		console.log('* help [cmd] - show this help or detailed information on a particular command');
		console.log('* build      - simply build the current release using information from the');
		console.log('               options.json file');
		console.log('* commit     - commit changes to the local git repository');
		console.log('* push       - push changes to the universe');
		console.log('* deploy     - deploy current release on the official server');
	}
	else {
		on = on.trim().toLowerCase();
		switch( on ) {
			case 'help':
				console.log('Not much to tell, it\'s just a helping hand to get back into deployment after having suspended the project for a while.');
				break;
			
			case 'build':
				console.log(' --- DiamondMVC Build Help : build command ---');
				console.log('Uses configuration settings from options.json in the same directory to');
				console.log('generate the updated local registry entry. Associated files - as');
				console.log('stored in the registry entry - are automatically discovered.\n');
				
				console.log('The update information file is generated alongside the updated registry entry.');
				console.log('Usually no further attention needs to be paid towards these two files as they');
				console.log('are handled properly using the \'deploy\' command.\n');
				console.log('Project files are conveniently packaged into a single file, stored under');
				console.log('    release/<version>/build-<buildnr>.zip');
				console.log('where <version> and <buildnr> are both read from the previously mentioned');
				console.log('configuration.\n');
				
				console.log('Update files are discovered using GIT features - thus it is of utter importance');
				console.log('to commit and push to remote when rolling a new release - and conveniently');
				console.log('packaged into a single file, stored under');
				console.log('    update/<version>/build-<buildnr>.zip');
				console.log('where the values are read as above.');
				break;
			
			case 'commit':
				console.log(' --- DiamondMVC Build Help : commit command ---');
				console.log('Stages and commits all changes in the local repository: modifications to');
				console.log('existing files, removed files, added files.\n');
				
				console.log('I recommend to execute this command only if all tests where successful!\n');
				
				console.log('An optional parameter may be passed providing the commit message. By');
				console.log('default the message follows this template:');
				console.log('    Version <version>');
				console.log('Where <version> is read from the configurations file called:');
				console.log('    options.json');
				break;
				
			case 'push':
				console.log(' --- DiamondMVC Build Help : push command ---');
				console.log('Uses locally stored credentials to push changes to a remote repository - by');
				console.log('default the master repository at github.\n');
				
				console.log('I seriously recommend to execute this command only if all tests where');
				console.log('successful as this usually means an official release!\n');
				
				console.log('An optional parameter may be passed indicating the remote repository\'s local');
				console.log('name. Defaults to "origin".');
				break;
				
			case 'deploy':
				console.log(' --- DiamondMVC Build Help : deploy command ---');
				console.log('Uses locally stored credentials to officially release the current version on');
				console.log('our servers using SSH.\n');
				
				console.log('An update script is run on the server to update the web presence. Files are');
				console.log('moved appropriately to enable future connections to acknowledge the update.\n');
				
				console.log('I friggin\' seriously recommend to handle this command with caution!');
				break;
		}
	}
}

function build( ) {
	Promise.all([
		incrementBuildNumber(),
		new Promise(
			function( resolve, reject ) {
				getAllFiles().then(function( files ) {
					// First update registry item
					updateRegistry(files);
					
					// Create release directory if necessary
					if( !fs.existsSync('release') ) {
						fs.mkdirSync('release');
					}
					else if( !fs.lstatSync('release').isDirectory() ) {
						fs.unlinkSync('release');
						fs.mkdirSync('release');
					}
					
					// Create version directory if necessary
					if( !fs.existsSync('release/' + buildopts.version) ) {
						fs.mkdir('release/' + buildopts.version);
					}
					else if( !fs.lstatSync('release/' + buildopts.version).isDirectory() ) {
						fs.unlinkSync('release/' + buildopts.version);
						fs.mkdir('release/' + buildopts.version);
					}
					
					// Remove any existing build
					if( fs.exists('release/' + buildopts.version + '/' + buildopts.buildnr + '.zip') ) {
						fs.unlinkSync('release/' + buildopts.version + '/' + buildopts.buildnr + '.zip');
					}
					
					// Then copy all files to release-<version>.zip archive
					var zip = archiver('zip', {}),
						out = fs.createWriteStream('release/' + buildopts.version + '/build-' + buildopts.buildnr + '.zip');
					zip.pipe(out);
					
					packFiles(files, zip).done(
						function(){
							zip.finalize();
							console.log('Packed release archive');
							resolve();
						},
						function( err ) {
							zip.finalize();
							console.log('Failed to pack release archive. Please do manually');
						});
				});
			},
			function( err ) {
				reject(err);
			}
		),
		generateUpdateFile(),
	]).done(function(){
		if( !fs.existsSync('update') ) {
			fs.mkdirSync('update');
		}
		else if( !fs.lstatSync('update').isDirectory() ) {
			fs.unlinkSync('update');
			fs.mkdirSync('update');
		}
		
		if( !fs.existsSync('update/' + buildopts.version) ) {
			fs.mkdirSync('update/' + buildopts.version);
		}
		else if( !fs.lstatSync('update/' + buildopts.version).isDirectory() ) {
			fs.unlinkSync('update/' + buildopts.version);
			fs.mkdirSync('update/' + buildopts.version);
		}
		
		var zip = archiver('zip', {}),
			out = fs.createWriteStream('update/' + buildopts.version + '/build-' + buildopts.buildnr + '.zip');
		zip.pipe(out);
		
		Promise.all([
			packUpdateFiles(zip),
			packUpdateScript(zip)
		]).done(function(){
			console.log('Packed update archive');
		},
		function( err ) {
			console.log('Failed to pack update archive');
		});
	});
}

/**
 * Promise to commit changed, added and removed files to the GIT repository.
 * @param {string} message Commit message
 */
function commit( message ) {
	if( !message || !message.trim().length ) {
		message = 'Version ' + buildopts.version;
	}
	
	var execPromise = Promise.denodeify(exec);
	
	return new Promise(function( resolve, reject ) {
		Promise.all([
			stageUntracked(),
			stageChanged(),
			stageDeleted(),
		]).then(function() {
			execPromise('git commit -m "' + message + '"').then(function(){
				resolve();
			},
			function( err ){
				reject(err);
			});
		},
		function( err ) {
			reject(err);
		});
	});
}

/**
 * Push the latest commits to the remote repository.
 */
function push( repo ) {
	if( !repo || !repo.trim().length ) {
		repo = 'origin';
	}
	
	return new Promise(function( resolve, reject ) {
		var execPromise = Promise.denodeify(exec);
		execPromise('git push ' + repo).then(function( ) {
			resolve();
		},
		function( err ) {
			reject(err);
		});
	});
}

/**
 * Deploy the current build on the server. Note: does not check if the current build is new.
 */
function deploy( ) {
	// TODO: Use an SSH client to connect to the server, pull changes and run the deployment script
}


/**
 * Promise to asynchronously increment the build number of our primitive versioning file.
 */
function incrementBuildNumber( ) {
	return new Promise(function( resolve, reject ) {
		buildopts.buildnr++;
		fs.writeFile('./options.json', JSON.stringify(buildopts, null, 4), function( err ) {
			if( err ) reject(err);
			resolve();
		});
	});
}

/**
 * Promise to asynchronously get all the important (i.e. non-temporary) file names of this library.
 */
function getAllFiles( ) {
	var files = [];
	
	return new Promise(function( resolve, reject ) {
		Promise.all([glob('!(build|logs|uploads|tmp)/**', {cwd: '..'}), glob('*', {cwd: '..'}), glob('**/.htaccess', {cwd: '..'})]).then(
			function( res ) {
				resolve(res[0].concat(res[1]).concat(res[2]));
			},
			function( err ) {
				reject(err);
			}
		);
	});
}

/**
 * Promise to update the registry meta data file.
 */
function updateRegistry( files ) {
	return new Promise(function( resolve, reject ) {
		// Generate registry item
		var registryItemOutput = {
			protocol_version: "1.0",
			version: buildopts.version,
			name: "DiamondMVC",
			description: "This is the system you are currently working with! The Linux of web server operating systems, DiamondMVC is designed as a developer's scaffolding.",
			author: buildopts.contributors.join(', '),
			copyright: "Copyright &copy; Wings of Dragons 2015. Copyright &copy; contributors 2015",
			license: "MIT License",
			distUrl: "http://diamondmvc.wings-of-dragons.com/",
			updateUrl: "http://dl.wings-of-dragons.com/diamondmvc/" + buildopts.version + "/update.json",
			files: files,
		};

		fs.writeFile('../registry/diamondmvc.json', JSON.stringify(registryItemOutput), function( err ) {
			if( err ) reject(err);
			console.log('Created registry item');
			resolve();
		});
	});
}

/**
 * Promise to generate the update information JSON file.
 */
function generateUpdateFile( ) {
	return new Promise(function( resolve, reject ) {
		// Generate update information file
		var updateOutput = {
			version: buildopts.version,
			url: "http://dl.wings-of-dragons.com/diamondmvc/" + buildopts.version + ".zip",
		};

		fs.writeFile('./update.json', JSON.stringify(updateOutput), function( err ) {
			if( err ) reject(err);
			console.log('Created update info file. Copy it to the old version\'s distribution directory');
			resolve();
		});
	});
}


/**
 * Promise to pack all relevant files into one archive (i.e. all except test upload and temp
 * files as well as build files).
 * @param {array}    files Files to package
 * @param {archiver} zip   Archiver instance to add the files to.
 */
function packFiles( files, zip ) {
	return new Promise(function( resolve, reject ) {
		for( var i = 0; i < files.length; ++i ) {
			zip.file('../' + files[i], {name: files[i]});
		}
		resolve();
	});
}

/**
 * Promise to pack changed and untracked files into the ZIP archive.
 * Upon success, passes the initially received ZIP archive and the added files to the handler.
 */
function packUpdateFiles( zip ) {
	return new Promise(function( _resolve, _reject ) {
		// Get both changed and untracked files
		Promise.all([
			getChanged(),
			getUntracked(),
		]).then(
			function( res ) {
				var files = res[0].concat(res[1]);
				
				for( var i = 0; i < files.length; ++i ) {
					zip.file('../' + files[i], {name: files[i]});
				}
				
				_resolve(zip, files);
			},
			function( err ) {
				_reject(err);
			}
		);
	});
}

/**
 * Generates and packs the basic update script file into the given archive. It unlinks all removed files
 * from client maschines.
 * @param  {archiver} zip
 */
function packUpdateScript( zip ) {
	return new Promise(function( resolve, reject ) {
		getDeleted().then(
			function( files ) {
				generateFileRemovalSnippet(files).then(
					function( snippet ) {
						zip.append(new Buffer('<?php ' + snippet + ' ?>'), {name: 'onbeforeinstall.php'});
						resolve();
					},
					function( err ) {
						reject(err);
					}
				);
			},
			function( err ) {
				reject(err);
			})
	});
}

/**
 * Generates a PHP snippet to unlink removed files on the end user's maschine. Excludes <?php and ?> markers
 * @return {string}
 */
function generateFileRemovalSnippet( files ) {
	return new Promise(function( resolve, reject ) {
		getDeleted().then(
			function( ) {
				// Double stringification to add quotes to the string and escape correctly.
				resolve('$tmpFiles=json_decode(' + JSON.stringify(JSON.stringify(files)) + ')' +
					'foreach( $tmpfiles as $file ) {' + 
						'unlink(jailpath(DIAMONDMVC_ROOT, $file));' +
					'}');
			},
			function( err ) {
				reject(err);
			}
		);
	});
}


function push( ) {
	
}

function stageChanged( ) {
	return stageAdd(getChanged);
}

function stageUntracked( ) {
	return stageAdd(getUntracked);
}

function stageAdd( getFiles ) {
	return new Promise(function( resolve, reject ) {
		getFiles().then(function( files ) {
			var execPromise = Promise.denodeify(exec),
				promises    = [];
			
			files.forEach(function(file) {
				promises.push(exec('git add ' + file));
			});
			
			Promise.all(promises).then(function( res ) {
				// TODO: Do something with the output of all calls to verify they properly ended. Or do that above.
				resolve();
			},
			function( err ) {
				reject(err);
			});
		},
		function( err ) {
			reject(err);
		});
	});
}

function stageDeleted( ) {
	return stageRm(getDeleted);
}

function stageRm( getFiles ) {
	return new Promise(function( resolve, reject ) {
		getFiles().then(function( files ) {
			var execPromise = Promise.denodeify(exec),
				promises    = [];
			
			files.forEach(function(file){
				promises.push(exec('git rm ' + file));
			});
			
			Promise.all(promises).then(function( res ) {
				resolve();
			},
			function( err ) {
				reject(err);
			});
		},
		function( err ) {
			reject(err);
		});
	});
}

/**
 * Gets changed files minus those in build from git to copy them to the ./update folder. Prepares the system for packing.
 */
function getChanged( ) {
	return new Promise(function( resolve, reject ) {
		exec('git diff --name-only HEAD', function( err, stdout, stderr ) {
			if( err ) reject(err);
			resolve(removeBuildFiles(stdout.split('\n')));
		});
	});
}

/**
 * Gets untracked files minus those in build from git to copy them to the ./update folder. Prepares the system for packing.
 */
function getUntracked( ) {
	return new Promise(function( resolve, reject ) {
		exec('git ls-files -o --exclude-standard', {cwd: '..'}, function( err, stdout, stderr ) {
			if( err ) reject(err);
			resolve(removeBuildFiles(stdout.split('\n')));
		});
	});
}

/**
 * Promise to get a list of all deleted files. We'll use this list to generate a quick and easy PHP script snippet
 * to delete these files in the end user's update process.
 */
function getDeleted( ) {
	return new Promise(function( resolve, reject ) {
		exec('git ls-files -d', function( err, stdout, stderr ) {
			if( err ) reject(err);
			resolve(removeBuildFiles(stdout.split('\n')));
		});
	});
}

/**
 * Removes files from the /build directory
 * @param  {array} files Unfiltered array of file paths relative to project root
 * @return {array}       Filtered array of file paths relative to project root
 */
function removeBuildFiles( files ) {
	var result = [];
	for( var i = 0; i < files.length; ++i ) {
		if( !files[i].trim().length || files[i].indexOf('build/') === 0 ) continue;
		result.push(files[i]);
	}
	return result;
}
