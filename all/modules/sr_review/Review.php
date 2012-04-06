<?php

class Review {
  
  public $rid;
  public $mid;
  public $uid;
  public $screenname;
  public $nid;
  public $vid; // node revision id useful for "read more..." link?
  public $summary;
  public $summaryWasTrimmed; // true if summary was trimmed further
  public $body;
  public $title;
  public $likes;
  public $status;
  
  CONST SUMMARY_LEN = 200;
  CONST REVIEW_CALLER_LOG = "medialog";
  CONST REVIEW_CALLER_PAGE = "mediapage";
  
  public function __construct() {
    // constructor
    $this->likes = Array();
    $this->summaryWasTrimmed = false;
  }
  
  public function isLiker($_uid) {
  	foreach ($this->likes as $value) {
  		if ($_uid == $value) {
  			return true;
  		}
  	}
  	return false;
  }
  
  public function saveReview($title) {
  	$rid=0;
  	// insert the node data
    module_load_include('inc', 'node', 'node.pages');
    
    $node = new StdClass();
    $node->type = "review";
    node_object_prepare($node); // not sure if this is needed or not
    $node->uid = $this->uid;
    $node->status = $this->status;
    $node->comment = 0;
    $node->promote = 0;
    $node->title = "Review: " . $title;
    $node->body = $this->body;
 //watchdog('actions', 'Stack overflow: '.$rid.$hasBadWords, array(), WATCHDOG_ERROR);
	$count = db_result(db_query("SELECT COUNT(*) FROM sr_review WHERE uid = %d AND mid = %d", $node->uid, $this->mid));
    $bob = $count ; 
    
	if ( $bob == 0 ) {
        node_save($node);

	    if (!$node->nid) {
	        return false;
	    }
	    $this->nid = $node->nid;
    
	    // insert review
	    db_query("INSERT INTO {sr_review} (uid, mid, nid)
	  	  VALUES (%d, %d, %d)",
	    	$this->uid, $this->mid, $this->nid);
        $rid= db_result(db_query("SELECT rid FROM {sr_review} WHERE nid = %d", $this->nid));  
	    // add to their log if not already
	    Media::toggleUserMedia(true, $this->uid, $this->mid);
	}  	

	return $rid;
  }
  
  // **************************************
  // static methods
  // **************************************
  

  public static function cast(Review $object) {
        return $object;
  }
  
  public static function saveLike($_rid, $_uid) {
  	
    db_query("INSERT INTO {sr_review_like} (rid, uid)
  	  VALUES (%d, %d)",
    	$_rid, $_uid);
    
    return true;
  	
  }
  
   public static function reportReview($_rid, $_uid) {
  	
     @db_query("INSERT INTO {sr_review_report} (rid, uid) VALUES (%d, %d)",$_rid, $_uid);
    
    return db_affected_rows();
  	
  }
  
   public static function removeReportReview($_rid) {
  	
     @db_query("DELETE FROM {sr_review_report} WHERE rid=%d",$_rid);
    
    return true;
  	
  }
  
  
  public static function loadMediaReviews($_mid, $_uid = 0) {
  	
  	$reviews = new Collection();

  	// load both published and unpublished, so user can see its unpublished
  	$sql = "SELECT r.rid, r.uid, r.mid, r.nid, n.vid, n.title, n.status, nr.title, nr.body, u.name  
  		FROM {sr_review} r 
  		INNER JOIN {node} n ON r.nid = n.nid 
  		INNER JOIN {node_revisions} nr ON n.vid = nr.vid 
  		INNER JOIN {users} u ON r.uid = u.uid 
  		WHERE r.mid = %d ";
  	if ($_uid > 0) {
  		// load just this users review
  		$sql .= " AND u.uid = %d";
  		$results = db_query($sql, $_mid, $_uid);
  	}
  	else {
  		$sql .= " AND u.uid = (SELECT ur.uid FROM {users_roles} ur WHERE ur.rid = %d AND u.uid = ur.uid)";
  		$sql .= " ORDER BY r.rid DESC";
  		$results = db_query($sql, $_mid, Util::RID_SUMMER_READER);
  	}
  	
  	$x = 0;
		while ($fields = db_fetch_array($results)) {
			  
			$review = new Review();
			Review::buildReview($fields, $review);
			
      // load likes
      $results_likes = db_query("SELECT lid, uid FROM {sr_review_like} WHERE rid = %d", $review->rid);
			while ($fields_likes = db_fetch_array($results_likes)) {
	 		  Review::buildLikes($fields_likes, $review);
	    }
      
      $reviews->addObject($x, $review);
      $x++;
    }
    
    return $reviews;
  }
  
