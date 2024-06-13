<?php
/*
Plugin Name: Practise Ajax Crud
Version:1.0 
Description: For Practise the crud operation through 
Author:W
*/

add_action('admin_menu', 'pagination_search_menu');
function pagination_search_menu() {
    add_menu_page('Page Menu', 'Page Menu', 'manage_options', 'pagination-post', 'pagination_post_menu');
}

function pagination_post_menu() {
    global $wpdb;
    $items_per_page = 3;
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM `wp_ajaxcrud`");
    $current_page = max(1, isset($_GET['paged']) ? intval($_GET['paged']) : 1);
    $offset = ($current_page - 1) * $items_per_page;
    $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM `wp_ajaxcrud` LIMIT %d OFFSET %d", $items_per_page, $offset), ARRAY_A);
?>
    <div>
        <form method="post">
            <label for="name">Name:</label>
            <input type="text" name="name" id="name"></br></br>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email"></br></br>
            <label for="address">Address:</label>
            <input type="text" name="address" id="address"></br></br>
            <label for="date">Date:</label>
            <input type="date" name="date" id="date"></br></br>
            <input type="submit" id="submit" class="submit" value="submit"></br></br>
            <input type="submit" id="update" class="update" value="update" style="display: none;">
        </form>
    </div>
    <table class="wp_list_table widefat fixed stripped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Address</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="table-body">
            <?php
            if ($result) {
                foreach ($result as $row) {
            ?>
                    <tr data-id="<?php echo $row['id']; ?>">
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['address']; ?></td>
                        <td><?php echo $row['date']; ?></td>
                        <td>
                            <button type="submit" class="edit" data-id="<?php echo $row['id']; ?>">Edit</button>
                            <button type="submit" class="delete" data-id="<?php echo $row['id']; ?>">Delete</button>
                        </td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
    <?php
    $total_pages = ceil($total_items / $items_per_page);
    if ($total_pages > 1) {
        echo '<div class="pagination">';
        echo paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '?paged=%#%',
            'current' => $current_page,
            'total' => $total_pages,
            'prev_text' => __('<< prev'),
            'next_text' => __('next >>'),
        ));
        echo '</div>';
    }
    ?>
    <script>
       jQuery(document).ready(function() {
            jQuery('#submit').click(function(e) {
                e.preventDefault();
                var $name = jQuery('#name').val();
                var $email = jQuery('#email').val();
                var $address = jQuery('#address').val();
                var $date = jQuery('#date').val();
                
                if ($name === '' || $email === '' || $address === '' || $date === '') {
                    alert('Please fill all fields');
                    return;
                } else {
                    var formData = new FormData();
                    formData.append('name', $name);
                    formData.append('email', $email);
                    formData.append('address', $address);
                    formData.append('date', $date);
                    formData.append('action', 'add_ayushi');
                    
                    jQuery.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            alert('Data saved successfully');
                            load_data();
                        }
                    });
                }
            }); 

            jQuery('.delete').click(function(e) {
                e.preventDefault();
                var confirmdelete = confirm('Are you sure you want to delete this item?');
                if (confirmdelete) {
                    var id = jQuery(this).data('id');
                    jQuery.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        data: {
                            id: id,
                            action: 'delete_ayushi'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('data deleted successfully');
                                load_data();
                            } else {
                                alert('data not deleted' + response.data);
                            }
                        }
                    });
                }
            });

            jQuery('.edit').click(function(e) {
                e.preventDefault();
                $id = jQuery(this).data('id');
                $name = jQuery(this).closest('tr').find('td:eq(0)').text();
                $email = jQuery(this).closest('tr').find('td:eq(1)').text();
                $address = jQuery(this).closest('tr').find('td:eq(2)').text();
                $date = jQuery(this).closest('tr').find('td:eq(3)').text();

                jQuery('#name').val($name);
                jQuery('#email').val($email);
                jQuery('#address').val($address);
                jQuery('#date').val($date);
                jQuery('.submit').hide();
                jQuery('.update').show().data('id', $id);
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
                };
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        edit_data: data,
                        action: 'edit_ayushi'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('data edited successfully');
                            load_data();
                        } else {
                            alert('data not edited' + response.data);
                        }
                    }
                });
            });

            function load_data(paged = 1) {
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'show_ayushi',
                        paged: paged
                    },
                    success: function(response) {
                        if (response) {
                            jQuery('#table-body').html(response);
                        } else {
                            alert('data not found');
                        }
                    }
                });
            }
        });
    </script>
    <?php
}

add_action('wp_ajax_add_ayushi', 'add_ayushi');
function add_ayushi() {
    if (isset($_POST['name'])) {
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $address = sanitize_text_field($_POST['address']);
        $date = sanitize_text_field($_POST['date']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ajaxcrud';
        $result = $wpdb->insert($table_name, array(
            'name' => $name,
            'email' => $email,
            'address' => $address,
            'date' => $date,
        ));
        if ($result) {
            wp_send_json_success('data inserted successfully');
        } else {
            wp_send_json_error('data not inserted');
        }
    }
    wp_die();
}

add_action('wp_ajax_delete
_ayushi', 'delete_ayushi');
function delete_ayushi() {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        global $wpdb;
        $table_name = $wpdb->prefix . 'ajaxcrud';
        $result = $wpdb->delete($table_name, array('id' => $id));
        if ($result) {
            wp_send_json_success('data deleted successfully');
        } else {
            wp_send_json_error('data not deleted');
        }
    }
    wp_die();
}

add_action('wp_ajax_edit_ayushi', 'edit_ayushi');
function edit_ayushi() {
    if (isset($_POST['edit_data'])) {
        $edit_value = $_POST['edit_data'];
        $id = intval($edit_value['id']);
        $name = sanitize_text_field($edit_value['name']);
        $email = sanitize_email($edit_value['email']);
        $address = sanitize_text_field($edit_value['address']);
        $date = sanitize_text_field($edit_value['date']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ajaxcrud';
        $result = $wpdb->update(
            $table_name,
            array(
                'name' => $name,
                'email' => $email,
                'address' => $address,
                'date' => $date,
            ),
            array('id' => $id)
        );
        if ($result !== false) {
            wp_send_json_success('data updated successfully');
        } else {
            wp_send_json_error('data not updated');
        }
    }
    wp_die();
}

add_action('wp_ajax_show_ayushi', 'show_ayushi');
function show_ayushi() {
    global $wpdb;
    $items_per_page = 3;
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $offset = ($paged - 1) * $items_per_page;

    $table_name = $wpdb->prefix . 'ajaxcrud';
    $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name LIMIT %d OFFSET %d", $items_per_page, $offset), ARRAY_A);

    if ($result) {
        foreach ($result as $row) {
            ?>
            <tr data-id="<?php echo $row['id']; ?>">
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['address']; ?></td>
                <td><?php echo $row['date']; ?></td>
                <td>
                    <button type="submit" class="edit" data-id="<?php echo $row['id']; ?>">Edit</button>
                    <button type="submit" class="delete" data-id="<?php echo $row['id']; ?>">Delete</button>
                </td>
            </tr>
            <?php
        }
    }
    wp_die();
}
