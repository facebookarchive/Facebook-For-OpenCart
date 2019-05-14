<!-- Copyright 2017-present, Facebook, Inc.  -->
<!-- All rights reserved. -->

<!-- This source code is licensed under the license found in the -->
<!-- LICENSE file in the root directory of this source tree. -->

<script src="view/javascript/facebook/dia.js" type="text/javascript"></script>
<link href="view/stylesheet/facebook/dia.css" type="text/css" rel="stylesheet" />
<script>
  (function () {
    var fb_url = 'www.facebook.com';
    var debug_url = '<?php echo $debug_url; ?>';
    if (debug_url) {
      fb_url = debug_url;
    }

    window.facebookAdsToolboxConfig = {
      hasGzipSupport: '<?php echo $has_gzip_support; ?>',
      enabledPlugins: ['MESSENGER_CHAT'],
      popupOrigin: 'https://' + fb_url,
      feedWasDisabled: 'true',
      platform: 'OpenCart',
      pixel: {
        pixelId: '<?php echo $facebook_pixel_id; ?>',
        advanced_matching_supported: true
      },
      diaSettingId: '<?php echo $facebook_dia_setting_id; ?>',
      store: {
        baseUrl: window.location.protocol + '//' + window.location.host,
        baseCurrency: '<?php echo $base_currency; ?>',
        canSetupShop: true,
        timezoneId: '<?php echo $time_zone_id; ?>',
        storeName: '<?php echo $store_name; ?>',
        version: '<?php echo $opencart_version; ?>',
        php_version: '<?php echo $php_version; ?>',
        plugin_version: '<?php echo $plugin_version; ?>'
      },
      feed: {
        totalVisibleProducts: <?php echo $total_visible_products; ?>
      },
      feedPrepared: {
        feedUrl: '',
        feedPingUrl: '',
        samples: <?php echo $sample_feed; ?>
      },
      debug_url: debug_url,
      token_string: '<?php echo= $token_string; ?>',
    };

    window.initial_product_sync = <?php echo $initial_product_sync; ?>;
  })();
</script>

<?php echo $header . $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <div class="alert alert-danger" id="divErrorText">
    </div>
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
    <div class="panel panel-default">
      <div class="panel-body">
        <div id="facebook-header">
          <table><tbody>
            <tr><td><i class="logo"></i></td>
            <td><span class="title"><?php echo= $heading_title; ?></span></td></tr>
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
          <div>
            <button
              type="button"
              class="blue"
              onClick="_facebookAdsExtension.dia.launchDiaWizard()"
              id="btnLaunchDiaWizard">
            </button>
          </div>
          <div id="divProductSyncStatus" class="product-sync-status">
            <div class="product-sync-status">
              <img
                src="view/image/facebook/loadingicon.gif"
                width="20"
                height="20"/>
            </div>
            <div
              id="divProductSyncStatusText"
              class="product-sync-status-dotted-underline">
            </div>
            <div class="product-sync-status-tooltiptext">
              The product sync status check will be performed every 30 secs.
            </div>
          </div>
          <div>
            <button
              type="button"
              class="blue"
              onClick="_facebookAdsExtension.dia.resyncAllProducts(
                '<?php echo $resync_confirm_text; ?>')"
              id="btnResyncProducts">
              <?php echo $resync_text; ?>
            </button>
          </div>
          <h2>
            <input
              type="checkbox"
              onchange="_facebookAdsExtension.dia.setEnableCookieBar(this.checked)"
              <?php echo $checked_enable_cookie_bar; ?> >
              <?php echo $enable_cookie_bar_text; ?>
          </h2>
          <div class="download">
            <a class="download" href="<?php echo $download_log_link; ?>">
              <?php echo $download_log_file_text; ?>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  $(function () {
    _facebookAdsExtension.dia.refreshUIForDiaSettings();
  });
</script>
<?php echo $footer; ?>
