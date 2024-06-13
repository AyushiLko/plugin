<?php
/*
Plugin Name: Crud User Page 
Description: to perform crud user in the file.
Version: 1.0 
Author: Mr Verma
*/
add_shortcode('test_user','crud_user_page');
function crud_user_page(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'users';
    $users_per_page = 3;
    $current_page = max(1,get_query_var('paged'));
    $offset = ($current_page-1) * $users_per_page;
    $total_users = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    
  
     if(isset($_GET['search']) && !empty($_GET['search'])) {
      $search = sanitize_text_field($_GET['search']);
      $result = $wpdb->get_results("SELECT * FROM $table_name WHERE user_email LIKE '%$search%'", ARRAY_A);
  } else {
      $result = $wpdb->get_results("SELECT * FROM $table_name LIMIT $users_per_page OFFSET $offset", ARRAY_A);
  }
  
      if (is_user_logged_in()) {
          $user_id = get_current_user_id();
          $user_data = wp_get_current_user();
          $firstname = $user_data->first_name;
          $lastname = $user_data->last_name;
          $user_email = $user_data->user_email;
          $username = $user_data->user_nicename;
          if (isset($_POST['submit'])) {
  
              $first_name = sanitize_text_field($_POST['first_name']);
              $last_name = sanitize_text_field($_POST['last_name']);
              $email = sanitize_text_field($_POST['user_email']);
              $password = sanitize_text_field($_POST['user_pass']);
              $nicename = sanitize_text_field($_POST['user_nicename']);
              $user = array(
                  'ID' => $user_id,
                  'user_nicename' => $nicename,
                  'user_email' => $email,
                  'user_pass' => $password,
              );
              wp_update_user($user);
              require_once(ABSPATH . 'wp-admin/includes/image.php');
              require_once(ABSPATH . 'wp-admin/includes/media.php');
              require_once(ABSPATH . 'wp-admin/includes/file.php');
              $attachment_id = media_handle_upload('image', $user_id);
  
              if ($user_id) {
                  update_user_meta($user_id, 'first_name', $first_name);
                  update_user_meta($user_id, 'last_name', $last_name);
                  update_user_meta($user_id, 'attachment_id', $attachment_id);
                  echo 'user updated successfully';
              } else {
                  echo 'user must be logged in';
              }
          }
      }
     
  ?>
      <div>
          <form method="post" enctype="multipart/form-data">
              <?php
              $attachment_id = get_user_meta($user_id, 'attachment_id', true);
              if ($attachment_id) {
                  $image_url = wp_get_attachment_url($attachment_id);
              }
              ?>
              <label for="image">Change User Profile</label>
              <input type="file" name="image"><br>
              <label for="first_name">First name</label>
              <input type="text" name="first_name" value="<?php echo esc_attr($firstname); ?>">
              <label for="last_name">Last name</label>
              <input type="text" name="last_name" value="<?php echo esc_attr($lastname); ?>"><br>
              <label for="user_nicename">User nicename</label>
              <input type="text" name="user_nicename" value="<?php echo esc_attr($username); ?>">
              <label for="user_email">User Email</label>
              <input type="email" name="user_email" value="<?php echo esc_attr($user_email); ?>"><br>
              <label for="user_pass">User Password</label>
              <input type="password" name="user_pass" value="">
              <input type="submit" name="submit" value="Update user"></br></br></br>
          </form>
      </div>
      <form method="get" role="search">
          <input type="text" name="search" value="<?php echo $search; ?>">
          <input type="submit" value="search">
      </form>
      <table>
          <thead>
              <tr>
                  <th>Profile Picture</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>First Name</th>
                  <th>Last_name</th>
              </tr>
          </thead>
          <tbody>
              <?php
              foreach ($result as $row) {
                  $user_id = $row['ID'];
                  $first_name = get_user_meta($user_id,'first_name',true);
                  $last_name = get_user_meta($user_id,'last_name',true);
              ?>
                  <tr>
                      <td>
                          <?php 
                          $attachment = get_user_meta($user_id, 'attachment_id', true);
                          $image_url = wp_get_attachment_url($attachment);
                          ?>
  
                          <img src="<?php echo esc_url($image_url); ?>" alt="no image">
                      </td>
                      <td><?php echo $row['user_nicename']; ?></td>
                      <td><?php echo $row['user_email']; ?></td>
                      <td><?php echo $first_name; ?></td>
                      <td><?php echo $last_name; ?></td>
                  </tr>
              <?php
              }
              ?>
          </tbody>
      </table>
      <?php
      $total_pages = Ceil($total_users/$users_per_page);
      if($total_pages>1){
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
  <?php
  }