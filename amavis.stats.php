<?php

/**

  amavis-stats.php - build rrd graphs from amavisd-new collected data.

  Copyright (C) 2004, 2005, 2006 Dale Walsh (buildsmart@daleenterprise.com)

  Copyright (C) 2003, 2004 Mark Lawrence (nomad@null.net)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License with
  the Debian GNU/Linux distribution in file /usr/share/common-licenses/GPL;
  if not, write to the Free Software Foundation, Inc., 59 Temple Place,
  Suite 330, Boston, MA  02111-1307  USA

  On Debian systems, the complete text of the GNU General Public
  License, version 2, can be found in /usr/share/common-licenses/GPL-2.

 */

function dummy(){
// This is just a place-holder
}
/* some basic compatibility for PHP 4.x.x and to reassign HTTP variables (incase register_globals is off) */
if (!isset($_GET)) { $_GET = &$HTTP_GET_VARS; }
if (!isset($_POST)) { $_POST = &$HTTP_POST_VARS; }
if (!isset($_SESSION)) { $_SESSION = &$HTTP_SESSION_VARS; }
if (!isset($_SERVER)) { $_SERVER = &$HTTP_SERVER_VARS; }
if (!isset($_COOKIE)) { $_COOKIE = &$HTTP_COOKIE_VARS; }
if (!isset($_FILES)) { $_FILES = &$HTTP_POST_FILES; }


/* Get and set the defaults for various options */
define('IN_AS', true);
$as_pkg						= "amavis-stats";
$as_root_path				= "/usr/local/share/amavis-stats";
$as_version					= "0.1.22";
$button						= NULL;
$button_name				= NULL;
$button_selected			= NULL;
$button_unselected			= NULL;
$dbg_msg					= NULL;
$font_color_selected		= NULL;
$font_color_unselected		= NULL;
$debug						= false;
$host						= "";
$imgdir						= "img";
$legend						= false;
$libdir						= "/usr/local/var/lib/$as_pkg";
$me							= $_SERVER["SCRIPT_NAME"];
$namefile					= $libdir . "/$as_pkg.names";
$now						= time();
$out_msg					= NULL;
$rate						= 3600;
$refresh					= NULL;
$show_graph_period			= false;
$span						= "yearly";
$t_path						= "/templates/";
$template					= NULL;
$text						= NULL;
$title						= NULL;
$ttfont						= NULL;
$ttsize						= NULL;

require('ressources/amavis-stat/template.php');
include('ressources/amavis-stat/amavis-stats.php.conf');

if (!empty($_GET)) while (list($name, $value) = each($_GET)) $$name = $value;
if (!empty($_POST)) while (list($name, $value) = each($_POST)) $$name = $value;

$debug = (($debug == "true" || $debug == 1) ? "true":"false");
$legend = (($legend == "true" || $legend == 1) ? "true":"false");
$show_graph_period = (($show_graph_period == "true" || $show_graph_period == 1) ? true:false);

if (!$refresh && $refresh !='0') $refresh = 300;

$template_path			= $template ? $t_path . $template : $t_path . 'standard';
$button_selected		= $button_selected ? $button_selected : 'blue';
$button_unselected		= $button_unselected ? $button_unselected : 'white';

if (!$ttfont) $ttfont	= $ttfont ? $ttfont : $as_root_path."/ChalkboardBold.ttf"; # default font type

if(!isset($ttsize)) $ttsize = 9; # default font size

// The rrdstep value MUST match that which is used by the perl script.
$rrdstep		= 300;
$rrd			= "shared-library";

if (function_exists('rrd_graph') && !@dl('rrdtool.so')) {
	$rrd = "PHP embedded";
}

if (!function_exists('rrd_graph') && !@dl('rrdtool.so')) {
	$rrd = "command-line";

	if (file_exists("/usr/local/rrdtool/bin/rrdtool")) {
		$rrdtool = "/usr/local/rrdtool/bin/rrdtool";
	} elseif (file_exists("/usr/local/bin/rrdtool")) {
		$rrdtool = "/usr/local/bin/rrdtool";
	} elseif (file_exists("/usr/bin/rrdtool")) {
		$rrdtool = "/usr/bin/rrdtool";
	}

	function rrd_graph ($img = '', $opts = '', $count = '') {
		global $rrd_error_val, $rrdtool;

		unset($output);

		$cmd = "$rrdtool graph $img '". implode("' \\\n'",$opts) . "'" . " 2>&1";
		echo $cmd;
		$out = exec($cmd, $output, $rrd_error_val);

		if ($rrd_error_val) {
			if (!is_numeric($rrd_error_val)) {
				$rrd_error_val = $output[0];
			}
			return false;
		}

		$retval = array();

		if (preg_match("/(\d+)x(\d+)/", $output[0], $matches)) {
			$retval["xsize"] = $matches[1];
			$retval["ysize"] = $matches[2];
		} else {
			$rrd_error_val = $output[0];
			return false;
		}

		array_shift($output);

		$retval["calcpr"] = array();
		foreach ($output as $index => $value) {
			$retval["calcpr"][$index] = $value;
		}
		return $retval;
	}

	function rrd_error() {
		global $rrd_error_val;
		return $rrd_error_val;
	}
}

function pre_graph($img, $opts, $count) {
	$tmp = "rrd_graph"; $$img = $opts;
	$$tmp = array("$img" => $opts);
	aslog($$tmp,0,'rrd_graph');
	$pre_ret = rrd_graph($img, $opts, $count);
	aslog($pre_ret, 0, 'ret');
	return $pre_ret;
}

