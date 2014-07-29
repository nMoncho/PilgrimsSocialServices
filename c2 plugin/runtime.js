﻿// ECMAScript 5 strict mode
"use strict";

assert2(cr, "cr namespace not created");
assert2(cr.plugins_, "cr.plugins_ not created");

/////////////////////////////////////
// Plugin class
cr.plugins_.PilgrimsSocialServices = function(runtime)
{
	this.runtime = runtime;
};

(function ()
{
	var pluginProto = cr.plugins_.PilgrimsSocialServices.prototype;

	var loggingPattern;
	var defaultTimeout;
	var webstorageAvailable = false;

	var loggedPlayer = null;

	var getDeviceId = function() {
		if (this.runtime.isCocoonJs) {
			return CocoonJS["App"].getDeviceInfo()["platformId"];
		}
	}
	/////////////////////////////////////
	// Object type class
	pluginProto.Type = function(plugin)
	{
		this.plugin = plugin;
		this.runtime = plugin.runtime;
	};

	var typeProto = pluginProto.Type.prototype;

	// called on startup for each object type
	typeProto.onCreate = function() {
	};

	/////////////////////////////////////
	// Instance class
	pluginProto.Instance = function(type)
	{
		this.type = type;
		this.runtime = type.runtime;
		
		// any other properties you need, e.g...
		// this.myValue = 0;
	};
	
	var instanceProto = pluginProto.Instance.prototype;

	// called whenever an instance is created
	instanceProto.onCreate = function() {
		// note the object is sealed after this call; ensure any properties you'll ever need are set on the object
		this.loggingPattern = this.properties[0];
		this.defaultTimeout = this.properties[1];
		
		if (typeof cr.plugins_.WebStorage == "undefined") {
			alert("WebStorage is not added to the project, this plugin is necessary to Pilgrim's Social Services");
		} else {
			webstorageAvailable = true;
		}
	};
	
	// called whenever an instance is destroyed
	// note the runtime may keep the object after this call for recycling; be sure
	// to release/recycle/reset any references to other objects in this function.
	instanceProto.onDestroy = function ()
	{
	};
	
	// called when saving the full state of the game
	instanceProto.saveToJSON = function ()
	{
		// return a Javascript object containing information about your object's state
		// note you MUST use double-quote syntax (e.g. "property": value) to prevent
		// Closure Compiler renaming and breaking the save format
		return {};
	};
	
	// called when loading the full state of the game
	instanceProto.loadFromJSON = function (o) { };
	
	// only called if a layout object - draw to a canvas 2D context
	instanceProto.draw = function(ctx) { };
	
	// only called if a layout object in WebGL mode - draw to the WebGL context
	// 'glw' is not a WebGL context, it's a wrapper - you can find its methods in GLWrap.js in the install
	// directory or just copy what other plugins do.
	instanceProto.drawGL = function (glw) { };
	
	// The comments around these functions ensure they are removed when exporting, since the
	// debugger code is no longer relevant after publishing.
	/**BEGIN-PREVIEWONLY**/
	instanceProto.getDebuggerValues = function (propsections)
	{
		// Append to propsections any debugger sections you want to appear.
		// Each section is an object with two members: "title" and "properties".
		// "properties" is an array of individual debugger properties to display
		// with their name and value, and some other optional settings.
		propsections.push({
			"title": "My debugger section",
			"properties": [
				// Each property entry can use the following values:
				// "name" (required): name of the property (must be unique within this section)
				// "value" (required): a boolean, number or string for the value
				// "html" (optional, default false): set to true to interpret the name and value
				//									 as HTML strings rather than simple plain text
				// "readonly" (optional, default false): set to true to disable editing the property
				
				// Example:
				// {"name": "My property", "value": this.myValue}
			]
		});
	};
	
	instanceProto.onDebugValueEdited = function (header, name, value)
	{
		// Called when a non-readonly property has been edited in the debugger. Usually you only
		// will need 'name' (the property name) and 'value', but you can also use 'header' (the
		// header title for the section) to distinguish properties with the same name.
		if (name === "My property")
			this.myProperty = value;
	};
	/**END-PREVIEWONLY**/

	//////////////////////////////////////
	// Conditions
	function Cnds() {};

	Cnds.prototype.onLeaderboardRetrieveSuccess = function(leaderboardName) {
		console.log("Checking for " + leaderboardName);
		return typeof leaderboards[leaderboardName] != "undefined"
					&& leaderboards[leaderboardName] != null;
	}

	Cnds.prototype.onLeaderboardRetrieveFailure = function(leaderboardName) {
		console.log("Checking for " + leaderboardName);
		return false;
	}

	Cnds.prototype.onLoginPlayerSuccess = function() {
		return false;
	}

	Cnds.prototype.onLoginPlayerFailure = function() {
		return false;
	}

	// the example condition
	/*Cnds.prototype.MyCondition = function (myparam)
	{
		// return true if number is positive
		return myparam >= 0;
	};*/
	
	// ... other conditions here ...
	
	pluginProto.cnds = new Cnds();
	
	//////////////////////////////////////
	// Actions
	function Acts() {};

	// the example action
	Acts.prototype.loginPlayer = function(timeout) {
		console.log("loginPlayer");
		// TODO: retrieve login (username, password)
		/*jQuery.ajax({
			"url": "http://www.pilgrimsgamestudio.com/sservices/login_player.php"
			"data": {}, // TODO retrieve data from device
			"timeout": getValidTimeout(timeout),
			"dataType": "json",
			"type": "POST",
			"success": function(data, textStatus, jqXHR) {
				// TODO: Define how to handle success
			},
			"error": function(jqXHR, textStatus, errorThrown) {
				// TODO: Define how to handle error
			}
		});*/
	};
	
	Acts.prototype.logMessage = function (level, message) {
		if (typeof console !== "undefined" && console != null) {
			if (level.toLowerCase() === "debug") {
				console.debug(formatLogMessage(level, message, loggingPattern));
			} else if (level.toLowerCase() === "info") {
				console.info(formatLogMessage(level, message, loggingPattern));
			} else if (level.toLowerCase() === "error") {
				console.error(formatLogMessage(level, message, loggingPattern));
			} else {
				console.log(formatLogMessage(level, message, loggingPattern));
			}
		}
	}

	function formatLogMessage(level, message, pattern) {
		return parseDate(new Date(), pattern.replace("{m}", message)
				.replace("{level}, level"));
	}
	
	Acts.prototype.getLeaderboardByName = function(leaderboardName, timeout) {
		console.log("Get leaderboard " + leaderboardName);
		jQuery.ajax({
			"url": "http://www.pilgrimsgamestudio.com/sservices/get_leaderboard.php"
			"data": {"name": leaderboardName}, 
			"timeout": getValidTimeout(timeout),
			"dataType": "json",
			"type": "GET",
			"success": function(data, textStatus, jqXHR) {
				var leaderboard = new Leaderboard(data.id, data.name, data.scores);
				leaderboards[leaderboard.name] = leaderboard;
				this.runtime.trigger(cr.plugins_.PilgrimsSocialServices.prototype.cnds.onLeaderboardRetrieveSuccess, this);
			},
			"error": function(jqXHR, textStatus, errorThrown) {
				this.runtime.trigger(cr.plugins_.PilgrimsSocialServices.prototype.cnds.onLeaderboardRetrieveSuccess, this);
			}
		});
	}
	
	Acts.prototype.registerPlayer = function(playerName, userAction, timeout) {
		playerName = ("" == playerName || "" === playerName) ? null : playerName;
		userAction = userAction == 1;
		console.log("Registering player " + playerName + " as user action = " + userAction);
		/*jQuery.ajax({
			"url": "http://www.pilgrimsgamestudio.com/sservices/register_player.php"
			"data": {}, // TODO retrieve data from device
			"timeout": getValidTimeout(timeout),
			"dataType": "json",
			"type": "POST",
			"success": function(data, textStatus, jqXHR) {
				// TODO: Define how to handle success
			},
			"error": function(jqXHR, textStatus, errorThrown) {
				// TODO: Define how to handle error
			}
		});*/
	}
	
	function getValidTimeout(timeout) {
		var ret = this.defaultTimeout;
		if (timeout != null && typeof timeout == "number"
				&& timeout > 0) {
			ret = timeout;
		} else if(timeout != null && typeof timeout == "string"
				&& !isNaN(parseInt(timeout))) {
			ret = parseInt(timeout)
		}
		
		return ret;
	}
	
	// ... other actions here ...
	
	pluginProto.acts = new Acts();
	
	//////////////////////////////////////
	// Expressions
	function Exps() {};
	
	function isPlayerLogged() {
		return typeof loggedPlayer !== "undefined" && loggedPlayer != null;
	} 

	Exps.prototype.isPlayerLogged = function(ret) {
		ret.set_int(isPlayerLogged() ? 1 : 0);
	}

	Exps.prototype.isPlayerLogged = function(ret) {
		ret.set_int(isPlayerLogged() ? player.name : null);
	}

	pluginProto.exps = new Exps();

}());
