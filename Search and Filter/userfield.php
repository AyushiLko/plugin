<?php
/*
Plugin Name: Users Input Field
Description: This is the field for the data input for table
Version: 1.0
Author: Mr Gupta
*/
add_action('admin_menu', 'user_input_field');
function user_input_field() {
    add_menu_page('Input Field', 'Input Field', 'manage_options', 'page_users', 'page_user_details');
    add_submenu_page('page_users', 'Input Table', 'Input Table', 'manage_options', 'data_input', 'data_input_field');
}
function page_user_details() {
    global $wpdb;
    if (is_user_logged_in()) {
        $current_user_id = get_current_user_id();
        $user_data = wp_get_current_user();
        $name = get_user_meta($current_user_id, 'name', true) ?: $user_data->display_name;
        $user_email = $user_data->user_email;
        $username = $user_data->user_nicename;

        $user_id = $current_user_id;
        $is_edit = false;
        if (isset($_GET['id']) && intval($_GET['id']) > 0) {
            $user_id = intval($_GET['id']);
            $user_data = get_userdata($user_id);
            $name = get_user_meta($user_id, 'name', true) ?: $user_data->display_name;
            $user_email = $user_data->user_email;
            $username = $user_data->user_nicename;
            $is_edit = true;
        }

        if (isset($_POST['submit'])) {
            $name = sanitize_text_field($_POST['name']);
            $email = sanitize_email($_POST['user_email']);
            $password = sanitize_text_field($_POST['user_pass']);
            $nicename = sanitize_text_field($_POST['user_nicename']);
            $login_name = sanitize_user($_POST['user_login']);

            $user = array(
                'user_login' => $login_name,
                'user_nicename' => $nicename,
                'user_email' => $email
            );
            if (!empty($password)) {
                $user['user_pass'] = $password;
            }
            if ($is_edit) {
                $user['ID'] = $user_id;
                $update_result = wp_update_user($user);
                if (is_wp_error($update_result)) {
                    echo 'Error updating user: ' . $update_result->get_error_message();
                } else {
                    update_user_meta($user_id, 'name', $name);
                    handle_user_image_upload($user_id);
                    wp_redirect(admin_url('admin.php?page=page_users'));
                    exit;
                }
            } else {
                $user_id = wp_insert_user($user);
                if (is_wp_error($user_id)) {
                    echo 'Error creating user: ' . $user_id->get_error_message();
                } else {
                    update_user_meta($user_id, 'name', $name);
                    handle_user_image_upload($user_id);
                    wp_redirect(admin_url('admin.php?page=page_users'));
                    exit;
                }
            }
        }
    } else {
        echo 'User must be logged in';
        return;
    }
    ?>
    <h1>Users Form</h1>
    <div>
        <form method="post" enctype="multipart/form-data">
            <?php
            $attachment_id = get_user_meta($user_id, 'attachment_id', true);
            $image_url = $attachment_id ? wp_get_attachment_url($attachment_id) : '';
            ?>
            <div style="width: 140px; height: 140px; border-radius: 100%; overflow: hidden; position: relative;">
                <img src="<?php echo esc_url($image_url); ?>" alt="No image" />
            </div><br>
            <label for="image">Users Image</label>
            <input type="file" name="image"><br><br>
            <label for="name">Name:</label>
            <input type="text" name="name" value="<?php echo esc_attr($is_edit ? $name : ''); ?>"><br><br>
            <label for="user_login">User Login:</label>
            <input type="text" name="user_login" value="<?php echo esc_attr($is_edit ? $username : ''); ?>"><br><br>
            <label for="user_nicename">User nicename:</label>
            <input type="text" name="user_nicename" value="<?php echo esc_attr($is_edit ? $username : ''); ?>"><br><br>
            <label for="user_email">User Email:</label>
            <input type="email" name="user_email" value="<?php echo esc_attr($is_edit ? $user_email : ''); ?>"><br><br>
            <label for="user_pass">User Password:</label>
            <input type="password" name="user_pass" value=""><br><br>
            <input type="submit" name="submit" value="<?php echo $is_edit ? 'Update user' : 'Create user'; ?>"><br><br><br>
        </form>
    </div>
    <?php
}
function handle_user_image_upload($user_id) {
    if (!empty($_FILES['image']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');

        $attachment_id = media_handle_upload('image', 0);
        if (is_wp_error($attachment_id)) {
            echo 'Error uploading image: ' . $attachment_id->get_error_message();
        } else {
            update_user_meta($user_id, 'attachment_id', $attachment_id);
        }
    }
}
function data_input_field() {
    global $wpdb;
    ?>
    <h2>Table</h2>
    <table class="wp_list_table widefat fixed striped">
        <thead>
            <tr>
                <th>Users Image</th>
                <th>Username</th>
                <th>Email</th>
                <th>Name</th>
                <th>Update</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $wpdb->get_results("SELECT * FROM wp_users", ARRAY_A);
            foreach ($result as $row) {
                $user_id = $row['ID'];
                $name = get_user_meta($user_id, 'name', true) ?: $row['display_name'];
                $attachment_id = get_user_meta($user_id, 'attachment_id', true);
                $image_url = wp_get_attachment_url($attachment_id);
                ?>
                <tr>
                    <td>
                        <img src="<?php echo esc_url($image_url); ?>" alt="no image" width="100" height="100">
                    </td>
                    <td><?php echo esc_html($row['user_nicename']); ?></td>
                    <td><?php echo esc_html($row['user_email']); ?></td>
                    <td><?php echo esc_html($name); ?></td>
                    <td><a href="<?php echo admin_url('admin.php?page=page_users&id=' . $user_id); ?>" class="btn btn-success">Update</a></td>
                    <td><a href="<?php echo admin_url('admin.php?page=data_input&delete=' . $user_id); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <?php
    if (isset($_GET['delete'])) {
        $delete_user_id = intval($_GET['delete']);
        if ($delete_user_id > 0) {
            require_once(ABSPATH . 'wp-admin/includes/user.php');
            wp_delete_user($delete_user_id);
            wp_redirect(admin_url('admin.php?page=data_input'));
            exit;
        }
    }
}
?>
