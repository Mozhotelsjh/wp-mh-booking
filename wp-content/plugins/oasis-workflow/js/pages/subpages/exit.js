jQuery( document ).ready( function () {
   if ( exit_wfid ) {
      jQuery( "#publishing-action" ).append( "<div class='abort-workflow-section right'><a href='#' id='exit_link'>" + owf_abort_workflow_vars.abortWorkflow + "</a><span class='blank-space loading owf-hidden'></span></div>" );
      jQuery( '.error' ).hide();

   }
   jQuery( document ).on( "click", "#exit_link", function () {
      if ( !confirm( owf_abort_workflow_vars.abortWorkflowConfirm ) )
         return;
      data = {
         action: 'exit_post_from_workflow',
         exitId: exit_wfid,
         security: jQuery( '#owf_exit_post_from_workflow' ).val(),
         command: 'exit_from_workflow'
      };
      jQuery( this ).hide();
      jQuery( ".loading" ).show();
      jQuery.post( ajaxurl, data, function ( response ) {
         if ( response.trim() ) {
            location.reload();
         }
      } );
   } )
} );