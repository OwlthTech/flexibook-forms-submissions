<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://owlth.tech
 * @since      1.0.0
 *
 * @package    Fbf_Submissions
 * @subpackage Fbf_Submissions/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php 
    // Access the global $list_table variable
    global $list_table;
    $list_table = new Fbf_Submissions_List_Table();


?>

<div class="wrap">
    <h2><?php _e( 'Custom List Table Example', 'fbf-submissions' ); ?></h2>
    <form method="get">
        <!-- Preserve the current page parameter -->
        <input type="hidden" name="page" value="fbf-submissions" />
        <!-- Include the nonce field for security -->
        <?php wp_nonce_field( 'bulk-submissions' ); ?>

        <?php 
            
            $list_table->prepare_items();
            
            // Display the search box with the correct form method
            $list_table->search_box( __( 'Search Submissions', 'fbf-submissions' ), 'submission' );

            // Display the table with bulk actions
            $list_table->display();
        ?>
    </form>
</div>



