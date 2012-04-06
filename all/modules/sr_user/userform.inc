<?php
// $Id: userform.inc,
require_once('librarycard.inc');
/**
 * @file
 * User and profile related form function for the Summer Reading website
 */

/**
 * Menu callback to edit user profile.
 */
function sr_user_page_myaccount() {
	
	//$output = t('My Account Information.');
	$output =sr_user_register_msg();
    $output .= drupal_get_form('sr_user_myaccount');
	if (module_exists('sr_util') && function_exists('sr_util_loadBranches')) {
		sr_util_loadBranches();
    }
    drupal_add_js(drupal_get_path('module', 'sr_util') . '/boroughbranch.js');
	
	global $user;
    profile_load_profile($user);
	
	drupal_add_js(array('boroughSelect' => $user->profile_borough), 'setting');
	drupal_add_js(array('branchSelect' => $user->profile_branch), 'setting'); 
	drupal_add_js(array('school' => $user->profile_school), 'setting'); 
    drupal_add_js(array('luid' => $user->profile_luid), 'setting'); 
	
    return $output;
	//drupal_goto("user/" . $user->uid . "/edit");
}

/**
 * Define edit user profile form.
 */
function sr_user_myaccount() {

	  global $user;
	  profile_load_profile($user);
	  
	  $ages = array(
		'' => t('-- Please select --'),
	  1 => t('Toddlers (0-5)'),
	  2 => t('Kids (6-12)'),
	  3 => t('Teens (12-18)'),
	  4 => t('Adults (19+)')
	  );
	  $form[] = array(
		'#value' => sr_user_roundbox_start("myaccount"),
	  ); 
	  $form['screen_name_fields'] = array(
		'#type' => 'fieldset',
		'#title' => t('Screen Name'),
		'#prefix' => '<div id="div_screen_name">',	
		'#suffix' => '</div">',				
	  );
	  $form['screen_name_fields']['screen_name'] = array(
	   '#value' => '<h4>'.check_plain($user->name).'</h4>',		
	  );
	  $form['account_information'] = array(
	   '#value' => '<span id="account_header">Account Information</span>',
	  );
	  $form['first_name'] = array(
		'#title' => t('First Name'), 
		'#type' => 'textfield',
		'#default_value' =>  $user->{profile_firstname},
		'#description' => t("Enter Your First Name. (private, not visible to others)")
	  );
	  $form['last_name'] = array(
		'#title' => t('Last Name'), 
		'#type' => 'textfield',
		'#default_value' =>  $user->{profile_lastname},
		'#description' => t("Enter Your Last Name. (private, not visible to others)")
	  );
	  $form[]=sr_user_branch_field();
	  $form['email'] = array(
		'#title' => t('Your Email Address'), 
		'#type' => 'textfield',
		'#default_value' =>  $user->mail,
		'#description' => t("Enter Your Email Address. (private, not visible to others)")
	  );
	  $form['age'] = array(
		'#title' => t('Age Group'), 
		'#type' => 'select',
		'#description' => t("Choose Your Age Group. (private, not visible to others)"),
		'#options' => $ages,
		'#required' => true,
		'#default_value' => variable_get('borough',$user->{profile_age}),
	  );
	  $form[]=sr_user_school_field();
	  $form[] = array(
	   '#value' => sr_user_roundbox_end(),
	  );
	  if(strlen($user->profile_luid)>0 )
	  {
		  $form['dissociate_libarary'] = array(
			'#type' => 'fieldset',
			'#title' => t('Stop Automatic Book / Media Logging'),
			'#prefix' => sr_user_yellow_roundbox2_start("dissociate_wrap"),
			'#suffix' => '<div class="imgclear"></div>' . sr_user_yellow_roundbox2_end(),
		  );
		  $form['dissociate_libarary']['msg']=array(
			'#value' => '<div class="cardimage"><img alt="libray card" src="sites/summerreading.org/files/images/img_librarycard_sm.png"/></div><div class="description">'.t('Select below to stop automatic book and media logging and remove your library card information').'</div><br/>',
		  );
		  $form['dissociate_libarary']['luid'] = array(
			'#type' => 'hidden',
			'#default_value' => variable_get('borough',$user->{profile_luid}),
		  );
	      $form['dissociate_libarary']['btn'] = array(
			  '#type' => 'image_button', 
			  '#value' => t(' '), 
			  '#src' => 'sites/summerreading.org/files/images/btn_removecard.gif',
			  '#ahah' => array(
			    'event' => 'click',
				'path' => 'dissociatelibrary',
				'wrapper' => 'dissociate_wrap',
				'method' => 'replace',
				'effect' => 'fade',
			),
		  );
	  }
	  $form[]=sr_user_librarycard_field();
	  $form[]=sr_user_policy_field();
	  $form['submit'] = array(
		'#type' => 'image_button', 
		'#value' => t(' '), 
		'#src' => 'sites/summerreading.org/files/images/btn_updateacct.gif',
		'#prefix'=>'<div class=submitBtn>',
		'#suffix'=>'</div>',
	  );
	  
	  return $form;
}

/**
 * Validate myaccount form.
 */
