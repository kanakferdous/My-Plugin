<?php
/**
 * Plugin Name: Kanak Popular Posts Plugin
 * Description: A simple plugin that counts post popularity based on view numbers
 * Version: 0.1
 * License: GPL2
 */
/**
 * Post popularity feature
 */

function kanak_popular_post_views($postID) {
    // Set a key name for the custom field
    $total_key = 'views';
    $total = get_post_meta($postID, $total_key, true);
    if($total==''){
        $total = 0;
        delete_post_meta($postID, $total_key);
        add_post_meta($postID, $total_key, '0');
    }else{
        $total++;
        update_post_meta($postID, $total_key, $total);
    }
}

// Remove prefetching to avoid confusion
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

// Dynamically inject counter into single posts
function kanak_count_popular_posts($post_id) {
    if ( !is_single() ) return;
    if ( !is_user_logged_in() ) {
        if ( empty ( $post_id) ) {
            global $post;
            $post_id = $post->ID;    
        }
        kanak_popular_post_views($post_id);
    }
}
add_action( 'wp_head', 'kanak_count_popular_posts');

// Add a 
function kanak_add_views_column($defaults){
    $defaults['post_views'] = __('View Count');
    return $defaults; 
}

function kanak_display_views($column_name){
    if($column_name === 'post_views'){
        echo (int) get_post_meta(get_the_ID(), 'views', true);
    }
}

add_filter('manage_posts_columns', 'kanak_add_views_column');
add_action('manage_posts_custom_column', 'kanak_display_views',5,2);
    

/**
 * Create Popular Posts widget
 */
class kanak_popular_widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'kanak_popular_widget', // Base ID
			__('Popular Posts', 'text_domain'), // Name
			array( 'description' => __( 'Displays list of most popular posts based on views.', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
        }
        echo '<div class="block-1-2 block-m-full popular__posts">';
		$query_args = array(
                        'post_type' => 'post',
                        'posts_per_page' => 5,
                        'meta_key' => 'views',
                        'orderby' => 'meta_value_num',
                        'order' => 'DESC',
                        'ignore_sticky_posts' => true
                );
                $the_query = new WP_Query( $query_args );
				if ( $the_query->have_posts() ) : 
                    /* Start the Loop */ 
                    while ( $the_query->have_posts() ) : $the_query->the_post();
                    echo '<article class="col-block popular__post">';
                                echo '<a href="' . get_the_permalink() .'" class="popular__thumb">' . get_the_post_thumbnail() .'</a>';
                                echo '<h5>'. get_the_title() .'</h5>';
								echo '<section class="popular__meta">';
								echo 'by  ';
								echo '<span class="popular__author"><a href="'.the_author().'"></a></span>';
								echo ' ';
								echo 'on  ';
                                echo '<span class="popular__date"><time datetime="2018-06-14">'.get_the_date('F j, Y').'</time></span>';
                                '</section>';
                    echo '</article>';
                    endwhile;
                endif;
        echo '</div>';
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Popular Posts', 'text_domain' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class kanak_popular_widget

// register Popular Posts widget
function kanak_register_popular_posts_widget() {
    register_widget( 'kanak_popular_widget' );
}
add_action( 'widgets_init', 'kanak_register_popular_posts_widget' );