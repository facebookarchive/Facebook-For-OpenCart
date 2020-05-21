<!-- Copyright 2017-present, Facebook, Inc.  -->
<!-- All rights reserved. -->

<!-- This source code is licensed under the license found in the -->
<!-- LICENSE file in the root directory of this source tree. -->

<link href="view/stylesheet/facebook/dia.css" type="text/css" rel="stylesheet" />
<script>
window.fbAsyncInit = function() {
    // FB JavaScript SDK configuration and setup
    FB.init({
        appId: "<?php echo $opencart_facebook_app_id; ?>", // FB App ID (replaced by OpenCart's appId once completed app review)
        cookie: true, // enable cookies to allow the server to access the session
        xfbml: true, // parse social plugins on this page
        version: "v5.0" // use graph api version 2.8
    });
};

// Load the JavaScript SDK asynchronously
(function(d, s, id) {
    var js,
        fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s);
    js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
})(document, "script", "facebook-jssdk");

(function() {
  window.facebookAdsToolboxConfig = {
    token_string: "<?php echo $token_string; ?>",
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
          const messageStr = JSON.stringify(message);
          reject(new Error(`An error occured when creating Facebook Business Extension setup. ${messageStr}`));
        } else {
          var settingsKeyValueData = {
            facebook_pixel_id: message.pixel_id,
            facebook_page_id: message.profiles[0],
            facebook_fbe_v2_installed: message.installed
          };
          resolve(settingsKeyValueData);
        }
      } else {
        // uninstalled
        resolve();
      }
    }
  })
  .then(result => handleMessageReceived(result))
  .then(result => reloadCurrentPage(result))
  .catch(error => logErrorMessage(error))
}

function handleMessageReceived(value) {
  if(!value) {
    return deleteSettings();
  } else {
    return updateFacebookBusinessExtensionSettings(value);
  }
}

function launchFBEManagementView() {
  FB.ui(
    {
      display: "popup",
      method: "facebook_business_extension",
      external_business_id: "<?php echo $external_business_id; ?>"
    },
    function(response) {}
  );
}

function updateFacebookBusinessExtensionSettings(data) {
  return new Promise(function(resolve, reject) {
    $.ajax({
      url: "index.php?route=extension/facebookadsextension/updatesettings&" +
        window.facebookAdsToolboxConfig.token_string,
      type: "post",
      data: data,
      dataType: "json",
      success: function(json) {
        if(!json || !json.success) {
          reject(new Error("An error occured when updating Facebook Business Extension settings."));
        } else {
          resolve(json);
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
      url: 'index.php?route=extension/facebookadsextension/deletesettings&' +
        window.facebookAdsToolboxConfig.token_string,
      type: 'post',
      success: function(json) {
        if(!json || !json.success) {
          reject(new Error("An error occured when deleting Facebook Business Extension settings."));
        } else {
          resolve(json);
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
  if(result.success) {
    location.reload();
  }
}
</script>

<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($plugin_upgrade_message) { ?>
      <div class="alert alert-info"><i class="fa fa-exclamation-circle"></i>
        <?php echo $plugin_upgrade_message; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php } ?>
    <?php if ($download_log_file_error_warning) { ?>
      <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $download_log_file_error_warning; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php } ?>
    <?php if ($plugin_code_injection_error_messages) { ?>
      <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $plugin_code_injection_error_messages; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php } ?>
    <?php if ($plugin_feed_migrated_and_website_in_maintenance_message) { ?>
      <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $plugin_feed_migrated_and_website_in_maintenance_message; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-body">
        <div id="facebook-header">
          <table><tbody>
            <tr><td><i class="logo"></i></td>
            <td><span class="title"><?php echo $heading_title; ?></span></td></tr>
          </tbody></table>
        </div>
        <div class="dia-flow-container">
          <div class="version">
            Plugin Version: <?php echo $plugin_version; ?>
          </div>        
          <h1><?php echo $sub_heading_title; ?></h1>
          <h2><?php echo $body_text; ?></h2>
          <h2 id="h2DiaSettingId">
          </h2>
          <?php if ($facebook_fbe_v2_installed) { ?>
            <div>
              <button
                type="button"
                class="blue"
                onClick="launchFBEManagementView()">
                Manage Settings
              </button>
            </div>
          <?php } ?>
          <iframe src="<?php echo $opencart_iframe_url; ?>" width="300" height="150" frameBorder="0"></iframe>
          <div class="download">
            <a class="download" href="<?php echo $download_log_link; ?>">
              <?php echo $download_log_file_text; ?>
            </a>
          </div>
        </div>
      </div>
      <?php if ($facebook_fbe_v2_installed) { ?>
        <div class="panel-body" id="divSettings">
          <div class="container-fluid">
            <div class="pull-right">
              <button title="Save" class="btn btn-primary" id="buttonSave"><i class="fa fa-save"></i></button>
            </div>
          </div>
          <div class="container-fluid">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $sub_heading_settings; ?></h3>
              </div>
              <div class="panel-body">
                <form class="form-horizontal">
                  <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo $enable_cookie_bar_text; ?></label>
                    <div class="checkbox">
                      <input
                        type="checkbox"
                        id="checkboxEnableCookieBar"
                        class="form-control"
                        <?php echo $checked_enable_cookie_bar; ?> >
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo $enable_special_price_text; ?></label>
                    <div class="checkbox">
                      <input
                        type="checkbox"
                        id="checkboxEnableSpecialPrice"
                        class="form-control"
                        <?php echo $checked_enable_special_price; ?> >
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>
</div>
<script type="text/javascript">
  $(function () {
    $("#buttonSave").on('click', function() {
      settingsKeyValueData = {
        '<?php echo $enable_cookie_bar_key; ?>': $("#checkboxEnableCookieBar").prop('checked'),
        '<?php echo $enable_special_price_key; ?>': $("#checkboxEnableSpecialPrice").prop('checked')
      };
      updateFacebookBusinessExtensionSettings(settingsKeyValueData)
      .then(result => alert("<?php echo $alert_settings_saved; ?>"))
      .catch(error => logErrorMessage(error));
    });
  });
</script>
<?php echo $footer; ?>
