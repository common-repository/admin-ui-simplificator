<?php
/*
  Plugin Name: Admin UI Simplificator
  Plugin URI: https://club.orbisius.com/products/wordpress-plugins/admin-ui-simplificator/
  Description: The plugin simplifies the WordPress admin area
  Version: 1.0.5
  Author: Svetoslav Marinov (Slavi)
  Author URI: https://orbisius.com
  License: GPL v2
 */

/*
  Copyright 2011-2020 Svetoslav Marinov (slavi@slavi.biz)

  This program ais free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; version 2 of the License.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
define('ADMIN_UI_SIMPLIFICATOR_ENABLED', 0);

// we can be called from the test script
if (empty($_ENV['ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR_TEST'])) {
    // Make sure we don't expose any info if called directly
    if (!function_exists('add_action')) {
        echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
        exit;
    }
    
	$orb_wp_simple_ui_obj = Orbisius_WP_Admin_UI_Simplificator::get_instance();
	
    add_action('init', array($orb_wp_simple_ui_obj, 'init'));

    register_activation_hook(__FILE__, array($orb_wp_simple_ui_obj, 'on_activate'));
    register_deactivation_hook(__FILE__, array($orb_wp_simple_ui_obj, 'on_deactivate'));
}

class Orbisius_WP_Admin_UI_Simplificator {
    private $log_enabled = 0;
    private $log_file = null;
    private $permalinks = 0;
    private static $instance = null; // singleton
    private $site_url = null; // filled in later
    private $plugin_url = null; // filled in later
    private $plugin_settings_key = null; // filled in later
    private $plugin_dir_name = null; // filled in later
    private $plugin_data_dir = null; // plugin data directory. for reports and data storing. filled in later
    private $plugin_name = 'Admin UI Simplificator'; //
    private $plugin_id_str = 'admin_ui_simplificator'; //
    private $plugin_business_sandbox = false; // sandbox or live ???
    private $plugin_business_email_sandbox = 'seller_1264288169_biz@slavi.biz'; // used for paypal payments
    private $plugin_business_email = 'billing@orbisius.com'; // used for paypal payments
    private $plugin_business_ipn = 'https://webweb.ca/wp/hosted/payment/ipn.php'; // used for paypal IPN payments
    //private $plugin_business_status_url = 'https://localhost/wp/hosted/payment/status.php'; // used after paypal TXN to to avoid warning of non-ssl return urls
    private $plugin_business_status_url = 'https://ssl.orbisius.com/webweb.ca/wp/hosted/payment/status.php'; // used after paypal TXN to to avoid warning of non-ssl return urls
    private $plugin_support_email = 'help@orbisius.com'; //
    private $plugin_support_link = 'https://miniads.ca/widgets/contact/profile/wp_admin_ui_simplificator?height=200&width=500&description=Please enter your enquiry below.'; //
    private $plugin_admin_url_prefix = null; // filled in later
    private $plugin_author_home_page = 'https://orbisius.com';
    private $plugin_home_page = 'https://orbisius.com';
    private $plugin_tinymce_name = 'wp_admin_ui_simplificator'; // if you change it update the tinymce/editor_plugin.js and reminify the .min.js file.
    private $plugin_cron_hook = __CLASS__;
    private $paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
    private $paypal_submit_image_src = 'https://www.paypal.com/en_GB/i/btn/btn_buynow_LG.gif';

    private $plugin_default_opts = array(
        'status' => 0,
        'logging_enabled' => 0,
    );

	private $app_title = '';
	private $plugin_description = '';

    private $plugin_uploads_path = null; // E.g. /wp-content/uploads/PLUGIN_ID_STR/
    private $plugin_uploads_url = null; // E.g. https://yourdomain/wp-content/uploads/PLUGIN_ID_STR/
    private $plugin_uploads_dir = null; // E.g. DOC_ROOT/wp-content/uploads/PLUGIN_ID_STR/

    private $download_key = null; // the param that will hold the download hash
    private $web_trigger_key = null; // the param will trigger something to happen. (e.g. PayPal IPN, test check etc.)

    // can't be instantiated; just using get_instance
    private function __construct() {
        
    }

    /**
     * handles the singleton
     */
    public static function get_instance() {
		if (is_null(self::$instance)) {
            global $wpdb;
            
			$cls = __CLASS__;	
			$inst = new $cls;
			
			$site_url = site_url();
			$site_url = rtrim($site_url, '/') . '/'; // e.g. https://domain.com/blog/

			$inst->site_url = $site_url;
			$inst->plugin_dir_name = basename(dirname(__FILE__)); // e.g. wp-command-center; this can change e.g. a 123 can be appended if such folder exist
			$inst->plugin_data_dir = dirname(__FILE__) . '/data';
			$inst->plugin_url = $site_url . 'wp-content/plugins/' . $inst->plugin_dir_name . '/';
			$inst->plugin_settings_key = $inst->plugin_id_str . '_settings';			
            $inst->plugin_support_link .= '&css_file=' . urlencode(get_bloginfo('stylesheet_url'));
            $inst->plugin_admin_url_prefix = $site_url . 'wp-admin/admin.php?page=' . $inst->plugin_dir_name;				

            $opts = $inst->get_options();

            if (!$inst->log_enabled && !empty($opts['logging_enabled'])) {
                $inst->log_enabled = $opts['logging_enabled'];
            }

            // the log file be: log.1dd9091e045b9374dfb6b042990d65cc.2012-01-05.log
			if ($inst->log_enabled) {
				$inst->log_file = $inst->plugin_data_dir . '/log.'
                        . md5($site_url . $inst->plugin_dir_name)
                        . '.' . date('Y-m-d') . '.log';
			}

			add_action('plugins_loaded', array($inst, 'init'), 100);
            
			define('ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR_BASE_DIR', dirname(__FILE__)); // e.g. // htdocs/wordpress/wp-content/plugins/wp-command-center
			define('ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR_DIR_NAME', $inst->plugin_dir_name);

            self::$instance = $inst;
        }
		
		return self::$instance;
	}

    public function __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    public function __wakeup() {
        trigger_error('Unserializing is not allowed.', E_USER_ERROR);
    }

    /**
     * Logs whatever is passed IF logs are enabled.
     */
    function log($msg = '') {
        if ($this->log_enabled) {
            $msg = '[' . date('r') . '] ' . '[' . $_SERVER['REMOTE_ADDR'] . '] ' . $msg . "\n";
            error_log($msg, 3, $this->log_file);
        }
    }
    
    /**
     * :)
     * @see https://codex.wordpress.org/Function_Reference/wp_enqueue_script
     */
    function load_scripts() {
        $scripts = array(
        );

        wp_enqueue_script('jquery');

        foreach ($scripts as $js_file) {
            $js_file_fmt = preg_replace('#\W#si', '_', $js_file);
            
            wp_enqueue_script(
                $this->plugin_id_str . '_js_' . $js_file_fmt,
                $this->plugin_url . 'js/' . $js_file
            );
        }

        $css_styles = array(
            'main.css',
        );

        foreach ($css_styles as $css_file) {
            $css_file_fmt = preg_replace('#\W#si', '_', $css_file);
            wp_register_style($this->plugin_id_str . '_css_' . $css_file_fmt, $this->plugin_url . 'css/' . $css_file);
            wp_enqueue_style($this->plugin_id_str . '_css_' . $css_file_fmt);
        }
    }
    
    /**
     * handles the init
     */
    function init() {
        global $wpdb;

        if (is_admin()) {
            // Administration menus
            add_action('admin_menu', array($this, 'administration_menu'));
            add_action('admin_init', array($this, 'register_settings'));
            add_action('admin_notices', array($this, 'notices'));

            // Hide the menu only for other users/admins
            if ($this->canSimplify()) {
                add_action('admin_menu', array($this, 'remove_menus'), 9999);
                add_action('admin_bar_menu', array($this, 'remove_nav_bar_items'), 9999);
                add_action('wp_dashboard_setup', array($this, 'remove_dashboard_widgets'), 9999);				
            } else {
                add_action('admin_bar_menu', array($this, 'add_switch'), 9999);
            }
            
            /*wp_enqueue_script(
                $this->plugin_id_str . '_main_js',
                $this->plugin_url . 'js/main.js'
            );*/
        }

        add_action('wp_head', array($this, 'add_plugin_credits'), 1); // be the first in the header
        add_action('wp_footer', array($this, 'add_plugin_credits'), 1000); // be the last in the footer
        
		add_action('wp_enqueue_scripts', array($this, 'load_scripts'), 10);
    }

    /**
     * a check wheather should be simplify or not.
     */
    function canSimplify() {
        $status = false;
        $opts = $this->get_options();
        $current_user = $this->get_user();

		if (defined('ADMIN_UI_SIMPLIFICATOR_ENABLED')) {
			return ADMIN_UI_SIMPLIFICATOR_ENABLED ? true : false;
		}

	    if (defined('ADMIN_UI_SIMPLIFICATOR_DISABLE') && ADMIN_UI_SIMPLIFICATOR_DISABLE) {
		    return false;
	    }

        // Hide the menu only for other users/admins
        if (!empty($opts['skip_simplification_for_id']) && $current_user->ID != $opts['skip_simplification_for_id']) {
            $status = true;
        }

        return $status;
    }

    /**
     * @see https://codex.wordpress.org/Dashboard_Widgets_API
     */
    function dashboard_welcome_widget() {
        echo "Welcome to the dashboard.";
    }

    /**
     * @see https://www.catswhocode.com/blog/10-wordpress-dashboard-hacks
     * @global type $wp_meta_boxes
     */
    function remove_dashboard_widgets() {
        // Globalize the metaboxes array, this holds all the widgets for wp-admin
        global $wp_meta_boxes;

        // Remove the incomming links widget
        unset($wp_meta_boxes['dashboard']['normal']);
        unset($wp_meta_boxes['dashboard']['side']);

        wp_add_dashboard_widget($this->plugin_id_str . '_dashboard_widget', 'Welcome', array($this, 'dashboard_welcome_widget'));
        
        /*unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);

        // Remove right now
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);*/
    }

    /**
     * TODO: allow switching to default UI on/off
     */
    function add_switch($wp_admin_bar) {
        $opts = $this->get_options();
        
        // if the main admin has allowed the other users to swtich put this in the admin
        if (0&&!empty($opts['allow_switch_to_default_ui_on_off'])) {
            $args = array(
                'id' => $this->plugin_id_str . '_switch_to_default_ui_on_off', // id of the existing child node (New > Post)
                'title' => 'Switch to Simple', // alter the title of existing node
                'parent' => false, // 'top-secondary' // set parent to false to make it a top level (parent) node
                'href' => '',
                'meta' => array('class' => 'ab-top-secondary'),
            );

            $wp_admin_bar->add_node($args);
        }
    }

    /**
     * This removes the clutter from the admin area BUT doesn't stop the access to the access to those menus.
     * @see https://www.wprecipes.com/how-to-remove-menus-in-wordpress-dashboard
     * @see https://hungred.com/how-to/remove-wordpress-admin-menu-affecting-wordpress-core-system/
     * @see admin pos https://blog.rutwick.com/add-items-anywhere-to-the-wp-3-3-admin-bar
     */
    function remove_nav_bar_items($wp_admin_bar) {
        $wp_admin_bar->remove_node('wp-logo');
        $wp_admin_bar->remove_menu('new-content');
        $wp_admin_bar->remove_menu('updates');
        $wp_admin_bar->remove_menu('comments');
        $wp_admin_bar->remove_menu('site-name');
        $wp_admin_bar->remove_node('top-secondary');

	    $current_user = wp_get_current_user(); // https://codex.wordpress.org/Function_Reference/wp_get_current_user
        
        // Logout
        $args = array(
            'id' => 'logout', // id of the existing child node (New > Post)
            'title' => 'Logout ' . $current_user->user_email, // alter the title of existing node // $current_user->ID
            'parent' => false, // 'top-secondary' // set parent to false to make it a top level (parent) node
            'meta' => array('class' => 'ab-top-secondary'),
        );

        $wp_admin_bar->add_node($args);

        $args = array(
            'id' => 'new-post', // id of the existing child node (New > Post)
            'title' => 'Add New Post', // alter the title of existing node
            'parent' => false, // set parent to false to make it a top level (parent) node
        );
        $wp_admin_bar->add_node($args);
        
        $args = array(
            'id' => 'new-page', // id of the existing child node (New > Post)
            'title' => 'Add New Page', // alter the title of existing node
            'parent' => false // set parent to false to make it a top level (parent) node
        );

        $wp_admin_bar->add_node($args);

        // add a parent item
        $args = array(
            'id' => 'orb_my_account',
            'title' => 'My Account',
            'meta' => array('class' => 'ab-top-secondary'),
        );
        $wp_admin_bar->add_node($args);

        // add a child item to a our parent item
        $args = array('id' => 'edit-profile', 'title' => 'Edit Profile', 'parent' => 'orb_my_account');
        $wp_admin_bar->add_node($args);

        // add another child item to a our parent item (not to our first group)
        /*$args = array('id' => 'orb_my_account_sub_change_password',
            'title' => 'Change Password',
            'parent' => 'orb_my_account',
            'href' => '?cmd=upgrade',
        );
        $wp_admin_bar->add_node($args);

        // add another child item to a our parent item (not to our first group)
        $args = array(
            'id' => 'orb_my_account_sub_affiliates',
            'title' => 'Affiliates ($)',
            'parent' => 'orb_my_account',
            'href' => '?cmd=upgrade',
        );
        $wp_admin_bar->add_node($args);

        // add a group node with a class "first-toolbar-group"
        $args = array(
            'id' => 'first_group',
            'parent' => 'orb_my_account',
            'meta' => array('class' => 'first-toolbar-group'),
        );
        $wp_admin_bar->add_group($args);

        // add an item to a our group item
        $args = array(
            'id' => 'first_grouped_node',
            'title' => 'Addons',
            'parent' => 'first_group',
            'href' => '?cmd=addons',
        );
        $wp_admin_bar->add_node($args);
        */
        
        // View Site
        $args = array(
            'id' => 'view-site', // id of the existing child node (New > Post)
            'title' => 'View Your Site', // alter the title of existing node
            'parent' => false, // 'top-secondary' // set parent to false to make it a top level (parent) node
            'meta' => array('class' => 'ab-top-secondary', 'target' => '_blank'),
        );

        $wp_admin_bar->add_node($args);
    }

    /**
     * This removes the clutter from the admin area BUT doesn't stop the access to the access to those menus.
     * @see https://www.wprecipes.com/how-to-remove-menus-in-wordpress-dashboard
     * @see https://hungred.com/how-to/remove-wordpress-admin-menu-affecting-wordpress-core-system/
     */
    function remove_menus() {
        /*remove_menu_page('edit.php');
        remove_menu_page('link-manager.php');
        remove_menu_page('themes.php');
        remove_menu_page('tools.php');
        remove_menu_page('upload.php');
        remove_menu_page('edit-comments.php');
        remove_menu_page('plugins.php');
        remove_submenu_page( 'options-general.php', 'options-media.php' );

        remove_menu_page('link-manager.php');
        //remove_menu_page('themes.php');
        remove_submenu_page( 'themes.php', 'themes.php' );
        remove_submenu_page( 'themes.php', 'theme-editor.php' );
        remove_submenu_page( 'themes.php', 'themes.php?page=custom-background' );
        remove_submenu_page( 'widgets.php', 'theme-editor.php' );
        remove_menu_page('tools.php');
        remove_menu_page('upload.php');
        remove_menu_page('edit-comments.php');
        remove_menu_page('plugins.php');
        remove_menu_page('admin.php?page=w3tc_general');
        remove_menu_page('admin.php?page=better_wp_security');
        remove_menu_page('admin.php?page=wpcf7');
        remove_submenu_page( 'index.php', 'update-core.php' );
        remove_submenu_page( 'options-general.php', 'options-discussion.php' );
        remove_submenu_page( 'options-general.php', 'options-writing.php' );
        remove_submenu_page( 'options-general.php', 'options-reading.php' );
        remove_submenu_page( 'options-general.php', 'options-permalink.php' );
        remove_submenu_page( 'options-general.php', 'options-media.php' );*/

        remove_submenu_page( 'index.php', 'update-core.php' );
        
        global $menu;
        // __('Posts'), __('Dashboard'),
        $restricted = array(__('Links'), __('Appearance'), __('Users'), __('Settings'), __('Tools'), __('Comments'), __('Plugins'), );
        end ($menu);
        
        while (prev($menu)){
            $value = explode(' ', $menu[key($menu)][0]);

            if (in_array($value[0] != NULL ? $value[0] : "" , $restricted)) {
                unset($menu[key($menu)]);
            }
        }

        if (defined('ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR_REMOVE_POSTS')
            && ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR_REMOVE_PAGES) {
	        remove_menu_page( 'edit.php' );                   // Posts
        }

        if (defined('ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR_REMOVE_PAGES')
            && ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR_REMOVE_PAGES) {
	        remove_menu_page( 'edit.php?post_type=page' );                   // Pages
        }

	    if (defined('ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR_REMOVE_MEDIA')
            && ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR_REMOVE_MEDIA) {
		    remove_menu_page( 'upload.php' );
	    }
    }

    /**
     * returns the obj of currently logged in user.
     * @global type $current_user
     * @return type
     */
    function get_user() {
        wp_get_current_user();
        global $current_user;
        $current_user->ID;

        return $current_user;
    }

    /**
     * Handles the plugin activation. creates db tables and uploads dir with an htaccess file
     */
    function on_activate() {
        $opts = array();

        $current_user = $this->get_user();
        $id = $current_user->ID;

        $opts['status'] = 1; // auto enable
        $opts['skip_simplification_for_id'] = $id; // the plugin will skip this user

        $this->set_options($opts);
    }

    /**
     * Handles the plugin deactivation.
     */
    function on_deactivate() {
        $opts = array();

        $opts['status'] = 0;
        $this->set_options($opts);
    }

    /**
     * Handles the plugin uninstallation.
     */
    function on_uninstall() {
        delete_option($this->plugin_settings_key);
    }

    /**
     * Allows access to some private vars
     * @param str $var
     */
    public function get($var) {
        if (isset($this->$var) /* && (strpos($var, 'plugin') !== false) */) {
            return $this->$var;
        }
    }

    /**
     * gets current options and return the default ones if not exist
     * @param void
     * @return array
     */
    function get_options() {
        $opts = get_option($this->plugin_settings_key);
        $opts = empty($opts) ? array() : (array) $opts;

        // if we've introduced a new default key/value it'll show up.
        $opts = array_merge($this->plugin_default_opts, $opts);

        return $opts;
    }

    /**
     * Updates options but it merges them unless $override is set to 1
     * that way we could just update one variable of the settings.
     */
    function set_options($opts = array(), $override = 0) {
        if (!$override) {
            $old_opts = $this->get_options();
            $opts = array_merge($old_opts, $opts);
        }

        update_option($this->plugin_settings_key, $opts);

        return $opts;
    }

    /**
     * This is what the plugin admins will see when they click on the main menu.
     * @var string
     */
    private $plugin_landing_tab = '/menu.settings.php';

    /**
     * Adds the settings in the admin menu
     */
    public function administration_menu() {
        // Settings > FirstInsLeadForm
//        add_options_page(__($this->plugin_name, "ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR"), __($this->plugin_name, "ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR"), 'manage_options', $this->plugin_dir_name . '/menu.settings.php');
        
		if (!$this->canSimplify()) { // if this is the admin user show the plugin section
	        add_menu_page(__($this->plugin_name, $this->plugin_dir_name), __($this->plugin_name, $this->plugin_dir_name),
                    'manage_options', $this->plugin_dir_name . '/menu.settings.php', null, $this->plugin_url . '/images/cup.png');
	
	        //add_submenu_page($this->plugin_dir_name . '/' . $this->plugin_landing_tab, __('Dashboard', $this->plugin_dir_name), __('Dashboard', $this->plugin_dir_name), 'manage_options', $this->plugin_dir_name . '/menu.dashboard.php');
	        add_submenu_page($this->plugin_dir_name . '/' . $this->plugin_landing_tab, __('Settings', $this->plugin_dir_name),
                    __('Settings', $this->plugin_dir_name), 'manage_options', $this->plugin_dir_name . '/menu.settings.php');
            
	        add_submenu_page($this->plugin_dir_name . '/' . $this->plugin_landing_tab, __('Help', $this->plugin_dir_name),
                    __('Help', $this->plugin_dir_name), 'manage_options', $this->plugin_dir_name . '/menu.support.php');
            
	        //add_submenu_page($this->plugin_dir_name . '/' . $this->plugin_landing_tab, __('Contact', $this->plugin_dir_name), __('Contact', $this->plugin_dir_name), 'manage_options', $this->plugin_dir_name . '/menu.contact.php');
	        //add_submenu_page($this->plugin_dir_name . '/' . $this->plugin_landing_tab, __('About', $this->plugin_dir_name), __('About', $this->plugin_dir_name), 'manage_options', $this->plugin_dir_name . '/menu.about.php');
	
	        // when plugins are show add a settings link near my plugin for a quick access to the settings page.
	        add_filter('plugin_action_links', array($this, 'add_plugin_settings_link'), 10, 2);
		}
    }
	
	/**
     * Allows access to some private vars
     * @param str $var
     */
    public function generate_newsletter_box($params = array()) {
        $file = ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR_BASE_DIR . '/zzz_newsletter_box.html';

        $buffer = Orbisius_WP_Admin_UI_SimplificatorUtil::read($file);

        wp_get_current_user();
        global $current_user;
        $user_email = $current_user->user_email;

        $replace_vars = array(
            '%%PLUGIN_URL%%' => $this->get('plugin_url'),
            '%%USER_EMAIL%%' => $user_email,
            '%%PLUGIN_ID_STR%%' => $this->get('plugin_id_str'),
            '%%admin_sidebar%%' => $this->get('plugin_id_str'),
        );

        if (!empty($params['form_only'])) {
            $replace_vars['NEWSLETTER_QR_EXTRA_CLASS'] = "app_hide";
        } else {
            $replace_vars['NEWSLETTER_QR_EXTRA_CLASS'] = "";
        }

        if (!empty($params['src2'])) {
            $replace_vars['SRC2'] = $params['src2'];
        } elseif (!empty($params['SRC2'])) {
            $replace_vars['SRC2'] = $params['SRC2'];
        }

        $buffer = Orbisius_WP_Admin_UI_SimplificatorUtil::replace_vars($buffer, $replace_vars);

        return $buffer;
    }

    /**
     * Allows access to some private vars
     * @param str $var
     */
    public function generate_donate_box() {
        $msg = '';
        $file = ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR_BASE_DIR . '/zzz_donate_box.html';

        if (!empty($_REQUEST['error'])) {
            $msg = $this->message('There was a problem with the payment.');
        }
        
        if (!empty($_REQUEST['ok'])) {
            $msg = $this->message('Thank you so much!', 1);
        }

        $return_url = Orbisius_WP_Admin_UI_SimplificatorUtil::add_url_params($this->get('plugin_business_status_url'), array(
            'r' => $this->get('plugin_admin_url_prefix') . '/menu.dashboard.php&ok=1', // paypal de/escapes
            'status' => 1,
        ));

        $cancel_url = Orbisius_WP_Admin_UI_SimplificatorUtil::add_url_params($this->get('plugin_business_status_url'), array(
            'r' => $this->get('plugin_admin_url_prefix') . '/menu.dashboard.php&error=1', // 
            'status' => 0,
        ));

        $replace_vars = array(
            '%%MSG%%' => $msg,
            '%%AMOUNT%%' => '9.99',
            '%%BUSINESS_EMAIL%%' => $this->plugin_business_email,
            '%%ITEM_NAME%%' => $this->plugin_name . ' Donation',
            '%%ITEM_NAME_REGULARLY%%' => $this->plugin_name . ' Donation (regularly)',
            '%%PLUGIN_URL%%' => $this->get('plugin_url'),
            '%%CUSTOM%%' => http_build_query(array('site_url' => $this->site_url, 'product_name' => $this->plugin_id_str)),
            '%%NOTIFY_URL%%' => $this->get('plugin_business_ipn'),
            '%%RETURN_URL%%' => $return_url,
            '%%CANCEL_URL%%' => $cancel_url,
        );

        // Let's switch the Sandbox settings.
        if ($this->plugin_business_sandbox) {
            $replace_vars['paypal.com'] = 'sandbox.paypal.com';
            $replace_vars['%%BUSINESS_EMAIL%%'] = $this->plugin_business_email_sandbox;
        }

        $buffer = Orbisius_WP_Admin_UI_SimplificatorUtil::read($file);
        $buffer = str_replace(array_keys($replace_vars), array_values($replace_vars), $buffer);

        return $buffer;
    }	

    /**
     * Outputs some options info. No save for now.
     */
    function options() {
		$orb_wp_simple_ui_obj = Orbisius_WP_Admin_UI_Simplificator::get_instance();
        $opts = get_option('settings');

        include_once(ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR_BASE_DIR . '/menu.settings.php');
    }

    /**
     * Sets the setting variables
     */
    function register_settings() { // whitelist options
        register_setting($this->plugin_dir_name, $this->plugin_settings_key);
    }

    // Add the ? settings link in Plugins page very good
    function add_plugin_settings_link($links, $file) {
        if ($file == plugin_basename(__FILE__)) {
            //$prefix = 'options-general.php?page=' . dirname(plugin_basename(__FILE__)) . '/';
            $prefix = $this->plugin_admin_url_prefix . '/';

            //$dashboard_link = "<a href=\"{$prefix}menu.dashboard.php\">" . __("Dashboard", $this->plugin_dir_name) . '</a>';
            $settings_link = "<a href=\"{$prefix}menu.settings.php\">" . __("Settings", $this->plugin_dir_name) . '</a>';

            array_unshift($links, $settings_link);
            //array_unshift($links, $dashboard_link);
        }

        return $links;
    }

    /**
     * adds some HTML comments in the page so people would know that this plugin powers their site.
     */
    function add_plugin_credits() {
        //printf("\n" . '<meta name="generator" content="Powered by ' . $this->plugin_name . ' (' . $this->plugin_home_page . ') " />' . PHP_EOL);
        printf(PHP_EOL . '<!-- ' . PHP_EOL . 'Powered by ' . $this->plugin_name
                . ': ' . $this->app_title . PHP_EOL
                . 'Created By: ' . $this->plugin_author_home_page . PHP_EOL
                . '-->' . PHP_EOL . PHP_EOL);
    }

    /**
     * Checks if WP simpple shopping cart is installed.
     */
    function notices() {
        $opts = $this->get_options();
return ;
        if (empty($opts['status'])) {
            echo $this->message($this->plugin_name . " is currently disabled. Please, enable it from " 
                    . "<a href='{$this->plugin_admin_url_prefix}/menu.settings.php'> {$this->plugin_name} &gt; Settings</a>");
        }
    }

    /**
     * Outputs a message (adds some paragraphs)
     */
    function message($msg, $status = 0) {
        $id = $this->plugin_id_str;
        $cls = empty($status) ? 'error fade' : 'success update'; // update is the WP class for success ?!?

        $str = <<<MSG_EOF
<div id='$id-notice' class='$cls'><p><strong>$msg</strong></p></div>
MSG_EOF;
        return $str;
    }

    /**
     * a simple status message, no formatting except color
     */
    function msg($msg, $status = 0, $use_inline_css = 0) {
        $inline_css = '';
        $id = $this->plugin_id_str;
        $cls = empty($status) ? 'app_error' : 'app_success';

        if ($use_inline_css) {
            $inline_css = empty($status) ? 'background-color:red;' : 'background-color:green;';
            $inline_css .= 'text-align:center;margin-left: auto; margin-right:auto; padding-bottom:10px;color:white;';
        }

        $str = <<<MSG_EOF
<div id='$id-notice' class='$cls' style="$inline_css"><strong>$msg</strong></div>
MSG_EOF;
        return $str;
    }
	
    /**
     * a simple status message, no formatting except color, simpler than its brothers
     */
    function m($msg, $status = 0, $use_inline_css = 0) {        
        $cls = empty($status) ? 'app_error' : 'app_success';
        $inline_css = '';

        if ($use_inline_css) {
            $inline_css = empty($status) ? 'color:red;' : 'color:green;';
            $inline_css .= 'text-align:center;margin-left: auto; margin-right: auto;';
        }

        $str = <<<MSG_EOF
<span class='$cls' style="$inline_css">$msg</span>
MSG_EOF;
        return $str;
    }

    private $errors = array();

    /**
     * accumulates error messages
     * @param array $err
     * @return void
     */
    function add_error($err) {
        return $this->errors[] = $err;
    }

    /**
     * @return array
     */
    function get_errors() {
        return $this->errors;
    }
    
    function get_errors_str() {
        $str  = join("<br/>", $this->get_errors());
        return $str;
    }

    /**
     *
     * @return bool
     */
    function has_errors() {
        return !empty($this->errors) ? 1 : 0;
    }
}

