<?php
/**
 * Plugin Name:     Simple Attribution
 * Plugin URI:      http://wordpress.org/plugins/simple-attribution/
 * Description:     Allows bloggers to easily add an attribution link to sourced blog posts.
 * Version:         1.1.2
 * Author:          Daniel J Griffiths
 * Author URI:      http://section214.com
 * Text Domain:     simple-attribution
 *
 * @package         SimpleAttribution
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 * @copyright       Copyright (c) 2013-2014, Daniel J Griffiths
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'Simple_Attribution' ) ) {

    /**
     * Main Simple_Attribution class
     *
     * @since       1.1.0
     */
    class Simple_Attribution {

        /**
         * @var         Simple_Attribution $instance The one true Simple_Attribution
         * @since        1.1.0
         */
        private static $instance;

        /**
         * Get active instance
         *
         * @access      public
         * @since       1.1.0
         * @return      object self::$instance The one true Simple_Attribution
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new Simple_Attribution();
                self::$instance->setup_constants();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.1.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin path
            define( 'SIMPLE_ATTRIBUTION_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'SIMPLE_ATTRIBUTION_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.1.0
         * @return      void
         */
        private function hooks() {
            // Edit plugin metalinks
            add_filter( 'plugin_row_meta', array( $this, 'plugin_metalinks' ), null, 2 );

            // Add attribution meta box
            add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

            // Save meta box data
            add_action( 'save_post', array( $this, 'meta_box_save' ) );

            // Add menu item
            add_action( 'admin_menu', array( $this, 'add_menu' ) );

            // Enqueue styles
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );

            // Display attribution
            add_filter( 'the_content', array( $this, 'filter_content' ) );
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
            $lang_dir = apply_filters( 'Simple_Attribution_lang_dir', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale     = apply_filters( 'plugin_locale', get_locale(), '' );
            $mofile     = sprintf( '%1$s-%2$s.mo', 'simple-attribution', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/simple-attribution/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/simple-attribution/ folder
                load_textdomain( 'simple-attribution', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/simple-attribution/languages/ folder
                load_textdomain( 'simple-attribution', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'simple-attribution', false, $lang_dir );
            }
        }


        /**
         * Modify plugin metalinks
         *
         * @access      public
         * @since       1.1.0
         * @param       array $links The current links array
         * @param       string $file A specific plugin table entry
         * @return      array $links The modified links array
         */
        public function plugin_metalinks( $links, $file ) {
            if( $file == plugin_basename( __FILE__ ) ) {
                $help_link = array(
                    '<a href="http://support.section214.com/community/forum/support/free-plugins/simple-attribution/" target="_blank">' . __( 'Support Forum', 'simple-attribution' ) . '</a>'
                );

                $links = array_merge( $links, $help_link );
            }

            return $links;
        }


        /**
         * Enqueue admin styles
         *
         * @access      public
         * @since       1.1.0
         * @return      void
         */
        public function enqueue_styles() {
            wp_register_style( 'simple-attribution', SIMPLE_ATTRIBUTION_URL . 'assets/css/admin.css' );
        }


        /**
         * Add menu item
         *
         * @access      public
         * @since       1.1.0
         * @return      void
         */
        public function add_menu() {
	        add_options_page(
                __( 'Simple Attribution', 'simple-attribution' ),
                __( 'Simple Attribution', 'simple-attribution' ),
                'manage_options',
                'simple-attribution',
                array( $this, 'display_options' )
            );
        }


        /**
         * Display options page
         *
         * @access      public
         * @since       1.1.0
         * @return      void
         */
        public function display_options() {
            global $simple_attribution_options;

            echo '<div class="wrap">';
            echo '<h2>' . __( 'Simple Attribution', 'simple-attribution' ) . '</h2>';

            // Update options
            if( isset( $_POST['sa-update'] ) ) {
                $options = array(
                    'disable'       => isset( $_POST['simple_attribution_disable'] ) ? true : false,
                    'ctype'         => $_POST['simple_attribution_ctype'],
                    'caption'       => esc_attr( $_POST['simple_attribution_caption'] ),
                    'icon'          => $_POST['simple_attribution_icon'],
                    'icon_url'      => esc_url( $_POST['simple_attribution_icon_url'] ),
                    'icon_height'   => esc_attr( $_POST['simple_attribution_icon_height'] )
                );

                update_option( 'simple-attribution', $options );

                echo '<div class="updated fade below-h2"><p>' . __( 'Settings updated successfully.', 'simple-attribution' ) . '</p></div>';
            }

            $simple_attribution_options = get_option( 'simple-attribution' );

            // Initialize options
            if( !$simple_attribution_options ) {
                $options = array(
                    'disable'       => get_option( 'simple_attribution_disable' ) ? get_option( 'simple_attribution_disable' ) : false,
                    'ctype'         => get_option( 'simple_attribution_ctype' ) ? get_option( 'simple_attribution_ctype' ) : 'text',
                    'caption'       => get_option( 'simple_attribution_caption' ) ? get_option( 'simple_attribution_caption' ) : __( 'Attribution:', 'simple-attribution' ),
                    'icon'          => 'clip',
                    'icon_url'      => get_option( 'simple_attribution_icon_url' ) ? get_option( 'simple_attribution_icon_url' ) : '',
                    'icon_height'   => get_option( 'simple_attribution_icon_height' ) ? get_option( 'simple_attribution_icon_url' ) : '24'
                );

                add_option( 'simple-attribution', $options );

                $old_options = array( 'disable', 'ctype', 'caption', 'icon', 'icon_url', 'icon_height' );

                foreach( $old_options as $slug ) {
                    delete_option( 'simple_attribution_' . $slug );
                }
            }

            echo '<form action="' . get_admin_url() . 'options-general.php?page=simple-attribution" method="post">';
            echo '<input type="hidden" name="sa-update" value="true" />';

            echo '<table cellpadding="5" class="widefat post fixed" style="width: 600px">';

            echo '<thead>
                <tr><th scope="row" colspan=2><strong>' . __( 'General Settings', 'simple-attribution' ) . '</strong></th></tr>
                </thead>';

            echo '<tbody>';

            echo '<tr valign="top">
				<th scope="row"><label for="simple_attribution_ctype"><strong>' . __( 'Caption Type:', 'simple-attribution' ) . '</strong></label></th>
				<td><input type="radio" name="simple_attribution_ctype" id="simple_attribution_ctype_text" value="text"' . ( $simple_attribution_options['ctype'] == 'text' ? ' checked' : '' ) . ' />
				<label for="simple_attribution_ctype_text" style="padding: 0 15px 0 5px;">' . __( 'Text-Based', 'simple-attribution' ) . '</label>
				</input>
				<input type="radio" name="simple_attribution_ctype" id="simple_attribution_ctype_image" value="image"' . ( $simple_attribution_options['ctype'] == 'image' ? 'checked' : '' ) . ' />
				<label for="simple_attribution_ctype_image" style="padding: 0 15px 0 5px;">' . __( 'Image-Based', 'simple-attribution' ) . '</label>
				</input>
				</td>
				</tr>';

            echo '<tr valign="top" id="simple_attribution_caption_row">
				<th scope="row"><label for="simple_attribution_caption"><strong>' . __( 'Caption:', 'simple-attribution' ) . '</strong></label></th>
				<td><input type="text" name="simple_attribution_caption" id="simple_attribution_caption" value="' . $simple_attribution_options['caption'] . '" style="width: 100%;" /></td>
				</tr>';

            $icons = array(
                'clip'          => __( 'Clip', 'simple-attribution' ),
                'clipboard'     => __( 'Clipboard', 'simple-attribution' ),
                'globe-1'       => __( 'Globe 1', 'simple-attribution' ),
                'globe-2'       => __( 'Globe 2', 'simple-attribution' ),
                'quote'         => __( 'Quote', 'simple-attribution' )
            );

            echo '<tr valign="top" id="simple_attribution_icon_row" style="display: none;">
                <th scope="row"><label for="simple_attribution_icon"><strong>' . __( 'Icon:', 'simple-attribution' ) . '</strong><br/></label>';

            foreach( $icons as $name => $desc ) {
                echo '<img src="' . SIMPLE_ATTRIBUTION_URL . 'assets/images/' . $name . '.png" style="height: 24px;" title="' . $desc . '" />';
                echo '<img src="' . SIMPLE_ATTRIBUTION_URL . 'assets/images/' . $name . '-light.png" style="height: 24px;" title="' . $desc . ' (light)" />';
            }

			echo '</th>
				<td><select name="simple_attribution_icon" id="simple_attribution_icon" style="width: 100%;">';

            foreach( $icons as $name => $desc ) {
                echo '<option value="' . $name . '"' . ( $simple_attribution_options['icon'] == $name ? ' selected' : '' ) . '>' . $desc . '</option>';
            }

            echo '<option value="custom"' . ( $simple_attribution_options['icon'] == 'custom' ? ' selected' : '' ) . '>' . __( 'Custom', 'simple-attribution' ) . '</option>
				</select>
				</td>
                </tr>';

            echo '<tr valign="top" id="simple_attribution_icon_url_row" style="display: none;">
				<th scope="row"><label for="simple_attribution_icon_url"><strong>' . __( 'Custom Icon URL:', 'simple-attribution' ) . '</strong></label></th>
				<td><input type="text" name="simple_attribution_icon_url" id="simple_attribution_icon_url" value="' . $simple_attribution_options['icon_url'] . '" style="width: 100%;"></td>
				</tr>';

            echo '<tr valign="top" id="simple_attribution_icon_height_row" style="display: none;">
				<th scope="row"><label for="simple_attribution_icon_height"><strong>' . __( 'Custom Icon Height', 'simple-attribution' ) . ' <small style="font-size: .65em;">(' . __( 'Do not enter px', 'simple-attribution' ) . ')</small>:</strong></label></th>
				<td><input type="text" name="simple_attribution_icon_height" id="simple_attribution_icon_height" value="' . $simple_attribution_options['icon_height'] . '" style="width: 100%;"></td>
                </tr>';

            echo '<tr valign="top">
				<th scope="row"><label for="simple_attribution_disable"><strong>' . __( 'Disable Auto-Attribution:', 'simple-attribution' ) . '</strong>
    			<p class="gnote"><small>' . __( 'Useful if you would prefer to add attribution to a specific place in your template as opposed to allowing it to auto-place at the bottom of posts.', 'simple-attribution' ) . '</small></p>
				</label></th>
				<td><input id="simple_attribution_disable" name="simple_attribution_disable" value="checked" type="checkbox"' . ( $simple_attribution_options['disable'] ? ' checked' : '' ) . ' /></td>
                </tr>';

            echo '<tr valign="top">
                <th scope="row" colspan=2><strong style="color: #ff0000;">' . __( 'Note:', 'simple-attribution' ) . '</strong> ' . __( 'You can change attribution styling by overriding the .simple-attribution class.', 'simple-attribution' ) . '						</th>
                </tr>';

            echo '</tbody>
    			</table>';

            echo '<div id="simple_attribution_actions" style="width: 600px; text-align: right; padding-top: 10px;">
				<input type="submit" name="submit" id="submit" class="button-primary" value="' . __( 'Update', 'simple-attribution' ) . '" />
			    </div>';

            echo '</form>';
?>

<script>
	jQuery(document).ready(function() {
		jQuery("input[name='simple_attribution_ctype']").change(function() {
			if (jQuery("input[name='simple_attribution_ctype']:checked").val() == 'text') {
				jQuery("tr#simple_attribution_caption_row").css("display", "");
				jQuery("tr#simple_attribution_icon_row").css("display", "none");
				jQuery("tr#simple_attribution_icon_url_row").css("display", "none");
				jQuery("tr#simple_attribution_icon_height_row").css("display", "none");
			} else if (jQuery("input[name='simple_attribution_ctype']:checked").val() == 'image') {
				jQuery("tr#simple_attribution_caption_row").css("display", "none");
				jQuery("tr#simple_attribution_icon_row").css("display", "");
				jQuery("tr#simple_attribution_icon_height_row").css("display", "");
			}
		}).change();
		jQuery("select[name='simple_attribution_icon']").change(function() {
			if (jQuery(this).val() == 'custom') {
				jQuery("tr#simple_attribution_icon_url_row").css("display", "");
			} else {
				jQuery("tr#simple_attribution_icon_url_row").css("display", "none");
			}
		}).change();
	});
</script>
<?php

            echo '</div>';
        }


        /**
         * Add meta box
         *
         * @access      public
         * @since       1.1.0
         * @return      void
         */
        public function add_meta_box() {
	        add_meta_box(
                'simple_attribution_meta',
                __( 'Simple Attribution', 'simple-attribution' ),
                array( $this, 'meta_box_callback' ),
                'post',
                'side',
                'low'
            );
        }


        /**
         * Meta box callback
         *
         * @access      public
         * @since       1.1.0
         * @param       object $post The post we are editing
         * @return      void
         */
        public function meta_box_callback( $post ) {
            // Define necessary variables
        	$custom = get_post_custom( $post->ID );
            $title  = isset( $custom['simple_attribution_title'] ) ? esc_attr( $custom['simple_attribution_title'][0] ) : '';
            $url    = isset( $custom['simple_attribution_url'] ) ? esc_url( $custom['simple_attribution_url'][0] ) : '';

	        // Safety first!
            wp_nonce_field( basename( __FILE__ ), 'simple_attribution_nonce' );

        	// Print the actual post meta box
        	echo '<p>
                <label for="simple_attribution_title">' . __( 'Attribution Title:', 'simple-attribution' ) . '</label>
                <input type="text" id="simple_attribution_title" name="simple_attribution_title" value="' . $title . '" class="widefat" />
                </p>';

            echo '<p>
                <label for="simple_attribution_url">' . __( 'Attribution URL:', 'simple-attribution' ) . '</label>
                <input type="text" id="simple_attribution_url" name="simple_attribution_url" value="' . $url . '" class="widefat" />
                </p>';
        }


        /**
         * Save meta box data
         *
         * @access      public
         * @since       1.1.0
         * @param       int $post_id The ID of the post we are editing
         */
        public function meta_box_save( $post_id ) {
            // Skip if this is an autosave
        	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;

            // Skip if nonce doesn't exist or isn't verifiable
        	if( !isset( $_POST['simple_attribution_nonce'] ) || !wp_verify_nonce( $_POST['simple_attribution_nonce'], basename( __FILE__ ) ) ) return $post_id;

        	// Skip if user can't edit this post
        	if( !current_user_can( 'edit_post' ) ) return $post_id;

        	// Save the data
        	if( isset( $_POST['simple_attribution_title'] ) ) {
        		update_post_meta( $post_id, 'simple_attribution_title', esc_attr( $_POST['simple_attribution_title'] ) );
            }
    	    if( isset( $_POST['simple_attribution_url'] ) ) {
        		update_post_meta( $post_id, 'simple_attribution_url', esc_url( $_POST['simple_attribution_url'] ) );
            }
        }


        /**
         * Filter content
         *
         * @access      public
         * @since       1.1.0
         * @param       string $content
         * @return      string $content
         */
        public function filter_content( $content ) {
            $simple_attribution_options = get_option( 'simple-attribution' );

            if( $simple_attribution_options['disable'] == true ) return $content;

            if( is_single() ) {
                $attribution = $this->build_attribution();

                $content .= $attribution;
            }

            return $content;
        }


        /**
         * Display attribution
         *
         * @access      public
         * @since       1.1.0
         * @return      void
         */
        public function display_attribution() {
            $attribution = $this->build_attribution();

            echo $attribution;
        }


        /**
         * Build attribution
         *
         * @access      public
         * @since       1.1.0
         * @global      object $post The post we are viewing
         * @return      string $attribution The constructed string
         */
        public function build_attribution() {
            global $post;

            $simple_attribution_options = get_option( 'simple-attribution' );

            // Define necessary variables
        	$title  = get_post_meta( $post->ID, 'simple_attribution_title', true );
        	$url    = get_post_meta( $post->ID, 'simple_attribution_url', true );
            $icon   = $simple_attribution_options['icon'];

            if( $icon != 'custom' ) {
                $icon = SIMPLE_ATTRIBUTION_URL . 'assets/images/' . $icon . '.png';
            } else {
                $icon = $simple_attribution_options['icon_url'];
            }

            // Build attribution
            if( $title && $url ) {
                if( $simple_attribution_options['ctype'] == 'image' ) {
                    $attribution = '<span class="simple-attribution"><img src="' . $icon . '" style="height: ' . $simple_attribution_options['icon_height'] . 'px; display: inline;"> <a href="' . $url . '" target="_new">' . $title . '</a></span>';
      	        } else {
	                $attribution = '<span class="simple-attribution">' . $simple_attribution_options['caption'] . ' <a href="' . $url . '" target="_new">' . $title . '</a></span>';
                }
            } else {
                $attribution = '';
            }

            return $attribution;
	    }
	}
}


/**
 * The main function responsible for returning the one true Simple_Attribution
 * instance to functions everywhere
 *
 * @since       1.1.0
 * @return      Simple_Attribution The one true Simple_Attribution
 */
function Simple_Attribution_load() {
    return Simple_Attribution::instance();
}
add_action( 'plugins_loaded', 'Simple_Attribution_load' );
