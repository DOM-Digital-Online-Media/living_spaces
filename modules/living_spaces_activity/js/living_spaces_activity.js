/**
 * @file
 * Contains living_spaces_activity.js.
 */

(function ($, Drupal, drupalSettings) {

  /**
   * Provides nodejs callback for updating message counter.
   */
  Drupal.Nodejs.callbacks.livingSpacesActivity = {
    callback: function (message) {
      if ('living_spaces_activity:' + drupalSettings.user.uid == message.channel) {
        $('#space-activity-notifications .dropdown-menu').removeClass('js-view-dom-id-message-user_notifications').empty();
        $('#space-activity-notifications .dropdown-toggle.show').dropdown('toggle');
        $('.notification-counter').text(message.count);
      }
    }
  };

})(jQuery, Drupal, drupalSettings);
