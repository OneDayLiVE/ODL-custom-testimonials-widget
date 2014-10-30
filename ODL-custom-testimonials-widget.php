<?php
/*
Plugin Name: ODL Custom Testimonials Widget
Plugin URI: http://goldplugins.com/our-plugins/easy-faqs-details/
Description: Easy Testimonials - Provides custom post type, shortcodes, widgets, and other functionality for Testimonials.
Author: Illuminati Karate
Version: 1.3.8
Author URI: http://illuminatikarate.com
GitHub Plugin URI: https://github.com/OneDayLiVE/ODL-custom-testimonials-widget
GitHub Branch:     nativeyards.com
*/
/*
This file is part of Easy Testimonials.

Easy Testimonials is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Easy Testimonials is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Easy Testimonials.  If not, see <http://www.gnu.org/licenses/>.

Shout out to http://www.makeuseof.com/tag/how-to-create-wordpress-widgets/ for the help
*/

class customTestimonialWidget extends WP_Widget
{
	function customTestimonialWidget(){
		$widget_ops = array('classname' => 'customTestimonialWidget', 'description' => 'Displays a custom Testimonial.' );
		$this->WP_Widget('customTestimonialWidget', 'Easy Custom Testimonial', $widget_ops);
	}

	function form($instance){
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'use_excerpt' => 0, 'count' => 1, 'show_title' => 0, 'category' => '', 'show_rating' => false ) );
		$title = $instance['title'];
		$count = $instance['count'];
		$show_title = $instance['show_title'];
		$show_rating = $instance['show_rating'];
		$use_excerpt = $instance['use_excerpt'];
		$category = $instance['category'];
		?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('count'); ?>">Count: <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo esc_attr($count); ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('show_title'); ?>">Show Testimonial Title: </label><input class="widefat" id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>" type="checkbox" value="1" <?php if($show_title){ ?>checked="CHECKED"<?php } ?>/></p>
			<p><label for="<?php echo $this->get_field_id('use_excerpt'); ?>">Use Testimonial Excerpt: </label><input class="widefat" id="<?php echo $this->get_field_id('use_excerpt'); ?>" name="<?php echo $this->get_field_name('use_excerpt'); ?>" type="checkbox" value="1" <?php if($use_excerpt){ ?>checked="CHECKED"<?php } ?>/></p>
			<p><label for="<?php echo $this->get_field_id('category'); ?>">Category Slug: <input class="widefat" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" type="text" value="<?php echo esc_attr($category); ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('show_rating'); ?>">Show Rating: </label></p>
			<p><select name="<?php echo $this->get_field_name('show_rating'); ?>" id="<?php echo $this->get_field_id('show_rating'); ?>">	
				<option value="before" <?php if(esc_attr($show_rating) == "before"): echo 'selected="SELECTED"'; endif; ?>>Before Testimonial</option>
				<option value="after" <?php if(esc_attr($show_rating) == "after"): echo 'selected="SELECTED"'; endif; ?>>After Testimonial</option>
				<option value="" <?php if(esc_attr($show_rating) == ""): echo 'selected="SELECTED"'; endif; ?>>Do Not Show</option>
			</select></p>
		<?php
	}

	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['count'] = $new_instance['count'];
		$instance['show_title'] = $new_instance['show_title'];
		$instance['use_excerpt'] = $new_instance['use_excerpt'];
		$instance['category'] = $new_instance['category'];
		$instance['show_rating'] = $new_instance['show_rating'];
		return $instance;
	}

	function widget($args, $instance){
		extract($args, EXTR_SKIP);

		echo $before_widget;
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$count = empty($instance['count']) ? 1 : $instance['count'];
		$show_title = empty($instance['show_title']) ? 0 : $instance['show_title'];
		$use_excerpt = empty($instance['use_excerpt']) ? 0 : $instance['use_excerpt'];
		$category = empty($instance['category']) ? '' : $instance['category'];
		$show_rating = empty($instance['show_rating']) ? false : $instance['show_rating'];

		if (!empty($title)){
			echo $before_title . $title . $after_title;;
		}
		
		echo outputCustomTestimonial(array('testimonials_link' => get_option('testimonials_link'), 'count' => $count, 'show_title' => $show_title, 'category' => $category, 'use_excerpt' => $use_excerpt, 'show_rating' => $show_rating));

		echo $after_widget;
	} 
}


function testimonial_register_widgets() {
	register_widget( 'customTestimonialWidget' );
}

add_action( 'widgets_init', 'testimonial_register_widgets' );

