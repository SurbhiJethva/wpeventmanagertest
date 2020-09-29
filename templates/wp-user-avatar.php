<?php
/**
 * user profile shortcode
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wp-user-profile-avatar">
	<a href="<?php echo $link; ?>" target="<?php echo $target; ?>" class="wp-user-profile-avatar-link">
		<img src="<?php echo $image_url; ?>" class="size-<?php echo $size; ?> <?php echo $align; ?>"  alt="<?php echo $content; ?>" />
	</a>
	<p class="caption-text <?php echo $align; ?>"><?php echo $content; ?></p>
	
	    <?php 
		global $post;
        $wp_user_author_id = $post->post_author;
		
		$wp_author_link = sprintf( '<a href="%s" class="vcard author" rel="author" itemprop="url"><span class="fn" itemprop="name">%s</span></a>', esc_url( get_author_posts_url( $wp_user_author_id ) ), esc_html( get_the_author_meta( 'display_name', $wp_user_author_id ) ) );
		$author_description = apply_filters( 'user_description', get_the_author_meta( 'description', $wp_user_author_id ), $wp_user_author_id );
		
		// author name
        echo  '<div class="wp_userplugin-authorname">' ;
        echo  apply_filters('wp_user_author_html',$wp_author_link,$wp_user_author_id) ;
        if ( is_user_logged_in() && get_current_user_id() == $wp_user_author_id ) {
            echo  '<a class="wp-profile-edit" target="_blank" href="' . get_edit_user_link() . '"> ' . esc_html__( 'Edit profile', 'wp-user-profile-avatar' ) . '</a>' ;
        }
        echo  '</div>' ;
		
		//author designation
		$designation_value = get_the_author_meta( 'designation', $wp_user_author_id ); 
		echo '<div class="wp_userplugin-designation">';
		echo $designation_value;
		echo '</div>';
		
		
		// author description
		echo  '<div class="wp_userplugin-desc">' ;
        echo  '<div itemprop="description">' ;
        $author_description = wptexturize( $author_description );
        $author_description = wpautop( $author_description );
        echo  wp_kses_post( $author_description ) ;
        if ( '' == $author_description && is_user_logged_in() && $wp_user_author_id == get_current_user_id() ) {
            echo  '<a target="_blank" href="' . admin_url() . 'profile.php">' . esc_html__( 'Add Biographical Info', 'wp-user-profile-avatar' ) . '</a>' ;
        }
        echo  '</div>' ;
        echo  '</div>' ;
		
		?>
</div>