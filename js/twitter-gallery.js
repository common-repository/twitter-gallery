/**
 * jQuery wrapper function
 */
( function( $ ) {
    /**
     * Creates a namespace specified by the arguments
     * of the function
     */
    $.createNamespace = function() {
        var a=arguments, o=null, i, j, d;
        for (i=0; i<a.length; i=i+1) {
            d=a[i].split(".");
            o=window;
            for (j=0; j<d.length; j=j+1) {
                o[d[j]]=o[d[j]] || {};
                o=o[d[j]];
            }
        }
        return o;
    };
})( jQuery );

var GALLERY = jQuery.createNamespace( 'com.scibuff.gallery' );

( function( $ ) {
	
	GALLERY.PLUGIN_BASE_URL = '';
	
	/**
	 * Creates a regular expression that search twitter items for picture
	 * urls, such as http://twitpic.com/[id] or http://flic.kr/p/[id]. The
	 * RegExp will return array of results such that:
	 * 	- the full url is at index 0
	 *  - the platform is at index 1
	 *  - the picture id is at index 2
	 */	
	GALLERY.createSearchRegExp = function(){
		var exp = 'http:\/\/[www.]*(';
		for (var i = 0; i < GALLERY.PLATFORMS.length; i++) {
			// replace '/' with '\/' for RegExp
			var platform = GALLERY.PLATFORMS[i].replace(/\//g, '\\/');
			//exp += 'http:\/\/' + platform;
			exp += platform;
			if (i < GALLERY.PLATFORMS.length - 1) {
				exp += '|';
			}
		}
		exp += ')\/([0-9a-z]{3,16})';
		return new RegExp( exp, 'gi');
	}
	
	GALLERY.RT_REG_EXP = new RegExp( '(RT[:]*) @([a-z0-9_]+)*', 'gi' );	
	GALLERY.GEODATA_REG_EXP = new RegExp( '([a-z0-9]+[ ]*[a-z0-9]*),([a-z]+),?([0-9]+/*[0-9]*)?', 'i' );
	
	GALLERY.Options = function(){
		var tmp = {};
		var pub = {};
		pub.lightbox = {
			download_link: true,
			show_linkback: false,
			show_helper_text: false			
		};
		pub.dataManager = {
			COUNT_PER_PAGE: 10
		}
		
		pub.LOADING_CONTAINER_ID = 'twitter-gallery-loading';
		pub.IMAGES_CONTAINER_ID = 'twitter-gallery-images';
		pub.LIGHTBOX_CLASS = 'twitter-gallery-lightbox';
		pub.GALLERY_ITEM_CLASS = 'twitter-gallery-item';
		pub.GALLERY_ARCHIVE_ITEM_CLASS = 'twitter-gallery-archive-item';
		pub.ARCHIVER_URL = 'archiver.php'; // this should be upaded to absolute url
		
		return pub;
	}();
	
	GALLERY.DataManager = function(){
		var tmp = {};
		tmp.query = null;
		tmp.photos = {};
		tmp.refresh = false;
		
		/**
		 * Adds all photos present in the <i>item</i>'s content
		 * @param {Object} i	The item index in the result data array
		 * @param {Object} item	The item whose photos will be added
		 */
		tmp.getItemPhotos = function( i, item ){
			
			var images_html = ''; 
			
			// match photos from all supported platforms
			while( ( match = GALLERY.REG_EXP.exec( item.text ) ) != null ){
				// match[0] = url, match[1] = platform, match[2] = id
				
				if ( match[0] && match[1] && match[2] ) {

					// prevent duplicates with www and no www
					var url = match[0].replace(/www./i, "");

					/* this photo is already displayed, do nothing */
					if ( tmp.photos[ url ] ) { }
					else {
						
						// get the photo html code
						var html = tmp.getPhotoHTML( url, match[1], match[2], item );
						if ( html != null ) {
							// append the image
							// store image
							tmp.photos[ url ] = html;
							//images_html += html;
							
							images_html += html;
							
							// save the item
							var data = {};
							data.url = url;
							data.data = $.base64Encode(html);
							data.user = item.from_user;
							
							// parse meteor data
							var geodata = GALLERY.GEODATA_REG_EXP.exec( item.text );
							if ( geodata != null && geodata[1] && geodata[2] ){
								data.zipcode = geodata[1];
								data.country = geodata[2];
								if ( geodata[3] ){
									data.count = geodata[3];
								}
								//
							}
							
							// get original user from RT'
							
							var last_rt = null;
							
							while( ( match = GALLERY.RT_REG_EXP.exec( item.text ) ) != null ){
								last_rt = match[2];
							}
							if ( last_rt ){
								data.user = last_rt;
							}

							if ( tmp.refresh ){ data.digg = 'true'; }
							$.post( GALLERY.PLUGIN_BASE_URL + '/' + GALLERY.Options.ARCHIVER_URL, data );
							
						}
					}
				}
			}			
			
			return images_html;
		}
		
		/**
		 * Process the data returns from the data request
		 * @param {Object} data
		 */
		tmp.processData = function( data ){

			tmp.query = data.refresh_url;
			$.post( GALLERY.PLUGIN_BASE_URL + '/' + GALLERY.Options.ARCHIVER_URL, {
				query: $.base64Encode( data.refresh_url )
			});
			
			var html = '';
			$.each( data.results, function( i, item ) {
				var item = tmp.getItemPhotos( i, item );
				html += item;
			});	
			$( '#' + GALLERY.Options.IMAGES_CONTAINER_ID ).prepend( html );				
			$( '.' + GALLERY.Options.LIGHTBOX_CLASS ).lightbox();
			tmp.showUpdate( false );
		}		
		
		/**
		 * Creates and returns the html code for the photo
		 * @param string url		the photo url
		 * @param string platform	the photo platform
		 * @param string id			the photo id
		 * @param {Object} item		the photo item
		 */
		tmp.getPhotoHTML = function( url, platform, id, item ){
			
			var html = null;
			
			switch( platform ){
				case 'twitpic.com' : {
					html = new GALLERY.photos.TwitpicPhoto( url, id, item ).getHTML( false );
					break;
				}
				case 'yfrog.com' : {
					html = new GALLERY.photos.YfrogPhoto( url, id, item ).getHTML( false );
					break;
				}
				case 'img.ly' : {
					html = new GALLERY.photos.ImglyPhoto( url, id, item ).getHTML( false );
					break;
				}
				case 'twitgoo.com' : {
					html = new GALLERY.photos.TwitgooPhoto( url, id, item ).getHTML( false );
					break;
				}
				case 'flic.kr/p' : {
					html = new GALLERY.photos.FlickrPhoto( url, id, item ).getHTML( false );
					break;
				}
				case 'ow.ly/i' : {
					html = new GALLERY.photos.OwlyPhoto( url, id, item ).getHTML( false );
					break;
				}				
				default : {
					break;
				}				
			}			
			
			return html;			
			
		}
		
		/**
		 * Returns the current query
		 */
		tmp.getQuery = function(){
			if ( tmp.query == null || tmp.query == '' ){
				// add the hash tag
				var s = '-' + GALLERY.USER + ' ' + GALLERY.HASH_TAG + ' ';
				var length = GALLERY.PLATFORMS.length;
				// add the platforms
				for ( var i = 0; i < length; i++ ){
					var token = GALLERY.PLATFORMS[i];
					if ( token == 'ow.ly/i' ){
						token = 'http://ow.ly/i';
					}
					s += token;
					if ( i < length - 1 ){
						s += ' OR ';
					}
				}
				tmp.query = '?q=' + escape( s );
			}
			return tmp.query;
		}		
		
		/**
		 * Displays the loading graphics
		 * @param {Object} value
		 */
		tmp.showUpdate = function( value ){
			if ( value ){
				$('#' + GALLERY.Options.LOADING_CONTAINER_ID ).show().slideDown('slow');
			}
			else {
				$('#' + GALLERY.Options.LOADING_CONTAINER_ID ).slideUp('slow').hide();
			}
		}		
		
		var pub = {};
		
		/**
		 * Sets the query value
		 * @param String query	the new query
		 */
		pub.setQuery = function( query ){
			tmp.query = query;
		}
		
		/**
		 * Requests data
		 */
		pub.requestData = function( refresh ){
			
			tmp.showUpdate( true );
			tmp.refresh = refresh;

		    var url = 'http://search.twitter.com/search.json';
			url += tmp.getQuery();
			url += '&page=1';
			url += '&rpp='+GALLERY.Options.dataManager.COUNT_PER_PAGE;
			url += '&callback=?';
			$.getJSON( url, function(data) {
				tmp.processData( data );
			});		
		}
		
		return pub;
		
	}();	
	
	/**
	 * GALLERY pictures
	 */
	GALLERY.photos = function(){
	
		var tmp = {};
		var pub = {};
		
		/**
		 * Abstract class AbstractPhoto
		 * @param string url	the photo url
		 * @param string id		the photo id
		 * @param {Object} item	the photo item
		 */
		tmp.AbstractPhoto = function( url, id, item ){
			var pub = {}
			pub.url = url;
			pub.id = id;
			pub.item = item;
			return pub;
		}		
		
		/**
		 * Creates a new FlickrPhoto object
		 * @param string url	the photo url
		 * @param string id		the photo id
		 * @param {Object} item	the photo item
		 */
		pub.FlickrPhoto = function( url, id, item ){
			
			var self = this;
			var AbstractPhoto = new tmp.AbstractPhoto( url, id, item );
			var tmp_self = {};
			tmp_self.base58_decode = function( id ){
				var alphabet = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ' ;
				var num = id.length ;
				var decoded = 0 ;
				var multi = 1 ;
				for ( var i = (num-1) ; i >= 0 ; i-- ){
					decoded = decoded + multi * alphabet.indexOf( id[i] ) ;
					multi = multi * alphabet.length ;
				}
				return decoded;
			}
			
			$.extend(
				self, {
					/**
					 * Creates and return the html code to display this photo
					 * @param Boolean archive	A value specifying whether or not
					 * 							the html code is for the archive
					 */
					getHTML: function( archive ){

						var css = ( archive == true ) ? ' ' + GALLERY.Options.GALLERY_ARCHIVE_ITEM_CLASS : '';						
						var itemText = item.text.replace(/['"]/g,'');
						var titleText = itemText;
						var flickr_id = tmp_self.base58_decode( id );
						
						var html = '<div id="flickr-'+id+'" class="' + GALLERY.Options.GALLERY_ITEM_CLASS + css + '">';
						html += '<a href="' + GALLERY.PLUGIN_BASE_URL + 'proxy/flickr.php?size=image&id='+id+'" class="' + GALLERY.Options.LIGHTBOX_CLASS + '" rel="http://flickr.com/photo.gne?id='+flickr_id+'" title="[Download]: '+item.from_user+' said: '+titleText+'">';
						html += '<img width="75" height="75" src="' + GALLERY.PLUGIN_BASE_URL + 'proxy/flickr.php?size=thumb&id='+id+'" title="'+itemText+'" alt="'+itemText+'" />';
						html += '</a><p style="text-align: center;"><a href="http://flickr.com/photo.gne?id='+flickr_id+'" target="_blank">Flickr page</a></p></div>';
						
						return html;
					},
					
				}, AbstractPhoto
			);
			
		}		
		
		/**
		 * Creates a new ImglyPhoto object
		 * @param string url	the photo url
		 * @param string id		the photo id
		 * @param {Object} item	the photo item
		 */
		pub.ImglyPhoto = function( url, id, item ){
			
			var self = this;
			var AbstractPhoto = new tmp.AbstractPhoto( url, id, item );
			
			$.extend(
				self, {
					/**
					 * Creates and return the html code to display this photo
					 * @param Boolean archive	A value specifying whether or not
					 * 							the html code is for the archive
					 */
					getHTML: function( archive ){

						var css = ( archive == true ) ? ' ' + GALLERY.Options.GALLERY_ARCHIVE_ITEM_CLASS : '';						
						var itemText = item.text.replace(/['"]/g,'');
						var titleText = itemText;
						
						var html = '<div id="imgly-'+id+'" class="' + GALLERY.Options.GALLERY_ITEM_CLASS + css + '">';
						html += '<a href="' + GALLERY.PLUGIN_BASE_URL + 'proxy/imgly.php?&id='+id+'" class="' + GALLERY.Options.LIGHTBOX_CLASS + '" rel="http://img.ly/'+id+'" title="[Download]: '+item.from_user+' said: '+titleText+'">';
						html += '<img width="75" height="75" src="http://img.ly/show/thumb/'+id+'" title="'+itemText+'" alt="'+itemText+'" />';
						html += '</a><p style="text-align: center;"><a href="'+url+'" target="_blank">Img.ly page</a></p></div>';
						
						return html;
					}
				}, AbstractPhoto
			);
			
		}		
		
		/**
		 * Creates a new OwlyPhoto object
		 * @param string url	the photo url
		 * @param string id		the photo id
		 * @param {Object} item	the photo item
		 */
		pub.OwlyPhoto = function( url, id, item ){
			
			var self = this;
			var AbstractPhoto = new tmp.AbstractPhoto( url, id, item );
			
			$.extend(
				self, {
					/**
					 * Creates and return the html code to display this photo
					 * @param Boolean archive	A value specifying whether or not
					 * 				the html code is for the archive
					 */
					getHTML: function( archive ){

						var css = ( archive == true ) ? ' ' + GALLERY.Options.GALLERY_ARCHIVE_ITEM_CLASS : '';
						var itemText = item.text.replace(/['"]/g,'');
						var titleText = itemText;
						
						var html = '<div id="twitpic-'+id+'" class="' + GALLERY.Options.GALLERY_ITEM_CLASS + css + '">';
						html += '<a href="http://static.ow.ly/photos/original/'+id+'.jpg" class="' + GALLERY.Options.LIGHTBOX_CLASS + '" rel="http://ow.ly/i/'+id+'" title="[Download]: '+item.from_user+' said: '+titleText+'">';
						html += '<img width="75" height="75" src="http://static.ow.ly/photos/thumb/'+id+'.jpg" title="'+itemText+'" alt="'+itemText+'" />';
						html += '</a><p style="text-align: center;"><a href="'+url+'" target="_blank">Ow.ly page</a></p></div>';
						
						return html;
					}
				}, AbstractPhoto
			);
			
		}

		/**
		 * Creates a new TwitpicPhoto object
		 * @param string url	the photo url
		 * @param string id		the photo id
		 * @param {Object} item	the photo item
		 */
		pub.TwitpicPhoto = function( url, id, item ){
			
			var self = this;
			var AbstractPhoto = new tmp.AbstractPhoto( url, id, item );
			
			$.extend(
				self, {
					/**
					 * Creates and return the html code to display this photo
					 * @param Boolean archive	A value specifying whether or not
					 * 							the html code is for the archive
					 */
					getHTML: function( archive ){

						var css = ( archive == true ) ? ' ' + GALLERY.Options.GALLERY_ARCHIVE_ITEM_CLASS : '';
						var itemText = item.text.replace(/['"]/g,'');
						var titleText = itemText;
						
						var html = '<div id="twitpic-'+id+'" class="' + GALLERY.Options.GALLERY_ITEM_CLASS + css + '">';
						html += '<a href="http://twitpic.com/show/full/'+id+'" class="' + GALLERY.Options.LIGHTBOX_CLASS + '" rel="http://twitpic.com/'+id+'" title="[Download]: '+item.from_user+' said: '+titleText+'">';
						html += '<img width="75" height="75" src="http://twitpic.com/show/thumb/'+id+'" title="'+itemText+'" alt="'+itemText+'" />';
						html += '</a><p style="text-align: center;"><a href="'+url+'" target="_blank">TwitPic page</a></p></div>';
						
						return html;
					}
				}, AbstractPhoto
			);
			
		}
		
		/**
		 * Creates a new TwitgooPhoto object
		 * @param string url	the photo url
		 * @param string id		the photo id
		 * @param {Object} item	the photo item
		 */
		pub.TwitgooPhoto = function( url, id, item ){
			
			var self = this;
			var AbstractPhoto = new tmp.AbstractPhoto( url, id, item );
			
			$.extend(
				self, {
					/**
					 * Creates and return the html code to display this photo
					 * @param Boolean archive	A value specifying whether or not
					 * 							the html code is for the archive
					 */
					getHTML: function( archive ){

						var css = ( archive == true ) ? ' ' + GALLERY.Options.GALLERY_ARCHIVE_ITEM_CLASS : '';
						var itemText = item.text.replace(/['"]/g,'');
						var titleText = itemText;
						
						var html = '<div id="twitgoo-'+id+'" class="' + GALLERY.Options.GALLERY_ITEM_CLASS + css + '">';
						html += '<a href="http://twitgoo.com/show/img/'+id+'" class="' + GALLERY.Options.LIGHTBOX_CLASS + '" rel="http://twitgoo.com/'+id+'" title="[Download]: '+item.from_user+' said: '+titleText+'">';
						html += '<img width="75" height="75" src="http://twitgoo.com/show/thumb/'+id+'" title="'+itemText+'" alt="'+itemText+'" />';
						html += '</a><p style="text-align: center;"><a href="'+url+'" target="_blank">Twitgoo page</a></p></div>';
						
						return html;
					}
				}, AbstractPhoto
			);
			
		}		
		
		/**
		 * Creates a new YfrogPhoto object
		 * @param string url	the photo url
		 * @param string id		the photo id
		 * @param {Object} item	the photo item
		 */
		pub.YfrogPhoto = function( url, id, item ){
			
			var self = this;
			var AbstractPhoto = new tmp.AbstractPhoto( url, id, item );
			
			$.extend(
				self, {
					/**
					 * Creates and return the html code to display this photo
					 * @param Boolean archive	A value specifying whether or not
					 * 							the html code is for the archive
					 */
					getHTML: function( archive ){

						var css = ( archive == true ) ? ' ' + GALLERY.Options.GALLERY_ARCHIVE_ITEM_CLASS : '';
						var itemText = item.text.replace(/['"]/g,'');
						var titleText = itemText;
						
						var html = '<div id="yfrog-'+id+'" class="' + GALLERY.Options.GALLERY_ITEM_CLASS + css + '">';
						html += '<a href="http://yfrog.com/'+id+':iphone" class="' + GALLERY.Options.LIGHTBOX_CLASS + '" rel="http://yfrog.com/'+id+':iphone" title="[Download]: '+item.from_user+' said: '+titleText+'">';
						html += '<img width="75" height="75" src="http://yfrog.com/'+id+'.th.jpg" title="'+itemText+'" alt="'+itemText+'" />';
						html += '</a><p style="text-align: center;"><a href="'+url+'" target="_blank">yFrog page</a></p></div>';
						
						return html;
					}
				}, AbstractPhoto
			);
			
		}		
		
		return pub;
	}();	
	
	/**
	 * The main object controlling the initialization of all objects 
	 */
	GALLERY.Main = function(){
		
		// private fields
		var tmp = {};
		// public fields
		var pub = {};
		
		/**
		 * Initializes GALLERY Application 
		 */
		pub.initialize = function(){
			if ($.Lightbox) {
				$.Lightbox.construct(GALLERY.Options.lightbox);
			}
		}
		
		return pub;
	}();		
	
})( jQuery );