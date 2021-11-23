(function ($, Drupal) {
  Drupal.behaviors.xLocatorMapBehaviors = {
    attach: function (context, settings) {

      const poiAll = [
        'poi.business',
        'poi.attraction',
        'poi.government',
        'poi.medical',
        'poi.park',
        'poi.place_of_worship',
        'poi.school',
        'poi.sports_complex',
      ];

      var poiObj = [
        {
          "featureType": "all",
          "stylers": [
            {
              "saturation": 0
            },
            {
              "hue": "#e7ecf0"
            }
          ]
        },
        {
          "featureType": "road",
          "stylers": [
            {
              "saturation": -70
            }
          ]
        },
        {
          "featureType": "transit",
          "stylers": [
            {
              "visibility": "off"
            }
          ]
        },
        {
          "featureType": "poi",
          "stylers": [
            {
              "visibility": "off"
            }
          ]
        },
        {
          "featureType": "water",
          "stylers": [
            {
              "visibility": "simplified"
            },
            {
              "saturation": -60
            }
          ]
        }
      ];

      for (var k = 0; k < poiAll.length; k++) {
        poiObj.push(
          {
            featureType: poiAll[k],
            stylers: [
              {
                visibility: "off"
              }
            ]
          }
        );
      }

      /**
       * @TODO Make sensitive to few maps on page.
       */
      var zoom = settings.zoom,
        centerPosition = settings.center.split(","),
        centerLat = centerPosition[0],
        centerLong = centerPosition[1],
        blockId = settings.block_id,
        wrapperId = settings.wrapper_id,
        markerList = [],
        salesRepController = settings.sales_rep_controller,
        lastInfoWindow,
        markers = [],
        oms,
        map;

      $(window).on('load', function () {
        var myCenter = new google.maps.LatLng(centerLat, centerLong);
        var mapCanvas = $('#' + blockId + ' ' + '.map--container', context)[0];
        var mapOptions = {
          center: myCenter,
          zoom: parseInt(zoom),
          mapTypeId: 'x_locator_style',
          disableDefaultUI: true,
        };

        var damonMapType = new google.maps.StyledMapType(poiObj, {
          name: 'X Locator Style'
        });
        map = new google.maps.Map(mapCanvas, mapOptions);
        map.mapTypes.set('x_locator_style', damonMapType);
        map.setMapTypeId('x_locator_style');
        animateEventsToLayouts();
        animateSearchToLayouts();

        oms = new OverlappingMarkerSpiderfier(map, {
          markersWontMove: true,
          markersWontHide: false,
          basicFormatEvents: true,
          circleSpiralSwitchover: 40,
        });
      });

      $(document, context).ready(function () {
        $(document, context).once('map-wrapper').on('click', '.hs-form-activated-btn', function () {
          openHubspotForm(this);
          actionToggleAdvancedSearchLayout('close');
        });
      });

      $('.form-header--close-form', context).on('click', function () {
        $('html', context).removeClass('mobile-forms-open');
        actionToggleLeftResultLayout('open');
        actionToggleSearchLayout('open');
        closeHubspotForm();
      });

      $('.list-block', context).on('click', function () {
        $('html').removeClass('mobile-forms-open');
        actionToggleLeftResultLayout('open');
        actionToggleSearchLayout('open');
        closeHubspotForm();
      });

      // push data for GTM dataLayer.
      if (typeof dataLayer !== 'undefined') {
        $('.container--item-phone a', context).once().on('click', function () {
          var phoneNumber = $(this).text();
          dataLayer.push({
            "event": "phoneNumberClick",
            "eventCategory": "click",
            "eventAction": "phoneNumberClick",
            "phone-number": phoneNumber
          });
        });

        $('.container--item-site a', context).once().on('click', function () {
          var site = $(this).text();
          dataLayer.push({
            "event": "siteTitleClick",
            "eventCategory": "click",
            "eventAction": "siteTitleClick",
            "site-title": site
          });
        });
      }
      /**
       * Open hubspot form.
       */
      function openHubspotForm(element) {
        var dataLocationId = $(element).attr('data-location-id');
        var mapWrapperBlock = $('#' + wrapperId, context);
        var hsFormPolicy = $('.scheduled-appointment--form .hs-form > div');
        var resultContainer = $('.left-sidebar-results--container[data-marker-id=' + dataLocationId + ']', context);
        $('span.practice-name', context).text(resultContainer.find('.container--item-title').text());
        closeLastOpenedInfoWindow(lastInfoWindow);
        $('.scheduled-appointment', context).removeClass('hidden');
        $('.map-result', context).addClass('hidden-block');
        $('.floating-panel', context).removeClass('hidden');
        $('.floating-panel--container', context).appendTo($('.floating-panel', context));
        $('#' + blockId, context).removeClass('width-fix');
        $(hsFormPolicy, context).each( function () {
          if ($(this).find('.hs_consent_privacy_policy_x').length !== 0 && !$(this).hasClass('hs-policy')) {
            $(this).addClass('hs-policy');
          }
        });
        if (mapWrapperBlock.hasClass('map-resized')) {
          mapWrapperBlock.removeClass('map-resized');
          mapWrapperBlock.addClass('show-mobile-map');
          $('#' + wrapperId + ' .list-block', context).removeClass('hidden');
          $('html', context).addClass('mobile-forms-open');
        }
        else if (mapWrapperBlock.hasClass('show-mobile-map')) {
          $('html', context).addClass('mobile-forms-open');
        }
        if (salesRepController.length > 0) {
          $.ajax({
            type: 'GET',
            url: salesRepController + '?zip=' + resultContainer.find('.zip').text(),
            success: function(response) {
              if (response.length > 0) {
                $('.map-locator-paragraph .hs_sales_rep_email input', context).val(response);
              }
            }
          });
        }
        $('.map-locator-paragraph .hs_doctor_clinic_zip_code input', context).val(resultContainer.find('.zip').text());
        $('.map-locator-paragraph .hs_doctor_phone input', context).val(resultContainer.find('.container--item-phone a').text());
        $('.map-locator-paragraph .hs_doctor_name input', context).val(resultContainer.find('.container--item-name').text());
        $('.map-locator-paragraph .hs_doctor_e_mail input', context).val(resultContainer.find('.container--item-email').text());
        $('.map-locator-paragraph .hs_doctor_clinic_name input', context).val(resultContainer.find('.container--item-title').text());

        // push data for GTM dataLayer.
        if (typeof dataLayer !== 'undefined') {
          $(".hs-form").submit(function () {
            var title = $(resultContainer).find('.container--item-title').text();
            dataLayer.push({
              "event": "scheduleAppClick",
              "eventCategory": "click",
              "eventAction": "scheduleAppClick",
              "schedule-app-title": title
            });
          });
        }
      }

      /**
       * Close hubspot form.
       */
      function closeHubspotForm() {
        $('.scheduled-appointment', context).addClass('hidden');
        $([document.documentElement, document.body]).stop().animate({
          scrollTop: $('#' + wrapperId).offset().top - 150
        }, 100);
      }

      /**
       * Provides submit for default search form.
       */
      $('.locator-search-form .search-btn', context).on('click', function () {
        hideAllMarkers();
        $(context).find('.map--preloader-map').fadeIn(500);
        $('#' + wrapperId + ' .form-submit', context).addClass('hidden');
        $('.mobile-toggle--view-all--btn', context).addClass('invisible');
        var address = $('.field-address', context).val().length;
        if (address === 0) {
          return messageToggleEvent('Field Address is required', true);
        }
        $(context).once().ajaxComplete(function () {
          if (settings.items && settings.items.length > 0) {
            markerList = [];
            actionToggleHintLayout('close');
            actionToggleAdvancedSearchLayout('close');
            ajaxPostMarkers();
            showMobileMapCallback();
            // push data for GTM dataLayer.
            if (typeof dataLayer !== 'undefined') {
              datalayerDataHelper('searchButtonClickAfter');
            }
          }
          else if (settings.items === 'empty') {
            $('#' + wrapperId, context).removeClass('show-mobile-map');
            messageToggleEvent('We could not find any locations.', true);
          }
        });

        // push data for GTM dataLayer.
        if (typeof dataLayer !== 'undefined') {
          dataLayer.push({
            "event": "searchButtonClick",
            "eventCategory": "click",
            "eventAction": "searchButtonClick",
            "search-address": $('#edit-address').val()
          });
        }
      });

      /**
       * Provides functionality for advance search.
       */
      $(document, context).ready(function() {
        var advancedSearchForm = '.map-locator-advanced-search-form';
        $('#second-search-form-adv-btn', context).once().on('click', function (e) {
          e.preventDefault();
          closeLastOpenedInfoWindow(lastInfoWindow);
          $(this).parents('.map-wrapper').first().find('.map-wrapper--advanced-form').toggleClass('active');
          closeHubspotForm();
          $('html', context).addClass('mobile-forms-open');
          var searchByActive = false;
          $(advancedSearchForm + ' .form-item-search-by', context).each( function () {
            if ($(this).find('label').hasClass('active')) {
              searchByActive = true;
            }
          });
          if (!searchByActive) {
            $(advancedSearchForm + ' .form-item-search-by', context).first().find('label').addClass('active');
          }
        });
        $(advancedSearchForm + ' .form-item-search-by label', context).on('click', function () {
          var inputValue = $(this).parent().find('.search-by-tab').val();
          $(advancedSearchForm + ' .form-item-search-by input', context).each(function() {
            if ($(this).val() !== inputValue) {
              $(this).parent().find('label').removeClass('active');
            }
            else {
              $(this).parent().find('label').addClass('active');
            }
          });
        });

        $('.close-advanced-form', context).on('click', function () {
          $(this).parents('.map-wrapper').find('.map-wrapper--advanced-form').removeClass('active');
          $('html').removeClass('mobile-forms-open');
        });

        $('.links--show-link', context).on('click', function () {
          showMapAction();
        });

        $(advancedSearchForm + ' .advanced-search-button', context).on('click', function (e) {
          e.preventDefault();
          hideAllMarkers();
          $(context).find('.map--preloader-map').fadeIn(500);
          $('#' + wrapperId + ' .form-submit', context).addClass('hidden');
          $('.mobile-toggle--view-all--btn', context).addClass('invisible');
          if ($('label[for=edit-search-by-doctor]', context).hasClass('active')) {
            var doctorState = $(advancedSearchForm + ' .advanced-map-locator-doctor-state', context).val();
            if (doctorState === '0') {
              return messageToggleEvent('Field State is required', true);
            }
          }
          if ($('label[for=edit-search-by-location]', context).hasClass('active')) {
            var locationState = $(advancedSearchForm + ' .advanced-map-locator-location-state', context).val();
            if (locationState === '0') {
              return messageToggleEvent('Field State is required', true);
            }
          }

          $(context).once().ajaxComplete(function () {
            if (settings.items && settings.items.length > 0) {
              hideAllMarkers();
              markerList = [];
              $('html', context).removeClass('mobile-forms-open');
              actionToggleHintLayout('close');
              ajaxPostMarkers();
              showMobileMapCallback();
              actionToggleAdvancedSearchLayout('close');
              // push data for GTM dataLayer.
              if (typeof dataLayer !== 'undefined') {
                datalayerDataHelper('advancedSearchButtonClickAfter');
              }
            }
            else if (settings.items === 'empty') {
              $('html', context).removeClass('mobile-forms-open');
              messageToggleEvent('We could not find any locations.', true);
            }
          });

          // push data for GTM dataLayer.
          if (typeof dataLayer !== 'undefined') {
            var location = $('#edit-location-form-city').val();
            dataLayer.push({
              "event": "advancedSearchButtonClickCity",
              "eventCategory": "click",
              "eventAction": "advancedSearchButtonClickCity",
              "advanced-search-location": location,
            });
            var zipCode = $('#edit-location-form-zipcode').val();
            dataLayer.push({
              "event": "advancedSearchButtonClickZip",
              "eventCategory": "click",
              "eventAction": "advancedSearchButtonClickZip",
              "advanced-search-zip": zipCode
            });
          }
        });
      });

      /**
       * Provides show map action on click.
       */
      function showMapAction() {
        var mapWrapperBlock = $('#' + wrapperId, context);
        mapWrapperBlock.removeClass('map-resized');
        mapWrapperBlock.addClass('show-mobile-map');
        $('#' + wrapperId + ' .list-block', context).removeClass('hidden');
        actionToggleSearchLayout('close');
        actionToggleLeftResultLayout('close');
        $([document.documentElement, document.body]).stop().animate({
          scrollTop: $('#' + wrapperId).offset().top - 150
        }, 100);
      }

      /**
       * Provides hints block animate.
       */
      function animateEventsToLayouts() {
        var layoutState = $('.action', context).find('.action--description--button').hasClass('hide-position') ? 'close' : 'open';
        $('.action', context).once().on('click', function () {
          if ($('.map-result', context).attr('animated') !== 'animated') {
            $('.map-result', context).attr('animated', 'animated');
            layoutState = layoutState === 'open' ? 'close' : 'open';
            actionToggleHintLayout(layoutState);
            setTimeout(function () {
              $('.map-result', context).removeAttr('animated');
            }, 1000);
          }
        });
      }

      /**
       * Close last opened infoWindow of Google Map.
       */
      function closeLastOpenedInfoWindow(infoWindow) {
        if (infoWindow) {
          infoWindow.close();
        }
      }

      /**
       * Provides continue with markers adding or error message.
       */
      function ajaxPostMarkers() {
        if (settings.items === 'empty') {
          $('html').removeClass('mobile-forms-open');
          $('#' + wrapperId, context).removeClass('show-mobile-map');
          var searchViewAll = $('.mobile-toggle--view-all--btn', context);
          if (!searchViewAll.hasClass('invisible')) {
            searchViewAll.addClass('invisible');
          }
          messageToggleEvent('We could not find any locations.', true);
        } else {
          actionToggleLeftResultLayout('open');
          extractMarkers(settings.items);
        }
      }

      /**
       * Show mobile search map results callback.
       */
      function showMobileMapCallback() {
        if ($(window).width() <= 768) {
          $([document.documentElement, document.body]).stop().animate({
            scrollTop: $('#' + wrapperId, context).offset().top - 150
          }, 100);
          $('.links--show-link', context).click();
        }
      }

      /**
       * Provides adding markers to map and info window.
       */
      function setMarkerToMap(itemMarkerData, marker) {
        var info;
        var id = marker.get('id');
        google.maps.event.addListener(marker, 'click', function () {
          if (lastInfoWindow) {
            lastInfoWindow.close();
          }
          $([document.documentElement, document.body]).stop().animate({
            scrollTop: $('#' + wrapperId).offset().top - 150
          }, 100);
          $('.mobile-toggle--view-all--btn', context).click();
          var infoBlock = $('div[data-entity-id=' + id + ']', context);
          info = new google.maps.InfoWindow({
            content: infoBlock.html(),
          });
          setTimeout(function () {
            marker.setPosition(marker.getPosition());
            map.panTo(marker.getPosition());
            info.open(map, marker);
          }, 500);
          lastInfoWindow = info;
          var resultContainer = $('.left-sidebar-results--container[data-marker-id=' + id + ']', context).parent();
          var container = $('.map-result', context);
          resultContainer.addClass('active');
          resultContainer.siblings().removeClass('active');
          container.stop().animate({
            scrollTop: resultContainer.offset().top - container.offset().top + container.scrollTop()
          });
        });

        $('.left-sidebar-results--container', context).on('click', function () {
          $(this).parent().addClass('active');
          $(this).parent().siblings().removeClass('active');
          var infoBlockId = $(this).attr('data-marker-id');
          closeLastOpenedInfoWindow(info);
          var infoBlock = $('div[data-entity-id=' + infoBlockId + ']', context);
          info = new google.maps.InfoWindow({
            content: infoBlock.html(),
          });
          if (marker.get('id') === infoBlockId) {
            setTimeout(function () {
              marker.setPosition(marker.getPosition());
              map.panTo(marker.getPosition());
              info.open(map, marker);
            }, 500);
            lastInfoWindow = info;
            showMobileMapCallback();
          }
        });
        delete settings.items;
      }

      /**
       * Hide all markers.
       */
      function hideAllMarkers() {
        if (markers && markers.length > 0) {
          markers.forEach(function (marker) {
            oms.removeMarker(marker);
            marker.setMap(null);
            markers = [];
          });

        }
        markerList = [];
      }

      /**
       * Select one marker function.
       */
      function actionSelectOneMarker() {
        var floatingPanel = $('.floating-panel', context),
          mapWrapper = $('#' + blockId, context),
          mapWrapperBlock = $('#' + wrapperId, context),
          mapBoxWrapper = $('.map-result', context);

        floatingPanel.addClass('hidden');
        mapWrapperBlock.addClass('map-resized');
        mapWrapper.addClass('width-fix');
        mapBoxWrapper.removeClass('hidden-block');

        $(context).on('click', '.mobile-toggle--view-all--btn', function() {
          $('.invisible', context).removeClass('invisible');
          $('.view-all-text', context).addClass('hidden');
          $('.mobile-toggle--view-all--btn', context).addClass('invisible');
        });

        $(context).on('click', '#second-search-form-adv-btn', function() {
          mapBoxWrapper.addClass('hidden-block');
          floatingPanel.removeClass('hidden');
          $('.floating-panel--container', context).appendTo($('.floating-panel', context));
          mapWrapper.removeClass('width-fix');
          mapWrapperBlock.removeClass('map-resized');
          actionToggleAdvancedSearchLayout('open');
          hideAllMarkers();
        });
      }

      /**
       * Provides build marker with needed data.
       */
      function extractMarkers(items) {
        markerList = [];
        var searchViewAll = $('.mobile-toggle--view-all--btn', context);
        if (items.length > 0) {
          items.forEach(function(item, i) {
            if (item.lat.length > 0) {
              var latlng = {
                lat: parseFloat(item.lat),
                lng: parseFloat(item.lng),
              };
              var itemMarkerData = {
                index: i,
                drName: item.name,
                drZip: item.zip,
                practice: item.practice,
                drPhone: item.phone,
                drSite: item.site,
                id: item.id,
                schedule_appointment: item.schedule_appointment,
                type: item.type,
                internalIcon: item.pin_internal_icon_url,
              };

              var icon = {
                url: item.pin_icon_url,
                size: new google.maps.Size(30, 44),
                scaledSize: new google.maps.Size(30, 44),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(20, 32),
              };

              const marker = new google.maps.Marker({
                position: latlng,
                animation: google.maps.Animation.DROP,
                icon: icon,
              });
              marker.set("id", item.id);
              markerList.push(marker);
              markers.push(marker);
              setMarkerToMap(itemMarkerData, marker);
              oms.addMarker(marker);
            }
          });
          actionSelectOneMarker();
          autoCenter(markerList);

          if (items.length < 3) {
            if (!searchViewAll.hasClass('invisible')) {
              searchViewAll.addClass('invisible');
            }
          }
          else if (searchViewAll.hasClass('invisible')) {
            searchViewAll.removeClass('invisible');
          }
        }
        else {
          if (!searchViewAll.hasClass('invisible')) {
            searchViewAll.addClass('invisible');
          }
        }
      }

      /**
       * Provides funtion for autocenter map beetwen few markers.
       */
      function autoCenter(markerList) {
        var timeout;
        markerList.forEach(function(value, key) {
          timeout = key * 200;
          setTimeout(function () {
            value.setMap(map);
          }, timeout);
        });
        const bounds = new google.maps.LatLngBounds();
        var i;
        var j;
        for (i = 0; i < markerList.length; i++) {
          for (j = i + 1; j < markerList.length; j++) {
            if (
              markerList[i].position.lat().toFixed(3) === markerList[j].position.lat().toFixed(3) &&
              markerList[i].position.lng().toFixed(3) === markerList[j].position.lng().toFixed(3)
            ) {
              markerList.splice(j, 1);
            }
          }
        }

        $.each(markerList, function (index, marker) {
          bounds.extend(marker.position);
        });
        setTimeout(function () {
          map.fitBounds(bounds);
          if (markerList.length <= 2) {
            map.setZoom(10);
          }
        }, 1000);

        setTimeout(function () {
          $('#' + wrapperId + ' .form-submit', context).removeClass('hidden');
          $(context).find('.map--preloader-map').fadeOut(500);
        }, timeout);
      }

      /**
       * Advance seach layout changing.
       */
      function actionToggleAdvancedSearchLayout(action) {
        var layout = $('.map-wrapper--advanced-form', context);
        switch (action) {
          case "open":
            layout.addClass('active');
            break;
          case 'close':
            layout.removeClass('active');
            break;
          default:
            layout.toggleClass('active');
            break;
        }
      }

      /**
       * Provides result layout changing.
       */
      function actionToggleLeftResultLayout(action) {
        var mapResultBox = $('.map-result', context),
          mapBlock = $('#' + blockId, context),
          mapWrapperBlock = $('#' + wrapperId, context),
          formContainer = $('.floating-panel--container', context);

        switch (action) {
          case 'open':
            mapResultBox.removeClass('hidden-block');
            mapBlock.addClass('width-fix');
            mapWrapperBlock.addClass('map-resized');
            mapWrapperBlock.removeClass('show-mobile-map');
            $('html', context).removeClass('mobile-forms-open');
            $('#' + wrapperId + ' .list-block', context).addClass('hidden');
            formContainer.appendTo($('.map-result .mobile-toggle--form--search-form', context));
            break;
          case 'close':
            mapResultBox.addClass('hidden-block');
            $('.floating-panel', context).removeClass('hidden');
            setTimeout(function () {
              formContainer.appendTo($('.floating-panel', context));
            }, 1000);
            mapBlock.removeClass('width-fix');
            mapWrapperBlock.removeClass('map-resized');
            break;
        }
      }

      /**
       * Hints layout changing.
       */
      function actionToggleHintLayout(action) {
        var layout = $('.hints-layout', context),
          layoutInner = $('.hints-layout > div', context),
          layoutHide = $('.layout-to-hide', context),
          layoutShow = $('.layout-to-show', context),
          hintsLayout = $('.action--description--button', context);

        closeLastOpenedInfoWindow(lastInfoWindow);
        switch (action) {
          case "open":
            layout.addClass('mobile-hidden');
            layout.animate({height: 175}, 400);
            layoutInner.animate({height: 175}, 400);
            layoutHide.addClass('hidden-block');
            layoutShow.removeClass('hidden-block');
            hintsLayout.removeClass('hide-position');
            actionToggleAdvancedSearchLayout('close');
            actionToggleLeftResultLayout('close');
            closeHubspotForm();
            break;

          case 'close':
            layout.animate({height: 30}, 400);
            layoutInner.animate({height: 30}, 400);
            layoutShow.addClass('hidden-block');
            layoutHide.removeClass('hidden-block');
            hintsLayout.addClass('hide-position');
            if (markerList.length > 0) {
              actionToggleLeftResultLayout('open');
            }
            break;
        }
      }

      /**
       * Provides hints block animate.
       */
      function animateSearchToLayouts() {
        var layoutState = $('.mobile-actions', context).find('.mobile-actions--action-button').hasClass('hide-position') ? 'close' : 'open';
        $('.mobile-actions', context).once().on('click', function() {
          layoutState = layoutState === 'open' ? 'close' : 'open';
          actionToggleSearchLayout(layoutState);
        });
      }

      /**
       * Hints layout changing.
       */
      function actionToggleSearchLayout(action) {
        var layout = $('.floating-panel--container--search-form', context),
          layoutInner = $('.floating-panel--container--search-form > div', context),
          layoutHide = $('.floating-panel .layout-to-hide', context),
          layoutShow = $('.floating-panel .layout-to-show', context),
          searchLayout = $('.mobile-actions--action-button', context);

        switch (action) {
          case "open":
            layout.addClass('show');
            layout.slideDown('slow');
            layoutInner.slideDown('slow');
            layoutHide.addClass('hidden-block');
            layoutShow.removeClass('hidden-block');
            searchLayout.removeClass('hide-position');
            break;

          case 'close':
            layout.slideUp('slow');
            layoutInner.slideUp('slow');
            layoutHide.removeClass('hidden-block');
            layoutShow.addClass('hidden-block');
            searchLayout.addClass('hide-position');
            break;
        }
      }

      /**
       * Provides error message on page.
       */
      function messageToggleEvent(message, error) {
        $(context).find('.map--preloader-map').fadeOut(500);
        var messageContainer = $('.map-wrapper--message', context);
        if (error) {
          messageContainer.addClass('error-message');
          delete settings.items;
        }
        $('#' + wrapperId + ' .form-submit', context).removeClass('hidden');
        messageContainer.text(message);
        messageContainer.fadeIn(300);

        setTimeout(function() {
          messageContainer.fadeOut(300);
        }, 4000);
        messageContainer.click(function () {
          $(this).fadeOut(300);
        });
      }

      /**
       * Returns data for GTM dataLayer.
       */
      function datalayerDataHelper(event) {
        $('.left-sidebar-results--container').each(function () {
          var title = $(this).find('.container--item-title').text();
          dataLayer.push({
            "event": event,
            "eventCategory": "click",
            "eventAction": event,
            "search-data": title
          });
        });
      }
    }
  };
}(jQuery, Drupal));
