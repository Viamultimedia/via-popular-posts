<?php
/**
* Plugin Name: Via Popular Posts
* Plugin URI: http://viamultimedia.ca
* Description: Widget permettant d'inserer les Articles Populaires dans le sidebar d&eacute;sir&eacute;. 
* Version: 1.1
* Author: Tony Breboin
* Author URI: http://viamultimedia.ca
* Tags: custom post types, post types, latest posts, sidebar widget, plugin
* License: GPL 2.0
*/

// register widget
add_action('widgets_init', create_function('', 'return register_widget("via_popular_posts");'));

///////////////////////////////////////
// Popular Posts By views for Posts
///////////////////////////////////////

function via_post_views($postID) {
$count_key = 'via_views_count';
$count = get_post_meta($postID, $count_key, true);
if($count==''){
$count = 0;
delete_post_meta($postID, $count_key);
add_post_meta($postID, $count_key, '0');
}else{
$count++;
update_post_meta($postID, $count_key, $count);
}}
//To keep the count accurate, lets get rid of prefetching
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

///////////////////////////////////////
// Tracks Views
///////////////////////////////////////
function via_track_post_views ($post_id) {
if ( !is_single() ) return;
if ( empty ( $post_id) ) {
	global $post;
$post_id = $post->ID;    
}
via_post_views($post_id);}
add_action( 'wp_head', 'via_track_post_views');

///////////////////////////////////////
// Get Posts Views
///////////////////////////////////////
function via_get_post_views($postID){
$count_key = 'via_views_count';
$count = get_post_meta($postID, $count_key, true);
if($count==''){
delete_post_meta($postID, $count_key);
add_post_meta($postID, $count_key, '0');
return "0 Vue";
}
return $count.' Vues';
}

///////////////////////////////////////
// Admin Columns display posts Views
///////////////////////////////////////

add_filter('manage_posts_columns', 'via_column_views');
add_action('manage_posts_custom_column', 'via_custom_column_views',5,2);
function via_column_views($defaults){
    $defaults['via_post_views'] = __('Vues');
    return $defaults;
}
function via_custom_column_views($column_name, $id){
        if($column_name === 'via_post_views'){
        echo via_get_post_views(get_the_ID());
    }
}


///////////////////////////////////////
// Popular posts Widget
///////////////////////////////////////

