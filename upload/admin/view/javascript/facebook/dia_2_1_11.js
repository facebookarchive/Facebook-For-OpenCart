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
          // clearing of the existing table oc_facebook_product
          // this table stores the assoc between OpenCart and FB product
          // this function will only be called on the initial setup to ensure
          // that this assoc table is freshly initialized
          clearAllFacebookProducts(function () {
            window.sendToFacebook('ack set catalog', message.params);
            window.facebookAdsToolboxConfig.catalogId = message.params.catalog_id;
          });
        }
      );
    };

    var clearAllFacebookProducts = function(onSuccess) {
      $.get(
        'index.php?route=extension/facebookadsextension/clearallfacebookproducts&' +
          window.facebookAdsToolboxConfig.token_string
      )
      .done(function(json) {
        if (typeof onSuccess === "function") {
          onSuccess();
        }
      })
      .fail(function(xhr, ajaxOptions, thrownError) {
        showError(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
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
        }
      );
    };

    var setPage = function(message) {
      if (!message.params.page_id || !message.params.page_token) {
        console.error('Facebook Extension Error: page id or page token not received', message.params);
        window.sendToFacebook('fail page access token', message.params);
        return;
      }  
      
      updateSettings(
        {
          'facebook_page_id': message.params.page_id,
          'facebook_page_token': message.params.page_token
        },
        function() {
          // set page is now used in 2 scenarios
          // 1. when it is a fresh FAE flow
          // 2. or when the access token is updated
          // we only want to run the product sync when it is a fresh FAE flow
          if (!window.initial_product_sync) {
            syncAllProductsUsingFeed(function() {
              window.sendToFacebook('ack page access token', message.params);
              window.facebookAdsToolboxConfig.page_id = message.params.page_id;
            });
          } else {
            // refresh the screen to update the error message
            refreshUIForDiaSettings();
          }
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

    var syncAllProducts = function(onSuccess) {
      $.get(
        'index.php?route=extension/facebookadsextension/syncallproductsusingfeed&' +
          window.facebookAdsToolboxConfig.token_string
      )
      .done(function(json) {
        if (json.total_to_be_sync === json.successfully_sync) {
          if (typeof onSuccess === "function") {
            onSuccess();
          }
          refreshUIForDiaSettings();
        } else {
          window.sendToFacebook('fail catalog', json);
        }
      })
      .fail(function(xhr, ajaxOptions, thrownError) {
        showError(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      });
    };

    var resyncAllProducts = function(resyncConfirmText) {
      if (confirm(resyncConfirmText)) {
        syncAllProducts(function() {});
      }
    };

    var syncAllProductsUsingFeed = function(onSuccess) {
      $.get(
        'index.php?route=extension/facebookadsextension/syncallproductsusingfeed&' +
          window.facebookAdsToolboxConfig.token_string
      )
      .done(function(json) {
        if (json.success === 'true') {
          window.initial_product_sync = true;
          if (typeof onSuccess === "function") {
            onSuccess();
          }
        } else {
          window.sendToFacebook('fail ack custom_feed_sync', json);
        }
        if (window.facebookAdsToolboxConfig.diaSettingId) {
          refreshUIForDiaSettings();
        }
      })
      .fail(function(xhr, ajaxOptions, thrownError) {
        if (xhr.status === 400) {
          showErrorText(xhr.statusText);
        }
        showError(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      });
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
            window.initial_product_sync = false;
            clearAllFacebookProducts(function() {
              window.sendToFacebook('ack reset');
              refreshUIForDiaSettings();
            });
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
      $("#btnResyncProducts").hide();
      $("#divErrorText").hide();
      if (window.facebookAdsToolboxConfig.diaSettingId) {
        showExistingDiaSettings(window.facebookAdsToolboxConfig.diaSettingId);
        monitorProductSyncStatus();
      } else {
        showNewDiaSettings();
      }
    };

    var monitorProductSyncStatus = function() {
      showCheckingUploadStatus();
      $.get(
        'index.php?route=extension/facebookadsextension/getinitialproductsyncstatus&' +
          window.facebookAdsToolboxConfig.token_string
      )
      .done(function(json) {
        switch (json.status) {
          case 'success':
            showUploadComplete();
            break;
          case 'in_progress':
            showUploadInProgress();
            window.setTimeout(monitorProductSyncStatus, 30000);
            break;
        }
      })
      .fail(function(xhr, ajaxOptions, thrownError) {
        if (xhr.status >= 400 && xhr.status < 500) {
          showErrorText(xhr.statusText);
          if (xhr.status === 452) {
            // specific to access token update
            window.facebookAdsToolboxConfig.tokenExpired = 'true';
          }
        }
        showError(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      });
    };

    var showNewDiaSettings = function() {
      $("#h2DiaSettingId").html('');
      $("#btnLaunchDiaWizard").html('Get Started');
      $("#btnLaunchDiaWizard").hide();
      $("#divProductSyncStatus").hide();
      $("#divProductSyncErrorText").hide();
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
      $("#divProductSyncStatus").show();
      $("#divErrorText").hide();
      $("#divSettings").show();
    };

    var showCheckingUploadStatus = function() {
      $("#divProductSyncStatusText").html('Checking on upload status...');
    };

    var showUploadComplete = function() {
      $("#btnResyncProducts").show();
      $("#divProductSyncStatus").hide();
    };

    var showUploadInProgress = function() {
      $("#divProductSyncStatusText").html('Product upload in progress...');
    };

    var showErrorText = function(errorMessage) {
      $("#divProductSyncStatus").hide();
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
          break;
        case 'set page access token':
          setPage(event.data);
          break;
        case 'reset':
          deleteSettings();
          break;
        case 'set msger chat':
          setMessenger(event.data);
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
      resyncAllProducts: resyncAllProducts,
      refreshUIForDiaSettings: refreshUIForDiaSettings,
      updateSettings: updateSettings
    };
  }());
}(window._facebookAdsExtension = window._facebookAdsExtension || {}, window, document));

_facebookAdsExtension.dia.addEventListenerForDIA(window,'message');
