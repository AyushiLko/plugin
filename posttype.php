<?php
/*
Plugin Name: Post For the Barabanki
Description: A post for the data publish
Version: 1.0
Author: Divya
*/
add_shortcode('type_of_post','type_of_post_maker');
function type_of_post_maker(){
    if(isset($_POST['submit'])){
        $text = sanitize_text_field($_POST['text']);
        $number = sanitize_text_field($_POST['number']);
        $email = sanitize_text_field($_POST['email']);
        //post data
        $post_data = array(
            "post_title" => $text,
            "post_content" => $number,
            "post_type" => "barabanki",
            "post_status" => "publish",
            "post_author" => 1,
        );
        $post_id = wp_insert_post($post_data);
        if ($post_id) {
            // Handle file upload
            if (!empty($_FILES['image']['name'])) {
                // Include necessary files for media handling
                require_once(ABSPATH . 'wp-admin/includes/media.php');
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                
                // Upload and attach the image to the post
                $attachment_id = media_handle_upload('image', $post_id);
                
                // Check if the upload was successful
                if (!is_wp_error($attachment_id)) {
                    // Set the uploaded image as the post thumbnail
                    set_post_thumbnail($post_id, $attachment_id);
                    echo "Post created successfully";
                } else {
                    // Display error message if the upload failed
                    echo "Error uploading image: " . $attachment_id->get_error_message();
                }
            } else {
                // Display a message if no image was uploaded
                echo "Post created successfully";
            }
        } else {
            // Display error message if post creation failed
            echo "Error creating post";
        }
    }
    ?>
    <h1>Form</h1>
    <form method="POST" enctype="multipart/form-data">
        <label for="text">Text:</label>
        <input type="text" id="text" name="text"></br></br>
        <label for="number">Number:</label>
        <input type="text" id="number" name="number"></br></br>
        <label for="email">Email:</label>
        <input type="text" id="email" name="email"></br></br>
        <label for="image">Image:</label>
        <input type="file" id="image" name="image"></br></br>
        <button type="submit" id="submit" name="submit">Submit</button>
</form>
    <?php
}