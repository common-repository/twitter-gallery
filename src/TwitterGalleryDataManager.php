<?php

require_once( 'TwitterGalleryConstants.php' );

/**
 * @author Tomas Vorobjov
 * @version 0.9
 * @date Jul 22 2010
 * 
 * @file DataManager.php
 * 
 * This class serves as the data manager class for the Twitter Gallery
 * wordpress plugin
 */
class TwitterGalleryDataManager {
	
	/**
	 * Creates a new TwitterGalleryDataManager object
	 * 
	 * @since	0.9
	 * 
	 */
	function TwitterGalleryDataManager(){
	}		
	
	static function print_archive( $count = -1 ){
	
		$filenames = TwitterGalleryDataManager::get_filenames( $count );
		
		foreach( $filenames as $filename ){
			require_once( TWITTER_GALLERY_DATA_FOLDER . $filename );
		}	
		
	}

	static function get_filenames( $count = -1 ){
	
		$filenames = array();
		
		# Initialise list arrays, files and array counters for them
		$t = 0;
		$f = 0;
		$file_arr_names = array();
		$file_arr_times = array();
		
		if ( @$handle = opendir( TWITTER_GALLERY_DATA_FOLDER ) ) {
			while ( FALSE !== ( $file = readdir( $handle ) ) ) {
				if( $file != "." && $file != ".." && $file != ".htaccess" ) {
					$fName = $file;
					$filename = $file;
					$file = TWITTER_GALLERY_DATA_FOLDER .'/' . $file;
					if( is_file( $file ) ){
						 //<---- here it is, just a seperate key in the array to store filetimes
						$file_arr_times[$t++] = filemtime($file);
						$file_arr_names[$f++] = $filename;
					};
				};
			};
		
			closedir($handle);
			
			if ( count( $file_arr_times ) == 0 ){
				return $filenames;
			}
			
			arsort( $file_arr_times );
			arsort( $file_arr_names );
			
			$t = 0;
			foreach ( $file_arr_times as $key => $ftime ){
				$filenames[$t++] = $file_arr_names[$key];
				if ( $count > 0 && $t > $count ){
					return $filenames;
				}
			}
		}
		return $filenames;
	}
	
}
?>