class Orbisius_WP_Admin_UI_SimplificatorUtil {
    // options for read/write methods.
    const FILE_APPEND = 1;
    const UNSERIALIZE_DATA = 2;
    const SERIALIZE_DATA = 3;

    /**
     * Loads news from Club Orbsius Site.
     * <?php Orbisius_WP_Admin_UI_SimplificatorUtil::output_orb_widget(); ?>
     */
    public static function output_orb_widget($obj = '') {
        ?>
        <!-- Orbisius JS Widget -->
            <?php
                $naked_domain = !empty($_SERVER['DEV_ENV']) ? 'orbclub.com.clients.com' : 'club.orbisius.com';

                if (!empty($_SERVER['DEV_ENV']) && is_ssl()) {
                    $naked_domain = 'ssl.orbisius.com/club';
                }

				// obj could be 'author'
                $obj = empty($obj) ? str_replace('.php', '', basename(__FILE__)) : sanitize_title($obj);

                $params = '?' . http_build_query(array('p' => $obj, 'layout' => 'plugin', ));
                echo '<div class="orbisius_ext_content"></div>' . "\n";
                echo "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://$naked_domain/wpu/widget/$params';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'orbsius-js');</script>";
            ?>
            <!-- /Orbisius JS Widget -->
        <?php
    }
    /**
     * Checks if we are on a page that belongs to our plugin.
     * It is really annoying to see a notice in every section of WordPress.
     * That way the notice will be shown only on the plugin's page.
     */
    public static function is_on_plugin_page() {
        $orb_wp_simple_ui_obj = Orbisius_WP_Admin_UI_Simplificator::get_instance();

        $req_uri = $_SERVER['REQUEST_URI'];
        $id_str = $orb_wp_simple_ui_obj->get('plugin_id_str');

        $req_uri = str_replace('_', '-', $req_uri);
        $id_str = str_replace('_', '-', $id_str);

        // because the plugin id str and uri can have dashes or underscore we'll make underscore dashes
        // for both req uri and plugin str id
        $stat = preg_match('#' . preg_quote($id_str) . '#si', $req_uri);

        return $stat;
    }

