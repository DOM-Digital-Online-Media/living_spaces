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
      setInterval(function() {
        $.ajax({
          url: Drupal.url('living-spaces-activity/get-persistent-messages/' + settings.user.uid),
          type: 'POST',
          contentType: 'application/json; charset=utf-8',
          dataType: 'json',
          success: function success(value) {
            if (value.success) {
              if ($('div[id^=block-views-block-message-persistent]').length) {
                $('div[id^=block-views-block-message-persistent]').replaceWith(value.message);
              }
              else {
                $('div.highlighted').append(value.message);
              }
            }
          }
        });
      }, 60 * 1000);
    }
  };

})(jQuery, Drupal, drupalSettings);
