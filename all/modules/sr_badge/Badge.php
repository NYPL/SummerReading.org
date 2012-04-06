<?php

class Badge {
  
	public $bid;
	public $nid;
  public $badgetype;
  public $title;
  public $desc;
  public $earn;
  public $code;
  public $code1;
  public $quantity;
  public $status;
  
  CONST TYPE_CODE = 					1;
  CONST TYPE_LOGIN = 					2;
  CONST TYPE_ADD2LOG = 				3;
  CONST TYPE_REVIEW = 				4;
  CONST TYPE_LIKE = 					5;
  CONST TYPE_WASLIKED = 			6;
  CONST TYPE_CODE_ADD2LOG= 			7;
  CONST WINNER_PAGESIZE =			100; // make sure divisible by 10 (badge page, number of columns)
  
  public function __construct() {
    // constructor
    $this->quantity = 0;
    $this->badgetype = 0;
    $this->status = 1;
    $this->code = "";
	$this->code1 = "";
  }
  
  public function saveBadge() {
  	
  	// insert the node data
    module_load_include('inc', 'node', 'node.pages');
    
    $node = new StdClass();
    $node->type = "badge";
    node_object_prepare($node); // not sure if this is needed or not
    $node->status = $this->status;
    $node->comment = 0;
    $node->promote = 0;
    $node->title = $this->title;
    $node->body = $this->desc;
    node_save($node);

    if (!$node->nid) {
        return false;
    }
    $this->nid = $node->nid;
    
    db_query("INSERT INTO {sr_badge} (nid, badgetype, earn, code, code1, quantity)
  	  VALUES (%d, %d, '%s', '%s','%s', %d)",
    	$this->nid, $this->badgetype, $this->earn, $this->code, $this->code1, $this->quantity);

	  $bid = db_result(db_query("SELECT bid FROM {sr_badge} WHERE nid = %d", $this->nid));  
	  $this->bid = $bid;
    
    return true;
  }
  
   public function saveBadgeLog($bid, $mid) {
    db_query("INSERT INTO {sr_badge_log} (bid, mid) VALUES (%d, %d) ON DUPLICATE KEY UPDATE mid= %d",$bid, $mid, $mid);
    return true;
  }
  
  // **************************************
  // static methods
  // **************************************

  public static function cast(Badge $object) {
        return $object;
  }
  
  public static function toggleUserBadge($add, $_uid, $_bid) {
      if ($add == 0 || $add == false) {
        @db_query("DELETE FROM {sr_user_badge} WHERE uid = %d AND bid = %d", $_uid, $_bid);
      }
      else {
      	// unique keys will prevent doubles
        @db_query("INSERT INTO {sr_user_badge} (uid, bid, timestamp) VALUES (%d, %d, %d)", $_uid, $_bid, time());
      }
      return db_affected_rows();
  }
  
  public static function existsUserBadge($_uid, $_bid) {
		$ts = db_result(db_query("SELECT timestamp FROM {sr_user_badge} WHERE uid = %d AND bid = %d", $_uid, $_bid));
  	if (is_null($ts) || $ts == 0) {
        return false;
    }
    else {
				return $ts;
    }
  }
  