    /**
     * Replaces the template variables
     * @param string buffer to operate on
     * @param array the keys are uppercased and surrounded by %%KEY_NAME%%
     * @return string modified data
     */
    public static function replace_vars($buffer, $params = array()) {
        $matches = array();
        
        foreach ($params as $key => $value) {
            $key = trim($key, '%');
            $key = strtoupper($key);
            $key = '%%' . $key . '%%';

            $buffer = str_ireplace($key, $value, $buffer);
        }
//        var_dump($params);
        // Let's check if there are unreplaced variables
        if (preg_match('#(%%[\w-]+%%)#si', $buffer, $matches)) {
//            trigger_error("Not all template variables were replaced. Please check the missing and add them to the input params." . join(",", $matches[1]), E_USER_WARNING);
            trigger_error("Not all template variables were replaced. Please check the missing and add them to the input params." . var_export($matches, 1), E_USER_WARNING);
        }

        return $buffer;
    }
    
    /**
     * Checks if the url is valid
     * @param string $url
     */
    public static function validate_url($url = '') {
        $status = preg_match("@^(?:ht|f)tps?://@si", $url);

        return $status;
    }

    /**
     *
     * @param string $buffer
     */
    public static function sanitizeFile($str = '', $lowercase = 0, $sep = '-') {
        $str = urldecode($str);
        $ext = @end(explode('.', $str));

        if (function_exists('iconv')) {
            $src    = "UTF-8";
            // If you append the string //TRANSLIT to out_charset  transliteration is activated.
            $target = "ISO-8859-1//TRANSLIT";
            $str = iconv($src, $target, $str);
        }

        $ext = preg_replace('#[^a-z\d]+#', '', $ext);
        $ext = strtolower($ext);

        $str = preg_replace('#\.\w{2,5}$#si', '', $str); // remove ext
        $str = preg_replace('#[^\w\-]+#', $sep, $str);
        $str = preg_replace('#[\s\-\_]+#', $sep, $str);
        $str = trim($str, ' /\\ -_');

        // If there are non-english characters they will be replaced with entities which we'll use
        // as guideline to find the equivalent in English.
        $str = htmlentities($str);

        // non-enlgish -> english equivalent
        $str = preg_replace('/&([a-z][ez]?)(?:acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);/si', '\\1', $str);

        // remove any unrecognized entities
        $str = preg_replace('/&([a-z]+);/', '', $str);

        // remove any unrecognized entities
        $str = preg_replace('@\&\#\d+;@s', '', $str);

        if ($lowercase) {
            $str = strtolower($str);
        }

        // There are creazy people that may enter longer link :)
        $str = substr($str, 0, 200);

        if (empty($str)) {
            $str = 'default-name-' . time();
        }
        
        if (empty($ext)) {
            $ext = 'default-ext';
        }

        $str .= '.' . $ext;

        return $str;
    }

