$Id: README.txt,v 1.1.2.1 2010/10/18 08:18:42 amitaibu Exp $

CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Round corners and images
 * Dependencies
 * Upgrading from 1.x branch
 * Authors

 INTRODUCTION
-------------

Rounded corners is a wrapper module around the corner jQuery plugin
http://www.malsup.com/jquery/corner/

This module also provides an API function that allows adding javascript corner()
commands via PHP.

For example, copy this code snippet to your custom module (change the "foo" in
the function name to be your module's name).

  /**
   * Implementation of hook_init().
   *
   * Add round corners on the top of the message area.
   */
  function foo_corners_init() {
    // Add an example message.
    drupal_set_message(t('This message should have the top with rounded corners.'));

    $commands = array();
    $commands[] = array('selector' => '.messages');
    // Add the rounded corners.
    rounded_corners_add_corners($commands);
  }

ROUND CORNERS AND IMAGES
------------------------
Round corners can be used on images, by selecting the wrapping div and not the
<img> itself and by settings the "image wrapper" property to TRUE. for example,
consider the following HTML:

  <div class="foo">
    <img src="bar.jpg" width="10" height="10">
  </div>

And the PHP code:

  $commands = array();
  $commands[] = array(
    // Select the wrapping DIV.
    'selector' => '.foo',
    // Let the module know this is a wrapping div of an image.
    'image wrapper' => TRUE,
  );
  rounded_corners_add_corners($commands);

Setting the "image wrapper" property to TRUE will insure two things:
1) The wrapping div height and width will be adjusted according to the image
   dimensions.
2) The round corners plugin will not try to use the browser's native support for
   round corners, thus will insure the image corners are properly "hidden".

DEPENDENCIES
------------

This module depends on jQuery 1.3.2 or higher,so jquery_update 6.x-2.x branch
should be installed.

UPGRADING FROM 1.x BRANCH
-------------------------
Rounded corners is no longer working by setting a variable with the jQuery
command, but rather uses an API function that is called via code
rounded_corners_add_corners().

This variable is now obsolete and its data is not used, thus you should copy the
values and place it in your code.
If for example the first line of your configuration is
$('.foo').corner('top 5px'); then it should be replaced with:

  $commands = array();
  $commands[] = array('selector => '.foo', 'corners' => 'top', 'width' => 5);
  rounded_corners_add_corners($commands);

After moving the data from the variable to the code you may delete the variable
by executing variable_del('rounded_corners_all_pages'); or re-installing the
module.

AUTHORS
-------
* Yuval Hager (yhager) - Author of the 6.x-1.x branch.
* Amitai Burstein (Amitaibu) - Author of the 6.x-2.x branch.