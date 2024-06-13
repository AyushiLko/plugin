<?php
/*
Plugin Name: Testing Product Crud Two
Description: For the purpose of the testing of the products.
Version: 1.0
Author: Mr. Testing
*/

add_shortcode('testing_product', 'testing_product_crud');
function testing_product_crud() {
    global $wpdb;
    if (isset($_POST['submit'])) {
        $productname = sanitize_text_field($_POST['productname']);
        $productdescription = sanitize_text_field($_POST['productdescription']);
        $productprice = sanitize_text_field($_POST['productprice']);

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        if (isset($_GET['product_id']) && $_GET['action'] == 'update') {
            $productid = intval($_GET['product_id']);
            $product_data = array(
                'ID' => $productid,
                'post_title' => $productname,
                'post_content' => $productdescription,
                'post_status' => 'publish',
                'post_type' => 'product',
            );
            wp_update_post($product_data);

            if (!empty($_FILES['productimage']['name'])) {
                $attachment_id = media_handle_upload('productimage', $productid);
                update_post_meta($productid, '_thumbnail_id', $attachment_id);
            }

            update_post_meta($productid, '_price', $productprice);

            echo "Product updated successfully";
        } else {
            $product_data = array(
                'post_title' => $productname,
                'post_content' => $productdescription,
                'post_status' => 'publish',
                'post_type' => 'product',
            );

            $product_id = wp_insert_post($product_data);
            $attachment_id = media_handle_upload('productimage', $product_id);
            update_post_meta($product_id, '_price', $productprice);
            update_post_meta($product_id, '_thumbnail_id', $attachment_id);

            echo "Product inserted successfully";
        }
    }
    if (isset($_GET['delete']) && isset($_GET['product_id'])) {
        $product_ID = intval($_GET['product_id']);
        $result = wp_delete_post($product_ID);

        if ($result) {
            echo "Product deleted successfully.";
        } else {
            echo "Product is not deleted";
        }
    }
    if (isset($_GET['product_id']) && isset($_GET['action']) && $_GET['action'] == 'update') {
        $productID = intval($_GET['product_id']);
        $productname = get_the_title($productID);
        $productdescription = get_post_field('post_content', $productID);
        $productprice = get_post_meta($productID, '_price', true);
    }
    ?>
    <h1>Product Form</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="productname">Product Name:</label>
        <input type="text" name="productname" id="productname" value="<?php echo isset($productname) ? esc_attr($productname) : ''; ?>"><br><br>
        <label for="productdescription">Product Description:</label>
        <input type="text" name="productdescription" id="productdescription" value="<?php echo isset($productdescription) ? esc_attr($productdescription) : ''; ?>"><br><br>
        <label for="productprice">Product Price:</label>
        <input type="text" name="productprice" id="productprice" value="<?php echo isset($productprice) ? esc_attr($productprice) : ''; ?>"><br><br>
        <label for="productimage">Product Image:</label>
        <input type="file" name="productimage" id="productimage"><br><br>
        <button type="submit" name="submit" id="submit">Submit</button>
    </form>

    <h2>Table</h2>

    <?php
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    ?>

    <form method="get" role="search">
        <input type="text" name="search" value="<?php echo esc_attr($search); ?>">
        <input type="submit" value="search">
    </form>

    <table class="wp_list_table widefat fixed striped">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Product Description</th>
                <th>Product Price</th>
                <th>Product Image</th>
                <th>Update</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $product_args = array(
                'post_status' => 'publish',
                'post_type' => 'product',
                's' => $search,
            );
            $products = get_posts($product_args);

            foreach ($products as $row) {
                $product_id = $row->ID;
                $productname = get_the_title($product_id);
                $productdescription = $row->post_content;
                $productprice = get_post_meta($product_id, '_price', true);
                $productimage = get_post_meta($product_id, '_thumbnail_id', true);
                $image_url = wp_get_attachment_url($productimage);
                ?>

                <tr>
                    <td><?php echo esc_html($productname); ?></td>
                    <td><?php echo esc_html($productdescription); ?></td>
                    <td><?php echo esc_html($productprice); ?></td>
                    <td><img src="<?php echo esc_url($image_url); ?>" alt="image not found" style="width: 100px; height: 100px;" /></td>
                    <td><a class="btn btn-success" href="<?php echo add_query_arg(array('action' => 'update', 'product_id' => $product_id), get_permalink()); ?>">Update</a></td>
                    <td><a class="btn btn-danger" href="<?php echo add_query_arg(array('delete' => 'delete', 'product_id' => $product_id), get_permalink()); ?>">Delete</a></td>
                </tr>

                <?php
            }
            ?>
        </tbody>
    </table>
    <?php
}
