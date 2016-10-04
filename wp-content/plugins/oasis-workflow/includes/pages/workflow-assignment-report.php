<?php
$selected_user = (isset( $_REQUEST['user'] ) && sanitize_text_field( $_REQUEST["user"] )) ? intval( sanitize_text_field( $_REQUEST["user"] ) ) : null;
$page_number = (isset( $_GET['paged'] ) && sanitize_text_field( $_GET["paged"] )) ? intval( sanitize_text_field( $_GET["paged"] ) ) : 1;

$ow_process_flow = new OW_Process_Flow();
$assigned_tasks = $ow_process_flow->get_assigned_post( null, $selected_user );
$count_posts = count( $assigned_tasks );

$workflow_service = new OW_Workflow_Service();
$per_page = OASIS_PER_PAGE;

$due_date_title = OW_Utility::instance()->get_custom_workflow_terminology( 'dueDateText' );
?>
<div class="wrap">
    <form id="assignment_report_form" method="post" action="<?php echo admin_url( 'admin.php?page=oasiswf-reports&tab=userAssignments' ); ?>">
        <div class="tablenav">
            <ul class="subsubsub"></ul>
            <div class="tablenav-pages">
<?php OW_Utility::instance()->get_page_link( $count_posts, $page_number, $per_page ); ?>
            </div>
        </div>
    </form>
    <table class="wp-list-table widefat fixed posts" cellspacing="0" border=0>
        <thead>
            <?php
            echo "<tr>";
            echo "<th class='column-role'>" . __( "User", "oasisworkflow" ) . "</th>";
            echo "<th>" . __( "Post/Page", "oasisworkflow" ) . "</th>";
            echo "<th class='column-role'>" . __( "Workflow", "oasisworkflow" ) . "</th>";
            echo "<th class='column-author'>" . __( "Status", "oasisworkflow" ) . "</th>";
            echo "<th class='column-author'>" . $due_date_title . "</th>";
            echo "</tr>";
            ?>
        </thead>
        <tfoot>
            <?php
            echo "<tr>";
            echo "<th class='column-role'>" . __( "User", "oasisworkflow" ) . "</th>";
            echo "<th>" . __( "Post/Page", "oasisworkflow" ) . "</th>";
            echo "<th class='column-role'>" . __( "Workflow", "oasisworkflow" ) . "</th>";
            echo "<th class='column-author'>" . __( "Status", "oasisworkflow" ) . "</th>";
            echo "<th class='column-author'>" . __( "$due_date_title", "oasisworkflow" ) . "</th>";
            echo "</tr>";
            ?>
        </tfoot>
        <tbody id="coupon-list">
            <?php
            $wf_status = get_site_option( "oasiswf_status" );
            if( $assigned_tasks ):
               $count = 0;
               $start = ($page_number - 1) * $per_page;
               $end = $start + $per_page;
               foreach ( $assigned_tasks as $assigned_task ) {
                  if( $count >= $end )
                     break;
                  if( $count >= $start ) {
                     $post = get_post( $assigned_task->post_id );
                     $user = get_userdata( $post->post_author );
                     $step_id = $assigned_task->step_id;
                     if( $step_id <= 0 || $step_id == "" ) {
                        $step_id = $assigned_task->review_step_id;
                     }
                     $step = $workflow_service->get_step_by_id( $step_id );
                     $workflow = $workflow_service->get_workflow_by_id( $step->workflow_id );

                     echo "<tr id='post-{$assigned_task->post_id}' class='post-{$assigned_task->post_id} post type-post status-pending format-standard hentry category-uncategorized alternate iedit author-other'> ";
                     $assigned_actor_id = null;
                     if( $assigned_task->assign_actor_id != -1 ) { // not in review process
                        $assigned_actor = OW_Utility::instance()->get_user_name( $assigned_task->assign_actor_id );
                     } else { //in review process
                        $assigned_actor = OW_Utility::instance()->get_user_name( $assigned_task->actor_id );
                     }

                     echo "<td>" . $assigned_actor . "</td>";
                     echo "<td><a href='post.php?post=" . $post->ID . "&action=edit'>{$post->post_title}</a></td>";
                     echo "<td>{$workflow->name}</td>";
                     echo "<td>" . $wf_status[$workflow_service->get_gpid_dbid( $workflow->ID, $step_id, 'process' )] . "</td>";
                     echo "<td>" . OW_Utility::instance()->format_date_for_display( $assigned_task->due_date ) . "</td>";
                     echo "</tr>";
                  }
                  $count++;
               }
            else:
               echo "<tr>";
               echo "<td class='hurry-td' colspan='5'>
   						<label class='hurray-lbl'>";
               echo __( "No current assignments.", "oasisworkflow" );
               echo "</label></td>";
               echo "</tr>";
            endif;
            ?>
        </tbody>
    </table>
    <div class="tablenav">
        <div class="tablenav-pages">
            <?php OW_Utility::instance()->get_page_link( $count_posts, $page_number, $per_page ); ?>
        </div>
    </div>
</div>