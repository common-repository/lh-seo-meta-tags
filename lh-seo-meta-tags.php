<?php
/**
 * Plugin Name: LH SEO Meta Tags
 * Plugin URI: https://lhero.org/portfolio/lh-seo-meta-tags/
 * Description: Customise your SEO meta tags the LocalHero way.
 * Version: 1.01
 * Author: Peter Shaw
 * Author URI: http://shawfactor.com/
 * Tags: Meta, html, head
 * Text Domain: lh_seo_meta_tags
 * Domain Path: /languages
*/

if (!class_exists('LH_seo_meta_tags_plugin')) {


class LH_seo_meta_tags_plugin {


var $options;
var $namespace = 'lh_html_meta_tags';
var $opt_name = 'lh_html_meta_tags-options';
var $site_keywords_field_name = 'lh_html_meta_tags-site_keywords';
var $site_google_meta_tag_id_field_name = 'lh_html_meta_tags-google_meta_tag_id';

private static $instance;

static function comma_tags($tags){
    

  if($tags) {
    $t = array();
    foreach($tags as $tag)  {
      $t[] = $tag->name;
    }
    return implode(",", $t);
 
  } else {
    return false;
  }
}


private function array_fix( $array ){

return array_filter(array_map( 'trim', $array ));

}


static function truncate_string($string,$min) {
    $text = strip_shortcodes(trim(strip_tags($string)));
    if(strlen($text)>$min) {
        $blank = strpos($text,' ');
        if($blank) {
            # limit plus last word
            $extra = strpos(substr($text,$min),' ');
            $max = $min+$extra;
            $r = substr($text,0,$max);
            if(strlen($text)>=$max) $r=trim($r,'.').'...';
        } else {
            # if there are no spaces
            $r = substr($text,0,$min).'...';
        }
    } else {
        # if original length is lower than limit
        $r = $text;
    }

$r =  trim(preg_replace('/\s\s+/', ' ', $r));
    return $r;
}

private function return_post_description($post){

if (get_post_meta( $post->ID, $this->namespace."-post_object-desc", true) != "" ){  

return esc_attr(self::truncate_string(get_post_meta( $post->ID, $this->namespace."-post_object-desc", true),300));

} elseif ($post->post_excerpt){

return self::truncate_string($post->post_excerpt,300);

} else {

return esc_attr(self::truncate_string(apply_filters('the_content',$post->post_content),300));

}

}


public function create_taxonomies() {

$types = array('page');

register_taxonomy(
    'lh_html_meta_tags-noindex',
   $types,
    array(
        'hierarchical' => false,
        'show_ui' => false
    )
);


}



public function add_html_meta() {


echo "\n<!-- begin LH SEO meta output -->\n";

if (is_home()){

echo "<meta name=\"description\" content=\"".get_bloginfo('description')."\"/>\n";
  
  if (isset($this->options[ $this->site_keywords_field_name ])){
?>
<meta name="Keywords" content="<?php echo implode(",", $this->options[ $this->site_keywords_field_name ]); ?>"/>
<?php
															  }

} elseif (is_singular()){

$terms = wp_get_post_terms( get_the_ID(), 'lh_html_meta_tags-noindex');


if (isset($terms[0]->name) and ($terms[0]->name == 'yes')) { 


echo '<meta name="robots" content="noindex" />' . "\n";


}

if (has_tag()) {
    
    
?>
<meta name="Keywords" content="<?php echo self::comma_tags(get_the_tags()); ?>"/>
<?php


}


    

    






}


if(is_archive() or ( is_home() && have_posts() )){
    
    global $paged;
    
		if(get_previous_posts_link()){ ?>
<link rel="prev" href="<?php echo get_pagenum_link($paged - 1) ?>" />
<?php }

		if(get_next_posts_link()){ ?>
<link rel="next" href="<?php echo get_pagenum_link($paged + 1) ?>" />
<?php }
	}

if (!is_admin() && isset($this->options[ $this->site_google_meta_tag_id_field_name ]) && !empty($this->options[ $this->site_google_meta_tag_id_field_name ])){
    
?>
<meta name="google-site-verification" content="<?php echo $this->options[ $this->site_google_meta_tag_id_field_name ]; ?>" />
<?php   
    
}


echo "<!-- end LH SEO meta output -->\n\n";



}



public function seo_meta_tags_metabox_content(){

$terms = wp_get_post_terms( get_the_ID(), 'lh_html_meta_tags-noindex');

wp_nonce_field( $this->namespace."-metabox-nonce", $this->namespace."-metabox-nonce" );

if (isset($terms[0]->name)){

$name = $terms[0]->name;

} else {

$name = 'no';


}


?>
<label>Hide from Search Engines</label>
<select name="lh_html_meta_tags-noindex" id="lh_html_meta_tags-noindex">
<option value="no" <?php if ($name == 'no') { echo 'selected="selected"';  } ?>>No</option>
<option value="yes" <?php if ($name == 'yes') { echo 'selected="selected"';  } ?>>Yes</option>
</select>
<?php



}


public function add_meta_boxes($post_type, $post)  {

$types = array('page');

if (in_array($post->post_type, $types)) {

add_meta_box($this->namespace."-seo_meta_tags_div", "Seo Meta Tag config", array($this,"seo_meta_tags_metabox_content"), $post_type, "side", "low");

}

}

public function update_post_meta_tags( $post_id, $post, $update ) {

if (isset($_POST[$this->namespace."-metabox-nonce"]) and wp_verify_nonce( $_POST[$this->namespace."-metabox-nonce"], $this->namespace."-metabox-nonce")){

if (($_POST["lh_html_meta_tags-noindex"] == 'yes') || ($_POST["lh_html_meta_tags-noindex"] == 'no')){

wp_set_post_terms( $post_id, array($_POST["lh_html_meta_tags-noindex"]), 'lh_html_meta_tags-noindex');

}

}


}


public function keywords_textbox_callback($args) {  // Textbox Callback

$this->options = get_option( $this->opt_name );


?>

<input type="text" id="<?php echo $this->site_keywords_field_name; ?>" name="<?php echo $this->opt_name.'['.$this->site_keywords_field_name.']'; ?>" value="<?php echo implode(",", $this->options[ $this->site_keywords_field_name ]); ?>" size="50" />
<?php
}


public function google_meta_tag_id_callback($args) {  // Textbox Callback

$this->options = get_option( $this->opt_name );


?>

<input type="text" id="<?php echo $this->site_google_meta_tag_id_field_name; ?>" name="<?php echo $this->opt_name.'['.$this->site_google_meta_tag_id_field_name.']'; ?>" value="<?php echo $this->options[ $this->site_google_meta_tag_id_field_name ]; ?>" size="50" />
<?php
}

public function validate_options( $input ) { 
    
    
        // Create our array for storing the validated options
    $output = array();
     
    // Loop through each of the incoming options
    foreach( $input as $key => $value ) {
         
        // Check to see if the current option has a value. If so, process it.
        if( isset( $input[$this->site_keywords_field_name] ) ) {
         
$pieces = explode(",", sanitize_text_field($input[$this->site_keywords_field_name]));

if (is_array($pieces)){

$output[ $this->site_keywords_field_name ] = $this->array_fix($pieces);

}
             
        } // end if
         
    } // end foreach
    
 //probably need additional validation
$output[ $this->site_google_meta_tag_id_field_name ] = $input[$this->site_google_meta_tag_id_field_name];
     
    // Return the array processing any additional functions filtered by this action
    return apply_filters( 'lh_seo_meta_tags_input_validation', $output, $input );


}

public function add_seo_section() {  
    add_settings_section(  
        $this->opt_name, // Section ID 
        'SEO Data', // Section Title
        'lh_seo_meta_tags_reading_section', // Callback
        'reading' // What Page?  This makes the section show up on the General Settings Page
    );

    add_settings_field( // Option 1
        $this->site_keywords_field_name, // Option ID
        'Keywords', // Label
        array($this, 'keywords_textbox_callback'), // !important - This is where the args go!
        'reading', // Page it will be displayed (General Settings)
        $this->opt_name, // Name of our section
        array( // The $args
            $this->site_keywords_field_name // Should match Option ID
        )  
    ); 
    
    
        add_settings_field( // Option 1
        $this->site_google_meta_tag_id_field_name, // Option ID
        'Google Meta tag ID', // Label
        array($this, 'google_meta_tag_id_callback'), // !important - This is where the args go!
        'reading', // Page it will be displayed (General Settings)
        $this->opt_name, // Name of our section
        array( // The $args
            $this->site_google_meta_tag_id_field_name // Should match Option ID
        )  
    ); 



    register_setting('reading',$this->opt_name, array($this, 'validate_options'));
}

public function lh_sitemaps_general_args($args){
    
$args['tax_query'] = array(
    array(
        'taxonomy' => 'lh_html_meta_tags-noindex',
        'terms' => array('yes'),
        'field' => 'slug',
        'operator' => 'NOT IN',
    ),
);
    
    
    return $args;
    
    
}

    /**
     * Gets an instance of our plugin.
     *
     * using the singleton pattern
     */
    public static function get_instance(){
        if (null === self::$instance) {
            self::$instance = new self();
        }
 
        return self::$instance;
    }



public function __construct() {

$this->options = get_option($this->opt_name);

add_action('wp_head', array($this,"add_html_meta"));
  
//create various taxonomies
add_action( 'init', array($this,"create_taxonomies"),9); 

//add a metabox to the post edit screen
add_action('add_meta_boxes', array($this,"add_meta_boxes"),10,2);

//save metabox content
add_action( 'save_post', array($this,"update_post_meta_tags"),10,3);

//add a section to manage seo data
add_action('admin_init', array($this,"add_seo_section"));  


//remove lh_html_meta_tags-noindex yes from sitemap
add_filter( 'lh_sitemaps_general_args', array($this, 'lh_sitemaps_general_args'), 10, 1 );

}



}


$lh_seo_meta_tags_instance = LH_seo_meta_tags_plugin::get_instance();

}

?>