function sr_user_myaccount_validate($form, &$form_state) {
	if (trim($form_state['values']['email']) != '' &&
   !eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", trim($form_state['values']['email']))) {
     form_set_error('email', t('The email address format is invalid.'));
   }
   if ($form_state['values']['branch_hidden'] == '') {
     form_set_error('branch', t('Branch field is required.'));
   }
   if(!$form_state['values']['is_other'] && strlen(trim($form_state['values']['school']))>0)
   {   $errText = sr_user_validateSchool(trim($form_state['values']['school']), $form_state['values']['borough']);
       if (strlen($errText) > 0) 
		form_set_error('school', t($errText));
   }
}

/**
 * Handle post-validation form submission.
 */
function sr_user_myaccount_submit($form, &$form_state) {
	
    global $user;
 
    $userData = array(
  	   'mail' => (trim($form_state['values']['email'])),
    );
  
    user_save($user, $userData);
	
	if(!strlen(trim($form_state['values']['luid']))>0 &&($form_state['values']['borough']=="Manhattan" ||$form_state['values']['borough']=="Bronx"||$form_state['values']['borough']=="Staten Island")  
    && strlen(trim($form_state['values']['librarycard_number']))>0  && strlen(trim($form_state['values']['librarycard_pin']))>0)
    { 
		$luid = uniqid(sr);

		$errText = AssociateSummerReadingUser($form_state['values']['librarycard_number'], $form_state['values']['librarycard_pin'], $luid);
		if($errText=="ok")
			$form_state['values']['luid']=$luid;
		else 
			$errormsg=$errText;
    }
  
	// save profile data
	$profileData = array(
	'profile_firstname' => (trim($form_state['values']['first_name'])),
	'profile_lastname' => (trim($form_state['values']['last_name'])),
	'profile_age' => $form_state['values']['age'],
	'profile_borough' => $form_state['values']['borough'],
	'profile_branch' => $form_state['values']['branch_hidden'],
	'profile_school' => (trim($form_state['values']['school'])),
	'profile_luid' => (trim($form_state['values']['luid']))
	);

	user_save($user, $profileData, t('summer_reader'));

	drupal_set_message(t('Your account information is successfully updated.'));
	
	if(strlen($errormsg)>0)
		drupal_set_message($errormsg);
}

/**
 * Menu callback for unitadmin form.
 */
function sr_user_page_unit_admin_register() {
	//$output = t('Pre-register a user for the Summer Reading website.');
	$output .= drupal_get_form('sr_user_unit_admin_register');
	
	// populate branch data
	if (module_exists('sr_util') && function_exists('sr_util_loadBranches')) {
		sr_util_loadBranches();
	}
	drupal_add_js(drupal_get_path('module', 'sr_util') .'/boroughbranch.js');
	return $output;
}

/**
 * Define unitadmin form.
 */
function sr_user_unit_admin_register() {
	
	$roles = array(
		'' => t('-- Please select --'),
	  Util::RID_BOROUGH_ADMIN => t('Borough Admin'),
	  Util::RID_BRANCH_ADMIN => t('Branch Admin'),
	  Util::RID_SCHOOL_ADMIN => t('School Admin'),
	);
    $form['account_information_start'] = array(
	   '#value' => sr_user_roundbox_start("register"),
	);
	$form['role'] = array(
		'#title' => t('Admin Type'), 
		'#type' => 'select',
		'#options' => $roles,
		'#required' => true,
	);
	$form['username'] = array(
		'#title' => t('User Name'), 
		'#type' => 'textfield',
		'#description' => t("Enter the user name(contain at least 4 characters)."),
		'#required'=>true,
	);
	$form[]=sr_user_password_field();
    $form['first_name'] = array(
		'#title' => t('First Name'), 
		'#type' => 'textfield',
		'#description' => t("Enter your firstname."),
		'#required'=>true,
	);
	$form['last_name'] = array(
		'#title' => t('Last Name'), 
		'#type' => 'textfield',
		'#description' => t("Enter your lastname."),
		'#required'=>true,
	);
	$form['email'] = array(
		'#title' => t('Email Address'), 
		'#type' => 'textfield',
		'#description' => t("Enter your email address."),
		'#required'=>true,
	);
	$form['phone'] = array(
		'#title' => t('Phone Number'), 
		'#type' => 'textfield',
		'#description' => t("Enter your phone number."),
		'#required'=>true,
	);
	$form['position'] = array(
		'#title' => t('Position'), 
		'#type' => 'textfield',
		'#description' => t("Enter your position."),
		'#required'=>true,
	);
	$form[]=sr_user_branch_field();
    $form[]=sr_user_school_field();
	$form['submit'] = array(
		'#type' => 'image_button', 
		'#value' => t(' '), 
		'#src' => 'sites/summerreading.org/files/images/btn_createyouraccount.gif',
		'#prefix'=>'<div class=submitBtn>',
		'#suffix'=>'</div>',
    );
    $form['account_information_end'] = array(
	   '#value' => sr_user_roundbox_end(),
	);
	return $form;
}

/**
 * Validate preregister form.
 */
