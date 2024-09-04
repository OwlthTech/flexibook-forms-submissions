<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://owlth.tech
 * @since      1.0.0
 *
 * @package    Fbf_Submissions
 * @subpackage Fbf_Submissions/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fbf_Submissions
 * @subpackage Fbf_Submissions/admin
 * @author     Owlth Tech <owlthtech@gmail.com>
 */
// Instantiate the list table class
require_once plugin_dir_path(__FILE__) . 'class-fbf-submissions-list-table.php';

class Fbf_Submissions_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		// Add screen options
		add_action('admin_menu', [$this, 'add_menu_items']);
		add_action("wp_loaded", array($this, 'clean_up_admin_url'));
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fbf_Submissions_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fbf_Submissions_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/fbf-submissions-admin.css', array(), $this->version, 'all');

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fbf_Submissions_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fbf_Submissions_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/fbf-submissions-admin.js', array('jquery'), $this->version, false);

	}

	public function add_menu_items()
	{
		global $fbf_menu_hook;

		// Add menu item
		$fbf_menu_hook = add_menu_page(
			__('FBF Submissions', 'fbf-submissions'),
			__('FBF Submissions', 'fbf-submissions'),
			'manage_options',
			'fbf-submissions',
			array($this, 'render_list_page'),
			'dashicons-email'
		);

		// Add the screen option to menu item ($fbf_menu_hook) page
		add_action("load-$fbf_menu_hook", array($this, 'add_screen_options'));

	}

	/**
	 * Add screen options to the plugin page.
	 */
	public function add_screen_options()
	{
		global $list_table;

		$screen = get_current_screen();
		if (!is_object($screen) || $screen->id !== 'toplevel_page_fbf-submissions') {
			return;
		}

		$args = [
			'label' => __('Submissions per page', 'flexibook-forms'),
			'default' => 5,
			'option' => 'submissions_per_page'
		];
		add_screen_option('per_page', $args);

		$list_table = new Fbf_Submissions_List_Table();

	}

	/**
	 * Render the list page.
	 */
	public function render_list_page()
	{
		// $this->clean_up_admin_url();
		global $list_table;
		// Instantiate the list table class
		$list_table = new Fbf_Submissions_List_Table();
		echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">' . __('Form Submissions', 'flexibook-forms') . '</h1>';
		echo "<form id='submissions-form' method='get'>";
		// wp_nonce_field('bulk-submissions', '_wpnonce', false);
		$list_table->prepare_items();
		$list_table->search_box('search', 'submissions');
		$list_table->display();
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Clean up URL parameters after actions.
	 */
	public function clean_up_admin_url()
	{
		// Check if we are on the specific admin page
		if (is_admin() && isset($_GET['page']) && $_GET['page'] === 'fbf-submissions') {
			// Prepare the base URL without unwanted parameters
			$redirect_url = $this->prepare_redirect_url(admin_url('admin.php?page=fbf-submissions'));

			// Check if the current URL contains any of the unwanted parameters
			if (isset($_GET['_wp_http_referer']) || isset($_GET['submission']) || isset($_GET['action2'])) {
				// Only redirect if unwanted parameters are found
				wp_redirect($redirect_url);
				// Stop further execution to prevent loops
				return;
			}
		}
	}

	/**
	 * Prepare redirect URL with existing query parameters.
	 */
	public function prepare_redirect_url($base_url)
	{
		// Remove specific query parameters if they exist
		$base_url = remove_query_arg(['_wp_http_referer', 'action2'], $base_url);

		if (!isset($_GET['page'])) {
			$base_url = add_query_arg('page', sanitize_text_field($_GET['fbf-submissions']), $base_url);
		}

		// Conditionally add existing parameters like 'orderby', 'order', 's' if they exist
		if (isset($_GET['orderby'])) {
			$base_url = add_query_arg('orderby', sanitize_text_field($_GET['orderby']), $base_url);
		}

		if (isset($_GET['order'])) {
			$base_url = add_query_arg('order', sanitize_text_field($_GET['order']), $base_url);
		}

		if (isset($_GET['s']) && !empty(trim($_GET['s']))) {
			$base_url = add_query_arg('s', sanitize_text_field($_GET['s']), $base_url);
		}

		return $base_url;
	}

}