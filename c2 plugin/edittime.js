//foobar
function GetPluginSettings()
{
	return {
		"name":			"Pilgrim's Social Services",				// as appears in 'insert object' dialog, can be changed as long as "id" stays the same
		"id":			"PilgrimsSocialServices",				// this is used to identify this plugin and is saved to the project; never change it
		"version":		"0.0.1",					// (float in x.y format) Plugin version - C2 shows compatibility warnings based on this
		"description":	"Pilgrim's social services, used for pilgrim's games to log players, record scores, etc.",
		"author":		"Pilgrim's Game Studio",
		"help url":		"http://www.pilgrimsgamestudio.com/",
		"category":		"Web",				// Prefer to re-use existing categories, but you can set anything here
		"type":			"object",				// either "world" (appears in layout and is drawn), else "object"
		"rotatable":	false,					// only used when "type" is "world".  Enables an angle property on the object.
		"flags":		0						// uncomment lines to enable flags...
						| pf_singleglobal		// exists project-wide, e.g. mouse, keyboard.  "type" must be "object".
					//	| pf_texture			// object has a single texture (e.g. tiled background)
					//	| pf_position_aces		// compare/set/get x, y...
					//	| pf_size_aces			// compare/set/get width, height...
					//	| pf_angle_aces			// compare/set/get angle (recommended that "rotatable" be set to true)
					//	| pf_appearance_aces	// compare/set/get visible, opacity...
					//	| pf_tiling				// adjusts image editor features to better suit tiled images (e.g. tiled background)
					//	| pf_animations			// enables the animations system.  See 'Sprite' for usage
					//	| pf_zorder_aces		// move to top, bottom, layer...
					//  | pf_nosize				// prevent resizing in the editor
					//	| pf_effects			// allow WebGL shader effects to be added
					//  | pf_predraw			// set for any plugin which draws and is not a sprite (i.e. does not simply draw
												// a single non-tiling image the size of the object) - required for effects to work properly
	};
};

////////////////////////////////////////
// Parameter types:
// AddNumberParam(label, description [, initial_string = "0"])			// a number
// AddStringParam(label, description [, initial_string = "\"\""])		// a string
// AddAnyTypeParam(label, description [, initial_string = "0"])			// accepts either a number or string
// AddCmpParam(label, description)										// combo with equal, not equal, less, etc.
// AddComboParamOption(text)											// (repeat before "AddComboParam" to add combo items)
// AddComboParam(label, description [, initial_selection = 0])			// a dropdown list parameter
// AddObjectParam(label, description)									// a button to click and pick an object type
// AddLayerParam(label, description)									// accepts either a layer number or name (string)
// AddLayoutParam(label, description)									// a dropdown list with all project layouts
// AddKeybParam(label, description)										// a button to click and press a key (returns a VK)
// AddAnimationParam(label, description)								// a string intended to specify an animation name
// AddAudioFileParam(label, description)								// a dropdown list with all imported project audio files

////////////////////////////////////////
// Conditions

// AddCondition(id,					// any positive integer to uniquely identify this condition
//				flags,				// (see docs) cf_none, cf_trigger, cf_fake_trigger, cf_static, cf_not_invertible,
//									// cf_deprecated, cf_incompatible_with_triggers, cf_looping
//				list_name,			// appears in event wizard list
//				category,			// category in event wizard list
//				display_str,		// as appears in event sheet - use {0}, {1} for parameters and also <b></b>, <i></i>
//				description,		// appears in event wizard dialog when selected
//				script_name);		// corresponding runtime function name

var addParamFunctions = {
	"number": 			AddNumberParam,
	"string":				AddStringParam,
	"anyType":			AddAnyTypeParam,
	"cmp":					AddCmpParam,
	"comboOption":	AddComboParamOption,
	"combo":				AddComboParam,
	"object":				AddObjectParam,
	"layer":				AddLayerParam,
	"layout":				AddLayoutParam,
	"keyb":					AddKeybParam,
	"animation":		AddAnimationParam,
	"audio":				AddAudioFileParam
}

function isIDValid(id) {
	return !isNaN(id) && id != null && id > 0;
}

function Parameter(type, label, description, initial_value) {
	this.type = type;
	this.label = label;
	this.description = description;
	this.initial_value = initial_value;
}