function sr_user_unit_admin_register_validate($form, &$form_state) {
   //$iserror = false;
   if (strlen(trim($form_state['values']['username'])) < 4) {
		form_set_error('username', t('username must contain at least 4 characters.'));
   }
   if (trim($form_state['values']['username'])!="" && !sr_user_isUniqueScreenname(trim($form_state['values']['username']))) {
		form_set_error('username', t('That username is already taken. Please change to another username.'));
   }
   if (strlen(trim($form_state['values']['password'])) < 4) {
		form_set_error('password', t('password must contain at least 4 characters.'));
   }
   //if (trim($form_state['values']['email']) != '' && !sr_user_isUniqueEmail(trim($form_state['values']['email'])))
   //{
   // 	form_set_error('email', t('The email address already exits.'));
   //}
   if (trim($form_state['values']['email']) != '' &&
   !eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", trim($form_state['values']['email']))) {
		form_set_error('email', t('The email address format is invalid.'));
   }
   if ($form_state['values']['role'] == 'branch_admin' && $form_state['values']['branch_hidden'] == '') {
		form_set_error('branch', t('Branch field is required for Branch Admin'));
   }
   if ($form_state['values']['role'] == 'school_admin' && $form_state['values']['school'] == '') {
		form_set_error('branch', t('School field is required for School Admin.'));
   }
   if(!$form_state['values']['is_other'] && strlen(trim($form_state['values']['school']))>0){
        $errText = sr_user_validateSchool(trim($form_state['values']['school']), $form_state['values']['borough']);
		if (strlen($errText) > 0) 
			form_set_error('school', t($errText));
   }
}

/**
 * Handle post-validation form submission.
 */
function  sr_user_unit_admin_register_submit($form, &$form_state) {
	// auto register new user
	global $user;
	$name = trim($form_state['values']['username']);
	$roles = array($form_state['values']['role'] => t('summer_reader'));
	$userData = array(
		'name' => $name,
		'pass' => trim($form_state['values']['password']),
		'mail' => trim($form_state['values']['email']),
		'init' => trim($form_state['values']['email']),
		'timezone' => '-18000',
		'roles' => $roles,
		  //'language' => $user->language,
		'access' => 1, // setting to *something* helps identify never logging in
		'status' => 0,
			// probably OK if these not manually inserted here
		'actionCount_LOGIN' => 0,
			'actionCount_ADD2LOG' => 0,
			'actionCount_REVIEW' => 0,
			'actionCount_LIKE' => 0,
			'actionCount_WASLIKED' => 0
	);
	$newuser = user_save(null, $userData);
	// save profile data
	$profileData = array(
		'profile_firstname' => trim($form_state['values']['first_name']),
		'profile_lastname' => trim($form_state['values']['last_name']),
		//'profile_phone' =>  trim($form_state['values']['phone']),
		//'profile_position' =>  trim($form_state['values']['position']),
		'profile_borough' => $form_state['values']['borough'],
		'profile_branch' => $form_state['values']['branch_hidden'],
		'profile_school' => trim($form_state['values']['school'])
	);
	user_save($newuser, $profileData, t('summer_reader'));
	$unitprofileData = array(
		'profile_phone' =>  trim($form_state['values']['phone']),
		'profile_position' =>  trim($form_state['values']['position'])
	);
	user_save($newuser, $unitprofileData, t('unit_admin'));
    drupal_set_message(t('Thank you for your registration. An email will be sent to the admin for approval.'));
}

/**
 * Menu callback for preregister form.
 */
function sr_user_page_preregister() {
    global $user;
	$output = '<div class="left">'.t('Pre-register a user for the Summer Reading website.').'</div>';
	$output .= '<div class="skipandgotoprofile"><a href="/profile/'.urlencode($user->name).'"><img src="/sites/summerreading.org/files/images/btn_skiptoprofile.gif" alt=""/></a></div>';
	$output .= drupal_get_form('sr_user_preregister');
	
	// populate branch data
	if (module_exists('sr_util') && function_exists('sr_util_loadBranches')) {
		sr_util_loadBranches();
	}
	drupal_add_js(drupal_get_path('module', 'sr_util') .'/boroughbranch.js');
	
	// default user's borough and branch
	profile_load_profile($user);
    drupal_add_js(array('boroughSelect' => $user->profile_borough), 'setting');
	drupal_add_js(array('branchSelect' => $user->profile_branch), 'setting'); 
	
	return $output;
}

/**
 * Define preregister form.
 */
function sr_user_preregister() {
	$ages = array(
		'' => t('-- Please select --'),
	  1 => t('Toddlers (0-5)'),
	  2 => t('Kids (6-12)'),
	  3 => t('Teens (12-18)'),
	  4 => t('Adults (19+)')
	);
    $form['account_information_start'] = array(
	   '#value' => sr_user_roundbox_start("register"),
	);
    $form[]=sr_user_name_field();
	$form['number'] = array(
    '#title' => t('4 Digit Number'), 
    '#type' => 'textfield',
    '#description' => t("Enter a 4 digit number."),
    '#size' => 15,
    '#required' => true
	);
	$form['email'] = array(
    '#title' => t('Email Address'), 
    '#type' => 'textfield',
    '#description' => t("Enter the user's email address (if available)."),
    '#default_value' => t('noreply@nypl.org')
	);
	$form['age'] = array(
    '#title' => t('Age'), 
    '#type' => 'select',
    '#description' => t("Choose the user's age group."),
    '#options' => $ages,
    '#required' => true
	);
	$form[]=sr_user_branch_field();
    $form[]=sr_user_school_field();
	$form['submit'] = array(
	'#type' => 'image_button', 
    '#value' => t(' '), 
    '#src' => 'sites/summerreading.org/files/images/btn_createacct.gif',
	'#prefix'=>'<div class=submitBtn>',
	'#suffix'=>'</div>',
   );
   $form['account_information_end'] = array(
	   '#value' => sr_user_roundbox_end(),
	);
/*
  	$form['reset'] = array(
    '#type' => 'button',
    '#value' => t('Reset'),
    '#attributes' => array('onclick' => 'this.form.reset(); return false;') 
	);
*/
	return $form;
}

