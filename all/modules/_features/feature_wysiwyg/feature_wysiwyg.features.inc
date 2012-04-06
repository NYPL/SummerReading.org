<?php

/**
 * Implementation of hook_wysiwyg_defaults().
 */
function feature_wysiwyg_wysiwyg_defaults() {
  module_load_include('inc', 'feature_wysiwyg', 'feature_wysiwyg.features.wysiwyg');
  $args = func_get_args();
  return call_user_func_array('_feature_wysiwyg_wysiwyg_defaults', $args);
}

/**
 * Implementation of hook_imagecache_default_presets().
 */
function feature_wysiwyg_imagecache_default_presets() {
  module_load_include('inc', 'feature_wysiwyg', 'feature_wysiwyg.defaults');
  $args = func_get_args();
  return call_user_func_array('_feature_wysiwyg_imagecache_default_presets', $args);
}

/**
 * Implementation of hook_ctools_plugin_api().
 */
function feature_wysiwyg_ctools_plugin_api() {
  module_load_include('inc', 'feature_wysiwyg', 'feature_wysiwyg.defaults');
  $args = func_get_args();
  return call_user_func_array('_feature_wysiwyg_ctools_plugin_api', $args);
}

/**
 * Implementation of hook_input_formats_default().
 */
function feature_wysiwyg_input_formats_default() {
  module_load_include('inc', 'feature_wysiwyg', 'feature_wysiwyg.features.input_formats');
  $args = func_get_args();
  return call_user_func_array('_feature_wysiwyg_input_formats_default', $args);
}

/**
 * Implementation of hook_user_default_permissions().
 */
function feature_wysiwyg_user_default_permissions() {
  module_load_include('inc', 'feature_wysiwyg', 'feature_wysiwyg.defaults');
  $args = func_get_args();
  return call_user_func_array('_feature_wysiwyg_user_default_permissions', $args);
}

/**
 * Implementation of hook_strongarm().
 */
function feature_wysiwyg_strongarm() {
  module_load_include('inc', 'feature_wysiwyg', 'feature_wysiwyg.defaults');
  $args = func_get_args();
  return call_user_func_array('_feature_wysiwyg_strongarm', $args);
}
