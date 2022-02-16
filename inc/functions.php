<?php

declare(strict_types=1);

/**
 * Main functions of plugin
 *
 * @package  BENLocator
 */

/* Shortcode to display locations list */
add_shortcode('fws-locations-list', 'fws_listing_parameters_shortcode');
function fws_listing_parameters_shortcode($atts)
{

    ob_start();
    extract(shortcode_atts([
        'post_type' => 'fws_locations',
        'order' => 'date',
        'orderby' => 'title',
        'posts' => -1,
        'post_status' => ['publish'],
    ], $atts));

    $tax_query = [];

    $options = [
        'post_type' => 'fws_locations',
        'post_status' => ['publish'],
        'order' => $order,
        'orderby' => $orderby,
        'posts_per_page' => $posts,
        'tax_query' => $tax_query,
    ];

    $listing_query = new WP_Query($options);
    // run the loop based on the query
    if ($listing_query->have_posts()) { ?>
        <script>
          var allLocations = [
            <?php while ($listing_query->have_posts()) :
                $listing_query->the_post();
                $locid = get_the_ID();
                $contact_email = get_post_meta($locid, 'contact_email', true);
                $address = str_replace(['[\', \']'], '', get_post_meta($locid, 'address', true));
                $zip_code = get_post_meta($locid, 'zip_code', true);
                $lat = get_post_meta($locid, 'lat', true);
                $lng = get_post_meta($locid, 'lng', true);
                ?>
                {
                    name: "<?php the_title(); ?>",
                    <?php if ($lat) { ?>
                    lat: <?php echo $lat; ?>,
                    <?php } ?>
                    <?php if ($lng) { ?>
                    lng: <?php echo $lng; ?>,
                    <?php } ?>
                    myid: <?php echo get_the_ID(); ?>,
                    <?php if ($contact_email) { ?>
                    email: "<?php echo $contact_email; ?>",
                    <?php } ?>
                    <?php if ($zip_code) { ?>
                    zip: "<?php echo $zip_code; ?>",
                    <?php } ?>
                    <?php if ($address) {
                        echo 'address: "' . str_replace(["\r\n", "\n"], "<br>", $address) . '",';
                    }  ?>
                },
            <?php endwhile;
            wp_reset_postdata(); ?>
        ];
        var fwsAPI = '<?php echo get_option('fws_map_api_key'); ?>';
        var fwsMaptype = '<?php if (!empty(get_option('fws_map_type'))) {
            echo get_option('fws_map_type');
                          } else {
                              echo 'roadmap';
                          } ?>';
        var clsIcon = '<?php if (!empty(get_option('fws_custom_map_marker'))) {
            echo get_option('fws_custom_map_marker');
                       } ?>';
        </script>
        <form class="fws-search-form" action="#" method="get">
            <label for="userAddress">
            <input name="userAddress" id="userAddress" type="text" value="<?php if (isset($_GET['userAddress'])) {
                echo sanitize_text_field($_GET['userAddress']);
                                                                          } ?>" placeholder="Zipcode"/>
            </label>
            <input name="maxRadius" id="maxRadius" type="hidden" value="<?php if (!empty($fws_map_default_radius)) {
                echo sanitize_text_field($fws_map_default_radius);
                                                                        } else {
                                                                            echo "20";
                                                                        } ?>" min="1" />
            <button id="submitLocationSearch">Search</button>
        </form>
      <h2 id="location-search-alert">All Locations</h2>
      <div class="fws-wrapper" id="fws-wrapper">    
      <div class="fws-left">
      <div id="locations-near-you">
        <?php
        if ($listing_query->have_posts()) {
            $i = 0;
            ?>
            <div class="location-near-you-box">
            <?php while ($listing_query->have_posts()) :
                $listing_query->the_post();
                $locid = get_the_ID();
                $contact_email = get_post_meta($locid, 'contact_email', true);
                $address = str_replace(['[\', \']'], '', get_post_meta($locid, 'address', true));
                $zip_code = get_post_meta($locid, 'zip_code', true);
                $lat = get_post_meta($locid, 'lat', true);
                $lng = get_post_meta($locid, 'lng', true);
                ?>
                <div class="fws-list-item">
                    <a data-markerid="<?php echo $i; ?>" href="#1" class="marker-link"> 
                        <h4><?php the_title(); ?></h4>
                        <p><?php if ($address) {
                            echo str_replace(["\r\n", "\n"], "<br>", $address);
                           }  ?></p>
                    </a>
                </div>
                <?php $i++;
            endwhile;
            wp_reset_postdata();
            ?>
            </div>
            <?php
        } ?>
      </div>
      </div>
      <div class="fws-right">
      <div id="locations-near-you-map"></div>
      </div>
      </div>
      
        
        <?php
        $myvariable = ob_get_clean();
        return $myvariable;
    }
}