    /**
     * Generates the hash + salt
     *
     * @param type $input_str
     * @return string
     */
    public static function generate_hash($input_str = '') {
        $orb_wp_simple_ui_obj = Orbisius_WP_Admin_UI_Simplificator::get_instance();

        $res = sha1($input_str . $_SERVER['HTTP_HOST'] . '-' . $orb_wp_simple_ui_obj->get('plugin_id_str'));

        return $res;
    }
    
    const SANITIZE_NUMERIC = 1;
    const SANITIZE_ALPHA_NUMERIC = 2;

    /**
     * Initially this was planned to be a function to clean the IDs. Not it stops when invalid input is found.
     * 
     * @param string $value
     * @return string
     */
    public static function stop_bad_input($value = '', $type_id = self::SANITIZE_NUMERIC) {
        if (!empty($value)) {
            $msg = '';
            $orb_wp_simple_ui_obj = Orbisius_WP_Admin_UI_Simplificator::get_instance();
            
            if ($type_id == self::SANITIZE_NUMERIC && !is_numeric($value)) {
                $orb_wp_simple_ui_obj->log("Invalid value supplied. Received: \n----------------------------------------------------\n"
                            . $value
                            . "\n----------------------------------------------------\n");
                $msg = $orb_wp_simple_ui_obj->get('plugin_id_str') . ': Received invalid input. <!-- r: n -->';
            } elseif ($type_id == self::SANITIZE_ALPHA_NUMERIC && !preg_match('#^[\w-]+$#si', $value)) { // alphanum from start to end + dash
                $orb_wp_simple_ui_obj->log("Invalid value supplied. Received: \n----------------------------------------------------\n"
                            . $value
                            . "\n----------------------------------------------------\n");
                $msg = $orb_wp_simple_ui_obj->get('plugin_id_str') . ': Received invalid input. <!-- r: an -->';
            }

            if (!empty($msg)) {
                $msg = $orb_wp_simple_ui_obj->m($msg, 0, 1) . $orb_wp_simple_ui_obj->add_plugin_credits();
                wp_die($msg);
            }
        }
        
        return $value;
    }

