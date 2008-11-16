<?php
/*
Plugin Name: Folding Stats Plus
Plugin URI: http://www.pross.org.uk/category/plugins/
Description: Display current Folding@Home stats
Version: 0.6
Author: Simon Prosser
Author URI: http://www.pross.org.uk
Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
*/
/*	Code is forked with permission from Jason F. Irwin J²fi's version http://www.j2fi.net/2007/03/23/foldinghome-wordpress-plugin/
	
	Copyright 2007  Simon Prosser  (email: pross@pross.org.uk)
	
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
function fold_init() {
  	define('FOLD_ACCT', get_option('fold_acct'));
  	define('FOLD_EXPY', get_option('fold_expy'));
	}
add_action('init','fold_init');
function folding() {
get_folding_stats();
}
function widget_folding() {
$title = get_option('widget_folding_title');
?><li><h2 class="sbtitle"><?php echo $title; ?></h2><?php
folding();
}
	// Settings form
	function widget_folding_control() {
 
		// Get options
		$options = get_option('widget_folding_title');
		// options exist? if not set defaults
		if ( !$options ) {
			$options = 'Folding Stats';
add_option('widget_folding_title',$options);
}
                      // form posted?
		if ( $_POST['folding-submit'] ) {
 			// Remember to sanitize and format use input appropriately.
			$options = strip_tags(stripslashes($_POST['folding-title']));
			update_option('widget_folding_title', $options);
		}
		// Get options for form fields to show
		$title = htmlspecialchars($options, ENT_QUOTES);
		// The form fields
		echo '<p style="text-align:right;">
				<label for="folding-title">' . __('Title:') . '
				<input style="width: 200px;" id="folding-title" name="folding-title" type="text" value="'.$title.'" />
				</label></p>';
		echo '<input type="hidden" id="folding-submit" name="folding-submit" value="1" />';
		}
function folding_init() {
  register_sidebar_widget(__('Foldingstats'), 'widget_folding');
	register_widget_control(array('Foldingstats','widgets'), 'widget_folding_control');
}
add_action("plugins_loaded", "folding_init");
define('FOLD_FILE', ABSPATH . 'wp-content/plugins/folding-stats-plus/folding_cache.txt');
function get_folding_stats() {
	//Get the Write Time
if (file_exists(FOLD_FILE)) {
	$fh = fopen(FOLD_FILE, 'r');
	$expiry = fread($fh, 10);
	fclose($fh);
}
	//Date Compare
	$today = mktime(date("H"), 0, 0, date("m"), date("d"), date("y"));

	if ($expiry < $today) {
		//Get Fresh Data
		read_fold_site();
	}
	//Read Data From Cache
if (file_exists(FOLD_FILE)) {
	$fg = fopen(FOLD_FILE, 'r');
	$out = fread($fg, filesize(FOLD_FILE));
	fclose($fg);
}
	$fold_logo = get_settings('home') . '/wp-content/plugins/folding-stats-plus/FAHlogoML.jpg';
	//Output
	$preout = '<div align="'.get_option('fold_align').'">';
	$out = $preout . substr($out, 10, strlen($out));
	if (get_option('fold_pic') == 'true') {
	$out = $out . '<a href="http://folding.stanford.edu"><img src="'.$fold_logo.'" alt="Folding@Home" /></a>';
	}
	$out = $out . '</div></li>';
	echo $out;
}
function read_fold_site() {
	$host = 'fah-web.stanford.edu';
	$path = '/cgi-bin/main.py?qtype=userpage&username=' . FOLD_ACCT;
	$stats_url = 'http://fah-web.stanford.edu/cgi-bin/main.py?qtype=userpage&username=' . FOLD_ACCT;
	$fold_url = 'http://folding.stanford.edu/';
	//Get the site data and trim to something managable
	$sFile = get_contents($stats_url, False);
	$sfile = substr($sfile, 0, 2000);
	if ($updating = strstr($sFile, 'Stats update in progress')) { 
	$updating = 'Stats update in progress';
	}
	$last_upd = strstr($sFile, 'Date of last work unit');
	$credit = strstr($sFile, 'Total score');
	$ov_rank = strstr($sFile, 'Overall rank');
	$wu = strstr($sFile, '<TD> WU</TD>');
	//Parse Strings
	$last_upd = substr($last_upd, strpos($last_upd, '=4>') + 4, 11);
	$credit = substr($credit, strpos($credit, '=4>') + 4, 12);
	$credit = substr($credit, 0, strpos($credit, '<'));
	$ov_rank = substr($ov_rank, strpos($ov_rank, '=4>') + 4, 20);
	if (get_option('fold_rank') == 'short') {
		$ov_rank = substr($ov_rank, 0, strpos($ov_rank, 'o')); 
		} else {
				$ov_rank = substr($ov_rank, 0, strpos($ov_rank, '<'));
		}
	$wu = substr($wu, strpos($wu, '<b>') + 4, 4);
	$wu = substr($wu, 0, strpos($wu, '<'));
	$expire = mktime(date("H")+FOLD_EXPY, 0, 0, date("m"), date("d"), date("y"));
	//Write to Cache
	$fh = fopen(FOLD_FILE, 'w') or die("can't open file");
	if ($updating = strstr($sFile, 'Stats update in progress')) { 
	$stringData = $expire.'Update in progress...<br />';
	fwrite($fh, $stringData);
	fclose($fh);
	} else {
	$stringData = $expire;
	$bold = get_option('fold_results_bold');
	$color = get_option('fold_results_color');
	$style = '<font style="font-weight: '.$bold.'; color: #'.$color.';">';
	$stringData .= '<ul><li> Total Score: '.$style.$credit.'</font></li>';
	$stringData .= '<li> Overall Rank: '.$style.$ov_rank.'</font></li>';
	$stringData .= '<li> WorkUnits: '.$style.$wu.'</font></li>';
	$stringData .= '<li> Last Update: '.$style.$last_upd.'</font></li></ul>';
	fwrite($fh, $stringData);
	fclose($fh);
	}
}
/** filters**/
add_action('admin_head', 'fold_add_options_page');
function fold_add_options_page() {
	add_options_page('Folding Options', 'Folding Options', 'manage_options', 'folding-stats-plus/options-folding.php');
	$expire = mktime(date("H")-FOLD_EXPY, 0, 0, date("m"), date("d"), date("y"));
	//Create the Cache
	$fh = fopen(FOLD_FILE, 'w') or die("can't open file");
	$stringData = $expire;
	fwrite($fh, $stringData);
	fclose($fh);
}
function get_contents($url) {
	if(ini_get('allow_url_fopen'))
		{
		return file_get_contents($url);
		}
	else if(function_exists('curl_init')) {
			$ch = curl_init();
			$timeout = 5; // set to zero for no timeout
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$file_contents = curl_exec($ch);
			curl_close($ch);
			return $file_contents;
			} else {
				echo 'This plugin requires you to have either allow_url_fopen or cURL. Please enable allow_url_fopen or install cURL to continue.';
			}
		}
?>
