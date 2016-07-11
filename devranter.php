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

class Devranter
{
    public static $endpoints = [
        'main_feed' => 'https://www.devrant.io/api/devrant/rants?app=3',
        'single' => 'https://www.devrant.io/api/devrant/rants/%query%?app=3',
        'profile' => 'https://www.devrant.io/api/users/%query%?app=3',
        'random' => 'https://www.devrant.io/api/devrant/rants/surprise?app=3'
    ];

    public static function devrantGetUserTemplateDir()
    {
        return realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . "devranter_templates";
    }

    public static function devrantPluginActivate()
    {
        $userTemplateDir = self::devrantGetUserTemplateDir();
        if (!is_dir($userTemplateDir)) {
            try {
                mkdir($userTemplateDir);
                chmod($userTemplateDir, 0775);
            } catch (Exception $e) {
                // Nothing to see here... move on
            }
        }
        // User's cutoms CSS
        if (!file_exists($userTemplateDir . DIRECTORY_SEPARATOR . "devranter.custom.css")) {
            touch($userTemplateDir . DIRECTORY_SEPARATOR . "devranter.custom.css");
        }
    }

    /**
     * Getting the data from the devrant api
     *
     * @param $endpoint
     *
     * @return array|mixed|object
     */
    public static function devrant_GetData($endpoint)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
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
    public static function devrant_loadTemplate($template = "default")
    {
        // User defined templates on their own frickin' directory...
        $userTemplateDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . "devranter_templates";
        if (is_dir($userTemplateDir)) {
            // This user actually has that directory! WTF
            $userTemplateFile = $userTemplateDir . DIRECTORY_SEPARATOR . $template . ".ctp";
            if (file_exists($userTemplateFile)) {
                // This user actually has got this template. What a nerd!
                return file_get_contents($userTemplateFile);
            }
        }
        // Fall back to standard Devranter templates
        $templateFile        = dirname(__FILE__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $template . ".ctp";
        $defaultTemplateFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . "default.ctp";
        if (file_exists($templateFile)) {
            return file_get_contents($templateFile);
        } else {
            return file_get_contents($defaultTemplateFile);
        }
    }

    /**
     * Getting data for the single rant
     *
     * @param null $query
     * @return mixed
     */
    public static function devrant_getSingleRant($query = null)
    {
        if (!empty($query['id'])) {
            $endpoint       = self::$endpoints['single'];
            $endpoint       = str_replace("%query%", $query['id'], $endpoint);
            $rant           = self::devrant_GetData($endpoint)->rant;
            $rant->template = !empty($query['template']) ? $query['template'] : 'default';
            return self::devrant_displaySingleRant($rant);
        }
    }

    /**
     * Displaying the single rant
     *
     * @param null $data
     * @return mixed
     */
    public static function devrant_displaySingleRant($data = null)
    {
        if (!is_null($data)) {
            $image = null;
            if (isset($data->attached_image->url)) {
                $image = '<img src="' . $data->attached_image->url . '">';
            }
            $linkback = null;
            if(get_option('devrant_link_back')) {
                $linkback = '
                		<span class="devrant-copy">Devranter by <a href="http://www.chaensel.de/devranter" target="_blank">Christian Hänsel</a></span>
                ';
            }
            $template = self::devrant_loadTemplate($data->template);
            $output   = str_replace("{username}", $data->user_username, $template);
            $output   = str_replace("{text}", $data->text, $output);
            $output   = str_replace("{id}", $data->id, $output);
            $output   = str_replace("{num_upvotes}", $data->num_upvotes, $output);
            $output   = str_replace("{num_downvotes}", $data->num_downvotes, $output);
            $output   = str_replace("{num_comments}", $data->num_comments, $output);
            $output   = str_replace("{date}", date("Y.m.d H:i", $data->created_time), $output);
            $output   = str_replace("{image}", $image, $output);
            $output = str_replace("{footer}", $linkback, $output);
            return $output;
        }
    }


    /**
     * Getting data for the random rant
     *
     * @param null $query
     * @return mixed
     */
    public static function devrant_getRandomRant($query = null)
    {
        $endpoint       = self::$endpoints['random'];
        $rant           = self::devrant_GetData($endpoint)->rant;
        $rant->template = !empty($query['template']) ? $query['template'] : 'default';
        return self::devrant_displaySingleRant($rant);
    }

    /**
     * Getting data for the random rant widget
     *
     * @return mixed
     */
    public static function devrant_getWidgetRant()
    {
        $endpoint       = self::$endpoints['random'];
        $rant           = self::devrant_GetData($endpoint)->rant;
        $rant->template = "widget";
        return self::devrant_displaySingleRant($rant);
    }
}


/*
 * Adding the shortcodes
 * */

add_shortcode('devrant-single', ['Devranter', 'devrant_getSingleRant']);
add_shortcode('devrant-random', ['Devranter', 'devrant_getRandomRant']);

register_activation_hook(__FILE__, ['Devranter', 'devrantPluginActivate']);


function devrant_load_plugin_css()
{
    $plugin_url = plugin_dir_url(__FILE__);
    wp_enqueue_style('devranter', $plugin_url . 'css/devranter.css');

    if (file_exists(Devranter::devrantGetUserTemplateDir() . DIRECTORY_SEPARATOR . "devranter.custom.css")) {
        wp_enqueue_style('devranter-custom', get_site_url() . DIRECTORY_SEPARATOR . "wp-content" . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . "devranter_templates" . DIRECTORY_SEPARATOR . "devranter.custom.css");
    }
}

add_action('wp_enqueue_scripts', 'devrant_load_plugin_css');

/*
 * ===================================================
 * The fucking widget
 * ===================================================
 */

class Devranter_Widget extends WP_Widget
{

