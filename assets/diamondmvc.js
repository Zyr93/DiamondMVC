/**
 * @package  DiamondMVC
 * @author   Brian Cobile
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * 
 * Provides the MVC's client side framework.
 * 
 */
require.config({
	baseUrl : DIAMONDMVC_URL + "/assets",
	shim : {
		bootstrap : {
			deps : ['jquery']
		},
		'jquery.easing' : {
			deps : ['jquery']
		},
		dropzone : {
			deps : ['jquery']
		},
	},
	paths : {
		// Dependencies and dependencies of dependencies
		jquery : [
			'https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min',
			'https://code.jquery.com/jquery-2.1.4.min',
			'jquery/dist/jquery.min',
		],
		'jquery-mobile' : [
			'https://ajax.googleapis.com/ajax/libs/jquerymobile/1.4.5/jquery.mobile.min.js',
			'https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js',
			'jquery-mobile/dist/jquery.mobile.min.js',
		],
		'jquery.easing' : 'diamondmvc/jquery.easing',
		bootstrap : 'bootstrap/js/bootstrap.min',
		selectize : 'selectize/dist/js/selectize',
		microplugin : 'microplugin/src/microplugin',
		sifter : 'sifter/sifter.min',
		dropzone : 'dropzone/dropzone-amd-module.min',
		
		// Modules
		// Nothing here yet.
	},
});

