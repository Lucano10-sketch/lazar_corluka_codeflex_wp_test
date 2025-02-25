<?php
if (! defined('WP_DEBUG')) {
	die( 'Direct access forbidden.' );
}
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
});


if (! defined('WP_DEBUG')) {
	die( 'Direct access forbidden.' );
}
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
});



function real_estate_edit_form() {

    if (is_singular('estate')) {

        $post = get_post();
        
        if (current_user_can('administrator') || $post->post_author == get_current_user_id()) {

            $title = get_field('title', $post->ID);
            $subtitle = get_field('subtitle', $post->ID);

            $nonce = wp_nonce_field('edit_real_estate_nonce_action', 'edit_real_estate_nonce', true, false);

            ?>
            <form action="" method="POST">
                <?php echo $nonce; ?>
                
                <p>
                    <label for="title">Title:</label><br>
                    <input type="text" name="title" id="title" value="<?php echo esc_attr($title); ?>" required>
                </p>
                
                <p>
                    <label for="subtitle">Subtitle:</label><br>
                    <input type="text" name="subtitle" id="subtitle" value="<?php echo esc_attr($subtitle); ?>" required>
                </p>
                
                <p>
                    <input type="submit" name="save_real_estate" value="Save Changes">
                </p>
            </form>
            <?php
        }
    }
}

add_action('the_content', 'real_estate_edit_form');

function save_real_estate_changes() {
    if (isset($_POST['save_real_estate'])) {
 
        if (isset($_POST['edit_real_estate_nonce']) && wp_verify_nonce($_POST['edit_real_estate_nonce'], 'edit_real_estate_nonce_action')) {

            $post_id = get_the_ID();

            $post = get_post($post_id);
            if (current_user_can('administrator') || $post->post_author == get_current_user_id()) {

                $title = sanitize_text_field($_POST['title']);
                $subtitle = sanitize_text_field($_POST['subtitle']);

                update_field('title', $title, $post_id);
                update_field('subtitle', $subtitle, $post_id);

                wp_update_post([
                    'ID' => $post_id,
                    'post_title' => $title
                ]);

                wp_redirect(get_permalink($post_id));
                exit;
            }
        }
    }
}

add_action('template_redirect', 'save_real_estate_changes');


// Add taxonomy filters for the Real Estate custom post type in the admin list
function real_estate_taxonomy_filters() {
    global $typenow;

    // Check if we are on the 'real_estate' post type admin screen
    if ($typenow == 'estate') {
        // List all taxonomies for the post type
        $taxonomies = array('location', 'tip'); // Add your custom taxonomies here
        
        foreach ($taxonomies as $taxonomy) {
            $taxonomy_obj = get_taxonomy($taxonomy);
            $taxonomy_name = $taxonomy_obj->labels->name;
            $terms = get_terms($taxonomy);

            // Output a dropdown filter for each taxonomy
            echo '<select name="' . $taxonomy . '" id="' . $taxonomy . '" class="postform">';
            echo '<option value="">' . sprintf(__('Select %s', 'textdomain'), $taxonomy_name) . '</option>';
            foreach ($terms as $term) {
                echo '<option value="' . $term->term_id . '" ' . (isset($_GET[$taxonomy]) && $_GET[$taxonomy] == $term->term_id ? 'selected="selected"' : '') . '>' . $term->name . '</option>';
            }
            echo '</select>';
        }
    }
}

add_action('restrict_manage_posts', 'real_estate_taxonomy_filters');
