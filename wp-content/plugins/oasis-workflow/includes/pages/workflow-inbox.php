<?php
global $ow_custom_statuses;

// sanitize the data
$selected_user = (isset( $_GET['user'] ) && sanitize_text_field( $_GET["user"] )) ? intval( sanitize_text_field( $_GET["user"] ) ) : get_current_user_id();
$page_number = (isset( $_GET['paged'] ) && sanitize_text_field( $_GET["paged"] )) ? intval( sanitize_text_field( $_GET["paged"] ) ) : 1;

$ow_inbox_service = new OW_Inbox_Service();
$ow_process_flow = new OW_Process_Flow();
$ow_workflow_service = new OW_Workflow_Service();

// get assigned posts for selected user
$inbox_items = $ow_process_flow->get_assigned_post( null, $selected_user );
$count_posts = count( $inbox_items );
$per_page = OASIS_PER_PAGE;

// TODO: see how to better look into the capabilities of current user
$is_post_editable = current_user_can( 'edit_others_posts' );
$current_user_role = OW_Utility::instance()->get_current_user_role();
$current_user_id = get_current_user_id();

// get custom terminology
$workflow_terminology_options = OW_Utility::instance()->get_custom_workflow_terminology();
$sign_off_label = $workflow_terminology_options['signOffText'];
$abort_workflow_label = $workflow_terminology_options['abortWorkflowText'];
?>
<div class="wrap">
    <div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
    <h1><?php _e( 'Inbox', 'oasisworkflow' ); ?></h1>
    <div id="workflow-inbox">
        <div class="tablenav top">

            <!-- Bulk Actions Start -->
            <?php do_action( 'owf_bulk_actions_section' ); ?>
            <!-- Bulk Actions End -->

            <input type="hidden" id="hidden_task_user" value="<?php echo esc_attr( $selected_user ); ?>" />
            <?php if( current_user_can( 'ow_view_others_inbox' ) ) { ?>
               <div class="alignleft actions">
                   <select id="inbox_filter">
                       <option value=<?php echo $current_user_id; ?> selected="selected"><?php echo __( "View inbox of ", "oasisworkflow" ) ?></option>
                       <?php
                       $assigned_users = $ow_process_flow->get_assigned_users();
                       if( $assigned_users ) {
                          foreach ( $assigned_users as $assigned_user ) {
                             if( ( isset( $_GET['user'] ) && $_GET["user"] == $assigned_user->ID ) )
                                echo "<option value={$assigned_user->ID} selected>{$assigned_user->display_name}</option>";
                             else
                                echo "<option value={$assigned_user->ID}>{$assigned_user->display_name}</option>";
                          }
                       }
                       ?>
                   </select>

                   <a href="javascript:window.open('<?php echo admin_url( 'admin.php?page=oasiswf-inbox&user=' ) ?>' + jQuery('#inbox_filter').val(), '_self')">
                       <input type="button" class="button-secondary action" value="<?php echo __( "Show", "oasisworkflow" ); ?>" />
                   </a>
               </div>
            <?php } ?>
            <ul class="subsubsub"></ul>
            <div class="tablenav-pages">
                <?php OW_Utility::instance()->get_page_link( $count_posts, $page_number, $per_page ); ?>
            </div>
        </div>
        <table class="wp-list-table widefat fixed posts" cellspacing="0" border=0>
            <thead>
                <?php $ow_inbox_service->get_table_header(); ?>
            </thead>
            <tfoot>
                <?php $ow_inbox_service->get_table_header(); ?>
            </tfoot>
            <tbody id="coupon-list">
                <?php
                $wf_process_status = get_site_option( "oasiswf_status" );
                $space = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                ob_start();
                if( $inbox_items ) {
                   $count = 0;
                   $start = ( $page_number - 1 ) * $per_page;
                   $end = $start + $per_page;
                   foreach ( $inbox_items as $inbox_item ) {
                      if( $count >= $end )
                         break;
                      if( $count >= $start ) {

                         $post_id = $inbox_item->post_id;
                         $workflow_id = $inbox_item->ID;

                         $post = get_post( $post_id );

                         $cat_name = OW_Utility::instance()->get_post_categories( $post_id );
                         $user = get_userdata( $post->post_author );
                         $stepId = $inbox_item->step_id;
                         if( $stepId <= 0 || $stepId == "" ) {
                            $stepId = $inbox_item->review_step_id;
                         }
                         $step = $ow_workflow_service->get_step_by_id( $stepId );
                         $workflow = $ow_workflow_service->get_workflow_by_id( $step->workflow_id );

                         $needs_to_be_claimed = $ow_process_flow->check_for_claim( $workflow_id );

                         $original_post_id = get_post_meta( $post_id, 'oasis_original', true );
                         /* Check due date and make post item background color in red to notify the admin */
                         $current_date = Date( " F j, Y " );
                         $due_date = OW_Utility::instance()->format_date_for_display( $inbox_item->due_date );

                         $past_due_date_row_class = $past_due_date_field_class = '';
                         if( $due_date != "" && strtotime( $due_date ) < strtotime( $current_date ) ) {
                            $past_due_date_row_class = 'past-due-date-row';
                            $past_due_date_field_class = 'past-due-date-field';
                         }
                         ?>
                         <tr id="post-<?php echo $post_id; ?>" class="post-<?php echo $post_id; ?> post type-post <?php echo $past_due_date_row_class; ?> status-pending format-standard hentry category-uncategorized alternate iedit author-other">
                             <th scope="row" class="check-column">
                                 <input type="checkbox" name="post[]" value=<?php echo $post_id; ?> wfid='<?php echo $workflow_id; ?>'>
                             </th>
                             <td>
                                 <?php
                                 $post_status = get_post_status( $post_id );
                                 $is_custom_status = $ow_custom_statuses->get_single_term_by( 'slug', $post_status );
                                 $post_status = ucfirst( $post_status );
                                 if( $is_custom_status ) {
                                    $post_status = $is_custom_status->name;
                                 }
                                 ?>
                                 <strong>
                                     <?php echo $post->post_title; ?>
                                     <span class="post-state"><?php echo wptexturize( '&mdash;' ) . ' ' . $post_status; ?></span>
                                 </strong>
                                 <div class="row-actions">
                                     <!--
                                     if the item needs to be claimed, only "Claim" action is visible
                                     -->
                                     <?php if( $needs_to_be_claimed ) { ?>
                                        <span>
                                            <a href="#" class="claim" actionid="<?php echo $workflow_id; ?>"><?php _e( 'Claim', 'oasisworkflow' ); ?></a>
                                        </span>
                                     <?php } else { ?>
                                        <?php if( OW_Utility::instance()->can_user_edit_post( $user->ID ) ) { ?>
                                           <span>
                                               <a href="post.php?post=<?php echo $post_id; ?>&action=edit&oasiswf=<?php echo $workflow_id; ?>&user=<?php echo $selected_user; ?>" class="edit" real="<?php echo $post_id; ?>">
                                                   <?php _e( 'Edit', 'oasisworkflow' ); ?>
                                               </a>
                                           </span>
                                           &nbsp;|&nbsp;
                                        <?php } ?>
                                        <span>
                                            <a target="_blank" href="<?php echo get_permalink( $post_id ); ?>&preview=true">
                                                <?php _e( 'View', 'oasisworkflow' ); ?>
                                            </a>
                                        </span>
                                        &nbsp;|&nbsp;

                                        <?php
                                        if( current_user_can( 'ow_sign_off_step' ) &&
                                                OW_Utility::instance()->can_user_edit_post( $user->ID ) ) {
                                           ?>
                                           <span>
                                               <a href="#" wfid="<?php echo $workflow_id; ?>" postid="<?php echo $post_id; ?>" class="quick_sign_off">
                                                   <?php echo $sign_off_label; ?>
                                               </a>
                                               <span class='loading'><?php echo $space; ?></span>
                                           </span>
                                           &nbsp;|&nbsp;
                                        <?php } ?>

                                        <?php if( current_user_can( 'ow_reassign_task' ) ) { ?>
                                           <span>
                                               <a href="#" wfid="<?php echo $workflow_id; ?>" class='reassign'>
                                                   <?php _e( 'Reassign', 'oasisworkflow' ); ?>
                                               </a>
                                               <span class='loading'><?php echo $space; ?></span>
                                           </span>
                                           &nbsp;|&nbsp;
                                        <?php } ?>
                                        <?php if( current_user_can( 'ow_abort_workflow' ) ) { ?>
                                           <span>
                                               <a href="#" wfid="<?php echo $workflow_id; ?>" postid="<?php echo $post_id; ?>" class='abort_workflow'>
                                                   <?php echo $abort_workflow_label; ?>
                                               </a>
                                               <span class='loading'><?php echo $space; ?></span>
                                           </span>
                                           &nbsp;|&nbsp;
                                        <?php } ?>
                                        <?php
                                        if( current_user_can( 'ow_view_workflow_history' ) ) {
                                           $nonce_url = wp_nonce_url( "admin.php?page=oasiswf-history&post=$post_id", 'owf_view_history_nonce' );
                                           ?>
                                           <span>
                                               <a href="<?php echo $nonce_url; ?>">
                                                   <?php _e( 'View History', 'oasisworkflow' ); ?>
                                               </a>
                                           </span>
                                        <?php } ?>
                                        <?php apply_filters( 'owf_workflow_inbox_row_action', $inbox_item ); ?>
                                        <?php
                                        // TODO: WHAT IS THIS FOR?
                                        get_inline_data( $post );
                                        ?>
                                     <?php } ?>
                                 </div>
                             </td>
                             <?php
                             if( get_option( 'oasiswf_priority_setting' ) == 'enable_priority' ) {
                                //priority settings
                                $priority = get_post_meta( $post->ID, 'ow_task_priority', true );
                                if( empty( $priority ) ) {
                                   $priority = '2normal';
                                }

                                $priority_array = OW_Utility::instance()->get_priorities();
                                $priority_value = $priority_array[$priority];
                                // the CSS is defined without the number part
                                $css_class = substr( $priority, 1 );
                                ?>
                                <td>
                                    <p class="post-priority <?php echo $css_class; ?>-priority">
                                        <?php echo $priority_value; ?>
                                    </p>
                                </td>
                             <?php } ?>
                             <?php $post_type_obj = get_post_type_object( get_post_type( $post_id ) ); ?>
                             <td><?php echo $post_type_obj->labels->singular_name; ?></td>
                             <td><?php echo OW_Utility::instance()->get_user_name( $user->ID ); ?></td>
                             <?php
                             $workflow_name = $workflow->name;
                             if( !empty( $workflow->version ) ) {
                                $workflow_name .= " (" . $workflow->version . ")";
                             }
                             ?>
                             <td><?php
                                 echo $workflow_name .
                                 ' [' .
                                 $ow_workflow_service->get_gpid_dbid( $workflow->ID, $stepId, 'lbl' ) .
                                 ']';
                                 ?></td>
                             <td><?php echo $cat_name; ?></td>
                             <?php
                             // if the due date is passed the current date show the field in a different color
                             ?>
                             <td>
                                 <span class="<?php echo $past_due_date_field_class; ?>">
                                     <?php echo OW_Utility::instance()->format_date_for_display( $inbox_item->due_date ); ?>
                                 </span>
                             </td>
                             <td class="column-comments" data-colname="<?php _e( 'Comments', 'oasisworkflow' ); ?>">
                                 <div class='post-com-count-wrapper'>
                                     <strong>
                                         <a href="#" actionid="<?php echo $workflow_id; ?>"
                                            class='post-com-count post-com-count-approved'
                                            data-comment='inbox_comment'
                                            post_id="<?php echo $post_id; ?>">
                                             <span class='comment-count-approved'><?php echo $ow_process_flow->get_comment_count( $workflow_id, TRUE, $post_id ); ?></span>
                                         </a>
                                         <span class='loading'><?php echo $space; ?></span>
                                     </strong>
                                 </div>
                             </td>
                         </tr>

                         <?php
                      }
                      $count++;
                   }
                } else {
                   ?>
                   <tr>
                       <td class="hurry-td" colspan="8">
                           <label class="hurray-lbl">
                               <?php _e( 'Hurray! No assignments', 'oasisworkflow' ); ?>
                           </label>
                       </td>
                   </tr>
                   <?php
                }
                echo ob_get_clean();
                ?>
            </tbody>
        </table>
        <div class="tablenav">
            <div class="tablenav-pages">
                <?php OW_Utility::instance()->get_page_link( $count_posts, $page_number, $per_page ); ?>
            </div>
        </div>
    </div>
</div>
<span id="wf_edit_inline_content"></span>
<div id ="step_submit_content"></div>
<div id="reassign-div"></div>
<div id="post_com_count_content"></div>
<input type="hidden" name="owf_claim_process_ajax_nonce" id="owf_claim_process_ajax_nonce" value="<?php echo wp_create_nonce( 'owf_claim_process_ajax_nonce' ); ?>" />
<input type="hidden" name="owf_inbox_ajax_nonce" id="owf_inbox_ajax_nonce" value="<?php echo wp_create_nonce( 'owf_inbox_ajax_nonce' ); ?>" />