/**
 * Validate preregister form.
 */
function sr_user_preregister_validate($form, &$form_state) {
	
	$iserror = false;
	if (strlen(trim($form_state['values']['first_name'])) < 3) {
		form_set_error('first_name', t('First name must contain at least 3 characters.'));
		$iserror = true;
	}
	if (strlen(trim($form_state['values']['last_name'])) < 3) {
		form_set_error('last_name', t('Last name must contain at least 3 characters.'));
    $iserror = true;
	}
	if (strlen(trim($form_state['values']['number'])) != 4) {
		form_set_error('number', t('The 4 digit number must contain only 4 digits.'));
    $iserror = true;
	}
	if (!is_numeric(trim($form_state['values']['number']))) {
		form_set_error('number', t('The 4 digit number must contain only numbers.'));
    $iserror = true;
	}
	if (trim($form_state['values']['email']) != '' &&
   !eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", trim($form_state['values']['email']))) {
    form_set_error('email', t('The email address format is invalid.'));
   }
	if ($form_state['values']['branch_hidden'] == '') {
    form_set_error('branch', t('Branch field is required.'));
 	}
   if (!$iserror && !sr_user_isUniqueScreenname(sr_user_formatScreenname($form_state['values']['first_name'], $form_state['values']['last_name'], 
    $form_state['values']['number']))) {
    form_set_error('number', t('The 4 digit number does not generate a unique Screen Name when combined with the First Name and Last Name fields. Please enter another number.'));
   }
   if(!$form_state['values']['is_other'] && strlen(trim($form_state['values']['school']))>0)
   {
        $errText = sr_user_validateSchool(trim($form_state['values']['school']), $form_state['values']['borough']);
		if (strlen($errText) > 0) 
			form_set_error('school', t($errText));
  }
}

/**
 * Handle post-validation form submission.
 */
function sr_user_preregister_submit($form, &$form_state) {
	// auto register new user
	global $user;
	
	$name = sr_user_formatScreenname($form_state['values']['first_name'], $form_state['values']['last_name'], $form_state['values']['number']);
	$roles = array(Util::RID_SUMMER_READER => t('summer_reader'));
	$userData = array(
    'name' => $name,
    'pass' => 'read',
    'mail' => trim($form_state['values']['email']),
    'init' => trim($form_state['values']['email']),
    'timezone' => '-18000',
    'roles' => $roles,
	  //'language' => $user->language,
    'access' => 1, // setting to *something* helps identify never logging in
    'status' => 1,
		// probably OK if these not manually inserted here
    'actionCount_LOGIN' => 0,
		'actionCount_ADD2LOG' => 0,
		'actionCount_REVIEW' => 0,
		'actionCount_LIKE' => 0,
		'actionCount_WASLIKED' => 0
	);
	$newuser = user_save(null, $userData);
	
	// save profile data
	$profileData = array(
    'profile_firstname' => trim($form_state['values']['first_name']),
    'profile_lastname' => trim($form_state['values']['last_name']),
    'profile_age' => $form_state['values']['age'],
    'profile_borough' => $form_state['values']['borough'],
    'profile_branch' => $form_state['values']['branch_hidden'],
	'profile_school' => trim($form_state['values']['school'])
	);
	
	user_save($newuser, $profileData, t('summer_reader'));

	drupal_set_message(t('The user was successfully added.'));
}

/**
 * Menu callback for registration form.
 */
function sr_user_page_register() {
   global $user;
   if($user->uid)
     drupal_goto('profile/'.urlencode($user->name));	
  $output=sr_user_register_msg();
  $output .= drupal_get_form('sr_user_register');
  if (module_exists('sr_util') && function_exists('sr_util_loadBranches')) {
    sr_util_loadBranches();
  }
  drupal_add_js(drupal_get_path('module', 'sr_util') . '/boroughbranch.js');

  return $output;
}

/**
 * Define register form.
 */
function sr_user_register() {

  $ages = array(
    '' => t('-- Please select --'),
  1 => t('Toddlers (0-5)'),
  2 => t('Kids (6-12)'),
  3 => t('Teens (12-18)'),
  4 => t('Adults (19+)')
  );
 
  $form[]=sr_user_screenname_field("register");
  $form['account_information_start'] = array(
   '#value' => sr_user_roundbox_start("register"),
  );
  $form['account_information'] = array(
   '#value' => t('<span id="account_header">Account Information</span>'),
  );
  $form[]=sr_user_password_field();
  $form[]=sr_user_name_field();
  $form[]=sr_user_branch_field();
  $form['email'] = array(
    '#title' => t('Your Email Address'), 
    '#type' => 'textfield',
    '#description' => t("Enter Your Email Address. (private, not visible to others)")
  );
  $form['age'] = array(
    '#title' => t('Age Group'), 
    '#type' => 'select',
    '#description' => t("Choose Your Age Group. (private, not visible to others)"),
    '#options' => $ages,
    '#required' => true
  );
  $form[]=sr_user_school_field();
  $form['account_information_end'] = array(
   '#value' => sr_user_roundbox_end(),
  );
  $form[]=sr_user_librarycard_field();
  $form[]=sr_user_policy_field();
  $form['screen_name'] = array(
    '#type' => 'hidden',
    '#default_value' => ''// 
  ); 
  $form['submit'] = array(
	'#type' => 'image_button', 
    '#value' => t(' '), 
    '#src' => 'sites/summerreading.org/files/images/btn_createyouraccount.gif',
	'#prefix'=>'<div class=submitBtn>',
	'#suffix'=>'</div>',
  );
  return $form;
}
 
