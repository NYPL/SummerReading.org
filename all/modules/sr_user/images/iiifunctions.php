<?php

	// Library's Patron Update Web Service Connection
	global $host;
	global $username;
	global $password;
	global $wsdl_url;
	global $client;
	$host = "partner.iii.com";
	$port = "443";
	$username = "summer";
	$password = "reading";
	$wsdl_url = "https://$host:$port/iii/patronio/services/PatronIO?wsdl";
	$client = new SoapClient($wsdl_url);

	// Library's MySQL Connection
	global $host_mysql;
	global $dsn;
	global $username_mysql;
	global $password_mysql;
	$host_mysql = 'localhost';
	$dsn = "mysql:host=$host_mysql;dbname=summerreading";
	$username_mysql = " summerreading";
	$password_mysql = "CRmAQjXTJzTD ";
	
	// Set timezone
	date_default_timezone_set("America/New_York");
	
	// Summer Reading Prefix Declaration
	global $projField;			// Summer Reading Project Prefix
	global $partialField;		// Summer Reading Pending Prefix
	global $sridField;			// Summer Reading SRID Prefix
	global $dateField;			// Summer Reading Signup Date Prefix
	global $isbnField;			// Summer Reading ISBN Prefix
	global $delim;				// Summer Reading Field delimiter
	$projField = "SR2011";		
	$partialField = "PENDING";	
	$sridField = "SRID";		
	$dateField = "DATE";		
	$isbnField = "ISBN";		
	$delim = "_-_";				
	
	// Misc Global Variables
	global $sr_tag;				// Summer Reading field tag
	global $sr_idx;				// Summer Reading index tag -- Normally value is same as field tag
	global $errorMsg;			// Error message
	global $status;				// Checks for busy records, no record and  duplicate SRID
	global $check_srid;			// Return found SRID
	global $task_complete;		// Flag to determine if task is completed
	global $generate_srid;		// Flag to randomly self-generate srid. DISABLE when SRID is provided by FlightPath.			
	$sr_tag = "c";
	$sr_idx = "n";	
	$errorMsg ="";
	$status = "default";		
	$check_srid = "";			
	$task_complete = 0;			
	$generate_srid = 0;			// Default - Disable to get SRID from FlightPath.

	
function GetPatronObj( $barcode ) {
// Calls Library's Patron Update Web Service by barcode and returns Patron Object
// Returns "fail" if patron record cannot be found

	global $username;
	global $password;
	global $client;
	global $status;
	
	// Check barcode begins with index value "b"
	if ( !(preg_match("/^b/", $barcode)) )
		$barcode = "b".$barcode;
		
	try {
		// Make search SOAP request
		$searchPatronsResponse = $client->searchPatrons($username, $password, "$barcode");
		if ($searchPatronsResponse) {
			return $searchPatronsResponse;
		}
	} catch (SoapFault $exception) {
		// Catch any problems and display the error code
			$errorMessage = $exception->getMessage();
			if ($errorMessage) {
				// echo "\nSoap Fail ( GetPatronObj function ) -- $errorMessage\n";
				if ( preg_match("/9001/", $errorMessage ) )		// Soap Error code for Patron Record Not Found
					$status = "norecord";	
				if ( preg_match("/9011/", $errorMessage ) )		// Soap Error code for Patron Record busy
					$status = "busy";

				return "fail";
			}
	}

} // End GetPatronObj function

function UpdatePatronRecord( $patronObj ) {
// Post changes made to Patron Record to Library's Patron Update Web Service
// and returns updated Patron Object
// Return "fail" if Patron Record cannot be updated

	global $username;
	global $password;
	global $client;
	global $status;

	try {
		// Post updated Patron Object
		$updatePatronResponse = $client->updatePatron($username, $password, $patronObj );
		if ($updatePatronResponse) {
			return $updatePatronResponse;
		}
	} catch (SoapFault $exception) {
		// Catch any problems and display the error code
		$errorMessage = $exception->getMessage();
		if ($errorMessage) {
			//echo "\nSoap Fail ( UpdatePatronRecord function ) -- $errorMessage\n";
			if ( preg_match("/9001/", $errorMessage ) )		// Soap Error code for Patron Record Not Found
				$status = "norecord";
			if ( preg_match("/9011/", $errorMessage ) )		// Soap Error code for Patron Record busy
				$status = "busy";		
				
			return "fail";
		}
	}


} // End UpdatePatronRecord function

