<?php

$id = $_REQUEST['id'];
$url = 'http://img.ly/' . $id;

//echo( file_get_contents($url) );

$image_url = get_image_url( file_get_contents($url) );
if ( $image_url != FALSE ){
	header( "Content-type: image/jpg" );
	header( "Location: http://img.ly".$image_url, TRUE, 302 );
}
else {
	$img = imagecreate( 75, 75 );
	$background = imagecolorallocate( $img, 255, 255, 255 );
	$text_color = imagecolorallocate( $img, 255, 0, 0 );
	imagestring( $img, 12, 5, 5, "x", $text_color );

	header( "Content-type: image/png" );
	imagepng( $img );
	imagecolordeallocate( $img, $background );
	imagedestroy( $img );
	exit(0);
}

function get_image_url( $html ){
	
	// get html head;
	$lines = explode( "\n", $html );
	
	foreach ( $lines as $key => $line ){

		if ( strpos(' '.$line, 'id="the-image"' ) ) {
			preg_match_all(
				"/<img[^>]" . "+src=\"([^\"]*)\"[^>]*>/i",
				$line, 
				$links_data[],
				PREG_PATTERN_ORDER
			);
		}
	}		

	if ( isset( $links_data[0][1][0] ) ){
		$path = substr( $links_data[0][1][0], 0, strrpos( $links_data[0][1][0], '/' ) );
		return 'http://img.ly' . $path . '/original_image.jpg';
	}

	return FALSE;
}

?>