/**
 * Validate registration form.
 */
function sr_user_register_validate($form, &$form_state) 
{ 
   $name = $form_state['values']['adjective_hidden'].$form_state['values']['color_hidden'].$form_state['values']['animal_hidden'].$form_state['values']['number_hidden'];
   form_set_value($form['screen_name'], $name, $form_state);
   //watchdog('sr_user', 'step1--name:'.$name.'screenname:'.$form_state['values']['screen_name'], array(), WATCHDOG_ERROR);
    
  $errText = sr_user_validateScreenName($form_state['values']['screen_name']);
  if (strlen($errText) > 0) {
		form_set_error('screen_name', t($errText));
  }
  if ($form_state['values']['branch_hidden'] == '') {
    form_set_error('branch', t('Branch field is required.'));
  }
  if (trim($form_state['values']['email']) != '' &&
   !eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", trim($form_state['values']['email']))) {
    form_set_error('email', t('The email address format is invalid.'));
  }
  if (trim($form_state['values']['password']) == "read") {
    form_set_error('password', t('The chosen Password is forbidden. Please choose another.'));
  }
  if (strlen(trim($form_state['values']['password'])) < 4) {
		form_set_error('password', t('password must contain at least 4 characters.'));
  }
  if(!$form_state['values']['is_other'] && strlen(trim($form_state['values']['school']))>0)
  {
     $errText = sr_user_validateSchool(trim($form_state['values']['school']), $form_state['values']['borough']);
	 if (strlen($errText) > 0) {
		form_set_error('school', t($errText));
     }
  }
  /*
  if(($form_state['values']['borough']=="Manhattan" ||$form_state['values']['borough']=="Bronx"||$form_state['values']['borough']=="Staten Island")  
  && strlen(trim($form_state['values']['librarycard_number']))>0  && strlen(trim($form_state['values']['librarycard_pin']))>0)
  { 
    $luid = uniqid(sr);

    $errText = AssociateSummerReadingUser($form_state['values']['librarycard_number'], $form_state['values']['librarycard_pin'], $luid);
	if($errText=="ok")
		$form_state['values']['luid']=$luid;
	else if($errText=="card not found")
		form_set_error('librarycard_number', t($errText));
	else if($errText=="PIN not valid")
		form_set_error('librarycard_pin', t($errText));
	else
	{
		form_set_error('librarycard_number', t($errText));
		//form_set_error('librarycard_pin', t($errText));
	}
   }*/
}

/**
 * Handle post-validation form submission.
 */
function sr_user_register_submit($form, &$form_state) {
  // logout current user
  //global $user;

  //$null = NULL; // only variables can be passed by reference workaround.
  //user_module_invoke('logout', $null, $user);
	
  // login useradmin for user permissions
  //$params = array(
  //'name'   => 'useradmin',
  //'pass'   => 'b0Oksmar7.'
  //);
  //$useradmin = user_authenticate($params);
   //watchdog('sr_user', 'step2--screenname'.$form_state['values']['screen_name'], array(), WATCHDOG_ERROR);
    
  // register new user
  $roles = array(Util::RID_SUMMER_READER => t('summer_reader'));
  $userData = array(
    'name' => trim($form_state['values']['screen_name']),
    'pass' => (trim($form_state['values']['password'])),
    'mail' => (trim($form_state['values']['email'])),
    'init' => (trim($form_state['values']['email'])),
    'timezone' => '-18000',
    'roles' => $roles,
    //'language' => t('en-NYPL'),
    'access' => 1, // setting to *something* helps identify never logging in
    'status' => 1,
		// probably OK if these not manually inserted here
  	'actionCount_LOGIN' => 0,
		'actionCount_ADD2LOG' => 0,
		'actionCount_REVIEW' => 0,
		'actionCount_LIKE' => 0,
		'actionCount_WASLIKED' => 0
  );
  $newuser = user_save(null, $userData);
  
  if(!strlen(trim($form_state['values']['luid']))>0 &&($form_state['values']['borough']=="Manhattan" ||$form_state['values']['borough']=="Bronx"||$form_state['values']['borough']=="Staten Island")  
    && strlen(trim($form_state['values']['librarycard_number']))>0  && strlen(trim($form_state['values']['librarycard_pin']))>0)
    { 
		$luid = uniqid(sr);

		$errText = AssociateSummerReadingUser($form_state['values']['librarycard_number'], $form_state['values']['librarycard_pin'], $luid);
		if($errText=="ok")
			$form_state['values']['luid']=$luid;
		else 
			$errormsg=$errText;
    }
  // now save profile data
  $profileData = array(
    'profile_firstname' => (trim($form_state['values']['first_name'])),
    'profile_lastname' => (trim($form_state['values']['last_name'])),
    'profile_age' => $form_state['values']['age'],
    'profile_borough' => $form_state['values']['borough'],
    'profile_branch' => $form_state['values']['branch_hidden'],
	'profile_school' => (trim($form_state['values']['school'])),
	'profile_luid' => (trim($form_state['values']['luid']))
  );
  user_save($newuser, $profileData, t('summer_reader'));
  //watchdog('sr_user', 'step3--screenname'.$form_state['values']['screen_name'], array(), WATCHDOG_ERROR);
    
  // logout useradmin
  //user_module_invoke('logout', $null, $useradmin);
  
  // finally login the new user
  $params = array(
  'name'   => trim($form_state['values']['screen_name']),
  'pass'   => (trim($form_state['values']['password']))
  );
   //watchdog('sr_user', 'step4--screenname'.$form_state['values']['screen_name'], array(), WATCHDOG_ERROR);
  
  $user = user_authenticate($params);
 if(strlen($errormsg)>0)
		drupal_set_message($errormsg);
  else
	drupal_goto('user/'. $newuser->uid);
}

