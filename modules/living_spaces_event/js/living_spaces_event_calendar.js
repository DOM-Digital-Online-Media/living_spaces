/**
 * @file
 * Fullcalendar View plugin JavaScript file.
 */

// Jquery wrapper for drupal to avoid conflicts between libraries.
(function ($, Drupal, drupalSettings) {

  var initialLocaleCode = 'en';
  // Dialog index.
  var dialogIndex = 0;
  // Dialog objects.
  var dialogs = [];
  // Date entry clicked.
  var slotDate;

  /**
   * Event render handler
   */
  function eventRender(info) {
    // Event title html markup.
    let eventTitleEle = info.el.getElementsByClassName('fc-title');
    if(eventTitleEle.length > 0) {
      eventTitleEle[0].innerHTML = info.event.title;
    }
    // Event list tile html markup.
    let eventListTitleEle = info.el.getElementsByClassName('fc-list-item-title');
    if(eventListTitleEle.length > 0) {
      if (info.event.url) {
        eventListTitleEle[0].innerHTML = '<a href="' + info.event.url + '">' + info.event.title + '</a>';
      }
      else {
        eventListTitleEle[0].innerHTML = info.event.title;
      }
    }
  }
  /**
   * Event resize handler
   */
  function eventResize(info) {
    const end = info.event.end;
    const start = info.event.start;
    let strEnd = '';
    let strStart = '';
    let viewIndex = parseInt(this.el.getAttribute("calendar-view-index"));
    let viewSettings = drupalSettings.fullCalendarView[viewIndex];
    const formatSettings = {
      month: '2-digit',
      year: 'numeric',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      timeZone: 'UTC',
      locale: 'sv-SE'
    };
    // define the end date string in 'YYYY-MM-DD' format.
    if (end) {
      // The end date of an all-day event is exclusive.
      // For example, the end of 2018-09-03
      // will appear to 2018-09-02 in the calendar.
      // So we need one day subtract
      // to ensure the day stored in Drupal
      // is the same as when it appears in
      // the calendar.
      if (end.getHours() == 0 && end.getMinutes() == 0 && end.getSeconds() == 0) {
        end.setDate(end.getDate() - 1);
      }
      // String of the end date.
      strEnd = FullCalendar.formatDate(end, formatSettings);
    }
    // define the start date string in 'YYYY-MM-DD' format.
    if (start) {
      strStart = FullCalendar.formatDate(start, formatSettings);
    }
    const title = info.event.title.replace(/(<([^>]+)>)/ig,"");;
    const msg = Drupal.t('@title end is now @event_end. Do you want to save this change?', {
      '@title': title,
      '@event_end': strEnd
    });

    if (
      viewSettings.updateConfirm === 1 &&
      !confirm(msg)
    ) {
      info.revert();
    }
    else {
      /**
       * Perform ajax call for event update in database.
       */
      jQuery
        .post(
          drupalSettings.path.baseUrl +
          "fullcalendar-view-event-update",
          {
            eid: info.event.extendedProps.eid,
            entity_type: viewSettings.entityType,
            start: strStart,
            end: strEnd,
            start_field: viewSettings.startField,
            end_field: viewSettings.endField,
            token: viewSettings.token
          }
        )
        .done(function (data) {
          if (data !== '1') {
            alert("Error: " + data);
            info.revert();
          }
        });
    }
  }

  // Day entry click call back function.
  function dayClickCallback(info) {
    slotDate = info.dateStr;
  }

  // Event click call back function.
  function eventClick(info) {
    slotDate = null;
    info.jsEvent.preventDefault();
    let thisEvent = info.event;
    let viewIndex = parseInt(this.el.getAttribute("calendar-view-index"));
    let viewSettings = drupalSettings.fullCalendarView[viewIndex];
    let des = thisEvent.extendedProps.des;
    // Show the event detail in a pop up dialog.
    if (viewSettings.dialogWindow) {
      let dataDialogOptionsDetails = {};
      if (des == '') {
        return false;
      }
      const jsFrame = new JSFrame({
        //Set the parent element to which the jsFrame is attached here.
        parentElement:info.el,
      });
      // Position offset.
      let posOffset = dialogIndex * 20;
      // Dialog options.
      let dialogOptions = JSON.parse(viewSettings.dialog_options);
      dialogOptions.left += posOffset + info.jsEvent.pageX;
      dialogOptions.top += posOffset + info.jsEvent.pageY;
      dialogOptions.title = dialogOptions.title ? dialogOptions.title : thisEvent.title.replace(/(<([^>]+)>)/ig,"");
      dialogOptions.html = des;
      //Create window
      dialogs[dialogIndex] = jsFrame.create(dialogOptions);
      dialogs[dialogIndex].show();
      dialogIndex++;
      return false;
    }

    // Open a new window to show the details of the event.
    if (thisEvent.url) {
      let eventURL = new URL(thisEvent.url, location.origin);
      if (eventURL.origin === "null") {
        // Invalid URL.
        return false;
      }
      if (viewSettings.openEntityInNewTab) {
        // Open a new window to show the details of the event.
        window.open(thisEvent.url);
        return false;
      }
      else {
        // Open in same window
        window.location.href = thisEvent.url;
        return false;
      }
    }

    return false;
  }

  // Event drop call back function.
  function eventDrop(info) {
    const end = info.event.end;
    const start = info.event.start;
    let strEnd = '';
    let strStart = '';
    let viewIndex = parseInt(this.el.getAttribute("calendar-view-index"));
    let viewSettings = drupalSettings.fullCalendarView[viewIndex];
    const formatSettings = {
      month: '2-digit',
      year: 'numeric',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      timeZone: 'UTC',
      locale: 'sv-SE'
    };
    // define the end date string in 'YYYY-MM-DD' format.
    if (end) {
      // The end date of an all-day event is exclusive.
      // For example, the end of 2018-09-03
      // will appear to 2018-09-02 in the calendar.
      // So we need one day subtract
      // to ensure the day stored in Drupal
      // is the same as when it appears in
      // the calendar.
      if (end.getHours() == 0 && end.getMinutes() == 0 && end.getSeconds() == 0) {
        end.setDate(end.getDate() - 1);
      }
      // String of the end date.
      strEnd = FullCalendar.formatDate(end, formatSettings);
    }
    // define the start date string in 'YYYY-MM-DD' format.
    if (start) {
      strStart = FullCalendar.formatDate(start, formatSettings);
    }
    const title = info.event.title.replace(/(<([^>]+)>)/ig,"");;
    const msg = Drupal.t('@title start is now @event_start and end is now @event_end - Do you want to save this change?', {
      '@title': title,
      '@event_start': strStart,
      '@event_end': strEnd
    });

    if (
      viewSettings.updateConfirm === 1 &&
      !confirm(msg)
    ) {
      info.revert();
    }
    else {
      /**
       * Perform ajax call for event update in database.
       */
      jQuery
        .post(
          drupalSettings.path.baseUrl +
          "fullcalendar-view-event-update",
          {
            eid: info.event.extendedProps.eid,
            entity_type: viewSettings.entityType,
            start: strStart,
            end: strEnd,
            start_field: viewSettings.startField,
            end_field: viewSettings.endField,
            token: viewSettings.token
          }
        )
        .done(function (data) {
          if (data !== '1') {
            alert("Error: " + data);
            info.revert();
          }
        });

    }
  }

  function datesRender (info) {
    Drupal.attachBehaviors(info.el);
  }

  function datesDestroy (info) {
    Drupal.detachBehaviors(info.el);
  }

  // Triggered after a view’s non-date-related DOM structure has been rendered.
  function viewSkeletonRender(info) {
    let viewIndex = parseInt(this.el.getAttribute("calendar-view-index"));
    let viewSettings = drupalSettings.fullCalendarView[viewIndex];
    if (viewSettings.calendar_filters) {
      var filter_count = 0;
      var hidden_filters = 0;

      $.each(viewSettings.calendar_filters, function (label, value) {
        filter_count++;

        $('#views-exposed-form-calendar-events fieldset[class*=form-item-' + label + ']').removeClass('visually-hidden');
        $('#views-exposed-form-calendar-events fieldset[id*=edit-' + label + '-wrapper]').removeClass('visually-hidden');

        $.each(value, function (index, item) {
          if ($('.view-id-calendar.view-display-id-events .fc-button-active').hasClass('fc-' + index + '-button') && !item) {
            $('#views-exposed-form-calendar-events fieldset[class*=form-item-' + label + ']').addClass('visually-hidden');
            $('#views-exposed-form-calendar-events fieldset[id*=edit-' + label + '-wrapper]').addClass('visually-hidden');

            hidden_filters++;
            return;
          }
        });
      });

      // Hide the form if there are no filters.
      var form = $('#views-exposed-form-calendar-events');
      form.removeClass('visually-hidden');
      if (filter_count === hidden_filters) {
        form.addClass('visually-hidden');
      }
    }
  }

  // Event mouse enter call back function.
  function eventMouseEnter(info) {
    slotDate = null;
    info.jsEvent.preventDefault();
    let thisEvent = info.event;
    let viewIndex = parseInt(this.el.getAttribute("calendar-view-index"));
    let viewSettings = drupalSettings.fullCalendarView[viewIndex];
    let des = thisEvent.extendedProps.des;
    // Show the event detail in a pop up dialog.
    if (viewSettings.dialogWindow) {
      let dataDialogOptionsDetails = {};
      if (des == '') {
        return false;
      }

      let el = info.el;
      const jsFrame = new JSFrame({
        parentElement: el,
      });

      let tmp = document.createElement("div");
      tmp.style.position = "absolute";
      tmp.style.left = "-100%";
      tmp.style.width = el.clientWidth;
      tmp.innerHTML = des;
      document.body.appendChild(tmp);
      let assumedHeight = tmp.getElementsByClassName('dropbutton-wrapper')[0].clientHeight;
      tmp.remove();

      // Dialog options.
      let dialogOptions = JSON.parse(viewSettings.dialog_options);
      dialogOptions.left = el.getBoundingClientRect().left;
      dialogOptions.top = info.jsEvent.pageY;
      dialogOptions.movable = false;
      dialogOptions.resizable = false;
      dialogOptions.width = el.clientWidth;
      dialogOptions.height = assumedHeight ? assumedHeight + 28 : 124;
      dialogOptions.title = dialogOptions.title ? dialogOptions.title : thisEvent.title.replace(/(<([^>]+)>)/ig, "");
      dialogOptions.html = des;

      // Close old window.
      if (dialogs[dialogIndex] !== undefined) {
        dialogs[dialogIndex].hide();
      }

      // Create window.
      dialogs[dialogIndex] = jsFrame.create(dialogOptions);
      dialogs[dialogIndex].show();

      return false;
    }
  }

  // Event mouse leave call back function.
  function eventMouseLeave(info) {
    let viewIndex = parseInt(this.el.getAttribute("calendar-view-index"));
    let viewSettings = drupalSettings.fullCalendarView[viewIndex];

    if (viewSettings.dialogWindow) {
      let el = dialogs[dialogIndex];

      if (el !== undefined) {
        $(el.htmlElement).mouseleave(function () {
          el.hide();
        });
      }
    }
  }

  // Build the calendar objects.
  function buildCalendars() {
    $('.js-drupal-fullcalendar')
      .each(function () {
        let calendarEl = this;
        let viewIndex = parseInt(calendarEl.getAttribute("calendar-view-index"));
        let viewSettings = drupalSettings.fullCalendarView[viewIndex];
        var calendarOptions = JSON.parse(viewSettings.calendar_options);
        // Bind the render event handler.
        calendarOptions.eventRender = eventRender;
        // Bind the resize event handler.
        calendarOptions.eventResize = eventResize;
        // Bind the day click handler.
        calendarOptions.dateClick = dayClickCallback;
        // Bind the event click handler.
        calendarOptions.eventClick = eventClick;
        // Bind the drop event handler.
        calendarOptions.eventDrop = eventDrop;
        // Trigger Drupal behaviors when calendar events are updated.
        calendarOptions.datesRender = datesRender;
        // Trigger Drupal behaviors when calendar events are destroyed.
        calendarOptions.datesDestroy = datesDestroy;
        // Bind the view skeleton render handler.
        calendarOptions.viewSkeletonRender = viewSkeletonRender;
        // Bind the event mouse enter handler.
        calendarOptions.eventMouseEnter = eventMouseEnter;
        // Bind the event mouse leave handler.
        calendarOptions.eventMouseLeave = eventMouseLeave;

        calendarOptions.eventSources.forEach(function (item, index) {
          calendarOptions.eventSources[index].extraParams = function () {
            let data = {
              view_name: viewSettings.view_name,
              view_display_id: viewSettings.view_display_id,
              view_args: viewSettings.view_args,
              view_path: viewSettings.view_path,
              view_dom_id: viewSettings.view_dom_id,
              view_base_path: viewSettings.view_base_path,
              pager_element: viewSettings.pager_element
            }

            // Take exposed form field values and pass as part of Ajax request to load the events.
            let exposed_form_data = new FormData($("form#views-exposed-form-".concat(viewSettings.view_name.replace(/_/g, '-'), "-").concat(viewSettings.view_display_id.replace(/_/g, '-')))[0]);
            for (const pair of exposed_form_data.entries()) {
              data[pair[0]] = pair[1];
            }
            return data;
          };
        })
        calendarOptions.loading = function (isLoading) {
          if (isLoading) {
            this.setOption('noEventsMessage', 'Loading events...');
          }
          else {
            // Restore back to original message.
            this.setOption('noEventsMessage', 'No events to display');
          }
        }
        // Language select element.
        var localeSelectorEl = document.getElementById('locale-selector-' + viewIndex);
        // Initial the calendar.
        if (calendarEl) {
          if (drupalSettings.calendar) {
            drupalSettings.calendar[viewIndex] = new FullCalendar.Calendar(calendarEl, calendarOptions);
          }
          else {
            drupalSettings.calendar = [];
            drupalSettings.calendar[viewIndex] = new FullCalendar.Calendar(calendarEl, calendarOptions);
          }
          let calendarObj = drupalSettings.calendar[viewIndex];
          calendarObj.render();
          // Language dropdown box.
          if (viewSettings.languageSelector) {
            // build the locale selector's options
            calendarObj.getAvailableLocaleCodes().forEach(function (localeCode) {
              var optionEl = document.createElement('option');
              optionEl.value = localeCode;
              optionEl.selected = localeCode == calendarOptions.locale;
              optionEl.innerText = localeCode;
              localeSelectorEl.appendChild(optionEl);
            });
            // when the selected option changes, dynamically change the calendar option
            localeSelectorEl.addEventListener('change', function () {
              if (this.value) {
                let viewIndex = parseInt(this.getAttribute("calendar-view-index"));
                drupalSettings.calendar[viewIndex].setOption('locale', this.value);
              }
            });
          }
          else if (localeSelectorEl){
            localeSelectorEl.style.display = "none";
          }

          // Double click event.
          calendarEl.addEventListener('dblclick' , function (e) {
            let viewIndex = parseInt(this.getAttribute("calendar-view-index"));
            let viewSettings = drupalSettings.fullCalendarView[viewIndex];
            // New event window can be open if following conditions match.
            // * The new event content type are specified.
            // * Allow to create a new event by double click.
            // * User has the permission to create a new event.
            // * The add form for the new event type is known.
            if (
              slotDate &&
              viewSettings.eventBundleType &&
              viewSettings.dblClickToCreate &&
              viewSettings.addForm !== ""
            ) {
              // Open a new window to create a new event (content).
              window.open(
                drupalSettings.path.baseUrl +
                viewSettings.addForm +
                "?start=" +
                slotDate +
                "&start_field=" +
                viewSettings.startField +
                "&destination=" + window.location.pathname,
                "_blank"
              );
            }

          });
        }
      });
  }

  // document.ready event does not work with BigPipe.
  // The workaround is to ckeck the document state
  // every 100 milliseconds until it is completed.
  // @see https://www.drupal.org/project/drupal/issues/2794099#comment-13274828
  var checkReadyState = setInterval(function() {
    if (
      document.readyState === "complete" &&
      $('.js-drupal-fullcalendar').length > 0
    ) {
      clearInterval(checkReadyState);
      // Build calendar objects.
      buildCalendars();
    }
  }, 100);

  // After an Ajax call, the calendar objects need to rebuild,
  // to reflect the changes, such as Ajax filter.
  $( document ).ajaxComplete(function(event, request, settings) {
    // Remove the existing calendars except updating Ajax events.
    if (
      drupalSettings.calendar &&
      settings.url !== '/fullcalendar-view-event-update'
    ) {
      // Rebuild the calendars.
      drupalSettings.calendar.forEach(function(calendar) {
        calendar.destroy();
      });
      //Re-build calendars.
      buildCalendars();
    }
  });

})(jQuery, Drupal, drupalSettings);
