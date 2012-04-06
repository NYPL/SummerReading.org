<?php

include_once 'iiifunctions.php';
	
function AssociateSummerReadingUser($cardId, $PIN, $UID)
{
	global $errorMsg;
	global $task_complete;
	
	$barcode = $cardId;
	$srid = $UID;
	
	$verify = PinVerify($barcode, $PIN);					// Returns "valid" or "fail"

	if ($verify == "valid") {

		$task = "ADD:$barcode:$srid";
		ProcessTask($task);

		// Add task to queue if not completed
		//if (!($task_complete)) {
		//	 db_query("INSERT INTO iii_queue ( action, value1, value2 ) VALUES ( 'ADD', '%s', '%s' )", $barcode, $srid);
		//}
		
		if ( (preg_match("/SUCCESS/", $errorMsg)) )
			return "ok";
		else
			return $errorMsg;									// Return $errorMsg from ProcessTask function
	}
	else
		return "Library card login fail";
	
}

function DisassociateSummerReadingUser($UID)
{
    // call the library web service and remove the flag in Millennium
	global $errorMsg;
	global $task_complete;
	
	$srid = $UID;
	$task = "REMOVE:$srid";
	ProcessTask($task);

	// Check if SRID is already in ADD queue, means has not added into Millennium yet?
	//$num = db_result(db_query("SELECT count(*) FROM iii_queue WHERE (action = 'ADD') and (value2 = '%s')", $srid));
    //$num = $dbh->query($check)->fetchColumn();

	//if ($num > 0 )
	//{
	   //db_query("delete FROM iii_queue WHERE (action = 'ADD') and (value2 = '%s')", $srid);
	   //db_query("update profile_values set value='' where value='%s'", $srid);
        //$task_complete = 0;
	//}
	//else
		//ProcessTask($task);
	//else
        //$task_complete = 1;
    //db_result(db_query("delete FROM iii_queue WHERE (action = 'ADD') and (value2 = '%s')", $srid));

	// Add task to queue if not completed
	//if (!($task_complete)) {
	//	 db_query("INSERT INTO iii_queue ( action, value1, value2 ) VALUES ( 'REMOVE', '%s', '' )", $srid);
	//}

	if ( (preg_match("/SUCCESS/", $errorMsg)) )
	{
		//db_query("update profile_values set value='' where value='%s'", $srid);
		return "ok";
	}
	else
		return $errorMsg;									// Return $errorMsg from ProcessTask function
			
}

?>