/**
 * Menu callback for password change form.
 */
function sr_user_page_passchange() {
  $output = t('Welcome to Summer Reading! Please change your initial Screen Name and Password.');
  $output .= drupal_get_form('sr_user_passchange');
  
  return $output;
}

/**
 * Define password change form.
 */
function sr_user_passchange() {
  
  $form[]=sr_user_screenname_field("passchange");
 
  $form[]=sr_user_password_field();
  
  $form['screen_name'] = array(
    '#type' => 'hidden',
    '#default_value' => ''// 
  );
  
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save')
  );

  return $form;
}

/**
 * Validate password change form.
 */
function sr_user_passchange_validate($form, &$form_state) {
  
    $name = $form_state['values']['adjective_hidden'].$form_state['values']['color_hidden'].$form_state['values']['animal_hidden'].$form_state['values']['number_hidden'];
    form_set_value($form['screen_name'], $name, $form_state);
	$errText = sr_user_validateScreenName($form_state['values']['screen_name']);
	if (strlen($errText) > 0) {
		form_set_error('screen_name', t($errText));
	}
	else {
		global $user;
		if ($user->name == trim($form_state['values']['screen_name'])) {
			form_set_error('screen_name', t('The chosen Screen Name is identical to your existing one. Please choose another.'));
		}
	}
	if (strlen(trim($form_state['values']['password'])) < 4) {
		form_set_error('password', t('password must contain at least 4 characters.'));
    }
	if (trim($form_state['values']['password']) == "read") {
		form_set_error('password', t('The chosen Password is forbidden. Please choose another.'));
	}
  
}

/**
 * Handle post-validation form submission.
 */
function sr_user_passchange_submit($form, &$form_state) {
  // change user password
  global $user;
  
  $userData = array(
  	'name' => trim($form_state['values']['screen_name']),
    'pass' => (trim($form_state['values']['password']))
  );
  user_save($user, $userData);
  
  //drupal_set_message(t('Thank you. Your password was successfully changed.'));
  //drupal_goto('profile/' . urlencode($form_state['values']['screen_name']));
  if (!sr_user_nodb_isAdmin($user)) {
	drupal_goto('timereading');
  }
  else {
	drupal_goto('administer');
  }
}

/**
 * Menu callback for password change reset form.
 */
function sr_user_page_passchangereset() {
  $output = t('Welcome to Summer Reading! Please change your Password.');
  $output .= drupal_get_form('sr_user_passchangereset');
  
  return $output;
}

/**
 * Define password change reset form.
 */
function sr_user_passchangereset() {
  
  $form[]=sr_user_password_field();
  
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save')
  );

  return $form;
}

/**
 * Validate password change form.
 */
function sr_user_passchangereset_validate($form, &$form_state) {
    
  if (trim($form_state['values']['password']) == "read") {
    form_set_error('password', t('The chosen Password is forbidden. Please choose another.'));
  }
  if (strlen(trim($form_state['values']['password'])) < 4) {
		form_set_error('password', t('password must contain at least 4 characters.'));
    }
}

/**
 * Handle post-validation form submission.
 */
function sr_user_passchangereset_submit($form, &$form_state) {
  // change user password
  global $user;
  
  $userData = array(
    'pass' => (trim($form_state['values']['password']))
  );
  user_save($user, $userData);
  
  //drupal_set_message(t('Thank you. Your password was successfully changed.'));
  //drupal_goto('profile/' . urlencode($form_state['values']['screen_name']));
  if (!sr_user_isAdmin($user)) {
	drupal_goto('timereading');
  }
  else {
	drupal_goto('administer');
  }
}

function sr_user_school_field() {
  $form['school_start'] = array(
    '#value' => "<div id='schoolDiv'><div class='title'>Your School:</div>
	            <div class='msg'>Students in grades K-12: Start typing your school then click on your school's name when it appears (private, not visible to others)</div>", 
  );
  $form['school'] = array(
    '#type' => 'textfield',
    '#autocomplete_path' => 'sr_user/autocomplete/node',
	'#size' => 80,
	'#default_value' =>  $user->{profile_school},
	'#prefix' => '<div id="school_text">', 
  );
  $form['is_other'] = array(
	 '#type' => 'checkbox',
	 '#title' => '<b>'.t('No school match, Enter what I typed.').'</b>',
	 '#suffix' => '</div>', 
  );
  $form['school_msg'] = array(
    '#value' => "<div class='msg'>If you don't see a match click \"No school match.  Enter what I typed.\" (hint: enter public schools using their initials and numbers \"PS 15\",
					 \"IS 49\" etc. or schools that start with the word \"Saint\" using the initials \"St\" i.e. St. Mary's</div>", 
  );
  $form['school_end'] = array(
    '#value' => "</div><br/>", 
  );
  return $form;
}