  public static function loadUnpublishedReviews() {
  	
  	$reviews = new Collection();

  	// load both published and unpublished, so user can see its unpublished
  	$results = db_query("SELECT r.rid, r.uid, r.mid, r.nid, n.vid, n.status, nr.title, nr.body, u.name  
  		FROM {sr_review} r 
  		INNER JOIN {node} n ON r.nid = n.nid 
  		INNER JOIN {node_revisions} nr ON n.vid = nr.vid 
  		INNER JOIN {users} u ON r.uid = u.uid 
  		WHERE n.status = 0 ORDER BY r.rid DESC");
  	
  	$x = 0;
		while ($fields = db_fetch_array($results)) {
			  
			$review = new Review();
			Review::buildReview($fields, $review);
			
      // load likes
      $results_likes = db_query("SELECT lid, uid FROM {sr_review_like} WHERE rid = %d", $review->rid);
			while ($fields_likes = db_fetch_array($results_likes)) {
	 		  Review::buildLikes($fields_likes, $review);
	    }
      
      $reviews->addObject($x, $review);
      $x++;
	 
    }
  
    return $reviews;
  }
  
   public static function loadReportedReviews() {
  	
  	$reviews = new Collection();
 
  	$results = db_query("
	SELECT distinct r.rid, r.uid, r.mid, r.nid, n.vid, n.status, nr.title, nr.body, u.name  
	    FROM {sr_review_report} re
  		INNER JOIN  {sr_review} r on r.rid=re.rid
  		INNER JOIN {node} n ON r.nid = n.nid 
  		INNER JOIN {node_revisions} nr ON n.vid = nr.vid 
  		INNER JOIN {users} u ON r.uid = u.uid 
		WHERE n.status = 1
  		ORDER BY r.rid DESC");
  	
  	    $x = 0;
		while ($fields = db_fetch_array($results)) {
			  
			$review = new Review();
			Review::buildReview($fields, $review);
			
        // load likes
        $results_likes = db_query("SELECT lid, uid FROM {sr_review_like} WHERE rid = %d", $review->rid);
			while ($fields_likes = db_fetch_array($results_likes)) {
	 		  Review::buildLikes($fields_likes, $review);
	    }
      
        $reviews->addObject($x, $review);
        $x++;
    }
    return $reviews;
  } 
  
  public static function emailReportedReview($rid, $uid, $subject) {
		//$to = 'bei.cao@gmail.com';
		$to = variable_get('site_mail', '').','.Review::getBranchAdminEmail($rid);
		$review=Review::loadReview($rid, 0);
		if($review!=null)
		{
		   global $base_url;
		  // $message['body'][0]="Unpublished OR Reported Reviews:<br/>"; 
		   $message['body'][1]=$review->title; 
		   $message['body'][2]="<br/>Review Body:"; 
		   $message['body'][3]=$review->body; 
		   $message['body'][4]="<br/>UserName:"; 
		   $message['body'][5]=$review->screenname; 
		   $message['body'][6]='<br/><a href="'.$base_url.'/node/'.$review->nid.'/edit">Check This Review</a>';
		   $message['body'][7]='<br/>If the link does not work, the review may have been deleted by another admin.</a>';
			$params = array(
			  'body' => $message['body'],
			  'subject' => $subject,
			);
			$message = drupal_mail('sr_review', 'report_reivew', $to, language_default(), $params);
			//return $email;
		}
		//return false;
   }
   
   private static function getBranchAdminEmail($rid) {
		$admin_emails="";
		$sql = "SELECT value FROM {profile_values} pv INNER JOIN {sr_review} r ON r.uid=pv.uid WHERE pv.fid=%d AND r.rid=%d LIMIT 1";
		$branchid = db_result(db_query($sql,Util::FID_PROFILE_BRANCH,$rid));
		if($branchid>0)
		{
			$branch_amdin_emails = db_query("SELECT mail FROM {users} u INNER JOIN {profile_values} pv on pv.uid=u.uid INNER JOIN {users_roles} ur ON ur.uid=u.uid 
									   WHERE pv.fid = %d AND ur.rid=%d AND pv.value=%d", Util::FID_PROFILE_BRANCH,Util::RID_BRANCH_ADMIN, $branchid);
			while ($email = db_fetch_array($branch_amdin_emails)) {
			   $admin_emails.=$email['mail'].",";
			}
		}
		return  $admin_emails;
   }
   
	private static function buildReview($fields, &$review) {
	
		foreach($fields as $key => $value) {
			switch ($key) {
				case "rid":
					$review->rid = $value;
					break;
				case "uid":
					$review->uid = $value;
					break;
				case "mid":
					$review->mid = $value;
					break;
				case "nid":
					$review->nid = $value;
					break;
				case "vid":
					$review->vid = $value;
					break;
				case "title":
					$review->title = $value;
					break;
				case "body":
					$review->body = $value;
					$summary = substr($review->body, 0, Review::SUMMARY_LEN);
					if (strlen($review->body) > Review::SUMMARY_LEN) {
						if (strrpos(trim($summary), ' ')) {
							$review->summaryWasTrimmed = true;
							$review->summary = trim(substr($summary, 0, strrpos($summary, ' ')));
						}
						else {
							$review->summary = $summary;
						}
						
					}
					else {
						$review->summary = $summary;
						
					}
					break;
				case "name":
					$review->screenname = $value;
					break;
				case "status":
					$review->status = $value;
				default:
			}
	  }
	
	}
	
	private static function buildLikes($fields, &$review) {
	
		foreach ($fields as $key => $value) {
			switch ($key) {
				case "lid":
					$_lid = $value;
					break;
				case "uid":
					$_uid = $value;
					break;
				default:
			}
	  }
	     
	  $review->likes[$_lid] = $_uid;
	
	}
	
	public static function loadReview($_rid) {
	  	
  	$results = db_query("SELECT r.rid, r.uid, r.mid, r.nid, n.vid, n.status, nr.title, nr.body, u.name  
  		FROM {sr_review} r 
  		INNER JOIN {node} n ON r.nid = n.nid 
  		INNER JOIN {node_revisions} nr ON n.vid = nr.vid 
  		INNER JOIN {users} u ON r.uid = u.uid 
  		WHERE r.rid = %d", 
  		$_rid);
  	
		while ($fields = db_fetch_array($results)) {
 		  
			$review = new Review();
			Review::buildReview($fields, $review);
			
      // load likes
      $results_likes = db_query("SELECT lid, uid FROM {sr_review_like} WHERE rid = %d", $review->rid);
			while ($fields_likes = db_fetch_array($results_likes)) {
	 		  Review::buildLikes($fields_likes, $review);
	    }
      
    }
	    
    return $review;
	  	
  }
  
  public static function makeLikeImageURL() {
  	return 'http://'. $_SERVER['HTTP_HOST'] . base_path() . file_directory_path() .'/like.gif';
  }
  
  public static function loadReviewBody($_rid) {
  	
  	$result = db_result(db_query("SELECT nr.body 
  	  FROM {sr_review} r 
  	  INNER JOIN {node} n ON r.nid = n.nid 
  		INNER JOIN {node_revisions} nr ON n.vid = nr.vid 
  		WHERE r.rid = %d", $_rid));
  	if ($result && is_string($result)) {
  		return $result;
  	}
  	else {
  		return "";
  	}
  }
  
} // end Review class


