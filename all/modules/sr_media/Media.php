<?php

class Media {
    
  public $mid;
  public $isbn;
  public $title;
  public $subtitle;
  public $author;
  public $publisher;
  public $pubdate;
  public $oclc;
  public $mediatype;          
  public $logtype;            // 0 = via worldcat, 1 = via user input
  
  CONST TYPE_BOOK = 0;
  CONST TYPE_VIDEO = 1;
  CONST TYPE_MUSIC = 2;
  CONST TYPE_GAME = 3;
  CONST MEDIALOG_PAGESIZE = 25;
  CONST ADMINMEDIA_PAGESIZE = 200;
  
  public function __construct() {
    // constructor
    $this->mid = 0;
    $this->mediatype = Media::TYPE_BOOK; // default to book media
    $this->logtype = 0; // default to worldcat-entry
  }

  // **************************************
  // accessor methods
  // **************************************
  
  public function setTitle($_title) {
    $str = trim((string)$_title);
    $str = Media::trimEndChar($str, "/");
    $str = Media::trimEndChar($str, ",");
    $str = Media::trimEndChar($str, ".");
    $str = Media::trimEndChar($str, ":");
    $this->title = $str;
  }

  public function setSubTitle($_subtitle) {
    $str = trim((string)$_subtitle);
    $str = Media::trimEndChar($str, "/");
    $str = Media::trimEndChar($str, ",");
    $str = Media::trimEndChar($str, ".");
    $this->subtitle = $str;
  }
  
  public function setAuthor($_author) {
    $str = trim((string)$_author);
    $str = Media::trimEndChar($str, ",");
    $str = Media::trimEndChar($str, ".");
    $this->author = $str;
  }
  
  public function setPublisher($_publisher) {
    $str = trim((string)$_publisher);
    $str = Media::trimEndChar($str, ",");
    $this->publisher = $str;
  }
  
  public function setOCLC($_oclc) {
    $str = trim((string)$_oclc);
    $this->oclc = $str;
  }
  
  public function setISBN($_isbn) {
    $str = trim((string)$_isbn);
    $this->isbn = $str;
  }
  
  public function setPubDate($_pubdate) {
    // TODO: convert to date type?
    $str = trim((string)$_pubdate);
    $str = Media::trimEndChar($str, ".");
    $this->pubdate = $str;
  }
  
  public function setMediaType($_mediatype) {
    $str = trim((string)$_mediatype);
    $str = Media::trimEndChar($str, "/");
    $str = Media::trimEndChar($str, ",");
    $str = Media::trimEndChar($str, ".");
    $str = Media::trimEndChar($str, ":");
    $str = str_replace(' ', '', strtolower($str)); // lower case, remove spaces
    switch ($str) {
      case '[videorecording]':
        $this->mediatype = Media::TYPE_VIDEO;
        break;
      
      case '[soundrecording]':
        $this->mediatype = Media::TYPE_MUSIC;
        break;
        
      case '[electronicresource]':
        $this->mediatype = Media::TYPE_GAME;
        break;
        
      default:
        $this->mediatype = Media::TYPE_BOOK;   
    }
  }
  
  public function makeMediaTypeImagePath() {
    return Media::makeMediaTypeImageURL($this->mediatype);
  }
  
  public function saveMedia() {
    
    $mid = 0;
  	$ret = false;
  	
    // check exists
    if ($this->logtype == 0) {
      $mid = db_result(db_query("SELECT mid FROM {sr_media} WHERE oclc = '%s'", $this->oclc));
    }
    else {
      $mid = db_result(db_query("SELECT mid FROM {sr_media} WHERE title = '%s'", $this->title));  
  	}
  	    
    if($mid == false || $mid == 0) {
      // media not found, save new
      db_query("INSERT INTO {sr_media} (isbn, title, subtitle, author, publisher, pubdate, oclc, mediatype, logtype)
        VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
        $this->isbn, $this->title, $this->subtitle, $this->author, $this->publisher, $this->pubdate, $this->oclc, 
        $this->mediatype, $this->logtype);
      
	    if ($this->logtype == 0) {
	      $mid = db_result(db_query("SELECT mid FROM {sr_media} WHERE oclc = '%s'", $this->oclc));
	    }
	    else {
	      $mid = db_result(db_query("SELECT mid FROM {sr_media} WHERE title = '%s'", $this->title));  
	    }
      $this->mid = $mid;
      
      $ret = true;
      
    }
    else {
    	$this->mid = $mid;
    }
    
    $this->saveThumbnail();
    
    return $ret;
  }
  
  // **************************************
  // static methods
  // **************************************
  
  public static function trimEndChar($_str, $_char) {
    $str = trim($_str);
    if (strlen($str) > 0 && substr_compare($str, $_char, -1, 1) == 0)
    {
      $str = trim(substr($str, 0, -1));
    }
    return $str;
  }
  