/**
 * make_button($text, $button)
 *
 * $test	- text to display on the png
 * $button	- color of png to select
 *
 * Builds a png with the supplied text.
 */
function make_button($text = 'example', $button = 'blue') {
	global	$as_root_path, $button_name, $button_selected, $button_unselected,$x,$y,
			$ttsize, $ttfont, $font_color_selected, $font_color_unselected, $saturation, $huerotation;

	$height       = 26;    # how tall is the button
	$cap_width    = 15;    # how wide are the left and right rounded caps
	$cap_overlap  = 5;     # how many pixels should the text intrude into each cap
	$center_width = 20;    # how wide is the stretchable center part

	$color = $button ? $button : 'white';
	$font_color = $button == $button_unselected ? ($font_color_unselected ?  $font_color_unselected : array(51, 51, 51) ) : ($font_color_selected ?  $font_color_selected : array(51, 51, 51) );

	$left_name   = $button_name ? "${button_name}_left_${color}.png" : "tab_left_${color}.png";
	$center_name = $button_name ? "${button_name}_center_${color}.png" : "tab_center_${color}.png";
	$right_name  = $button_name ? "${button_name}_right_${color}.png" : "tab_right_${color}.png";

	# Load in the three images needed to create the button
	#
	$img_button_left   = ImageCreateFromPNG($left_name) or die("Unable to open $left_name");
	$img_button_center = ImageCreateFromPNG($center_name) or die("Unable to open $center_name");
	$img_button_right  = ImageCreateFromPNG($right_name) or die("Unable to open $right_name");

	$ttbbox = imagettfbbox($ttsize, 0, $ttfont, $text);
	$ttwidth = $ttbbox[2] - $ttbbox[0];
	$img_width = $ttwidth + 2 * ($cap_width - $cap_overlap);

	$imout = ImageCreate($img_width, $height) or die("Unable to create new image");

	# Fill the empty image canvas by tiling the center image
	#
	for ($i =0; $i < $img_width / $center_width; $i++) {
		ImageCopy($imout, $img_button_center, $i * $center_width, 0, 0, 0, $center_width, $height);
	}

	# Now add the left and right cap, this finishes the button
	#
	ImageCopy($imout, $img_button_left, 0, 0, 0, 0, $cap_width, $height);
	ImageCopy($imout, $img_button_right, $img_width - $cap_width, 0, 0, 0, $cap_width, $height);

	# Define text color and render the text onto the button image
	#
	$text_color = ImageColorAllocate($imout, $font_color[0], $font_color[1], $font_color[2]);
	ImageTTFText($imout, $ttsize, 0, $cap_width - $cap_overlap, 19, $text_color, $ttfont, $text);

	# That's it, output the result
	#
	Header('Cache-control: no-cache, no-store');
	Header("Content-type: image/png");
	Header("Expires: Tue, 01 Jan 1980 00:00:00 GMT");
	ImagePng($imout);
	ImageDestroy($imout);

}

/* Timing function to work out how long things take*/
function elapsed($start) {
	$end = microtime();
	list($start2, $start1) = explode(" ", $start);
	list($end2, $end1) = explode(" ", $end);
	$diff1 = $end1 - $start1;
	$diff2 = $end2 - $start2;
	if( $diff2 < 0 ){
		$diff1 -= 1;
		$diff2 += 1.0;
	}
	return $diff2 + $diff1;
}

function aslog($txt = '', $pre = 0, $msg = '') {
	global $as_pkg, $debug, $dbg_msg;
	if (!$msg) $msg = $txt;
	if ($debug == "true") {
		if ($pre) $dbg_msg .= "</pre>\n" && $txt = explode(', ',str_replace('\\', ', ', $txt));
		$dbg_msg .= "<hr width=200 align=left><strong>$as_pkg::debug:</strong> $msg<br />\n";
		if (is_array($txt)) $dbg_msg .= print_array($txt);
		if ($pre) $dbg_msg .= "<pre>\n";
	}
}

function asErr($txt = "") {
	global $as_pkg, $out_msg;
	$out_msg .= "$as_pkg::error: $txt<br>\n";
}

function print_array($array,$prep = '',$no_pre = false) {
$newArray = array();

foreach( $array as $key => $value ) {
		$newArray[$key] = $value;
}
// Print an array tree.
	$ret = '';
	$prep = "$prep|";

	foreach($array as $key=>$val) { 
		$type = gettype($val);
		if (is_array($val) && !$val) {
			$line = "-+ $key (<strong>$type</strong>)\n";
			$line .= "	".$prep." |\t(<em>is empty</em>)\n";
		} elseif (is_array($val)) {
			$line = "-+ $key (<strong>$type</strong>)\n";
			$line .= print_array($val,"$prep ",true);
		} else {
			$val = trim($val);
			$line = "-&gt; $key = \"$val\" (<em>$type</em>)\n";
		}
		
		
		$ret .= "	".$prep.$line;
	}
	if ($no_pre) return $ret;
	return "<pre>\n".$ret."</pre>\n";
}

