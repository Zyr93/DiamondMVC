var fs       = require('fs'),
	glob     = require('glob'),
	globopts = {
		nodir: true,
	},
	buildopts = require('./options.json');

// Increment build number
buildopts.buildnr++;
fs.writeFile('./options.json', JSON.stringify(buildopts, null, 4), function( err ) {
	if( err ) throw err;
});

var zip = new AdmZip();

// Assemble list of files of this project (excluding this and the logs directory)
var files  = [],
	result = glob.sync('../!(build|logs)/**');

for( var i = 0; i < result.length; ++i ) {
	files.push(result[i].substr(3));
}

result = glob.sync('../**/.htaccess');
for( var i = 0; i < result.length; ++i ) {
	files.push(result[i].substr(3));
}

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
	if( err ) throw err;
	console.log('Created registry item');
});


// Generate update information file
var updateOutput = {
	version: buildopts.version,
	url: "http://dl.wings-of-dragons.com/diamondmvc/" + buildopts.version + ".zip",
};

fs.writeFile('./update.json', JSON.stringify(updateOutput), function( err ) {
	if( err ) throw err;
	console.log('Created update info file. Copy it to the old version\'s distribution directory');
});


