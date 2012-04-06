<?php
// $Id: template.php,v 1.17.2.1 2009/02/13 06:47:44 johnalbin Exp $

/**
 * @file
 * Contains theme override functions and preprocess functions for the theme.
 *
 * ABOUT THE TEMPLATE.PHP FILE
 *
 *   The template.php file is one of the most useful files when creating or
 *   modifying Drupal themes. You can add new regions for block content, modify
 *   or override Drupal's theme functions, intercept or make additional
 *   variables available to your theme, and create custom PHP logic. For more
 *   information, please visit the Theme Developer's Guide on Drupal.org:
 *   http://drupal.org/theme-guide
 *
 * OVERRIDING THEME FUNCTIONS
 *
 *   The Drupal theme system uses special theme functions to generate HTML
 *   output automatically. Often we wish to customize this HTML output. To do
 *   this, we have to override the theme function. You have to first find the
 *   theme function that generates the output, and then "catch" it and modify it
 *   here. The easiest way to do it is to copy the original function in its
 *   entirety and paste it here, changing the prefix from theme_ to summerreading_.
 *   For example:
 *
 *     original: theme_breadcrumb()
 *     theme override: summerreading_breadcrumb()
 *
 *   where summerreading is the name of your sub-theme. For example, the
 *   zen_classic theme would define a zen_classic_breadcrumb() function.
 *
 *   If you would like to override any of the theme functions used in Zen core,
 *   you should first look at how Zen core implements those functions:
 *     theme_breadcrumbs()      in zen/template.php
 *     theme_menu_item_link()   in zen/template.php
 *     theme_menu_local_tasks() in zen/template.php
 *
 *   For more information, please visit the Theme Developer's Guide on
 *   Drupal.org: http://drupal.org/node/173880
 *
 * CREATE OR MODIFY VARIABLES FOR YOUR THEME
 *
 *   Each tpl.php template file has several variables which hold various pieces
 *   of content. You can modify those variables (or add new ones) before they
 *   are used in the template files by using preprocess functions.
 *
 *   This makes THEME_preprocess_HOOK() functions the most powerful functions
 *   available to themers.
 *
 *   It works by having one preprocess function for each template file or its
 *   derivatives (called template suggestions). For example:
 *     THEME_preprocess_page    alters the variables for page.tpl.php
 *     THEME_preprocess_node    alters the variables for node.tpl.php or
 *                              for node-forum.tpl.php
 *     THEME_preprocess_comment alters the variables for comment.tpl.php
 *     THEME_preprocess_block   alters the variables for block.tpl.php
 *
 *   For more information on preprocess functions and template suggestions,
 *   please visit the Theme Developer's Guide on Drupal.org:
 *   http://drupal.org/node/223440
 *   and http://drupal.org/node/190815#template-suggestions
 */


/*
 * Add any conditional stylesheets you will need for this sub-theme.
 *
 * To add stylesheets that ALWAYS need to be included, you should add them to
 * your .info file instead. Only use this section if you are including
 * stylesheets based on certain conditions.
 */
/* -- Delete this line if you want to use and modify this code
// Example: optionally add a fixed width CSS file.
if (theme_get_setting('summerreading_fixed')) {
  drupal_add_css(path_to_theme() . '/layout-fixed.css', 'theme', 'all');
}
// */


/**
 * Implementation of HOOK_preprocess_page().
 */
function summerreading_preprocess_page(&$vars) {
  
	// check if logged in summer_reader has changed their password
	if (arg(0) != 'passchange') {
	  global $user;
	  if ($user->uid > 0)
	  {
	    if (md5("read") == $user->pass) {
	      drupal_goto('passchange');
	    }
	  }	
	}
	
	if ($vars['is_front']) {
    $vars['title'] = '';
  }
  
  $vars['booklist_links'] = menu_navigation_links("menu-booklists");
  $vars['library_links'] = menu_navigation_links("menu-libraries");
  
}



/**
 * Implementation of HOOK_menu_item_link()
 */