function asLoadStats ($host) {
	global $libdir, $namefile, $virus, $maxi, $msgs, $as_pkg, $vid;

	$do_host =  $host ? $host:'localhost';
	$statefile = "$libdir/$as_pkg.state";
	$seenfile  = "$libdir/$as_pkg.seen";

	$readfile = @file($seenfile);
	if (!is_array($readfile)) {
		asErr("Could not open $seenfile");
		return false;
	}

	$virus = array();
	for ($k = 0; $k <= (count($readfile) - 1); $k++) {
		$fields = preg_split("/\s+/",$readfile[$k], -1, PREG_SPLIT_NO_EMPTY);
		$virus[$fields[0]]["id"] = $fields[0];
		$virus[$fields[0]]["firstseen"] = $fields[1];
		$virus[$fields[0]]["lastseen"] = $fields[2];
	}

	$readfile = @file($namefile);
	if (!is_array($readfile)) {
		asErr("Couldn't open id => name mappings file.");
	}

	$msgs = array();
	for ($k = 0; $k <= (count($readfile) - 1); $k++) {
		$fields = preg_split("/\s+/",$readfile[$k], 2, PREG_SPLIT_NO_EMPTY);

		$id = trim($fields[0]);
		$name = trim($fields[1]);

		if (isset($virus[$id])) { // ID has been seen
			$virus[$id]["name"] = $name;  // so record the name
		} else {
			continue; // otherwise we don't need to know about this guy
		}

		// Pull out the "standard" IDs (Passed *, Blocked *)
		if (strpos($name, "Passed") === 0 ||
			strpos($name, "Blocked") === 0) {
			$msgs[$id] = $name;
			unset($virus[$id]);
			continue;
		}

		$vid[$name] = $id;

	}

	if (count($virus) >= 1)
		aslog($virus, 0, "asLoadStats('".$do_host."')->\$virus");
	if (count($vid) >= 1)
		aslog($vid, 0, "asLoadStats('".$do_host."')->\$vid");
	if (count($msgs) >= 1)
		aslog($msgs, 0, "asLoadStats('".$do_host."')->\$msg");

	if (count($virus) == 0 and count($msgs) == 0) {
		asErr("No viruses/IDs seen.");
		return false;
	}

	$readfile = @file($statefile);
	if (!is_array($readfile)) {
		asErr("Couldn't open state file.");
	}
	aslog($statefile);
	aslog($readfile, 0, "asLoadStats('".$do_host."')->\$readfile");

	for ($k = 0; $k <= (count($readfile) - 1); $k++) {
		$fields = preg_split("/\s+/",$readfile[$k], -1, PREG_SPLIT_NO_EMPTY);
		if ($fields[0] == "lastupdate:" && is_numeric($fields[1])) {
			$lastupdate = $fields[1];
		} elseif ($fields[0] == "LC_TIME:") {
			setlocale(LC_TIME, $fields[1]);
		}
	}

	if (!isset($lastupdate)) {
		asErr("lastupdate not defined.");
		return false;
	}
	elseif ($lastupdate == 0) {
		asErr("last update was at 0 seconds.");
		return false;
	}

	return true;
}

/*
* The beginning and the end of the calculations must not fall half
* way through any of the rrd "bins", but only on the boundaries. Which
* bin is used is determined by rrdtool based on the span.
*
* The rrds are created as follows (from the amavis-stats perl script)
* with a "step" of 300:
*
*   "RRA:AVERAGE:0.5:1:300"   = 1 bin every step (300 seconds)
*   "RRA:AVERAGE:0.5:6:700"   = 1 bin every 6 steps (1800 seconds - 1/2 hour)
*   "RRA:AVERAGE:0.5:24:775"  = 1 bin every 24 steps (7200 seconds - 2 hours)
*   "RRA:AVERAGE:0.5:288:797" = 1 bin every 288 steps (86400 seconds - 1 day)
*
* If we make sure that for the appropriate span we start and end on the
* appropriate place the totals should work out accurately.
*
* By the same logic, we have to make sure that the rrd files are first
* populated with data at the beginning of the highest bin (ie at the start
* of a day). Otherwise we are calculating over a period which contains
* some *UNKNOWN* data.
*
* At least, that's the theory.
*
* getlimits($end, $span)
*
* $end  - end time in seconds
* $span - string either "daily", "weekly", "monthly", or "yearly"
*
* Returns an array with the new end, a start value, and a better text
* description of the span.
*/
function getlimits($end, $span) {
	global $rrdstep;

	/* how wide is each bin, in seconds? */
	$dayw	 = $rrdstep;
	$weekw	= 6*$rrdstep;
	$monthw   = 24*$rrdstep;
	$yearw	= 288*$rrdstep;

	/* how long is each span in seconds? */
	$daysec   = 60*60*24;
	$weeksec  = $daysec*7;
	$monthsec = $weeksec*4;
	$yearsec  = $weeksec*52;

	/* make sure that the span is a multiple of the bin size */
	/* is this actually necessary? */
	$dayspan   = floor($daysec/$dayw) * $dayw;
	$weekspan  = floor($weeksec/$weekw) * $weekw;
	$monthspan = floor($monthsec/$monthw) * $monthw;
	$yearspan  = (int)($yearsec/$yearw) * $yearw;

	$result   = array();

	if ($span == "daily") {
		$result["end"]		 = floor($end/$dayw) * $dayw;
		$result["start"]	   = $result["end"] - $dayspan;
		$result["description"] = "24 hours";
	} elseif ($span == "weekly") {
		$result["end"]		 = floor($end/$weekw) * $weekw;
		$result["start"]	   = $result["end"] - $weekspan;
		$result["description"] = "1 week";
	} elseif ($span == "monthly") {
		$result["end"]		 = floor($end/$monthw) * $monthw;
		$result["start"]	   = $result["end"] - $monthspan;
		$result["description"] = "4 weeks";
	} elseif ($span == "yearly") {
		$result["end"]		 = floor($end/$yearw) * $yearw;
		$result["start"]	   = $result["end"] - $yearspan;
		$result["description"] = "52 weeks";
	} else {
		asErr("getlimits: bad span: $span");
	}

	aslog($result, 0, "getlimits(".$end.",".$span.")->\$result");
	return $result;
}