//load testimonials into an array and output a random one
function outputCustomTestimonial($atts){
	//load shortcode attributes into an array
	extract( shortcode_atts( array(
		'testimonials_link' => get_option('testimonials_link'),
		'count' => 1,
		'word_limit' => 40,
		'body_class' => 'testimonial_body',
		'author_class' => 'testimonial_author',
		'show_title' => true,
		'short_version' => false,
		'use_excerpt' => true,
		'category' => '',
		'show_thumbs' => '',
		'show_rating' => false
	), $atts ) );
	
	$show_thumbs = ($show_thumbs == '') ? get_option('testimonials_image') : $show_thumbs;
	
	//load testimonials into an array
	$i = 0;
	$loop = new WP_Query(array( 'post_type' => 'testimonial','posts_per_page' => '-1', 'easy-testimonial-category' => $category));
	while($loop->have_posts()) : $loop->the_post();
		$postid = get_the_ID();	

		//load rating
		//if set, append english text to it
		$testimonials[$i]['rating'] = get_post_meta($postid, '_ikcf_rating', true); 
		if(strlen($testimonials[$i]['rating'])>0){
			$testimonials[$i]['rating'] = '<span class="easy_t_ratings">' . $testimonials[$i]['rating'] . '/5 Stars.</span>';
		}	

		if($use_excerpt){
			$testimonials[$i]['content'] = get_the_excerpt();
		} else {				
			$testimonials[$i]['content'] = get_the_content();
		}
		
		//if nothing is set for the short content, use the long content
		if(strlen($testimonials[$i]['content']) < 2){
			$temp_post_content = get_post($postid); 			
			if($use_excerpt){
				$testimonials[$i]['content'] = $temp_post_content->post_excerpt;
				if($testimonials[$i]['content'] == ''){
					$testimonials[$i]['content'] = wp_trim_excerpt($temp_post_content->post_content);
				}
			} else {				
				$testimonials[$i]['content'] = $temp_post_content->post_content;
			}
		}
		
		if ($word_limit) {
			$testimonials[$i]['content'] = word_trim($testimonials[$i]['content'], $word_limit, TRUE);
		}
			
		if(strlen($show_rating)>2){
			if($show_rating == "before"){
				$testimonials[$i]['content'] = $testimonials[$i]['rating'] . ' ' . $testimonials[$i]['content'];
			}
			if($show_rating == "after"){
				$testimonials[$i]['content'] =  $testimonials[$i]['content'] . ' ' . $testimonials[$i]['rating'];
			}
		}
		
		if ($show_thumbs) {
			$testimonial_image_size = isValidKey() ? get_option('easy_t_image_size') : "easy_testimonial_thumb";
			if(strlen($testimonial_image_size) < 2){
				$testimonial_image_size = "easy_testimonial_thumb";
			}
			
			$testimonials[$i]['image'] = get_the_post_thumbnail($postid, $testimonial_image_size);
			if (strlen($testimonials[$i]['image']) < 2 && get_option('easy_t_mystery_man')){
				$testimonials[$i]['image'] = '<img class="attachment-easy_testimonial_thumb wp-post-image" src="' . plugins_url('include/css/mystery_man.png', __FILE__) . '" />';
			}
		}
		
		$testimonials[$i]['title'] = get_the_title($postid);	
		$testimonials[$i]['postid'] = $postid;	
		$testimonials[$i]['client'] = get_post_meta($postid, '_ikcf_client', true); 	
		$testimonials[$i]['position'] = get_post_meta($postid, '_ikcf_position', true); 
		
		$i++;
	endwhile;
	wp_reset_query();
	
	$randArray = UniqueRandomNumbersWithinRange(0,$i-1,$count);
	
	ob_start();
	
	foreach($randArray as $key => $rand){
		if(isset($testimonials[$rand])){
			$this_testimonial = $testimonials[$rand];
			if(!$short_version){			
				echo build_custom_testimonial($this_testimonial,$show_thumbs,$show_title,$this_testimonial['postid'],$author_class,$body_class,$testimonials_link);
			} else {
				// echo $this_testimonial['content'];
				// echo '<a href="'.get_the_permalink().'" class="more-link">Continue reading <i class="fa fa-chevron-right"></i></a>';
			}
		}
	}
	
	$content = ob_get_contents();
	ob_end_clean();
	
	return $content;
}


//given a full set of data for a testimonial
//assemble the html for that testimonial
//taking into account current options
function build_custom_testimonial($testimonial,$show_thumbs,$show_title,$postid,$author_class,$body_class,$testimonials_link){
?>
	<blockquote class="easy_testimonial">		
		<?php if ($show_thumbs) {
			echo $testimonial['image'];
		} ?>		
		<?php if ($show_title) {
			echo '<p class="easy_testimonial_title">' . get_the_title($postid) . '</p>';
		} ?>	
		<?php if(get_option('meta_data_position')): ?>
			<p class="<?php echo $author_class; ?>">
				<?php if(strlen($testimonial['client'])>0 || strlen($testimonial['position'])>0 ): ?>
				<cite><span class="testimonial-client"><?php echo $testimonial['client'];?></span><br/><span class="testimonial-position"><?php echo $testimonial['position'];?></span></cite>
				<?php endif; ?>
			</p>	
		<?php endif; ?>
		<div class="<?php echo $body_class; ?>">
				<?php echo wpautop($testimonial['content']); ?>			
		</div>	
			<p class="<?php echo $author_class; ?>">
				<?php if(strlen($testimonial['client'])>0 || strlen($testimonial['position'])>0 ): ?>
				<cite><span class="testimonial-client"><?php echo $testimonial['client'];?></span><br/><span class="testimonial-position"><?php echo $testimonial['position'];?></span></cite>
				<?php endif; ?>
			</p>
			<div class="clearfix">
			<a href="<?php echo get_the_permalink($postid); ?>" class="">Continue reading <i class="fa fa-chevron-right"></i></a><br/>
		</div>	
	</blockquote>
			

<?php
}