function summerreading_menu_item_link($link) {
  if (empty($link['localized_options'])) {
    $link['localized_options'] = array();
  }

  // If an item is a LOCAL TASK, render it as a tab
  if ($link['type'] & MENU_IS_LOCAL_TASK) {
	// Local task for sent messages shouldn't be displayed to users who can't write messages.
	if (!user_access('write privatemsg')) {
//	  if ($link['path'] === 'messages/sent' || $link['path'] === 'messages/list') {
	  if ($link['path'] === 'messages/sent') {
	    return '';
	  }
	}
	// But for those with access...
	else {
		// Override the original 'Write a message' prompt
		if ($link['path'] === 'messages/new') {
		  $link['title'] = '<span class="tab">' . 'Send A Message' . '</span>';
		  $link['localized_options']['html'] = TRUE;
		}
		// Hide the 'All messages' tab
		elseif ($link['path'] === 'messages/list') {
			return '';
		}
		// Uncomment this if they really do want to hide the inbox, too
		/*
		elseif ($link['path'] === 'messages') {
			return '';
		}
		*/
		else {
		  $link['title'] = '<span class="tab">' . check_plain($link['title']) . '</span>';
		  $link['localized_options']['html'] = TRUE;
		}
	}
  }

  return l($link['title'], $link['href'], $link['localized_options']);
}

/**
 * Duplicate of theme_menu_local_tasks() but adds clearfix to tabs.
 */
function summerreading_menu_local_tasks() {
// function summerreading_menu_local_tasks($link, $active = FALSE) {
  // Don't return a <li> element if $link is empty
  if ($link === '') {
    return '';
  }
  else {
	  $output = '';

	  // CTools requires a different set of local task functions.
	  if (module_exists('ctools')) {
		ctools_include('menu');
		$primary = ctools_menu_primary_local_tasks();
		$secondary = ctools_menu_secondary_local_tasks();
	  }
	  else {
		$primary = menu_primary_local_tasks();
		$secondary = menu_secondary_local_tasks();
	  }

	  if ($primary) {
		$output .= '<ul class="tabs primary clearfix">' . $primary . '</ul>';
	  }
	  if ($secondary) {
		$output .= '<ul class="tabs secondary clearfix">' . $secondary . '</ul>';
	  }

	  return $output;
   }
}

/**
 * Implementation of HOOK_theme().
 */
function summerreading_theme(&$existing, $type, $theme, $path) {
  $hooks = zen_theme($existing, $type, $theme, $path);
  // Add your theme hooks like this:
  /*
  $hooks['hook_name_here'] = array( // Details go here );
  */
  // @TODO: Needs detailed comments. Patches welcome!

  return $hooks;
}


/**
 * Override or insert variables into all templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered (name of the .tpl.php file.)
 */
/* -- Delete this line if you want to use this function
function summerreading_preprocess(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
// */

/**
 * Override or insert variables into the page templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("page" in this case.)
 */
/* -- Delete this line if you want to use this function
function summerreading_preprocess_page(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
// */

/**
 * Override or insert variables into the node templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("node" in this case.)
 */
/* -- Delete this line if you want to use this function
function summerreading_preprocess_node(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
// */

/**
 * Override or insert variables into the comment templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("comment" in this case.)
 */
/* -- Delete this line if you want to use this function
function summerreading_preprocess_comment(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
// */

/**
 * Process variables for search-theme-form.tpl.php.
 *
 * The $variables array contains the following arguments:
 * - $form
 *
 * @see search-theme-form.tpl.php
 */
function summerreading_preprocess_search_theme_form(&$variables) {
  $variables['search'] = array();
  $hidden = array();
  // Provide variables named after form keys so themers can print each element independently.
  foreach (element_children($variables['form']) as $key) {
    $type = $variables['form'][$key]['#type'];
    if ($type == 'hidden' || $type == 'token') {
      $hidden[] = drupal_render($variables['form'][$key]);
    }
    else {
      $variables['search'][$key] = drupal_render($variables['form'][$key]);
    }
  }
  // Hidden form elements have no value to themers. No need for separation.
  $variables['search']['hidden'] = implode($hidden);
  // Collect all form elements to make it easier to print the whole form.
  $variables['search_form'] = implode($variables['search']);
}