function sr_user_policy_field() {

   $form['private_policy_message'] = array(
    '#value' => "<div class='policy-description'>The information you provide to SummerReading.org is governed by the privacy policy of The New York Public 
			Library, which runs the SummerReading.org site.  If your home library is not the New York Public Library, 
			information you provide to SummerReading.org will also be shared with your home library.  
			Use of your information by your home library is governed by that library's privacy policy.</div><br/>
			Click below to learn more about how your information may be used.<br/>"
  );
  
  $form['privacy_policy'] = array(
    '#value' => '<a href="/nypl-privacy-policy" target="_blank"><b>READ THE NYPL PRIVACY POLICY HERE</b></a>',
		'#prefix' => '<div id="manhattan_policy">', 
  	    '#suffix' => '</div>', 
  );
  
  $form['brooklyn_policy'] = array(
    '#value' => '<a href="/brooklyn-privacy-policy" target="_blank"><b>READ THE BROOKLYN PRIVACY POLICY HERE</b></a>',
	'#prefix' => '<div id="brooklyn_policy" style="display:none">', 
  	'#suffix' => '</div>', 
  );
  
  $form['queens_policy'] = array(
    '#value' => '<a href="/queens-privacy-policy" target="_blank"><b>READ THE QUEENS PRIVACY POLICY HERE</b></a>',
	'#prefix' => '<div id="queens_policy" style="display:none">', 
  	'#suffix' => '</div>', 
  );
  $form['line_break'] = array(
    '#value' => t('<br/><br/>'),
  );
  
  return $form;
}