    /**
     * @desc write function using flock
     *
     * @param string $vars
     * @param string $buffer
     * @param int $append
     * @return bool
     */
    public static function write($file, $buffer = '', $option = null) {
        $buff = false;
        $tries = 0;
        $handle = '';

        $write_mod = 'wb';

        if ($option == self::SERIALIZE_DATA) {
            $buffer = serialize($buffer);
        } elseif ($option == self::FILE_APPEND) {
            $write_mod = 'ab';
        }

        if (($handle = @fopen($file, $write_mod))
                && flock($handle, LOCK_EX)) {
            // lock obtained
            if (fwrite($handle, $buffer) !== false) {
                @fclose($handle);
                return true;
            }
        }

        return false;
    }

    /**
     * @desc read function using flock
     *
     * @param string $vars
     * @param string $buffer
     * @param int $option whether to unserialize the data
     * @return mixed : string/data struct
     */
    public static function read($file, $option = null) {
        $buff = false;
        $read_mod = "rb";
        $handle = false;

        if (($handle = @fopen($file, $read_mod))
                && (flock($handle, LOCK_EX))) { //  | LOCK_NB - let's block; we want everything saved
            $buff = @fread($handle, filesize($file));
            @fclose($handle);
        }

        if ($option == self::UNSERIALIZE_DATA) {
            $buff = unserialize($buff);
        }

        return $buff;
    }

