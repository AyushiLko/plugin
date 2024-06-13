<?php
/*
Plugin Name: New Plugin
Description: New plugin for the work purpose.
Version: 1.0 
Author: Mr RK
*/
function wpdocs_selectively_enqueue_admin_script( $hook ) {
    wp_enqueue_script( 'my_custom_script', plugin_dir_url( __FILE__ ) . 'test.js', array(), '1.0', true );

    wp_localize_script('my_custom_script', 'frontend_script', array(
        'ajaxUrl' => admin_url('admin-ajax.php')    
    ));
}
add_action( 'admin_enqueue_scripts', 'wpdocs_selectively_enqueue_admin_script' );

add_action('admin_menu','tested_files');
function tested_files(){

add_menu_page('Enqueue File','Enqueue File','manage_options','enqueue_menu','enqueue_file_menu');
add_submenu_page('enqueue_menu','Table Listing','Table Listing','manage_options','table_listing_menu','table_listing_submenu');
}
function table_listing_submenu(){
    ob_start();
    include plugin_dir_path(__FILE__)."testing.php";
    $template = ob_get_contents();
    ob_end_clean();
    echo $template;   
}
function enqueue_file_menu(){
   if (isset($_GET['id'])) {
    $id = $_GET['id'];
   }
    ?>
    <h1>FORM</h1>
    <form action="" method="POST">
        <label for="name"> Name:</label>
        <input type="text" id="name" value="<?php echo isset($name) ? $name : ''; ?>"></br></br>
        <label for="email">Email:</label>
        <input type="text" id="email" value="<?php echo isset($email) ? $email : ''; ?>"></br></br>
        <label for="address">Address:</label>
        <input type="text" id="address" value="<?php echo isset($address) ? $address : ''; ?>"></br></br>
        <label for="date">Date:</label>
        <input type="date" id="date" value="<?php echo isset($date) ? $date : ''; ?>"></br></br>
        <button type="submit" id="submit" name="submit">Submit</button>
</form>
<?php
}
?>
<?php
add_action('wp_ajax_file_ayushi','file_ayushi');
function file_ayushi(){
    if (isset($_POST['name'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $date = $_POST['date'];
        
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
add_action('wp_ajax_del_ayushi', 'del_ayushi');
function del_ayushi()
{
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        global $wpdb;
        $table_name = $wpdb->prefix . 'ajaxcrud';
        $result = $wpdb->delete($table_name, array(
            'id' => $id
        ));
        if ($result) {
            wp_send_json_success('data deleted successfully');
        } else {
            wp_send_json_error('data not delete');
        }
    }
    wp_die();
}
add_action('wp_ajax_update_ayushi', 'update_ayushi');
function update_ayushi()
{
    if (isset($_POST['edit_data'])) {
        $edit_value = $_POST['edit_data'];
        $id = intval($edit_value['id']);
        $name = sanitize_text_field($edit_value['name']);
        $email = sanitize_text_field($edit_value['email']);
        $address = sanitize_text_field($edit_value['address']);
        $date = sanitize_text_field($edit_value['date']);
        global $wpdb;
        $table_name = $wpdb->prefix . 'ajaxcrud';
        $result = $wpdb->update(
           $table_name ,
            array(
                'name' => $name,
                'email' => $email,
                'address' => $address,
                'date' => $date,
            ),
            array('id' => $id)
        );
        if ($result) {
            wp_send_json_success('data updated successfully');
        } else {
            wp_send_json_error('data not updated');
        }
    }
    wp_die();
}
add_action('wp_ajax_showed_ayushi', 'showed_ayushi');
function showed_ayushi()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ajaxcrud';
    $result = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
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