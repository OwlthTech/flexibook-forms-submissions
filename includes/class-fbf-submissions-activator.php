<?php

/**
 * Fired during plugin activation
 *
 * @link       https://owlth.tech
 * @since      1.0.0
 *
 * @package    Fbf_Submissions
 * @subpackage Fbf_Submissions/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Fbf_Submissions
 * @subpackage Fbf_Submissions/includes
 * @author     Owlth Tech <owlthtech@gmail.com>
 */
class Fbf_Submissions_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::create_submissions_table();
	}

	/**
     * Creates the database table for submissions.
     */
    private static function create_submissions_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'fbf_submissions'; // Define your table name
        $charset_collate = $wpdb->get_charset_collate();

        // SQL statement to create the table
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            date_submitted datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

}