    /**
     *
     * Appends a parameter to an url; uses '?' or '&'
     * It's the reverse of parse_str().
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    public static function add_url_params($url, $params = array()) {
        $str = '';

        $params = (array) $params;

        if (empty($params)) {
            return $url;
        }

        $query_start = (strpos($url, '?') === false) ? '?' : '&';

        foreach ($params as $key => $value) {
            $str .= ( strlen($str) < 1) ? $query_start : '&';
            $str .= rawurlencode($key) . '=' . rawurlencode($value);
        }

        $str = $url . $str;

        return $str;
    }

    // generates input text select
    public static function html_text($name = '', $val = null, $attr = '') {
        if (is_null($val) && !empty($_REQUEST[$name])) {
            $val = $_REQUEST[$name];
        }
        
        $html = "\n" . '<input type="text" name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" ' . $attr . ' value="' . esc_attr($val) . '"/>' . "\n";

        return $html;
    }

    // generates HTML select
    public static function html_select($name = '', $sel = null, $options = array(), $attr = '') {
        $html = "\n" . '<select name="' . $name . '" id="' . $name . '" ' . $attr . '>' . "\n";

        foreach ($options as $key => $label) {
            $selected = $sel == $key ? ' selected="selected"' : '';
            $html .= "\t<option value='$key' $selected>$label</option>\n";
        }

        $html .= '</select>';
        $html .= "\n";

        return $html;
    }

    // generates status msg
    public static function msg($msg = '', $status = 0) {
        $cls = empty($status) ? 'error' : 'success';
        $cls = $status == 2 ? 'notice' : $cls;

        $msg = "<p class='status_wrapper'><div class=\"status_msg $cls\">$msg</div></p>";

        return $msg;
    }
}

/**
 * This class is a common class for every plugin developed by WebWeb.ca team.
 *
 * @author Svetoslav Marinov | https://WebWeb.ca
 */
class Orbisius_WP_Admin_UI_SimplificatorCrawler {

