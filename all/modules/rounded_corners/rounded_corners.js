// $Id: rounded_corners.js,v 1.1.2.1 2010/10/18 08:18:42 amitaibu Exp $

/**
 * Add rounded corners.
 */
Drupal.behaviors.roundedcorners = function() {
  // Set the useNative property.
  if (Drupal.settings.rounded_corners.settings) {
    $.fn.corner.defaults.useNative = Drupal.settings.rounded_corners.settings.useNative;
  }

  // Set the height and width on the div wrapping an element.
  if (Drupal.settings.rounded_corners.wrapping_divs) {
    var wrappingDivs = Drupal.settings.rounded_corners.wrapping_divs;

    // Add the rounded corners to the page.
    for (var key in wrappingDivs) {
      // Iterate over selectors and set the width and height of the wrapping 
      // div, according to the dimensions of the image.
      $(wrappingDivs[key]['selector']).each(function() {
        var $this = $(this).children('img');

        var imgWidth = $($this).width();
        var imgHeight = $($this).height();

        var $parent = $($this).parent('div');

        $parent.width(imgWidth);
        $parent.height(imgHeight);
      });
    }
  }

  // Set the rounded corners.
  if (Drupal.settings.rounded_corners.commands) {
    var roundedCorners = Drupal.settings.rounded_corners.commands;

    // Add the rounded corners to the page.
    for (var key in roundedCorners) {
      $(roundedCorners[key]['selector']).corner(roundedCorners[key]['effect'] + ' ' + roundedCorners[key]['corners'] + ' ' + roundedCorners[key]['width'] + 'px');
    }
  }
}