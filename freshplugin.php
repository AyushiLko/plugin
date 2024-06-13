<?php
/*
Plugin Name: New Plugin Pagination
Description: For the pagination purpose.
Version: 1.0 
Author: Gita
*/

add_shortcode('fresh_plugin', 'fresh_plugin_page');

function fresh_plugin_page() {
    global $wpdb;
    $items_per_page = 4;
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM `wp_crud2`");
    $current_page = max(1, get_query_var('paged'));
    $offset = ($current_page - 1) * $items_per_page;
    if(isset($_POST['search'])){
        $search = sanitize_text_field($_POST['search']);
        $result = $wpdb->get_results("SELECT * FROM `wp_crud2` WHERE name LIKE '$search'", ARRAY_A);
    }else{
        $result = $wpdb->get_results("SELECT * FROM `wp_crud2` LIMIT $items_per_page OFFSET $offset", ARRAY_A);
    }

    if(isset($_POST['filter'])){
        $filter = sanitize_text_field($_POST['filter']);
        $result = $wpdb->get_results("SELECT * FROM `wp_crud2` WHERE email LIKE '$filter'", ARRAY_A);
    }else{
        $result = $wpdb->get_results("SELECT * FROM `wp_crud2` LIMIT $items_per_page OFFSET $offset", ARRAY_A);
    }
    
?>
    <h1>Table</h1>
    
    <?php
   

    ?>

    <form method="post">
        <input type="text" name="search">
        <input type="submit" value="search">
    </form>
    <form method="post">
        <input type="text" name="filter">
        <input type="submit" value="filter">
    </form>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Address</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php         
            foreach($result as $rows) {
                $name    = $rows['name'];
                $email   = $rows['email'];
                $address = $rows['address'];
                $date    = $rows['date'];
                $id      = $rows['id'];
                ?>
                <tr>
                    <td><?php echo $name; ?></td>
                    <td><?php echo $email; ?></td>
                    <td><?php echo $address; ?></td>
                    <td><?php echo $date; ?></td>
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
    <?php
}
