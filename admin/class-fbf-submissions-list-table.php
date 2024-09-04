<?php

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Fbf_Submissions_List_Table extends WP_List_Table
{

    public function __construct()
    {
        parent::__construct([
            'singular' => __('Submission', 'fbf-submissions'),
            'plural' => __('Submissions', 'fbf-submissions'),
            'ajax' => false
        ]);

        // Register screen options
        // add_filter('manage_edit-submissions_columns', [$this, 'get_columns']);
        // add_filter('manage_submissions_posts_custom_column', [$this, 'column_default'], 10, 2);
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

        // Set column headers
        $hidden = get_hidden_columns(get_current_screen());
        $this->_column_headers = [$this->get_columns(), $hidden, $this->get_sortable_columns()];

        // Process bulk actions
        $this->process_bulk_action();

        // Fetch data
        ob_start();
        $data = $this->fetch_submissions_data($per_page, $current_page);
        ob_end_clean();
        $this->items = $data;

        // Set pagination arguments
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ]);
    }

    /**
     * Bulk actions.
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'bulk-delete' => __('Delete all', 'fbf-submissions'),
            'bulk-read' => __('Mark as read', 'fbf-submissions'),
            'bulk-unread' => __('Mark as unread', 'fbf-submissions')
        );
        return $actions;
    }


    /**
     * Display the search box.
     */
    public function search_box($text, $input_id)
    {
        if (empty($_REQUEST['s']) && !$this->has_items()) {
            return;
        }

        foreach ($_REQUEST as $key => $value) {
            if(!$key === 's' || !is_array($value)) {
                echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
            }
        }

        $input_id = $input_id . '-search-input';

        if (!empty($_REQUEST['orderby'])) {
            if (is_array($_REQUEST['orderby'])) {
                foreach ($_REQUEST['orderby'] as $key => $value) {
                    echo '<input type="hidden" name="orderby[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" />';
                }
            } else {
                echo '<input type="hidden" name="orderby" value="' . esc_attr($_REQUEST['orderby']) . '" />';
            }
        }
        if (!empty($_REQUEST['order'])) {
            echo '<input type="hidden" name="order" value="' . esc_attr($_REQUEST['order']) . '" />';
        }
        if (!empty($_REQUEST['post_mime_type'])) {
            echo '<input type="hidden" name="post_mime_type" value="' . esc_attr($_REQUEST['post_mime_type']) . '" />';
        }
        if (!empty($_REQUEST['detached'])) {
            echo '<input type="hidden" name="detached" value="' . esc_attr($_REQUEST['detached']) . '" />';
        }
        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo esc_attr($input_id); ?>"><?php echo $text; ?>:</label>
            <input type="search" id="<?php echo esc_attr($input_id); ?>" name="s" value="<?php _admin_search_query(); ?>" />
            <?php submit_button($text, '', '', false, array('id' => 'search-submit')); ?>
        </p>
        <?php
    }


    /**
     * Fetch submissions data using WP_Query.
     */
    public function fetch_submissions_data($per_page, $page_number)
    {
        $search = (isset($_REQUEST['s'])) ? sanitize_text_field($_REQUEST['s']) : '';

        $args = [
            'post_type' => 'submissions',
            'posts_per_page' => $per_page,
            'paged' => $page_number,
        ];

        // If searching, add search query
        if (!empty($search)) {
            $args['s'] = $search;
        }

        $query = new WP_Query($args);
        return $query->posts;
    }

    /**
     * Get the total number of submissions.
     */
    public function get_total_items()
    {
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        $args = [
            'post_type' => 'submissions',
            'posts_per_page' => -1,
        ];

        // If searching, add search query
        if (!empty($search)) {
            $args['s'] = $search;
        }
        ob_start();
        $query = new WP_Query($args);
        ob_end_clean();
        return $query->found_posts;
    }

    /**
     * Define the columns of the table.
     */
    public function get_columns()
    {
        $columns = [
            'cb' => '<input type="checkbox" />',
            'name' => __('Name', 'fbf-submissions'), // Changed from 'Title' to 'Name'
            'email' => __('Email', 'fbf-submissions'),
            'phone' => __('Phone', 'fbf-submissions'),
            'company' => __('Company', 'fbf-submissions'),
            'country' => __('Country', 'fbf-submissions'),
            'message' => __('Message', 'fbf-submissions'),
            'date_submitted' => __('Date Submitted', 'fbf-submissions'),
            // 'status'         => __('Status', 'fbf-submissions'), // New column for read/unread status
            'actions' => __('Actions', 'fbf-submissions'),
        ];

        return $columns;
    }

    /**
     * Define which columns are sortable.
     */
    protected function get_sortable_columns()
    {
        return [
            'name' => ['name', true],
            'email' => ['email', false],
            'date' => ['date', false],
        ];
    }

    /**
     * Render the custom columns.
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'name':
                return esc_html(get_the_title($item->ID));
            case 'email':
            case 'phone':
            case 'company':
            case 'country':
            case 'message':
                return esc_html(get_post_meta($item->ID, $column_name, true));
            case 'date_submitted':
                return esc_html(get_the_date('Y-m-d H:i:s', $item->ID));
            // case 'status':
            //     return $this->column_status($item);
            case 'actions':
                return $this->column_actions($item);
            default:
                return print_r($item, true); // Debugging
        }
    }

    /**
     * Render the checkbox column.
     */
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="post[]" value="%s" />',
            $item->ID
        );
    }

    /**
     * Render the name column.
     */
    public function column_name($item)
    {
        $title = '<strong>' . esc_html(get_the_title($item->ID)) . '</strong>';

        $view_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(add_query_arg(['post' => $item->ID, 'action' => 'view'], admin_url('admin.php?page=fbf-submissions'))),
            __('View Entry', 'fbf-submissions')
        );

        $actions = [
            'view' => $view_link
        ];

        return $title . $this->row_actions($actions);
    }

    /**
     * Render the status column.
     */
    public function column_status($item)
    {
        $status = get_post_meta($item->ID, '_submission_status', true);
        $status = $status ? $status : 'unread';
        $toggle_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(add_query_arg(['post' => $item->ID, 'action' => 'toggle_status'], admin_url('admin.php?page=fbf-submissions'))),
            ucfirst($status)
        );

        return $toggle_link;
    }

    /**
     * Render the actions column.
     */
    public function column_actions($item)
    {
        $view_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(add_query_arg(['post' => $item->ID, 'action' => 'view'], $_SERVER['REQUEST_URI'])),
            __('View Entry', 'fbf-submissions')
        );

        $status = get_post_meta($item->ID, '_submission_status', true);
        $status = $status ? $status : 'unread';
        $status_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(add_query_arg(['post' => $item->ID, 'action' => 'toggle_status'], $_SERVER['REQUEST_URI'])),
            ucfirst($status)
        );

        // Combine both actions
        $actions = $view_link . ' | ' . $status_link;
        return $actions;
    }


    /**
     * Process bulk actions.
     */
    public function process_bulk_action() {
        $action      = $this->current_action();
        // $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
        // if($action = $this->current_action() && !wp_verify_nonce( $nonce, 'bulk-' . $this->_args['plural'])) {
        //     wp_die(__('Security check failed.', 'fbf-submissions'));
        // }
        $request_ids = isset( $_REQUEST['post'] ) ? wp_parse_id_list( wp_unslash( $_REQUEST['post'] ) ) : array();
    
        if ( empty( $request_ids ) ) {
            return;
        }
    
        $count    = 0;
        $failures = 0;
    
        check_admin_referer( 'bulk-' . $this->_args['plural'] );
    
        switch ( $action ) {
            case 'resend':
                foreach ( $request_ids as $request_id ) {
                    $resend = _wp_privacy_resend_request( $request_id );
    
                    if ( $resend && ! is_wp_error( $resend ) ) {
                        ++$count;
                    } else {
                        ++$failures;
                    }
                }
    
                if ( $failures ) {
                    add_settings_error(
                        'bulk_action',
                        'bulk_action',
                        sprintf(
                            /* translators: %d: Number of requests. */
                            _n(
                                '%d confirmation request failed to resend.',
                                '%d confirmation requests failed to resend.',
                                $failures
                            ),
                            $failures
                        ),
                        'error'
                    );
                }
    
                if ( $count ) {
                    add_settings_error(
                        'bulk_action',
                        'bulk_action',
                        sprintf(
                            /* translators: %d: Number of requests. */
                            _n(
                                '%d confirmation request re-sent successfully.',
                                '%d confirmation requests re-sent successfully.',
                                $count
                            ),
                            $count
                        ),
                        'success'
                    );
                }
    
                break;
    
            case 'complete':
                foreach ( $request_ids as $request_id ) {
                    $result = _wp_privacy_completed_request( $request_id );
    
                    if ( $result && ! is_wp_error( $result ) ) {
                        ++$count;
                    }
                }
    
                add_settings_error(
                    'bulk_action',
                    'bulk_action',
                    sprintf(
                        /* translators: %d: Number of requests. */
                        _n(
                            '%d request marked as complete.',
                            '%d requests marked as complete.',
                            $count
                        ),
                        $count
                    ),
                    'success'
                );
                break;
    
            case 'bulk-delete':
                foreach ( $request_ids as $request_id ) {
                    if ( wp_delete_post( $request_id, true ) ) {
                        ++$count;
                    } else {
                        ++$failures;
                    }
                }
    
                if ( $failures ) {
                    add_settings_error(
                        'bulk_action',
                        'bulk_action',
                        sprintf(
                            /* translators: %d: Number of requests. */
                            _n(
                                '%d request failed to delete.',
                                '%d requests failed to delete.',
                                $failures
                            ),
                            $failures
                        ),
                        'error'
                    );
                }
    
                if ( $count ) {
                    add_settings_error(
                        'bulk_action',
                        'bulk_action',
                        sprintf(
                            /* translators: %d: Number of requests. */
                            _n(
                                '%d request deleted successfully.',
                                '%d requests deleted successfully.',
                                $count
                            ),
                            $count
                        ),
                        'success'
                    );
                }
    
                break;
        }
    }
    
}
