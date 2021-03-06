<?php

if ( !defined( 'ABSPATH' ) )
	exit( 'No direct script access allowed' ); // Exit if accessed directly

class Social_Counter {
	/**
	 * @var array
	 *
	 */
	public $allowed_socials = array(
		//'facebook' => 'http://www.facebook.com/sharer/sharer.php?u={link}&t={title}',
		'facebook' => 'http://www.facebook.com/sharer/share.php?u={link}',
		'twitter' => 'http://twitter.com/share?text={title}&url={link}',
		'google-plus' => 'https://plus.google.com/share?url={link}',
		'stumbleupon' => 'http://www.stumbleupon.com/submit?url={link}&title={title}',
		'linkedin' => 'http://www.linkedin.com/shareArticle?mini=true&url={link}&title={title}&source={source}',
		'delicious' => 'http://www.delicious.com/save?v=5&noui&jump=close&url={link}&title={title}',
		'pinterest' => 'http://www.pinterest.com/pin/create/button/?url={url}&media={media}&description={title}'
	);

	/**
	 * @var string
	 *
	 */
	public $url = '';

	/**
	 * @var string
	 *
	 */
	public $title = '';

	/**
	 * @var string
	 *
	 */
	public $thumb = '';

	/**
	 * @var string
	 *
	 */
	public $media = '';

	
	/**
	 * @var array
	 *
	 */
	public $settings = array();

	/**
	 * @var array
	 *
	 */
	public $requested_counts = array();

	/**
	 * @var array
	 *
	 */
	public $args = array();


	function __construct($args = '') {

		$defaults = array(
			'before' => '<div class="social-share-button-container clear">',
			'after' => '</div>',
			'social_buttons' => '',
			'tag_count' => 'span',
			'count_class' => 'social-share-count',
			'wrapper_class' => '',
			'text' => __('Share this on :', 'roots'),
		);

		$this->args = wp_parse_args( $args, $defaults );

		if(!is_array($this->args['social_buttons']) || empty($this->args['social_buttons'])) {
			return;
		}

		foreach($this->args['social_buttons'] as $arg) {
			$this->requested_counts[$arg] = $arg;
		}
	}

