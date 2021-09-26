<!-- Copyright (c) Facebook, Inc. and its affiliates. -->
<!-- All rights reserved. -->

<!-- This source code is licensed under the license found in the -->
<!-- LICENSE file in the root directory of this source tree. -->
<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-facebook-business" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($error_maintenance_mode) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_maintenance_mode; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-facebook-business" class="form-horizontal">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-connection" data-toggle="tab"><?php echo $tab_connection; ?></a></li>
            <li><a href="#tab-settings" data-toggle="tab"><?php echo $tab_settings; ?></a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab-connection">
              <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title"><?php echo $text_connection; ?></h3></div>
                <div class="panel-body">
                  <div id="facebook-info">
                    <img src="view/image/facebook_business/f_logo.png" alt="Facebook" title="Facebook" width="60px" class="img-responsive" /><br />
                    <h3><?php echo $text_heading; ?></h3>
                    <p><i class="fa fa-puzzle-piece"></i> <?php echo $text_info_1; ?></p>
                    <p><i class="fa fa-pie-chart"></i> <?php echo $text_info_2; ?></p>
                    <p><i class="fa fa-cart-plus"></i> <?php echo $text_info_3; ?></p>
                  </div>
                  <iframe id="fbIframe" src="<?php echo $opencart_iframe_url; ?>" width="100%" height="150" frameBorder="0"></iframe>
                </div>
              </div>
              <?php if ($access_token) { ?>
              <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title"><?php echo $text_ads_creation; ?></h3></div>
                <div class="panel-body">
                  <div class="fb-lwi-ads-creation" 
                      data-fbe-extras=
                      "{
                            'business_config' : {
                                'business': {
                                    'name':'<?php echo $business_name; ?>'
                                }
                            },
                            'setup'             : {
                                'external_business_id' : '<?php echo $external_business_id; ?>',
                                'timezone'             : '<?php echo $timezone; ?>',
                                'currency'             : '<?php echo $currency; ?>',
                                'business_vertical'    : 'ECOMMERCE'
                            },
                            'repeat'            : false
                        }"
                        data-fbe-scopes="manage_business_extension,ads_management,catalog_management"
                        data-fbe-redirect-uri='<?php echo $redirect_uri; ?>'>
                  </div>
                </div>
              </div>
              <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title"><?php echo $text_ads_insights; ?></h3></div>
                <div class="panel-body">
                  <div class="fb-lwi-ads-insights" 
                      data-fbe-extras=
                      "{
                            'business_config'   : {
                                'business' : {
                                    'name' : '<?php echo $business_name; ?>'
                                }
                            },
                            'setup'             : {
                                'external_business_id' : '<?php echo $external_business_id; ?>',
                                'timezone'             : '<?php echo $timezone; ?>',
                                'currency'             : '<?php echo $currency; ?>',
                                'business_vertical'    : 'ECOMMERCE'
                            },
                            'repeat'            : false
                      }"
                      data-fbe-scopes="manage_business_extension,ads_management,catalog_management"
                      data-fbe-redirect-uri='<?php echo $redirect_uri; ?>'>
                  </div>
                </div>
              </div>
              <?php } ?>
              <div class="text-left"><p><small><?php echo $text_plugin_version; ?></small></p></div>
            </div>
            <div class="tab-pane" id="tab-settings">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-cookie-bar"><?php echo $entry_cookie_bar; ?></label>
                <div class="col-sm-10">
                  <select name="facebook_business_cookie_bar_status" id="input-cookie-bar" class="form-control">
                    <?php if ($facebook_business_cookie_bar_status) { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-sync-specials"><span data-toggle="tooltip" data-container="#tab-settings" title="<?php echo $help_sync_specials; ?>"><?php echo $entry_sync_specials; ?></span></label>
                <div class="col-sm-10">
                  <select name="facebook_business_sync_specials_status" id="input-sync-specials" class="form-control">
                    <?php if ($facebook_business_sync_specials_status) { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>
<style>
#facebook-info h3 {
    padding-top: 10px;
    padding-bottom: 10px;
    font-weight: 700;
}
#facebook-info p {
    font-size: 16px;
}
</style>
<script type="text/javascript">
window.fbAsyncInit = function() {
    // FB JavaScript SDK configuration and setup
    FB.init({
        appId      : '<?php echo $facebook_app_id; ?>', // FB App ID
        cookie     : true,  // enable cookies to allow the server to access the session
        xfbml      : true,  // parse social plugins on this page
        version    : 'v10.0' // uses graph api version v10.0
    });
};

