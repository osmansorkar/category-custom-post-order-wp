<?php
/*
Plugin Name: Category-Custom-Post-Order
Plugin URI: http://fb.me/osmansorkar
Description: Category Custom Post Order.
Author: osmansorkar
Version: 1.0.0
Author URI: http://fb.me/osmansorkar
*/

/*add_action("wp_head",function(){
    //corder
    query_posts(array(
        'posts_per_page'=>4,
        "orderby" => '_pposition_2',
        "meta_key" => '_pposition_2',
        "order" => 'ASC'
    ));
    while (have_posts()){
        the_post();
        the_title();
        echo "<br>";

    }

    wp_reset_query();
});*/



/**
 * Calls the class on the post edit screen.
 */
function call_category_post_order_metabox() {
    new category_post_order_metabox();
}

if ( is_admin() ) {
    add_action( 'load-post.php',     'call_category_post_order_metabox' );
    add_action( 'load-post-new.php', 'call_category_post_order_metabox' );
}

/**
 * The Class.
 */
class category_post_order_metabox {

    /**
     * Hook into the appropriate actions when the class is constructed.
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post',      array( $this, 'save'         ) );
    }

    /**
     * Adds the meta box container.
     */
    public function add_meta_box( $post_type ) {
        // Limit meta box to certain post types.
        $post_types = array( 'post' );

        if ( in_array( $post_type, $post_types ) ) {
            add_meta_box(
                'category_post_order_metabox',
                __( 'Category Post Order', 'textdomain' ),
                array( $this, 'render_meta_box_content' ),
                $post_type,
                'advanced',
                'high'
            );
        }
    }

    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save( $post_id ) {

        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */

        // Check if our nonce is set.
        if ( ! isset( $_POST['category_post_order_metabox_nonce'] ) ) {
            return $post_id;
        }

        $nonce = $_POST['category_post_order_metabox_nonce'];

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'category_post_order_metabox' ) ) {
            return $post_id;
        }

        /*
         * If this is an autosave, our form has not been submitted,
         * so we don't want to do anything.
         */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        // Check the user's permissions.
        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }

        /* OK, it's safe for us to save the data now. */


        foreach (get_the_category() as $category){

            if($category->parent){
                continue;
            }



            query_posts(array(
                'posts_per_page'=>4,
                'meta_query' => array(array(
                    'key' => '_pposition_'.$category->term_id,
                    'value' => $_POST['pposition_'.$category->term_id],
                    'compare' => '=='
                ))
            ));
            while (have_posts()){
                the_post();

                if(get_the_ID()!=$post_id){
                    delete_post_meta(get_the_ID(),'_pposition_'.$category->term_id,$_POST['pposition_'.$category->term_id]);
                }

            }

            wp_reset_query();


            // Sanitize the user input.
            $mydata = sanitize_text_field( $_POST['pposition_'.$category->term_id] );

            // Update the meta field.
            update_post_meta( $post_id, '_pposition_'.$category->term_id, $mydata );

        }

        return $post_id;
    }


    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content( $post ) {

        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'category_post_order_metabox', 'category_post_order_metabox_nonce' );



        // Display the form, using the current value.

        foreach (get_the_category() as $category){

            if($category->parent){
                continue;
            }


            // Use get_post_meta to retrieve an existing value from the database.
            $value = get_post_meta( $post->ID, '_pposition_'.$category->term_id, true );

            ?>

            <label for="myplugin_new_field">
                <?php _e( 'Postion for : '.$category->name, 'textdomain' ); ?>
            </label>
            <input type="text" id="myplugin_new_field" name="pposition_<?php echo $category->term_id ?>" value="<?php echo esc_attr( $value ); ?>" size="25" />
            <hr>
            <?php
        }

    }
}