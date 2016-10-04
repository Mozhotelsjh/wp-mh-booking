jQuery(function(){
    jQuery('.sola_nl_sub_form').submit(function(event) {
        event.preventDefault();
        if(jQuery.trim(jQuery('[name="sub_email"]').val())==='')
        {
            alert('Please enter your e-mail address.');
        }
        else
        {
            var div = "#".concat(jQuery(this).parent().attr("id"));
            var form = jQuery(this).serialize();
            jQuery.ajax({
                url:ajaxurl,
                type:"POST",
                data: form,
                beforeSend: function(){
                    jQuery(div).empty().append('<p><img width="25px"  src="wp-content/plugins/sola-newsletters/images/loading.gif" /> Subscribing...</p>');
                },
                error: function(jqXHR, exception) {
                    if (jqXHR.status === 0) {
                        alert('Not connect.\n Verify Network.');
                    } else if (jqXHR.status == 404) {
                        alert('Requested page not found. [404]');
                    } else if (jqXHR.status == 500) {
                        alert('Internal Server Error [500].');
                    } else if (exception === 'parsererror') {
                        alert('Requested JSON parse failed.');
                    } else if (exception === 'timeout') {
                        alert('Time out error.');
                    } else if (exception === 'abort') {
                        alert('Ajax request aborted.');
                    } else {
                        alert('Uncaught Error.\n' + jqXHR.responseText);
                    }
                },
                success: function(response){
                    jQuery(div).empty().append(response);
                }

            });

        }
    });
    
    
    
    jQuery('#ddl_lists_widget').unbind('change').on('change',function()
    {
        var list_id=jQuery(this).val();
        if(list_id!=='')
        {
            manipulate_form.show_list_description(list_id);
        }
        else
        {
            manipulate_form.hide_all_lists();
        }
    });
    
});

var manipulate_form={
    show_list_description:function(id)
    {
        this.hide_all_lists();
        jQuery('#list_description_display_'+id).attr('style','display:block;');
    },
    hide_all_lists:function()
    {
        jQuery('.hidden_list_descriptions').attr('style','display:none;');
    }
};