  public static function toggleUserMedia($add, $_uid, $_mid) {
      if ($add == 0) {
        @db_query("DELETE FROM {sr_user_media} WHERE uid = %d AND mid = %d", $_uid, $_mid);
      } else {
      	// unique keys will prevent doubles
        @db_query("INSERT INTO {sr_user_media} (uid, mid, timestamp) VALUES (%d, %d, %d)", $_uid, $_mid, time());
      }

    return db_affected_rows();
  }
  
  public static function isInUserMedia($_uid, $_mid) {
     $count = db_result(db_query("select count(*) from {sr_user_media} where uid=%d and mid=%d", $_uid, $_mid));
	  if (is_null($count) || $count == 0) {
		return false;
	  }
	  else {
			return true;
	  }
  }
  
  public static function existsUserMedia($_uid, $_mid) {
		$count = db_result(db_query("SELECT count(*) FROM {sr_user_media} WHERE uid = %d AND mid = %d", $_uid, $_mid));
  	if (is_null($count) || $count == 0) {
      return false;
    }
    else {
			return true;
    }
  }
  
	public static function loadMediaLoggers($_mid) {
		
		$array = array();
		
		// only load summer readers ... rid == 4
		$results = db_query("SELECT u.name, u.uid
  		FROM {users} u 
  		INNER JOIN {sr_user_media} um ON u.uid = um.uid
  		JOIN {users_roles} ur ON u.uid = ur.uid
  		WHERE u.status = 1
  		AND ur.rid = %d
  		AND um.mid = %d
  		ORDER BY um.timestamp DESC",
  		Util::RID_SUMMER_READER, $_mid);

  	$x = 0;
		while ($fields = db_fetch_array($results)) {
			foreach($fields as $key => $value) {
				switch ($key) {
					case "name":
						$array[$x]['name'] = $value;
						break;
					case "uid":
						$array[$x]['uid'] = $value;
						break;
					default:
		    }
	    }
	    $x++;
		}
	  
    return $array;
	}
	
  public static function loadMedia($_mid) {
		
  	$media = new Media();
  	$results = db_query("SELECT * FROM {sr_media} WHERE mid = %d", $_mid);
  	
		while ($fields = db_fetch_array($results)) {
			$media = new Media();
			Media::buildMedia($fields, $media);
    }
    
    return $media;
  }
  
  public static function loadMedia_byISBN($_isbn) {
  	$mid=0;
	$mid = db_result(db_query("SELECT mid FROM {sr_media} WHERE isbn = '%s' LIMIT 1", $_isbn));

    return $mid;
  }
  
