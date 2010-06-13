<?php
/*
Plugin Name: Folding Stats Plus
Plugin URI: http://www.pross.org.uk/category/plugins/
Description: This plugin is intended to show the current Folding@Home statistics for a given account.
Version: 2.0.3
Author: Simon Prosser
Author URI: http://www.pross.org.uk
Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
*/
$version = '2.0.3';
$update = 3600;
/*
	Code is forked with permission from Jason F. Irwin J?fi's version http://www.j2fi.net/2007/03/23/foldinghome-wordpress-plugin/
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
add_action("plugins_loaded", "foldingstats_init");
register_activation_hook(__FILE__, 'folding_activation');
function foldingstats_init() {
register_sidebar_widget('Folding-Stats-Plus', 'widget_foldingstats');
register_widget_control('Folding-Stats-Plus', 'foldingstats_control');
add_action('wp_head', 'folding_head', 12); 
if ( is_admin() ): 
	wp_register_script( 'jscolor', WP_PLUGIN_URL . '/folding-stats-plus/jscolor/jscolor.js' );
	wp_enqueue_script( 'jscolor' );
endif;
$currentLocale = get_locale();
if(!empty($currentLocale)) {
	$moFile = dirname(__FILE__) . "/lang/folding-stats-plus-" . $currentLocale . ".mo";
	if(@file_exists($moFile) && is_readable($moFile)) load_textdomain('folding', $moFile);
	}
}

function widget_foldingstats($args) {
global $version;
global $fold_options;
extract($args);
if (!$fold_options):	
	$fold_options = get_option("widget_foldingstats");
endif;
if (!is_array( $fold_options )) {
	$fold_options = array(
  	'title' => 'Folding-stats',
  	'name' => 'Simon_P',
	'team' => '35216',
	'outer' => '#3f6daf',
	'inner' => '#E1E1FF'		
  	);
}      
echo $before_widget;
echo $before_title;
echo $fold_options['title'];
echo $after_title;
if (!check_version()):
	echo "<strong>".__('Error!!!', 'folding')."</strong>";
else:
	draw_table();
endif;
echo $after_widget;
echo '<!--
Folding-Stats-Plus Version ' . $version . "\n";
echo 'Next stats refresh in '. (time() - $fold_options['expire']) * -1 .' Seconds.' . "\n";
echo 'Advanced XML stats provided by http://folding.extremeoverclocking.com/ with permission ' . "\n";
echo 'http://folding.extremeoverclocking.com/xml/user_summary.php?un=' . $fold_options['name'] . '&t=' . $fold_options['team'] . "\n";
echo ' -->';
}

function foldingstats_control() {
global $fold_options;
if (!$fold_options):	
	$fold_options = get_option("widget_foldingstats");
endif;
if (!is_array( $fold_options )) {
	$fold_options = array(
  	'title' => 'Folding-stats',
  	'name' => 'Simon_P',
	'team' => '35216',
	'outer' => '#3f6daf',
	'inner' => '#E1E1FF'
	);
}      
if ($_POST['foldingstats-Submit']) {
	$fold_options['title'] = htmlspecialchars($_POST['foldingstats-title']);
	$fold_options['name'] =  htmlspecialchars($_POST['foldingstats-name']);
	$fold_options['team'] =  htmlspecialchars($_POST['foldingstats-team']);
	$fold_options['outer'] =  htmlspecialchars($_POST['foldingstats-outer']);
	$fold_options['inner'] =  htmlspecialchars($_POST['foldingstats-inner']);
	$fold_options['expire'] = time() + 1; // force cache to reset...
	update_option("widget_foldingstats", $fold_options);
}
if (!check_version()):
	echo '<strong>PHP5 needed.</strong>.';
else:	
?>
<p>
<label for="foldingstats-title"><?php _e('Title:', 'folding') ?></label>
<input type="text" id="foldingstats-title" name="foldingstats-title" value="<?php echo $fold_options['title'];?>" />
<label for="foldingstats-name"><?php _e('Name:', 'folding') ?></label>
<input type="text" id="foldingstats-name" name="foldingstats-name" value="<?php echo $fold_options['name'];?>" />
<label for="foldingstats-team"><?php _e('Team:', 'folding') ?></label>
<input type="text" id="foldingstats-team" name="foldingstats-team" value="<?php echo $fold_options['team'];?>" />
<label for="foldingstats-outer"><?php _e('Outer:', 'folding') ?></label>
<input type="text" id="foldingstats-outer" name="foldingstats-outer" class="color {hash:true}" value="<?php echo $fold_options['outer'];?>" />	
<label for="foldingstats-inner"><?php _e('Inner:', 'folding') ?></label>
<input type="text" id="foldingstats-inner" name="foldingstats-inner" class="color {hash:true}" value="<?php echo $fold_options['inner'];?>" />
<input type="hidden" id="foldingstats-Submit" name="foldingstats-Submit" value="1" />
</p>
<?php
endif;
}

function draw_table() {
global $update;
get_xml();
global $fold_options;
if (!$fold_options):	
	$fold_options = get_option("widget_foldingstats");
endif;
$xmlobj = simplexml_load_string($fold_options['xml']);
if (!$xmlobj):
	_e('FoldingStats error!!', 'folding');
	$fold_options['expire'] = time() + $update;
	update_option("widget_foldingstats", $fold_options);
else:
?>
<div id="folding_border_main" style="background-color: <?php echo $fold_options['outer']; ?>;" class="rounded_STYLE rounded">
<div class="tl"></div><div class="tr"></div>
<div style="text-align:center; color: #fff;"><?php echo (string) $xmlobj->user->User_Name ?></div>
<div id="folding_user"  style="background-color: <?php echo $fold_options['inner']; ?>;" class="rounded_STYLE rounded">
<div class="tl"></div><div class="tr"></div>
<span class="folding_user" style="float:left;"><?php _e('User Rank', 'folding') ?></span><span class="folding_user_results" ><?php echo number_format((double)$xmlobj->user->Overall_Rank, 0, "", ","); ?><?php if ((string) $xmlobj->user->Change_Rank_7days >0 ) { echo '<span class="folding_arrow"> (&uarr;'.(string) $xmlobj->user->Change_Rank_7days.')</span>'; }
if ((string) $xmlobj->user->Change_Rank_7days <0 ) { echo '<span class="folding_arrow"> (&darr;'. ereg_replace("[^0-9]", "", (string) $xmlobj->user->Change_Rank_7days).')</span>'; }?></span><br />
<span class="folding_user" ><?php _e('Points', 'folding') ?></span><span class="folding_user_results" ><?php echo number_format((double)$xmlobj->user->Points, 0, "", ","); ?></span><br />
<span class="folding_user" ><?php _e('24h Avg', 'folding') ?></span><span class="folding_user_results" ><?php echo number_format((double)$xmlobj->user->Points_24hr_Avg, 0, "", ","); ?></span><br />
<span class="folding_user" ><?php _e('This week', 'folding') ?></span><span class="folding_user_results" ><?php echo number_format((double)$xmlobj->user->Points_Week, 0, "", ","); ?></span><br />
<span class="folding_user" ><?php _e('Work Units', 'folding') ?></span><span class="folding_user_results" ><?php echo number_format((double)$xmlobj->user->WUs, 0, "", ","); ?></span><br />
<div class="bl"></div><div class="br"></div>
</div>
<div id="folding_border"  style="background-color: <?php echo $fold_options['outer']; ?>;" class="rounded_STYLE rounded">
<div class="tl"></div><div class="tr"></div>
<div style="text-align:center; color: #fff;"><?php echo '<a style="color: #fff; text-decoration: none !important; border-bottom: none !important;" href="http://folding.extremeoverclocking.com/team_summary.php?s=&amp;t='. $fold_options['team'] . '">' . (string) $xmlobj->team->Team_Name .'</a>'; ?></div>
<div class="bl"></div><div class="br"></div>
<div id="folding_team"  style="background-color: <?php echo $fold_options['inner']; ?>;" class="rounded_STYLE rounded">
<div class="tl"></div><div class="tr"></div>
<span class="folding_team" ><?php _e('Rank', 'folding') ?></span><span class="folding_team_results" ><?php echo number_format((double)$xmlobj->team->Rank, 0, "", ","); ?></span><br />
<span class="folding_team" ><?php _e('Points', 'folding') ?></span><span class="folding_team_results" ><?php echo number_format((double)$xmlobj->team->Points, 0, "", ","); ?></span><br />
<span class="folding_team" ><?php _e('24h Avg', 'folding') ?></span><span class="folding_team_results" ><?php echo number_format((double)$xmlobj->team->Points_24hr_Avg, 0, "", ","); ?></span><br />
<span class="folding_team" ><?php _e('This week', 'folding') ?></span><span class="folding_team_results" ><?php echo number_format((double)$xmlobj->team->Points_Week, 0, "", ","); ?></span><br />
<span class="folding_team" ><?php _e('Work Units', 'folding') ?></span><span class="folding_team_results" ><?php echo number_format((double)$xmlobj->team->WUs, 0, "", ","); ?></span><br />
<span class="folding_team" ><?php _e('Team Users', 'folding') ?></span><span class="folding_team_results" ><?php echo number_format((double)$xmlobj->team->Users, 0, "", ","); ?><span class="folding_arrow"> (<?php echo (string) $xmlobj->team->Users_Active; _e(')', 'folding');?></span></span><br />
<span class="folding_team" ><?php _e('Your Rank', 'folding') ?></span><span class="folding_team_results" ><?php echo number_format((double) $xmlobj->user->Team_Rank, 0, "", ","); ?>
<?php if ((string) $xmlobj->user->Change_Rank_7days >0 ) { echo '<span class="folding_arrow"> (&uarr;'.(string) $xmlobj->user->Change_Rank_7days.')</span>'; }
if ((string) $xmlobj->user->Change_Rank_7days <0 ) { echo '<span class="folding_arrow"> (&darr;'. ereg_replace("[^0-9]", "", (string) $xmlobj->user->Change_Rank_7days).')</span>'; } ?></span><br />
<div class="bl"></div><div class="br"></div>
</div></div></div>
<?php
endif;
} //end draw

function get_xml() {
global $update;
global $fold_options;
if (!$fold_options):	
	$fold_options = get_option("widget_foldingstats");
endif;
$url = 'http://folding.extremeoverclocking.com/xml/user_summary.php?un=' . $fold_options['name'] . '&t=' . $fold_options['team'];
//check if xml exists?
if ( !$fold_options['xml'] ):
	$fold_options['xml'] = get_contents($url);
	$fold_options['expire'] = time() + $update;
else: //xml exists so check if stale...
	$today = time();
		if ($fold_options['expire'] < $today):
		$xml = get_contents($url);
		//check for valid xml
		$xmlobj = simplexml_load_string($xml);
		if (!$xmlobj):
			//xml no good! site maybe down? reset timer and try again in a few mins...
			$fold_options['expire'] = time() + 900;
		else:
			$fold_options['xml'] = $xml;
			$fold_options['expire'] = time() + $update;
		endif;
	endif;
endif;
update_option("widget_foldingstats", $fold_options);
}

function check_version() {  
     return (floatval(phpversion()) >=5.0 ? TRUE : FALSE);  
}  

function folding_head() {
echo "\n" . '<!-- Folding css -->';
echo "\n" . '<style type="text/css" media="screen">@import url(' . WP_PLUGIN_URL . '/folding-stats-plus/css/folding-def.css);' . "\n</style>";
if ( file_exists(TEMPLATEPATH . "/folding.css") ):
	$css_url = get_bloginfo("template_url") . "/folding.css";
	echo "\n" . '<link rel="stylesheet" href="' . $css_url . '" type="text/css" media="screen" />' . "\n";
endif;
}

function get_contents($url) {
global $version;
$headers = 'Folding-Stats-Plus for Wordpress V'.$version;
$request = new WP_Http;
$result = $request->request( $url , $headers );
return $result['body'];
}

function folding_activation()
{
if ( get_option("folding_xml") ):
	// old data exists...lets try to upgrade...
	$fold_options = get_option("widget_foldingstats");
	if (!is_array( $fold_options )):
		$fold_options = array(
	  	'title' => 'Folding-stats',
	  	'name' => get_option('folding_acct'),
		'team' => get_option('folding_team'),
		'outer' => '#3f6daf',
		'inner' => '#E1E1FF'		
	  	);
	endif;
	delete_option('folding_acct');
	delete_option('folding_expire');
	delete_option('folding_expy');
	delete_option('folding_pic');
	delete_option('folding_results_bold');
	delete_option('folding_results_color');
	delete_option('folding_results_team');
	delete_option('folding_team');
	delete_option('folding_xml');
else:
	$fold_options = array(
  	'title' => 'Folding-stats',
  	'name' => 'Simon_P',
	'team' => '35216',
	'outer' => '#3f6daf',
	'inner' => '#E1E1FF'
	);
update_option("widget_foldingstats", $fold_options);
endif;
}
if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook(__FILE__, 'folding_deinstall');
 
function folding_deinstall() {
 
	delete_option('widget_foldingstats');
}
?>