<?php
/*
Plugin Name: CF Abritrary Text
Plugin URI: http://crowdfavorite.com
Description: Insert arbitrary text (usually ads) at specific places in stories
Version: 0.1a
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

define('CFAT_VERSION', '0.1a');
define('CFAT_DIR', plugin_dir_path(__FILE__));
if (file_exists(trailingslashit(get_template_directory()).'plugins/'.basename(dirname(__FILE__)))) {
	define('CFAT_DIR_URL', trailingslashit(trailingslashit(get_bloginfo('template_url')).'plugins/'.basename(dirname(__FILE__))));
}
else {
	define('CFAT_DIR_URL', trailingslashit(plugins_url(basename(dirname(__FILE__)))));
}
load_plugin_textdomain('cf-arbitrary-text');

class cf_arbitrary_text {

	static protected $enabled = true;

	public static function onInit() {
		// Make sure CF Snippets is available
		if (!class_exists('CF_Snippet')) {
			add_action('admin_notices', 'cf_arbitrary_text::noCfSnippetsNotice');
			self::$enabled = false;
			return;
		}
		global $pagenow;

		if (is_admin() && $pagenow=='options-general.php') {
			add_action('admin_enqueue_scripts', 'cf_arbitrary_text::adminEnqueueScripts');
		}
	}

	/**
	 * Wrapper to show notice if CF Snippets disabled
	 */
	public static function noCfSnippetsNotice() {
		global $pagenow;
    	include('views/notice.php');
	}	

	/**
	 * Message holder
	 */
	public static function parseMessage($name) {
		$messages = array(
			'post_types_saved' => __('Post types were successfully saved.'),
			'package_added' => __('The package was sucessfully added.'),
			'package_edited' => __('The package was successfully edited.'),
			'package_deleted' => __('The package was successfully deleted.'),
		);

		return $messages[$name];
	}

	/**
	 * Admin init actions
	 * 
	 * Add appropriate metaboxes to post types
	 */

	public static function onAdminInit() {
		if (false == self::$enabled) {
			return;
		}
		//register_setting('cf-arbitrary-text-options', 'cf-arbitrary-text', 'cf_arbitrary_text::optionsValidate');

		$options = get_option('_cf-arbitrary-text');
		foreach ($options['post_type'] as $post_type => $null) {
			add_meta_box(
				'cf-arbitrary-text',
				__('CF Arbitrary Text', 'cf-arbitrary-text'),
				'cf_arbitrary_text::arbitraryTextAdminBox',
				$post_type,
				'side'
			);
		}
	}

