<?php

/*
Plugin Name: Widget WP API
Description: Creates widget that connects to other WP sites API and extracts posts that are in certain categories or have certain tags.
Version: 1.0
Author: Vigil Web
Text Domain: widget-api-woo
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Register and load the widget

function wpb_load_widget() {
    register_widget( 'widget_wp_api' );
}

add_action( 'widgets_init', 'wpb_load_widget' );
 
// Creating the widget 
class widget_wp_api extends WP_Widget {
 
	function __construct() {

		parent::__construct(
		 
		// Base ID of your widget
		'widget_wp_api', 
		 
		// Widget name will appear in UI
		__('Widget WP API', 'widget_wp_api_domain'), 
		 
		// Widget description
		array( 'description' => __( 'Extracts posts from other WP sites that are in certain categories or have certain tags', 'widget_wp_api_domain' ), ) 
		);

	}
	 
	// Creating widget front-end
	public function widget( $args, $instance ) {

		$target = apply_filters( 'widget_target', $instance['target'] );
		
		if ($instance['post_category'] != 0) {
			$target = $target . '/wp-json/wp/v2/posts?categories=' . $instance['post_category'];
		}else{
			error_log('select a category');
			$target = $target . '/wp-json/wp/v2/posts';
		}


		$request = $target;
		$response = wp_remote_get( $request, $this->params );

		if( is_wp_error( $response ) ) {
			return $response;
		}

		$body_json = json_decode( $response['body'], true );
		 
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];

		if ( ! empty( $target ) )
		 
		// This is where you run the code and display the output
		?> <h2 class="widget-title">Posts Based on Category</h2><?php

		foreach ($body_json as $key => $value) {

			?>
			    <ul>
			     	<li><a href=<?php echo $value['link']?>><?php echo $value['title']['rendered'];?></a></li>
			    </ul>
			<?php

		}

	}
	         
	// Widget Backend 
	public function form( $instance ) {

		if ( isset( $instance[ 'target' ] ) ) {

		$target = $instance[ 'target' ];
		}
		else {
		$target = __( 'New target', 'widget_wp_api_domain' );
		}

		$request = 'https://pioneer-solar.com/wp-json/wp/v2/categories';
		$response = wp_remote_get( $request, $this->params );

		if( is_wp_error( $response ) ) {
			return $response;
		}

		$body_json = json_decode( $response['body'], true );

		// Widget admin form
		?>
		<p>
		  <label for="<?php echo $this->get_field_id( 'target' ); ?>"><?php _e( 'Target Website: (https//mywordpresssite.com)' ); ?></label> 
		  <input class="widefat" id="<?php echo $this->get_field_id( 'target' ); ?>" name="<?php echo $this->get_field_name( 'target' ); ?>" type="text" value="<?php echo esc_attr( $target ); ?>" />

			<p>Choose which categories of posts to display in fron end

				<select id="<?php echo $this->get_field_id( 'post_category' ); ?>" name="<?php echo $this->get_field_name( 'post_category' ); ?>" class="widefat" data-placeholder="Select Category">
				<option value= "0" selected="selected">Select A Category</option>

					<?php 

						foreach ($body_json as $key => $value) {

							?><option value="<?php echo esc_attr($value['id']);?>"<?php selected( $value['id'], $instance['post_category'] ) ?>><?php echo strip_tags($value['name']); ?></option><?php

						}

					?>

				</select>

			</p>

		</p>
		<?php 

	}
	     
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {

		$instance = array();
		$instance['target'] = ( ! empty( $new_instance['target'] ) ) ? strip_tags( $new_instance['target'] ) : '';
		$instance['post_category'] = esc_sql( $new_instance['post_category'] );

		return $instance;

	}

}