    protected $user_agent = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0) Gecko/20100101 Firefox/6.0";
    protected $error = null;
    protected $buffer = null;

    function __construct() {
        ini_set('user_agent', $this->user_agent);
    }

    /**
     * Error(s) from the last request
     *
     * @return string
     */
    function getError() {
        return $this->error;
    }

    // checks if buffer is gzip encoded
    function is_gziped($buffer) {
        return (strcmp(substr($buffer, 0, 8), "\x1f\x8b\x08\x00\x00\x00\x00\x00") === 0) ? true : false;
    }

    /*
      henryk at ploetzli dot ch
      15-Feb-2002 04:28
      https://php.online.bg/manual/hu/function.gzencode.php
     */

    function gzdecode($string) {
        if (!function_exists('gzinflate')) {
            return false;
        }

        $string = substr($string, 10);
        return gzinflate($string);
    }

    /**
     * Fetches a url and saves the data into an instance variable. The returned status is whether the request was successful.
     *
     * @param string $url
     * @return bool
     */
    function fetch($url) {
        $ok = 0;
        $buffer = '';

        $url = trim($url);

        if (!preg_match("@^(?:ht|f)tps?://@si", $url)) {
            $url = "https://" . $url;
        }

        // try #1 cURL
        // https://fr.php.net/manual/en/function.fopen.php
        if (empty($ok)) {
            if (function_exists("curl_init") && extension_loaded('curl')) {
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Encoding: gzip'));
                curl_setopt($ch, CURLOPT_TIMEOUT, 45);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 5); /* Max redirection to follow */
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

                /* curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ; // in the future pwd protected dirs
                  curl_setopt($ch, CURLOPT_USERPWD, "username:password"); */ //  https://php.net/manual/en/function.curl-setopt.php

                $string = curl_exec($ch);
                $curl_res = curl_error($ch);

                curl_close($ch);

                if (empty($curl_res) && strlen($string)) {
                    if ($this->is_gziped($string)) {
                        $string = $this->gzdecode($string);
                    }

                    $this->buffer = $string;

                    return 1;
                } else {
                    $this->error = $curl_res;
                    return 0;
                }
            }
        } // empty ok*/
        // try #2 file_get_contents
        if (empty($ok)) {
            $buffer = @file_get_contents($url);

            if (!empty($buffer)) {
                $this->buffer = $buffer;
                return 1;
            }
        }

        // try #3 fopen
        if (empty($ok) && preg_match("@1|on@si", ini_get("allow_url_fopen"))) {
            $fp = @fopen($url, "r");

            if (!empty($fp)) {
                $in = '';

                while (!feof($fp)) {
                    $in .= fgets($fp, 8192);
                }

                @fclose($fp);
                $buffer = $in;

                if (!empty($buffer)) {
                    $this->buffer = $buffer;
                    return 1;
                }
            }
        }

        return 0;
    }

    function get_content() {
        return $this->buffer;
    }
}
