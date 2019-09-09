(function ($) {
  'use strict';
  
  let lat = $('[data-lat]');
  let lng = $('[data-lng]');
  let address = $('[data-address]');
  let address2 = $('[data-address2]');
  let city = $('[data-city]');
  let state = $('[data-state]');
  let zip = $('[data-zip]');
  let country = $('[data-country]');
  let email = $('[data-email]');
  let phone = $('[data-phone]');
  let website = $('[data-website]');
  let placeID = $('[data-placeid]');
  let textLat = $('[data-lat_text]');
  let textLng = $('[data-lng_text]');
  
  function pageLoadSetLatLng() {
    textLat.val(lat.val());
    textLng.val(lng.val());
  }
  
  function mapGeoLocate() {
    $('#get_geoloc').on('click', function () {
      
      let paramAddress = {
        address:  address.val(),
        address2: address2.val(),
        city:     city.val(),
        state:    state.val(),
        zip:      zip.val(),
        country:  country.val()
      };
      
      console.log(paramAddress);
      
      $('#map-loading').addClass('show');
      $('.map-row').addClass('loading');
      $('.map-field input, .map-field.disabled input').prop('readonly', true);
      
      // Start Ajax Build
      $.ajax({
        url:      '/wp-json/locations/v1/place/',
        method:   'GET',
        data:     {
          address: paramAddress
        },
        complete: function () {
          $('#map-loading').removeClass('show');
          $('.map-row').removeClass('loading');
          $('.map-field:not(.readonly) input').prop('readonly', false);
        },
        success:  function (data) {
          
          if (!data)
            return;
          
          let lat = parseFloat(data.lat);
          let lng = parseFloat(data.lng);
          placeID = data.place_id;
          
          console.log(data);
          
          document.getElementById('lm_meta[lat_text]').value = lat;
          document.getElementById('lm_meta[lng_text]').value = lng;
          document.getElementById('lm_meta[lat]').value = lat;
          document.getElementById('lm_meta[lng]').value = lng;
          document.getElementById('lm_meta[placeID]').value = placeID;
          
          let returnData = {
            _ajax_nonce: MAP_ADMIN.nonce,
            action:      'geo_cb',
            address:     address.val() || '',
            address2:    address2.val() || '',
            city:        city.val() || '',
            state:       state.val() || '',
            zip:         zip.val() || '',
            country:     country.val() || '',
            phone:       phone.val() || '',
            website:     website.val() || '',
            placeID:     placeID || '',
            lat:         lat || '',
            lng:         lng || '',
            id:          $('#get_geoloc').data('postid')
          };
          
          // $.post(MAP_ADMIN.adminURL, returnData, function (msg) {
          //   alert('Address and Place Saved! Please be sure to hit Update if you made any other content changes.');
          // });
          
        },
        error:    function (data) {
          alert('Stopping function to grab location. There has been an error!');
        }
      });
      
    });
  }
  
  function mapAddressSave() {
    $('#save_address').on('click', function () {
      
      let returnData = {
        _ajax_nonce: MAP_ADMIN.nonce,
        action:      'geo_cb',
        address:     address.val() || '',
        address2:    address2.val() || '',
        city:        city.val() || '',
        state:       state.val() || '',
        zip:         zip.val() || '',
        lat:         lat.val() || '',
        lng:         lng.val() || '',
        email:       email.val() || '',
        phone:       phone.val() || '',
        website:     website.val() || '',
        id:          $('#get_geoloc').data('postid'),
        // country:     country.val() || '',
        placeID:     placeID.val() || ''
      };
      
      console.log(returnData);
      
      $.post(MAP_ADMIN.adminURL, returnData, function (msg) {
        alert('Address saved. If you haven\'t already, click "Get Place" to get the Place Informtaion.');
      });
      
    });
  }
  
  function mapGeoLocateReset() {
    $('#geo_reset').on('click', function () {
      
      if (!confirm('Are you sure you want to reset the geolocation?'))
        return;
      
      document.getElementById('lm_meta[lat_text]').value = '';
      document.getElementById('lm_meta[lng_text]').value = '';
      document.getElementById('lm_meta[lat]').value = '';
      document.getElementById('lm_meta[lng]').value = '';
      document.getElementById('lm_meta[placeID]').value = '';
      
      let returnData = {
        _ajax_nonce: MAP_ADMIN.nonce,
        action:      'geo_cb',
        address:     address.val() || '',
        address2:    address2.val() || '',
        city:        city.val() || '',
        state:       state.val() || '',
        zip:         zip.val() || '',
        lat:         '',
        lng:         '',
        phone:       phone.val() || '',
        website:     website.val() || '',
        id:          $('#get_geoloc').data('postid')
        // country:     country.val() || '',
        // placeID:     placeID.val() || '',
      };
      
      $.post(MAP_ADMIN.adminURL, returnData, function (msg) {
        alert('Data Deleted!');
      });
      
    });
  }
  
  function mapClearChildren(element) {
    for (let i = 0; i < element.childNodes.length; i++) {
      let e = element.childNodes[i];
      if (e.tagName) switch (e.tagName.toLowerCase()) {
        case 'input':
          switch (e.type) {
            case 'radio':
            case 'checkbox':
              e.checked = false;
              break;
            case 'button':
            case 'submit':
            case 'image':
              break;
            case 'hidden' :
            default:
              e.value = '';
              break;
          }
          break;
        case 'select':
          e.selectedIndex = 0;
          break;
        case 'textarea':
          e.innerHTML = '';
          break;
        default:
          mapClearChildren(e);
      }
    }
  }
  
  function mapFormReset() {
    $('#form-reset').on('click', function () {
      
      if (!confirm('Are you sure you want to reset the form? This is unreversable.'))
        return;
      
      mapClearChildren(document.getElementById('map-elements'));
      
      let returnData = {
        _ajax_nonce: MAP_ADMIN.nonce,
        action:      'geo_cb',
        address:     '',
        address2:    '',
        city:        '',
        state:       '',
        zip:         '',
        // country:     '',
        lat:         '',
        lng:         '',
        phone:       '',
        website:     '',
        // placeID:   "",
        id:          $('#get_geoloc').data('postid')
      };
      
      $.post(MAP_ADMIN.adminURL, returnData, function (msg) {
        alert('Data Reset! Please make sure to save the post to update all the address fields..');
      });
      
    });
  }
  
  function mapAPICheck() {
    $('#get_geoloc').on('click', function () {
      
      let address = $('[data-address]').val() || '';
      let address2 = $('[data-address2]').val() || '';
      let city = $('[data-city]').val() || '';
      let state = $('[data-state]').val() || '';
      let zip = $('[data-zip]').val() || '';
      let country = $('[data-country]').val() || '';
      let phone = $('[data-phone]').val() || '';
      let website = $('[data-website]').val() || '';
      //var placeID = $( "[data-placeID]" ).val() || "";
      
      // let addressInput = address !== '' ? address.replace(/\s/g, '+') + ',+' : '';
      // let cityInput = city !== '' ? city.replace(/\s/g, '+') + ',+' : '';
      // let stateInput = state !== '' ? state + ',+' : '';
      // let zipInput = zip !== '' ? zip + ',+' : '';
      // // let countryInput = country !== "" ? country.replace( /\s/g, "+" ) + ",+" : "";
      // let countryInput = '';
      
      // Example Data
      address = '12000 E 56th Avenue';
      city = 'Denver';
      state = 'Colorado';
      zip = '80239';
      
      
      let paramAddress = {
        address:  address,
        address2: address2,
        city:     city,
        state:    state,
        zip:      zip,
        country:  country
      };
      
      $('#map-loading').addClass('show');
      $('.map-row').addClass('loading');
      $('.map-field input, .map-field.disabled input').prop('readonly', true);
      
      // Start Ajax Build
      $.ajax({
        url:      '/wp-json/locations/v1/place/',
        method:   'GET',
        data:     {
          address: paramAddress
        },
        // dataType: 'json',
        complete: function () {
          $('#map-loading').removeClass('show');
          $('.map-row').removeClass('loading');
          $('.map-field:not(.readonly) input').prop('readonly', false);
        },
        success:  function (data) {
          console.log(data);
        },
        error:    function (data) {
          console.log(data);
        }
      });
    });
  }
  
  if ($('body').hasClass('post-type-locations')) {
    pageLoadSetLatLng();
    mapGeoLocate();
    mapAddressSave();
    mapGeoLocateReset();
    mapFormReset();
    // mapAPICheck();
  }
  
})(jQuery);
