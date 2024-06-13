<?php
/*
Plugin Name: Delete Account Request
Description: This is a plugin for delete account requests.
Version: 1.0 
Author: Mr D
*/
add_shortcode('delete_account', 'delete_account_request');
function delete_account_request(){
    global $wpdb;
    $items_per_page = 3;
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM `deleteaccount`");
    $current_page = max(1, get_query_var('paged'));
    $offset = ($current_page - 1) * $items_per_page;
    $results = $wpdb->get_results("SELECT * FROM `deleteaccount` LIMIT $items_per_page OFFSET $offset", ARRAY_A);
?>
    <h1>Delete Account Request</h1>
    <form id="deleteAccountForm" method="POST" action="">
        <label for="username">User Name:</label>
        <input type="text" name="username" id="username" required><br><br>
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br><br>
        <label for="phone">Phone:</label>
        <input type="text" name="phone" id="phone" required><br><br>
        <label for="address">Address:</label>
        <input type="text" name="address" id="address" required><br><br>
        <input type="hidden" id="status" name="status" value="1"><br><br>
        <button type="submit" name="submit" id="submit">Submit</button>
    </form>
    <h2>Table</h2>
    <table>
        <thead>
            <tr>
                <th>Username:</th>
                <th>Email:</th>
                <th>Phone:</th>
                <th>Address:</th> 
                <th>Select Box:</th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach($results as $rows){
            $username = $rows['username'];
            $email = $rows['email'];
            $phone = $rows['phone'];
            $address = $rows['address'];
            $id = $rows['id'];
            ?>
            <tr>
                <td><?php echo $username; ?></td>
                <td><?php echo $email; ?></td>
                <td><?php echo $phone; ?></td>
                <td><?php echo $address; ?></td>
                <td>
                    <select name="select" class="select-status" data-id="<?php echo $id; ?>">
                        <option value="1" <?php selected($rows['status'], 1); ?>>Pending</option>
                        <option value="2" <?php selected($rows['status'], 2); ?>>Delete</option>
                        <option value="3" <?php selected($rows['status'], 3); ?>>Approved</option>
                    </select>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <?php
    $total_pages = Ceil($total_items / $items_per_page);
    if ($total_pages > 1) {
        echo '<div class="pagination">';
        echo paginate_links(array(
            'base' => get_pagenum_link(1) . '%_%',
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
    jQuery(document).ready(function($){
        $('#deleteAccountForm').submit(function(e){
            e.preventDefault();
            var formData = {
                'action': 'custom_account',
                'username': $('#username').val(),
                'email': $('#email').val(),
                'phone': $('#phone').val(),
                'address': $('#address').val(),
                'status': $('#status').val()
            };
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData,
                success: function (response) {
                    if(response.success) {
                        alert(response.data);
                        location.reload();
                    } else {
                        alert(response.data);
                    }
                },
                error: function(response) {
                    alert('There was an error processing the request.');
                }
            });
        });
        $('.select-status').change(function(){
            var status = $(this).val();
            var id = $(this).data('id');
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    'action': 'change_status',
                    'status': status,
                    'id': id
                },
                success: function (response) {
                    if(response.success) {
                        alert(response.data);
                        location.reload();
                    } else {
                        alert(response.data);
                    }
                },
                error: function(response) {
                    alert('There was an error processing the request.');
                }
            });
        });
    });
    </script>
    <?php
}
add_action('wp_ajax_change_status', 'change_status');
add_action('wp_ajax_nopriv_change_status', 'change_status');
function change_status(){
    if(isset($_POST['id']) && isset($_POST['status'])){
        global $wpdb;
        $id = intval($_POST['id']);
        $status = intval($_POST['status']);
        $result = $wpdb->update('deleteaccount', array('status' => $status), array('id' => $id));
        if($result !== false){
            wp_send_json_success("Status updated successfully");
        } else {
            wp_send_json_error("Failed to update status");
        }
    } else {
        wp_send_json_error("Invalid data provided");
    }
}
add_action('wp_ajax_custom_account', 'custom_account');
add_action('wp_ajax_nopriv_custom_account', 'custom_account');
function custom_account(){
    if(isset($_POST['username']) && isset($_POST['email']) && isset($_POST['phone']) && isset($_POST['address'])){
        global $wpdb;
        $username = sanitize_text_field($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $address = sanitize_text_field($_POST['address']);
        $status = intval($_POST['status']); 
        
        $result = $wpdb->insert('deleteaccount', array(
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'status' => $status
        ));
        if($result){
            wp_send_json_success("Data inserted successfully");
        } else {
            wp_send_json_error("Data not inserted!");
        }
    } else {
        wp_send_json_error("Invalid data provided");
    }
}
?>
