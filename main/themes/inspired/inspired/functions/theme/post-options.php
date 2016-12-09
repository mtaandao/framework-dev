<?php


/* Registering metaboxes
============================================*/

add_action( 'admin_menu', 'mtaa_options_box' );

function mtaa_options_box() {

    add_meta_box( 'mtaa_top_button', 'Slideshow Options', 'mtaa_top_button_options', 'slider', 'side', 'high' );

}


function mnz_nemnost_head() {
    ?><style type="text/css">
        fieldset.fieldset-show { padding: 0.3em 0.8em 1em; border: 1px solid rgba(0, 0, 0, 0.2); -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius: 3px; }
        fieldset.fieldset-show p { margin: 0 0 1em; }
        fieldset.fieldset-show p:last-child { margin-bottom: 0; }
    </style><?php
}
add_action('admin_head-post-new.php', 'mnz_nemnost_head', 100);
add_action('admin_head-post.php', 'mnz_nemnost_head', 100);

/* Slideshow Options
============================================*/
function mtaa_top_button_options() {
    global $post;

    ?>

    <p>
        <strong><label for="mtaa_slide_url"><?php _e( 'Slide URL', 'mtaa' ); ?></label></strong> (<?php _e('optional', 'mtaa'); ?>)<br/>
        <input type="text" name="mtaa_slide_url" id="mtaa_slide_url" class="widefat"
               value="<?php echo esc_url( get_post_meta( $post->ID, 'mtaa_slide_url', true ) ); ?>"/>
    </p>


    <fieldset class="fieldset-show">
        <legend><strong><?php _e( 'Slide Button', 'mtaa' ); ?></strong></legend>

        <p>
            <label>
                <strong><?php _e( 'Title', 'mtaa' ); ?></strong> <?php _e( '(optional)', 'mtaa' ); ?>
                <input type="text" name="mtaa_slide_button_title" id="mtaa_slide_button_title" class="widefat" value="<?php echo esc_attr( get_post_meta( $post->ID, 'mtaa_slide_button_title', true ) ); ?>" />
            </label>
        </p>

        <p>
            <label>
                <strong><?php _e( 'URL', 'mtaa' ); ?></strong> <?php _e( '(optional)', 'mtaa' ); ?>
                <input type="text" name="mtaa_slide_button_url" id="mtaa_slide_button_url" class="widefat" value="<?php echo esc_url( get_post_meta( $post->ID, 'mtaa_slide_button_url', true ) ); ?>" />
            </label>
        </p>
   </fieldset>




<?php
}



add_action( 'save_post', 'custom_add_save' );

function custom_add_save( $postID ) {

    // called after a post or page is saved
    if ( $parent_id = mn_is_post_revision( $postID ) ) {
        $postID = $parent_id;
    }


    if ( isset( $_POST['save'] ) || isset( $_POST['publish'] ) ) {

        if ( isset( $_POST['mtaa_slide_url'] ) )
            update_custom_meta( $postID, esc_url_raw( $_POST['mtaa_slide_url'] ), 'mtaa_slide_url' );

        if ( isset( $_POST['mtaa_slide_button_title'] ) )
            update_custom_meta( $postID, $_POST['mtaa_slide_button_title'] , 'mtaa_slide_button_title' );

        if ( isset( $_POST['mtaa_slide_button_url'] ) )
            update_custom_meta( $postID, esc_url_raw( $_POST['mtaa_slide_button_url'] ), 'mtaa_slide_button_url' );

    }
}


function update_custom_meta( $postID, $value, $field ) {
    // To create new meta
    if ( ! get_post_meta( $postID, $field ) ) {
        add_post_meta( $postID, $field, $value );
    } else {
        // or to update existing meta
        update_post_meta( $postID, $field, $value );
    }
}