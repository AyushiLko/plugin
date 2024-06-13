<?php
global $wpdb;
$records_per_page = 5;
$current_page = max(1, get_query_var('paged', 1));
$offset = ($current_page - 1) * $records_per_page;
$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM `wp_ajaxcrud` LIMIT %d OFFSET %d", $records_per_page, $offset), ARRAY_A);
$total_records = $wpdb->get_var("SELECT COUNT(*) FROM `wp_ajaxcrud`");
$total_pages = ceil($total_records / $records_per_page);

?>
<h2>Data Table<h2>
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
    if ($results) {
        foreach ($results as $row) {
            $id = $row['id'];
            $name = $row['name'];
            $email = $row['email'];
            $address = $row['address'];
            $date = $row['date'];
            ?>
            <tr data-id="<?php echo $id; ?>">
                <td><?php echo $name; ?></td>
                <td><?php echo $email; ?></td>
                <td><?php echo $address; ?></td>
                <td><?php echo $date; ?></td>
                <td>
                    <a href="http://localhost/wordpress/wp-admin/admin.php?page=enqueue_menu&id=<?php echo $id; ?>">Update</a>
                    <button type="button" class="del" data-id="<?php echo $id; ?>">Delete</button>
                </td>
            </tr>
            <?php
        }
    }
    ?>
    </tbody>
</table>

<div class="pagination">
    <?php
    echo paginate_links(array(
        'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
        'format' => '%_%',
        'total' => $total_pages,
        'current' => $current_page,
        'prev_text' => __('&laquo; Prev'),
        'next_text' => __('Next &raquo;'),
    ));
    ?>
</div>
