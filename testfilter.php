<?php
/*
Plugin Name: Testing for filter
Description: This is for the searching process.
Version: 1.0 
Author: Mr filter
*/
add_shortcode('filter_testing', 'filter_for_testing');
function filter_for_testing() {
    ?>
    <h1>Filter Form</h1>
    <form id="filter-form" method="POST" action="">
        <label for="username">User Name:</label>
        <input type="text" name="username" id="username" required><br><br>
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br><br>
        <label for="phone">Phone:</label>
        <input type="text" name="phone" id="phone" required><br><br>
        <label for="status">Status:</label>
        <input type="text" name="status" id="status" required><br><br>
        <button type="submit" name="submit" id="submit">Submit</button>
    </form>
    <br><br>
    <div>
        <label for="filter">Filter:</label>
        <select name="filter" id="filter">
            <option value="">All</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
        </select>
        <button class="filter">Filter</button>
    </div>
    <br><br>
    <h2>Table</h2>
    <table>
        <thead>
            <tr>
                <th>User Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="table-body">
        </tbody>
    </table>
    <script>
        jQuery(document).ready(function() {
            jQuery('.filter').click(function(e) {
                e.preventDefault();
                var status = jQuery('#filter').val();
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        filter: status,
                        action: 'filter_page'
                    },
                    success: function(response) {
                        if (response.success) {
                            jQuery('#table-body').html(response.data);
                            alert(status.charAt(0).toUpperCase() + status.slice(1));
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }
                });
            });
        });
    </script>
    <?php
}
add_action('wp_ajax_insert_data', 'insert_data');
add_action('wp_ajax_nopriv_insert_data', 'insert_data');
function insert_data() {
    if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['phone']) && isset($_POST['status'])) {
        global $wpdb;
        $username = sanitize_text_field($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $status = sanitize_text_field($_POST['status']);

        $result = $wpdb->insert('testingfilter', array(
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'status' => $status
        ));
        if ($result) {
            wp_send_json_success("Data inserted successfully");
        } else {
            wp_send_json_error("Data not inserted!");
        }
    } else {
        wp_send_json_error("Invalid data provided");
    }
}
add_action('wp_ajax_filter_page', 'filter_page');
add_action('wp_ajax_nopriv_filter_page', 'filter_page');
function filter_page() {
    global $wpdb;
    $filter = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : '';
    $query = "SELECT * FROM `testingfilter`";
    if ($filter) {
        $query .= $wpdb->prepare(" WHERE status = %s", $filter);
    }
    $results = $wpdb->get_results($query, ARRAY_A);
    if ($results) {
        ob_start();
        foreach ($results as $rows) {
            ?>
            <tr>
                <td><?php echo esc_html($rows['username']); ?></td>
                <td><?php echo esc_html($rows['email']); ?></td>
                <td><?php echo esc_html($rows['phone']); ?></td>
                <td><?php echo esc_html($rows['status']); ?></td>
            </tr>
            <?php
        }
        $data = ob_get_clean();
        wp_send_json_success($data);
    } else {
        wp_send_json_error("No data found");
    }
}
?>
