jQuery( document ).ready( function () {
   var select_id = jQuery( "#reassign_actors" ).val();

   modal_close = function () {
      jQuery( document ).find( "#reassign-div" ).html( "" );
      jQuery.modal.close();
   }
   jQuery( document ).on( "click", "#reassignCancel, .modalCloseImg", function () {
      modal_close();
   } );

   jQuery( document ).on( "click", "#reassignSave", function () {

      // if assigned actors length is 0 then show alert
      if ( 0 === jQuery( '#actors-set-select option' ).length ) {
         alert( owf_reassign_task_vars.selectUser );
         return false;
      }
      var actors = [ ];
      jQuery( '#actors-set-select option' ).each( function () {
         actors.push( jQuery( this ).val() );
      } );

      var obj = this;
      jQuery( this ).parent().children( "span" ).addClass( "loading" );
      jQuery( this ).hide();


      var data = {
         action: 'reassign_process',
         oasiswf: jQuery( "#action_history_id" ).val(),
         reassign_id: actors,
         reassignComments: jQuery( '#reassignComments' ).val(),
         task_user: jQuery( '#task_user_inbox' ).val(),
         security: jQuery( '#owf_reassign_ajax_nonce' ).val()
      };
      jQuery.post( ajaxurl, data, function ( response ) {
         response = response.trim();
         if ( response && isNaN( response ) ) {
            jQuery( obj ).parent().children( "span" ).removeClass( "loading" );
            alert( response );
            jQuery( "#reassignSave" ).show();
            return false;
         }
         if ( response ) {
            modal_close();
            location.reload();
         }
      } );
   } );
} );