function SearchSummerReader ( $srid ) {
// Search for a patron record in the library DB with Summer Reading ID.  Returns Patron Object.

	// Build SRID for index -- EX: SR2011_-_SRID_-_1234
	global $projField;
	global $sridField;
	global $sr_idx;
	global $delim;
	global $status;

	$srid = $projField.$delim.$sridField.$delim.$srid;

	// Check if SRID submitted begins with searching index value 
	if (( (preg_match("/_-_/", $srid)) ) && ( !(preg_match("/^$sr_idx/", $srid)) ))
		$srid = "$sr_idx".$srid;

	global $username;
	global $password;
	global $client;
		
	try {
		// Make search SOAP request
		$searchPatronsResponse = $client->searchPatrons($username, $password, $srid);
		if ($searchPatronsResponse) {
			return $searchPatronsResponse;
		}
	} catch (SoapFault $exception) {
		// Catch any problems and display the error code
			$errorMessage = $exception->getMessage();
			if ($errorMessage) {
				// echo "\nSoap Fail ( GetPatronObj function ) -- $errorMessage\n";
				if ( preg_match("/9001/", $errorMessage ) )		// Soap Error code for Patron Record Not Found
					$status = "norecord";	
				if ( preg_match("/9011/", $errorMessage ) )		// Soap Error code for Patron Record busy
					$status = "busy";

					
				return "fail";
			}
	}

}  // End SearchSummerReader function

function PrintPatronObj ( $patronObj ) {
// Print Patron Object

	global $sr_tag;
	$newline = "</br>";
	
	$obj_size = sizeof($patronObj->patronFields);
	echo "<font color=green><center>";
	echo "$newline";
	if ( $patronObj == "fail" ) {
		echo "Print Fail: Unable to Print Patron Object$newline";
	}
	else {
	
		echo "$newline------- Patron Record --------$newline$newline";
		for ($count = 0; $count < $obj_size; $count++) {

			$checker = $patronObj->patronFields[$count]->fieldTag;
			$value = $patronObj->patronFields[$count]->value;
			
			if ($checker == "n")
				echo "Patron name is:  $value$newline";
			if ($checker == "b")
				echo "Barcode is:  $value$newline";
			if ($checker == $sr_tag)
				echo "Summer Reading UID is:  $value$newline";
			
		}
		echo "$newline-------------------------------$newline";
		echo "</center></font>";
	}


}	// End PrintPatronObj function

function AddField( $project, $fieldType, $value, $patronObj ) {
// Adds Summer Reading ID to Patron Object; Don't add duplicate SRID in Patron Record

	global $delim;
	global $sr_tag;
	global $sridField;
	global $status;
	global $check_srid;

	$obj_size = sizeof($patronObj->patronFields);
	
	// Construct SRID in III format -- ex. 'SR2011_-_SRID_-_1234', 'SR2011_-_DATE_-_YYYYMMDD', 'SR2011_-_ISBN_-_25436269'
	$srid = $project.$delim.$fieldType.$delim.$value;
	
	// Fail if SRID already exists
	for ($count = 0; $count < $obj_size; $count++) {
		$checker = $patronObj->patronFields[$count]->value;
		if (preg_match("/SRID/", $checker) ) {
			//echo "Summer Reading ID already exists in Library's Patron Record.  SRID $srid not added";
			$check_srid = $checker;
			$status = "duplicate";
			$patronObj = "fail";
			
			$prefix= $project.$delim.$sridField.$delim;
			$check_srid = preg_replace("/$prefix/", "", $check_srid);					// Deconstruct III SRID format and return only SRID value
		}
		
		// Check for Partial Sign up
		if ( preg_match("/PENDING/", $checker) ) {
			$status = "pending";
			$patronObj = "fail";
		}
	}

		// Add SRID to Patron Object
	if ( ($status != "duplicate") && ($status != "pending") ){
		$patronObj->patronFields[$obj_size]->fieldTag = "$sr_tag";
		$patronObj->patronFields[$obj_size]->marcTag = "0";
		$patronObj->patronFields[$obj_size]->value = "$srid";
		$patronObj->patronFields[$obj_size]->valueIsBinary = 0;
	}
		
	if ( strlen($value ) == 0 ) {												// Fail if SRID string is blank
		//echo "\nSummer Reading ID is blank.\n";
		$status = "nosrid";
		$patronObj = "fail";
	}

	


	return $patronObj;		// Return Patron Object with added Summer Reading field

}	// End AddField Function