// Load the JavaScript SDK asynchronously
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "https://connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

(function() {
  window.facebookAdsToolboxConfig = {
    token_string: "<?php echo $token; ?>",
    error_message: ""
  };
  var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
  var messageEvent = eventMethod === "attachEvent" ? "onmessage" : "message";
  window.addEventListener(messageEvent, receiveMessage);
})();

function receiveMessage(e) {
  return new Promise(function(resolve, reject) {
    if(e.origin === "<?php echo $opencart_server_base_url; ?>" && e.data) {
      var message = JSON.parse(e.data);
      if(!message.success && message.error_message) {
        reject(new Error(message.error_message));
      } else if(message.installed) {
        // installed
        if(!message.pixel_id || !message.profiles || !message.profiles[0]) {
          var messageStr = JSON.stringify(message);
          reject(new Error(`An error occured when creating Facebook Business Extension setup. ${messageStr}`));
        } else {
          var settings = {
            facebook_pixel_id: message.pixel_id,
            facebook_page_id: message.profiles[0],
            facebook_fbe_v2_installed: message.installed,
            facebook_system_user_access_token: message.system_user_access_token,
            facebook_jssdk_version: 'v10.0',
            facebook_messenger_activated: false
          };
          resolve(settings);
        }
      } else if(message.updated) {
        // launch management view and updated configs
        var data = message.data;
        var settings = {
          updated: true,
          facebook_messenger_activated: data.messenger_chat.enabled,
          facebook_customization_locale: data.messenger_chat.default_locale
        };
        resolve(settings);
      } else {
        // uninstalled
        resolve();
      }
    }
  })
  .then(result => handleMessageReceived(result))
  .catch(error => logErrorMessage(error));
}

function handleMessageReceived(value) {
    if (!value) {
        return deleteSettings()
          .then(result => reloadCurrentPage(result));
    } else if (value.updated) {
        // management view
        return updateFacebookBusinessExtensionSettings(value);
    } else {
        // setup
        return updateFacebookBusinessExtensionSettings(value)
          .then(result => reloadCurrentPage(result));
    }
}

function updateFacebookBusinessExtensionSettings(data) {
    return new Promise(function(resolve, reject) {
        $.ajax({
            url: "index.php?route=module/facebook_business/updateSettings&token=" + window.facebookAdsToolboxConfig.token_string,
            type: "post",
            data: data,
            dataType: "json",
            success: function(json) {
                if(!json || !json.success) {
                    reject(new Error("An error occured when updating Facebook Business Extension settings."));
                } else {
                    resolve("");
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                reject(new Error(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText));
            }
        });
    });
}

function deleteSettings() {
    return new Promise(function(resolve, reject) {
        $.ajax({
          url: 'index.php?route=module/facebook_business/deleteSettings&token=' + window.facebookAdsToolboxConfig.token_string,
          type: 'post',
          success: function(json) {
            if(!json || !json.success) {
              reject(new Error("An error occured when deleting Facebook Business Extension settings."));
            } else {
              resolve("Your FBE settings are deleted.");
            }
          },
          error: function(xhr, ajaxOptions, thrownError) {
            reject(new Error(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText));
          }
        });
    });
};

function logErrorMessage(error) {
    window.facebookAdsToolboxConfig.error_message = error.message;
    alert(window.facebookAdsToolboxConfig.error_message);
}

function reloadCurrentPage(result) {
    if (result) {
        alert(result);
    }
    
    location.reload();
}
</script>