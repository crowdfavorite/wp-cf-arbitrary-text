<?php
/*
Plugin Name: CF Abritrary Text
Plugin URI: http://crowdfavorite.com
Description: Insert arbitrary text (usually ads) at specific places in stories
Version: 0.62
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

define('CFAT_VERSION', '0.62');
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
			add_action('admin_head', 'cf_arbitrary_text::adminEnqueueStyles');
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
			'invalid_package' => __('The package settings given were invalid.'),
			'auto_enable_saved' => __('Post type auto-enable was successfully saved.'),
			'auto_enable_delete' => __('Post type auto-enable was successfully deleted.'),
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
		foreach ((array)$options['post_type'] as $post_type => $null) {
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

		$enabled = !empty($options) && !empty($options['enable']);

		// Enable checkbox is unchecked
		if (!$enabled) {
			
			// Check if auto-enable is set for this post type
			$auto_options = get_option('_cf-arbitrary-text-auto-enable');
			if (isset($auto_options[$post->post_type]) && !empty($auto_options[$post->post_type]['package'])) {
				$auto_package = $auto_options[$post->post_type]['package'];
				$auto_disabled = !empty($options) && !empty($options['auto-disable']);
			}
		}

		include('views/admin-box.php');
	}
		
	/**
	 * Add proper data to saved posts
	 */		
	public static function onSavePost($post_id, $post) {
		if (false == self::$enabled || empty($post)) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if ($post->post_type == 'revision') {
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

	public static function getAutoEnablePackage($post) {

		$post = &get_post($post);

		$options = get_option('_cf-arbitrary-text-auto-enable');
		if (isset($options[$post->post_type]) && !empty($options[$post->post_type]['package'])) {
			return $options[$post->post_type]['package'];
		}

		return false;
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
		$packages = self::getPackages();

		$package_options = self::getPackageOptions($edit);

		$package = $packages[$edit];

		$align_options = self::getSnippetAlignmentOptions();

		include('views/edit-package.php');
	}

	public static function getSnippetAlignmentOptions() {
		return apply_filters('cf-arbitrary-align-options', array(
			'left' => 'Left',
			'right' => 'Right',
			'center' => 'Center',
			'float-left' => 'Float Left',
			'float-right' => 'Float Right',
		));
	}

	/**
	 * Update package data on save
	 */
	public static function updatePackagesProcess($is_new = false) {
		// Validate new package
		if (
			   !isset($_POST['package'])
			|| empty($_POST['package'])
			|| !isset($_POST['package']['name'])
			|| empty($_POST['package']['name'])
			|| !isset($_POST['zones'])
			|| empty($_POST['zones'])
			|| !is_array($_POST['zones'])
			|| count($_POST['zones']) < 2
		) {
			wp_redirect('/wp-admin/options-general.php?page=cf-arbitrary-text&message=invalid_package');
			exit();
		}

		// Remove Placeholder
		$new_package = $_POST['zones'];
		unset($new_package['xxx']);

		// Sanitize text input data
		foreach ($new_package as $id => &$package) {
			$package['position'] = intval($package['position']);
			if ($package['position'] < 1) {
				$package['position'] = 1;
			}
		}

		// Grab existing packages
		$packages = get_option('_cf-arbitrary-text-packages');
		$new_package_name = esc_html($_POST['package']['name']);
		$unique_package_name = $new_package_name;


		// If we're changing the name, delete the old one and treat this as creating a new package
		if (
			   isset($_POST['package']['orig_name'])
			&& $_POST['package']['name'] != $_POST['package']['orig_name']
			&& !empty($packages[$_POST['package']['orig_name']])
		) {
			unset($packages[$_POST['package']['orig_name']]);
			$is_new = true;
		}

		// If the package is new, instead of replacing an existing one, ensure the name is unique
		if ($is_new) {
			$package_iterator = 1;
			while (isset($packages[$unique_package_name])) {
				++$package_iterator;
				$unique_package_name = $new_package_name.'-'.$package_iterator;
			}
		}
		
		// Save information
		$packages[$unique_package_name] = $new_package;
		update_option('_cf-arbitrary-text-packages', $packages);

		// Update package options
		$package_options = self::getPackageOptions();
		if (isset($_POST['package']['paragraph_limit_display'])) {
			$package_options[$unique_package_name]['paragraph_limit_display'] = '1';
		}
		else {
			unset($package_options[$unique_package_name]['paragraph_limit_display']);
		}
		update_option('_cf-arbitrary-text-package-options', $package_options);

		$url = admin_url('options-general.php?page=cf-arbitrary-text&message=package_added');

		wp_redirect($url);
		echo '<script>window.location="' . $url . '";</script>';
		exit();
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

		$url = admin_url('options-general.php?page=cf-arbitrary-text&message=package_deleted');
		wp_redirect($url, $status = 302);
		echo '<script>window.location="' . $url . '";</script>';
		exit();
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
		if (isset($_REQUEST['cfat_action'])) {
			if ($_POST['cfat_action'] == 'save_post_types') {
				$options = get_option('_cf-arbitrary-text');
				if (is_array($_POST['cf-arbitrary-text'])) {
					$options['post_type'] = $_POST['cf-arbitrary-text']['post_type'];
					update_option('_cf-arbitrary-text', $options);
				}
				$message = self::parseMessage('post_types_saved');
			}
			else if ($_POST['cfat_action'] == 'save_post_types_auto_enable') {
				$options = get_option('_cf-arbitrary-text-auto-enable');
				if (is_array($_POST['cf-arbitrary-text'])) {

					if (empty($options)) {
						$options = array();
					}

					$item = $_POST['cf-arbitrary-text']['post_type_auto_enable'];

					if (!empty($item['auto_post_type']) && !empty($item['package'])) {

						$options[$item['auto_post_type']] = array(
							'post_type' => $item['auto_post_type'],
							'package' => $item['package'],
						);

						ksort($options);

						update_option('_cf-arbitrary-text-auto-enable', $options);
					}
				}
				$message = self::parseMessage('auto_enable_saved');
			}
			else if ($_REQUEST['cfat_action'] == 'delete_auto_enable') {
				$options = get_option('_cf-arbitrary-text-auto-enable');
				if (!empty($options) && !empty($_REQUEST['package']) && !empty($_REQUEST['auto_post_type'])) {

					if (isset($options[$_REQUEST['auto_post_type']])) {
						unset($options[$_REQUEST['auto_post_type']]);
					}

					update_option('_cf-arbitrary-text-auto-enable', $options);
				}
				$message = self::parseMessage('auto_enable_deleted');
			}
		}

		// Get list of post types that are normally visible to end users
		$post_types_query = get_post_types(array(
			'public' => true,
			'publicly_queryable' => true,
			'has_archive' => true,
			'show_ui' => true,
		), 'objects');


		$post_types = array();

		foreach ($post_types_query as $post_type) {
			$post_types[$post_type->name] = $post_type->label;
		}

		$post_types['page'] = 'Page';
		$post_types['post'] = 'Post';
		asort($post_types);

		// Get options
		$options = get_option('_cf-arbitrary-text');

		// Get list of packages 
		$packages = self::getPackages();

		// Get auto-enabled posts
		$post_auto_enable = self::getAutoEnableOptions();

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
		
		$options = self::getPostOptions($post->ID);

		// Check if auto-enable is set
		$enabled = !empty($options['enable']);

		if (isset($options['name'])) {
			$package_name = $options['name'];
		}

		// Check if the post has text enabled, short circuit if not
		if ((!$enabled || empty($package_name))) {
			if (!empty($options['auto-disable'])) {
				return $content;
			}

			// Check if auto-enable is set for this post type
			$auto_options = self::getAutoEnableOptions();
			if (!isset($auto_options[$post->post_type]) || empty($auto_options[$post->post_type]['package'])) {
				// Return if not enabled and no auto-enable package set
				return $content;
			}

			$package_name = $auto_options[$post->post_type]['package'];
			$enabled = true;
		}

		if (empty($package_name)) {
			return $content;
		}

		$package_name = str_replace('&', '&amp;', $package_name);

		// Get package information for post
		$packages = self::getPackages();
		$package = $packages[$package_name];

		// Return gracefully if package not found.
		if (!is_array($package)) {
			if (!empty($options['auto-disable'])) {
				return $content;
			}

			// Check if auto enable package was searched
			if (!isset($auto_options)) {
				// Check if auto-enable is set for this post type
				$auto_options = self::getAutoEnableOptions();
				if (!isset($auto_options[$post->post_type]) || empty($auto_options[$post->post_type]['package'])) {
					// Return if not enabled and no auto-enable package set
					return $content;
				}

				$package_name = $auto_options[$post->post_type]['package'];
				$package = $packages[$package_name];
			}

			if (!is_array($package)) {
				return $content;
			}
		}
		
		$paragraph_delimiter = apply_filters('cf-arbitrary-p-delimiter', '</p>');

		// Find paragraphs
		$paragraphs = apply_filters('cf-arbitrary-paragraphs', explode($paragraph_delimiter, $content), $content, $paragraph_delimiter);

		// Re-assemble content with
		$paragraph_count = 1;
		$new_content = '';
		foreach ((array)$paragraphs as $paragraph) {
			if (empty($paragraph) || $paragraph == PHP_EOL) {
				continue;
			}
			$new_content .= $paragraph . $paragraph_delimiter;
			if ($paragraph == $paragraph_delimiter) {
				continue;
			}
			foreach ($package as $zone) {
				if ($zone['position'] == $paragraph_count) {

					$wrap_snippet = !empty($zone['margin']) || !empty($zone['align']);

					if ($wrap_snippet) {
						$new_content .= '<font class="cfat-snippet cfat-snippet-' . $paragraph_count . ' cfat-snippet-' . $zone['snippet'];

						if (!empty($zone['align'])) {
							$new_content .= ' cfat-align-' . $zone['align'];
						}

						$new_content .= '" style="display:block;';

						if (!empty($zone['margin']) && is_numeric($zone['margin'])) {
							$new_content .= ' margin:' . $zone['margin'];

							if (is_numeric($zone['margin'])) {
								$new_content .= 'px';
							}
						}

						$new_content .= '">';
					}

					$new_content .= cfsp_get_content( $zone['snippet'] );

					if ($wrap_snippet) {
						$new_content .= '</font>';
					}
				}
			}
			$paragraph_count++;
		}

		$package_options = self::getPackageOptions($package_name);

		if (empty($package_options) || empty($package_options['paragraph_limit_display'])) {
			// Handle ads that didn't have enough paragraphs, basically put them at the end.
			foreach ((array)$package as $zone) {
				if ($zone['position'] >= $paragraph_count) {
					$new_content .= cfsp_get_content( $zone['snippet'] );
				}
			}
		}

		return $new_content;
	}

	/**
	 * Get all packages available.
	 * @staticvar array $packages
	 * @return array
	 */
	public static function getPackages() {

		$packages = get_option('_cf-arbitrary-text-packages');

		if (empty($packages)) {
			$packages = array();
		}

		return $packages;
	}

	public static function getPackageOptions($package = null) {
		$packageOptions = get_option('_cf-arbitrary-text-package-options');

		if (empty($packageOptions)) {
			$packageOptions = array();
		}

		if (!empty($package)) {

			if (!empty($packageOptions[$package])) {
				return $packageOptions[$package];
			}

			return array();
		}
		
		return $packageOptions;
	}

	/**
	 * Get global plugin options
	 * @return array
	 */
	public static function getOptions() {
		$options = get_option('_cf-arbitrary-text');

		if (empty($options)) {
			$options = array();
		}

		return $options;
	}

	/**
	 * Get options for a specified post
	 * @param int $post_id
	 * @return array
	 */
	public static function getPostOptions($post_id) {

		if (empty($post_id)) {
			$post_id = get_the_ID();
		}

		$options = get_post_meta($post_id, '_cf-arbitrary-text-post', true);

		if (empty($options)) {
			$options = array();
		}

		return $options;
	}

	/**
	 * Get auto-enable post types
	 * @return array
	 */
	public static function getAutoEnableOptions() {
		$options = get_option('_cf-arbitrary-text-auto-enable');

		if (empty($options)) {
			$options = array();
		}

		return $options;
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
	 * Add necessary CSS to admin
	 */
	public static function adminEnqueueStyles() {
		wp_enqueue_style('cfat-admin', trailingslashit(CFAT_DIR_URL) . 'css/cf-arbitrary.css', array(), CFAT_VERSION);
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
					self::updatePackagesProcess($is_new=true);
					return;

				case 'edit_package_process':
					self::updatePackagesProcess($is_new=false);
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
add_filter('the_content', 'cf_arbitrary_text::insertText', 10);
