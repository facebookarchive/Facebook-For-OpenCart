// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

; (function(facebookAdsExtension, window, document, undefined) {
  var dia = facebookAdsExtension.dia = facebookAdsExtension.dia || (function () {
    var launchDiaWizard = function() {
      // checks if there is an existing DIA wizard popup. If so, close it
      if (window.sendToFacebook) {
        window.sendToFacebook('close popup');
      }

      window.sendToFacebook = openPopup();    
      window.diaConfig = { 'clientSetup': window.facebookAdsToolboxConfig };
    };

    var openPopup = function() {
      var width = 1153;
      var height = 808;
      var topPos = screen.height/2 - height/2;
      var leftPos = screen.width/2 - width/2;
      window.originParam = window.location.protocol + '//' + window.location.host + '/admin';
      var popupUrl;
      if(window.facebookAdsToolboxConfig.popupOrigin.includes('staticxx')) {
        window.facebookAdsToolboxConfig.popupOrigin = 'https://www.facebook.com/';
      }
      window.facebookAdsToolboxConfig.popupOrigin = prepend_protocol(
        window.facebookAdsToolboxConfig.popupOrigin
      );
      popupUrl = window.facebookAdsToolboxConfig.popupOrigin;
      var path = '/ads/dia';

      if (window.facebookAdsToolboxConfig.debug_url) {
        console.log(window.facebookAdsToolboxConfig);
      }

      var page = window.open(
        popupUrl + '/login.php?display=popup&next=' + encodeURIComponent(popupUrl + path + '?origin=' + window.originParam + '&merchant_settings_id=' + window.facebookAdsToolboxConfig.diaSettingId),
        'DiaWizard',
        [
          'width=' + width,
          'height=' + height,
          'top=' + topPos,
          'left=' + leftPos
        ].join(',')
      );

      return function (type, params) {
        if (type === 'close popup') {
          page.close();
        } else {
          page.postMessage({
            type: type,
            params: params
          }, popupUrl);
        }
      };    
    };

    var prepend_protocol = function(url) {
      // Preprend https if the url begis with //www.
      if (url.indexOf('//www.') === 0) {
        url = 'https:' + url;
      }
      return url;
    };

    var updateSettings = function(data, onSuccess) {
      $.ajax({
        url: 'index.php?route=extension/facebookadsextension/updatesettings&' +
          window.facebookAdsToolboxConfig.token_string,
        type: 'post',
        data: data,
        dataType: 'json',
        success: function(json) {
          if (json.success === 'true') {
            if (typeof onSuccess === "function") {
              onSuccess();
            }
          } else {
            showError('Error updating DIA settings');
          }
        },
        error: function(xhr, ajaxOptions, thrownError) {
          showError(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
      });  
    };

    var showError = function(errorMessage) {
      console.log(errorMessage);
    };

    var setCatalog = function(message) {
      if (!message.params.catalog_id) {
        console.error('Facebook Extension Error: catalog id not received', message.params);
        showError('Facebook Extension Error: catalog id not received');
        window.sendToFacebook('fail set catalog', message.params);
        return;
      }     

      updateSettings(
        {'facebook_catalog_id': message.params.catalog_id},
        function() {
          window.sendToFacebook('ack set catalog', message.params);
          window.facebookAdsToolboxConfig.catalogId = message.params.catalog_id;
        }
      );
    };

    var setFeedMigrated = function(message) {
      if (!message.params.hasOwnProperty('feed_migrated')) {
        console.error('Facebook Extension Error: feed migrated not received', message.params);
        showError('Facebook Extension Error: feed migrated not received');
        window.sendToFacebook('fail set feed migrated', message.params);
        return;
      }     

      updateSettings(
        {'facebook_feed_migrated': message.params.feed_migrated},
        function() {
          window.sendToFacebook('ack set feed migrated', message.params);
          window.facebookAdsToolboxConfig.feedPrepared.feedMigrated = message.params.feed_migrated;
        }
      );
    }

    var genFeed = function(message) {
      $.get(window.facebookAdsToolboxConfig.feedPrepared.feedUrl + 'Now')
      .done(function(json) {
        window.sendToFacebook('ack feed', message.params);
      })
      .fail(function(xhr, ajaxOptions, thronwError){
        window.sendToFacebook('fail feed', message.params);
        showError(thronwError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      });
    };

    var setPixel = function(message) {
      if (!message.params.pixel_id) {
        console.error('Facebook Ads Extension Error: pixel id not received', message.params);
        showError('Facebook Extension Error: pixel id not received');
        window.sendToFacebook('fail set pixel', message.params);
        return;
      }

      updateSettings({
        'facebook_pixel_id': message.params.pixel_id,
        'facebook_pixel_use_pii': message.params.pixel_use_pii},
        function() {
          window.sendToFacebook('ack set pixel', message.params);
          window.facebookAdsToolboxConfig.pixel.pixelId = message.params.pixel_id;
        }
      );
    };

    var setMerchantSettings = function(message) {
      if (!message.params.setting_id) {
        console.error('Facebook Extension Error: merchant id not received', message.params);
        window.sendToFacebook('fail set merchant settings', message.params);
        return;
      }  
      
      window.facebookAdsToolboxConfig.diaSettingId = message.params.setting_id;
      updateSettings(
        {'facebook_dia_setting_id': message.params.setting_id},
        function() {
          window.sendToFacebook('ack set merchant settings', message.params);
          window.facebookAdsToolboxConfig.diaSettingId = message.params.setting_id;
          refreshUIForDiaSettings();
        }
      );
    };

    var setMessenger = function(message) {
      updateSettings(
        {
          'facebook_page_id': message.params.page_id,
          'facebook_messenger_activated': message.params.is_messenger_chat_plugin_enabled,
          'facebook_jssdk_version': message.params.facebook_jssdk_version,
          'facebook_customization': message.params.customization
        },
        function() {
          // messenager would only have 2 options, only toggling on and off
          window.sendToFacebook('ack msger chat', message.params);

        }
      );
    };

    var deleteSettings = function() {
      $.ajax({
        url: 'index.php?route=extension/facebookadsextension/deletesettings&' +
          window.facebookAdsToolboxConfig.token_string,
        type: 'post',
        success: function(json) {
          if (json.success === 'true') {
            window.facebookAdsToolboxConfig.diaSettingId = '';
            window.facebookAdsToolboxConfig.pixel.pixelId = '';
            window.sendToFacebook('ack reset');
            refreshUIForDiaSettings();
          } else {
            window.sendToFacebook('fail reset');
            showError('Error deleting DIA settings');
          }
        },
        error: function(xhr, ajaxOptions, thrownError) {
          showError(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
      });
    };

    var refreshUIForDiaSettings = function() {
      $("#divErrorText").hide();
      if (window.facebookAdsToolboxConfig.diaSettingId) {
        showExistingDiaSettings(window.facebookAdsToolboxConfig.diaSettingId);
      } else {
        showNewDiaSettings();
      }
    };

    var showNewDiaSettings = function() {
      $("#h2DiaSettingId").html('');
      $("#btnLaunchDiaWizard").html('Get Started');
      $("#btnLaunchDiaWizard").hide();
      $("#divSettings").hide();
      $.get(
        'index.php?route=extension/facebookadsextension/iswritableproductfeedfolderavailable&' +
          window.facebookAdsToolboxConfig.token_string
      )
      .done(function(json) {
        if (json.available) {
          $("#btnLaunchDiaWizard").show();
        }
      })
      .fail(function(xhr, ajaxOptions, thrownError) {
        if (xhr.status === 400) {
          showErrorText(xhr.statusText);
        }
        showError(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      });
    };

    var showExistingDiaSettings = function(dia_setting_id) {
      $("#h2DiaSettingId").html('Your Facebook Store ID: ' + dia_setting_id);
      $("#btnLaunchDiaWizard").html('Manage Settings');
      $("#divErrorText").hide();
      $("#divSettings").show();
    };

    var showErrorText = function(errorMessage) {
      $("#divErrorText").show();
      $("#divErrorText").html(errorMessage);
    };

    var iFrameListener = function(event) {
      // Fix for web.facebook.com
      const origin = event.origin || event.originalEvent.origin;
      if (origin != window.facebookAdsToolboxConfig.popupOrigin &&
        urlFromSameDomain(origin, window.facebookAdsToolboxConfig.popupOrigin)) {
        window.facebookAdsToolboxConfig.popupOrigin = origin;
      }
      switch (event.data.type) {
        case 'get dia settings':
          window.sendToFacebook('dia settings', window.diaConfig);
          break; 
        case 'set merchant settings':
          setMerchantSettings(event.data);
          break;
        case 'set catalog':
          setCatalog(event.data);
          break;
        case 'set pixel':
          setPixel(event.data);
          break;
        case 'gen feed':
          genFeed(event.data);
          break;
        case 'set page access token':
          break;
        case 'reset':
          deleteSettings();
          break;
        case 'set msger chat':
          setMessenger(event.data);
          break;
        case 'set feed migrated':
          setFeedMigrated(event.data);
          break;
      }
    };

    var urlFromSameDomain = function(url1, url2) {
      if (!url1.startsWith('http') || !url2.startsWith('http')) {
        return false;
      }
      var u1 = parseURL(url1);
      var u2 = parseURL(url2);
      var u1host = u1.host.replace(/^\w+\./, 'www.');
      var u2host = u2.host.replace(/^\w+\./, 'www.');
      return u1.protocol === u2.protocol && u1host === u2host;
    };

    function parseURL(url) {
      var parser = document.createElement('a');
      parser.href = url;
      return parser;
    }

    var addEventListenerForDIA = function (obj,evt) {
      if ('addEventListener' in obj) {
        obj.addEventListener(evt, iFrameListener, false);
      } else if ('attachEvent' in obj) {//IE
        obj.attachEvent('on' + evt, iFrameListener);
      }
    };

    return {
      launchDiaWizard: launchDiaWizard,
      addEventListenerForDIA: addEventListenerForDIA,
      refreshUIForDiaSettings: refreshUIForDiaSettings,
      updateSettings: updateSettings
    };
  }());
}(window._facebookAdsExtension = window._facebookAdsExtension || {}, window, document));

_facebookAdsExtension.dia.addEventListenerForDIA(window,'message');