function on_host($name){

	return $name != "" ? "on $name " : "";
}

function calc_maxi($num) {
	if ($num > 20) {
		return 2;
	} else {
		return 3;
	}
}

/*
*
*/
function addopts(&$opts, $type, $id, $vcount, $thename, $thecolor) {
	global $libdir, $rate, $maxi;

//	$name = sprintf("%-".$maxi."s", $thename);
	$thename = preg_replace('/:/', "\:", $thename);
	$name = substr(sprintf("%-".$maxi."s", strlen($thename) > 32 ? substr(sprintf("%s",$thename),0,29)."..." : $thename),0,32);
	$count = $vcount[$id];
	$count = sprintf("%8d", $count);
	$opts[] = "DEF:v$id=$libdir/$id.rrd:hits:AVERAGE";
//	$opts[] = "CDEF:gv$id=v$id,$rate,*";
	$opts[] = "CDEF:gv$id=v$id,UN,0,v$id,IF,$rate,*";
	$opts[] = "$type:gv$id#$thecolor:$name $count";

	return $opts;
}

/**
 * asPGraph($img, $end, $span)
 *
 * $img  - name of the png to generate
 * $end  - end time in seconds
 * $span - string either "daily", "weekly", "monthly", or "yearly"
 *
 * Build a graph of clean or "Passed" emails.
 * Returns either a html-valid <img> tag which can be printed, or the
 * boolean "false" if something went wrong.
 */
function asPGraph($img, $end, $span) {
	global $libdir, $virus, $debug, $refresh, $span, $as_pkg, $host_list;
	global $rate, $as_version, $rrdstep, $host, $msgs, $show_graph_period;

	/*
	* Work out the beginning and the end, based on the span
	*/
	$limits   = getlimits($end, $span);
	$end	  = $limits["end"];
	$start	= $limits["start"];
	$length   = $end - $start;
	$timetext = $limits["description"];

	$start_period = strftime("%A %B %d %H:%M:%S (GMT%z)", $start);
	$end_period = strftime("%A %B %d %H:%M:%S (GMT%z)", $end);
	$startdate = strftime("%c", $start);
	$enddate   = strftime("%c", $end);
	$nowdate   = strftime("%c", time());

	$hostname = on_host($host);

	if ($rate == 60) {
		$ratemsg = "min";
	} else {
		$rate = 3600;
		$ratemsg = "hour";
	}

	$arrcol = array("Passed SPAM"	  => "7a5aff",
					"Blocked SPAM"	 => "fa5aff",
					"Passed BANNED"	=> "cc9900",
					"Blocked BANNED"   => "ffdd00",
					"Passed BAD-HEADER"	=> "3f00cc",
					"Blocked BAD-HEADER"   => "594e61",
					"Passed INFECTED"  => "9900aa",
					"Blocked INFECTED" => "ff3a3d",
					"Passed CLEAN"	 => "10d0d0",
					"Blocked CLEAN"	=> "7ffa00");
	

	/*
	* It is a two-step process to build the final graph. The average over
	* a specific time period seems to be impossible to get without actually
	* building a graph. Ie, rrd fetch will not calculate the values we
	* need - we would have to sum and average manually.
	*
	* However the PRINT function of a graph will return what we want
	* in an array. So first of all build a graph that PRINTs the average
	* of every virus over the selected time period.
	*/

	$opts = array();
	$opts[] = "--start=$start";
	$opts[] = "--end=$end";

	foreach ($msgs as $id => $name) {
		$opts[] = "DEF:v$id=$libdir/$id.rrd:hits:AVERAGE";
		$opts[] = "CDEF:gv$id=v$id,UN,0,v$id,IF";
		$opts[] = "CDEF:gvt$id=gv$id,$length,*";
		$opts[] = "PRINT:gvt$id:AVERAGE:%.0lf";
	}

	$ret = pre_graph($img, $opts, count($opts));

	if (!is_array($ret)) {
		$msg = rrd_error();
		asErr("rrd_graph()1: $msg");
		return false;
	}

	/*
	* All results from PRINT commands are in the array $ret["calcpr"][..]
	*/
	$i = 0;
	$pcount = array();
	foreach ($msgs as $id => $name) {
		/*
		* We don't have enough resolution in the rrds
		* to calculate the correct counts at low averages,
		* so we just don't display them
		*/
		if ($ret["calcpr"][$i] != 0) {
			$pcount[$id] = $ret["calcpr"][$i];
		}
		$i++;
	}

	if (count($pcount) >= 1) {
		arsort($pcount);
	}
	else {
		aslog("pcount is an empty array");
	}
	aslog($pcount,0,'pcount');

	$opts = array();
	$opts[] = "--start=$start";
	$opts[] = "--end=$end";
	$opts[] = "--imgformat=PNG";
	$opts[] = "--title=Message Breakdown $hostname($timetext to $enddate)";
	$opts[] = "--width=580";
	$opts[] = "--vertical-label=msgs/$ratemsg";

	$type = "AREA";

	foreach ($pcount as $id => $count) {
			$name = sprintf("%-21s", $msgs[$id]);
			$col  = $arrcol[$msgs[$id]];
			$opts[] = "DEF:v$id=$libdir/$id.rrd:hits:AVERAGE"; // rrd datasrc 
			$opts[] = "CDEF:gv$id=v$id,UN,0,v$id,IF";		  // make UNK=0
			$opts[] = "CDEF:gtvt$id=gv$id,$rate,*";			// height/rate 
			$opts[] = "$type:gtvt$id#$col:$name ";			 // name&graph
			$opts[] = "CDEF:gvt$id=gv$id,$length,*";		   // how many?
			$opts[] = "GPRINT:gvt$id:AVERAGE:%.0lf";		   // how many?
			$opts[] = "COMMENT:\\n";
			$type = "STACK";
	}

	$opts[] = "COMMENT:\\n";
	if ($show_graph_period) {
		$opts[] = graph_date($start_period, $end_period);
	}
	$opts[] = "COMMENT:$as_pkg v$as_version\\r";

	$start = microtime();
	$ret = pre_graph($img, $opts, count($opts));
	$t = elapsed($start);
	if (!is_array($ret)) {
		$err = rrd_error();
		asErr("rrd_graph()2: $err");
		return false;
	}

	return "<img class=\"2\" src=\"$img\" alt=\"[image: $timetext]\">";
}

