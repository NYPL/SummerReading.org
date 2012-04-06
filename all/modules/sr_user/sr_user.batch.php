<?php
/**
* The $batch can include the following values. Only 'operations'
* and 'finished' are required, all others will be set to default values.
*
* @param operations
*   An array of callbacks and arguments for the callbacks.
*   There can be one callback called one time, one callback
*   called repeatedly with different arguments, different
*   callbacks with the same arguments, one callback with no
*   arguments, etc.
*
* @param finished
*   A callback to be used when the batch finishes.
*
* @param title
*   A title to be displayed to the end user when the batch starts.
*
* @param init_message
*   An initial message to be displayed to the end user when the batch starts.
*
* @param progress_message
*   A progress message for the end user. Placeholders are available.
*   Placeholders note the progression by operation, i.e. if there are
*   2 operations, the message will look like:
*    'Processed 1 out of 2.'
*    'Processed 2 out of 2.'
*   Placeholders include:
*     @current, @remaining, @total and @percentage
*
* @param error_message
*   The error message that will be displayed to the end user if the batch
*   fails.
*
*/
function batch_sr_user_save() {
  $batch = array(
    'operations' => array(
      array('batch_sr_user_save_process', array()),
      ),
    'finished' => 'batch_sr_user_save_finished',
    'title' => t('Processing SR Users'),
    'init_message' => t('SR User Save Batch is starting.'),
    'progress_message' => t('Processed @current out of @total.'),
    'error_message' => t('SR User Save Batch has encountered an error.'),
    'file' => './sites/summerreading.org/modules/sr_user/sr_user.batch.php',
  );
  batch_set($batch);

  // If this function was called from a form submit handler, stop here,
  // FAPI will handle calling batch_process().

  // If not called from a submit handler, add the following,
  // noting the url the user should be sent to once the batch
  // is finished.
  batch_process('admin/user/user');
}

/**
* Batch Operation Callback
*
* Each batch operation callback will iterate over and over until
* $context['finished'] is set to 1. After each pass, batch.inc will
* check its timer and see if it is time for a new http request,
* i.e. when more than 1 minute has elapsed since the last request.
*
* An entire batch that processes very quickly might only need a single
* http request even if it iterates through the callback several times,
* while slower processes might initiate a new http request on every
* iteration of the callback.
*
* This means you should set your processing up to do in each iteration
* only as much as you can do without a php timeout, then let batch.inc
* decide if it needs to make a fresh http request.
*
* @param options1, options2
*   If any arguments were sent to the operations callback, they
*   will be the first argments available to the callback.
*
* @param context
*   $context is an array that will contain information about the
*   status of the batch. The values in $context will retain their
*   values as the batch progresses.
*
* @param $context['sandbox']
*   Use the $context['sandbox'] rather than $_SESSION to store the
*   information needed to track information between successive calls.
*   The values in the sandbox will be stored and updated in the database
*   between http requests until the batch finishes processing. This will
*   avoid problems if the user navigates away from the page before the
*   batch finishes.
*
* @param $context['results']
*   The array of results gathered so far by the batch processing.
*   The current operation can append its own.
*
* @param $context['message']
*   A text message displayed in the progress page.
*
* @param $context['finished']
*   A float number between 0 and 1 informing the processing engine
*   of the completion level for the operation.
*
*   1 (or no value explicitly set) means the operation is finished
*   and the batch processing can continue to the next operation.
*/
function batch_sr_user_save_process(&$context) {
  if (empty($context['sandbox'])) {
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['current_user'] = 0;
    $context['sandbox']['max'] = db_result(db_query("SELECT COUNT(DISTINCT u.uid) 
      FROM {users} u 
      JOIN {users_roles} ur ON u.uid = ur.uid
      WHERE ur.rid = %d AND u.data LIKE '%%%s%%'", 4, 'profile_'));
  }

  // For this example, we decide that we can safely process
  // 5 nodes at a time without a timeout.
  $limit = 20;

  // With each pass through the callback, retrieve the next group of nids.
  $result = db_query_range("SELECT u.uid 
    FROM {users} u 
    JOIN {users_roles} ur ON ur.uid = u.uid
    WHERE ur.rid = %d AND u.data LIKE '%%%s%' AND u.uid > %d 
    ORDER BY u.uid ASC", 4, 'profile_', $context['sandbox']['current_user'], 0, $limit);
  while ($row = db_fetch_array($result)) {

    // Here we actually perform our processing on the current node.
    $user = user_load(array('uid' => $row['uid']));
    $userData = unserialize($user->data);
    $profileData = array(
      'profile_firstname' => $userData['profile_firstname'],
      'profile_lastname' => $userData['profile_lastname'],
      'profile_age' => $userData['profile_age'],
      'profile_borough' => $userData['profile_borough'],
      'profile_branch' => $userData['profile_branch'],
    );
    $savedUser = user_save($user, $profileData, t('summer_reader'));

    // Store some result for post-processing in the finished callback.
    $context['results'][] = check_plain($savedUser->name);

    // Update our progress information.
    $context['sandbox']['progress']++;
    $context['sandbox']['current_user'] = $savedUser->uid;
    $context['message'] = check_plain($savedUser->name);
  }

  // Inform the batch engine that we are not finished,
  // and provide an estimation of the completion level we reached.
  if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
}

/**
* Batch 'finished' callback
*/
function batch_sr_user_save_finished($success, $results, $operations) {
  if ($success) {
    // Here we do something meaningful with the results.
    $message = format_plural(count($results), 'One user saved.', '@count users saved.');
  }
  else {
    $message = t('Finished with an error');
  }
  drupal_set_message($message);
}
?>