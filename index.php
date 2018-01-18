<?php
/*
Plugin Name: Bandcamp Woocommcerce Product Player
Plugin URI:
Description: Make your WooCommerce music products an interactive music player based on the song's Bandcamp song ID.
Version: 1.0
Author: Grayson Erhard
Author URI: https://graysonerhard.com
License: GPLv2 or later
Text Domain: bandcamp-woocommerce-product-player
*/

add_action('wp_enqueue_scripts', 'bwpp_scripts');
function bwpp_scripts() {

  wp_enqueue_style('bwpp-style', WP_PLUGIN_URL.'/bandcamp-woocommerce-product-player/assets/css/bwpp-styles.css');

  wp_register_script('bwpp', WP_PLUGIN_URL.'/bandcamp-woocommerce-product-player/assets/js/bwpp.js', array('jquery'), false, true);

  wp_enqueue_script('bwpp');

}


// Display Fields
add_action( 'woocommerce_product_options_general_product_data', 'woo_add_custom_general_fields' );
function woo_add_custom_general_fields() {

    //TODO: MAKE RADIO BUTTON: TRACK/ALBUM

  global $woocommerce, $post;

  echo '<div class="options_group">';

  woocommerce_wp_text_input(
      array(
          'id'          => '_text_field',
          'label'       => __( 'Bandcamp Track ID', 'woocommerce' ),
          'placeholder' => 'track=682966733',
          'desc_tip'    => 'true',
          'description' => __( 'Enter the Bandcamp track ID found in the iframe embed code here.', 'woocommerce' )
      )
  );

  echo '</div>';

}


// Save Fields
add_action( 'woocommerce_process_product_meta', 'woo_add_custom_general_fields_save' );
function woo_add_custom_general_fields_save($post_id) {
  // Text Field
  $woocommerce_text_field = $_POST['_text_field'];
  if( !empty( $woocommerce_text_field ) )
    update_post_meta( $post_id, '_text_field', esc_attr( $woocommerce_text_field ) );

}

if (is_single()) {
  add_action('woocommerce_before_shop_loop_item', 'new_product_image_iframe');
}

add_action('woocommerce_single_product_image_thumbnail_html', 'new_product_image_iframe');
function new_product_image_iframe() {

  global $post;

  $bandcampID = get_post_meta($post->ID, '_text_field', true);

  if ($bandcampID) {

    if (is_single()) {
      ?>
      <div class="bwpp_iframe_wrap">
        <iframe class="bwpp" style="border: 0;" src="https://bandcamp.com/EmbeddedPlayer/<?php echo $bandcampID; ?>/size=large/bgcol=000/linkcol=e99708/tracklist=false/transparent=true/" seamless></iframe>
      </div>
      <?php
    } else {
      remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );

      ?>
      <div class="bwpp_iframe_wrap">
        <iframe class="bwpp" style="border: 0;" src="https://bandcamp.com/EmbeddedPlayer/<?php echo $bandcampID; ?>/size=large/bgcol=333333/linkcol=e99708/minimal=true/transparent=true/" seamless></iframe>
      </div>
      <?php
    }


  } else {

    if ( is_single() ) {

      // DISPLAY USUAL PRODUCT IMAGE

      $columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
      $post_thumbnail_id = get_post_thumbnail_id( $post->ID );
      $full_size_image   = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );
      $image_title       = get_post_field( 'post_excerpt', $post_thumbnail_id );
      $placeholder       = has_post_thumbnail() ? 'with-images' : 'without-images';
      $wrapper_classes   = apply_filters( 'woocommerce_single_product_image_gallery_classes', array(
          'woocommerce-product-gallery',
          'woocommerce-product-gallery--' . $placeholder,
          'woocommerce-product-gallery--columns-' . absint( $columns ),
          'images',
      ) );

      $attributes = array(
          'title'                   => $image_title,
          'data-src'                => $full_size_image[0],
          'data-large_image'        => $full_size_image[0],
          'data-large_image_width'  => $full_size_image[1],
          'data-large_image_height' => $full_size_image[2],
      );

      if ( has_post_thumbnail() ) {
        $html = '<div data-thumb="' . get_the_post_thumbnail_url( $post->ID, 'shop_thumbnail' ) . '" class="woocommerce-product-gallery__image"><a href="' . esc_url( $full_size_image[0] ) . '">';
        $html .= get_the_post_thumbnail( $post->ID, 'shop_single', $attributes );
        $html .= '</a></div>';
      } else {
        $html = '<div class="woocommerce-product-gallery__image--placeholder">';
        $html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src() ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
        $html .= '</div>';
      }

      echo $html;

      do_action( 'woocommerce_product_thumbnails' );

    } else {
      add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
    }

  }

}
