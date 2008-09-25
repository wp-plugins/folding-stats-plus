=== Folding Stats Plus ===
Contributors:  pross
Author URI:  http://www.pross.org.uk
Plugin URL:  http://www.pross.org.uk/category/plugins/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=pross2%40gmail%2ecom&item_name=Simon%20Prosser&page_style=PayPal&no_shipping=0&return=http%3a%2f%2fwww%2epross%2eorg%2euk&no_note=1&tax=0&currency_code=GBP&lc=GB&bn=PP%2dDonationsBF&charset=UTF%2d8

Requires at Least:  WordPress 2.0.x (all versions) / PHP 4.0 >= 4.3 or 5.0+
Tested Up To:  WordPress 2.6.2
Tags:  Folding@Home, Folding, stats
Stable tag: 0.3 

== Description ==
This plugin is intended to show the current Folding@Home statistics for a given account.

== Installation ==
* upload the contents of the zip file to your wp-content/plugins directory
* ensure the folding-stats plugin directory has write permissions (used for file cache)
* go to the Plugins main menu and find Folding@Home Stats, then click Activate
* go to the Options / Folding Options menu and enter your account id, and the number of hours to wait to refresh the cache

You are good to go.

== Usage ==
Use the included widget in your sidebar, or use the following code to include the stats anywhere in your theme.
<php get_folding_stats(); ?>


== Frequently Asked Questions ==
Q. I installed the plugin, but I get a Cannot Open File message
A. This is probably because the cache file cannot be created.  Ensure the folding-stats plugin directory has write permissions. (e.g. chmod 777)

== Screenshots ==
See it in action on my site:  http://www.pross.org.uk

== Un-Installation ==
* go to the Plugins main menu and find Folding@Home Stats Plus, then click Deactivate 
* delete the files from your wp-content/plugins directory 

== Change Log ==
See website

