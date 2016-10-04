jQuery( document ).ready( function () {
   var wfpath = "";
   var stepProcess = ""; // process of selected step
   function calendar_action() {
      jQuery( "#due-date" ).datepicker( {
         autoSize: true,
         dateFormat: owf_submit_step_vars.editDateFormat
      } );
   }

   // When page is called from post edit page
   function load_setting() {
      if ( jQuery( "#hi_editable" ).val() ) {
         jQuery( ".loading" ).show();
         var check_claim = {
            action: 'check_for_claim_ajax',
            history_id: jQuery( "#hi_oasiswf_id" ).val(),
            security: jQuery( '#owf_check_claim_nonce' ).val()
         };
         jQuery.post( ajaxurl, check_claim, function ( response ) {
            jQuery( ".loading" ).hide();
            if ( response.trim() === "false" ) {
               jQuery( "#publishing-action" ).append(
                       "<input type='button' id='step_submit' class='button button-primary button-large'" +
                       " value='" + owf_submit_step_vars.signOffButton + "' style='float:left;margin-top:10px;clear:both' />" +
                       "<input type='hidden' name='hi_process_info' id='hi_process_info' />" +
                       "<input type='hidden' name='hi_oasiswf_redirect' id='hi_oasiswf_redirect' value=''/>" ).css( { "width": "100%" } );
            } else {
               jQuery( "#publishing-action" ).append(
                       "<input type='button' id='claimButton' class='button button-primary button-large'" +
                       " value='" + owf_submit_step_vars.claimButton + "' style='float:left;margin-top:10px;clear:both' />" +
                       "<input type='hidden' name='hi_process_info' id='hi_process_info' />" +
                       "<input type='hidden' name='hi_oasiswf_redirect' id='hi_oasiswf_redirect' value=''/>" ).css( { "width": "100%" } );
            }
         } );
      } else {
         jQuery( "#publish" ).hide();
         jQuery( ".loading" ).show();

         var check_claim = {
            action: 'check_for_claim_ajax',
            history_id: jQuery( "#hi_oasiswf_id" ).val(),
            security: jQuery( '#owf_check_claim_nonce' ).val()
         };

         jQuery.post( ajaxurl, check_claim, function ( response ) {
            jQuery( ".loading" ).hide();
            if ( response.trim() === "false" ) {
               jQuery( "#publishing-action" ).append( "<input type='button' id='step_submit' class='button button-primary button-large' " +
                       "style='float:left;margin-top:10px;' value='" + owf_submit_step_vars.signOffButton + "' />" );
            } else {
               jQuery( "#publishing-action" ).append( "<input type='button' id='claimButton' class='button button-primary button-large' " +
                       "style='float:left;margin-top:10px;' value='" + owf_submit_step_vars.claimButton + "' />" );
            }
         } );
      }
      jQuery( "#publishing-action" ).append( "<a style='float:right;margin-top:10px;' href='admin.php?page=oasiswf-inbox'>" +
              owf_submit_step_vars.inboxButton + "</a>" );

      jQuery( '.inline-edit-status' ).hide();
      jQuery( '.error' ).hide();
   }

   jQuery( document ).on( "click", ".date-clear", function () {
      jQuery( this ).parent().children( ".date_input" ).val( "" );
   } );

   jQuery( document ).on( "click", "#submitCancel, .modalCloseImg", function () {
      modal_close();
   } );

   modal_close = function () {
      wfpath = "";
      stepProcess = "";
      jQuery.modal.close();
      if ( jQuery( "#hi_parrent_page" ).val() == "inbox" )
         jQuery( document ).find( "#step_submit_content" ).html( "" );
   }

   // When page is loaded, this function is processed
   if ( jQuery( "#hi_parrent_page" ).val() == "post_edit" ) {
      load_setting();
   }

   jQuery( document ).on( "click", "#step_submit", function () {

      // hook for custom validation before submitting to the workflow
      if ( typeof owSignOffPre === 'function' ) {
         var sign_off_pre_result = owSignOffPre();
         if ( sign_off_pre_result == false ) {
            return false;
         }
      }

      // hook for running ACF or other third party plugin validation if needed prior to signing off on the workflow
      owThirdPartyValidation.run( signOffSubmit );

      return false;

   } );

   //-----------function-------------------


   first_last_step_message = function ( path, oasiswf_id ) {
      if ( path == "failure" ) {
         var msg = owf_submit_step_vars.firstStepMessage;
         jQuery( "#message_div" ).html( msg ).css( {
            'background-color': '#fbd7f0',
            'border': '1px solid #f989d8'
         } ).show();

         jQuery( "#cancelSave" ).show();
         jQuery( "#submitSave" ).hide();
         jQuery( "#completeSave" ).hide();

         jQuery( "#sum_step_info" ).hide();

      }

      if ( path == "success" ) {
         var msg = owf_submit_step_vars.lastStepMessage;
         jQuery( "#message_div" ).html( msg ).css( { "background-color": "#dcddfa", "border": "1px solid #b0b4fa" } ).show();

         jQuery( "#submitSave" ).hide();
         jQuery( "#cancelSave" ).hide();
         jQuery( "#comments-div" ).hide();

         jQuery( this ).parent().children( ".loading" ).show();

         data = {
            action: 'is_original_post',
            oasiswf_id: oasiswf_id
         };
         // if this is the original post, then we need to show the immediately div (date/time for publish)
         // if this is a revision post, then hide it.
         jQuery.post( ajaxurl, data, function ( response ) {
            jQuery( "#immediately-div" ).show();
            if ( jQuery( "#update_publish_msg" ).length ) {
               jQuery( "#update_publish_msg" ).hide();
            }

            // If future date is set then uncheck the checkbox by default & show immediate span
            if ( jQuery( '#immediately-chk' ).attr( "checked" ) == "checked" )
            {
               jQuery( "#immediately-span" ).hide();
            } else
            {
               jQuery( "#immediately-span" ).show();
            }
            if ( response.trim() == "false" && jQuery( "#update_publish_msg" ).length ) {
               jQuery( "#update_publish_msg" ).show();
            }
         } );

         jQuery( "#completeSave" ).show();
         jQuery( "#sum_step_info" ).hide();
      }

      set_position();
   }

   set_position = function () {
      jQuery( "#simplemodal-container" ).css( "max-height", "80%" );

      // call modal.setPosition, so that the window height can adjust automatically depending on the displayed fields.
      jQuery.modal.setPosition();
   }

   action_setting = function ( v ) {
      if ( v == "complete" ) {
         wfpath = "success";
      }
      if ( v == "unable" ) {
         wfpath = "failure";
      }

      jQuery( "#message_div" ).hide().html( "" );

      jQuery( "#submitSave" ).show();
      jQuery( "#comments-div" ).show();

      jQuery( "#cancelSave" ).hide();
      jQuery( "#completeSave" ).hide();
      jQuery( "#immediately-div" ).hide();
      if ( jQuery( "#update_publish_msg" ).length ) {
         jQuery( "#update_publish_msg" ).hide();
      }

      jQuery( "#sum_step_info" ).show();

      jQuery( "#step-select" ).find( 'option' ).remove();
      jQuery( "#actor-one-select" ).find( 'option' ).remove();
      jQuery( "#actors-list-select" ).find( 'option' ).remove();
      jQuery( "#actors-set-select" ).find( 'option' ).remove();

      jQuery( "#step-select" ).attr( "disabled", true );
      jQuery( "#actor-one-select" ).attr( "disabled", true );
      jQuery( "#actors-list-select" ).attr( "disabled", true );
      jQuery( "#actors-set-select" ).attr( "disabled", true );

      set_position();
   }
   jQuery( document ).on( "change", "#decision-select", function () {
      var get_action = "";
      var v = jQuery( this ).val();
      action_setting( v );
      if ( !v )
         return;
      data = {
         action: 'get_pre_next_steps',
         oasiswfId: jQuery( "#hi_oasiswf_id" ).val(),
         security: jQuery( '#owf_signoff_ajax_nonce' ).val()
      };
      jQuery( "#sum_step_info" ).css( "opacity", 1 );
      jQuery( "#step-loading-span" ).addClass( "loading" );
      jQuery.ajax( {
         type: "POST",
         url: ajaxurl,
         data: data,
         success: function ( response ) {
            if ( response == -1 ) { // incorrect nonce
               return false;
            }

            jQuery( "#step-loading-span" ).removeClass( "loading" );
            var steps = response.data;
            if ( steps[wfpath] ) {
               jQuery( "#step-select" ).removeAttr( "disabled" );
               add_option_to_select( "step-select", steps[wfpath] );
               var count = 0;
               for ( var steps in steps[wfpath] )
               {
                  count++;
               }
               if ( count == 1 ) {
                  jQuery( "#step-select" ).change();
               }
            } else {
               // wfpath - is set in action_setting() function
               first_last_step_message( wfpath, jQuery( "#hi_oasiswf_id" ).val() );
            }
         },
         error: function ( XMLHttpRequest, textStatus, errorThrown ) {
            alert( "error while loading the steps list" );
         }
      } );
   } );

   jQuery( document ).on( "change", "#step-select", function () {
      stepProcess = "";
      data = {
         action: 'get_users_in_step',
         stepid: jQuery( this ).val(),
         postid: jQuery( "#hi_post_id" ).val(),
         decision: jQuery( "#decision-select" ).val(),
         security: jQuery( '#owf_signoff_ajax_nonce' ).val()
      };

      jQuery( "#actors-list-select" ).find( 'option' ).remove();
      jQuery( "#actors-set-select" ).find( 'option' ).remove();
      jQuery( "#actors-list-select" ).attr( "disabled", true );
      jQuery( "#actors-set-select" ).attr( "disabled", true );

      jQuery( ".assign-loading-span" ).addClass( "loading" );
      jQuery.ajax( {
         type: "POST",
         url: ajaxurl,
         data: data,
         success: function ( response ) {
            if ( response.trim() == -1 ) {
               return false;
            }
            if ( response.trim() == "nodefine" && response.trim() == "No dbdata" )
               return;

            // if response is false then there are no users for given role..!
            if ( response.trim() == 'false' ) {
               jQuery( ".assign-loading-span" ).removeClass( "loading" );
               alert( owf_submit_step_vars.noUsersFound );
               return false;
            }

            jQuery( "#actor-one-select" ).removeAttr( "disabled" );
            jQuery( "#actors-list-select" ).removeAttr( "disabled" );
            jQuery( "#actors-set-select" ).removeAttr( "disabled" );
            var result = { }, users = { };
            if ( response.trim() ) {
               result = JSON.parse( response.trim() );
               if ( typeof result["users"][0] == 'object' ) // no users are defined
               {
                  users = result["users"];
               } else
               {
                  alert( owf_submit_step_vars.noUsersFound );
               }
               stepProcess = result["process"];
            }
            // get assign to all value from the step
            var is_assign_to_all = parseInt( result["assign_to_all"] );

            var get_workflow_team = {
               action: 'get_team_in_workflow',
               post_id: jQuery( "#hi_post_id" ).val(),
               security: jQuery( '#owf_signoff_ajax_nonce' ).val()
            };

            jQuery.post( ajaxurl, get_workflow_team, function ( response ) {
               jQuery( ".assign-loading-span" ).removeClass( "loading" );
               if ( response.trim() != "" ) {
                  jQuery( "#multi-actors-div" ).hide(); // essentially no need to show the user selection, since its submitted to a team
               } else {
                  // if assign_to_all = 1 then hide the assignee box and assign this step to all the users in the choosen role
                  if ( is_assign_to_all === 1 ) {
                     jQuery( '#multi-actors-div' ).hide();
                     jQuery( '<input>' ).attr( {
                        type: 'hidden',
                        id: 'assign_to_all',
                        name: 'assign_to_all',
                        value: is_assign_to_all
                     } ).appendTo( '#new-step-submit-div' );
                  } else {
                     jQuery( "#multi-actors-div" ).show();
                     add_option_to_select( "actors-list-select", users, 'name', 'ID' );
                  }
               }
            } );
            jQuery( "#one-actors-div" ).hide();

         },
         error: function ( XMLHttpRequest, textStatus, errorThrown ) {
            alert( "error while loading the user list" );
         }
      } );
   } );
   //--------------------------------
   jQuery( document ).on( "click", "#assignee-set-point", function () {

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

   jQuery( document ).on( "click", "#assignee-unset-point", function () {
      jQuery( '#actors-set-select option:selected' ).each( function () {
         var v = jQuery( this ).val();
         var t = jQuery( this ).text();
         insert_remove_options( 'actors-set-select', 'actors-list-select', v, t );
      } );
   } );

   var option_exist_chk = function ( val ) {
      if ( jQuery( '#actors-set-select option[value=' + val + ']' ).length > 0 ) {
         return false;
      } else {
         return true;
      }
   }
   //-----------save -------------------
   jQuery( document ).on( "click", "#submitSave", function () {
      var obj = this;
      if ( !sign_off_form_data_check() ) {
         return false;
      }

      jQuery( ".changed-data-set span" ).addClass( "loading" );
      jQuery( "#submitSave" ).hide();

      // now we will validate condition group for signoff process
      var signoff_to_workflow_pre = {
         action: 'sign_off_post_from_workflow_pre',
         post_id: jQuery( "#hi_post_id" ).val(), // this will be required in inbox
         form: jQuery( 'form#post' ).serialize(),
         step_id: jQuery( "#step-select" ).val(),
         decision: jQuery( "#decision-select" ).val(),
         security: jQuery( '#owf_signoff_ajax_nonce' ).val()
      };
      jQuery.post( ajaxurl, signoff_to_workflow_pre, function ( response ) {
         if ( response.trim() != "true" ) {
            jQuery( '#ow-step-messages' ).html( response.trim() );
            jQuery( '#ow-step-messages' ).removeClass( 'owf-hidden' );
            // scroll to the top of the window to display the error messages
            jQuery( ".simplemodal-wrap" ).css( 'overflow', 'hidden' );
            jQuery( ".simplemodal-wrap" ).animate( { scrollTop: 0 }, "slow" );
            jQuery( ".simplemodal-wrap" ).css( 'overflow', 'scroll' );

            jQuery( ".changed-data-set span" ).removeClass( "loading" );
            jQuery( "#submitSave" ).show();
            return false;
         } else {
            after_pre_workflow_sign_off_validation();
         }
      } );

   } );

   after_pre_workflow_sign_off_validation = function ( ) {

      jQuery( ".changed-data-set span" ).addClass( "loading" );
      jQuery( "#submitSave" ).hide();

      var actors = "";

      // Let's first get the team (if any) for the selected task(s)
      var get_workflow_team = {
         action: 'get_team_in_workflow',
         post_id: jQuery( "#hi_post_id" ).val(), // this could be a comma separated list of values too
         security: jQuery( '#owf_signoff_ajax_nonce' ).val()
      };
      jQuery.post( ajaxurl, get_workflow_team, function ( response ) {
         if ( !isNaN( parseInt( response.trim() ) ) ) {
            team_id = response.trim();
            // check if the team has the users with the required role(s)
            var team_has_users = {
               action: 'has_users_in_team',
               team_id: team_id,
               step_id: jQuery( "#step-select" ).val(),
               post_id: jQuery( "#hi_post_id" ).val(),
               security: jQuery( '#owf_signoff_ajax_nonce' ).val()
            };

            jQuery.post( ajaxurl, team_has_users, function ( response ) {
               if ( response.trim() == "false" ) {
                  alert( owf_submit_step_vars.noRoleUsersInTeam );
                  jQuery( ".changed-data-set span" ).removeClass( "loading" );
                  jQuery( "#submitSave" ).show();
                  return false;
               } else {
                  continue_submit_to_step( team_id );
                  return team_id;
               }
            } );
         } else {
            if ( jQuery( "#one-actors-div" ).css( "display" ) == "block" ) {
               if ( !jQuery( "#actor-one-select" ).val() ) {
                  alert( owf_submit_step_vars.noAssignedActors );
                  jQuery( ".changed-data-set span" ).removeClass( "loading" );
                  jQuery( "#submitSave" ).show();
                  return false;
               }
               actors = jQuery( "#actor-one-select" ).val();
               continue_submit_to_step( actors );
            } else {
               var is_assigned_to_all = parseInt( jQuery( '#assign_to_all' ).val() );

               // if assign to all checkbox is checked in step-menu then do not do anything for actors
               if ( is_assigned_to_all === 1 ) {
                  multi_actors = '';
               } else {
                  var optionNum = jQuery( "#actors-set-select option" ).length;
                  if ( !optionNum ) {
                     alert( owf_submit_step_vars.noAssignedActors );
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
               }
               actors = multi_actors;
               continue_submit_to_step( actors, is_assigned_to_all );
            }
         }
      } );
   }

   continue_submit_to_step = function ( actors, is_assigned_to_all ) {
      if ( typeof is_assigned_to_all === 'undefined' ) {
         is_assigned_to_all = 0;
      }
      data = {
         action: 'submit_post_to_step',
         oasiswf: jQuery( "#hi_oasiswf_id" ).val(),
         hi_step_id: jQuery( "#step-select" ).val(),
         hi_actor_ids: actors,
         post_ID: jQuery( "#hi_post_id" ).val(),
         hi_due_date: jQuery( "#due-date" ).val(),
         hi_comment: jQuery( "#workflowComments" ).val(),
         review_result: jQuery( "#decision-select" ).val(),
         hi_task_user: jQuery( "#hi_task_user" ).val(),
         security: jQuery( '#owf_signoff_ajax_nonce' ).val(),
         assign_to_all: is_assigned_to_all,
         priority: jQuery( "#priority-select" ).val(),
      };
      jQuery( document ).find( "#step_submit" ).remove();
      jQuery.post( ajaxurl, data, function ( response ) {
         if (response == -1) {
            return false; // Invalid nonce
         }
         jQuery( ".changed-data-set span" ).removeClass( "loading" );
         if ( jQuery( "#hi_parrent_page" ).val() == "inbox" ) {
            location.reload();
         } else {
            var step_status_data = {
               action: 'get_post_status_by_history_id',
               to_step_id: jQuery("#step-select").val(),
               old_history_id: jQuery("#hi_oasiswf_id").val(),
               security: jQuery('#owf_signoff_ajax_nonce').val()
            };
            jQuery.post(ajaxurl, step_status_data, function (response) {
               if (response == -1) {
                  return false; // Invalid nonce
               }
               jQuery("#post_status").val(response.data);
               jQuery("#save-post").click();
               modal_close();
            });
         }
         return false;
      } );
   }

   sign_off_form_data_check = function () {
      if ( !jQuery( "#decision-select" ).val() ) {
         alert( owf_submit_step_vars.decisionSelectMessage );
         return false;
      }

      if ( !jQuery( "#step-select" ).val() ) {
         alert( owf_submit_step_vars.selectStep );
         return false;
      }
      /* This is for checking that reminder email checkbox is selected in workflow settings.
       If YES then Due Date is Required Else Not */
      if ( owf_submit_step_vars.drdb != "" || owf_submit_step_vars.drda != "" || owf_submit_step_vars.defaultDueDays != "" )
      {
         if ( jQuery( "#due-date" ).val() == '' ) {
            alert( owf_submit_step_vars.dueDateRequired );
            return false;
         }
         if ( !chk_due_date( "due-date", owf_submit_step_vars.dateFormat ) ) {
            return false;
         }
      }

      return true;
   }

   assign_actor_chk = function () {
      var get_workflow_team = {
         action: 'get_team_in_workflow',
         post_id: jQuery( "#hi_post_id" ).val(), // this could be a comma separated list of values too
         security: jQuery( '#owf_signoff_ajax_nonce' ).val()
      };
      jQuery.post( ajaxurl, get_workflow_team, function ( response ) {
         if ( !isNaN( parseInt( response.trim() ) ) ) {
            team_id = response.trim();
            // check if the team has the users with the required role(s)
            var team_has_users = {
               action: 'has_users_in_team',
               team_id: team_id,
               step_id: jQuery( "#step-select" ).val(),
               post_id: jQuery( "#hi_post_id" ).val(),
               security: jQuery( '#owf_signoff_ajax_nonce' ).val()
            };

            jQuery.post( ajaxurl, team_has_users, function ( response ) {
               if ( response.trim() == "false" ) {
                  alert( owf_submit_step_vars.noRoleUsersInTeam );
                  jQuery( ".changed-data-set span" ).removeClass( "loading" );
                  jQuery( "#submitSave" ).show();
                  return false;
               } else {
                  return team_id;
               }
            } );
         } else {
            if ( jQuery( "#one-actors-div" ).css( "display" ) == "block" ) {
               if ( !jQuery( "#actor-one-select" ).val() ) {
                  alert( owf_submit_step_vars.noAssignedActors );
                  jQuery( ".changed-data-set span" ).removeClass( "loading" );
                  jQuery( "#submitSave" ).show();
                  return false;
               }
               return jQuery( "#actor-one-select" ).val();
            } else {
               var optionNum = jQuery( "#actors-set-select option" ).length;
               if ( !optionNum ) {
                  alert( owf_submit_step_vars.noAssignedActors );
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
         }
      } );
   }

   //--------complete workflow------------
   jQuery( document ).on( "click", "#immediately-chk", function () {
      if ( jQuery( this ).attr( "checked" ) == "checked" ) {
         jQuery( "#immediately-span" ).hide();
      } else {
         jQuery( "#immediately-span" ).show();
      }
   } );

   jQuery( document ).on( "click", "#completeSave", function () {
      var im_date = "";
      if ( jQuery( "#immediately-span" ).length > 0 && jQuery( "#immediately-span" ).is( ':visible' ) )
      {
         if ( isNaN( jQuery( "#im-year" ).val() ) ) {
            jQuery( "#im-year" ).css( "background-color", "#fadede" );
            return;
         }
         if ( isNaN( jQuery( "#im-day" ).val() ) ) {
            jQuery( "#im-day" ).css( "background-color", "#fadede" );
            return;
         }
         if ( isNaN( jQuery( "#im-hh" ).val() ) ) {
            jQuery( "#im-hh" ).css( "background-color", "#fadede" );
            return;
         }
         if ( isNaN( jQuery( "#im-mn" ).val() ) ) {
            jQuery( "#im-mn" ).css( "background-color", "#fadede" );
            return;
         }

         im_date = jQuery( "#im-year" ).val() + "-" + jQuery( "#im-mon" ).val() + "-" + jQuery( "#im-day" ).val() + " " + jQuery( "#im-hh" ).val() + ":" + jQuery( "#im-mn" ).val() + ":00";
      }

      data = {
         action: 'change_workflow_status_to_complete',
         oasiswf_id: jQuery( "#hi_oasiswf_id" ).val(),
         post_id: jQuery( "#hi_post_id" ).val(),
         immediately: im_date,
         security: jQuery( '#owf_signoff_ajax_nonce' ).val()
      };
      jQuery( ".changed-data-set span" ).addClass( "loading" );
      jQuery( this ).hide();
      jQuery.post( ajaxurl, data, function ( response ) {
         if ( response == -1 ) { // incorrect nonce
            return false;
         }
         var workflow_status = response.data.workflow_status; // whether its the original post or revised post which is getting completed.
         workflow_status = workflow_status.toLowerCase(); // make string Lowercase for case-insensitive search

         var new_post_status = response.data.post_status;
         jQuery( document ).find( "#step_submit" ).remove();

         /*
          * changing the post status to draft from "currentrev" on the revised post will trigger the currentrev_to_draft action
          * which will copy over the contents of the revision to the published post.
          * We need to do so, since even in the last step the revised post can be updated.
          * We need to copy over the most updated revised post
          */
         if ( workflow_status.indexOf( 'complete_revision' ) >= 0 ) { // if its a revision post, then change the status to draft
            if ( jQuery( "#hi_parrent_page" ).val() == "inbox" ) {
               new_post_status = "draft";
               var update_post_status = {
                  action: 'schedule_or_publish_revision',
                  post_id: jQuery( "#hi_post_id" ).val(),
                  post_status: new_post_status,
                  immediately: im_date,
                  security: jQuery( '#owf_signoff_ajax_nonce' ).val()
               };
               // we need to update the post_status on the revision to draft in order for the action to trigger.
               jQuery.post( ajaxurl, update_post_status, function ( response ) {
                  if ( response.trim() == -1 ) {
                     return false;
                  }
                  jQuery( ".changed-data-set span" ).removeClass( "loading" );
                  // reload the inbox page
                  location.reload();
               } );
            } else { // from the edit post page
               var update_post_status = {
                  action: 'schedule_or_publish_revision',
                  post_id: jQuery( "#hi_post_id" ).val(),
                  post_status: "currentrev",
                  immediately: im_date,
                  security: jQuery( '#owf_signoff_ajax_nonce' ).val()
               };
               jQuery.post( ajaxurl, update_post_status, function ( response ) {
                  if ( response.trim() == -1 ) {
                     return false;
                  }
                  jQuery( ".changed-data-set span" ).removeClass( "loading" );
                  modal_close();
                  jQuery( "#post_status" ).val( "future" ); //scheduled update
                  jQuery( "#save-post" ).click();
               } );
            }
         } else if ( workflow_status.indexOf( 'complete_original' ) >= 0 ) { //if its the original post, simply change the status to the one from the workflow
            if ( jQuery( "#hi_parrent_page" ).val() == "inbox" ) {
               // we need to update the post_status on the revision to draft in order for the action to trigger.
               var update_post_status = {
                  action: 'change_status_to_owf_status',
                  post_id: jQuery( "#hi_post_id" ).val(),
                  post_status: new_post_status,
                  security: jQuery( '#owf_signoff_ajax_nonce' ).val()
               };
               jQuery.post( ajaxurl, update_post_status, function ( response ) {
                  if ( response.trim() == -1 ) {
                     return false;
                  }
                  jQuery( ".changed-data-set span" ).removeClass( "loading" );
                  // reload the inbox page
                  location.reload();
               } );
            } else { // from the edit post page
               jQuery( ".changed-data-set span" ).removeClass( "loading" );
               modal_close();
               jQuery( "#post_status" ).val( new_post_status );
               jQuery( "#save-post" ).click();
               modal_close();
            }
         }
      } );
   } );

   jQuery( ".immediately" ).keydown( function () {

      jQuery( this ).css( "background-color", "#ffffff" );
   } );

   //--------complate------------
   jQuery( document ).on( "click", "#cancelSave", function () {
      var obj = this;
      data = {
         action: 'change_workflow_status_to_cancelled',
         oasiswf_id: jQuery( "#hi_oasiswf_id" ).val(),
         post_id: jQuery( "#hi_post_id" ).val(),
         hi_comment: jQuery( "#workflowComments" ).val(),
         review_result: jQuery( "#decision-select" ).val(),
         security: jQuery( '#owf_signoff_ajax_nonce' ).val()
      };

      jQuery( ".changed-data-set span" ).addClass( "loading" );
      jQuery( this ).hide();
      jQuery.post( ajaxurl, data, function ( response ) {
         if ( response.trim() == -1 ) { // nonce check failed
            return false;
         }
         jQuery( ".changed-data-set span" ).removeClass( "loading" );
         jQuery( document ).find( "#step_submit" ).remove();
         if ( jQuery( "#hi_parrent_page" ).val() == "inbox" ) {
            location.reload();
         } else {
            modal_close();
            location.reload();
         }
      } );
   } );

   jQuery( document ).on( "click", "#claimButton", function () {
      var claim = jQuery( this );
      data = {
         action: 'claim_process',
         actionid: jQuery( "#hi_oasiswf_id" ).val().trim(),
         security: jQuery( '#owf_claim_process_ajax_nonce' ).val()
      };

      jQuery( this ).parent().children( ".loading" ).show();
      jQuery.post( ajaxurl, data, function ( response ) {
         if ( response.trim() == -1 ) {
            claim.parent().children( ".loading" ).hide();
            return false;
         }
         if ( response.trim() )
            jQuery( "#hi_oasiswf_id" ).val( response.trim() );
         location.reload();
      } );
   } );

   function signOffSubmit() {
      jQuery( '#hi_oasiswf_redirect' ).val( "step" );
      jQuery( "#new-step-submit-div" ).owfmodal( {
         onShow: function ( dlg ) {
            jQuery( "#simplemodal-container" ).css( "max-height", "80%" );
            jQuery( dlg.wrap ).css( 'overflow', 'auto' ); // or try ;
            jQuery.modal.update();
            if ( owf_submit_step_vars.workflowTeamsAvailable == 'yes' ) { // essentially no need to show the user selection, since its submitted to a team
               jQuery( "#multi-actors-div" ).hide();
            } else {
               jQuery( "#multi-actors-div" ).show();
            }
         }
      } );
      wfpath = "";
      stepProcess = "";
      calendar_action();
   }

} );