// Scripts in this file are included in both the IDE and runtime, so you only
// need to write scripts common to both once.
// foobar

var leaderboards = {};

var dateSupportedTokens = ["dd", "MM", "yyyy", "yy", "hh", "mm", "sss", "ss"];

function Game(id, name) {
	this.id = typeof id != "undefined" ? (id > 0 ? id : null) : null;
	this.name = typeof name != "undefined" ? name : null;
}

function Leaderboard(id, name, scores) {
	this.id = typeof id != "undefined" ? id : null;
	this.name = typeof name != "undefined" ? name : null;
	this.scores = typeof scores != "undefined" ? scores : [];
	this.retrievedDate = new Date();

	for (var i = 0; i < this.scores.length; i++) {
		this.scores.push(new Score(this.scores[i].id, this.scores[i].score
			, this.scores[i].player));
	}
}

function Score(id, score, player) {
	this.id = typeof id != "undefined" ? id : null;
	this.score = typeof name != "undefined" ? score : 0;
	this.player = typeof scores != "undefined" ? player : null;

	if (this.player != null) {
		this.player = new Player(this.player.id, this.player.name);
	}
}

function Player(id, name, uuid) {
	this.id = typeof id != "undefined" ? id : null;
	this.name = typeof name != "undefined" ? name : null;
	this.uuid = typeof uuid != "undefined" ? uuid : null; 
}

function parseDate(date, pattern) {
	if (date != null && date instanceof Date
			&& pattern != null && typeof pattern == "string") {
			var extractedTokens = dateSupportedTokens
					.filter(function(el) {return pattern.indexOf(el) >= 0;});
			var parsed = extractedTokens.reduce(function(prev, curr) {
				return prev.indexOf(curr) >= 0 ?
					prev.replace(curr, extractDateFragment(date, curr)) :
					prev;
			}, pattern);

			return parsed;
	} else {
		return "";
	}
}

function extractDateFragment(date, pattern) {
	var extract = "";
	if (date != null && date instanceof Date
			&& pattern != null && typeof pattern == "string") {
		switch (pattern) {
			case "dd":
				extract = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();
				break;
			case "MM":
				extract = date.getMonth() + 1 < 10 ? "0" + (date.getMonth() + 1) : (date.getMonth() + 1);
				break;
			case "yyyy":
				extract = date.getFullYear();
				break;
			case "yy":
				extract = date.getFullYear() % 1000 < 10 ? "0" + (date.getFullYear() % 1000) : date.getFullYear() % 1000;
				break;
			case "hh":
				extract = date.getHours() < 10 ? "0" + date.getHours() : date.getHours();
				break;
			case "mm":
				extract = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes();
				break;
			case "sss":
				extract = date.getMilliseconds();
				break;
			case "ss":
				extract = date.getSeconds() < 10 ? "0" + date.getSeconds() : date.getSeconds();
				break;
		}
	}

	return extract;
}

var mockGuid = (function() {
  function s4() {
    return Math.floor((1 + Math.random()) * 0x10000)
               .toString(16)
               .substring(1);
  }
  return function() {
    return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
           s4() + '-' + s4() + s4() + s4();
  };
})();

function MockExpressionRet() {
}

MockExpressionRet.prototype.set_int = function(val) {
	this.ret = val;
}

MockExpressionRet.prototype.set_float = function(val) {
	this.ret = val;
}

MockExpressionRet.prototype.set_string = function(val) {
	this.ret = val;
}

MockExpressionRet.prototype.set_any = function(val) {
	this.ret = val;
}

