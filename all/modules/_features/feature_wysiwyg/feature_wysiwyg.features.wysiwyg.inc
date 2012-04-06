<?php

/**
 * Helper to implementation of hook_wysiwyg_defaults().
 */
function _feature_wysiwyg_wysiwyg_defaults() {
  $export = array();
  $wysiwyg = new stdClass;
  $wysiwyg->disabled = FALSE; /* Edit this to true to make a default wysiwyg disabled initially */
  $wysiwyg->api_version = 2;
  $wysiwyg->format = 1;
  $wysiwyg->editor = 'tinymce';
  $wysiwyg->settings = array(
    'default' => 1,
    'user_choose' => 1,
    'show_toggle' => 1,
    'theme' => 'advanced',
    'language' => 'en',
    'buttons' => array(
      'default' => array(
        'bold' => 1,
        'italic' => 1,
        'underline' => 1,
        'strikethrough' => 1,
        'justifyleft' => 1,
        'justifycenter' => 1,
        'justifyright' => 1,
        'justifyfull' => 1,
        'bullist' => 1,
        'numlist' => 1,
        'outdent' => 1,
        'indent' => 1,
        'undo' => 1,
        'redo' => 1,
        'link' => 1,
        'unlink' => 1,
        'blockquote' => 1,
        'cut' => 1,
        'copy' => 1,
        'paste' => 1,
      ),
      'font' => array(
        'formatselect' => 1,
      ),
      'paste' => array(
        'pasteword' => 1,
      ),
      'safari' => array(
        'safari' => 1,
      ),
      'drupal' => array(
        'imagebrowser' => 1,
        'break' => 1,
      ),
    ),
    'toolbar_loc' => 'top',
    'toolbar_align' => 'left',
    'path_loc' => 'bottom',
    'resizing' => 1,
    'verify_html' => 1,
    'preformatted' => 0,
    'convert_fonts_to_spans' => 0,
    'remove_linebreaks' => 1,
    'apply_source_formatting' => 0,
    'paste_auto_cleanup_on_paste' => 1,
    'block_formats' => 'p,h2,h3,h4,h5,h6',
    'css_setting' => 'theme',
    'css_path' => '',
    'css_classes' => '',
  );
  $wysiwyg->format = 'filtered_html';

  $export[$wysiwyg->format] = $wysiwyg;
  return $export;  $wysiwyg = new stdClass;
  $wysiwyg->disabled = FALSE; /* Edit this to true to make a default wysiwyg disabled initially */
  $wysiwyg->api_version = 2;
  $wysiwyg->format = 2;
  $wysiwyg->editor = 'tinymce';
  $wysiwyg->settings = array(
    'default' => 1,
    'user_choose' => 1,
    'show_toggle' => 1,
    'theme' => 'advanced',
    'language' => 'en',
    'buttons' => array(
      'default' => array(
        'bold' => 1,
        'italic' => 1,
        'underline' => 1,
        'strikethrough' => 1,
        'justifyleft' => 1,
        'justifycenter' => 1,
        'justifyright' => 1,
        'justifyfull' => 1,
        'bullist' => 1,
        'numlist' => 1,
        'outdent' => 1,
        'indent' => 1,
        'undo' => 1,
        'redo' => 1,
        'link' => 1,
        'unlink' => 1,
        'blockquote' => 1,
        'cut' => 1,
        'copy' => 1,
        'paste' => 1,
      ),
      'font' => array(
        'formatselect' => 1,
      ),
      'paste' => array(
        'pasteword' => 1,
      ),
      'table' => array(
        'tablecontrols' => 1,
      ),
      'media' => array(
        'media' => 1,
      ),
      'safari' => array(
        'safari' => 1,
      ),
      'drupal' => array(
        'imagebrowser' => 1,
        'break' => 1,
      ),
    ),
    'toolbar_loc' => 'top',
    'toolbar_align' => 'left',
    'path_loc' => 'bottom',
    'resizing' => 1,
    'verify_html' => 1,
    'preformatted' => 0,
    'convert_fonts_to_spans' => 0,
    'remove_linebreaks' => 1,
    'apply_source_formatting' => 0,
    'paste_auto_cleanup_on_paste' => 1,
    'block_formats' => 'p,h2,h3,h4,h5,h6',
    'css_setting' => 'theme',
    'css_path' => '',
    'css_classes' => '',
  );
  $wysiwyg->format = 'full_html';

  $export[$wysiwyg->format] = $wysiwyg;
  return $export;
}
