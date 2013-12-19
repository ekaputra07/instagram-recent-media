<?php
/*
Plugin Name: Instagram Recent Media
Plugin URI: http://balitechy.com/wp-plugins/instagram-recent-media/
Description: A simple plugin to display your recent Instagram photos as WordPress widget.
Author: Eka Putra
Version: 1.0
Author URI: http://balitechy.com/
*/


define('IRM_UID_OPTION', 'irm_user_id');
define('IRM_UDATA_OPTION', 'irm_user_data');
define('IRM_CLIENTID_OPTION', 'irm_client_id');


// Wordpress init 
function irm_init(){
    if(!current_user_can( 'manage_options' )) return;
    require plugin_dir_path( __FILE__ ) . 'admin.php';
}
add_action('init', 'irm_init', 20);


// To simplify this plugin operation, I assumes that we don't need to fecth instagram
// every minute or hour. So daily seems to be good choice.
function irm_fetch_instagram(){
    $user_id = get_option(IRM_UID_OPTION);
    $client_id = get_option(IRM_CLIENTID_OPTION);

    if($user_id && $client_id){
        $url = "https://api.instagram.com/v1/users/$user_id/media/recent/?client_id=$client_id";
        $cURL = curl_init();
        curl_setopt($cURL, CURLOPT_URL, $url);
        curl_setopt($cURL, CURLOPT_HTTPGET, true);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($cURL);
        curl_close($cURL);

        $result_arr = json_decode($result, true);
        if(isset($result_arr['data'])){
            update_option(IRM_UDATA_OPTION, $result_arr['data']);
        }else{
            update_option(IRM_UDATA_OPTION, array());
        }
    }
}
add_action('irm_scheduler_hook', 'irm_fetch_instagram');


// Start scheduler on plugin activation.
function irm_activation() {
    wp_schedule_event(time(), 'daily', 'irm_scheduler_hook');
}
register_activation_hook(__FILE__, 'irm_activation');


// Stop scheduler on plugin deactivation.
function irm_deactivation() {
    wp_clear_scheduled_hook('irm_scheduler_hook');
}
register_deactivation_hook(__FILE__, 'irm_deactivation');


// Add settings link on plugin page
function irm_plugin_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=irm_settings">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}

$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'irm_plugin_settings_link');


// Recent media Widget
class IRM_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'iw_insta_widget',
            'Instagram photos',
            array('description' => 'Display your recent Instagram photos.')
        );
    }

    public function widget( $args, $instance ){
        echo $args['before_widget'];

        $title = apply_filters('widget_title', $instance['title']);
        if (!empty($title))
            echo $args['before_title'] . $title . $args['after_title'];

        $user_data = get_option(IRM_UDATA_OPTION);
        echo '<div class="irm_images">';
        $counter = 0;
        if($user_data){
            foreach($user_data as $data){
                if($counter >= $instance['num']){
                    break;
                }else{
                    echo '<a href="'.$data['link'].'" title="'.$data['caption']['text'].'" target="_blank"><img src="'.$data['images']['thumbnail']['url'].'" width="'.$instance['size'].'"></a>';
                    $counter++;
                }
            }
        }
        echo '</div>';
        echo $args['after_widget'];
    }

    public function form($instance){
        // default title
        if(isset($instance['title'])) $title = $instance['title'];
        else $title = 'Instagram photos';

        // default number of image to show
        if(isset($instance['num']))
            $num = $instance['num'];
        else
            $num = 20;

        // default thumb. size
        if(isset($instance['size'])) $size = $instance['size'];
        else $size = 150;

        ?>
        <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id('title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
        <label for="<?php echo $this->get_field_id( 'size' ); ?>">Thumbnail size</label> 
        <input class="widefat" id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>" type="text" value="<?php echo esc_attr($size) ?>" />
        </p>
        <p>
        <label for="<?php echo $this->get_field_id( 'num' ); ?>">Number of images (max. 20)</label> 
        <input class="widefat" id="<?php echo $this->get_field_id( 'num' ); ?>" name="<?php echo $this->get_field_name( 'num' ); ?>" type="text" value="<?php echo esc_attr($num) ?>" />
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = (! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title']) : '';
        $instance['size'] = (! empty( $new_instance['size'] ) ) ? strip_tags( $new_instance['size']) : '';
        $instance['num'] = (! empty( $new_instance['num'] ) ) ? strip_tags( $new_instance['num']) : '';
        return $instance;
    }
}
add_action('widgets_init', create_function('', 'return register_widget("IRM_widget");'));