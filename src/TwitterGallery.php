<?php

require_once( 'TwitterGalleryConstants.php' );


/**
 * @author Tomas Vorobjov
 * @version 1.0
 * @date Dec 21 2010
 * 
 * @file TwitterGallery.php
 * 
 * This class serves as the main class for the Twiter Gqllery 
 * wordpress plugin
 * 
 * twitter account: ISSWavePix
 * password: ISSWave2010
 */

class TwitterGallery {

	/**
	 * @var TwitterGalleryAdminPanel
	 */
	var $adminPanel;

	/**
	 * Creates a new TwitterGallery object
	 * 
	 * @since	0.9
	 * 
	 */
	function TwitterGallery(){
	
		$this->add_init_hook();
	}	
	
	/**
	 * This function is executed when the plugin is actived.
	 * 
	 * @since	0.9
	 */
	function activate(){
		
		// try to chmod the data folder
		@chmod( TWITTER_GALLERY_DATA_FOLDER, 0777 );
		// try to chmod the inc folder
		@chmod( TWITTER_GALLERY_INC_FOLDER, 0777 );
		
		// copy the twitter-gallery-page.php to the current theme directory
		$page_content = file_get_contents( WP_PLUGIN_DIR . '/twitter-gallery/pages/twitter-gallery-page.php' );
		if ( !empty( $page_content ) ){
			$fp = fopen( TEMPLATEPATH . '/twitter-gallery-page.php', 'w');
			fwrite( $fp, $page_content );
			fclose( $fp );
		}
	}
	
	/**
	 * This function is executed when the plugin is deactived.
	 * 
	 * @since	0.9
	 */
	function deactivate(){
		// drop table code?!
	}
	
	/**
	 * Adds init wordpress hook. 
	 * 
	 * For add_feed to work, we have to wait until WordPress has 
	 * completely initialized before calling it. We use the init action 
	 * hook to accomplish this.
	 * 
	 * @private
	 * @since	0.9
	 */
	function add_init_hook(){
		
		add_action( 'init', array( &$this, 'add_wp_hooks' ) );

		//add_action( 'admin_menu', array( &$this, 'add_admin_panel' ) );
		// since 1.0
		add_action( 'admin_init', array( &$this, 'add_admin_settings' ), 9 );
		add_action( 'admin_menu', array( &$this, 'add_admin_panel' ) );
		
	}
	
	/**
	 * Adds wordpress hooks (and filters) necessary for this plugin
	 * 
	 * @private
	 * @since	0.9
	 */
	function add_wp_hooks(){
	}
	
	/**
	 * Adds plugin's admin panel to the wp dashboard
	 * 
	 * @private 
	 * @since	0.9
	 */
	function add_admin_panel(){
		
		//$this->adminPanel = new TwitterGalleryAdminPanel();

		global $wp_tg_options_page;
		
		$wp_tg_options_page = add_menu_page(
			__( 'Twitter Gallery', TWITTER_GALLERY_TEXT_DOMAIN ),
			__( 'Twitter Gallery', TWITTER_GALLERY_TEXT_DOMAIN ), 
			'manage_options', 
			TWITTER_GALLERY_SETTINGS_PAGE, 
			array( &$this, 'admin_section_page' )
		);
		
		global $wp_tg_manage_page;
		
		$wp_tg_manage_page = add_submenu_page(
			TWITTER_GALLERY_SETTINGS_PAGE,
			__( 'Manage Gallery', TWITTER_GALLERY_TEXT_DOMAIN ), 
			__( 'Manage Gallery', TWITTER_GALLERY_TEXT_DOMAIN ), 
			'manage_options', 
			TWITTER_GALLERY_MANAGE_PAGE, 
			array( &$this, 'admin_section_manage_page' )
		);		
	}	
	
