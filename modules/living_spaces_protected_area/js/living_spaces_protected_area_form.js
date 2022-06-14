/**
 * @file
 * Contains living_spaces_protected_area_form.js.
 */

(function ($, Drupal, drupalSettings) {

  /**
   * Adds fingerprint value as a 'browser_key' field value.
   */
  Drupal.behaviors.livingSpacesProtectedAreaForm = {
    attach: function (context, settings) {
      Fingerprint2.get(function (components) {
        var hash = Fingerprint2.x64hash128(components.map(function (pair) {
          return pair.value;
        }).join(), 31);

        $('input[name="browser_key"]').val(hash);
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
