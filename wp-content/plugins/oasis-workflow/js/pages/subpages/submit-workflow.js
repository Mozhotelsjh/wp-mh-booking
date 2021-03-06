jQuery( document ).ready( function () {
   var stepProcess = "";
   //------main function-------------
   function load_setting() {
      var allowed_post_types = jQuery.parseJSON( owf_submit_workflow_vars.allowedPostTypes );
      var current_post_type = jQuery( '#post_type' ).val();

      // If not allowed post/page type then do not show
      if ( jQuery.inArray( current_post_type, allowed_post_types ) != -1 )
      {

         jQuery( "#publishing-action" ).append(
                 "<input type='button' id='workflow_submit' class='button button-primary button-large'" + " value='" + owf_submit_workflow_vars.submitToWorkflowButton + "' style='float:left;clear:both;' />"
                 ).css( { "width": "100%" } );

         jQuery( "#post" ).append(
                 "<input type='hidden' id='hi_workflow_id' name='hi_workflow_id' />" +
                 "<input type='hidden' id='hi_step_id' name='hi_step_id' />" +
                 "<input type='hidden' id='hi_priority_select' name='hi_priority_select' />" +
                 "<input type='hidden' id='hi_actor_ids' name='hi_actor_ids' />" +
                 "<input type='hidden' id='hi_due_date' name='hi_due_date' />" +
                 "<input type='hidden' id='hi_comment' name='hi_comment' />" +
                 "<input type='hidden' id='save_action' name='save_action' />"
                 );
         jQuery( "#publishing-action" ).css( { "margin-top": "10px" } );
      }

//		jQuery('.inline-edit-status').hide() ;

      jQuery( '.error' ).hide();
   }

   function calendar_action() {
      jQuery( "#due-date" ).attr( "readonly", true );
      jQuery( "#due-date" ).datepicker( {
         autoSize: true,
         dateFormat: owf_submit_workflow_vars.editDateFormat
      } );

      if ( jQuery( '.misc-pub-curtime #timestamp b' ).html().indexOf( "@" ) > -1 ) {// future publish date
         // get the user set date/time
         var user_set_publish_date_arr = jQuery( '.misc-pub-curtime #timestamp b' ).html().split( '@' );

         var parsedDate = '';

         // date
         var publish_date_mm_dd_yyyy = '';
         if ( user_set_publish_date_arr[0].trim().indexOf( "-" ) > -1 ) {
            // get from the UI
            parsedDate = jQuery.datepicker.parseDate( 'mm-M dd, yy', user_set_publish_date_arr[0].trim() );

            // convert the date to edit format
            publish_date_mm_dd_yyyy = jQuery.datepicker.formatDate( owf_submit_workflow_vars.editDateFormat, parsedDate );
            jQuery( "#publish-date" ).val( publish_date_mm_dd_yyyy );

            // time
            var user_set_publish_time_arr = user_set_publish_date_arr[1].split( ":" );
            jQuery( "#publish-hour" ).val( user_set_publish_time_arr[0].trim() );
            jQuery( "#publish-min" ).val( user_set_publish_time_arr[1].trim() );

         } else {
            // get from the DB
            jQuery( ".publish-date-loading-span" ).addClass( "loading" );

            data = {
               action: 'get_post_publish_date_edit_format',
               post_id: jQuery( '#post_ID' ).val()
            };

            jQuery.post( ajaxurl, data, function ( response ) {
               if ( response.trim() != "" ) {
                  var user_set_publish_date_arr = response.trim().split( '@' );
                  parsedDate = jQuery.datepicker.parseDate( 'mm-M dd, yy', user_set_publish_date_arr[0].trim() );

                  // convert the date to edit format
                  publish_date_mm_dd_yyyy = jQuery.datepicker.formatDate( owf_submit_workflow_vars.editDateFormat, parsedDate );
                  jQuery( "#publish-date" ).val( publish_date_mm_dd_yyyy );

                  // time
                  var user_set_publish_time_arr = user_set_publish_date_arr[1].split( ":" );
                  jQuery( "#publish-hour" ).val( user_set_publish_time_arr[0].trim() );
                  jQuery( "#publish-min" ).val( user_set_publish_time_arr[1].trim() );

                  jQuery( ".publish-date-loading-span" ).removeClass( "loading" );
               }
            } );

         }
      }

      jQuery( "#publish-date" ).attr( "readonly", true );
      // add jquery datepicker functionality to publish textbox
      jQuery( "#publish-date" ).datepicker( {
         autoSize: true,
         dateFormat: owf_submit_workflow_vars.editDateFormat
      } );
   }

   jQuery( ".date-clear" ).click( function () {
      jQuery( this ).parent().children( ".date_input" ).val( "" );
   } );

   jQuery( document ).on( "click", "#workflow_submit", function () {

      // hook for custom validation before submitting to the workflow
      if ( typeof owSubmitToWorkflowPre === 'function' ) {
         var pre_submit_to_workflow_result = owSubmitToWorkflowPre();
         if ( pre_submit_to_workflow_result == false ) {
            return false;
         }
      }

      // hook for running ACF or other third party plugin validations if needed before submitting to the workflow
      owThirdPartyValidation.run( workflowSubmit );
//      jQuery('.simplemodal-wrap').css('overflow', 'hidden');
//      jQuery('.simplemodal-container').css('height', '100%');

   } );

   jQuery( document ).on( "click", "#submitCancel, .modalCloseImg", function () {
      modal_close();
   } );

   modal_close = function () {
      stepProcess = "";
      jQuery.modal.close();
   }

   load_setting();

   //----------setting when selecte------------------
   action_setting = function ( inx, frm ) {
      if ( inx == "wf" ) {
         if ( frm == "pre" ) {
            stepProcess = "";
            jQuery( "#step-select" ).find( 'option' ).remove();
            jQuery( "#actor-one-select" ).find( 'option' ).remove();
            jQuery( "#actors-list-select" ).find( 'option' ).remove();
            jQuery( "#actors-set-select" ).find( 'option' ).remove();

            jQuery( "#actor-one-select" ).attr( "disabled", true );
            jQuery( "#actors-list-select" ).attr( "disabled", true );
            jQuery( "#actors-set-select" ).attr( "disabled", true );

            jQuery( "#step-loading-span" ).addClass( "loading" );
         }
      }
      if ( inx == "step" ) {
         stepProcess = "";
         if ( frm == "pre" ) {
            jQuery( "#actor-one-select" ).find( 'option' ).remove();
            jQuery( "#actors-list-select" ).find( 'option' ).remove();
            jQuery( "#actors-set-select" ).find( 'option' ).remove();

            jQuery( "#actor-one-select" ).attr( "disabled", true );
            jQuery( "#actors-list-select" ).attr( "disabled", true );
            jQuery( "#actors-set-select" ).attr( "disabled", true );

            jQuery( ".assign-loading-span" ).addClass( "loading" );
         } else {
            jQuery( ".assign-loading-span" ).removeClass( "loading" );
            jQuery( "#actor-one-select" ).removeAttr( "disabled" );
            jQuery( "#actors-list-select" ).removeAttr( "disabled" );
            jQuery( "#actors-set-select" ).removeAttr( "disabled" );

         }
      }
   }

   function workflow_select( workflow_id ) {
      action_setting( "wf", "pre" );
      if ( !workflow_id ) {
         jQuery( "#step-loading-span" ).removeClass( "loading" );
         return;
      }

      data = {
         action: 'get_first_step',
         wf_id: workflow_id,
         security: jQuery( '#owf_signoff_ajax_nonce' ).val()
      };

      jQuery( "#step-loading-span" ).addClass( "loading" );
      jQuery.post( ajaxurl, data, function ( response ) {
         jQuery( "#step-loading-span" ).removeClass( "loading" );

         if ( response.trim() == -1 ) {
            return false; // Invalid Nonce
         }
         if ( response.trim() == "nodefine" ) {
            alert( owf_submit_workflow_vars.allStepsNotDefined );
            jQuery( "#workflow-select" ).val( "" );
            return;
         }
         if ( response.trim() == "wrong" ) {
            alert( owf_submit_workflow_vars.notValidWorkflow );
            jQuery( "#workflow-select" ).val( "" );
            return;
         }
         var stepinfo = { };
         if ( response.trim() ) {
            stepinfo = JSON.parse( response.trim() );
            jQuery( "#step-select" ).find( 'option' ).remove();
            jQuery( "#step-select" ).append( "<option value='" + stepinfo["first"][0][0] + "'>" + stepinfo["first"][0][1] + "</option>" );
            jQuery( "#step-select" ).change();
         }
      } );
   }

   jQuery( "#workflow-select" ).change( function () {
      workflow_select( jQuery( this ).val() );
   } );

   jQuery( "#step-select" ).change( function () {
      action_setting( "step", "pre" );
      // teams plugin is active
      if ( owf_submit_workflow_vars.workflowTeamsAvailable == 'yes' )
      {
         data = {
            action: 'get_teams_for_workflow',
         };
         jQuery.post( ajaxurl, data, function ( response ) {
            teams = JSON.parse( response.trim() );
            add_option_to_select( "teams-list-select", teams, 'name', 'ID' );
            action_setting( "step", "after" );
         } );
      } else
      {
         data = {
            action: 'get_users_in_step',
            stepid: jQuery( this ).val(),
            postid: jQuery( "#post_ID" ).val(),
            security: jQuery( '#owf_signoff_ajax_nonce' ).val(),
            decision: 'complete' // it will always be success on submit
         };

         jQuery.post( ajaxurl, data, function ( response ) {
            if ( response.trim() == -1 ) {
               return false;
            }
            action_setting( "step", "after" );

            // if response is false then there are no users for given role..!
            if ( response.trim() == 'false' ) {
               alert( owf_submit_workflow_vars.noUsersDefined );
               return false;
            }

            var result = { }, users = { };
            if ( response.trim() ) {
               result = JSON.parse( response.trim() );
               if ( typeof result["users"][0] == 'object' ) {
                  users = result["users"];
               }
               stepProcess = result["process"];
            }
            // get assign to all value from the step
            var is_assign_to_all = parseInt( result["assign_to_all"] );

            // multiple actors applicable to all the steps
            jQuery( "#one-actors-div" ).hide();

            // if assign to all is checked, then hide the assignee selection.
            if ( is_assign_to_all === 1 ) {
               jQuery( '#multiple-actors-div' ).hide();
               jQuery( '<input>' ).attr( {
                  type: 'hidden',
                  id: 'assign_to_all',
                  name: 'assign_to_all',
                  value: is_assign_to_all
               } ).appendTo( '#post' );
               return false;
            }

            jQuery( "#multiple-actors-div" ).show();
            add_option_to_select( "actors-list-select", users, 'name', 'ID' );

         } );
      } //teams else ended
   } );

   //---- point function -------
   jQuery( "#assignee-set-point" ).click( function () {
      jQuery( '#actors-list-select option:selected' ).each( function () {
         var v = jQuery( this ).val();
         var t = jQuery( this ).text();
         insert_remove_options( 'actors-list-select', 'actors-set-select', v, t );
      } );
      return false;
   } );

   var insert_remove_options = function ( removeSelector, appendSelector, val, text ) {
      if ( typeof val !== 'undefined' ) {
         jQuery( "#" + removeSelector + " option[value='" + val + "']" ).remove();
         jQuery( '#' + appendSelector ).append( '<option value=' + val + '>' + text + '</option>' );
      }
   };

   jQuery( "#assignee-unset-point" ).click( function () {
      jQuery( '#actors-set-select option:selected' ).each( function () {
         var v = jQuery( this ).val();
         var t = jQuery( this ).text();
         insert_remove_options( 'actors-set-select', 'actors-list-select', v, t );
      } );
      return false;
   } );

   var option_exist_chk = function ( val ) {
      if ( jQuery( '#actors-set-select option[value=' + val + ']' ).length > 0 ) {
         return false;
      } else {
         return true;
      }
   }
   //------------save-------------------
   jQuery( "#submitSave" ).click( function () {
      if ( !jQuery( "#workflow-select" ).val() ) {
         alert( owf_submit_workflow_vars.selectWorkflow );
         return false;
      }

      if ( !jQuery( "#step-select" ).val() ) {
         alert( owf_submit_workflow_vars.selectStep );
         return false;
      }

      if ( jQuery( "#step-select" ).val() == "nodefine" ) {
         alert( owf_submit_workflow_vars.stepNotDefined );
         return false;
      }

      jQuery( ".changed-data-set span" ).addClass( "loading" );
      jQuery( this ).hide();

      var submit_to_workflow_pre = {
         action: 'submit_post_to_workflow_pre',
         form: jQuery( 'form#post' ).serialize(),
         step_id: jQuery( "#step-select" ).val(),
         security: jQuery( '#owf_signoff_ajax_nonce' ).val()
      };

//		if ( wp && wp.autosave ) {
//			wp.autosave.server.triggerSave();
//		}

      jQuery.post( ajaxurl, submit_to_workflow_pre, function ( response ) {
         if ( response.trim() != "true" ) {
            jQuery( '#ow-step-messages' ).html( response.trim() );
            jQuery( '#ow-step-messages' ).removeClass( 'owf-hidden' );
            // scroll to the top of the window to display the error messages
            jQuery( ".simplemodal-wrap" ).css( 'overflow', 'hidden' );
            jQuery( ".simplemodal-wrap" ).animate( { scrollTop: 0 }, "slow" );
            jQuery( ".simplemodal-wrap" ).css( 'overflow', 'scroll' );
            jQuery( ".changed-data-set span" ).removeClass( "loading" );
            jQuery( "#simplemodal-container" ).css( "max-height", "80%" );

            // call modal.setPosition, so that the window height can adjust automatically depending on the displayed fields.
            jQuery.modal.setPosition();

            jQuery( "#submitSave" ).show();
            return false;
         } else {
            after_pre_workflow_submit_validation();
         }
      } );
   } );

   after_pre_workflow_submit_validation = function ( ) {
      /*
       * get publish date value, if not null set as post publish date
       * if publish date is not set then proceess default.
       * Immediate post publish date
       */
      if ( jQuery( "#publish-date" ).length > 0 && jQuery( "#publish-date" ).val() != '' )
      {
         var publish = jQuery( '#publish-date' ).val();
         var parsedDate = jQuery.datepicker.parseDate( owf_submit_workflow_vars.editDateFormat, publish );
         //split into array
         var publish_date_mm_dd_yyyy = jQuery.datepicker.formatDate( 'mm/dd/yy', parsedDate );
         var pdate = publish_date_mm_dd_yyyy.split( '/' );

         //set this mm/dd/yyyy value as wordpress publish date
         jQuery( '#mm' ).val( pdate[0] );
         jQuery( '#jj' ).val( pdate[1] );
         jQuery( '#aa' ).val( pdate[2] );

         if ( jQuery( '#publish-hour' ).val() == '' )
         {
            jQuery( '#hh' ).val( '12' );
         } else
         {
            jQuery( '#hh' ).val( parseInt( jQuery( '#publish-hour' ).val(), 10 ) );
         }

         if ( jQuery( '#publish-min' ).val() == '' )
         {
            jQuery( '#mn' ).val( '00' );
         } else
         {
            jQuery( '#mn' ).val( parseInt( jQuery( '#publish-min' ).val(), 10 ) );
         }

      }

      var actors;
      // Lets check if chosen workflow is assigned to role or between role's users
      var is_assigned_to_all = parseInt( jQuery( '#assign_to_all' ).val() );
      if ( is_assigned_to_all === 1 ) {
         actors = true;
      } else {
         var actors = assign_actor_chk();
      }

      if ( !actors )
         return;
      /* This is for checking that reminder email checkbox is selected in workflow settings.
       If YES then Due Date is Required Else Not */
      if ( owf_submit_workflow_vars.drdb != "" || owf_submit_workflow_vars.drda != "" || owf_submit_workflow_vars.defaultDueDays != "" )
      {
         if ( jQuery( "#due-date" ).val() == '' ) {
            alert( owf_submit_workflow_vars.dueDateRequired );
            jQuery( ".changed-data-set span" ).removeClass( "loading" );
            jQuery( "#submitSave" ).show();
            return false;
         }
         if ( !chk_due_date( "due-date", owf_submit_workflow_vars.dateFormat ) ) {
            jQuery( ".changed-data-set span" ).removeClass( "loading" );
            jQuery( "#submitSave" ).show();
            return false;
         }
      }

      jQuery( "#hi_workflow_id" ).val( jQuery( "#workflow-select" ).val() );
      jQuery( "#hi_step_id" ).val( jQuery( "#step-select" ).val() );
      jQuery( "#hi_priority_select" ).val( jQuery( "#priority-select" ).val() );
      jQuery( "#hi_actor_ids" ).val( actors );
      jQuery( "#hi_due_date" ).val( jQuery( "#due-date" ).val() );
      jQuery( "#hi_comment" ).val( jQuery( "#workflowComments" ).val() );

      jQuery( ".changed-data-set span" ).addClass( "loading" );
      jQuery( this ).hide();

      // teams plugin is active
      if ( owf_submit_workflow_vars.workflowTeamsAvailable == 'yes' )
      {
         // check if the team has the users with the required role(s)
         var team_has_users = {
            action: 'has_users_in_team',
            team_id: jQuery( "#hi_actor_ids" ).val(),
            step_id: jQuery( "#hi_step_id" ).val(),
            security: jQuery( '#owf_signoff_ajax_nonce' ).val()
         };

         jQuery.post( ajaxurl, team_has_users, function ( response ) {
            if ( response.trim() == "false" ) {
               alert( owf_submit_workflow_vars.noRoleUsersInTeam );
               jQuery( ".changed-data-set span" ).removeClass( "loading" );
               jQuery( "#submitSave" ).show();
               return false;
            } else {
               jQuery( "#save_action" ).val( "submit_post_to_workflow" );
               jQuery( "#save-post" ).click();
               modal_close();
               return;
            }
         } );
      } else {
         jQuery( "#save_action" ).val( "submit_post_to_workflow" );
         jQuery( "#save-post" ).click();
         modal_close();
         return;
      }
   }

   assign_actor_chk = function () {
      // teams plugin is active
      if ( owf_submit_workflow_vars.workflowTeamsAvailable == 'yes' )
      {
         var optionNum = jQuery( "#teams-list-select" ).val();
         if ( optionNum == '' ) {
            alert( owf_submit_workflow_vars.noTeamSelected );
            return false;
         }

         var team_id = jQuery( '#teams-list-select' ).val();
         if ( team_id ) {
            return team_id;
         } else {
            return false;
         }
      }

      if ( jQuery( "#one-actors-div" ).css( "display" ) == "block" ) {
         if ( !jQuery( "#actor-one-select" ).val() ) {
            alert( "No assigned actor." );
            return false;
         }
         return jQuery( "#actor-one-select" ).val();
      } else {
         var optionNum = jQuery( "#actors-set-select option" ).length;
         if ( !optionNum ) {
            alert( owf_submit_workflow_vars.noAssignedActors );
            jQuery( ".changed-data-set span" ).removeClass( "loading" );
            jQuery( "#submitSave" ).show();
            return false;
         }
         var multi_actors = "", i = 1;
         jQuery( "#actors-set-select option" ).each( function () {
            if ( i == optionNum )
               multi_actors += jQuery( this ).val();
            else
               multi_actors += jQuery( this ).val() + "@";
            i++;
         } );
         if ( multi_actors )
            return multi_actors;
         else
            return false;
      }
      return false;
   }

   function workflowSubmit() {
      jQuery( "#new-workflow-submit-div" ).owfmodal( {
         onShow: function ( dlg ) {
            jQuery( "#simplemodal-container" ).css( "max-height", "80%" );
            jQuery( dlg.wrap ).css( 'overflow', 'auto' ); // or try ;
            jQuery.modal.update();
         }
      } );
      //-------select function------------
      if ( jQuery( '#workflow-select option:selected' ).length > 0 ) {
         workflow_select( jQuery( "#workflow-select" ).val() );
      }
      calendar_action();
   }
} );
