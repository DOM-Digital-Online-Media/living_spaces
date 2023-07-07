/**
 * @file
 * Contains living_spaces_activity_load.js.
 */

(function ($, Drupal, drupalSettings) {

  /**
   * Loads PDM messages via ajax.
   */
  Drupal.behaviors.livingSpacesActivityLoad = {
    attach: function (context, settings) {
      // @todo: call to the 'living_spaces_activity.get_persistent_messages' route for getting PDM messages for the current user (e.g. each 1 min).
      console.log('we in');
    }
  };

})(jQuery, Drupal, drupalSettings);
