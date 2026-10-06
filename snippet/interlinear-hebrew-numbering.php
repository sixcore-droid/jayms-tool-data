/**
 * JAYMS - Interlinear links: English reference in, Hebrew numbering out
 *
 * The interlinear keeps Hebrew chapter and verse numbering, which is the
 * scholarly reading and is deliberate. Every other tool on the site holds
 * English references. jaymsInterlinearURL, which lives in the jayms-core
 * plugin and prints at wp_footer priority 5, took the English reference
 * straight through, so 1,447 links across six tools opened the wrong verse:
 * Joel 3:1 on a card is Joel 4:1 in the interlinear.
 *
 * This wraps that function at priority 6 rather than editing the plugin. It is
 * additive and reversible: disable this snippet and the old behaviour is back.
 *
 * It is a wrapper and not a data field because the runner builds the link, the
 * verse box and the label from one reference string. A Hebrew number in
 * bibleRefOverride would have fixed the link and broken the other two.
 */
add_action(
	'wp_footer',
	function () {
		if ( is_admin() ) { return; }
		$js = <<<'JAYMSJS'
(function(){
  var orig = window.jaymsInterlinearURL;
  if (typeof orig !== "function") { return; }

  // The interlinear is numbered the Hebrew way, which is the scholarly reading
  // and is staying. Every other tool holds English references, so Joel 3:1 on a
  // card is Joel 4:1 in the interlinear and the link was landing a chapter out.
  // 140 rules, each [book, englishChapter, hebrewChapter, fromVerse, toVerse,
  // verseOffset], verified against all 1,970 shifts in STEPBible TVTMS.
  var R = [["1 Chronicles",6,5,1,15,26],["1 Chronicles",6,6,16,81,-15],["1 Chronicles",12,12,4,40,1],["1 Kings",4,5,21,34,-20],["1 Kings",5,5,1,18,14],["1 Kings",22,22,43,53,1],["1 Samuel",20,21,42,42,-41],["1 Samuel",21,21,1,15,1],["1 Samuel",23,24,29,29,-28],["1 Samuel",24,24,1,22,1],["2 Chronicles",2,1,1,1,17],["2 Chronicles",2,2,2,18,-1],["2 Chronicles",14,13,1,1,22],["2 Chronicles",14,14,2,15,-1],["2 Kings",11,12,21,21,-20],["2 Kings",12,12,1,21,1],["2 Samuel",18,19,33,33,-32],["2 Samuel",19,19,1,43,1],["Daniel",4,3,1,3,30],["Daniel",4,4,4,37,-3],["Daniel",5,6,31,31,-30],["Daniel",6,6,1,28,1],["Deuteronomy",12,13,32,32,-31],["Deuteronomy",13,13,1,18,1],["Deuteronomy",22,23,30,30,-29],["Deuteronomy",23,23,1,25,1],["Deuteronomy",29,28,1,1,68],["Deuteronomy",29,29,2,29,-1],["Ecclesiastes",5,4,1,1,16],["Ecclesiastes",5,5,2,20,-1],["Exodus",8,7,1,4,25],["Exodus",8,8,5,32,-4],["Exodus",22,21,1,1,36],["Exodus",22,22,2,31,-1],["Ezekiel",20,21,45,49,-44],["Ezekiel",21,21,1,32,5],["Genesis",31,32,55,55,-54],["Genesis",32,32,1,32,1],["Hosea",1,2,10,11,-9],["Hosea",2,2,1,23,2],["Hosea",11,12,12,12,-11],["Hosea",12,12,1,14,1],["Hosea",13,14,16,16,-15],["Hosea",14,14,1,9,1],["Isaiah",9,8,1,1,22],["Isaiah",9,9,2,21,-1],["Isaiah",64,63,1,1,18],["Isaiah",64,64,2,12,-1],["Jeremiah",9,8,1,1,22],["Jeremiah",9,9,2,26,-1],["Job",41,40,1,8,24],["Job",41,41,9,34,-8],["Joel",2,3,28,32,-27],["Joel",3,4,1,21,0],["Jonah",1,2,17,17,-16],["Jonah",2,2,1,10,1],["Leviticus",6,5,1,7,19],["Leviticus",6,6,8,30,-7],["Malachi",4,3,1,6,18],["Micah",5,4,1,1,13],["Micah",5,5,2,15,-1],["Nahum",1,2,15,15,-14],["Nahum",2,2,1,13,1],["Nehemiah",4,3,1,6,32],["Nehemiah",4,4,7,23,-6],["Nehemiah",7,7,69,73,-1],["Nehemiah",9,10,38,38,-37],["Nehemiah",10,10,1,39,1],["Numbers",16,17,36,50,-35],["Numbers",17,17,1,13,15],["Numbers",26,25,1,1,18],["Numbers",29,30,40,40,-39],["Numbers",30,30,1,16,1],["Psalm",3,3,1,8,1],["Psalm",4,4,1,8,1],["Psalm",5,5,1,12,1],["Psalm",6,6,1,10,1],["Psalm",7,7,1,17,1],["Psalm",8,8,1,9,1],["Psalm",9,9,1,20,1],["Psalm",12,12,1,8,1],["Psalm",13,13,1,5,1],["Psalm",18,18,1,50,1],["Psalm",19,19,1,14,1],["Psalm",20,20,1,9,1],["Psalm",21,21,1,13,1],["Psalm",22,22,1,31,1],["Psalm",30,30,1,12,1],["Psalm",31,31,1,24,1],["Psalm",34,34,1,22,1],["Psalm",36,36,1,12,1],["Psalm",38,38,1,22,1],["Psalm",39,39,1,13,1],["Psalm",40,40,1,17,1],["Psalm",41,41,1,13,1],["Psalm",42,42,1,11,1],["Psalm",44,44,1,26,1],["Psalm",45,45,1,17,1],["Psalm",46,46,1,11,1],["Psalm",47,47,1,9,1],["Psalm",48,48,1,14,1],["Psalm",49,49,1,20,1],["Psalm",51,51,1,19,2],["Psalm",52,52,1,9,2],["Psalm",53,53,1,6,1],["Psalm",54,54,1,7,2],["Psalm",55,55,1,23,1],["Psalm",56,56,1,13,1],["Psalm",57,57,1,11,1],["Psalm",58,58,1,11,1],["Psalm",59,59,1,17,1],["Psalm",60,60,1,12,2],["Psalm",61,61,1,8,1],["Psalm",62,62,1,12,1],["Psalm",63,63,1,11,1],["Psalm",64,64,1,10,1],["Psalm",65,65,1,13,1],["Psalm",67,67,1,7,1],["Psalm",68,68,1,35,1],["Psalm",69,69,1,36,1],["Psalm",70,70,1,5,1],["Psalm",75,75,1,10,1],["Psalm",76,76,1,12,1],["Psalm",77,77,1,20,1],["Psalm",80,80,1,19,1],["Psalm",81,81,1,16,1],["Psalm",83,83,1,18,1],["Psalm",84,84,1,12,1],["Psalm",85,85,1,13,1],["Psalm",88,88,1,18,1],["Psalm",89,89,1,52,1],["Psalm",92,92,1,15,1],["Psalm",102,102,1,28,1],["Psalm",108,108,1,13,1],["Psalm",140,140,1,13,1],["Psalm",142,142,1,7,1],["Song of Solomon",6,7,13,13,-12],["Song of Solomon",7,7,1,13,1],["Zechariah",1,2,18,21,-17],["Zechariah",2,2,1,13,4]];

  var ALIAS = { "Psalms": "Psalm" };

  function toHebrew(ref) {
    var m = /^(.*?)\s+(\d+):(\d+)(?:-(\d+))?$/.exec(String(ref || "").trim());
    if (!m) { return ref; }
    var book = m[1], ch = +m[2], v1 = +m[3], v2 = m[4] ? +m[4] : null;
    var keys = [book];
    if (ALIAS[book]) { keys.push(ALIAS[book]); }
    for (var k = 0; k < keys.length; k++) {
      for (var i = 0; i < R.length; i++) {
        var r = R[i];
        if (r[0] !== keys[k] || r[1] !== ch) { continue; }
        if (v1 < r[3] || v1 > r[4]) { continue; }
        var out = book + " " + r[2] + ":" + (v1 + r[5]);
        // A range carries over only when both ends sit under one rule;
        // otherwise the start verse alone is still the right place to land.
        if (v2 !== null && v2 >= r[3] && v2 <= r[4]) { out += "-" + (v2 + r[5]); }
        return out;
      }
    }
    return ref;
  }

  // Only the href changes. The link text, the verse the box fetches and the
  // label above it all go on using the English reference, which is what the
  // reader should see.
  window.jaymsInterlinearURL = function (reference) { return orig(toHebrew(reference)); };
  window.jaymsToHebrewRef = toHebrew;
})();
JAYMSJS;

		echo "\n<script>\n" . $js . "\n</script>\n";
	},
	6
);