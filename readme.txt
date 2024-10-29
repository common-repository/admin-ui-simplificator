=== Admin UI Simplificator ===
Contributors: lordspace,orbisius
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7APYDVPBCSY9A
Tags: admin ui, ux, admin ui, simple, simplificator, adminimize, better ui, better ux, simple ui color, scheme, theme, admin, dashboard, color scheme, plugin, interface, ui, metabox, hide, editor, minimal, menu, customization, interface, administration, lite, light, usability, lightweight, layout, zen
Requires at least: 2.6.2
Tested up to: 5.2
Stable tag: 1.0.5
License: GPL v2

The plugin simplifies the WordPress admin user interface by hiding most of the WordPress menus.

== Description ==

The plugin simplifies the WordPress admin user interface by hiding most of the WordPress menus.
This doesn't mean that the functionality is not accessible. It is just hidden.
Also the admin navigation bar is cleaned as well.
The plugin is intended to be used by developers/designers who manage WordPress on behalf of their clients.
Clients don't need to know or see anything about plugins, themes, available updates etc.

= Usage =

* Install and Activate the plugin
* Create a separate account for your client
* Everybody except the user who activated the plugin will sees the simplified WordPress admin area

If you end up locking yourself out add this line to your *wp-config.php* file just right after the opening <?php tag.
define('ADMIN_UI_SIMPLIFICATOR_DISABLE', 1);

= Demo =

http://www.youtube.com/watch?v=xQLe2uxmWiA

= Benefits / Features =

* Easy to use
* Just activate and create a separate account for your client.

<a href="http://orbisius.com/go/intro2site?wp_admin_ui_simplificator"
    target="_blank">Free e-book: How to Build a Website Using WordPress: Beginners Guide</a>

= Author =

Svetoslav Marinov (Slavi) | <a href="http://orbisius.com/?utm_source=admin-ui-simplificator&utm_medium=readme-author&utm_campaign=plugin-update" title="Custom Web Programming, Web Design, e-commerce, e-store, Wordpress Plugin Development, Facebook and Mobile App Development in Niagara Falls, St. Catharines, Ontario, Canada" target="_blank">Custom Web and Mobile Programming by Orbisius.com</a>

Icon Credits: famfamfam

**NOTE: ** Support is handled in our support forums: <a href="http://club.orbisius.com/support/?utm_source=admin-ui-simplificator&utm_medium=readme&utm_campaign=plugin-update" target="_blank" title="[new window]">http://club.orbisius.com/forums/</a>
Please do NOT use the WordPress forums to seek support. 

== Installation ==

= Automatic Install =
Please go to Wordpress Admin &gt; Plugins &gt; Add New Plugin &gt; Search for: Admin UI simplificator and then press install

= Manual Installation =
1. Upload wp-admin-ui-simplificator.zip into to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

If you end up locking yourself out add this line to your *wp-config.php* file just right after the opening <?php tag.
define('ADMIN_UI_SIMPLIFICATOR_DISABLE', 1);


If you happen to remove the original admin account and then enter your current admin user ID and click on save
yoursite.com/wordpress/wp-admin/admin.php?page=admin-ui-simplificator/menu.settings.php


Run into issues or have questions/suggestions? 
Visit our support forums: <a href="http://club.orbisius.com/support/?utm_source=admin-ui-simplificator&utm_medium=readme-faq&utm_campaign=plugin-update" target="_blank" title="[new window]">http://club.orbisius.com/forums/</a>

== Screenshots ==
1. Admin area after the plugin activation.

== Upgrade Notice ==
n/a

== Changelog ==

= 1.0.6 =
* added these consts to optionally clean the other UI elements.
* define('ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR_REMOVE_POSTS', 1);
* define('ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR_REMOVE_PAGES', 1);
* define('ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR_REMOVE_MEDIA', 1);

* Tested with WP 5.2
* Turn on or off the plugin by adding this const. define('ADMIN_UI_SIMPLIFICATOR_ENABLED', 0 or 1);

= 1.0.5 =
* Tested with WP 4.1

= 1.0.4 =
* Fixed warnings on not calling a static method
* Fixed warnings on use of get_option
* Fixed numerous warnings
* Tested with WP 4.0.1
* Removed boostrap file which wasn't used that much and could cause WP to break if wp-load couldn't be found in the expected path.
* Removed the dashboard link ... wasn't used that much.
* Added author in Help
* Added Orbisius News Widget
* Removed calls to zzz_custom

= 1.0.3 =
* Added a constant 'ADMIN_UI_SIMPLIFICATOR_DISABLE' that will allow you to access the admin area
* Improved settings UI
* Tested for wp 3.8.1
* Added links to forum and product page

= 1.0.2 =
* Tested for wp 3.5.2

= 1.0.1 =
* Fixes & improvements
* Added links to support forums

= 1.0.0 =
* Initial Release
