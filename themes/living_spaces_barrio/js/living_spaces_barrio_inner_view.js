/**
 * @file
 * Contains living_spaces_barrio_inner_view.js.
 */

(function ($, Drupal, drupalSettings) {

  /**
   * Adds view to the container.
   */
  Drupal.behaviors.livingSpacesBarrioAddInnerView = {
    attach: function (context, settings) {
      // Rebuild all ajax links after adding the view.
      $.fn.attachAjaxToDynamicLink = function () {
        var base = $(this).attr('id');
        var elementSettings = {
          base: base,
          progress : {'type': 'throbber'},
          url : $(this).attr('href'),
          event : 'click'
        };
        elementSettings.selector = '#' + base;
        $(elementSettings.selector).each(function () {
          elementSettings.element = this;
          elementSettings.base = base;
          Drupal.ajax(elementSettings);
        });
      }

      $('*[data-view-name][data-view-display]').on('show.bs.dropdown', function () {
        var $element = $(this);
        if (!$('.js-view-dom-id-' + $element.attr('data-view-name') + '-' + $element.attr('data-view-display')).length) {
          var base = $(this).attr('id');

          var elementSettings = {
            base: base,
            progress : {'type': 'none'},
            url: Drupal.url('views/ajax'),
            type: 'POST',
            event : 'click',
            dataType: 'json',
            beforeSerialize: function (element, options) {
              options.data.view_name = $element.attr('data-view-name');
              options.data.view_display_id = $element.attr('data-view-display');
              $element.find('> .dropdown-toggle').dropdown('dispose');
            },
            success: function (response) {
              if (!$('.js-view-dom-id-' + $element.attr('data-view-name') + '-' + $element.attr('data-view-display')).length && response[3] !== undefined) {
                $('#' + base + ' .dropdown-menu').replaceWith(response[3].data);

                $('.use-ajax').attachAjaxToDynamicLink();
                $element.off('click');
                $element.find('> .dropdown-toggle').dropdown('hide');
                $element.find('> .dropdown-toggle').dropdown('show');
              }
            }
          };

          elementSettings.selector = '#' + base;
          $(elementSettings.selector).each(function () {
            elementSettings.element = this;
            elementSettings.base = base;

            Drupal.ajax(elementSettings);
          });
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