  public static function loadUserMedia($_uid, $page = 1) {
		
		if ($page == 1) {
			$start = 0;
		}
		else {
			$start = (Media::MEDIALOG_PAGESIZE * ($page - 1));
		}
  	
  	$medias = new Collection();
  	$results = db_query("SELECT DISTINCT * 
  	  FROM {sr_media} m
  	  INNER JOIN {sr_user_media} um ON m.mid = um.mid 
  		WHERE um.uid = %d
  		ORDER BY um.timestamp DESC
  		LIMIT %d,%d",
  		$_uid, $start, Media::MEDIALOG_PAGESIZE);
  	
  	$x = 0;
		while ($fields = db_fetch_array($results)) {
 		  
			$media = new Media();
			Media::buildMedia($fields, $media);
			$medias->addObject($x, $media);
      
			$x++;
    }
    
    return $medias;  	
  }
  
    
  public static function loadLibraryUserMedia($_uid, $page = 1) {
		
		if ($page == 1) {
			$start = 0;
		}
		else {
			$start = (Media::MEDIALOG_PAGESIZE * ($page - 1));
		}
  
  	$medias = new Collection();
  	$results = db_query("SELECT DISTINCT * 
  	  FROM {sr_media} m
  	  INNER JOIN {sr_user_library_log} um ON m.mid = um.mid 
  		WHERE um.luid = '%s'
  		ORDER BY um.timestamp DESC", $_uid);
  		//LIMIT %d,%d",
  		//$_uid, $start, Media::MEDIALOG_PAGESIZE);
  	
  	$x = 0;
		while ($fields = db_fetch_array($results)) {
 		  
			$media = new Media();
			Media::buildMedia($fields, $media);
			$medias->addObject($x, $media);
      
			$x++;
    }
    
    return $medias;  	
  }
  
  public static function removeUserLibraryLog($mid, $luid) {
  	
  	 db_query("DELETE FROM {sr_user_library_log} WHERE luid = '%s' AND mid = %d", $luid, $mid);
  }
  
  private static function buildMedia($fields, &$media) {
	
		foreach($fields as $key => $value) {
			switch ($key) {
				case "mid":
					$media->mid = $value;
					break;
				case "isbn":
					$media->isbn = $value;
					break;
				case "title":
					$media->title = $value;
					break;
				case "subtitle":
					$media->subtitle = $value;
					break;
				case "author":
					$media->author = $value;
					break;
				case "publisher":
					$media->publisher = $value;
					break;
				case "pubdate":
					$media->pubdate = $value;
					break;
				case "oclc":
					$media->oclc = $value;
					break;
				case "mediatype":
					$media->mediatype = $value;
					break;
				case "logtype":
					$media->logtype = $value;
					break;
				default:
			}
	  }
	  	
	}
	
  public function saveThumbnail() {
  	
  	// TODO: local thumbnails may not be worth the time
  	return false;
  	
  	// must have isbn to save
  	if (strlen($this->isbn) < 1) {
  		return false;
  	}
  	
  	$url = 'http://contentcafe2.btol.com/ContentCafe/Jacket.aspx?UserID=NYPL49807&amp;Password=CC68707&amp;Return=1&amp;Type=S&amp;erroroverride=1&amp;Value='. $isbn;

  	$ctx = stream_context_create(array('http' => array('timeout' => 2))); // in seconds
    $thumb = @file_get_contents($url, 0, $ctx);
    if (!$thumb || strlen($thumb) < 2053) {
    	return false;
    }
  	@file_put_contents(drupal_get_path("module", "sr_media") ."files/media/covers/". $this->isbn ."-S.jpg", $thumb); 
  	return true;
  }
  
  public static function makeMediaImageURL($isbn, $size = 'S', $local = false) {
  	
  	// TODO: local thumbnails may not be worth the time to develop

    //	if ($local && strtoupper($size) == 'S') {
		// currently only small covers are saved locally
    //  		$ret = "http://" . $_SERVER['HTTP_HOST'] . base_path() . file_directory_path() . "/media/covers/" . $isbn . "-S.jpg";
    //  	}
    //  	else {

  	// sizes are S or M or L
	  if (strlen($isbn) > 0) {
	  	$url = 'http://contentcafe2.btol.com/ContentCafe/Jacket.aspx?UserID=NYPL49807&amp;Password=CC68707&Return=1&amp;Type=' . 
    	$size . '&amp;erroroverride=1&amp;Value='. $isbn;
	  }
	  else {
  		$url = 'http://'. $_SERVER['HTTP_HOST'] . base_path() . file_directory_path() .'/media/nocover-'. $size .'.gif';
  	}
	    	
    // TODO: this check might not be worth doing - often slow servers
    // it forces async processing of images on page ... slooooow
    $ret = $url;
    /*
		$ctx = stream_context_create(array('http' => array('timeout' => 2))); // in seconds
  	$img = @file_get_contents($url, 0, $ctx);
  	if ($img && strlen($img) > 2053) {
    	$ret = $url;
  	} else {
  		$ret = 'http://'. $_SERVER['HTTP_HOST'] . base_path() . file_directory_path() .'/media/nocover-'. $size .'.gif';
  	}
  	*/
    			    	
    // }
  	
  	return $ret;
	}
	
	public static function makeMediaURL($media, $add = false) {
		$ret = 'http://'. $_SERVER['HTTP_HOST'] . base_path() ."media";
		if (!is_null($media)) {
			// pass media object
    	$ret .= "?view=". base64_encode(serialize($media));
    	if ($add) $ret .= "&amp;add=1";
		}

		return $ret;
	}
	
	public static function makeMediaURLbyID($mid) {
		$ret = 'http://'. $_SERVER['HTTP_HOST'] . base_path() ."media";
   	$ret .= "?mid=". $mid;

		return $ret;
	}

  public static function makeLibrarySystemURL($system, $media) {
  	$url = "http://";
  	switch (strtolower($system)) {
  		case "bpl":
  			$url .= "iii.brooklynpubliclibrary.org/search~/a?searchtype=X&amp;searcharg=";
  			$url .= urlencode($media->title);
  			$url .= "&amp;searchscope=63&amp;SORT=D";
  			break;
  		case "qpl":
  			$url .= "aqua.queenslibrary.org/?q=";
  			$url .= urlencode($media->title);
  			break;
   		case "nypl": default:
   			$url .= "catalog.nypl.org/iii/encore/search/C|S";
   			$url .= urlencode($media->title);
   			$url .= "?lang=eng";
   			
  	}

  	return $url;
  }
  
  public static function makeMediaTypeImageURL($mediatype) {
    
    $ret = 'http://'. $_SERVER['HTTP_HOST'] . base_path() . file_directory_path() ."/media/media_";
    switch ($mediatype) {
      case Media::TYPE_BOOK:
        $ret .= 'book.png';
        break;
      
      case Media::TYPE_VIDEO:
        $ret .= 'dvd.png';
        break;
        
      case Media::TYPE_MUSIC:
        $ret .= 'music.png';
        break;
      
      case Media::TYPE_GAME:
        $ret .= 'computerfile.png';
        break;
        
      default:
        $ret .= 'book.png';
        
    }
    
    return $ret;
  }
  
} // end Media class