	public static function loadBadgeWinners($_bid, $page = 1) {
		
		$array = array();
		
		if ($page == 1) {
			$start = 0;
		}
		else {
			$start = (Badge::WINNER_PAGESIZE * ($page - 1));
		}
		// only load summer readers
		$results = db_query("SELECT u.name, u.uid
  		FROM {users} u 
  		INNER JOIN {sr_user_badge} ub ON u.uid = ub.uid
  		JOIN {users_roles} ur ON u.uid = ur.uid
  		WHERE u.status = %d
  		AND ur.rid = %d
  		AND ub.bid = %d
  		ORDER BY ub.timestamp DESC
  		LIMIT %d,%d",
  		1, Util::RID_SUMMER_READER, $_bid, $start, Badge::WINNER_PAGESIZE);

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
	
	public static function loadBadgeWinnersCount($_bid) {
		
		// only load summer readers
		$results = db_result(db_query("SELECT count(u.uid)
  		FROM {users} u 
  		INNER JOIN {sr_user_badge} ub ON u.uid = ub.uid
  		JOIN {users_roles} ur ON u.uid = ur.uid
  		WHERE u.status = %d
  		AND ur.rid = %d
  		AND ub.bid = %d",
  		1, Util::RID_SUMMER_READER, $_bid));
    
  	return $results;
	}
	
  public static function loadBadge($_bid) {
	  
		$badge = new Badge();
		
		$results = db_query("SELECT b.bid, b.nid, b.badgetype, b.earn, b.code, b.quantity, n.vid, n.status, nr.title, nr.body
  		FROM {sr_badge} b 
  		INNER JOIN {node} n ON b.nid = n.nid 
  		INNER JOIN {node_revisions} nr ON n.vid = nr.vid
  		WHERE b.bid = %d", 
  		$_bid);
  	
		while ($fields = db_fetch_array($results)) {
			Badge::buildBadge($fields, $badge);
    }
	    
    return $badge;
  }
  
  public static function loadUserBadges($_uid) {
	  
  	$badges = new Collection();
  	  	
  	$results = db_query("SELECT b.bid, b.nid, b.badgetype, b.earn, b.code, b.quantity, n.vid, n.status, nr.title, nr.body
  		FROM {sr_badge} b 
  		INNER JOIN {node} n ON b.nid = n.nid 
  		INNER JOIN {node_revisions} nr ON n.vid = nr.vid
  		INNER JOIN {sr_user_badge} ub ON b.bid = ub.bid
  		WHERE ub.uid = %d", 
  		$_uid);
  	
  	$x = 0;
		while ($fields = db_fetch_array($results)) {
 		  
			$badge = new Badge();
			Badge::buildBadge($fields, $badge);
      $badges->addObject($x, $badge);
      
      $x++;
    }
	  
    return $badges;
  }

  public static function loadAllBadges() {
	  
  	$badges = new Collection();
  	  	
  	$results = db_query("SELECT DISTINCT b.bid, b.nid, b.badgetype, b.earn, b.code,  b.code1, b.quantity, n.vid, n.status, nr.title, nr.body
  		FROM {sr_badge} b 
  		INNER JOIN {node} n ON b.nid = n.nid 
  		INNER JOIN {node_revisions} nr ON n.vid = nr.vid 
  		ORDER BY b.bid");
  	
  	$x = 0;
		while ($fields = db_fetch_array($results)) {
 		  
			$badge = new Badge();
			Badge::buildBadge($fields, $badge);
      $badges->addObject($x, $badge);
      
      $x++;
    }
	  
    return $badges;
  }
  
  public static function loadBadgeList() {
	  
  	$badges = new Collection();
  	  	
  	$results = db_query("SELECT DISTINCT b.bid, b.nid, b.badgetype, n.vid, n.status, nr.title
  		FROM {sr_badge} b 
  		INNER JOIN {node} n ON b.nid = n.nid 
  		INNER JOIN {node_revisions} nr ON n.vid = nr.vid
  		WHERE n.status = %d
  		ORDER BY nr.title", 1);
  	
  	$x = 0;
		while ($fields = db_fetch_array($results)) {
 		  
			$badge = new Badge();
			Badge::buildBadge($fields, $badge);
      $badges->addObject($x, $badge);
      
      $x++;
    }
	  
    return $badges;
  }
	
  private static function loadTypeBadges($_badgetype, $_uid = 0, $awarded = false) {
	  
  	$badges = new Collection();
  	
  	if ($awarded) {
  		// asking to include already awarded badges
			$results = db_query("SELECT b.bid, b.nid, b.badgetype, b.earn, b.code, b.quantity, n.vid, n.status, nr.title, nr.body
	  		FROM {sr_badge} b INNER JOIN {node} n ON b.nid = n.nid INNER JOIN {node_revisions} nr ON n.vid = nr.vid
	  		WHERE n.status = %d AND b.badgetype = %d",
	  		1, $_badgetype);
  	}
  	else {
  		// only badges not yet awarded
			$results = db_query("SELECT b.bid, b.nid, b.badgetype, b.earn, b.code, b.quantity, n.vid, n.status, nr.title, nr.body
	  		FROM {sr_badge} b 
	  		INNER JOIN {node} n ON b.nid = n.nid 
	  		INNER JOIN {node_revisions} nr ON n.vid = nr.vid
	  		WHERE n.status = %d 
	  		AND b.badgetype = %d 
	  		AND %d NOT IN 
	  		(SELECT ub.uid FROM {sr_user_badge} ub WHERE b.bid = ub.bid)",
	  		1, $_badgetype, $_uid);
  	}
  	  	
  	$x = 0;
		while ($fields = db_fetch_array($results)) {
 		  
			$badge = new Badge();
			Badge::buildBadge($fields, $badge);
      $badges->addObject($x, $badge);
      
      $x++;
    }
	  
    return $badges;
  }
  
  private static function buildBadge($fields, &$badge) {
	
		foreach($fields as $key => $value) {
			switch ($key) {
				case "bid":
					$badge->bid = $value;
					break;
				case "nid":
					$badge->nid = $value;
					break;
				case "title":
					$badge->title = $value;
					break;
				case "body":
					$badge->desc = $value;
					break;
				case "badgetype":
					$badge->badgetype = $value;
					break;
				case "earn":
					$badge->earn = $value;
					break;
				case "code":
					$badge->code = $value;
					break;
				case "code1":
					$badge->code1 = $value;
					break;
				case "quantity":
					$badge->quantity = $value;
					break;
				case "status":
					$badge->status = $value;
				default:
			}
	  }
	
	}
	
	public static function makeBadgeImageURL($bid, $size = 'S') {
		return "http://". $_SERVER['HTTP_HOST'] . base_path() . file_directory_path() ."/badge/badge-". strtoupper($size) ."__". $bid .".jpg";
	}
	
	public static function getBadgeLog($bid) {
	
	  $results = db_query("SELECT mid from sr_badge_log where bid=%d",$bid);
  	  $mids=array();
	  
		while ($fields = db_fetch_object($results)) {
 		  $mids[]=$fields->mid;
		}
		return $mids;
	}
	
	public static function processAction($_badgetype, $_uid, $code = "") {
		
		$user = user_load($_uid);
		if (!$user) {
			return false;
		}
		
		$ret = false;
		
		// increment user action tally
		$badgetype = (int)$_badgetype;
		$array = "";
		switch ($badgetype) {
			case Badge::TYPE_CODE:
				break;
			case Badge::TYPE_LOGIN:
				$actionCount = $user->actionCount_LOGIN + 1;
				$array = array('actionCount_LOGIN' => $actionCount);
				break;
			case Badge::TYPE_ADD2LOG:
				$actionCount = $user->actionCount_ADD2LOG + 1;
				$array = array('actionCount_ADD2LOG' => $actionCount);
				break;
			case Badge::TYPE_REVIEW:
				$actionCount = $user->actionCount_REVIEW + 1;
				$array = array('actionCount_REVIEW' => $actionCount);
				break;
			case Badge::TYPE_LIKE:
				$actionCount = $user->actionCount_LIKE + 1;
				$array = array('actionCount_LIKE' => $actionCount);
				break;
			case Badge::TYPE_WASLIKED:
				$actionCount = $user->actionCount_WASLIKED + 1;
				$array = array('actionCount_WASLIKED' => $actionCount);
				break;
			case Badge::TYPE_CODE_ADD2LOG:
				break;
			default:
		}
		if (is_array($array)) {
			user_save($user, $array);
		}
		
		// list of possible badges to be awarded
		if ($badgetype == Badge::TYPE_CODE) {
			$badges = Badge::loadTypeBadges($badgetype, $_uid, true);
		}
		else {
			$badges = Badge::loadTypeBadges($badgetype, $_uid, false);
		}
		
		$x = 0;
		while ($x < $badges->count) {
			$badge = $badges->getObject($x);
			
			if (!$badge) {
				break;
			}

			// check if they deserve badge
			if ($badgetype == Badge::TYPE_CODE || $badgetype == Badge::TYPE_CODE_ADD2LOG) {
		
				if (strtoupper(trim($code)) == strtoupper(trim($badge->code))) {
					if($badgetype == Badge::TYPE_CODE_ADD2LOG)
					{
						 $mids=Badge::getBadgeLog($badge->bid);
						
						 foreach($mids as $mid)
						 {
						      if(!Media::isInUserMedia($user->uid, $mid))
							  {
							        // save to user's log
							        Media::toggleUserMedia(true, $user->uid, $mid);
									Badge::processAction(Badge::TYPE_ADD2LOG, $user->uid);
							  }
						    
						  }
					}
					Badge::toggleUserBadge(true, $_uid, $badge->bid);
					// return now with bid
					return $badge->bid;
				}
			}
			else {
				// loading ones they haven't yet received
				// so i do a retroactive quantity check
				if ($actionCount >= $badge->quantity) {
					Badge::toggleUserBadge(true, $_uid, $badge->bid);
					$ret = true;
				}
			}
			
			$x++;
		} // end badge loop
		
		return $ret;
	}
  
} // end Badge class