	/**
	 * Adds plugin's admin panel to the wp dashboard
	 * 
	 * @private
	 * @since	1.0
	 */
	function add_admin_settings(){
		
		$options = get_option( TWITTER_GALLERY_OPTIONS );
		
		if ( empty( $options[ OPTIONS_TWITTER_HASH_TAG ] ) ) {
			add_action( 'admin_notices', create_function( '', "echo '<div class=\"error\"><p>" . sprintf( __('Twitter Gallery needs configuration information on its <a href="%s">settings</a> page.', TWITTER_GALLERY_TEXT_DOMAIN ), admin_url( 'options-general.php?page=' . TWITTER_GALLERY_SETTINGS_PAGE ) )."</p></div>';" ) );
		}
		
		register_setting( TWITTER_GALLERY_OPTIONS, TWITTER_GALLERY_OPTIONS, array( &$this, 'admin_settings_validate' ) );
		
		// adds sections
		add_settings_section( TWITTER_GALLERY_SETTINGS_SECTION_MAIN, __( 'Main Settings', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_section_text' ), TWITTER_GALLERY_SETTINGS_PAGE );
		add_settings_section( TWITTER_GALLERY_SETTINGS_SECTION_PLATFORMS, __( 'Platforms Settings', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_section_text' ), TWITTER_GALLERY_SETTINGS_PAGE );
		add_settings_section( TWITTER_GALLERY_SETTINGS_SECTION_OAUTH, __( 'OAuth Settings', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_section_text' ), TWITTER_GALLERY_SETTINGS_PAGE );
		
		// twitter account
		add_settings_field( OPTIONS_TWITTER_ACCOUNT, __( 'Twitter Account', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_setting_twitter_account' ), TWITTER_GALLERY_SETTINGS_PAGE, TWITTER_GALLERY_SETTINGS_SECTION_MAIN );
		
		// hash tag
		add_settings_field( OPTIONS_TWITTER_HASH_TAG, __( 'Hash tag', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_setting_hash_tag' ), TWITTER_GALLERY_SETTINGS_PAGE, TWITTER_GALLERY_SETTINGS_SECTION_MAIN );
		
		// gallery url
		add_settings_field( OPTIONS_GALLERY_URL, __( 'Gallery URL', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_setting_gallery_url' ), TWITTER_GALLERY_SETTINGS_PAGE, TWITTER_GALLERY_SETTINGS_SECTION_MAIN );
		
		// platforms
		add_settings_field( OPTIONS_PLATFORM_TWITTER, __( 'twitpic.com', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_setting_platform_twitter' ), TWITTER_GALLERY_SETTINGS_PAGE, TWITTER_GALLERY_SETTINGS_SECTION_PLATFORMS );
		add_settings_field( OPTIONS_PLATFORM_FLICKR, __( 'flic.kr', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_setting_platform_flickr' ), TWITTER_GALLERY_SETTINGS_PAGE, TWITTER_GALLERY_SETTINGS_SECTION_PLATFORMS );
		add_settings_field( OPTIONS_PLATFORM_YFROG, __( 'yfrog.com', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_setting_platform_yfrog' ), TWITTER_GALLERY_SETTINGS_PAGE, TWITTER_GALLERY_SETTINGS_SECTION_PLATFORMS );
		add_settings_field( OPTIONS_PLATFORM_TWITGOO, __( 'twitgoo.com', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_setting_platform_twitgoo' ), TWITTER_GALLERY_SETTINGS_PAGE, TWITTER_GALLERY_SETTINGS_SECTION_PLATFORMS );
		add_settings_field( OPTIONS_PLATFORM_IMGLY, __( 'img.ly', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_setting_platform_imgly' ), TWITTER_GALLERY_SETTINGS_PAGE, TWITTER_GALLERY_SETTINGS_SECTION_PLATFORMS );
		add_settings_field( OPTIONS_PLATFORM_OWLY, __( 'ow.ly', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_setting_platform_owly' ), TWITTER_GALLERY_SETTINGS_PAGE, TWITTER_GALLERY_SETTINGS_SECTION_PLATFORMS );

		// oauth settings
		add_settings_field( OPTIONS_OAUTH_API_KEY, __( 'OAuth API Key', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_setting_oauth_api_key' ), TWITTER_GALLERY_SETTINGS_PAGE, TWITTER_GALLERY_SETTINGS_SECTION_OAUTH );
		add_settings_field( OPTIONS_OAUTH_CONSUMER_KEY, __( 'OAuth Consumer Key', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_setting_oauth_consumer_key' ), TWITTER_GALLERY_SETTINGS_PAGE, TWITTER_GALLERY_SETTINGS_SECTION_OAUTH );
		add_settings_field( OPTIONS_OAUTH_CONSUMER_SECRET, __( 'OAuth Consumer Secret', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_setting_oauth_consumer_secret' ), TWITTER_GALLERY_SETTINGS_PAGE, TWITTER_GALLERY_SETTINGS_SECTION_OAUTH );
		add_settings_field( OPTIONS_OAUTH_ACCESS_TOKEN, __( 'OAuth Access Token', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_setting_oauth_access_token' ), TWITTER_GALLERY_SETTINGS_PAGE, TWITTER_GALLERY_SETTINGS_SECTION_OAUTH );
		add_settings_field( OPTIONS_OAUTH_ACCESS_SECRET, __( 'OAuth Access Secret', TWITTER_GALLERY_TEXT_DOMAIN ), array( &$this, 'admin_setting_oauth_access_secret' ), TWITTER_GALLERY_SETTINGS_PAGE, TWITTER_GALLERY_SETTINGS_SECTION_OAUTH );

	}
	
	/**
	 * Validates admin settings
	 * @param	$input the settings value
	 */
	function admin_settings_validate( $input ){
		
		$input = apply_filters( TWITTER_GALLERY_OPTIONS, $input ); // filter to let sub-plugins validate their options too
		return $input;
		
	}
	
	function admin_section_text(){}
	
	/**
	 * Renders the twitter account setting
	 */
	function admin_setting_twitter_account(){
		
		$options = get_option( TWITTER_GALLERY_OPTIONS );
		echo '<input type="text" id="'.OPTIONS_TWITTER_ACCOUNT.'" name="'.TWITTER_GALLERY_OPTIONS.'['.OPTIONS_TWITTER_ACCOUNT.']" value="'. $options[ OPTIONS_TWITTER_ACCOUNT ] .'" size="20" />';
		_e( "(required)", TWITTER_GALLERY_TEXT_DOMAIN );
		
	}
	
	/**
	 * Renders the twitter hash tag setting
	 */
	function admin_setting_hash_tag(){
		
		$options = get_option( TWITTER_GALLERY_OPTIONS );
		echo '<input type="text" id="'.OPTIONS_TWITTER_HASH_TAG.'" name="'.TWITTER_GALLERY_OPTIONS.'['.OPTIONS_TWITTER_HASH_TAG.']" value="'. $options[ OPTIONS_TWITTER_HASH_TAG ] .'" size="20" />';
		_e( "(required)", TWITTER_GALLERY_TEXT_DOMAIN );
		
	}
	
	/**
	 * Renders the gallery url setting
	 */
	function admin_setting_gallery_url(){
		
		$options = get_option( TWITTER_GALLERY_OPTIONS );
		echo '<input type="text" id="'.OPTIONS_GALLERY_URL.'" name="'.TWITTER_GALLERY_OPTIONS.'['.OPTIONS_GALLERY_URL.']" value="'. $options[ OPTIONS_GALLERY_URL ] .'" size="20" />';
		_e( "(required) - should be a shortened url", TWITTER_GALLERY_TEXT_DOMAIN );
		
	}	
			
	/**
	 * Renders the platform twitter.com
	 */	
	function admin_setting_platform_twitter(){
		$options = get_option( TWITTER_GALLERY_OPTIONS );
		echo '<input type="checkbox" id="'.OPTIONS_PLATFORM_TWITTER.'" name="'.TWITTER_GALLERY_OPTIONS.'['.OPTIONS_PLATFORM_TWITTER.']" '. $this->aux_get_checked_string( $options[ OPTIONS_PLATFORM_TWITTER ] ) .' />';
		_e( "Include images from twitpic.com", TWITTER_GALLERY_TEXT_DOMAIN );
	}
	
	/**
	 * Renders the platform flic.kr
	 */	
	function admin_setting_platform_flickr(){
		$options = get_option( TWITTER_GALLERY_OPTIONS );
		echo '<input type="checkbox" id="'.OPTIONS_PLATFORM_FLICKR.'" name="'.TWITTER_GALLERY_OPTIONS.'['.OPTIONS_PLATFORM_FLICKR.']" '. $this->aux_get_checked_string( $options[ OPTIONS_PLATFORM_FLICKR ] ) .' />';
		_e( "Include images from flic.kr", TWITTER_GALLERY_TEXT_DOMAIN );
	}
	
	/**
	 * Renders the platform yfrog.com
	 */	
	function admin_setting_platform_yfrog(){
		$options = get_option( TWITTER_GALLERY_OPTIONS );
		echo '<input type="checkbox" id="'.OPTIONS_PLATFORM_YFROG.'" name="'.TWITTER_GALLERY_OPTIONS.'['.OPTIONS_PLATFORM_YFROG.']" '. $this->aux_get_checked_string( $options[ OPTIONS_PLATFORM_YFROG ] ) .' />';
		_e( "Include images from yfrog.com", TWITTER_GALLERY_TEXT_DOMAIN );
	}
	
	/**
	 * Renders the platform twitgoo.com
	 */	
	function admin_setting_platform_twitgoo(){
		$options = get_option( TWITTER_GALLERY_OPTIONS );
		echo '<input type="checkbox" id="'.OPTIONS_PLATFORM_TWITGOO.'" name="'.TWITTER_GALLERY_OPTIONS.'['.OPTIONS_PLATFORM_TWITGOO.']" '. $this->aux_get_checked_string( $options[ OPTIONS_PLATFORM_TWITGOO ] ) .' />';
		_e( "Include images from twitgoo.com", TWITTER_GALLERY_TEXT_DOMAIN );
	}
	
	/**
	 * Renders the platform img.ly
	 */	
	function admin_setting_platform_imgly(){
		$options = get_option( TWITTER_GALLERY_OPTIONS );
		echo '<input type="checkbox" id="'.OPTIONS_PLATFORM_IMGLY.'" name="'.TWITTER_GALLERY_OPTIONS.'['.OPTIONS_PLATFORM_IMGLY.']" '. $this->aux_get_checked_string( $options[ OPTIONS_PLATFORM_IMGLY ] ) .' />';
		_e( "Include images from img.ly", TWITTER_GALLERY_TEXT_DOMAIN );
	}
	
	/**
	 * Renders the platform ow.ly
	 */	
	function admin_setting_platform_owly(){
		$options = get_option( TWITTER_GALLERY_OPTIONS );
		echo '<input type="checkbox" id="'.OPTIONS_PLATFORM_OWLY.'" name="'.TWITTER_GALLERY_OPTIONS.'['.OPTIONS_PLATFORM_OWLY.']" '. $this->aux_get_checked_string( $options[ OPTIONS_PLATFORM_OWLY ] ) .' />';
		_e( "Include images from ow.ly", TWITTER_GALLERY_TEXT_DOMAIN );
	}

	/**
	 * 
	 * @param $flag
	 */
	function aux_get_checked_string( $flag ){ 
		return ( $flag == OPTIONS_VALUE_IS_PLATFORM_ENABLED ) ? ' checked="checked"' : '';
	}	
	
	/**
	 * Renders the oauth api key setting
	 */
	function admin_setting_oauth_api_key(){
		
		$options = get_option( TWITTER_GALLERY_OPTIONS );
		echo '<input type="text" id="'.OPTIONS_OAUTH_API_KEY.'" name="'.TWITTER_GALLERY_OPTIONS.'['.OPTIONS_OAUTH_API_KEY.']" value="'. $options[ OPTIONS_OAUTH_API_KEY ] .'" size="32" />';
		_e( "(required)", TWITTER_GALLERY_TEXT_DOMAIN );
		
	}
	
	/**
	 * Renders the oauth consumer key setting
	 */
	function admin_setting_oauth_consumer_key(){
		
		$options = get_option( TWITTER_GALLERY_OPTIONS );
		echo '<input type="text" id="'.OPTIONS_OAUTH_CONSUMER_KEY.'" name="'.TWITTER_GALLERY_OPTIONS.'['.OPTIONS_OAUTH_CONSUMER_KEY.']" value="'. $options[ OPTIONS_OAUTH_CONSUMER_KEY ] .'" size="32" />';
		_e( "(required)", TWITTER_GALLERY_TEXT_DOMAIN );
		
	}

	/**
	 * Renders the oauth consumer secret setting
	 */
	function admin_setting_oauth_consumer_secret(){
		
		$options = get_option( TWITTER_GALLERY_OPTIONS );
		echo '<input type="text" id="'.OPTIONS_OAUTH_CONSUMER_SECRET.'" name="'.TWITTER_GALLERY_OPTIONS.'['.OPTIONS_OAUTH_CONSUMER_SECRET.']" value="'. $options[ OPTIONS_OAUTH_CONSUMER_SECRET ] .'" size="64" />';
		_e( "(required)", TWITTER_GALLERY_TEXT_DOMAIN );
		
	}

	/**
	 * Renders the oauth access token setting
	 */
	function admin_setting_oauth_access_token(){
		
		$options = get_option( TWITTER_GALLERY_OPTIONS );
		echo '<input type="text" id="'.OPTIONS_OAUTH_ACCESS_TOKEN.'" name="'.TWITTER_GALLERY_OPTIONS.'['.OPTIONS_OAUTH_ACCESS_TOKEN.']" value="'. $options[ OPTIONS_OAUTH_ACCESS_TOKEN ] .'" size="64" />';
		_e( "(required)", TWITTER_GALLERY_TEXT_DOMAIN );
		
	}

	/**
	 * Renders the oauth access secret setting
	 */
	function admin_setting_oauth_access_secret(){
		
		$options = get_option( TWITTER_GALLERY_OPTIONS );
		echo '<input type="text" id="'.OPTIONS_OAUTH_ACCESS_SECRET.'" name="'.TWITTER_GALLERY_OPTIONS.'['.OPTIONS_OAUTH_ACCESS_SECRET.']" value="'. $options[ OPTIONS_OAUTH_ACCESS_SECRET ] .'" size="64" />';
		_e( "(required)", TWITTER_GALLERY_TEXT_DOMAIN );
		
	}	

	/**
	 * 
	 */
	function admin_section_page(){
		
		if ( !current_user_can( 'manage_options' ) )  {
		    wp_die( __('You do not have sufficient permissions to access this page.') );
		}
?>		
		
		<div class="wrap">
			<h2><?php _e('Twitter Gallery', TWITTER_GALLERY_TEXT_DOMAIN ) ?></h2>
			<form method="post" action="options.php">
			<?php settings_fields( TWITTER_GALLERY_OPTIONS ); ?>
				<table><tr><td>
				<?php do_settings_sections( TWITTER_GALLERY_SETTINGS_PAGE ); ?>
				</td></tr></table>
				<p class="submit">
					<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
				</p>
			</form>
		</div>
<?php 	
	
	}
	
	/**
	 * 
	 */
	function admin_section_manage_page(){
		
		if ( !current_user_can( 'manage_options' )
			 || ( isset( $_POST['delete-items'] ) && !wp_verify_nonce( $_POST['_wpnonce'], TWITTER_GALLERY_MANAGE_PAGE ) ) ){
		    wp_die( __('You do not have sufficient permissions to access this page.') );
		}

		if ( isset( $_POST['delete-items'] ) && count( $_POST['delete-items'] ) > 0 ){
			foreach( $_POST['delete-items'] as $delete_item ){ 
				$this->writeFile( WP_PLUGIN_DIR . '/twitter-gallery/log', $delete_item . "\n", "w+" );
		        if ( isset( $_POST[ $delete_item ] ) ){
		        	$this->writeFile( TWITTER_GALLERY_DATA_FOLDER . $delete_item . '.dat', "", "w+");
		        }
		    }			
		}
		
		require_once( WP_PLUGIN_DIR . '/twitter-gallery/src/TwitterGalleryDataManager.php' );
		$filenames = TwitterGalleryDataManager::get_filenames();

		$path = WP_PLUGIN_DIR . '/twitter-gallery/data/';
		
?>		
		<div class="wrap">
			<h2><?php _e('Twitter Manage Gallery', TWITTER_GALLERY_TEXT_DOMAIN ) ?></h2>
			<form method="post" action="">
				<?php wp_nonce_field( 'TWITTER_GALLERY_MANAGE_PAGE' ); ?>

				<div style="width:90%;padding:10px">
				
				<?php 
					foreach( $filenames as $filename ){
						$id = str_replace( '.dat', '', $filename );
						$content = file_get_contents( $path . $filename );
						if ( !empty( $content ) ) : ?>
							<div style="float:left;padding:4px;width:80px;height:120px;overflow:hidden;">
								<div style="width:75px;height:75px;overflow:hidden;">
									<?php echo $content ?>
								</div>
								<input type="hidden" name="delete-items[]" value="<?php echo $id; ?>"/>
								<p><input type="checkbox" name="<?php echo $id; ?>"/> Delete</p>
							</div>
						<?php endif; ?>
					<?php } ?>
					<div style="clear:both"></div>
					<p class="submit">
						<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
					</p>
				</div>
			</form>			
			
		</div>
<?php 
	}
	
	function writeFile( $file, $data, $mode ){
		$fp = fopen( $file, $mode );
		fwrite( $fp, $data );
		fclose( $fp );		
	}	
}
?>