function DeleteField( $project, $fieldType, $value, $patronObj ) {
// Removes Summer Reading ID from Patron Object;  Fail if SRID does not exist.

	global $delim;
	global $sr_tag;
	global $sridField;
	global $partialField;

	$obj_size = sizeof($patronObj->patronFields);
	
	// Construct SRID in III format -- ex. 'SR2011_-_SRID_-_1234', 'SR2011_-_DATE_-_YYYYMMDD', 'SR2011_-_ISBN_-_25436269'
	$srid = $project.$delim.$fieldType.$delim.$value;

	if ( ( strlen($value ) == 0 ) && ($fieldType == $sridField) )  {												// Fail if SRID string is blank
		//echo "\nSummer Reading ID is blank.\n";
		$patronObj = "fail";
	}
	
	$num = 0;																	// Counter to check if SRID exist

	// Remove any occurences of SRID from Patron Object
	if ($patronObj != "fail") {	
		for ($count = 0; $count < $obj_size; $count++) {
			$checker = $patronObj->patronFields[$count]->value;						// Get Value of each field

			if ($checker == $srid) {
				$num++;
				$patronObj ->patronFields[$count]->fieldTag = "";
				$patronObj ->patronFields[$count]->marcTag = "";
				$patronObj ->patronFields[$count]->value = "";
				$patronObj ->patronFields[$count]->valueIsBinary = "";
			}

			if ( (preg_match("/PENDING/", $checker)) && ($fieldType == $partialField) ) {
				$num++;
				$patronObj ->patronFields[$count]->fieldTag = "";
				$patronObj ->patronFields[$count]->marcTag = "";
				$patronObj ->patronFields[$count]->value = "";
				$patronObj ->patronFields[$count]->valueIsBinary = "";
			}
		}
	}

	if ( ($num == 0 ) && ($fieldType == $sridField) ){															// Fail if Summer Reading ID is not found
		//echo "\nDeleting '$srid'  fail.  Summer Reading ID does not exist in Library's Patron Record.\n";
		$patronObj = "fail";
	}
	
	return $patronObj;
	
}	// End DeleteField function

function DeleteFieldByBarcode( $barcode, $patronObj ) {
// Removes Summer Reading ID from Patron Object;  Fail if SRID does not exist.

	global $delim;
	global $sr_tag;
	global $sridField;
	global $partialField;
	global $status;


	$obj_size = sizeof($patronObj->patronFields);
	
	// Construct SRID and partial search string -- ex. 'SR2011_-_SRID', 'SR2011_-_PENDING'
	$srid = $project.$delim.$sridField;
	$p_srid = $project.$delim.$partialField;

	$num = 0;																// Counter to check if SRID exist	
	// Remove Summer Reading ID from Patron Object
	if ($patronObj != "fail") {	
		for ($count = 0; $count < $obj_size; $count++) {
			$checker = $patronObj->patronFields[$count]->value;				// Get Value of each field

			if ( preg_match("/$srid/", $checker ) ){
				$num++;
				$patronObj ->patronFields[$count]->fieldTag = "";
				$patronObj ->patronFields[$count]->marcTag = "";
				$patronObj ->patronFields[$count]->value = "";
				$patronObj ->patronFields[$count]->valueIsBinary = "";
			}

			if ( preg_match("/$p_srid/", $checker ) ){
				$num++;
				$patronObj ->patronFields[$count]->fieldTag = "";
				$patronObj ->patronFields[$count]->marcTag = "";
				$patronObj ->patronFields[$count]->value = "";
				$patronObj ->patronFields[$count]->valueIsBinary = "";
			}
		}
	}


	if ($num == 0 ){						// Fail if Summer Reading ID is not found
		$status = "nosrid";
		$patronObj = "fail";
	}	
	
	return $patronObj;
	
}	// End DeleteFieldByBarcode function

