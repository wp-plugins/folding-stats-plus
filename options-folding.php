<?php
/*
	Author:  Simon Prosser
	Author URI: http://www.pross.org.uk
	Description: Displays Folding@Home stats
*/
load_plugin_textdomain('foldhome');
/* Create Default Account Number if it Doesn't Exist */
add_option('fold_acct', 'fold-id');
add_option('fold_expy', '4');
add_option('fold_align', 'left');
add_option('fold_pic', 'true');
add_option('fold_results_bold', 'true');
add_option('fold_results_color', '000000');
add_option('fold_rank', 'short');
$location = get_option('foldurl') . '/wp-admin/admin.php?page=folding-stats-plus/options-folding.php'; // Form Action URI
$script_directory = substr(__FILE__, 0, strrpos(__FILE__, '/'));
$foldfile = $script_directory . '/folding_cache.txt';
/*check form submission and update options*/
if ('process' == $_POST['stage'])
{
  update_option('fold_acct', $_POST['fold_acct']);
  update_option('fold_expy', $_POST['fold_expy']);
  update_option('fold_align', $_POST['fold_align']);
  update_option('fold_results_color', $_POST['fold_results_color']);
		if (isset($_POST['fold_pic'])) {
			update_option('fold_pic', 'true');
		} else {
			update_option('fold_pic', 'false');
		}
		if (isset($_POST['fold_results_bold'])) {
			update_option('fold_results_bold', 'bold');
		} else {
			update_option('fold_results_bold', 'normal');
		}
			if (isset($_POST['fold_rank'])) {
			update_option('fold_rank', 'full');
		} else {
			update_option('fold_rank', 'short');
		}	
if (isset($_POST['fold_del'])) {
//unlink($foldfile);
update_option('folding_expire',0);
}
}
/*Get options for form fields*/
$fold_acct = get_option('fold_acct');
$fold_expy = get_option('fold_expy');
$fold_align = get_option('fold_align');
$fold_pic = get_option('fold_pic');
$fold_results_bold = get_option('fold_results_bold');
$fold_results_color = get_option('fold_results_color');
$fold_rank = get_option('fold_rank');
?>
<form name="fold"></form>
<div class="wrap">
  <h2><?php _e('Folding Options') ?></h2>
  <form id="fold_form" name="form1" method="post" action="<?php echo $location ?>&amp;updated=true">
  	<input type="hidden" name="stage" value="process" />
  	<fieldset class="options">
  		<h3><?php _e('Personal Settings') ?></h3>
  		<div style="width:350px;text-align:left;padding:10px;background-color:#F5F5F5;border: 1px solid black;font-size:10px;">
		<p><label  for="fold_acct">Account ID:</label><input style="width: 200px;" id="fold_acct" name="fold_acct" type="text" value="<?php echo $fold_acct;?>" /></p>
			<p><label for="fold_expy">Refresh (in Hours):</label>
			<input style="width: 50px;" id="fold_expy" name="fold_expy" type="text" value="<?php echo $fold_expy;?>" /><br />			
					<label for="fold_acct">Stats Alignment:</label>
					Left:<input id="fold_align" name="fold_align" type="radio" value="left" <?php if(get_option('fold_align') == 'left') {?> checked="checked" <?php } ?> />
					Right:<input id="fold_align" name="fold_align" type="radio" value="right" <?php if(get_option('fold_align') == 'right') {?> checked="checked" <?php } ?> /></p>
					<p><label for="fold_pic">Show Picture:</label>
					<input id="fold_pic" name="fold_pic" type="checkbox" value="<?php print get_option('fold_pic');?>" <?php if(get_option('fold_pic') == 'true') {?> checked="checked" <?php } ?>/></p>
					<p><label for="fold_results_bold">Results in Bold?:</label>
					<input id="fold_results_bold" name="fold_results_bold" type="checkbox" value="<?php print get_option('fold_results_bold');?>" <?php if(get_option('fold_results_bold') == 'bold') {?> checked="checked" <?php } ?>/></p>		
					<p><label for="fold_results_color">Results Color:</label>
					<input id="fold_results_color" name="fold_results_color" type="text" value="<?php print get_option('fold_results_color');?>" style="width: 60px;" /> Eg: <font style="color: #ff0000;">ff0000</font><font style="color: #00ff00;"> 00ff00 </font><font style="color: #c0c0c0;">c0c0c0</font></p>
					<p><label for="fold_rank">Show full rank:</label>
					<input id="fold_rank" name="fold_rank" type="checkbox" value="<?php print get_option('fold_rank');?>" <?php if(get_option('fold_rank') == 'full') {?> checked="checked" <?php } ?>/></p></div>	
		<br />
					<p><label for="fold_del">Delete cache?:</label>
					<input id="fold_del" name="fold_del" type="checkbox" value="delete"  /></p>
			    </fieldset>
<h2>Preview:</h2>
<ul><li>Total Score: <font style="font-weight: <?php print get_option('fold_results_bold'); ?>; color: #<?php print get_option('fold_results_color'); ?>;">19916</font></li>
<li>Overall Rank: <font style="font-weight: <?php print get_option('fold_results_bold'); ?>; color: #<?php print get_option('fold_results_color'); ?>;">143752<?php if (get_option('fold_rank') == 'full') { ?> of 1122430 <?php } ?></font></li>
    <li>WorkUnits: <font style="font-weight: <?php print get_option('fold_results_bold'); ?>;  color: #<?php print get_option('fold_results_color'); ?>;">46</font></li>
    <li>Last Update: <font style="font-weight: <?php print get_option('fold_results_bold'); ?>; color: #<?php print get_option('fold_results_color'); ?>;">2008-11-06</font></li></ul>
    <p class="submit">
      <input type="submit" name="Submit" value="<?php _e('Update options') ?> &raquo;" />
    </p>
  </form>
</div>