	public function init() {

		if(is_singular()) {

			global $post;

			if( !is_singular('page') ) {

				$this->url = get_permalink($post->ID);
				$this->title = get_the_title($post->ID);
				$this->thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID),'thumbnail', true);
				$this->media = $this->thumb[0];

				if(is_array($this->requested_counts) && !empty($this->requested_counts)) {
				
					$output = $this->args['before'];

					$output .= '<div class="header-lined inline">';

					$output .= '<h3>'.$this->args['text'].'</h3>';

					$output .= $this->render_button();

					$output .= $this->args['after'];

					$output .= '</div>';

					return $output;
				}
			}
		}
	}

	public function render_button_after_post($content) {

		return $this->render_after($content);
		
	}

	public function render_button_before_post($content) {

		return $this->render_before($content);
	}


	public function render_after($content) {
		
		$button = $this->init();
		
		$output = $content . $button;

		return $output;
	}

	public function render_before($content) {

		$button = $this->init();

		$output = $button . $content;

		return $output;
	}

	public function render_alone() {

		$button = $this->init();

		$output = $button;

		return $output;
	}

	public function render_button() {

		$output = '';

		foreach($this->requested_counts as $requested_count) {

			$output .= "<div class='social-share-{$requested_count}'>";

			$output .= $this->render_sharer($requested_count);

			$output .= "</div>";

		}

		return $output;

	}

	public function render_sharer($type) {
		
		$sharer_url = $this->allowed_socials;

		if(!array_key_exists($type, $sharer_url))
			return '';


		$output = '';

		$onclick = "javascript:window.open(this.href,\"\", \"width=480,height=480,scrollbars=yes,status=yes\"); return false;";

		$fnSlug = str_replace('-', '_', $type);

		$count = call_user_func(array($this, "get_{$fnSlug}_count"));

		$sharer_url_string = str_replace('{title}', $this->title, str_replace('{link}', urlencode($this->url), $sharer_url[$type]));

		$sharer_url_string = str_replace('{source}', get_bloginfo( 'name', 'display' ), $sharer_url_string );

		$sharer_url_string = str_replace('{media}', $this->media, $sharer_url_string );

		$title_attr = sprintf(__('Share this on %s', 'roots'), ucwords($type));

		$output .= "<a class='{$type} margin right-tiny' onclick='{$onclick}' href='{$sharer_url_string}' title='{$title_attr}' rel='nofollow'>";

		//$output .= "<span class='fa-stack fa-lg'><i class='fa fa-square-o fa-stack-2x'></i><i class='fa fa-{$type} fa-stack-1x'></i>";

		$output .= "<i class='fa fa-{$type}-square'></i>";

		$output .= "</a>";

		$output .= "<{$this->args['tag_count']} class='{$type}-count social-share-button-count {$this->args['count_class']}'>{$count}</{$this->args['tag_count']}>";

		return $output;

	}

	protected function get_google_plus_count() {
		$args = array(
	            'method' => 'POST',
	            'headers' => array(
	                'Content-Type' => 'application/json'
	            ),
	            'body' => json_encode(array(
	                'method' => 'pos.plusones.get',
	                'id' => 'p',
	                'method' => 'pos.plusones.get',
	                'jsonrpc' => '2.0',
	                'key' => 'p',
	                'apiVersion' => 'v1',
	                'params' => array(
	                    'nolog'=>true,
	                    'id'=> $this->url,
	                    'source'=>'widget',
	                    'userId'=>'@viewer',
	                    'groupId'=>'@self'
	                )
	             )),
	            'sslverify'=> false
	        );
	     
	    // retrieves JSON with HTTP POST method for current URL 
	    $json_string = wp_remote_post("https://clients6.google.com/rpc", $args);
	     
	    if (is_wp_error($json_string)){
	        return "0";            
	    } else {       
	        $json = json_decode($json_string['body'], true);
	        if( isset( $json['result'] ) ) {              
	        	return intval( $json['result']['metadata']['globalCounts']['count'] );
	    	} else {
	    		return "0";
	    	}
	    }
	}

	protected function get_twitter_count() {
		// retrieves data with HTTP GET method for current URL     
	    $json_string = wp_remote_get(
	        'https://urls.api.twitter.com/1/urls/count.json?url='.$this->url,
	        array(
	            // disable checking SSL sertificates
	            'sslverify'=>false
	        )
	    );
	     
	    // retrives only body from previous HTTP GET request
	    $json_string = wp_remote_retrieve_body($json_string);
	     
	    // convert body data to JSON format
	    $json = json_decode($json_string, true);
	     
	    // return count of Tweets for requested URL        
	    return (isset( $json['count'] )) ? intval( $json['count'] ) : "0";
	}

	protected function get_facebook_count() {
		 // retrieves data with HTTP GET method for current URL     
	    $json_string = wp_remote_get(
	        'https://graph.facebook.com/'.$this->url,
	        array(
	            // disable checking SSL sertificates
	            'sslverify'=>false
	        )
	    ); 
	     
	    // retrives only body from previous HTTP GET request   
	    $json_string = wp_remote_retrieve_body($json_string);
	     
	    // convert body data to JSON format
	    $json = json_decode($json_string, true);   
	         
	        // return count of Facebook shares for requested URL
	        return (isset( $json['shares'] )) ? intval( $json['shares'] ) : "0";
	}

	protected function get_stumbleupon_count() {
	    $json_string = wp_remote_get(
	        'https://www.stumbleupon.com/services/1.01/badge.getinfo?url='.$this->url,
	        array(
	            'sslverify'=> false
	        )
	    ); 
	     
	    // retrives only body from previous HTTP GET request   
	    $json_string = wp_remote_retrieve_body($json_string);
	     
	    // convert body data to JSON format
	    $json = json_decode($json_string, true);   
	         
	    // return count of Facebook shares for requested URL
	    return (isset( $json['views'] )) ? intval( $json['views'] ) : "0";
	}

	protected function get_linkedin_count() {
	    $json_string = wp_remote_get(
	        'https://www.linkedin.com/countserv/count/share?url='.$this->url.'&format=json',
	        array(
	            'sslverify'=> false
	        )
	    ); 
	     
	    // retrives only body from previous HTTP GET request   
	    $json_string = wp_remote_retrieve_body($json_string);
	     
	    // convert body data to JSON format
	    $json = json_decode($json_string, true);   
	         
	        // return count of Facebook shares for requested URL
	        return (isset( $json['count'] )) ? intval( $json['count'] ) : "0";
	}

	protected function get_pinterest_count() {
	    $json_string = wp_remote_get(
	        'https://api.pinterest.com/v1/urls/count.json?callback=&url='.$this->url,
	        array(
	            'sslverify'=> false
	        )
	    ); 
	     
	    // retrives only body from previous HTTP GET request   
	    $json_string = wp_remote_retrieve_body($json_string);
	     
	    // convert body data to JSON format
	    $json = json_decode($json_string, true);   
	         
	        // return count of Facebook shares for requested URL
	        return (isset( $json['count'] )) ? intval( $json['count'] ) : "0";
	}

	protected function get_delicious_count() {
	    $json_string = wp_remote_get(
	        'http://feeds.delicious.com/v2/json/urlinfo/data?url='.$this->url,
	        array(
	        	'sslverify' => false
	        )
	    ); 
	     
	    // retrives only body from previous HTTP GET request   
	    $json_string = wp_remote_retrieve_body($json_string);
	     
	    // convert body data to JSON format
	    $json = json_decode($json_string, true);   
	         
	        // return count of Facebook shares for requested URL
	        return (isset( $json[0]['total_posts'] )) ? intval( $json[0]['total_posts'] ) : "0";
	}
}