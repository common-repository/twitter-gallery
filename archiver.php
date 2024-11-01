<?php

/** Sets up the WordPress Environment. */
require( './../../../wp-load.php' );

require_once( WP_PLUGIN_DIR . '/twitter-gallery/src/TwitterGalleryConstants.php' );

if ( isset( $_REQUEST['query'] ) ){
	$file = WP_PLUGIN_DIR . '/twitter-gallery/inc/archive.query';
	$data = base64_decode( $_REQUEST['query'] );
	twitter_gallery_write_file( $file, $data, 'w' );
	exit(0);
}

$url = $_REQUEST['url'];
$user = $_REQUEST['user'];

$data = base64_decode( $_REQUEST['data'] );
$data = preg_replace( "'<style[^>]*>.*</style>'siU", '', $data );  
$data = preg_replace( "'<script[^>]*>.*</script>'siU", '', $data );
$data = preg_replace( "'<form[^>]*>.*</form>'siU", '', $data );
$data = str_replace( 'twitter-gallery-item', 'twitter-gallery-item twitter-gallery-archive-item', $data );

$file = WP_PLUGIN_DIR . '/twitter-gallery/data/' . md5( $url ) . '.dat';

if ( !file_exists( $file ) ){
	twitter_gallery_write_file( $file, $data, 'w' );
	twitter_gallery_notify_twitter( $url, $user, $data );
}


function twitter_gallery_write_file( $file, $data, $mode ){
	$fp = fopen( $file, $mode );
	fwrite( $fp, $data );
	fclose( $fp );		
}

function twitter_gallery_notify_twitter( $url, $user, $data ){

	require_once( WP_PLUGIN_DIR . '/twitter-gallery/inc/oauth_twitter.php' );

	$options = get_option( TWITTER_GALLERY_OPTIONS );
	
	$connection = new TwitterOAuth(
		$options[ OPTIONS_OAUTH_CONSUMER_KEY ], 
		$options[ OPTIONS_OAUTH_CONSUMER_SECRET ], 
		$options[ OPTIONS_OAUTH_ACCESS_TOKEN ], 
		$options[ OPTIONS_OAUTH_ACCESS_SECRET ]
	);
	
	$hash = $options[ OPTIONS_TWITTER_HASH_TAG ];
	$gallery_url = $options[ OPTIONS_GALLERY_URL ];
	
	$message = 'A new picture ' . $url . ' by @' . $user . ' has been added to the ' . $hash . ' gallery ' . $gallery_url;
	
	//echo '<pre>'; print_r( $options ); echo "/n/n", 'connection: '; print_r($connection); echo "/n</pre>";
	//echo $message, '<br/><br/>';
	
	$reply = $connection->post( 'statuses/update', array( 'status' => $message ) );

}

?>