function sr_user_branch_field() {
 $boroughs = array(
   '' => t('-- Please select --'),
   'Bronx' => t('Bronx'),
   'Brooklyn' => t('Brooklyn'),
   'Manhattan' => t('Manhattan'),
   'Staten Island' => t('Staten Island'),
   'Queens' => t('Queens'),
   'Not in NYC' => t('Not in NYC')
  );
  
  $branches = t('<div class="form-item" id="edit-branch-wrapper"><label for="edit-branch">Your Branch Library: <span class="form-required" title="This field is required.">*</span></label>
    <select name="branch" class="form-select required" id="edit-branch" ><option value="" selected="selected">...</option></select>
    <div class="description">Choose your library branch. (private, not visible to others)</div></div>');
  
  $form['borough'] = array(
    '#title' => t('Your Borough'), 
    '#type' => 'select',
    '#description' => t("Choose Your Borough in NYC. (private, not visible to others)"),
    '#options' => $boroughs,
    '#required' => true,
	//'#default_value' => variable_get('borough',$user->{profile_borough}),
  ); 
    
  $form['branch'] = array(
    '#prefix' => '<div>',
    '#value' => $branches, 
    '#suffix' => '</div>', 
	//'#default_value' => variable_get('branch',$user->{profile_branch}),
  );
  
  $form['branch_hidden'] = array(
    '#type' => 'hidden',
    '#default_value' => '',// $user->{profile_branch}, // use #default_value for form_state to recognize jquery alterations
  );
  
  return $form;
}

function sr_user_screenname_field($type) {
  $form['screen_name_start'] = array(
	'#value' => sr_user_roundbox_start("screenname"),
  ); 
  
  $form['screen_name_fields'] = array(
    '#type' => 'fieldset',
    '#title' => t('Screen Name'),
	'#description' => t('Pick a screen name from the choices below.  These 3 words will be combined to create your screen name.  
	                   Keep clicking the "New" buttons until you get the name you want. (Your screen will be visible to others) Our screen name generator has created the following screen name for you:
					  '),
	'#prefix' => '<div id="div_screen_name">',	
    '#suffix' => '</div">',				
  );
  
  $adjective_value=sr_user_get_screenname("adjective");
  $color_value=sr_user_get_screenname("color");
  $animal_value=sr_user_get_screenname("animal");
  $number_value=sr_user_get_screenname_number($adjective_value.$color_value.$animal_value);
//watchdog('sr_user', 
//'sr_user_screenname_field:$adjective_value:'.$ $adjective_value.$color_value.$animal_value, array(), WATCHDOG_ERROR); 
  $form['screen_name_fields']['adjective'] = array(
    '#type' => 'item',
    '#value' => $adjective_value,
	'#prefix' => '<div id="div_screen_name_adjective">',
  );
  
  $form['screen_name_fields']['adjective_hidden'] = array(
    '#type' => 'hidden',
    '#value' => $adjective_value,
  );
  
  $form['screen_name_fields']['color'] = array(
    '#type' => 'item',
    '#value' => $color_value,
  );
  
  $form['screen_name_fields']['color_hidden'] = array(
    '#type' => 'hidden',
    '#value' => $color_value,
  );
  
  $form['screen_name_fields']['animal'] = array(
    '#type' => 'item',
    '#value' => $animal_value,
  );
  
  $form['screen_name_fields']['animal_hidden'] = array(
    '#type' => 'hidden',
    '#value' => $animal_value,
  );
  
  $form['screen_name_fields']['number'] = array(
    '#type' => 'item',
    '#value' => $number_value,
  );
  
  $form['screen_name_fields']['number_hidden'] = array(
    '#type' => 'hidden',
    '#value' => $number_value,
    '#suffix' => '</div>',
  );
  
  $form['btn_start'] = array(
	'#value' => '<div id="btnDiv" class="form-item">',
  );
  
  $form['screen_name_fields1'] = array(
    '#type' => 'fieldset',
    '#title' => "",
  );
  
  $form['screen_name_fields1']['btn_adjective'] = array(
  '#type' => 'image_button', 
  '#value' => t(' '), 
  '#src' => 'sites/summerreading.org/files/images/btn_newdescription.gif',
  '#ahah' => array(
   'event' => 'click',
    'path' => 'screenname/adjective',
    'wrapper' => 'div_screen_name_adjective',
    'method' => 'replace',
    'effect' => 'fade',
    ),
  );
  
  $form['screen_name_fields2'] = array(
    '#type' => 'fieldset',
    '#title' =>"",
  );
  
  $form['screen_name_fields2']['btn_color'] = array(
  '#type' => 'image_button', 
  '#value' => t(' '), 
  '#src' => 'sites/summerreading.org/files/images/btn_newcolorlook.gif',
  '#ahah' => array(
    'event' => 'click',
    'path' => 'screenname/color',
    'wrapper' => 'div_screen_name_adjective',
    'method' => 'replace',
    'effect' => 'fade',
    ),
  );
  
  $form['screen_name_fields3'] = array(
    '#type' => 'fieldset',
    '#title' => "",
  );
  
  $form['screen_name_fields3']['btn_animal'] = array(
  '#type' => 'image_button', 
  '#value' => t(' '), 
  '#src' => 'sites/summerreading.org/files/images/btn_newanimal.gif',
  '#ahah' => array(
    'event' => 'click',
    'path' => 'screenname/animal',
    'wrapper' => 'div_screen_name_adjective',
    'method' => 'replace',
    'effect' => 'fade',
    ),
  );
  
  if($type=="register")
  {
	  $form['btn_end'] = array(
		'#value' => '</div> <div class="description">Once you find a name you like, please fill out the form below, read NYPL\'s privacy policy, and then click the "Create Your Account" button </div>',
	  );
  }
  else
      $form['btn_end'] = array(
		'#value' => '</div> ',
	  );
  
  $form['screen_name_end'] = array(
	'#value' => sr_user_roundbox_end(),
  ); 
 
  return $form;
}

function sr_user_password_field() {
 $form['password'] = array(
    '#type' => 'password_confirm',
    '#required' => true,
    '#size' => 25,
	'#description' => t("Select a password that you will remember. It should be at least 4 characters long. Remember to keep your password secret and do not share it with others."),
  );
   return $form;
}

function sr_user_name_field() {
 $form['first_name'] = array(
    '#title' => t('First Name'), 
    '#type' => 'textfield',
    '#description' => t("Enter Your First Name. (private, not visible to others)")
  );
  
  $form['last_name'] = array(
    '#title' => t('Last Name'), 
    '#type' => 'textfield',
    '#description' => t("Enter Your Last Name. (private, not visible to others)")
  );
   return $form;
}

function sr_user_librarycard_field() {

 $form['librarycard_start'] = array(
   '#value' => '<div id="librarycard" style="display:none">'.sr_user_yellow_roundbox_start("librarycarddiv"),
 );
 
 $form['librarycard_information'] = array(
   '#value' => '<span class="header">Automatic Book/Media Logging</span>',
 );
 
 
 
  $form[] = array(
    '#value' => '<div class="cardimage"><img alt="libray card" src="sites/summerreading.org/files/images/img_librarycard_sm.png"/></div>'
  );
  
 $form['librarycard_number'] = array(
    '#title' => t('Library Card Number'), 
    '#type' => 'textfield',
	'#size' => 30,
      '#description' => t("Enter Your Library Card Number (private, not visible to others)")
  );
  
  $form['librarycard_pin'] = array(
    '#title' => t('PIN Number'), 
    '#type' => 'password',
	'#size' => 20,
    '#description' => t("Enter the PIN Number Associated with Your Library Card. (private, not visible to others)")
  );
    
  $form['librarycard_end'] = array(
    '#value' => '<div class="bot"></div>'.sr_user_yellow_roundbox_end(),
  );
   return $form;
}

function sr_user_register_msg() {

  $output = '<div class="page-subtitle">'. t("Please read the following <a href='/safety'>Summer Reading info</a> before you register. When participating in the Summer Reading program please remember to be safe. 
              Don't put your personal information on the Internet for others to see. Keep your passwords, real name, age, address, school, and grade private. 
			  Ask your parent or guardian for help if you aren't sure what to do.") ."<br/><br/>";
  $output .= t("Note to Adults: Adults are welcome to sign up for Summer Reading also.  Please note, however, 
             that you will need to comply with the same rules created for participants who are under the age of 18:  
			 the site may not be used to exchange personal information or to set up meetings.") ."</div>";
  
  return $output;
}