class via_popular_posts extends WP_Widget {
 
function via_popular_posts() {
 $widget_ops = array('classname' => 'via_popular_posts', 'description' => __('Les Articles les Plus Populaires'));
 $this->WP_Widget('via_popular_posts', __('Via Articles Populaires'), $widget_ops, $control_ops);
 }
 
function widget($args, $instance){
 extract($args);
 
//$options = get_option('custom_recent');
 $title = $instance['title'];
 $postscount = $instance['posts'];
 $une = $instance['une'];
 $viasticky = $instance['via-sticky']? 'true' : 'false';
 $viacolor = $instance['via-color'];
 $viacolortext = $instance['via-color-text'];
 
//GET the posts
global $post;
$sticky = get_option( 'sticky_posts' );
$myposts = get_posts(array(
'meta_key' => 'via_views_count',
'orderby' => 'meta_value_num',
'numberposts' =>$postscount
));
 
echo $before_widget . $before_title . $title . $after_title;
 
//SHOW the posts
foreach($myposts as $post){

?>
<div class="via-popular-post">
    <?php if(is_sticky()) { ?>
	<div class="via-side-special">
	<span style="background:<?php if ( $viacolor ) { echo $viacolor; } else { echo '#bf392e'; }; ?>; 
	color:<?php if ( $viacolortext ) { echo $viacolortext; } else { echo '#ffffff'; }; ?>">
	<?php if ( $une == true ) { echo $instance['une']; } else { echo 'A la Une'; } ?></span>		
	</div>
	<?php } ?>
    <div class="via-popular-thumbnail">
	<a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>">
	<?php the_post_thumbnail(); ?>
    </a>	
	</div>
	<div class="via-popular-content">
		<h3>
			<a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>">
			<?php echo substr(strip_tags($post->post_title), 0, 60);  ?>
			</a>
		</h3>
		<p><?php $tcontent = strip_tags( get_the_content() ); if ( mb_strlen( $tcontent ) >= 90 ) echo mb_substr( $tcontent, 0, 90 ).'...'; else echo $tcontent; ?></p>
		<span class="via-popular-date"><?php the_time('j M Y') ?> 
        <?php if('on' == $instance['via-sticky'] ) { ?>
		- <?php echo via_get_post_views(get_the_ID()); ?>
		<?php } ?>	
		</span>
	</div>
	<div class="via-clear"></div>
	
	
</div>
<?php
}
echo $after_widget;
}

 
function update($newInstance, $oldInstance){
 $instance = $oldInstance;
 $instance['title'] = strip_tags($newInstance['title']);
 $instance['posts'] = $newInstance['posts'];
 $instance['une'] = $newInstance['une'];
 $instance['via-sticky'] = $newInstance['via-sticky'];
 $instance['via-color'] = $newInstance['via-color'];
 $instance['via-color-text'] = $newInstance['via-color-text'];
 
return $instance;
}

function form($instance) {
 
$defaults = array( 'title' => __( ''), 'posts' => '' );
$instance = wp_parse_args( (array) $instance, $defaults );
$une = $instance['une'];
$viasticky = $instance['via-sticky'];
$viacolor = $instance['via-color'];
$viacolortext = $instance['via-color-text'];

?>
 
<p>
<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Titre :'); ?></label>
<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" />
</p>
 
<p>
<label for="<?php echo $this->get_field_id('posts'); ?>"><?php _e( 'Nombre D&eacute;sir&eacute :'); ?></label>
<input type="text" class="widefat" id="<?php echo $this->get_field_id('posts'); ?>" name="<?php echo $this->get_field_name('posts'); ?>" value="<?php echo $instance['posts']; ?>" />
</p>

<p>
<label for="<?php echo $this->get_field_id('une'); ?>"><?php _e( 'Titre de votre article à la Une :'); ?></label>
<input type="text" class="widefat" id="<?php echo $this->get_field_id('une'); ?>" name="<?php echo $this->get_field_name('une'); ?>" value="<?php echo $instance['une']; ?>" />
</p>

<p>
	<label for="<?php echo $this->get_field_id('via-color'); ?>"><?php _e( 'Background Ribbon à la Une :'); ?></label>
	<input type="text" class="widefat" id="<?php echo $this->get_field_id('via-color'); ?>" name="<?php echo $this->get_field_name('via-color'); ?>" value="<?php echo $instance['via-color']; ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id('via-color-text'); ?>"><?php _e( 'Couleur Texte Ribbon :'); ?></label>
	<input type="text" class="widefat" id="<?php echo $this->get_field_id('via-color-text'); ?>" name="<?php echo $this->get_field_name('via-color-text'); ?>" value="<?php echo $instance['via-color-text']; ?>" />
</p>
<p>
    <input class="checkbox" type="checkbox" <?php checked($instance['via-sticky'], 'on'); ?> id="<?php echo $this->get_field_id('via-sticky'); ?>" name="<?php echo $this->get_field_name('via-sticky'); ?>" /> 
    <label for="<?php echo $this->get_field_id('via-sticky'); ?>">Afficher le nombre de Vues </label>
</p>
<?php
}
}
 
///////////////////////////////////////
// Fonction Load CSS
///////////////////////////////////////
function via_popular_stylesheet() {

// Style.css 
    wp_register_style( 'via-popular', plugins_url('/style.css', __FILE__) );
    wp_enqueue_style( 'via-popular' );
}
add_action( 'wp_enqueue_scripts', 'via_popular_stylesheet' );

?>
