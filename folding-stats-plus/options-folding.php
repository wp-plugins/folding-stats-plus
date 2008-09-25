<?php
/*
	Author:  Simon Prosser
	Author URI: http://www.pross.org.uk
	Description: Displays Folding@Home stats
*/

load_plugin_textdomain('foldhome');
define('FOLD_FILE', ABSPATH . 'wp-content/plugins/folding-stats-plus/folding_cache.txt');
/* Create Default Account Number if it Doesn't Exist */
add_option('fold_acct', 'fold-id');
add_option('fold_expy', '4');
add_option('fold_align', 'left');
add_option('fold_pic', 'true');
$location = get_option('foldurl') . '/wp-admin/admin.php?page=folding-stats-plus/options-folding.php'; // Form Action URI

$script_directory = substr(__FILE__, 0, strrpos(__FILE__, '/'));
$foldfile = $script_directory . '/folding_cache.txt';
/*check form submission and update options*/
if ('process' == $_POST['stage'])
{
  update_option('fold_acct', $_POST['fold_acct']);
  update_option('fold_expy', $_POST['fold_expy']);
  update_option('fold_align', $_POST['fold_align']);
		if (isset($_POST['fold_pic'])) {
			update_option('fold_pic', 'true');
		} else {
			update_option('fold_pic', 'false');
		}
if (isset($_POST['fold_del'])) {
unlink($foldfile);
}
}

/*Get options for form fields*/
$fold_acct = get_option('fold_acct');
$fold_expy = get_option('fold_expy');
$fold_align = get_option('fold_align');
$fold_pic = get_option('fold_pic');
?>

<form name="fold"></form>
<div class="wrap">
  <h2><?php _e('Folding Options') ?></h2>
  <form id="fold_form" name="form1" method="post" action="<?php echo $location ?>&amp;updated=true">
  	<input type="hidden" name="stage" value="process" />

  	<fieldset class="options">
  		<legend><?php _e('Personal Settings') ?></legend>
  		<table width="100%" cellpadding="5" class="editform">
  			<tr>
					<td><label for="fold_acct">Account ID:</label></td>
					<td><input style="width: 200px;" id="fold_acct" name="fold_acct" type="text" value="<?php echo $fold_acct;?>" /></td>
				</tr>
  			<tr>
					<td><label for="fold_expy">Refresh (in Hours):</label></td>
					<td><input style="width: 50px;" id="fold_expy" name="fold_expy" type="text" value="<?php echo $fold_expy;?>" /></td>
				</tr>
			<tr>
					<td><label for="fold_acct">Stats Alignment:</label></td>
					<td>Left:<input id="fold_align" name="fold_align" type="radio" value="left" <?php if(get_option('fold_align') == 'left') {?> checked="checked" <?php } ?> /></br />
					Right:<input id="fold_align" name="fold_align" type="radio" value="right" <?php if(get_option('fold_align') == 'right') {?> checked="checked" <?php } ?> /></td>
			</tr>
			<tr>
					<td><label for="fold_pic">Show Picture:</label></td>
					<td><input id="fold_pic" name="fold_pic" type="checkbox" value="<?php print get_option('fold_pic');?>" <?php if(get_option('fold_pic') == 'true') {?> checked="checked" <?php } ?>/></td>			
			</tr>
			<tr>
					<td><label for="fold_pic">Delete cache?:</label></td>
					<td><input id="fold_del" name="fold_del" type="checkbox" value="delete"  /></td>
			</tr>
</table>
    </fieldset>

    <p class="submit">
      <input type="submit" name="Submit" value="<?php _e('Update options') ?> &raquo;" />
    </p>
  </form>
</div>