/**
 * asVGraph($img, $end, $span)
 *
 * $img	 - name of the png to generate
 * $end	 - end time in seconds
 * $span	- string either "daily", "weekly", "monthly", or "yearly"
 *
 * Build a graph of Virus infected emails.
 * Returns either a html-valid <img> tag which can be printed, or the
 * boolean "false" if something went wrong.
 */
function asVGraph($img, $end, $span) {
	global $rate, $maxi, $libdir, $virus, $show_graph_period;
	global $as_version, $rrdstep, $host, $as_pkg;

	if (count($virus) > 0) {
	/*
	* Work out the beginning and the end, based on the span
	*/
	$limits   = getlimits($end, $span);
	$end	  = $limits["end"];
	$start	= $limits["start"];
	$length   = $end - $start;
	$timetext = $limits["description"];

	$start_period = strftime("%A %B %d %H:%M:%S (GMT%z)", $start);
	$end_period = strftime("%A %B %d %H:%M:%S (GMT%z)", $end);
	$startdate = strftime("%c", $start);
	$enddate   = strftime("%c", $end);
	$nowdate   = strftime("%c", time());

	$hostname = on_host($host);

	/*
	* It is a two-step process to build the final graph. The average over
	* a specific time period seems to be impossible to get without actually
	* building a graph. Ie, rrd fetch will not calculate the values we
	* need - we would have to sum and average manually.
	*
	* However the PRINT function of a graph will return what we want
	* in an array. So first of all build a graph that PRINTs the average
	* of every virus over the selected time period.
	*/

	$opts = array();
	$opts[] = "--start=$start";
	$opts[] = "--end=$end";

	foreach ($virus as $id => $rest) {
		$opts[] = "DEF:v$id=$libdir/$id.rrd:hits:AVERAGE";
		$opts[] = "CDEF:gv$id=v$id,UN,0,v$id,IF";
		$opts[] = "CDEF:gvt$id=gv$id,$length,*";
		$opts[] = "PRINT:gvt$id:AVERAGE:%.0lf";
	}

	$ret = pre_graph($img, $opts, count($opts));

	$infected = 0;

	if (!is_array($ret)) {
		$msg = rrd_error();
		asErr("rrd_graph(): $msg");
		return false;
	}

	/*
	* All results from PRINT commands are in the array $ret["calcpr"][..]
	*/
	$maxi = 0;
	$i = 0;
	$vcount = array();
	foreach ($virus as $id => $rest) {
		/*
		* We don't have enough resolution in the rrds
		* to calculate the correct counts at low averages,
		* so we just don't display them
		*/
		if ($ret["calcpr"][$i] != 0) {
			$vcount[$id] = $ret["calcpr"][$i];
			$maxi = max($maxi, strlen($virus[$id]["name"]));
		}
		$i++;
	}
	$maxi++;
	aslog("Maxi: $maxi");

	/*
	*/
	if (count($vcount) >= 1) {
		arsort($vcount);
		aslog($vcount,0,'$vcount');
	}
	else {
		aslog("vcount is an empty array");
	}

	if ($rate == 3600) {
		$ratemsg = "hour";
	} else {
		$rate = 60;
		$ratemsg = "min";
	}

	/*
	* Now that we have the counts of each virus over the time period
	* we can build the actual graph
	*/
	$opts = array();
	$opts[] = "--start=$start";
	$opts[] = "--end=$end";
	$opts[] = "--imgformat=PNG";
	$opts[] = "--title=Virus Detection $hostname($timetext to $enddate)";
	$opts[] = "--width=580";
	$opts[] = "--vertical-label=viruses/$ratemsg";

	/*
	* The tricky part, building rrd rows but ordering the elements by
	* columns...
	*/

	$width = calc_maxi($maxi);
	
	$total = count($vcount);
	$depth = ceil($total / $width);

	$mod = $total % $width;

	if ($total > 0) {

		$keyarray = array_keys ($vcount);

		for ($d = 1; $d <= $depth; $d++) {
			for ($col = 1; $col <= $width; $col++) {

				if ($col == 1) {
					$index = $d;
				}
				elseif ($d != $depth || $mod == 0 || $mod >= $col) {
					if (($mod == 0) || ($col - $mod) < 2) {
						$index = ($col - 1) * $depth + $d;
					} else {
						$index = $mod * $depth + ($col - $mod - 1)*($depth - 1) + $d ;
					}
				} else {
					continue;
				}

				$id = $keyarray[$index - 1];

				if ($d == 1 && $col == 1) {
					addopts($opts, "AREA", $id, $vcount, $virus[$id]["name"], substr(md5($virus[$id]["name"]),7,6));
				} else {
					addopts($opts, "STACK", $id, $vcount, $virus[$id]["name"], substr(md5($virus[$id]["name"]),7,6));
				}
			}
			$opts[] = "COMMENT:\\n";
		}
	}

	$opts[] = "COMMENT:\\n";
	if ($show_graph_period) {
		$opts[] = graph_date($start_period, $end_period);
	}
	$opts[] = "COMMENT:$as_pkg v$as_version\\r";

	$ret = pre_graph($img, $opts, count($opts));

	if (!is_array($ret)) {
		$err = rrd_error();
		asErr("rrd_graph(): $err");
		return false;
	}
	} else {
	$ret['ysize'] = 189;
	}

	if ($ret['ysize'] < 190) {
	return "<br><center>NO VIRUS DATA TO GRAPH</center><br>";
	}

	return "<img src=\"$img\" alt=\"[image: $timetext]\">";
}