function PartialSignup( $barcode ) {
// Sign Patron up for Summer Reading Program; Fail if there is a duplicate SRID already in Library's Patron Record

	global $projField;
	global $sridField;
	global $dateField;
	global $partialField;

	$patronObj = getPatronObj ($barcode);											// Retrieve Patron Object with barcode

	$format = "Ymd";					//YYYYMMDD
	$date = date($format);
	
	if ( $patronObj != "fail" )
		$patronObj = AddField($projField, $partialField, $date, $patronObj);		// Add PENDING timestamp to Patron Object
		
	if ( $patronObj != "fail" )	
		$patronObj = UpdatePatronRecord($patronObj);								// Update changes to Library Patron Record

	return $patronObj;
	
}	//End OptInSummerReader function

function OptInSummerReader( $srid, $barcode ) {
// Sign Patron up for Summer Reading Program; Fail if there is a duplicate SRID already in Library's Patron Record

	global $projField;
	global $sridField;
	global $partialField;
	global $dateField;

	$patronObj = getPatronObj ($barcode);											// Retrieve Patron Object with barcode
	
	if ( $patronObj != "fail" ) {
		$patronObj = DeleteField($projField, $partialField, "", $patronObj);		// Remove Partial Signup from Patron Object
		$patronObj = AddField($projField, $sridField, $srid, $patronObj);			// Add SRID to Patron Object
	}
	
	if ( $patronObj != "fail" )	
		$patronObj = UpdatePatronRecord($patronObj);								// Update changes to Library Patron Record	
		
	return $patronObj;
	
}	//End OptInSummerReader function

function OptOutSummerReader( $srid ) {
//  Opt patron out from Summer Reading Program;  Fail if SRID cannot be located in Library's Patron Record

	global $projField;
	global $sridField;
	global $dateField;

	$patronObj = SearchSummerReader ($srid);										// Retrieve Patron Object with SRID

	if ( $patronObj != "fail" )
		$patronObj = DeleteField($projField, $sridField, $srid, $patronObj);		// Remove Summer Reading ID from Patron Object

	if ( $patronObj != "fail" )	
		$patronObj = UpdatePatronRecord($patronObj);								// Update changes to Library Patron Record
	
	return $patronObj;
	
}	// End OptOutSummerReader function

function OptOutSummerReaderByBarcode( $barcode ) {
//  Opt patron out from Summer Reading Program;  Fail if SRID cannot be located in Library's Patron Record

	global $projField;
	global $sridField;

	$patronObj = getPatronObj ($barcode);												// Retrieve Patron Object with barcode

	if ( $patronObj != "fail" )
		$patronObj = DeleteFieldByBarcode($barcode, $patronObj);				// Remove Summer Reading ID from Patron Object

		if ( $patronObj != "fail" )		
		$patronObj = UpdatePatronRecord($patronObj);								// Update changes to Library Patron Record
	
	return $patronObj;
	
}	// End OptOutSummerReader function

function PinVerify ( $barcode, $pin ) {

	global $host;
	
	// Check and remove initial index value "b"
	if ( (preg_match("/^b/", $barcode)) )
		$barcode = substr("$barcode", 1);
		
	$url = "https://$host:54620/PATRONAPI/$barcode/$pin/pintest";

	$handle = fopen("$url", "rb");
	$contents = stream_get_contents($handle);
	fclose($handle);

	if ( preg_match("/RETCOD=0/", $contents ) )
		return "valid";
	else
		return "fail";
	
}	// End PinVerify function

function ConnectMySQL () {

	global $dsn;
	global $username_mysql;
	global $password_mysql;

    try {
        $conn = new PDO($dsn, $username_mysql, $password_mysql);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $conn;
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage()."\n";
		return "fail";
    }
			
}

