<?php

/**
 * Helper to implementation of hook_input_formats_default().
 */
function _feature_wysiwyg_input_formats_default() {
  $input_formats = array();

  // Exported input format: filtered_html
  $input_format =   array(
    'name' => 'Filtered HTML',
    'machine_name' => 'filtered_html',
    'roles' => array(
        '1' => 'anonymous user',
        '2' => 'authenticated user',
    ),
  
    'order' => array(
      'image_resize_filter' => array(
        // (Delta => Order) for: Image resize filter
        '0' => '-9',
      ),
      'filter' => array(
        // (Delta => Order) for: URL filter
        '2' => '0',
        // (Delta => Order) for: HTML filter
        '0' => '1',
        // (Delta => Order) for: Line break converter
        '1' => '2',
        // (Delta => Order) for: HTML corrector
        '3' => '10',
      ),
      'imagebrowser' => array(
        // (Delta => Order) for: Image Browser Images
        '0' => '10',
      ),
    ),
  
    'settings' => array(
      //Settings for: image_resize_filter
      'image_resize_filter' => array(
        // Resize images stored
        'image_resize_filter_image_locations' => array(
                'local' => 'local',
                'remote' => 0,
        ),
        // If resized, add a link to the original image.
        'image_resize_filter_link' => 0,
        // Optionally, give it the class
        'image_resize_filter_link_class' => '',
        // and/or a rel attribute
        'image_resize_filter_link_rel' => '',
      ),
      //Settings for: filter
      'filter' => array(
        // Filter HTML tags
        'filter_html' => '1',
        // Allowed HTML tags
        'allowed_html' => '<a> <em> <strong> <cite> <code> <ul> <ol> <li> <dl> <dt> <dd> <img><div> <span> <p> <br> <blockquote> <hr> <strike> <font>',
        // Display HTML help
        'filter_html_help' => 1,
        // Spam link deterrent
        'filter_html_nofollow' => 0,
      ),
    ),
  
    'extras' => array(
    ),
  );
  $input_formats['filtered_html'] = $input_format;

  // Exported input format: full_html
  $input_format =   array(
    'name' => 'Full HTML',
    'machine_name' => 'full_html',
    'roles' => array(),
  
    'order' => array(
      'filter' => array(
        // (Delta => Order) for: URL filter
        '2' => '0',
        // (Delta => Order) for: HTML corrector
        '3' => '10',
      ),
      'imagebrowser' => array(
        // (Delta => Order) for: Image Browser Images
        '0' => '10',
      ),
      'image_resize_filter' => array(
        // (Delta => Order) for: Image resize filter
        '0' => '10',
      ),
    ),
  
    'settings' => array(
      //Settings for: filter
      'filter' => array(
        // Maximum link text length
        'filter_url_length' => '',
      ),
      //Settings for: image_resize_filter
      'image_resize_filter' => array(
        // Resize images stored
        'image_resize_filter_image_locations' => '',
        // If resized, add a link to the original image.
        'image_resize_filter_link' => '',
        // Optionally, give it the class
        'image_resize_filter_link_class' => '',
        // and/or a rel attribute
        'image_resize_filter_link_rel' => '',
      ),
    ),
  
    'extras' => array(
    ),
  );
  $input_formats['full_html'] = $input_format;

  return $input_formats;
}
