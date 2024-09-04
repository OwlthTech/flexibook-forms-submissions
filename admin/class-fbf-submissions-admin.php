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
// require_once plugin_dir_path(__FILE__) . 'class-fbf-submissions-list-table.php';
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

	/**
	 * Register the custom post type for submissions.
	 */
	public function fbf_register_submission_post_type()
	{
		$labels = array(
			'name' => _x('Submissions', 'Post Type General Name', 'fbf-submissions'),
			'singular_name' => _x('Submission', 'Post Type Singular Name', 'fbf-submissions'),
			'menu_name' => __('Submissions', 'fbf-submissions'),
			'name_admin_bar' => __('Submission', 'fbf-submissions'),
			'edit_item' => __('View Submission', 'fbf-submissions'), // "Edit" changed to "View"
			'view_item' => __('View Submission', 'fbf-submissions'),
			'search_items' => __('Search Submissions', 'fbf-submissions'),
			'not_found' => __('No submissions found', 'fbf-submissions'),
			'not_found_in_trash' => __('No submissions found in Trash', 'fbf-submissions'),
		);

		$args = array(
			'label' => __('Submissions', 'fbf-submissions'),
			'labels' => $labels,
			'supports' => array('custom-fields', 'comments'), // Support custom fields and comments for notes
			'public' => false, // Make it private
			'show_ui' => true,
			'show_in_menu' => true,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'capability_type' => 'post',
			'capabilities' => array(
				'edit_post' => 'manage_options',
				'edit_posts' => 'manage_options',
				'edit_others_posts' => 'manage_options',
				'publish_posts' => 'manage_options',
				'read_post' => 'manage_options',
				'read_private_posts' => 'manage_options',
				'delete_post' => false,
			),
			'menu_icon' => 'dashicons-email',
		);

		register_post_type('submissions', $args);
	}

	/**
	 * Add menu items for the plugin.
	 */
	public function add_menu_items()
	{
		$hook = add_menu_page(
			__('Form Submissions', 'fbf-submissions'),
			__('Form Submissions', 'fbf-submissions'),
			'manage_options',
			'fbf-submissions',
			[$this, 'render_list_page'],
			'dashicons-email'
		);

		// Add the screen option to menu item page
		add_action("load-$hook", [$this, 'add_screen_options']);
	}

	/**
	 * Render the list page.
	 */
	public function render_list_page()
	{
		// Handle the view action and mark as read
		// if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['post'])) {
		// 	$post_id = intval($_GET['post']);
		// 	update_post_meta($post_id, '_submission_status', 'read'); // Mark as read when viewed
		// }

		
		$list_table = new Fbf_Submissions_List_Table();
		
		echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">' . __('Form Submissions', 'flexibook-forms') . '</h1>';
		echo "<form id='submissions-form' method='get'>";
		wp_nonce_field('bulk-submissions', '_wpnonce', false);
		$list_table->prepare_items();
		$list_table->search_box('search', 'submissions');
		$list_table->display();
		echo '</form>';
		echo '</div>';


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

}