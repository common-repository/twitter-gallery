<?php
/**
 * Template Name: Twitter Gallery Page
 * @author Tomas Vorobjov
 * @version 1.1
 * @date Dec 21, 2010
 * 
 * @file twitter-gallery-page.php
 * 
 */

require_once( WP_PLUGIN_DIR . '/twitter-gallery/src/TwitterGalleryConstants.php' );
require_once( WP_PLUGIN_DIR . '/twitter-gallery/src/TwitterGalleryDataManager.php' );

$options = get_option( TWITTER_GALLERY_OPTIONS );

		// add javascript
		
		wp_enqueue_script('jquery');		
		wp_enqueue_script('jquery.base64', WP_PLUGIN_URL . '/twitter-gallery/js/jquery.base64.min.js', array('jquery'), TWITTER_GALLERY_VERSION );
		wp_enqueue_script('jquery.lightbox', WP_PLUGIN_URL . '/twitter-gallery/js/jquery.lightbox.min.js', array('jquery'), TWITTER_GALLERY_VERSION );
		wp_enqueue_script('jquery.timers', WP_PLUGIN_URL . '/twitter-gallery/js/jquery.timers.min.js', array('jquery'), TWITTER_GALLERY_VERSION );
		wp_enqueue_script('twitter-gallery', WP_PLUGIN_URL . '/twitter-gallery/js/twitter-gallery.js', array('jquery'), TWITTER_GALLERY_VERSION );	
		
		// add styles
		
		wp_enqueue_style('twitter-gallery', WP_PLUGIN_URL . '/twitter-gallery/css/twitter-gallery.css', false, TWITTER_GALLERY_VERSION, 'screen' );

		// should be loaded by javascript
		//wp_enqueue_style('jquery.lightbox', WP_PLUGIN_URL . '/twitter-gallery/css/jquery.lightbox.packed.css', false, TWITTER_GALLERY_VERSION, 'screen' );

function twitter_gallery_get_platforms( $options ){
	
	$platforms = array();
	$platforms[ 'twitpic.com' ] = $options[ OPTIONS_PLATFORM_TWITTER ];
	$platforms[ 'flic.kr/p' ] = $options[ OPTIONS_PLATFORM_FLICKR ];
	$platforms[ 'yfrog.com' ] = $options[ OPTIONS_PLATFORM_YFROG ];
	$platforms[ 'twitgoo.com' ] = $options[ OPTIONS_PLATFORM_TWITGOO ];
	$platforms[ 'img.ly' ] = $options[ OPTIONS_PLATFORM_IMGLY ];
	$platforms[ 'ow.ly/i' ] = $options[ OPTIONS_PLATFORM_OWLY ];

	$string = '';
	
	foreach( $platforms as $platform => $enabled ){
		if ( $enabled == OPTIONS_VALUE_IS_PLATFORM_ENABLED ){
			if ( !empty( $string ) ){ $string .= ','; }
			$string .= '"' . $platform . '"';
		}
	}
	
	return $string;
}

function twitter_gallery_get_query(){
	if ( file_exists( TWITTER_GALLERY_ARCHIVE_QUERY_PATH ) ){
		return file_get_contents( TWITTER_GALLERY_ARCHIVE_QUERY_PATH );
	}
	return '';
}

get_header(); 

?>

		<div id="twitter-gallery">
			<p style="margin:0;">This page will refresh every 60 seconds. Click on any photo to bring up the gallery view or click on the link beneath it to navigate to its source website.</p>
			<p id="twitter-gallery-loading" style="text-align: center"><img src="<?php echo WP_PLUGIN_URL, "/twitter-gallery/images/loading.gif"; ?>" alt="loading..." /></p>
			<div id="twitter-gallery-images">
				<?php TwitterGalleryDataManager::print_archive(); ?>
			</div>
			<div style="clear:both;"></div>
		</div>
		
		<script type="text/javascript" language="JavaScript">
			
			if ( GALLERY ) {
			
				GALLERY.USER = '<?php echo $options[ OPTIONS_TWITTER_ACCOUNT ]; ?>';
				GALLERY.HASH_TAG = '<?php echo $options[ OPTIONS_TWITTER_HASH_TAG ]; ?>';
				GALLERY.PLATFORMS = [<?php echo twitter_gallery_get_platforms( $options ); ?>];
				GALLERY.REG_EXP = GALLERY.createSearchRegExp();
				GALLERY.PLUGIN_BASE_URL = '<?php echo WP_PLUGIN_URL, '/twitter-gallery'; ?>';
				
			}	
				
			
			function initialize(){
				
				if ( GALLERY && GALLERY.Main && GALLERY.DataManager ) {
					
					GALLERY.Main.initialize();
					
					var query = '<?php echo twitter_gallery_get_query(); ?>';
					
					GALLERY.DataManager.setQuery( query );
					GALLERY.DataManager.requestData( false );

					jQuery('#' + GALLERY.Options.IMAGES_CONTAINER_ID ).everyTime('60s',function(i){
						GALLERY.DataManager.requestData( true );
					});								
				}

			}
			if (document.readyState === 'complete ') {
				initialize();
			}
			else {
				jQuery(document).ready(function(){
					initialize();
				});
			}
		</script>
		
		
<?php get_footer(); ?>