/* End of Shortcode to display locations list */

/* Search Form Shortcode */
function fws_search_form($atts)
{

    extract(shortcode_atts([
        'pageid' => '',
    ], $atts));
    $fws_default_radius = get_option('fws_map_default_radius');
    if (!empty($fws_default_radius)) {
        $fws_map_default_radius = $fws_default_radius;
    } else {
        $fws_map_default_radius = 20;
    }
    $search_form = '<form class="fws-search-form" action="' . get_permalink($pageid) . '" method="get">
	<label for="userAddress"> Enter Zipcode: <input name="userAddress" id="userAddress" type="text" placeholder="Zipcode" /></label>
	<input name="maxRadius" id="maxRadius" type="hidden" value="' . sanitize_text_field($fws_map_default_radius)  . '" min="1" />
	<button id="submitLocationSearch">Search</button>
	</form>';
    return $search_form;
}
add_shortcode('fws-search', 'fws_search_form');
/* End of Search Form Shortcode */

/*Adds a metabox to location posts */
add_action('add_meta_boxes', 'add_fws_metaboxes');
function add_fws_metaboxes()
{

    add_meta_box(
        'metabox_settings',
        'Dealer locations Settings',
        'metabox_settings',
        'fws_locations',
        'normal',
        'high'
    );
}

/**
 * Output the HTML for the metabox.
 */
function metabox_settings()
{

    global $post;

    // Nonce field to validate form request came from current site
    wp_nonce_field(basename(__FILE__), 'event_fields');

    // Get the location data if it's already been entered
    $contact_email = get_post_meta($post->ID, 'contact_email', true);
    $address = get_post_meta($post->ID, 'address', true);
    $zip_code = get_post_meta($post->ID, 'zip_code', true);
    $lat = get_post_meta($post->ID, 'lat', true);
    $lng = get_post_meta($post->ID, 'lng', true);
    echo '<p><label for="contact_email">Contact Email</label><input type="text" id="contact_email" name="contact_email" value="' . esc_textarea($contact_email)  . '" class="widefat" placeholder="Dealer Contact Email"></p>';
    echo '<p><label for="address">Dealer Address</label><textarea id="address" name="address" class="widefat" placeholder="Dealer Address">' . esc_textarea($address)  . '</textarea></p>';
    echo '<p><label for="zip_code">Dealer Zip Code</label><input type="text" id="zip_code" name="zip_code" value="' . esc_textarea($zip_code)  . '" class="widefat" placeholder="Dealer Zip Code"></p>';
    echo '<p><label for="lat">Dealer Latitude</label><input type="text" id="lat" name="lat" class="widefat" value="' . esc_textarea($lat)  . '" placeholder="Dealer Latitude"></p>';
    echo '<p><label for="lng">Dealer Longitude</label><input type="text" id="lng" name="lng" value="' . esc_textarea($lng)  . '" class="widefat" placeholder="Dealer Longitude"></p>';
}

/* Save the metabox data */
function save_fws_meta($post_id, $post)
{

    if (! current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    if (! isset($_POST['lat']) || ! wp_verify_nonce($_POST['event_fields'], basename(__FILE__))) {
        return $post_id;
    }

    $fws_data_meta['contact_email'] = sanitize_email($_POST['contact_email']);
    $fws_data_meta['address'] = sanitize_textarea_field($_POST['address']);
    $fws_data_meta['zip_code'] = sanitize_text_field($_POST['zip_code']);
    $fws_data_meta['lat'] = sanitize_text_field($_POST['lat']);
    $fws_data_meta['lng'] = sanitize_text_field($_POST['lng']);

    foreach ($fws_data_meta as $key => $value) :
        if ('revision' === $post->post_type) {
            return;
        }
        if (get_post_meta($post_id, $key, false)) {
            update_post_meta($post_id, $key, $value);
        } else {
            add_post_meta($post_id, $key, $value);
        }
        if (! $value) {
            delete_post_meta($post_id, $key);
        }
    endforeach;
}
add_action('save_post', 'save_fws_meta', 1, 2);
/*End of Adds a metabox to location posts */

