<?php
/*
Plugin Name: For the User Data
Description: Data used for the user purpose.
Version: 1.1
Author: Mr User
*/

add_action('admin_menu', 'for_user_data');
function for_user_data() {
    add_menu_page('For Users', 'For Users', 'manage_options', 'users_profile', 'users_profile_data');
}

function users_profile_data() {
    global $wpdb;

    if (isset($_POST['submit'])) {
        $username = sanitize_text_field($_POST['username']);
        $useremail = sanitize_email($_POST['useremail']);
        $userpassword = sanitize_text_field($_POST['userpassword']);
        $userdate = sanitize_text_field($_POST['userdate']);
        $userimage = $_FILES['userimage'];

        if (isset($_POST['user_id']) && $_POST['user_id'] != '') {
            // Update existing user
            $user_id = intval($_POST['user_id']);
            wp_update_user(array(
                'ID' => $user_id,
                'user_login' => $username,
                'user_pass' => $userpassword,
                'user_email' => $useremail,
            ));

            update_user_meta($user_id, 'userdate', $userdate);

            if (!empty($userimage['name'])) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');

                $attachment_id = media_handle_upload('userimage', 0);

                if (!is_wp_error($attachment_id)) {
                    update_user_meta($user_id, 'attachment_id', $attachment_id);
                } else {
                    echo 'Failed to upload image';
                }
            }
            echo 'User updated successfully';
        } else {
            // Create new user
            $user_id = wp_create_user($username, $userpassword, $useremail);

            if (!is_wp_error($user_id)) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');

                $attachment_id = media_handle_upload('userimage', 0);

                if (!is_wp_error($attachment_id)) {
                    update_user_meta($user_id, 'userdate', $userdate);
                    update_user_meta($user_id, 'attachment_id', $attachment_id);
                    echo 'User created successfully';
                } else {
                    echo 'Failed to upload image';
                }
            } else {
                echo 'Failed to create user: ' . $user_id->get_error_message();
            }
        }
    }
    if (isset($_GET['delete_user'])) {
        $user_id = intval($_GET['delete_user']);
        require_once(ABSPATH . 'wp-admin/includes/user.php');
        wp_delete_user($user_id);
        echo 'User deleted successfully';
    }

    $edit_user = null;
    if (isset($_GET['edit_user'])) {
        $edit_user_id = intval($_GET['edit_user']);
        $edit_user = get_userdata($edit_user_id);
    }
    ?>

    <h1>Profile For The Users</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="user_id" value="<?php echo isset($edit_user) ? $edit_user->ID : ''; ?>">
        <label for="username">User Name:</label>
        <input type="text" name="username" id="username" value="<?php echo isset($edit_user) ? esc_attr($edit_user->user_login) : ''; ?>"><br><br>
        <label for="useremail">User Email:</label>
        <input type="text" name="useremail" id="useremail" value="<?php echo isset($edit_user) ? esc_attr($edit_user->user_email) : ''; ?>"><br><br>
        <label for="userpassword">User Password:</label>
        <input type="password" name="userpassword" id="userpassword" value=""><br><br>
        <label for="userdate">User Date:</label>
        <input type="date" name="userdate" id="userdate" value="<?php echo isset($edit_user) ? esc_attr(get_user_meta($edit_user->ID, 'userdate', true)) : ''; ?>"><br><br>
        <label for="userimage">User Image:</label>
        <input type="file" name="userimage" id="userimage"><br><br>
        <input type="submit" name="submit" id="submit" value="Submit">
    </form>
    <h2>Table</h2>
    <table class="wp_list_table widefat fixed stripped">
        <thead>
            <tr>
                <th>User Name</th>
                <th>User Email</th>
                <th>User Date</th>
                <th>User Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $users = get_users();
        foreach ($users as $user) {
            $user_id = $user->ID;
            $username = $user->user_login;
            $useremail = $user->user_email;
            $userdate = get_user_meta($user_id, 'userdate', true);
            $attachment_id = get_user_meta($user_id, 'attachment_id', true);
            $image_url = wp_get_attachment_url($attachment_id);
            ?>
            <tr>
                <td><?php echo esc_html($username); ?></td>
                <td><?php echo esc_html($useremail); ?></td>
                <td><?php echo esc_html($userdate); ?></td>
                <td>
                    <?php if ($image_url): ?>
                        <img src="<?php echo esc_url($image_url); ?>" alt="User Image" width="50">
                    <?php else: ?>
                        No image
                    <?php endif; ?>
                </td>
                <td>
                    <a href="?page=users_profile&edit_user=<?php echo $user_id; ?>">Edit</a> |
                    <a href="?page=users_profile&delete_user=<?php echo $user_id; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                </td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>

    <?php
}
?>
