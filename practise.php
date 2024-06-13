<?php
/*
Plugin Name: Practise for test
Version: 1.0 
Description: For Practise section
Author: Mr Verma
*/

add_shortcode('search_page', 'search_page_post');
function search_page_post(){
    ?>
    <h1>FORM</h1>
    <form id="custom-form" method="POST">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name"><br><br>
        <label for="about">About:</label>
        <textarea name="about" id="about" rows="10" cols="30"></textarea><br><br>
        <label for="number">Number:</label>
        <input type="text" name="number" id="number"><br><br>
        <button type="submit" name="submit" id="submit">Submit</button><br><br>
    </form>
    <table>
    <form method="GET" action="">
        <input type="text" name="search">
        <input type="submit" value="search">
    </form>
        <thead>
            <tr>
                <th>Name</th>
                <th>About</th>
                <th>Number</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
        if (isset($_GET['search'])){
            $search= $_GET['search'];
        $args = array (
            'post_status' => 'publish',
            'post_type'              => 'test',
            'posts_per_page'         => '3',
            'paged' => $paged,
            's' => $search,
        );
    }else{
        $args = array(
            'post_status' => 'publish',
            'post_type' => 'test',
            'posts_per_page'         => '3',
            'paged' => $paged
        ); 
    }
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $name = get_the_title();
                $about = get_post_meta($post_id, 'text', true);
                $number = get_post_meta($post_id, 'number', true);
        ?>
            <tr>
                <td><?php echo esc_html($name); ?></td>
                <td><?php echo esc_html($about); ?></td>
                <td><?php echo esc_html($number); ?></td>
            </tr>
        <?php
            }
            wp_reset_postdata();
        }
        ?>
        </tbody>
    </table>
    <?php
    $big = 999999999; // need an unlikely integer
echo paginate_links( array(
    'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
    'format' => '?paged=%#%',
    'current' => max( 1, get_query_var('paged') ),
    'total' =>  $query->max_num_pages
) );
?>
    <script>
        jQuery(document).ready(function($){
            $('#custom-form').submit(function(e){
                e.preventDefault();
                var name = $('#name').val();
                var about = $('#about').val();
                var number = $('#number').val();

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'custom_pagination',
                        name: name,
                        about: about,
                        number: number,
                    },
                    success: function(response) {
                        alert(response.data);
                        location.reload();
                    },
                    error: function(response) {
                        alert(response.responseJSON.data);
                    }
                });
            });
        });
    </script>
    <?php
}

add_action('wp_ajax_custom_pagination', 'custom_pagination');
add_action('wp_ajax_nopriv_custom_pagination', 'custom_pagination');

function custom_pagination(){
    if (isset($_POST['name']) && !empty($_POST['name'])) {
        $name = sanitize_text_field($_POST['name']);
        $about = sanitize_textarea_field($_POST['about']);
        $number = sanitize_text_field($_POST['number']);

        $post_data = array(
            'post_title' => $name,
            'post_content' => $about,
            'post_status' => 'publish',
            'post_type' => 'test',
        );
        $post_id = wp_insert_post($post_data);
        if ($post_id) {
            update_post_meta($post_id, 'text', $about);
            update_post_meta($post_id, 'number', $number);
            wp_send_json_success('Post created successfully');
        } else {
            wp_send_json_error('Post failed to create');
        }
    } else {
        wp_send_json_error('Post title is required');
    }
}
?>
