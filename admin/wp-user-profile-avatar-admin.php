<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {	
	exit;
}

/**
 * WPUPA_Admin class.
 */

class WPUPA_Admin {

	/**
	 * Constructor - get the plugin hooked in and ready
	 */
	public function __construct() 
	{
		include_once( 'wp-user-profile-avatar-settings.php' );
		$this->settings_page = new WPUPA_Settings();

		$wpupa_tinymce = get_option('wpupa_tinymce');
	    if($wpupa_tinymce) 
	    {	
	      	add_action('init', array( $this, 'wpupa_add_buttons'));
	    }

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 12 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		add_action( 'show_user_profile', array( $this, 'wpupa_add_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'wpupa_add_fields' ) );

		add_action( 'personal_options_update', array( $this, 'wpupa_save_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'wpupa_save_fields' ) );

		add_action( 'admin_init', array($this,'allow_contributor_subscriber_uploads'));

		add_action('init', array( $this, 'thickbox_model_init'));
		add_action('wp_ajax_thickbox_model_view', array( $this, 'thickbox_model_view'));
		add_action('wp_ajax_nopriv_thickbox_model_view', array( $this, 'thickbox_model_view'));
		
	}

    /**
     * admin_menu function.
     *
     * @access public
     * @param 
     * @return 
     * @since 1.0
     */
	public function admin_menu() {

		add_submenu_page( 'users.php', __( 'Profile Avatar Settings', 'wp-user-profile-avatar' ), __( 'Profile Avatar Settings', 'wp-user-profile-avatar' ), 'manage_options', 'wp-user-profile-avatar-settings', array( $this->settings_page, 'settings' ) );
	}

    /**
     * admin_enqueue_scripts function.
     *
     * @access public
     * @param 
     * @return 
     * @since 1.0
     */
	public function admin_enqueue_scripts() 
	{
		wp_register_style( 'wp-user-profile-avatar-backend', WPUPA_PLUGIN_URL . '/assets/css/backend.min.css' );

		wp_register_script( 'wp-user-profile-avatar-admin-avatar', WPUPA_PLUGIN_URL . '/assets/js/admin-avatar.min.js', array( 'jquery' ), WPUPA_VERSION, true);
		
		wp_register_script( 'wp-user-profile-avatar-admin-editor', WPUPA_PLUGIN_URL . '/assets/js/admin-editor.js');
		
		wp_localize_script( 'wp-user-profile-avatar-admin-avatar', 'wp_user_profile_avatar_admin_avatar', array( 
								'thinkbox_ajax_url' 	 => admin_url( 'admin-ajax.php' ) . '?height=600&width=770&action=thickbox_model_view',
								'thinkbox_title' 	 =>  __( 'WP User Profile Avatar', 'wp-user-profile-avatar'),
								'icon_title' 	 =>  __( 'WP User Profile Avatar', 'wp-user-profile-avatar'),
								'wp_user_profile_avatar_security'  => wp_create_nonce( "_nonce_user_profile_avatar_security" ),
								'media_box_title' => __( 'Choose Image: Default Avatar', 'wp-user-profile-avatar'),
								'default_avatar' => WPUPA_PLUGIN_URL.'/assets/images/wp-user-thumbnail.png',
							)
						);

		wp_enqueue_style( 'wp-user-profile-avatar-backend' );
		wp_enqueue_script( 'wp-user-profile-avatar-admin-avatar' );
		
		wp_enqueue_media();
		wp_enqueue_editor();
		wp_enqueue_script( 'wp-user-profile-avatar-admin-editor' );
	}

    /**
     * wpupa_add_fields function.
     *
     * @access public
     * @param $user
     * @return 
     * @since 1.0
     */
	public function wpupa_add_fields( $user ) 
	{
		wp_enqueue_media();

		wp_enqueue_style( 'wp-user-profile-avatar-backend');

		wp_enqueue_script( 'wp-user-profile-avatar-admin-avatar' );

		$user_id = $user->ID;

		$wpupa_original = get_wpupa_url($user_id, ['size' => 'original']);
		$wpupa_thumbnail = get_wpupa_url($user_id, ['size' => 'thumbnail']);

		$wpupa_attachment_id = get_user_meta($user_id, '_wpupa_attachment_id', true);
		$wpupa_url = get_user_meta($user_id, '_wpupa_url', true);
		
		$designation_value = get_user_meta( $user_id, 'designation', true );

		?>
		<h3><?php _e('WP User Profile Avatar', 'wp-user-profile-avatar'); ?></h3>
		
		<table class="form-table">
			<tr>
				<th>
					<label for="wp_user_profile_avatar"><?php _e('Image', 'wp-user-profile-avatar'); ?></label>
				</th>
				<td>
					<p>
						<input type="text" name="wpupa_url" id="wpupa_url" class="regular-text code" value="<?php echo $wpupa_url; ?>" placeholder="Enter Image URL">
					</p>

					<p><?php _e('OR Upload Image', 'wp-user-profile-avatar'); ?></p>

					<p id="wp_user_profile_avatar_add_button_existing">
						<button type="button" class="button" id="wp_user_profile_avatar_add"><?php _e('Choose Image'); ?></button>
						<input type="hidden" name="wpupa_attachment_id" id="wpupa_attachment_id" value="<?php echo $wpupa_attachment_id; ?>">
					</p>

					<?php
	              	$class_hide = 'wp-user-profile-avatar-hide';
	              	if(!empty($wpupa_attachment_id))
	              	{
	              		$class_hide = '';
	              	}
	              	else if(!empty($wpupa_url))
	              	{
	              		$class_hide = '';
	              	}

	              	?>
					<div id="wp_user_profile_avatar_images_existing">
				      	<p id="wp_user_profile_avatar_preview">
				        	<img src="<?php echo $wpupa_original; ?>" alt="">
				        	<span class="description"><?php _e('Original Size', 'wp-user-profile-avatar'); ?></span>
				      	</p>
				      	<p id="wp_user_profile_avatar_thumbnail">
				        	<img src="<?php echo $wpupa_thumbnail; ?>" alt="">
				        	<span class="description"><?php _e('Thumbnail', 'wp-user-profile-avatar'); ?></span>
				      	</p>
				      	<p id="wp_user_profile_avatar_remove_button" class="<?php echo $class_hide; ?>">
					        <button type="button" class="button" id="wp_user_profile_avatar_remove"><?php _e('Remove Image', 'wp-user-profile-avatar'); ?></button>
				        </p>
				      	<p id="wp_user_profile_avatar_undo_button">
				      		<button type="button" class="button" id="wp_user_profile_avatar_undo"><?php _e('Undo', 'wp-user-profile-avatar'); ?></button>
				      	</p>
				    </div>
				</td>
			</tr>
			
			<tr>
					<th><label for="designation">Designation</label></th>		 
					<td><input type="text" class="input-text form-control regular-text" name="designation" id="designation" value="<?php echo $designation_value; ?>"/></td>		 
			</tr>
		</table>
		<?php
	}
	 
	public function add_social_area($profileuser) {
        $user_id = $profileuser->data->ID;

        $social_links = Simple_Author_Box_Helper::get_user_social_links($user_id);
        $social_icons = apply_filters('sabox_social_icons', Simple_Author_Box_Helper::$social_icons);

	    unset($social_icons['user_email']);

        ?>
        <div class="sab-user-profile-wrapper">
            <h2><?php esc_html_e('Social Media Links (Simple Author Box)', 'saboxplugin'); ?></h2>
            <table class="form-table" id="sabox-social-table">
                <?php

                if (!empty($social_links)) {
                    foreach ($social_links as $social_platform => $social_link) {
                        ?>
                        <tr>
                            <th>
                                <span class="sabox-drag"></span>
                                <select name="sabox-social-icons[]">
                                    <?php foreach ($social_icons as $sabox_social_id => $sabox_social_name) { ?>
                                        <option value="<?php echo esc_attr($sabox_social_id); ?>" <?php selected($sabox_social_id, $social_platform); ?>><?php echo esc_html($sabox_social_name); ?></option>
                                    <?php } ?>
                                </select>
                            </th>
                            <td>
                                <input name="sabox-social-links[]"
                                       type="<?php echo ('whatsapp' == $social_platform || 'phone' == $social_platform) ? 'tel' : 'text'; ?>"
                                       class="regular-text"
                                       value="<?php echo ( 'whatsapp' == $social_platform  || 'telegram' == $social_platform || 'skype' == $social_platform || 'phone' == $social_platform ) ? esc_attr($social_link) : esc_url( $social_link ); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            <td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <th>
                            <span class="sabox-drag"></span>
                            <select name="sabox-social-icons[]">
                                <?php foreach ($social_icons as $sabox_social_id => $sabox_social_name) { ?>
                                    <option value="<?php echo esc_attr($sabox_social_id); ?>"><?php echo esc_html($sabox_social_name); ?></option>
                                <?php } ?>
                            </select>
                        </th>
                        <td>
                            <input name="sabox-social-links[]" type="text" class="regular-text" value="">
                            <span class="dashicons dashicons-trash"></span>
                        <td>
                    </tr>
                    <?php
                }

                ?>

            </table>

            <div class="sabox-add-social-link">
                <a href="#"
                   class="button button-primary button-hero"></span><?php esc_html_e('+ Add new social platform', 'saboxplugin'); ?></a>
            </div>
        </div>

        <?php
    }

    /**
     * wpupa_save_fields function.
     *
     * @access public
     * @param $user_id
     * @return 
     * @since 1.0
     */
	public function wpupa_save_fields( $user_id ) 
	{
		if (current_user_can( 'edit_user', $user_id ) )
		{
			$wpupa_url=esc_url_raw($_POST['wpupa_url']);
			$wpupa_attachment_id=absint($_POST['wpupa_attachment_id']);
			
			$designation_data = $_POST['designation'];

			if(isset($wpupa_url,$wpupa_attachment_id))
			{
				update_user_meta( $user_id, '_wpupa_attachment_id', $wpupa_attachment_id );
				update_user_meta( $user_id, '_wpupa_url', $wpupa_url );
			}


			if( !empty($wpupa_attachment_id) || !empty($wpupa_url) )
			{
				update_user_meta( $user_id, '_wpupa_default', 'wp_user_profile_avatar' );
			}
			else
			{
				update_user_meta( $user_id, '_wpupa_default', '' );
			}
			
			 
			 update_user_meta( $user_id, 'designation', $designation_data );
		}
		else
		{
		    status_header( '403' );
		    die();
		}
		
		
		if (isset($_POST['sabox-social-icons']) && isset($_POST['sabox-social-links'])) {
            $social_platforms = apply_filters('sabox_social_icons', Simple_Author_Box_Helper::$social_icons);
            $social_links     = array();

            foreach ($_POST['sabox-social-links'] as $index => $social_link) {
                if ($social_link) {
                    $social_platform = isset($_POST['sabox-social-icons'][$index]) ? $_POST['sabox-social-icons'][$index] : false;
                    if ($social_platform && isset($social_platforms[$social_platform])) {
                        if ('whatsapp' == $social_platform || 'phone' == $social_platform) {
                            $social_links[$social_platform] = esc_html($social_link);
                        } else {
                            $social_links[$social_platform] = esc_url_raw($social_link);
                        }
                    }
                }
            }

			$social_platforms = apply_filters( 'sabox_social_icons', Simple_Author_Box_Helper::$social_icons );
			$social_links     = array();
			foreach ( $_POST['sabox-social-links'] as $index => $social_link ) {
				if ( $social_link ) {
					$social_platform = isset( $_POST['sabox-social-icons'][ $index ] ) ? $_POST['sabox-social-icons'][ $index ] : false;
					if ( $social_platform && isset( $social_platforms[ $social_platform ] ) ) {
						if ( 'whatsapp' == $social_platform  || 'telegram' == $social_platform || 'skype' == $social_platform || 'phone' == $social_platform) {
							$social_links[ $social_platform ] = esc_html($social_link);
						} else {
							$social_links[ $social_platform ] = esc_url_raw( $social_link );
						}
					}
				}
            }

        update_user_meta($user_id, 'sabox_social_links', $social_links);

        } else {
            delete_user_meta($user_id, 'sabox_social_links');
        }
		
	}
	
	/**
     * wpupa_add_buttons function.
     *
     * @access public
     * @param 
     * @return 
     * @since 1.0
     */
    public function wpupa_add_buttons() 
    {
        // Add only in Rich Editor mode
        if(get_user_option('rich_editing') == 'true') 
        {
            add_filter('mce_external_plugins', array( $this, 'wpupa_add_tinymce_plugin'));
            add_filter('mce_buttons', array( $this, 'wpupa_register_button'));
        }
    }

    /**
     * wpupa_register_button function.
     *
     * @access public
     * @param $buttons
     * @return 
     * @since 1.0
     */
    public function wpupa_register_button($buttons) 
    {
        array_push($buttons, 'separator', 'wp_user_profile_avatar_shortcodes');
        return $buttons;
    }

    /**
     * wpupa_add_tinymce_plugin function.
     *
     * @access public
     * @param $plugins
     * @return 
     * @since 1.0
     */
    public function wpupa_add_tinymce_plugin($plugins) 
    {
        $plugins['wp_user_profile_avatar_shortcodes'] = WPUPA_PLUGIN_URL . '/assets/js/admin-avatar.js';
        return $plugins;
    }

    /**
     * thickbox_model_init function.
     *
     * @access public
     * @param 
     * @return 
     * @since 1.0
     */
    public function thickbox_model_init()
	{
	    add_thickbox();
	}

	/**
     * thickbox_model_view function.
     *
     * @access public
     * @param 
     * @return 
     * @since 1.0
     */
	public  function thickbox_model_view()
	{
		include_once (WPUPA_PLUGIN_DIR . '/admin/templates/shortcode-popup.php' );

		wp_die();
	}

	/**
     * allow_contributor_uploads function.
     *`
     * @access public
     * @param 
     * @return 
     * @since 1.0
     */
	public function allow_contributor_subscriber_uploads() 
	{		
		$contributor = get_role('contributor');
		$subscriber = get_role('subscriber');

		$wpupa_allow_upload = get_option('wpupa_allow_upload');		

		if(!empty($contributor))
		{
			if($wpupa_allow_upload)
			{
				$contributor->add_cap('upload_files');
			}
			else
			{
				$contributor->remove_cap('upload_files');
			}
		}

		if(!empty($subscriber))
		{
			if($wpupa_allow_upload)
			{
				$subscriber->add_cap('upload_files');
			}
			else
			{
				$subscriber->remove_cap('upload_files');
			}
		}
		
	}


}

new WPUPA_Admin();