function AddParameter(param) {
	if (typeof param.type == "string" && param.type != null 
				&& typeof param.label != "string" && param.label != null
				&& typeof param.description != "string" && param.description != null) {
		var type = param.toLowerCase();
		var func = addParamFunctions[type];
		if (typeof func == "function") {
			func.apply(this, [param.label, param.description, param.initial_value]);
		} else {
			alert("Error searching " + type + "in addParamFunctions. Supported types: number, string, anyType, cmp, comboOption, combo, object, layer, layout, keyb, animation, audio.");
		}
	} else {
		alert("The parameter " + param.label + " has no type, label or description, there are mandatory");
	}
}

function AddResponseHandlerConditions(id_success_handler, id_failure_handler
		, flags, list_name, category, display_str, description
		, success_script_name, failure_script_name, params) {
	var i = 0;
	if (typeof params == "undefined") {
		params = [];
	}
	// First add the success handler
	if (isIDValid(id_success_handler)) {
		for (i = 0; i < params.length; i++) {
			AddParameter(params[i]);
		}
		AddCondition(id_success_handler, flags, "[SUCCESS] " + list_name, category, "[SUCCESS] " + display_str, description, success_script_name);
	}
	// Last add the failure handler
	if (isIDValid(id_failure_handler)) {
		for (i = 0; i < params.length; i++) {
			AddParameter(params[i]);
		}
		AddCondition(id_failure_handler, flags, "[FAILURE] " + list_name, category, "[FAILURE] " + display_str, description, failure_script_name);
	}
}

AddStringParam("Leaderboard name", "Name of the leaderboard to be retrieve.", "\"default\"");
AddCondition(1, cf_trigger, "Leaderboard retrieved successfuly", "Social Services"
	, "Leaderboard {0} retrieve successfully", "Check this condition when you have requested for a leaderboard"
	, "onLeaderboardRetrieveSuccess");
AddStringParam("Leaderboard name", "Name of the leaderboard to be retrieve.", "\"default\"");
AddCondition(2, cf_trigger, "Leaderboard retrieved failed", "Social Services"
	, "Leaderboard {0} retrieve failed", "Check this condition when you have requested for a leaderboard"
	, "onLeaderboardRetrieveFailure");

AddResponseHandlerConditions(3, 4, cf_trigger, " Log-in Player", "Social Services", "Player logged in", "Check this condition when you have requested for a user to be logged in", "onLoginPlayerSuccess", "onLoginPlayerFailure");
AddResponseHandlerConditions(5, 6, cf_trigger, " Register Player", "Social Services", "Player registered", "Check this condition when you have requested a player to be registered", "onPlayerRegisterSuccess", "onPlayerRegisterFailure");

AddCondition(7, cf_none, "Is Player logged-in?", "Social Services", "Is Player logged-in?", "Check if a player is logged in", "isPlayerLoggedIn");

////////////////////////////////////////
// Actions

// AddAction(id,				// any positive integer to uniquely identify this action
//			 flags,				// (see docs) af_none, af_deprecated
//			 list_name,			// appears in event wizard list
//			 category,			// category in event wizard list
//			 display_str,		// as appears in event sheet - use {0}, {1} for parameters and also <b></b>, <i></i>
//			 description,		// appears in event wizard dialog when selected
//			 script_name);		// corresponding runtime function name

AddNumberParam("Request timeout", "Enter the request timeout (in seconds) for this server interaction.", "60");
AddAction(100, af_none, "Log-in player", "Social Services", "Log-in player", "Logs in the player using the game.", "loginPlayer");

AddStringParam("Log level", "Defines the log level to be used in the loggin Enum{debug, info, error}", "\"info\"");
AddStringParam("Message", "Message to log to the console");
AddAction(101, af_none, "Log message", "General", "Logs message {0}.{1}", "Log message to Chrome/Firebug console for debug and error", "logMessage");

AddStringParam("Leaderboard name", "Name of the leaderboard to be retrieved.", "\"default\"");
AddNumberParam("Request timeout", "Enter the request timeout (in seconds) for this server interaction.", "60");
AddAction(102, af_none, "Get Leaderboard by name", "Social Services", "Get Leaderboard {0}", "Retrives leaderboard from server with all its scores, you must watch for 'Leaderboard retrieved successfuly' condition", "getLeaderboardByName");

AddStringParam("Player name", "Specifies the name to be used for the player");
AddNumberParam("Is User action?", "Specifies if the registering is being requested by the user, or if it's automatic 1: true, otherwise false", "0");
AddNumberParam("Request timeout", "Enter the request timeout (in seconds) for this server interaction.", "60");
AddAction(103, af_none, "Register player", "Social Services", "Register player {0} being {1} user action", "Registers a player in the server", "registerPlayer");

