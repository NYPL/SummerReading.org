<?php


/**
 * Helper to implementation of hook_imagecache_default_presets().
 */
function _feature_wysiwyg_imagecache_default_presets() {
$presets = array();
$presets['thumbnail'] = array (
  'presetname' => 'thumbnail',
  'actions' => 
  array (
    0 => 
    array (
      'weight' => '0',
      'module' => 'imagecache',
      'action' => 'imagecache_scale_and_crop',
      'data' => 
      array (
        'width' => '85',
        'height' => '85',
      ),
    ),
  ),
);  
$presets['small'] = array (
  'presetname' => 'small',
  'actions' => 
  array (
    0 => 
    array (
      'weight' => '0',
      'module' => 'imagecache',
      'action' => 'imagecache_scale_and_crop',
      'data' => 
      array (
        'width' => '130',
        'height' => '130',
      ),
    ),
  ),
);
$presets['medium'] = array (
  'presetname' => 'medium',
  'actions' => 
  array (
    0 => 
    array (
      'weight' => '0',
      'module' => 'imagecache',
      'action' => 'imagecache_scale',
      'data' => 
      array (
        'width' => '200',
      ),
    ),
  ),
);
$presets['large'] = array (
  'presetname' => 'large',
  'actions' => 
  array (
    0 => 
    array (
      'weight' => '0',
      'module' => 'imagecache',
      'action' => 'imagecache_scale',
      'data' => 
      array (
        'width' => '415',
      ),
    ),
  ),
);
$presets['xlarge'] = array (
  'presetname' => 'xlarge',
  'actions' => 
  array (
    0 => 
    array (
      'weight' => '0',
      'module' => 'imagecache',
      'action' => 'imagecache_scale',
      'data' => 
      array (
        'width' => '628',
      ),
    ),
  ),
);
  return $presets;
}

/**
 * Helper to implementation of hook_ctools_plugin_api().
 */
function _feature_wysiwyg_ctools_plugin_api() {
  $args = func_get_args();
  $module = array_shift($args);
  $api = array_shift($args);
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => 1);
  }
}

/**
 * Helper to implementation of hook_user_default_permissions().
 */
function _feature_wysiwyg_user_default_permissions() {
  $permissions = array();

  // Exported permission: view imagecache large
  $permissions[] = array(
    'name' => 'view imagecache large',
    'roles' => array(
      '0' => 'anonymous user',
      '1' => 'authenticated user',
    ),
  );

  // Exported permission: view imagecache medium
  $permissions[] = array(
    'name' => 'view imagecache medium',
    'roles' => array(
      '0' => 'anonymous user',
      '1' => 'authenticated user',
    ),
  );

  // Exported permission: view imagecache small
  $permissions[] = array(
    'name' => 'view imagecache small',
    'roles' => array(
      '0' => 'anonymous user',
      '1' => 'authenticated user',
    ),
  );

  // Exported permission: view imagecache thumbnail
  $permissions[] = array(
    'name' => 'view imagecache thumbnail',
    'roles' => array(
      '0' => 'anonymous user',
      '1' => 'authenticated user',
    ),
  );

  // Exported permission: view imagecache tiny
  $permissions[] = array(
    'name' => 'view imagecache tiny',
    'roles' => array(
      '0' => 'anonymous user',
      '1' => 'authenticated user',
    ),
  );

  // Exported permission: view imagecache xlarge
  $permissions[] = array(
    'name' => 'view imagecache xlarge',
    'roles' => array(
      '0' => 'anonymous user',
      '1' => 'authenticated user',
    ),
  );

  return $permissions;
}

/**
 * Helper to implementation of hook_strongarm().
 */
function _feature_wysiwyg_strongarm() {
  $export = array();
  $strongarm = new stdClass;
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'image_resize_filter_image_locations_1';
  $strongarm->value = array(
    'local' => 'local',
    'remote' => 0,
  );

  $export['image_resize_filter_image_locations_1'] = $strongarm;
  $strongarm = new stdClass;
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'image_resize_filter_link_1';
  $strongarm->value = 0;

  $export['image_resize_filter_link_1'] = $strongarm;
  $strongarm = new stdClass;
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'image_resize_filter_link_class_1';
  $strongarm->value = '';

  $export['image_resize_filter_link_class_1'] = $strongarm;
  $strongarm = new stdClass;
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'image_resize_filter_link_rel_1';
  $strongarm->value = '';

  $export['image_resize_filter_link_rel_1'] = $strongarm;
  
  return $export;
}
