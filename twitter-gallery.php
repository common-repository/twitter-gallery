<?php

/*
Plugin Name: Twitter Gallery
Plugin URI: http://www.scibuff.com/wordpress-plugins
Description: This plugin creates and maintains a twitter image gallery on a Wordpress page
Version: 1.0
Author: Tomas Vorobjov aka SciBuff
Author URI: htpt://www.scibuff.com
*/

/*  Copyright 2009-10  SciBuff - Twitter Gallery

    This file is part of Twitter Gallery Wordpress Plugin.

    Twitter Gallery is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Twitter Gallery is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Twitter Gallery.  If not, see <http://www.gnu.org/licenses/>.

*/

require_once( 'src/TwitterGallery.php' );

register_activation_hook( __FILE__, 'activate_twitter_gallery' );
register_deactivation_hook( __FILE__, 'deactivate_twitter_gallery' );

global $twitter_gallery;

$twitter_gallery = new TwitterGallery();

if ( !function_exists('activate_twitter_gallery') ){
	/**
	 * This function is executed when this plugin is activated. The
	 * function simply calls the <code>active</code> function of the
	 * <code>WordpressConnect</code>'s object, which takes over the activation
	 * procedures.
	 * 
	 * @since	0.9
	 */
	function activate_twitter_gallery(){
		global $twitter_gallery;
		$twitter_gallery->activate();
	}
}

if ( !function_exists('deactivate_twitter_gallery') ){
	/**
	 * This function is executed when this plugin is activated. The
	 * function simply calls the <code>active</code> function of the
	 * <code>WordpressConnect</code>'s object, which takes over the activation
	 * procedures.
	 * 
	 * @since	0.9
	 */
	function deactivate_twitter_gallery(){
		global $twitter_gallery;
		$twitter_gallery->deactivate();
	}
}

?>
