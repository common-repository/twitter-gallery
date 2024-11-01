<?php

define( 'ALPHABET', '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ' );

$id = $_REQUEST['id'];
$size = $_REQUEST['size'];
$url = 'http://flickr.com/photo.gne?id=' . base_58_decode( $id );

$image_url = get_image_url( file_get_contents( $url ), $size );
if ( $image_url != FALSE ){
	header( "Content-type: image/jpg" );
	header( "Location: ".$image_url, TRUE, 302 );
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

function get_image_url( $html, $size ){
	
	if ( $html === FALSE ){ return FALSE; }	
	
	// get html head;
	preg_match( '/<head.*>(.*)<\/head>/smU', $html, $matches );
	$head = $matches[1];	
	
	$head = preg_replace( "'<style[^>]*>.*</style>'siU", '', $head );  
	// strip css
	$head = preg_replace( "'<script[^>]*>.*</script>'siU", '', $head );	
	
	$lines = explode( "\n", $head );
	
	foreach ( $lines as $key => $line ){
	
        if ( strpos(' '.$line, '<link' ) ) {
			preg_match_all(
				"/<link[^>]+(rel)=\"image_src\"[^>]" . "+href=\"([^\"]*)\"[^>]*>/i",
				$line, 
				$links_data[],
				PREG_PATTERN_ORDER
			);
		}
	}		
	
	foreach( $links_data as $link_data ){
		if ( isset( $link_data[2][0] ) ){
			$src = $link_data[2][0];
		}
	}
	
	switch( $size ){
		case 'thumb' : {
			return $src;
		}
		case 'image' : {
			return substr_replace( $src, '', -6, 2);
		}
		default : {
			return FALSE;
		}
	}
	return FALSE;
}

/**
 * Decodes Flic.kr base 58 encoded id
 * @return decoded photo id
 * 
 * @param object $id	encoded photo id
 */
function base_58_decode( $id ) {
	$decoded = 0;
	$multi = 1;
	while (strlen($id) > 0) {
		$digit = $id[strlen($id)-1];
		$decoded += $multi * strpos(ALPHABET, $digit);
		$multi = $multi * strlen(ALPHABET);
		$id = substr($id, 0, -1);
	}
	
	return $decoded;
}

?>