    /**
     * Sets up the widgets name etc
     */
    public function __construct()
    {
        $widget_ops = array(
            'classname' => 'devranter_widget',
            'description' => 'The awesome Devranter Widget',
        );
        parent::__construct('devranter_widget', 'Devranter Widget', $widget_ops);
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        echo Devranter::devrant_getWidgetRant();
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     * @return string|void
     */
    public function form($instance)
    {
        // outputs the options form on admin
    }

    /**
     * Processing widget options on save
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     * @return array|void
     */
    public function update($new_instance, $old_instance)
    {
        // processes widget options to be saved
    }
}

add_action('widgets_init', function () {
    register_widget('Devranter_Widget');
});


/***********************************************************************
 *
 * ADMIN PAGE
 */

/** Step 2 (from text above). */
add_action('admin_menu', 'devrant_admin_menu');
add_action('admin_init', 'devrant_admin_custom_settings');

/** Step 1. */
function devrant_admin_menu()
{
    add_options_page('Devranter Options', 'Devranter', 'manage_options', 'devranter-options', 'devrant_admin_options');
}

/** Step 3. */
function devrant_admin_options()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    echo '<div class="wrap">';
    include("devranter-admin.php");
    echo '</div>';
}

function devrant_admin_custom_settings()
{
    register_setting('devrant-settings-group', 'devrant_link_back');
    add_settings_section('devrant-general-settings', 'General Settings', 'devrant_general_options', 'devranter-options');
    add_settings_field('devrant_link_back', 'Link back to me', 'devranter_link_back_field', 'devranter-options', 'devrant-general-settings');
}

function devrant_general_options()
{
    echo '';
}


/*==================== THE FIELDS FOR THE ADMIN THINGY ====================*/

function devranter_link_back_field()
{
    $linkBackChecked = '';
    if(get_option('devrant_link_back')) {
        $linkBackChecked = 'checked="checked"';
    }
    echo '
<label for="devrant_link_back">
    <input type="checkbox" name="devrant_link_back" '.$linkBackChecked.' id="devrant_link_back">
    Link back to the plugin author to show some love?</label>
    ';
}