function ProcessTask( $task ) {
// Process the following action items:
// ADD	 	Opt-in Patron with supplied barcode
// REMOVE	Opt-out Patron with supplied SRID
// REMOVEB	Opt-out Patron with supplied barcode
// PRINT	Print Library Patron Recod with supplied barcode
// PRINTSR	Print Summer Reader Patron Record with supplied SRID
// VERIFY	Verify PIN


	global $status;									// Checker for busy record or duplicate SRID
	global $errorMsg;
	global $task_complete;
	global $generate_srid;
	global $check_srid;

	
	// Summer Reading DB connection
	$dbh = ConnectMySQL();
	$table = "iii_log";	
	$sql = "SELECT * FROM $table";
	
    try {
        $logging = $dbh->query($sql);
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage()."\n";
    }

	
	$delete = "DELETE FROM $table WHERE date < DATE_SUB(NOW(), INTERVAL 10 DAY)";		// Maintain log table from growing too large
	$num = $dbh->exec($delete);
	//echo "Deleted $num records<br>";		
	
	$format = "Y-m-d\TH:i:s";						// Log Date Format

	

	$buffer = explode(":", $task);			// Parse line by delimeter colon
	$action = trim($buffer[0]);				// Get action item - "ADD", "REMOVE", "REMOVEB", "PRINT", "PRINTSR", "VERIFY"
	$value = trim($buffer[1]);			
	$value2 = trim($buffer[2]);
		
	$date = date($format);
		
	$task_complete = 0;						// Determine if task needs to be removed from queue
	switch ($action) {
		case "PARTIAL":		// Partial Signup
				$barcode = $value;
				$patronObj = PartialSignup( $barcode );							// Pass  barcode to PartialSignup function
				if ($patronObj != "fail" ) {
					$task_complete = 1;
					$errorMsg = "SUCCESS - Library Record $barcode is partially signed up for Summer Reading.";
					$log = "$action SUCCESS - Library Record $barcode is partially signed up.";
					$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
					$logging = $dbh->query($insert);				
				}
				else {
					if ($status == "pending") {
						$task_complete = 1;			
						$errorMsg = "FAIL - Library Record $barcode has already partially signed up for Summer Reading.";	
						$log = "$action FAIL - Library Record $barcode already partially signed up.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
					}
					elseif ($status == "duplicate") {
						$task_complete = 1;			
						$errorMsg = "FAIL - Library Record $barcode has already fully signed up for Summer Reading.";	
						$log = "$action FAIL - Library Record $barcode already signed up.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
					}
					elseif ($status == "busy") {
						$task_complete = 0;	
						$errorMsg = "SUCCESS - Library Record $barcode is partially signed up for Summer Reading.";
						$log = "$action FAIL - Busy Record $barcode. Returned success --  Task added to queue to be proccessed later.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
					}
					elseif ($status == "norecord") {
						$task_complete = 1;
						$errorMsg =  "FAIL - Library Patron Record $barcode not found.";
						$log = "$action FAIL - Library Patron Record $barcode not found.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
					}
					else {
						$task_complete = 1;
						$errorMsg =  "FAIL - Connection Error.";
						$log = "$action FAIL - Connection Error.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
					}
				}
				break;
		case "ADD":		// OptInSummer Reader
				$barcode = $value;
				// Manually generate SRID for testing (ex. srid1).  SRID will eventually need to come from Flightpath
				if ($generate_srid) {
					$sr_num = rand(1,99);													// Randomly generate 
					$sr_pre = "srid";
					$srid = $sr_pre.$sr_num;
				}
				else
					$srid = $value2;
					
				$patronObj = OptInSummerReader( $srid, $barcode );				// Pass SRID and barcode to OptInSummerReader function
				if ($patronObj != "fail" ) {					
					$task_complete = 1;							// Get new number of lines of file				
					$errorMsg = "$action SUCCESS - Summer Reading ID $srid added to Library Patron Record $barcode.";
					$log = "$action SUCCESS - Summer Reading ID $srid added to Library Patron Record $barcode.";
					$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
					$logging = $dbh->query($insert);
				}
				else {
					if ($status == "duplicate") {
						$task_complete = 1;			
						$errorMsg = "$action FAIL - Duplicate Summer Reading ID $check_srid already in Library Patron Record $barcode.";	
						$log = "$action FAIL - Duplicate Summer Reading ID $check_srid already in Library Patron Record $barcode.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
					}
					elseif ($status == "busy") {
						$task_complete = 0;			
						$errorMsg = "$action SUCCESS - Busy Record $barcode. -- Task added to queue to be proccessed later.";
						$log = "$action FAIL - Busy Record $barcode. -- Returned success -- Task added to queue to be proccessed later.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
					}
					elseif ($status == "norecord") {
						$task_complete = 1;
						$errorMsg =  "$action FAIL - Library Patron Record $barcode not found.";
						$log = "$action FAIL - Library Patron Record $barcode not found.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
						}
					elseif ($status == "nosrid") {
						$task_complete = 1;
						$errorMsg =  "$action FAIL - No Summer Reading ID provided.";
						$log = "$action FAIL - No Summer Reading ID not provided.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
						}
					else {
						$task_complete = 1;
						$errorMsg =  "$action FAIL - Connection Error.";
						$log = "$action FAIL - Connection Error.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
					}
				}
				break;
		case "REMOVE":		// OptOut Summer Reader
				$srid = $value;
				$patronObj = OptOutSummerReader( $srid );						// Pass SRID to OptOutSummerReader function
				if ($patronObj != "fail" ) {
					$task_complete = 1;
					$errorMsg = "$action SUCCESS - Summer Reading ID $srid removed from Library Patron Record.";
					$log = "$action SUCCESS - Summer Reading ID $srid removed from Library Patron Record.";
					$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
					$logging = $dbh->query($insert);	
				}
				else {
					if ($status == "busy") {
						$task_complete = 0;	
						$errorMsg =  "$action SUCCESS - Busy Record $srid. -- Task added to queue to be proccessed later.";
						$log = "$action FAIL - Busy Record $srid. -- Returned success -- Task added to queue to be proccessed later.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
					}
					elseif ($status == "norecord") {
						$task_complete = 1;
						$errorMsg =  "$action FAIL - Summer Reading ID $srid not found in Library Patron Record.";
						$log = "$action FAIL - Summer Reading ID $srid not found in Library Patron Record.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
						}
					else {
						$task_complete = 1;
						$errorMsg =  "$action FAIL - Connection Error.";
						$log = "$action FAIL - Connection Error.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
					}
				}
				break;
		case "REMOVEB":		// OptOut Summer Reader by Barcode
				$barcode = $value;
				$patronObj = OptOutSummerReaderByBarcode( $barcode );						// Pass Barcode to OptOutSummerReaderByBarcoe function
				if ($patronObj != "fail" ) {
					$task_complete = 1;
					$errorMsg = "$action SUCCESS - Library Patron Record $barcode removed from Summer Reading.";
					$log = "$action SUCCESS - Library Patron Record $barcode removed from Summer Reading.";
					$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
					$logging = $dbh->query($insert);	
				}
				else {
					if ($status == "busy") {
						$task_complete = 0;	
						$errorMsg = "$action FAIL - Busy Record $barcode. -- Task added to queue to be proccessed later.";
						$log = "$action FAIL - Busy Record $barcode. -- Task added to queue to be proccessed later.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
					}
					elseif ($status == "norecord") {
						$task_complete = 1;
						$errorMsg =  "$action FAIL - Library Patron Record $barcode not found.";
						$log = "$action FAIL - Library Patron Record $barcode not found.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
						}
					elseif ($status == "nosrid") {
						$task_complete = 1;
						$errorMsg =  "$action FAIL - No Summer Reading ID found in Library Patron Record $barcode.";
						$log = "$action FAIL - No Summer Reading ID found in Library Patron Record $barcode.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
						}
					else {
						$task_complete = 1;
						$errorMsg =  "$action FAIL - Connection Error.";
						$log = "$action FAIL - Connection Error.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
					}
				}
				break;			
		case "PRINT":		// Print Patron Record
				$barcode = $value;
				$patronObj = GetPatronObj($barcode);								// Get Patron Record with barcode
				if ($patronObj != "fail" ) {
					$task_complete = 1;
					$errorMsg = "$date: $action SUCCESS - Print Library Patron Record $barcode.";
					$log = "$action SUCCESS - Print Library Patron Record $barcode.";
					$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
					$logging = $dbh->query($insert);	
					//PrintPatronObj($patronObj);
					}
				else {
					if ($status == "busy") {
						$task_complete = 1;
						$errorMsg =  "$date: $action FAIL - Busy Record $barcode. -- Try again later.";
						$log = "$action FAIL - Busy Record $barcode. -- Try again later.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
					}
					elseif ($status == "norecord") {
						$task_complete = 1;
						$errorMsg =  "$date: $action FAIL - Library Patron Record $barcode not found.";
						$log = "$action FAIL - Library Patron Record $barcode not found.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
						}
					else {
						$task_complete = 1;
						$errorMsg =  "$date: $action FAIL - Connection Error.";
						$log = "$action FAIL - Connection Error.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
					}
				}
						
				if ( (preg_match("/SUCCESS/", $errorMsg)) ) {
					echo "<font color=green><center>$errorMsg</center></font>";
					PrintPatronObj($patronObj);
				}
				else
					echo "<font color=red><center>$errorMsg</center></font>";
					break;
		case "PRINTSR":		// Print Summer Reader
			$srid = $value;
			$patronObj = SearchSummerReader($srid);						// Get Patron Record with SRID
				if ($patronObj != "fail" ) {
					$task_complete = 1;
					$errorMsg =  "$date: $action SUCCESS - Print Summer Reader Library Record $srid.";
					$log = "$action SUCCESS - Print Summer Reader Library Record $srid.";
					$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
					$logging = $dbh->query($insert);	
					//PrintPatronObj($patronObj);				
				}
				else {
					if ($status == "busy") {
						$task_complete = 1;
						$errorMsg =  "$date: $action FAIL - Busy Record $srid.  Try again later.";
						$log = "$action FAIL - Busy Record $srid.  Try again later.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
					}
					elseif ($status == "norecord") {
						$task_complete = 1;
						$errorMsg =  "$date: $action FAIL - Summer Reading ID $srid not found in Library Patron Record.";
						$log = "$action FAIL - Summer Reading ID $srid not found in Library Patron Record.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
						}
					else {
						$task_complete = 1;
						$errorMsg =  "$date: $action FAIL - Connection Error.";
						$log = "$action FAIL - Connection Error.";
						$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
						$logging = $dbh->query($insert);	
					}
				}
					if ( (preg_match("/SUCCESS/", $errorMsg)) ) {
					echo "<font color=green><center>$errorMsg</center></font>";
					PrintPatronObj($patronObj);
				}
				else
					echo "<font color=red><center>$errorMsg</center></font>";
				
				break;
		case "VERIFY":		// Verify Pin
			$barcode = $value;
			$pin = $value2;

			$verify = PinVerify($barcode, $pin);						  	// Verify Barcode/PIN
				if ($verify == "valid" ) {
					$errorMsg =  "SUCCESS - Credentials are Valid";
					//echo "<font color=green><center>$errorMsg</center></font>";
				}
				else {
					$errorMsg = "FAIL - Invalid Credentials";
					//echo "<font color=red><center>$errorMsg</center></font>";	
				}					
				
				$task_complete = 1;
					
				break;
		default:
				$log = "$action FAIL - Task Unknown";
				$insert = "INSERT INTO $table ( date, log ) VALUES ( '$date', '$log' )";
				$logging = $dbh->query($insert);	
				$task_complete = 1;
	
	}	//End switch

	$dbh = null;
}	//End ProcessTask function

