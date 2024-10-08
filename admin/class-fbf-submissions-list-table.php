<?php

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Fbf_Submissions_List_Table extends WP_List_Table
{

    /**
     * Constructor for the list table.
     */
    public function __construct()
    {
        parent::__construct(array(
            'singular' => __('Submission', 'fbf-submissions'),  // Singular label
            'plural' => __('Submissions', 'fbf-submissions'),  // Plural label
            'ajax' => false  // Does this table support ajax?
        ));

        add_action('admin_notices', [$this, 'display_admin_notices']);

    }

    /**
     * Prepare the list of items for displaying.
     */
    public function prepare_items()
    {
        
        $per_page = $this->get_items_per_page('submissions_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items = $this->get_total_items();

        // Handle the search query if it exists
        $search = isset($_REQUEST['s']) ? wp_unslash(trim($_REQUEST['s'])) : '';

        $hidden = get_hidden_columns(get_current_screen());
        // Set column headers
        $this->_column_headers = array(
            $this->get_columns(),
            $hidden,
            $this->get_sortable_columns()
        );

        
        // Process bulk actions if any
        ob_start();
        $this->process_bulk_action();
        ob_clean();
        // Fetch data and set pagination arguments
        $data = $this->fetch_submissions_data($per_page, $current_page, $search);
        $this->items = $data;

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }
    

    /**
     * Fetch data for the table.
     *
     * @param int $per_page Number of items per page.
     * @param int $page_number Current page number.
     * @return array Data for the table.
     */
    public function fetch_submissions_data($per_page, $page_number, $search = '')
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'fbf_submissions';
        $offset = ($page_number - 1) * $per_page;

        // Base query
        $query = "SELECT * FROM $table_name";

        // Search functionality
        if (!empty($search)) {
            $query .= $wpdb->prepare(" WHERE name LIKE %s OR email LIKE %s", '%' . $wpdb->esc_like($search) . '%', '%' . $wpdb->esc_like($search) . '%');
        }

        // Handle sorting
        if (!empty($_REQUEST['orderby'])) {
            $query .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $query .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        }

        // Add pagination to the query
        $query .= " LIMIT $offset, $per_page";

        $results = $wpdb->get_results($query, ARRAY_A);

        return $results;
    }

    /**
     * Get total number of submissions in the database.
     *
     * @return int Total number of items.
     */
    public function get_total_items()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'fbf_submissions'; // Change to your table name
        $query = "SELECT COUNT(*) FROM $table_name";

        return (int) $wpdb->get_var($query);
    }

    /**
     * Define the columns of the table.
     *
     * @return array Columns for the table.
     */
    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', // Checkbox for bulk actions
            'id' => __('ID', 'fbf-submissions'),
            'name' => __('Name', 'fbf-submissions'),
            'email' => __('Email', 'fbf-submissions'),
            'date_submitted' => __('Date Submitted', 'fbf-submissions'),
            'actions' => __('Actions', 'fbf-submissions')
        );

        return $columns;
    }

    /**
     * Define which columns are sortable.
     *
     * @return array Sortable columns.
     */
    protected function get_sortable_columns()
    {
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        $sortable_columns = array(
            'id' => array('id', true),
            'name' => array('name', false),
            'date_submitted' => array('date_submitted', false)
        );

        // Preserve the search query in the sorting links
        foreach ($sortable_columns as $column => $values) {
            $sortable_columns[$column][1] = add_query_arg(array('s' => $search));
        }

        return $sortable_columns;
    }


    public function get_bulk_actions()
    {
        $actions = array(
            'bulk-delete' => __('Delete', 'fbf-submissions')
        );
        return $actions;
    }


    public function process_bulk_action()
    {
        // Detect the bulk action
        $action = $this->current_action();
        // ob_start();
        if ($action === 'delete') {
            // Check for nonce
            $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';

            if (!wp_verify_nonce($nonce, 'bulk-submissions')) {
                $redirect_url = add_query_arg('message', 'error', admin_url('admin.php?page=fbf-submissions'));
                wp_die(__('Nope! Security check failed.', 'fbf-submissions'));
            } else {
                // self::delete_submission(absint($_GET['submission']));
                // ob_clean();
                $redirect_url = add_query_arg('message', 'deleted', admin_url('admin.php?page=fbf-submissions'));
                ob_start();
                wp_redirect($redirect_url);
                ob_clean();
            }
        }

        // Handle the bulk delete action
        if ($action === 'bulk-delete') {
            // Check for nonce
            $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';

            // Verify nonce for security
            if ( !wp_verify_nonce( $nonce, 'bulk-' . $this->_args['plural'] ) ) {
                $redirect_url = add_query_arg('message', 'error', admin_url('admin.php?page=fbf-submissions'));
                wp_die(__('Security check failed.', 'fbf-submissions'));
            }

            // Get submission IDs
            $submission_ids = isset($_REQUEST['submission']) ? array_map('intval', $_REQUEST['submission']) : [];

            // Perform deletion
            foreach ($submission_ids as $id) {
                // $this->delete_submission($id); // Define this function to handle deletion
            }
            $redirect_url = add_query_arg('message', 'bulk-deleted', admin_url('admin.php?page=fbf-submissions'));
            ob_start();
            wp_redirect($redirect_url);
            ob_clean();
        }

    }

    public function display_admin_notices() {
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
    
            if ($message === 'deleted') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Submission deleted successfully.', 'fbf-submissions') . '</p></div>';
            } elseif ($message === 'bulk-deleted') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Submissions deleted successfully.', 'fbf-submissions') . '</p></div>';
            } elseif ($message === 'error') {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('There was an error processing the request.', 'fbf-submissions') . '</p></div>';
            }
        }
    }

    public static function delete_submission($id)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'fbf_submissions';

        $wpdb->delete(
            $table_name,
            ['id' => $id],
            ['%d']
        );
    }


    /**
     * Render the checkbox column.
     *
     * @param array $item The current item.
     * @return string Checkbox HTML.
     */
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="submission[]" value="%s" />',
            $item['id']
        );
    }


    /**
     * Render a column when no specific method exists.
     *
     * @param array $item The current item.
     * @param string $column_name The name of the column.
     * @return mixed Column output.
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'name':
            case 'email':
            case 'date_submitted':
                return $item[$column_name];
            case 'actions':
                return $this->column_actions($item);
            default:
                return print_r($item, true);
        }
    }

    public function column_actions($item)
    {
        $delete_nonce = wp_create_nonce('bulk-submissions');

        // Preserve the search query in the delete link
        $delete_action = sprintf(
            '<a href="?page=%s&action=%s&submission=%s&_wpnonce=%s&s=%s" title="%s">
                <span class="dashicons dashicons-trash"></span>
             </a>',
            esc_attr($_REQUEST['page']),
            'delete',
            absint($item['id']),
            $delete_nonce,
            esc_attr(isset($_REQUEST['s']) ? $_REQUEST['s'] : ''),
            __('Delete', 'fbf-submissions')
        );

        // Placeholder action link with an icon for future use
        $future_action = sprintf(
            '<a href="#" title="%s">
                <span class="dashicons dashicons-admin-generic"></span>
             </a>',
            __('Future Action', 'fbf-submissions')
        );

        // Combine both actions
        $actions = $delete_action . ' ' . $future_action;

        return $actions;
    }


    /**
     * Display the search box.
     *
     * @param string $text The button text.
     * @param string $input_id The input id.
     */
    public function search_box($text, $input_id)
    {
        if (empty($_REQUEST['s']) && !$this->has_items()) {
            return;
        }

        $input_id .= '-search-input';
        $search_query = isset($_REQUEST['s']) ? esc_attr($_REQUEST['s']) : ''; // Get the current search query

        // Add any additional hidden fields necessary to maintain context
        
        foreach ($_REQUEST as $key => $value) {
            if(!$key === 's' || !is_array($value)) {
                echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
            }
        }

        echo '<p class="search-box">';
        echo '<label class="screen-reader-text" for="' . esc_attr($input_id) . '">' . esc_html($text) . ':</label>';
        echo '<input type="search" id="' . esc_attr($input_id) . '" name="s" value="' . $search_query . '" />';
        echo get_submit_button($text, '', '', false, ['id' => 'search-submit']);
        echo '</p>';

        // Display the "Search Results For" message and Back button if there is a search term
        if (!empty($search_query)) {
            echo '<div class="search-results">';
            printf(__('Search results for: %s', 'fbf-submissions'), '<strong>' . esc_html($search_query) . '</strong>');
            echo ' <a href="' . esc_url(remove_query_arg('s')) . '" class="button">' . __('Clear Search', 'fbf-submissions') . '</a>';
            echo '</div>';
        }
    }


    protected function get_table_classes()
    {
        // Preserving the search query in the URL for sorting
        return ['widefat', 'fixed', 'striped', 'fbf-submissions-table'];
    }

    public function get_views()
    {
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        // Add search query parameter to sorting links if it exists
        $views = [
            'all' => sprintf(
                '<a href="%s"%s>%s</a>',
                esc_url(add_query_arg(['s' => $search])),
                empty($search) ? ' class="current"' : '',
                __('All', 'fbf-submissions')
            ),
        ];

        return $views;
    }


    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function no_items()
    {
        _e('No submissions found.', 'fbf-submissions');
    }
}