/*
* asBGraph($img, $end, $span)
*
* $img	 - name of the png to generate
* $end	 - end time in seconds
* $span	- string either "daily", "weekly", "monthly", or "yearly"
*
* Build a graph of Blocked emails.
* Returns either a html-valid <img> tag which can be printed, or the
* boolean "false" if something went wrong.
*/
function asSBGraph($img, $end, $span) {
	global $rate, $maxi, $libdir, $msgs, $show_graph_period;
	global $as_version, $rrdstep, $host, $as_pkg;

	/*
	* Work out the beginning and the end, based on the span
	*/
	$limits   = getlimits($end, $span);
	$end	  = $limits["end"];
	$start	= $limits["start"];
	$length   = $end - $start;
	$timetext = $limits["description"];

	$start_period = strftime("%A %B %d %H:%M:%S (GMT%z)", $start);
	$end_period = strftime("%A %B %d %H:%M:%S (GMT%z)", $end);
	$startdate = strftime("%c", $start);
	$enddate   = strftime("%c", $end);
	$nowdate   = strftime("%c", time());

	$hostname = on_host($host);

	if ($rate == 60) {
		$rate = 60;
		$ratemsg = "min";
	} else {
		$rate = 3600;
		$ratemsg = "hour";
	}

	$arrcol = array("Passed SPAM"	  => "6a5aff",
					"Blocked SPAM"	 => "fa5aff",
					"Blocked CLEAN"	=> "7ffa00",
					"Passed BANNED"	=> "cc9900",
					"Blocked BANNED"   => "ffdd00",
					"Passed INFECTED"  => "9900aa",
					"Blocked INFECTED" => "ff3a3d",
					"Passed BAD-HEADER"	=> "3f40cc",
					"Blocked BAD-HEADER"   => "594e61");
	
	/*
	* It is a two-step process to build the final graph. The average over
	* a specific time period seems to be impossible to get without actually
	* building a graph. Ie, rrd fetch will not calculate the values we
	* need - we would have to sum and average manually.
	*
	* However the PRINT function of a graph will return what we want
	* in an array. So first of all build a graph that PRINTs the average
	* of every virus over the selected time period.
	*/

	$opts = array();
	$opts[] = "--start=$start";
	$opts[] = "--end=$end";

	foreach ($msgs as $id => $name) {
		foreach ($arrcol as $matchname => $rest) {
			if ($name == $matchname) {
				$opts[] = "DEF:v$id=$libdir/$id.rrd:hits:AVERAGE";
				$opts[] = "CDEF:gv$id=v$id,UN,0,v$id,IF";
				$opts[] = "CDEF:gvt$id=gv$id,$length,*";
				$opts[] = "PRINT:gvt$id:AVERAGE:%.0lf";
			}
		}
	}

	$ret = pre_graph($img, $opts, count($opts));

	$infected = 0;

	if (!is_array($ret)) {
		$msg = rrd_error();
		asErr("rrd_graph(): $msg");
		return false;
	}

	/*
	* All results from PRINT commands are in the array $ret["calcpr"][..]
	*/
	$maxi = 0;
	$i = 0;
	$bcount = array();
	foreach ($msgs as $id => $name) {
		foreach ($arrcol as $matchname => $rest) {
			if ($name == $matchname) {
		/*
		* We don't have enough resolution in the rrds
		* to calculate the correct counts at low averages,
		* so we just don't display them
		*/
		if ($ret["calcpr"][$i] != 0) {
			$bcount[$id] = $ret["calcpr"][$i];
			$maxi = max($maxi, strlen($msgs[$id]));
		}
		$i++;
		}
		}
	}
	$maxi++;
	aslog("Maxi: $maxi");

	/*
	*/
	if (count($bcount) >= 1) {
		arsort($bcount);
		aslog($bcount,0,'$bcount');
	}
	else {
		aslog("bcount is an empty array");
	}

	/*
	* Now that we have the counts of each virus over the time period
	* we can build the actual graph
	*/
	$opts = array();
	$opts[] = "--start=$start";
	$opts[] = "--end=$end";
	$opts[] = "--imgformat=PNG";
	$opts[] = "--title=Spam/Header Detection $hostname($timetext to $enddate)";
	$opts[] = "--width=580";
	$opts[] = "--vertical-label=non-virus/$ratemsg";

	/*
	* The tricky part, building rrd rows but ordering the elements by
	* columns...
	*/

	$width = calc_maxi($maxi);
	
	$total = count($bcount);
	$depth = ceil($total / $width);

	$mod = $total % $width;

	if ($total > 0) {

		$keyarray = array_keys ($bcount);

		for ($d = 1; $d <= $depth; $d++) {
			for ($col = 1; $col <= $width; $col++) {

				if ($col == 1) {
					$index = $d;
				}
				elseif ($d != $depth || $mod == 0 || $mod >= $col) {
					if (($mod == 0) || ($col - $mod) < 2) {
						$index = ($col - 1) * $depth + $d;
					} else {
						$index = $mod * $depth + ($col - $mod - 1)*($depth - 1) + $d ;
					}
				} else {
					continue;
				}

				$id = $keyarray[$index - 1];

				foreach ($arrcol as $matchname => $color) {
					if ($msgs[$id] == $matchname) {
						$usecolor = $color;
					}
				}

				if ($d == 1 && $col == 1) {
					addopts($opts, "AREA", $id, $bcount, $msgs[$id], $usecolor);
				} else {
					addopts($opts, "STACK", $id, $bcount, $msgs[$id], $usecolor);
				}
			}
			$opts[] = "COMMENT:\\n";
		}
	}

	$opts[] = "COMMENT:\\n";
	if ($show_graph_period) {
		$opts[] = graph_date($start_period, $end_period);
	}
	$opts[] = "COMMENT:$as_pkg v$as_version\\r";


	$ret = pre_graph($img, $opts, count($opts));

	if (!is_array($ret)) {
		$err = rrd_error();
		asErr("rrd_graph(): $err");
		return false;
	}

	if ($ret['ysize'] < 190) {
	return "<br><center>NO SPAM/HEADER DATA TO GRAPH</center><br>";
	}

	return "<img src=\"$img\" alt=\"[image: $timetext]\">";
}