define(['jquery', 'jquery.easing', 'bootstrap', 'selectize'], function($){
	
	var w = window,
		d = document,
		mvc = {};
	
	mvc.fnBind = function( ) {
		var args  = Array.prototype.slice.call(arguments),
			that  = typeof this === 'function' ? this : args.shift(),
			scope = args.shift();
		return function( ) {
			that.apply(scope || this, args);
		};
	};
	// Make our fnBind
	if( !Function.prototype.bind ) {
		Function.prototype.bind = mvc.fnBind;
	}
	
	// Attach our startsWith and endsWith methods
	if( !String.prototype.startsWith ) {
		String.prototype.startsWith = mvc.startsWith;
	}
	if( !String.prototype.endsWith ) {
		String.prototype.endsWith = mvc.endsWith;
	}
	
	mvc.addNotification = function( title, notification, level ) {
		$(d.body).prepend(mvc.generateNotification(title, notification, level));
	};
	
	mvc.generateNotification = function( title, notification, level ) {
		switch( (level + '').toLowerCase() ) {
			case 'success':
			case 'done':
				level = 'success';
				break;
				
			case 'info': default:
			case 'note':
				level = 'info';
				break;
				
			case 'warning':
			case 'warn':
			case 'caution':
				level = 'warning';
				break;
				
			case 'danger':
			case 'alert':
			case 'fail':
			case 'error':
				level = 'danger';
				break;
		}
		return $('<div>').addClass('alert alert-' + level).attr('role', 'alert')
			.append($('<button>').attr({type : 'button', 'data-dismiss' : 'alert', 'aria-label' : 'Close'}).addClass('close')
				.append($('<span aria-hidden="true">').html('&times;')))
			.append($('<strong>').text(title + ' '))
			.append(notification);
	};
	
	mvc.generateRandomName = function( length, range ) {
		if( !arguments.length ) {
			length = 10;
		}
		if( arguments.length < 2 ) {
			range  = 'A-Za-z_-';
		}
		
		var result  = '',
			charset = '';
		
		for( var i = 0; i < range.length; ++i ) {
			var char = range.charAt(i);
			if( char === '-' ) {
				var startCode = range.charCodeAt(i - 1),
					endCode   = range.charCodeAt(i + 1);
				for( var j = startCode + 1; j < endCode; ++j ) {
					charset += String.fromCharCode(j);
				}
			}
			else {
				charset += char;
			}
		}
		
		for( var i = 0; i < length; ++i ) {
			result += charset[Math.floor(Math.random() * charset.length)];
		}
		
		return result;
	};
	
	$.fn.scrollTo = function( done ){
		done = done || function(){};
		return $('html, body').animate({
			scrollTop  : $(this).offset().top  - $(w).innerHeight() * 0.1,
			scrollLeft : $(this).offset().left - $(w).innerWidth()  * 0.1
		}, 500, 'easeOutExpo', done);
	};
	
	/**
	 * Filter a given array or object using a callback.
	 * When filtering an array, the callback will receive the index as first and the item as
	 * second parameters. The items will be reindexed in the resulting array.
	 * When filtering an object, the callback will receive the key as first and the value as
	 * second parameters. The keys will remain untouched in the resulting object.
	 * @param  {array|object} target   Array or object to filter.
	 * @param  {function}     callback Function to consult whether to keep an item.
	 * @return {array|object}          Filtered array or object.
	 */
	mvc.filter = function( target, callback ) {
		var result;
		if( $.isArray(target) ) {
			result = [];
			$.each(target, function( index, elem ) {
				if( callback(index, elem) ) {
					result.push(elem);
				}
			});
		}
		else if( typeof target === 'object' ) {
			result = {};
			$.each(target, function( index, elem ) {
				if( callback(index, elem) ) {
					result[index] = elem;
				}
			});
		}
		else {
			result = target;
		}
		return result;
	};
	
	/**
	 * Maps the values of the target array or object to new values.
	 * When mapping an array, the callback will receive the index as first and the item
	 * as second parameters.
	 * When mapping an object, the callback will receive the key as first and the value
	 * as second parameters. An array can be returned to remap the key as well. The
	 * first item of the array will be the new key, the second item of the array resembles
	 * its new value.
	 * @param  {array|object} target   Array or object to map.
	 * @param  {function}     callback Function to map the value with.
	 * @param  {boolean?}     copy     Whether to directly map the passed array/object or to create a copy thereof.
	 * @return {array|object}          Mapped array or object.
	 */
	mvc.map = function( target, callback, copy ) {
		copy = !!copy;
		var result = target;
		
		if( $.isArray(target) ) {
			if( copy ) {
				result = [];
			}
			
			for( var i = 0; i < target.length; ++i ) {
				result[i] = callback(i, target[i]);
			}
		}
		else if( typeof target === 'object' ) {
			if( copy ) {
				result = {};
			}
			
			for( var i in target ) {
				if( target.hasOwnProperty(i) ) {
					var pair = callback(i, target[i]);
					if( $.isArray(pair) ) {
						delete result[i];
						result[pair[0]] = pair[1];
					}
					else {
						result[i] = pair;
					}
				}
			}
		}
		
		return result;
	};
	
	/**
	 * Cookie micro-library. Get or set the value of a named cookie.
	 * TODO: Expand with additional cookie options, such as expires, path and domain.
	 * @param  {string}           name  of the cookie to set or get.
	 * @param  {string?}          value to set if used as a setter.
	 * @return {string|object}          The value of the cookie if used as a getter, otherwise this micr-library to enable method chaining.
	 */
	mvc.cookie = function( name, value ) {
		// Get the value of a cookie.
		if( arguments.length === 1 ) {
			var cookies = document.cookie.split(';');
			for( var i = 0; i < cookies.length; ++i ) {
				var parts = cookies[i].trim().split('=', 2);
				if( parts[0] == name ) {
					return parts[1];
				}
			}
			return null;
		}
		
		// Set the value of a cookie.
		// TODO: Add expires, domain, path, and other candy.
		document.cookie = name + '=' + value;
		return this;
	};
	
	
	mvc.toCamelCase = function( str ) {
		return str.replace(/-(.)/g, function(match, p1){
			return p1.toUpperCase();
		});
	};
	
	mvc.fromCamelCase = function( str ) {
		return str.replace(/[A-Z]/g, function(match){
			return '-' + match.toLowerCase();
		});
	};
	
	/**
	 * Checks if the given string starts with the given substring. Can be called on a string
	 * directly in which case the first parameter is taken from the instance directly.
	 * @param  {string}  subject   Optional. Can be omitted if called on a string directly.
	 * @param  {string}  substring Substring to match the beginning of the target string against.
	 * @return {boolean}           Whether the target string starts with the given substring.
	 */
	mvc.startsWith = function( subject, substring ) {
		if( typeof this === 'string' ) {
			substring = subject;
			subject   = this;
		}
		
		return subject.indexOf(substring) === 0;
	};
	
	/**
	 * Checks if the given string ends with the given substring. Can be called on a string
	 * directly in which case the first parameter is taken from the instance directly.
	 * @param  {string}  subject   Optional. Can be omitted if called on a string directly.
	 * @param  {string}  substring Substring to match the end of the target string against.
	 * @return {boolean}           Whether the target string ends with the given substring.
	 */
	mvc.endsWith = function( subject, substring ) {
		if( typeof this === 'string' ) {
			substring = subject;
			subject   = this;
		}
		
		index = subject.indexOf(substring);
		return index !== -1 && index === subject.length - substring.length;
	};
	
	
	// Set the default configuration of Selectize.
	$.fn.selectize.defaults.labelField = 'display';
	$.fn.selectize.defaults.searchField = ['display', 'value'];
	
	
	// Initiate standard elements with more exciting features.
	$(function onDOMReady(){
		$('[data-toggle="tooltip"]').tooltip();
		
		// Load additional scripts with RequireJS.
		var modules = $.parseJSON($('#amd-modules').html());
		$.each(modules, function(index, elem){
			require([elem]);
		});
	});
	
	return mvc;
});
