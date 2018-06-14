// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

; (function(facebookAdsExtension, window, document, undefined) {
  var facebookPixel =
    facebookAdsExtension.facebookPixel =
    facebookAdsExtension.facebookPixel || (function () {
    var init = function(facebook_pixel_id, pii, params) {
      fbq('init', facebook_pixel_id, pii, params);
      fbq('track', 'PageView');
    };

    var firePixel = function(facebook_pixel_event_params) {
      event_name = facebook_pixel_event_params.event_name;
      delete facebook_pixel_event_params.event_name;
      track_param =
        facebook_pixel_event_params.is_custom_event ? 'trackCustom' : 'track';
      delete facebook_pixel_event_params.is_custom_event;
      fbq(track_param, event_name, facebook_pixel_event_params);
    };

    return {
      init: init,
      firePixel: firePixel
    };
  }());
}(window._facebookAdsExtension = window._facebookAdsExtension || {}, window, document));

(function () {
  function fireProductInfoEvent(eventName, productID, quantity) {
    jQuery.get(
      'index.php?route=extension/facebookproduct/getproductinfoforfacebookpixel',
      {
        event_name: eventName,
        product_id: productID,
        quantity: quantity,
      },
      function (json) {
        if (json.facebook_pixel_event_params_FAE) {
          _facebookAdsExtension.facebookPixel.firePixel(
            json.facebook_pixel_event_params_FAE);
        }
      }
    );
  }

  // catalog/view/javascript/common.js has a cart variable which is used by
  // various product listing pages to add product to cart directly.
  // We will like to modify and inject AddToCart event in the cart.add() method.
  // However, OpenCart's modification system does not support js and css files
  // as they are not accessible from the front end.
  // https://github.com/vqmod/vqmod/wiki/About-vQmod
  // Instead, we modify the existing cart.add method and fire off addToCart.
  // A ajax call is fire to the URl endpoint /getproductinfoforfacebookpixel
  // to get the price and name of the product
  var oldCartAdd = cart.add;
  cart.add = function(productID, quantity) {
    fireProductInfoEvent('AddToCart', productID, quantity);
    oldCartAdd.apply(oldCartAdd, [productID, quantity]);
  };

  // adopting the same cart.add strategy for wishlist.add
  var oldWishlist = wishlist.add;
  wishlist.add = function(productID) {
    fireProductInfoEvent('AddToWishlist', productID);
    oldWishlist.apply(oldWishlist, [productID]);
  };

  jQuery(function ($) {
    $('#button-cart').on('click', function() {
      var quantity = Number(
        $("input[name*='quantity'], input[id*='quantity']").first().val()
      );
      quantity = isNaN(quantity) || quantity <= 0 ? 1 : quantity;
      var productID = $('#fbProductID').val();
      fireProductInfoEvent('AddToCart', productID, quantity);
    });
  });
})();
