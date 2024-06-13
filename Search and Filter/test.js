jQuery(document).ready(function() {
jQuery('#submit').click(function(e) {
    e.preventDefault();
        var name= jQuery('#name').val(); 
        var email = jQuery('#email').val();
        var address = jQuery('#address').val();
        var date = jQuery('#date').val();
        
        if (name === '' || email === '' || address === '' || date === '') {
            alert('Please fill all fields');
            return;
        } else {
            var formData = new FormData();
            formData.append('name', name);
            formData.append('email', email);
            formData.append('address', address);
            formData.append('date', date);
            formData.append('action', 'file_ayushi');
            
            jQuery.ajax({
                url: frontend_script.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                        alert('Data saved successfully');
                }
            });
        }
    }); 
});
jQuery('.del').click(function(e) {
    e.preventDefault();
    var confirmdelete = confirm('Are you sure you want to delete this item?');
    if (confirmdelete) {
        var id = jQuery(this).data('id');
        jQuery.ajax({
            url: frontend_script.ajaxUrl,
            data: {
                id: id,
                action: 'del_ayushi'
            },
            success: function(response) {
                if (response) {
                    alert('data deleted successfully');
                  location.reload();
                } else {
                    alert('data not deleted' + response.data);
                }
            }
        });
    }
});
jQuery('#update').click(function(e) {
    e.preventDefault();
    var id = jQuery(this).data('id');
    var data = {
        name: jQuery('#name').val(),
        email: jQuery('#email').val(),
        address: jQuery('#address').val(),
        date: jQuery('#date').val(),
        id: id,
    }
    jQuery.ajax({
        url: frontend_script.ajaxUrl,
        type: 'POST',
        data: {
            edit_data: data,
            action: 'edit_ayushi'
        },
        success: function(response) {
            if (response.success) {
                alert('data edited successfully');
                location.reload();
            } else {
                alert('data not edited' + response.data);
            }
        }
    });
});

        
    
        
