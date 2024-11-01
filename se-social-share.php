<?php
/*
 * Plugin Name: Super Easy Social Share
 * Description: Adds social share links to your website. Includes content buttons and desktop and mobile floating bar 
 * Version: 1.0.0
 * Author: StudioExcel
 * Author URI: https://www.studioexcel.co.uk
 * License URI: license.txt
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
define('SE_SOCIAL_SHARE_VERSION', '1.0.2');

//Add admin menu item
function se_social_share_menu_item(){
  add_submenu_page('options-general.php', 'SE Social Share', 'SE Social Share', 'manage_options', 'social-share', 'se_social_share_page'); 
}

//Enqueue styling
function se_social_share_style() {
  wp_register_style('se-social-share-style', plugin_dir_url(__FILE__) . 'style.css', array(), SE_SOCIAL_SHARE_VERSION);
  wp_enqueue_style('se-social-share-style');
}

//Let's set some defaults on activation
function se_social_share_activate() {

  // Set default values
  $defaults = array(
    'facebook' => 1,
    'twitter' => 1,
    'google' => 1,
    'linkedin' => 1,
    'pinterest' => 1,
    'above' => 1,
    'posts' => 1
  );

  //Save on activation
  if ( get_option( 'se-social-share' ) === false ) {
    update_option( 'se-social-share', $defaults );
  }
}
register_activation_hook( __FILE__, 'se_social_share_activate' );


//Hook up actions and filters
add_action('admin_menu', 'se_social_share_menu_item');
add_action('admin_init', 'se_social_share_settings');
add_filter('the_content', 'se_add_social_share_icons');
add_action('wp_enqueue_scripts', 'se_social_share_style');
add_action('wp_footer', 'se_social_share_floating_bar');
add_action('add_meta_boxes', 'se_social_share_on_page' );
add_action('save_post', 'se_social_share_save_meta');

//Add social share metabox on pages
function se_social_share_on_page($post){
    add_meta_box('se_enable_social_share', 'SE Social Share', 'se_social_share_page_meta', 'page', 'normal' , 'high');
}

function se_social_share_save_meta(){ 
  global $post;
  if( ! current_user_can( 'edit_page', $post_id)) return;
  //Let's sanitize
  //We're only expecting NULL or 1
  if ($_POST["se_enable_social_share"] == '' || $_POST["se_enable_social_share"] == 1 ){
    update_post_meta($post->ID, 'se_enable_social_share', $_POST["se_enable_social_share"]);
  }
}

function se_social_share_page_meta($post){
  $se_enable_social_share = get_post_meta($post->ID, 'se_enable_social_share', true );
  ?>
  <label>
  <input type="checkbox" name="se_enable_social_share" value="1" <?php checked(1, $se_enable_social_share, true); ?> /> Show social share links
  </label>
  <?php
}


//Create options page
function se_social_share_page(){

  ?>

  <style type="text/css">
  .settings-section{
    background: #fefefe;
    margin: 10px 0;
    padding: 20px;
    border: 1px solid #dfdfdf;
  }

  h3{
    margin-top: 5px;
    color: #369;
  }

  .instructions{
    //font-size: 0.8em;
    color: #666;
    font-style: italic;
  }
  .form-table td, .form-table th{
    padding-top: 10px;
    padding-bottom: 10px;
  }

</style>

<div class="wrap">
 
 <h1>Social Sharing Options</h1>
 
 <form method='post' action='options.php'>
   
  <?php settings_fields('social_share_config_section'); ?>
  
  <div class="settings-section">
    <h3>Networks</h3>
    <div class="instructions">
      Select which social networks should be included in your social share links 
    </div>
    <?php do_settings_sections('social-share-networks'); ?>
  </div>

  <div class="settings-section">
    <h3>Appearance</h3>
    <div class="instructions">
      Choose what text should be prepended to the share links (it's ok to leave it empty) and you can decide if you want the share links to appear above or below the content of your post or page. You can enable both if you like. 
    </div>
    <?php do_settings_sections('social-share-appearance'); ?>
  </div>

  <div class="settings-section">
    <h3>Location</h3>
    <div class="instructions">
      Here you can choose to show the share links to appear on post or hide them altogether. If you want the share links to appear on pages you can enable them on individual pages. You can also use the floating bar - on left or right on desktop and tablet and below the footer on mobile. Whatever works best for your website. 
    </div>
    <?php do_settings_sections('social-share-location'); ?>
  </div>
  
  <?php submit_button(); ?>
</form>
</div>
<?php
}

//Create settings sections and register
function se_social_share_settings(){
  add_settings_section('social_share_config_section', '', null, 'social-share-networks');
  add_settings_section('social_share_config_section', '', null, 'social-share-appearance');
  add_settings_section('social_share_config_section', '', null, 'social-share-location');
  
  add_settings_field('se-social-share-facebook', 'Facebook', 'se_social_share_facebook_checkbox', 'social-share-networks', 'social_share_config_section');
  add_settings_field('se-social-share-twitter', 'Twitter', 'se_social_share_twitter_checkbox', 'social-share-networks', 'social_share_config_section');
  add_settings_field('se-social-share-linkedin', 'LinkedIn', 'se_social_share_linkedin_checkbox', 'social-share-networks', 'social_share_config_section');
  add_settings_field('se-social-share-google', 'Google+', 'se_social_share_google_checkbox', 'social-share-networks', 'social_share_config_section');
  add_settings_field('se-social-share-pinterest', 'Pinterest', 'se_social_share_pinterest_checkbox', 'social-share-networks', 'social_share_config_section');
  
  add_settings_field('se-social-share-prompt', 'Prompt', 'se_social_share_prompt', 'social-share-appearance', 'social_share_config_section');
  add_settings_field('se-social-share-above', 'Above content', 'se_social_share_above', 'social-share-appearance', 'social_share_config_section');
  add_settings_field('se-social-share-below', 'Below content', 'se_social_share_below', 'social-share-appearance', 'social_share_config_section');
  add_settings_field('se-social-share-shape', 'Shape', 'se_social_share_shape', 'social-share-appearance', 'social_share_config_section');
  
  add_settings_field('se-social-share-posts', 'Show on posts', 'se_social_share_posts', 'social-share-location', 'social_share_config_section');
  add_settings_field('se-social-share-float', 'Floating Bar Position', 'se_social_share_float', 'social-share-location', 'social_share_config_section');
  add_settings_field('se-social-share-float-hide-mobile', 'Hide floating bar on mobile', 'se_social_share_float_hide_mobile', 'social-share-location', 'social_share_config_section');

  register_setting('social_share_config_section', 'se-social-share');
}

function se_social_share_facebook_checkbox(){  
  $share = get_option('se-social-share');
  //echo '<pre>';print_r($share); echo '</pre>';
 ?>
 <input type="checkbox" name="se-social-share[facebook]" value="1" <?php checked(1, $share['facebook'], true); ?> />
 <?php
}

function se_social_share_twitter_checkbox(){  
  $share = get_option('se-social-share');
  ?>
  <input type="checkbox" name="se-social-share[twitter]" value="1" <?php checked(1, $share['twitter'], true); ?> />
  <?php
}

function se_social_share_linkedin_checkbox(){  
  $share = get_option('se-social-share');
  ?>
  <input type="checkbox" name="se-social-share[linkedin]" value="1" <?php checked(1, $share['linkedin'], true); ?> />

  <?php
} 

function se_social_share_google_checkbox(){  
  $share = get_option('se-social-share');
  ?>
  <input type="checkbox" name="se-social-share[google]" value="1" <?php checked(1, $share['google'], true); ?> />

  <?php
} 

function se_social_share_pinterest_checkbox(){  
  $share = get_option('se-social-share');
  ?>
  <input type="checkbox" name="se-social-share[pinterest]" value="1" <?php checked(1, $share['pinterest'], true); ?> />

  <?php
} 

function se_social_share_prompt(){  
  $share = get_option('se-social-share');
  ?>
  <input type="text" name="se-social-share[prompt]" value="<?php echo $share['prompt']; ?>" />
  <?php
} 

function se_social_share_above(){  
  $share = get_option('se-social-share');
  ?>
  <input type="checkbox" name="se-social-share[above]" value="1" <?php checked(1, $share['above'], true); ?> />
  <?php
} 

function se_social_share_below(){  
  $share = get_option('se-social-share');
  ?>
  <input type="checkbox" name="se-social-share[below]" value="1" <?php checked(1, $share['below'], true); ?> />
  <?php
} 

function se_social_share_posts(){  
  $share = get_option('se-social-share');
  ?>
  <input type="checkbox" name="se-social-share[posts]" value="1" <?php checked(1, $share['posts'], true); ?> />
  <?php
} 

function se_social_share_float_hide_mobile(){  
  $share = get_option('se-social-share');
  ?>
  <input type="checkbox" name="se-social-share[hide_mobile]" value="1" <?php checked(1, $share['hide_mobile'], true); ?> />
  <?php
} 

function se_social_share_float(){  
  $share = get_option('se-social-share');
  ?>
  <select name="se-social-share[float]">
      <option value="" <?php selected($share['float'], ""); ?>>Hidden</option>
      <option value="left" <?php selected($share['float'], "left"); ?>>Left</option>
      <option value="right" <?php selected($share['float'], "right"); ?>>Right</option>
  </select>
 
  <?php
} 

function se_social_share_shape(){  
  $share = get_option('se-social-share');
  ?>
  <select name="se-social-share[shape]">
      <option value="" <?php selected($share['shape'], ""); ?>>Square</option>
      <option value="rounded" <?php selected($share['shape'], "rounded"); ?>>Rounded</option>
      <option value="circle" <?php selected($share['shape'], "circle"); ?>>Circle</option>
  </select>
 
  <?php
} 

// Display share links in content
function se_add_social_share_icons($content){

  global $post;

  //Fetching plugin options
  $share = get_option('se-social-share');

  //Let's make sure we show the links only on singular pages
  if( ! is_singular() ) return $content;

  //If display above and below content is disabled simply ignore the links and show the content only
  if( empty($share['above']) && empty($share['below'])) return $content;

  //If it's a page let's see if social links are enabled for it
  $se_enable_social_share = get_post_meta($post->ID, 'se_enable_social_share', true );
  if( is_page() && empty($se_enable_social_share)) return $content;

  //If the links are meant to be hidden on posts return the content only
  if( is_singular() && empty($share['posts'])) return $content;

  if( ! empty ($share['shape'])) {
    $shape =  'se-' . $share['shape'];
  } else {
    $shape = NULL;
  }
  
  $html = '<div class="se-social-share-wrapper '.$shape.'">';
  
  if( $share['prompt']){
    $html .= '<div class="se-prompt">' . $share['prompt'] . '</div>'; 
  }
  
  $url = get_permalink($post->ID);
  $url = esc_url($url);
  $text = urlencode(get_the_title($post->ID));

  if( ! empty($share['facebook'])){
    $html .= se_facebook_code($url, $text);
  }

  if( ! empty($share['twitter'])){
    $html .= se_twitter_code($url, $text);
  }

  if( ! empty($share['google'])){
    $html .= se_google_code($url, $text);
  }

  if( ! empty($share['linkedin'])){
   $html .= se_linkedin_code($url, $text);
  }

  if( ! empty($share['pinterest'])){
    $html .= se_pinterest_code($url, $text);
  }

  $html = $html . '<div class="clear"></div></div>';

  if ($share['above']){
    $content = $html . $content;
  }

  if ($share['below']){
    $content = $content . $html;
  }

  $html .= '</div>';

  return $content;
}

//Display the floating bar
function se_social_share_floating_bar(){
  
  $share = get_option('se-social-share');

  if( empty ($share['float'])) return;

  //Let's see how many networks we need to display and apply the right class to the mobile floating bar
  $link_count = 0;
  if( ! empty($share['facebook'])) $link_count++;
  if( ! empty($share['twitter'])) $link_count++;
  if( ! empty($share['google'])) $link_count++;
  if( ! empty($share['linkedin'])) $link_count++;
  if( ! empty($share['pinterest'])) $link_count++;

  $mobile_visibility = NULL;
  if( ! empty($share['hide_mobile']) ) {
    $mobile_visibility = 'se-hide-on-mobile';
  }

  $html = '<div class="se-share-float ' . $mobile_visibility . ' se-' . $share['float'] . ' count'.$link_count.'">';
  
  global $post;
  
  $url = get_permalink($post->ID);
  $url = esc_url($url);
  $text = urlencode(get_the_title($post->ID));

  if( ! empty($share['facebook'])){
    $html .= se_facebook_code($url, $text);
  }

  if( ! empty($share['twitter'])){
    $html .= se_twitter_code($url, $text);
  }

  if( ! empty($share['google'])){
    $html .= se_google_code($url, $text);
  }

  if( ! empty($share['linkedin'])){
   $html .= se_linkedin_code($url, $text);
  }

  if( ! empty($share['pinterest'])){
    $html .= se_pinterest_code($url, $text);
  }

  $html .= '</div>';
  echo $html;
}

function se_facebook_code($url = NULL, $text = NULL){
    $icon = '<img src="' . plugin_dir_url(__FILE__) . '/i/facebook.svg" alt="Facebook">';
    $html .= '<a class="se-link se-facebook"
    href="https://www.facebook.com/sharer.php?t=' . $text . '&amp;u='.$url.'" 
    title="Share on Facebook" 
    onclick="window.open(this.href, \'se-share\', \'left=50,top=50,width=600,height=400,toolbar=0\'); return false;">
    ' . $icon . '</a>';
    return $html;
}

function se_twitter_code($url = NULL, $text = NULL){
    $icon = '<img src="' . plugin_dir_url(__FILE__) . '/i/twitter.svg" alt="Twitter">';
    $html .= '<a class="se-link se-twitter"
    href="https://twitter.com/share?text=' . $text . '&amp;url='.$url.'" 
    title="Share on Twitter" 
    onclick="window.open(this.href, \'se-share\', \'left=50,top=50,width=600,height=400,toolbar=0\'); return false;">
    ' . $icon . '</a>';
    return $html;
}

function se_linkedin_code($url = NULL, $text = NULL){
    $icon = '<img src="' . plugin_dir_url(__FILE__) . '/i/linkedin.svg" alt="LinkedIn">';
    $html .= '<a class="se-link se-linkedin"
    href="https://www.linkedin.com/shareArticle?title=' . $text . '&amp;url='.$url.'" 
    title="Share on LinkedIn" 
    onclick="window.open(this.href, \'se-share\', \'left=50,top=50,width=600,height=400,toolbar=0\'); return false;">
    ' . $icon . '</a>';
    return $html;
}

function se_google_code($url = NULL, $text = NULL){
    $icon = '<img src="' . plugin_dir_url(__FILE__) . '/i/google-plus.svg" alt="Google+">';
    $html .= '<a class="se-link se-google"
    href="https://plus.google.com/share?url=' . $url . '" 
    title="Share on Google Plus" 
    onclick="window.open(this.href, \'se-share\', \'left=50,top=50,width=600,height=400,toolbar=0\'); return false;">
    ' . $icon . '</a>';
    return $html;
}

function se_pinterest_code($url = NULL, $text = NULL){
    $icon = '<img src="' . plugin_dir_url(__FILE__) . '/i/pinterest.svg" alt="Pinterest">';
    $html .= '<a class="se-link se-pinterest"
    href="https://pinterest.com/pin/create/button/?url=' . $url . '&description=' . $title . '" 
    title="Share on Pinterest" 
    onclick="window.open(this.href, \'se-share\', \'left=50,top=50,width=600,height=400,toolbar=0\'); return false;">
    ' . $icon . '</a>';
    return $html;
}