function graph_date($begin_graph, $end_graph) {

	return	"COMMENT:Graphed from $begin_graph  - $end_graph\\n";
}

function as_realpath($path) {
	global $as_root_path;

	return (!@function_exists('realpath') || !@realpath($as_root_path . 'includes/functions.php')) ? $path : @realpath($path);
}

function show_info($function,$basic = 0) {
$func = new ReflectionFunction($function);
$ret = '';
$ret .= "<pre><strong>Information:</strong>\n".var_export($func->getDocComment(), 1)."</pre>\n";
if ($basic) $ret .= "<pre><pre>===> The ".($func->isInternal() ? 'internal' : 'user-defined').
	" function '".$func->getName()."'\n".
	"\tdeclared in ".$func->getFileName().
	"\n\tlines ".$func->getStartLine().
	" to ".$func->getEndline()."</pre>\n";
return $ret;
}

if ($button && $text ) {
	make_button($text,$button);
return;
}

$handle=opendir($libdir);
$host_list = array();
while ($file = readdir($handle)) {
	if ($file != "." && is_dir("$libdir/$file")) {
		if (file_exists("$libdir/$file/$as_pkg.state")) {
			$host_list[] = $file;
		}
	}
}

if ($host) {
	$libdir = $libdir."/$host";
	$imgdir	= "img/$host";
}

$handle=opendir($libdir);

$hosts = array();
while ($file = readdir($handle)) {
	if ($file != "." && is_dir("$libdir/$file")) {
		if (file_exists("$libdir/$file/$as_pkg.state")) {
			$hosts[] = $file;
		}
	}
}

if (extension_loaded('Reflection') && $debug == 'true') $dbg_msg .= show_info('dummy',0);

/*
*  Finish up the html with the description of the message classifications
*/
if ($legend == 'true') {
	$legend_msg = "<table style=\"font-size:x-small;\" cellpadding=0 border=0 cellspacing=0>
	<tr>\n\t\t<td><b><font color=green>Passed CLEAN</font></b>:</td><td>&nbsp;&nbsp;</td><td>clean or '\$tag_level' &lt; '\$tag2_level'</td>\n\t</tr>
	<tr>\n\t\t<td><b><font color=green>Passed SPAM</font></b>:</td><td></td><td>classified as SPAM but delivered because '\$tag_level' &lt; '\$kill_level'</td>\n\t</tr>
	<tr>\n\t\t<td><b><font color=green>Passed BANNED</font></b>:</td><td></td><td>classified as BANNED but delivered</td>\n\t</tr>
	<tr>\n\t\t<td><b><font color=green>Passed BAD-HEADER</font></b>:</td><td></td><td>classified as BAD-HEADER but delivered</td>\n\t</tr>
	<tr>\n\t\t<td><b><font color=red>Blocked SPAM</font></b>:</td><td></td><td>not delivered, spam classified with '\$tag_level' &gt;= '\$kill_level' and '\$final_spam_destiny' != 'pass'</td>\n\t</tr>
	<tr>\n\t\t<td><b><font color=red>Blocked BANNED</font></b>:</td><td></td><td>not delivered, banned file extension attached.</td>\n\t</tr>
	<tr>\n\t\t<td><b><font color=red>Blocked BAD-HEADER</font></b>:</td><td></td><td>not delivered, contains bad headers.</td>\n\t</tr>
	<tr>\n\t\t<td><b><font color=red>Blocked INFECTED</font></b>:</td><td></td><td>not delivered, contains virus</td>\n\t</tr>
	<tr>\n\t\t<td><b><font color=red>Not Delivered</font></b>:</td><td></td><td>could not be delivered, e.g. destination host refuses to accept delivery.</td>\n\t</tr>\n</table>\n";
}

