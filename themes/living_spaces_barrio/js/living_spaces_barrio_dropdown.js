/**
 * @file
 * Contains living_spaces_barrio_dropdown.js.
 */

(function ($, Drupal, once) {

  /**
   * Additional code for bootstrap dropdowns to work with Drupal.
   */
  Drupal.behaviors.livingSpacesBarrioDropdown = {
    attach: function (context) {

      // Go through all ajax links in dropdown menus and hide dropdown on ajax.
      if (Drupal.ajax.instances instanceof Array) {
        for (const instance of Drupal.ajax.instances) {
          if (instance && instance.element
        && instance.element.classList.contains('dropdown-item')) {
            $(once('lsb-dropdown-ajax', instance.element))
              .on(instance.event, function () {
                $(this)
                  .closest('.lsb-dropdown')
                  .find('> .dropdown-toggle')
                  .dropdown('hide');
              });
          }
        }
      }
    }
  };

})(jQuery, Drupal, once);