/*	// leave this in temporarily
	public static function optionsValidate($input) {
		return $input;
	}
*/

	/**
	 * Show the meta box on post edit pages
	 */
	public static function arbitraryTextAdminBox() {
		if (false == self::$enabled) {
			return;
		}
		global $post;
		// Get list of packages (post type)
		$packages = get_option('_cf-arbitrary-text-packages');
		$options = get_post_meta($post->ID, '_cf-arbitrary-text-post', true);

		include('views/admin-box.php');
	}
		
	/**
	 * Add proper data to saved posts
	 */		
	public static function onSavePost($post_id, $post) {
		if (false == self::$enabled) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		remove_action('save_post', 'cf_arbitrary_text::onSavePost', 10);
		if (!empty($_POST['cf-arbitrary-text-post'])) {
			update_post_meta($post_id, '_cf-arbitrary-text-post', $_POST['cf-arbitrary-text-post']);
		}
		else {
			delete_post_meta($post_id, '_cf-arbitrary-text-post');
		}
	}
	
	/**
	 * Add package page
	 */
	public static function addPackage() {
		// Check permissions.
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		if (false == self::$enabled) {
			include('views/disabled.php');
			return;
		}

		// Get list of snippets.
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
			$cf_snippet = new CF_Snippet();
		}

		$keys = $cf_snippet->get_keys();
		sort($keys);

		include('views/add-package.php');
	}

	/**
	 * Process to Add a new package
	 */
	public static function addPackageProcess() {
		// Grab existing packages
		$packages = get_option('_cf-arbitrary-text-packages');
		$new_package_name = esc_html($_POST['package']['name']);

		// Verify name does not exist.
		$new_package = $_POST['zones'];

		// remove palceholder
		unset($new_package['xxx']);

		// Sanitize and verify information

		// Report errors

		// Save information
		$packages[$new_package_name] = $new_package;
		update_option('_cf-arbitrary-text-packages', $packages);

		wp_redirect('/wp-admin/options-general.php?page=cf-arbitrary-text&message=package_added', $status = 302);
	}

	/**
	 * Edit package page
	 */
	public static function editPackage() {

		// Get list of snippets.
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
			$cf_snippet = new CF_Snippet();
		}

		$keys = $cf_snippet->get_keys();
		sort($keys);

		$edit = esc_attr($_GET['package']);

		// get existing packages
		$packages = get_option('_cf-arbitrary-text-packages');

		$package = $packages[$edit];

		include('views/edit-package.php');
	}

	/**
	 * Process to edit an existing package
	 */
	public static function editPackageProcess() {

		// Grab existing packages
		$packages = get_option('_cf-arbitrary-text-packages');

		$edit_name = esc_html($_POST['package']['name']);
		$edit_package = $_POST['zones'];
		
		// remove palceholder and old package info
		unset($edit_package['xxx']);
		unset($packages['edit_name']);

		// Sanitize and verify information

		// Report errors

		// Save information
		$packages[$edit_name] = $edit_package;
		update_option('_cf-arbitrary-text-packages', $packages);

		wp_redirect('/wp-admin/options-general.php?page=cf-arbitrary-text&message=package_edited', $status = 302);
	}

	/**
	 * Process ot delete a package
	 */
	public static function deletePackage() {

		$delete = esc_attr($_GET['package']);

		// get existing packages
		$packages = get_option('_cf-arbitrary-text-packages');

		// unset the deleted one
		unset($packages[$delete]);

		// update packages
		update_option('_cf-arbitrary-text-packages', $packages);

		// refresh page
		wp_redirect('/wp-admin/options-general.php?page=cf-arbitrary-text&message=package_deleted', $status = 302);
	}

	/**
	 * Main admin options page and save options logic
	 */
	public static function pluginOptions() {
		// Check permissions.
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		if (false == self::$enabled) {
			include('views/disabled.php');
			return;
		}

		if ($_GET['message']) {
			$message = self::parseMessage($_GET['message']);
		}

		// If a save is requested, save post types
		if ($_POST['cfat_action'] && $_POST['cfat_action'] == 'save_post_types') {
			$options = get_option('_cf-arbitrary-text');
			if (is_array($_POST['cf-arbitrary-text'])) {
				$options['post_type'] = $_POST['cf-arbitrary-text']['post_type'];
				update_option('_cf-arbitrary-text', $options);
			}
			$message = self::parseMessage('post_types_saved');
		}

		// Get list of post types that are normally visible to end users
		$post_types = get_post_types(array(
			'public' => true,
			'publicly_queryable' => true,
			'has_archive' => true,
			'show_ui' => true,
		), 'names');
		$post_types[] = 'page';
		$post_types[] = 'post';
		sort($post_types);

		// Get options
		$options = get_option('_cf-arbitrary-text');

		// Get list of packages 
		$packages = get_option('_cf-arbitrary-text-packages');


		include('views/options.php');
	}
	
	/**
	 * Intelligent filter to go through single posts and insert text zones if enabled
	 */
	public static function insertText($content) {
		if (!is_single() || false == self::$enabled) {
			return $content;
		}
		
		global $post;
		$options = get_post_meta($post->ID, '_cf-arbitrary-text-post', true);

		// Check if the post has text enabled, short circuit if not
		if ($options['enable'] != 1) {
			return $content;
		}

		// Get package information for post
		$packages = get_option('_cf-arbitrary-text-packages');
		$package = $packages[$options['name']];
		unset($packages);

		// Return gracefully if package not found.
		if (!is_array($package)) {
			return $content;
		}

		// Get zone information
		$zones = array();
		foreach ($package as $zone) {
			$zones[$zone['position']] = $zone['snippet'];
		}

		// Find paragraphs
		$paragraphs = explode('</p>', $content);

		// Re-assemble content with
		$paragraph_count = 1;
		$new_content = '';
		foreach ($paragraphs as $paragraph) {
			$new_content .= $paragraph . '</p>';
			if (array_key_exists($paragraph_count, $zones)) {
				$new_content .= $snippet = cfsp_get_content($zones[$paragraph_count] );
			}
			$paragraph_count++;
		}

		// Handle ads that didn't have enough paragraphs, basically put them at the end.
		foreach ($zones as $paragraph_number => $zone) {
			if ($paragraph_number >= $paragraph_count) {
				$new_content .= $snippet = cfsp_get_content($zones[$paragraph_number] );
			}
		}

		return $new_content;
	}

	/**
	 * Add menu pages to Admin
	 */
	public static function pluginSettingsMenu() {
		if (false == self::$enabled) {
			return;
		}
		add_submenu_page( null, 'CF Add Arbitrary Text Package', 'CF Add Arbitrary Text Package', 'manage_options', 'cf-arbitrary-text-add-package', 'cf_arbitrary_text::addPackageHandler' );
		add_options_page( 'CF Arbitrary Text Options', 'CF Arbitrary Text', 'manage_options', 'cf-arbitrary-text', 'cf_arbitrary_text::pluginOptions' );
	}

	/**
	 * Add necessary JS to admin
	 */
	public static function adminEnqueueScripts() {
		wp_enqueue_script('cfat-admin', trailingslashit(CFAT_DIR_URL) . 'js/cf-arbitrary.js', array('jquery'), CFAT_VERSION);
	}

	/**
	 * Handler for Add Package / Edit package pages
	 */
	public static function addPackageHandler() {
		if (!empty($_GET['cfat_action'])) {
			switch ($_GET['cfat_action']) {
				case 'delete':
					self::deletePackage();
					return;

				case 'edit':
					self::editPackage();
					return;

				default:
					//nothing
			}
		}
		if (!empty($_POST['cfat_action'])) {
			switch ($_POST['cfat_action']) {
				case 'add_package':
					self::addPackageProcess();
					return;

				case 'edit_package_process':
					self::editPackageProcess();
					return;

				default:
					//nothing
			}
		}
		self::addPackage();
	}
}

/* Actions and filters */

add_action('admin_menu', 'cf_arbitrary_text::pluginSettingsMenu');
add_action('init', 'cf_arbitrary_text::onInit');
add_action('admin_init', 'cf_arbitrary_text::onAdminInit');
add_action('save_post', 'cf_arbitrary_text::onSavePost', 10, 2);
add_filter('the_content', 'cf_arbitrary_text::insertText', 1000);
