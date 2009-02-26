<?php
/*
Plugin Name: Folding Stats Plus
Plugin URI: http://www.pross.org.uk/category/plugins/
Description: This plugin is intended to show the current Folding@Home statistics for a given account. <a href="options-general.php?page=folding-stats-plus/options-folding.php">Settings</a> page.
Version: 1.3.1
Author: Simon Prosser
Author URI: http://www.pross.org.uk
Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
*/
/*	Code is forked with permission from Jason F. Irwin J?fi's version http://www.j2fi.net/2007/03/23/foldinghome-wordpress-plugin/
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
	define('FOLD_ACCT', get_option('folding_acct'));
  	define('FOLD_EXPY', get_option('folding_expy'));
	define('FOLD_TEAM', get_option('folding_team'));
		     }
add_action('init','fold_init');

function folding() {
get_folding_stats();
		}

function widget_folding($args) {
	extract($args);
$title = get_option('widget_folding_title');

echo $before_widget;
echo $before_title . $title . $after_title;
folding();
	echo $after_widget;
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
  add_action('wp_head', 'folding_head', 12); 
  add_action('admin_head', 'folding_admin_head', 10);
			}
add_action("plugins_loaded", "folding_init");

function get_folding_stats() {
	if (get_option('folding_acct') != 'fold-id') {
	//Date Compare
	$today = mktime(date("H"), 0, 0, date("m"), date("d"), date("y"));
	$expiry = get_option('folding_expire');
		  if (!$expiry) { $expiry = 0; }
		if ($expiry < $today) {
			//Get Fresh Data
		read_fold_site();
			}
	//Read Data From Cache
	$fold_logo = get_settings('home') . '/wp-content/plugins/folding-stats-plus/FAHlogoML.jpg';
	//Output

echo '
<!-- Folding-stats-plus http://www.pross.org.uk/wordpress-plugins/
Advanced XML stats provided by http://folding.extremeoverclocking.com/ with permission -->
';
	draw_stats();
	echo '
<!-- Folding-stats-plus END -->
';

	} else { 
echo 'Check settings!';
		}
	}

function read_fold_site() {
if (get_option('folding_acct') != 'fold-id') {
if (get_option('folding_team') != '0') {
	// advanced stats...
	$stats_url = 'http://folding.extremeoverclocking.com/xml/user_summary.php?un=' . FOLD_ACCT . '&t='. get_option('folding_team');
	//Get the site data and trim to something managable
	$sFile = file_get_contents($stats_url, False);
	$sFile = substr($sFile, 0, 4000);
	if (simplexml_load_string($sFile)) {
	update_option('folding_xml', $sFile);
	$expire = mktime(date("H")+FOLD_EXPY, 0, 0, date("m"), date("d"), date("y"));
	update_option('folding_expire',$expire);
	} else {
	$expire = mktime(date("H")+1, 0, 0, date("m"), date("d"), date("y"));
	update_option('folding_expire',$expire);
	}
	} else {
	$stats_url = 'http://fah-web.stanford.edu/cgi-bin/main.py?qtype=userpage&username=' . FOLD_ACCT;
	//Get the site data and trim to something managable
	$sFile = file_get_contents($stats_url, False);
	$sFile = substr($sFile, 0, 6000);
	update_option('folding_html', $sFile);
	$expire = mktime(date("H")+FOLD_EXPY, 0, 0, date("m"), date("d"), date("y"));
	//Write to Cache
	if ($updating = strstr($sFile, 'Stats update in progress')) { 
		$expire = mktime(date("H")+1, 0, 0, date("m"), date("d"), date("y"));;
		update_option('folding_expire',$expire);
	} else { 
update_option('folding_expire',$expire);
		}

}
	}
} 


add_action('admin_head', 'fold_add_options_page');

function fold_add_options_page() {
	add_options_page('Folding Options', 'Folding Options', 'manage_options', 'folding-stats-plus/options-folding.php');
	}

function folding_stats_dashboard() {
draw_stats();
}

function folding_stats_dashboard_setup() {
	wp_add_dashboard_widget( 'folding_stats_dashboard', __( 'Folding Stats Plus' ), 'folding_stats_dashboard' ,
		array(
		'all_link' => 'Full URL For "See All" link', // Example: 'index.php?page=wp-useronline/wp-useronline.php'
		'feed_link' => 'Full URL For "RSS" link', // Example: 'index.php?page=wp-useronline/wp-useronline-rss.php'
		'width' => 'half', // OR 'fourth', 'third', 'half', 'full' (Default: 'half')
		'height' => 'double', // OR 'single', 'double' (Default: 'single')
		)
	);
} // end folding_stats_dashboard_setup
 
add_action('wp_dashboard_setup', 'folding_stats_dashboard_setup');

function draw_stats() {
		$expire = mktime(date("H")+FOLD_EXPY, 0, 0, date("m"), date("d"), date("y"));
		$fold_logo = get_settings('home') . '/wp-content/plugins/folding-stats-plus/FAHlogoML.jpg';
if (get_option('folding_team') >0 ) {
	if (get_option('folding_xml') =='' ) {
	read_fold_site();
	} 

$foldxml = get_option('folding_xml');
$xmlobj = simplexml_load_string($foldxml); 
?>
<div id="folding">
<div id="folding_label">
User Name<br />
User Rank<br />
Points<br />
Points/24h<br />
Points/7D<br />
Work Units<br />
Team Name<br />
Team Rank<br />
Team Points<br />
Team Users<br />
Your Rank
</div>
<div id="folding_results">
<span style="color: #<?php echo get_option('folding_results_color'); ?>; font-weight: <?php echo get_option('folding_results_bold') ?>;"><?php echo FOLD_ACCT; ?></span><br />
<span style="color: #<?php echo get_option('folding_results_color'); ?>; font-weight: <?php echo get_option('folding_results_bold') ?>;"><?php echo number_format((string) $xmlobj->user->Overall_Rank, 0, "", ","); ?></span>
<?php if ((string) $xmlobj->user->Change_Rank_7days >0 ) { echo '<span class="folding_arrow"> (&uarr;'.(string) $xmlobj->user->Change_Rank_7days.')</span>'; }
	if ((string) $xmlobj->user->Change_Rank_7days <0 ) { echo '<span class="folding_arrow"> (&darr;'. ereg_replace("[^0-9]", "", (string) $xmlobj->user->Change_Rank_7days).')</span>'; }
?><br />
<span style="color: #<?php echo get_option('folding_results_color'); ?>; font-weight: <?php echo get_option('folding_results_bold') ?>;"><?php echo number_format((string) $xmlobj->user->Points, 0, "", ","); ?></span><br />
<span style="color: #<?php echo get_option('folding_results_color'); ?>; font-weight: <?php echo get_option('folding_results_bold') ?>;"><?php echo number_format((string) $xmlobj->user->Points_24hr_Avg, 0, "", ","); ?></span><br />
<span style="color: #<?php echo get_option('folding_results_color'); ?>; font-weight: <?php echo get_option('folding_results_bold') ?>;"><?php echo number_format((string) $xmlobj->user->Points_Week, 0, "", ","); ?></span><br />
<span style="color: #<?php echo get_option('folding_results_color'); ?>; font-weight: <?php echo get_option('folding_results_bold') ?>;"><?php echo number_format((string) $xmlobj->user->WUs, 0, "", ","); ?></span><br />
<span style="color: #<?php echo get_option('folding_results_color'); ?>; font-weight: <?php echo get_option('folding_results_bold') ?>;"><?php if ( get_option('folding_results_team') == 'true') { echo '<span class="folding_links"><a href="http://folding.extremeoverclocking.com/team_summary.php?s=&amp;t='. get_option('folding_team').'">' . (string) $xmlobj->team->Team_Name .'</a></span>'; } else { echo (string) $xmlobj->team->Team_Name; } ?></span><br />
<span style="color: #<?php echo get_option('folding_results_color'); ?>; font-weight: <?php echo get_option('folding_results_bold') ?>;"><?php echo number_format((string) $xmlobj->team->Rank, 0, "", ","); ?></span>
<?php if ((string) $xmlobj->team->Change_Rank_7days >0 ) { echo '<span class="folding_arrow"> (&uarr;'.(string) $xmlobj->team->Change_Rank_7days.')</span>'; }
	if ((string) $xmlobj->team->Change_Rank_7days <0 ) { echo '<span class="folding_arrow"> (&darr;'. ereg_replace("[^0-9]", "", (string) $xmlobj->team->Change_Rank_7days).')</span>'; }
?><br />
<span style="color: #<?php echo get_option('folding_results_color'); ?>; font-weight: <?php echo get_option('folding_results_bold') ?>;"><?php echo number_format((string) $xmlobj->team->Points, 0, "", ","); ?></span><br />
<span style="color: #<?php echo get_option('folding_results_color'); ?>; font-weight: <?php echo get_option('folding_results_bold') ?>;"><?php echo number_format((string) $xmlobj->team->Users, 0, "", ","); ?></span><span class="folding_arrow"> (<?php echo (string) $xmlobj->team->Users_Active; ?> active)</span><br />
<span style="color: #<?php echo get_option('folding_results_color'); ?>; font-weight: <?php echo get_option('folding_results_bold') ?>;"><?php echo number_format((string) $xmlobj->user->Team_Rank, 0, "", ","); ?></span>
</div>
</div>
<p><?php if (get_option('folding_pic') == 'true') {
	echo '<a href="http://folding.stanford.edu"><img style="border:none;" src="'.$fold_logo.'" alt="Folding@Home" /></a>';
		}
?></p>
<?

} else {
// old ways...
	if (get_option('folding_html') =='' ) {
	read_fold_site();
	} 
	$sFile = get_option('folding_html');
	$sFile = substr($sFile, 0, 6000);
	$last_upd = strstr($sFile, 'Date of last work unit');
	$credit = strstr($sFile, 'Total score');
	$ov_rank = strstr($sFile, 'Overall rank');
	$wu = strstr($sFile, '<TD> WU</TD>');
	$credit = substr($credit, strpos($credit, '=4>') + 4, 12);
	$credit = substr($credit, 0, strpos($credit, '<'));
	$ov_rank = substr($ov_rank, strpos($ov_rank, '=4>') + 4, 20);
	$ov_rank = substr($ov_rank, 0, strpos($ov_rank, 'o')); 
	$wu = substr($wu, strpos($wu, '<b>') + 5, 4);
	$wu = substr($wu, 0, strpos($wu, '<'));
?>
<div id="folding_leg">
<div id="folding_label">
User Name<br />
User Rank<br />
Points<br />
Work Units
</div>
<div id="folding_results">
<span style="color: #<?php echo get_option('folding_results_color'); ?>; font-weight: <?php echo get_option('folding_results_bold') ?>;"><?php echo FOLD_ACCT; ?></span><br />
<span style="color: #<?php echo get_option('folding_results_color'); ?>; font-weight: <?php echo get_option('folding_results_bold') ?>;"><?php echo $ov_rank; ?></span><br />
<span style="color: #<?php echo get_option('folding_results_color'); ?>; font-weight: <?php echo get_option('folding_results_bold') ?>;"><?php echo $credit; ?></span><br />
<span style="color: #<?php echo get_option('folding_results_color'); ?>; font-weight: <?php echo get_option('folding_results_bold') ?>;"><?php echo $wu; ?></span><br />
</div>
</div>
<p><?php if (get_option('folding_pic') == 'true') {
	echo '<a href="http://folding.stanford.edu"><img style="border:none;" src="'.$fold_logo.'" alt="Folding@Home" /></a>';
		}
?></p>
<?php
	// $out = '<p>';
	// $out = $out .'Total Score: <span style="color: #'.get_option('folding_results_color').'; font-weight: '.get_option('folding_results_bold').';">'.$credit.'</span><br />';
	// $out = $out .'OverallRank: <span style="color: #'.get_option('folding_results_color').'; font-weight: '.get_option('folding_results_bold').';">'.$ov_rank.'</span><br />';
	// $out = $out .'WorkUnits  : <span style="color: #'.get_option('folding_results_color').'; font-weight: '.get_option('folding_results_bold').';">'.$wu.'</span></p>';
	// if (get_option('folding_pic') == 'true') {
	// $out = $out . '<br /><a href="http://folding.stanford.edu"><img style="border:none;" src="'.$fold_logo.'" alt="Folding@Home" /></a>';
//	}
//	update_option('folding_expire',$expire);
//	echo $out; 
	} // end old ways
	} // end draw
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
		} //end get_contents
		
function folding_head() {
	$css_url = get_bloginfo("wpurl") . '/wp-content/plugins/folding-stats-plus/css/folding-def.css';
	if ( file_exists(TEMPLATEPATH . "/folding.css") ){
		$css_url = get_bloginfo("template_url") . "/folding.css";
	}
	echo "\n" . '<!-- Folding css -->';
	echo "\n" . '<link rel="stylesheet" href="' . $css_url . '" type="text/css" media="screen" />';
}
function folding_admin_head() {
	$css_url = get_bloginfo("wpurl") . '/wp-content/plugins/folding-stats-plus/css/admin.css';
	echo "\n" . '<!-- Folding css -->';
	echo "\n" . '<link rel="stylesheet" href="' . $css_url . '" type="text/css" media="screen" />';
}

?>