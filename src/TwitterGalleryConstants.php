<?php

/**
 * @author Tomas Vorobjov
 * @version 1.0
 * @date Dec 21 2010
 * 
 * @file TwitterGalleryConstants.php
 * 
 * Provides constant values for the plugin
 */

define( 'TWITTER_GALLERY_VERSION', '1.0' );
define( 'TWITTER_GALLERY_TEXT_DOMAIN', 'twitter-gallery-text-domain' );
define( 'TWITTER_GALLERY_DATA_FOLDER', WP_PLUGIN_DIR . '/twitter-gallery/data/' );
define( 'TWITTER_GALLERY_INC_FOLDER', WP_PLUGIN_DIR . '/twitter-gallery/inc/' );
define( 'TWITTER_GALLERY_ARCHIVE_QUERY_PATH', TWITTER_GALLERY_INC_FOLDER . 'archive.query' );

define( 'TWITTER_GALLERY_SETTINGS_SECTION_MAIN', 'wp_tg_settings_section_main' );
define( 'TWITTER_GALLERY_SETTINGS_SECTION_PLATFORMS', 'wp_tg_settings_section_platforms' );
define( 'TWITTER_GALLERY_SETTINGS_SECTION_OAUTH', 'wp_tg_settings_section_oauth' );
define( 'TWITTER_GALLERY_SETTINGS_PAGE', 'wp_tg_settings_page' );
define( 'TWITTER_GALLERY_MANAGE_PAGE', 'wp_tg_manage_page' );

/* -------------------- OPTIONS START -------------------- */
define( 'TWITTER_GALLERY_OPTIONS', 'twitter_gallery_options_settings' );
define( 'TWITTER_GALLERY_OPTIONS_MANAGE', 'twitter_gallery_options_manage' );
define( 'OPTIONS_TWITTER_ACCOUNT', TWITTER_GALLERY_OPTIONS . '_twitter_account' );
define( 'OPTIONS_TWITTER_HASH_TAG', TWITTER_GALLERY_OPTIONS . '_twitter_hash_tag' );

define( 'OPTIONS_GALLERY_URL', TWITTER_GALLERY_OPTIONS . '_gallery_url' );

define( 'OPTIONS_PLATFORM_TWITTER', TWITTER_GALLERY_OPTIONS . '_platform_twitter' );
define( 'OPTIONS_PLATFORM_FLICKR', TWITTER_GALLERY_OPTIONS . '_platform_flickr' );
define( 'OPTIONS_PLATFORM_YFROG', TWITTER_GALLERY_OPTIONS . '_platform_yfrog' );
define( 'OPTIONS_PLATFORM_TWITGOO', TWITTER_GALLERY_OPTIONS . '_platform_twitgoo' );
define( 'OPTIONS_PLATFORM_IMGLY', TWITTER_GALLERY_OPTIONS . '_platform_imgly' );
define( 'OPTIONS_PLATFORM_OWLY', TWITTER_GALLERY_OPTIONS . '_platform_owly' );
define( 'OPTIONS_VALUE_IS_PLATFORM_ENABLED', 'on' );

define( 'OPTIONS_OAUTH_API_KEY', TWITTER_GALLERY_OPTIONS . '_oauth_api_key' );
define( 'OPTIONS_OAUTH_CONSUMER_KEY', TWITTER_GALLERY_OPTIONS . '_oauth_consumer_key' );
define( 'OPTIONS_OAUTH_CONSUMER_SECRET', TWITTER_GALLERY_OPTIONS . '_oauth_consumer_secret' );
define( 'OPTIONS_OAUTH_ACCESS_TOKEN', TWITTER_GALLERY_OPTIONS . '_oauth_access_token' );
define( 'OPTIONS_OAUTH_ACCESS_SECRET', TWITTER_GALLERY_OPTIONS . '_oauth_access_secret' );

/* --------------------  OPTIONS END  -------------------- */

?>
