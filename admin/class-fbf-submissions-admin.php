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
		if ( ! is_object( $screen ) || $screen->id !== 'toplevel_page_fbf-submissions' ) {
		    return;
		}
    
		$args = [
		    'label'   => __( 'Submissions per page', 'flexibook-forms' ),
		    'default' => 5,
		    'option'  => 'submissions_per_page'
		];
		add_screen_option( 'per_page', $args );

		
		$list_table = new Fbf_Submissions_List_Table();

	}

	public function set_screen_options($status, $option, $value){
		// var_dump($value);
		//exit;
		return $value;
	}
	/**
	 * Render the list page.
	 */
	public function render_list_page()
	{
		global $list_table;
		// Instantiate the list table class
		$list_table = new Fbf_Submissions_List_Table();
		
		echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">' . __('Form Submissions', 'flexibook-forms') . '</h1>';
		echo "<form id='submissions-form' method='get'>";
		// Add a nonce for security
		wp_nonce_field('bulk-submissions');
		$list_table->prepare_items();
		// Table process
		$list_table->search_box('search', 'submissions');
		$list_table->display();
		echo '</form>';
		echo '</div>';
	}
}