function ProcessQueue() {
// Process task from queue.  Queue contains following action items
// ADD	 	Opt-in Patron with supplied barcode
// REMOVE	Opt-out Patron with supplied SRID
// REMOVEB	Opt-out Patron with supplied barcode
// PRINT	Print Library Patron Recod with supplied barcode
// PRINTSR	Print Summer Reader Patron Record with supplied SRID
// VERIFY	Verify PIN

	global $task_complete;
	global $status;
	global $check_srid;

	// Summer Reading DB connection
	$dbh = ConnectMySQL();
	$table = "iii_queue";
	$sql = "SELECT * FROM $table";

	try {
		$rows = $dbh->query($sql);
		foreach ( $rows as $row ) {
			$action = $row["action"];
			$value1 = $row["value1"];
			$value2 = $row["value2"];
			$task = "$action:$value1:$value2";
			ProcessTask($task);
			
			if ($task_complete) {
				$delete = "DELETE from $table where (action = '$action') and (value1 = '$value1') and (value2 = '$value2')";		
				//$del = $db->prepare($delete);
				$num = $dbh->exec($delete);
				//echo "Deleted $num records<br>";	
			}

			$status = "reset";							// Reset soap_status
			$check_srid = "";							// Reset $check_srid
		}

	} catch ( PDOException $e) {
		echo "Query failed: " . $e->getMessage();
	}

	$dbh = null;
}	//End ProcessQueue function

?>
