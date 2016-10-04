<?php
//----------------
$action = ( isset( $_REQUEST["action"] ) && sanitize_text_field( $_REQUEST["action"] )) ? sanitize_text_field( $_REQUEST["action"] ) : "not-workflow";
$post_type = ( isset( $_REQUEST["type"] ) && sanitize_text_field( $_REQUEST["type"] )) ? sanitize_text_field( $_REQUEST["type"] ) : "all";
$page_number = (isset( $_GET['paged'] ) && sanitize_text_field( $_GET["paged"] )) ? intval( sanitize_text_field( $_GET["paged"] ) ) : 1;

$ow_process_flow = new OW_Process_Flow();

$submitted_posts = $ow_process_flow->get_submitted_articles( $post_type );
$un_submitted_posts = $ow_process_flow->get_unsubmitted_articles( $post_type );

if ( $action == "in-workflow" ) {
   $posts = $submitted_posts;
} else {
   $posts = $un_submitted_posts;
}

$count_posts = count( $posts );
$per_page = OASIS_PER_PAGE;
?>
<div class="wrap">
    <div id="view-workflow">
        <form id="submission_report_form" method="post" action="<?php echo admin_url( 'admin.php?page=oasiswf-reports&tab=workflowSubmissions' ); ?>">
            <div class="tablenav top">
                <input type="hidden" name="page" value="oasiswf-submission" />
                <input type="hidden" id="action" name="action" value="<?php echo esc_attr( $action ); ?>" />
                <div class="alignleft actions">
                    <select name="type">
                        <option value="all" <?php echo ( $post_type == "all" ) ? "selected" : ""; ?> >All Types</option>
                        <?php OW_Utility::instance()->owf_dropdown_post_types_multi( $post_type ); ?>
                    </select>
                    <input type="submit" class="button action" value="Filter" />
                </div>
                <div>
                    <ul class="subsubsub">
                        <?php
                        $all = ( $action == "all" ) ? "class='current'" : "";
                        $not_in_wf = ( $action == "not-workflow" ) ? "class='current'" : "";
                        $in_wf = ( $action == "in-workflow" ) ? "class='current'" : "";
                        echo '<li class="all"><a id="notInWorkflow" href="#" ' . $not_in_wf . '>' . __( 'Not in Workflow' ) .
                        '<span class="count"> (' . count( $un_submitted_posts ) . ')</span></a> </li>';
                        echo ' | <li class="all"><a id="inWorkflow" href="#" ' . $in_wf . '>' . __( 'In Workflow' ) .
                        '<span class="count"> (' . count( $submitted_posts ) . ')</span></a> </li>';
                        ?>
                    </ul>
                </div>
                <div class="tablenav-pages">
                    <?php OW_Utility::instance()->get_page_link( $count_posts, $page_number, $per_page, $action ); ?>
                </div>
            </div>
        </form>
        <table class="wp-list-table widefat fixed posts" cellspacing="0" border=0>
            <thead>
                <tr>
                    <?php
                    echo "<tr>";
                    if ( $action == 'in-workflow' ) {
                       echo "<th scope='col' class='manage-column check-column'><input type='checkbox' name='abort-all'  /></th>";
                    }
                    echo "<th>" . __( "Title" ) . "</th>";
                    echo "<th class='column-role'>" . __( "Type" ) . "</th>";
                    echo "<th class='column-role'>" . __( "Author" ) . "</th>";
                    echo "<th class='column-role'>" . __( "Date" ) . "</th>";
                    echo "</tr>";
                    ?>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <?php
                    echo "<tr>";
                    if ( $action == 'in-workflow' ) {
                       echo "<th scope='col' class='manage-column check-column'><input type='checkbox' name='abort-all'  /></th>";
                    }
                    echo "<th>" . __( "Title" ) . "</th>";
                    echo "<th class='column-role'>" . __( "Type" ) . "</th>";
                    echo "<th class='column-role'>" . __( "Author" ) . "</th>";
                    echo "<th class='column-role'>" . __( "Date" ) . "</th>";
                    echo "</tr>";
                    ?>
                </tr>
            </tfoot>
            <tbody id="coupon-list">
                <?php
                if ( $posts ):
                   $count = 0;
                   $start = ($page_number - 1) * $per_page;
                   $end = $start + $per_page;
                   foreach ( $posts as $post ) {
                      if ( $count >= $end )
                         break;
                      if ( $count >= $start ) {
                         $user = get_userdata( $post->post_author );
                         echo "<tr>";
                         if ( $action == 'in-workflow' ) {
                            echo "<td><input type='checkbox' id='abort-" . $post->ID . "' value='" . esc_attr( $post->ID ) . "' name='abort' /></td>";
                         }
                         echo "<td><a href='post.php?post=" . $post->ID . "&action=edit'>{$post->post_title}</a></td>";
                         echo "<td>{$post->post_type}</td>";
                         echo "<td>{$user->data->display_name}</td>";
                         echo "<td>" . OW_Utility::instance()->format_date_for_display( $post->post_date, "-", "datetime" ) . "</td>";
                         echo "</tr>";
                      }
                      $count++;
                   }
                else:
                   echo "<tr>";
                   echo "<td class='hurry-td' colspan='4'>
							<label class='hurray-lbl'>";
                   echo __( "No Posts/Pages found in any workflows." );
                   echo "</label></td>";
                   echo "</tr>";
                endif;
                ?>
            </tbody>
        </table>
        <?php
        $role = OW_Utility::instance()->get_current_user_role();
        ?>
        <?php if ( $action == 'in-workflow' && current_user_can( 'ow_abort_workflow' ) ) : ?>

           <div class="tablenav bottom">
               <!-- Bulk Actions Start -->
               <div class="alignleft actions">
                   <select name="action_type" id="action_type">
                       <option value="none"><?php echo __( "-- Select Action --" ); ?></option>
                       <option value="abort"><?php echo __( "Abort" ); ?></option>
                   </select>
                   <input type="button" class="button action" id="apply_action" value="Apply"><span class='loading owf-hidden' class='inline-loading'></span>
               </div>
               <!-- Bulk Actions End -->
               <!-- Display pages Start -->
               <div class="tablenav-pages">
                   <?php OW_Utility::instance()->get_page_link( $count_posts, $page_number, $per_page, $action ); ?>
               </div>
               <!-- Display pages End -->
           </div>

        <?php endif; ?>
    </div>
    <div id="out"></div>
</div>
<script type="text/javascript">
   jQuery(document).ready(function () {
       jQuery('#notInWorkflow').click(function (event) {
           jQuery("#action").val("not-workflow");
           jQuery("#submission_report_form").submit();
       });

       jQuery('#inWorkflow').click(function (event) {
           jQuery("#action").val("in-workflow");
           jQuery("#submission_report_form").submit();
       });

       jQuery('input[name=abort-all]').click(function (event) {
           jQuery('input[type=checkbox]').prop('checked', jQuery(this).prop("checked"));
       });

       jQuery('#apply_action').click(function ()
       {
           if (jQuery('#action_type').val() == 'none')
               return;

           var arr = jQuery('input[name=abort]:checked');
           var post_ids = new Array();
           jQuery.each(arr, function (k, v)
           {
               post_ids.push(jQuery(this).val());
           });
           if (post_ids.length === 0)
               return;

           data = {
               action: 'multi_abort_from_workflow',
               postids: post_ids,
           };

           jQuery(".loading").show();
           jQuery.post(ajaxurl, data, function (response) {
               if (response) {
                   jQuery(".loading").hide();
                   jQuery('#inWorkflow').click();
               }
           });
       });

   });
</script>