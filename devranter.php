<?php

/*
Plugin Name: Devranter
Plugin URI:  http://www.chaensel.de/devranter
Description: Display awesome devrant rants. This is an UNOFFICIAL plugin
Version:     0.1
Author:      Christian Hänsel
Author URI:  http://www.chaensel.de
License:     GPL2

Devranter is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Devranter is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
*/


defined('ABSPATH') or die('No fucking script kiddies please!');

class Devranter {
	public static $endpoints = [
		'main_feed' => 'https://www.devrant.io/api/devrant/rants?app=3',
		'single'    => 'https://www.devrant.io/api/devrant/rants/%query%?app=3',
		'profile'   => 'https://www.devrant.io/api/users/%query%?app=3',
		'random'    => 'https://www.devrant.io/api/devrant/rants/surprise?app=3'
	];

	/**
	 * Getting the data from the devrant api
	 *
	 * @param $endpoint
	 *
	 * @return array|mixed|object
	 */
	public static function getData($endpoint) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $endpoint);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,3);
		curl_setopt($ch,CURLOPT_TIMEOUT,10);
		$response = curl_exec($ch);
		curl_close($ch);
		return json_decode($response);
	}


	/**
	 * Load the template
	 *
	 * @param string $template
	 *
	 * @return null|string
	 */
	public static function loadTemplate($template = "default") {
		$templateFile =  dirname(__FILE__)  . DIRECTORY_SEPARATOR . "templates".DIRECTORY_SEPARATOR.$template.".ctp";
		$defaultTemplateFile =  dirname(__FILE__)  . DIRECTORY_SEPARATOR . "templates".DIRECTORY_SEPARATOR."default.ctp";
		if(file_exists($templateFile)) {
			return file_get_contents($templateFile);
		} else {
			return file_get_contents($defaultTemplateFile);
		}
	}

	/**
	 * Getting data for the single rant
	 *
	 * @param null $query
	 */
	public static function getSingleRant($query = null) {
		if(!empty($query['id'])) {
			$endpoint = self::$endpoints['single'];
			$endpoint = str_replace("%query%", $query['id'], $endpoint);
			$rant = self::getData($endpoint)->rant;
			$rant->template = !empty($query['template']) ? $query['template'] : 'default';
			return self::displaySingleRant($rant);
		}
	}

	/**
	 * Displaying the single rant
	 *
	 * @param null $data
	 */
	public static function displaySingleRant($data = null) {
		if(!is_null($data)) {
			$image = null;
			if(isset($data->attached_image->url)) {
				$image = '<img src="'.$data->attached_image->url.'">';
			}
			$template = self::loadTemplate($data->template);
			$output = str_replace("{username}", $data->user_username, $template);
			$output = str_replace("{text}", $data->text, $output);
			$output = str_replace("{id}", $data->id, $output);
			$output = str_replace("{num_upvotes}", $data->num_upvotes, $output);
			$output = str_replace("{num_downvotes}", $data->num_downvotes, $output);
			$output = str_replace("{num_comments}", $data->num_comments, $output);
			$output = str_replace("{date}", date("Y.m.d H:i", $data->created_time), $output);
			$output = str_replace("{image}", $image, $output);
			return $output;
		}
	}


	/**
	 * Getting data for the random rant
	 *
	 * @param null $query
	 */
	public static function getRandomRant($query = null) {
		$endpoint = self::$endpoints['random'];
		$rant = self::getData($endpoint)->rant;
		$rant->template = !empty($query['template']) ? $query['template'] : 'default';
		return self::displaySingleRant($rant);
	}

	/**
	 * Getting data for the random rant widget
	 *
	 * @param null $query
	 */
	public static function getWidgetRant() {
		$endpoint = self::$endpoints['random'];
		$rant = self::getData($endpoint)->rant;
		$rant->template = "widget";
		return self::displaySingleRant($rant);
	}
}



/*
 * Adding the shortcodes
 * */

add_shortcode('devrant-single', ['Devranter', 'getSingleRant']);
add_shortcode('devrant-random', ['Devranter', 'getRandomRant']);



function wpse_load_plugin_css() {
	$plugin_url = plugin_dir_url( __FILE__ );
	wp_enqueue_style( 'devranter', $plugin_url . 'css/devranter.css' );
}
add_action( 'wp_enqueue_scripts', 'wpse_load_plugin_css' );


/*
 * ===================================================
 * The fucking widget
 * ===================================================
 */

class Devranter_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'devranter_widget',
			'description' => 'The awesome Devranter Widget',
		);
		parent::__construct( 'devranter_widget', 'DevranterWidget', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		echo  Devranter::getWidgetRant();
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
	}
}

add_action( 'widgets_init', function(){
	register_widget( 'Devranter_Widget' );
});