AddNumberParam("Score", "Specifies the score obtained", "0");
AddAction(104, af_none, "Register Score", "Social Services", "Register {0} points to logged player", "Registers score points", "registerScore");

// AddStringParam("Message", "Enter a string to alert.");
// AddAction(0, af_none, "Alert", "My category", "Alert {0}", "Description for my action!", "MyAction");

////////////////////////////////////////
// Expressions

// AddExpression(id,			// any positive integer to uniquely identify this expression
//				 flags,			// (see docs) ef_none, ef_deprecated, ef_return_number, ef_return_string,
//								// ef_return_any, ef_variadic_parameters (one return flag must be specified)
//				 list_name,		// currently ignored, but set as if appeared in event wizard
//				 category,		// category in expressions panel
//				 exp_name,		// the expression name after the dot, e.g. "foo" for "myobject.foo" - also the runtime function name
//				 description);	// description in expressions panel

AddExpression(1001, ef_return_number, "Is any player logged?", "Social Services", "isPlayerLogged", "Check if a player is logged in the current play session. 1 == true, otherwise == false");
AddExpression(1001, ef_return_string, "Logged player name", "Social Services", "loggedPlayerName", "Get the logged player name in this device, returns an empty string if there isn't a logged player.");
AddExpression(1002, ef_return_string, "Device ID", "General", "deviceId", "Get the device id that is running the game. If testing in a browser provides a seudo-GUID.");


////////////////////////////////////////
ACESDone();

////////////////////////////////////////
// Array of property grid properties for this plugin
// new cr.Property(ept_integer,		name,	initial_value,	description)		// an integer value
// new cr.Property(ept_float,		name,	initial_value,	description)		// a float value
// new cr.Property(ept_text,		name,	initial_value,	description)		// a string
// new cr.Property(ept_color,		name,	initial_value,	description)		// a color dropdown
// new cr.Property(ept_font,		name,	"Arial,-16", 	description)		// a font with the given face name and size
// new cr.Property(ept_combo,		name,	"Item 1",		description, "Item 1|Item 2|Item 3")	// a dropdown list (initial_value is string of initially selected item)
// new cr.Property(ept_link,		name,	link_text,		description, "firstonly")		// has no associated value; simply calls "OnPropertyChanged" on click

var property_list = [
	new cr.Property(ept_text, "logging_pattern", "hh:mm:ss:sss {m}", "Defines the logging pattern to be used (i.e. hh:mm:ss:sss {m} or dd/MM/yyyy {m})."),
	new cr.Property(ept_integer, "Default timeout",	60,	"Defines the default timeout for all operations that must connect to the server."),
	new cr.Property(ept_integer, "Game ID",	0,	"Defines the ID of this game, if it's not supplied it will be fetched from the server the first game session."),
	new cr.Property(ept_text, "Game name", "SparkCity", "Defines the name of this game, it's used when retrieving all data from this game.")
	];
	
// Called by IDE when a new object type is to be created
function CreateIDEObjectType()
{
	return new IDEObjectType();
}

// Class representing an object type in the IDE
function IDEObjectType()
{
	assert2(this instanceof arguments.callee, "Constructor called as a function");
}

// Called by IDE when a new object instance of this type is to be created
IDEObjectType.prototype.CreateInstance = function(instance)
{
	return new IDEInstance(instance);
}

// Class representing an individual instance of an object in the IDE
function IDEInstance(instance, type)
{
	assert2(this instanceof arguments.callee, "Constructor called as a function");
	
	// Save the constructor parameters
	this.instance = instance;
	this.type = type;
	
	// Set the default property values from the property table
	this.properties = {};
	
	for (var i = 0; i < property_list.length; i++)
		this.properties[property_list[i].name] = property_list[i].initial_value;
		
	// Plugin-specific variables
	// this.myValue = 0...
}

// Called when inserted via Insert Object Dialog for the first time
IDEInstance.prototype.OnInserted = function()
{
}

// Called when double clicked in layout
IDEInstance.prototype.OnDoubleClicked = function()
{
}

// Called after a property has been changed in the properties bar
IDEInstance.prototype.OnPropertyChanged = function(property_name)
{
}

// For rendered objects to load fonts or textures
IDEInstance.prototype.OnRendererInit = function(renderer)
{
}

// Called to draw self in the editor if a layout object
IDEInstance.prototype.Draw = function(renderer)
{
}

// For rendered objects to release fonts or textures
IDEInstance.prototype.OnRendererReleased = function(renderer)
{
}