$template = new Template($as_root_path . $template_path);

$template->assign_vars(array(
	"PAGE_TITLE" => $title)
);

$template->set_filenames(array(
	'body' => 'index_body.tpl')
);

$template->assign_vars(array(
	'S_CONTENT_DIRECTION' => 'ltr',
	'S_CONTENT_ENCODING' => 'iso-8859-1',
	'RATE' => $rate,
	'META_REFRESH' => ($refresh && is_numeric($refresh) && $refresh > 0) ? "<meta http-equiv=\"refresh\" content=\"$refresh\">" : NULL,
	'REFRESH' => ($refresh && is_numeric($refresh) && $refresh > 0) ? $refresh : 0,
	'LEGEND' => $legend,
	'LEGEND_MSG' => $legend_msg,
	'SPAN' => $span,
	'PGRAPH' => asLoadStats($host) ? asPGraph("$imgdir/passed-$span.png", $now, $span) : "No statistics available.",
	'VGRAPH' => asLoadStats($host) ? asVGraph("$imgdir/virus-$span.png", $now, $span) : "No statistics available.",
	'SBGRAPH' => asLoadStats($host) ? asSBGraph("$imgdir/sb-$span.png", $now, $span) : "No statistics available.",
	'VERSION' => $as_version,
	'OUT_MSG' => $out_msg ? $out_msg : '',
	'DBG_MSG' => $dbg_msg ? $dbg_msg : '',
	"PAGE_TITLE" => $title,
	'HOST' => $host)

);

$template->assign_block_vars("time_date_row", array(
	"CURRENT_DATE" => strftime("%A %B %d %H:%M:%S (GMT%z)", time()))
);

$template->assign_block_vars("button_row", array(
	"BUTTON" => $debug != 'true' ? "<a href=\"$me?host=$host&span=$span&refresh=$refresh&debug=true&rate=$rate&legend=$legend\"><img src=\"$me?button=$button_unselected&text=debug\"></a>" : "<a href=\"$me?host=$host&span=$span&refresh=$refresh&debug=false&rate=$rate&legend=$legend\"><img src=\"$me?button=$button_unselected&text=nodebug\"></a>")
	);

$template->assign_block_vars("button_row", array(
	"BUTTON" => $rate != 3600 ? "<a href=\"$me?host=$host&span=$span&refresh=$refresh&debug=$debug&rate=3600&legend=$legend\"><img src=\"$me?button=$button_unselected&text=hour\"></a>" : "<a href=\"$me?host=$host&span=$span&refresh=$refresh&debug=$debug&rate=60&legend=$legend\"><img src=\"$me?button=$button_unselected&text=min\"></a>")
	);

$button_text = array('daily', 'weekly', 'monthly', 'yearly');
for($i = 0; $i < count($button_text); $i++) {
	$template->assign_block_vars("button_row", array(
		"BUTTON" => $span != $button_text[$i] ? "<a href=\"$me?host=$host&span=$button_text[$i]&refresh=$refresh&debug=$debug&rate=$rate&legend=$legend\"><img src=\"$me?button=$button_unselected&text=".$button_text[$i]."\"></a>" : "<img src=\"$me?text=".$button_text[$i]."&button=$button_selected\">")
		);
}

if (!$host_list) {
	$template->assign_block_vars("button_row", array(
		"BUTTON" => $legend != 'true' ? "<a href=\"$me?host=$host&span=$span&refresh=$refresh&debug=$debug&rate=$rate&legend=true\"><img src=\"$me?button=$button_unselected&text=legend\"></a>" : "<a href=\"$me?host=$host&span=$span&refresh=$refresh&debug=$debug&rate=$rate&legend=false\"><img src=\"$me?button=$button_selected&text=legend\"></a>")
		);
} else {
	$template->assign_block_vars('hostlist_row', array());
	$template->assign_block_vars("hostlist_row.button_row2", array(
		"BUTTON" => $legend != 'true' ? "<a href=\"$me?host=$host&span=$span&refresh=$refresh&debug=$debug&rate=$rate&legend=true\"><img src=\"$me?button=$button_unselected&text=legend\"></a>" : "<a href=\"$me?host=$host&span=$span&refresh=$refresh&debug=$debug&rate=$rate&legend=false\"><img src=\"$me?button=$button_unselected&text=nolegend\"></a>")
		);
	$template->assign_block_vars("hostlist_row.button_row2", array(
		"BUTTON" => $host ? "<a href=\"$me?host=&span=$span&refresh=$refresh&debug=$debug&rate=$rate&legend=$legend\"><img src=\"$me?button=$button_unselected&text=ALL\"></a>" : "<img src=\"$me?text=ALL&button=$button_selected\">")
		);
	$cnt = 1;
	for($i = 0; $i < count($host_list); $i++) {
		if ($cnt == 3) {
			$template->assign_block_vars('hostlist_row', array());
			$cnt = 0;
		}
		$template->assign_block_vars("hostlist_row.button_row2", array(
			"BUTTON" => $host != $host_list[$i] ? "<a href=\"$me?host=$host_list[$i]&span=$span&refresh=$refresh&debug=$debug&rate=$rate&legend=$legend\"><img src=\"$me?button=$button_unselected&text=".$host_list[$i]."\"></a>" : "<img src=\"$me?text=".$host_list[$i]."&button=$button_selected\">")
			);
		$cnt++;
	}

}

include($as_root_path . '/includes/page_header.php');

$template->pparse('body');

include($as_root_path . '/includes/page_tail.php');

?>