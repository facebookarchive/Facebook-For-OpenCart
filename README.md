Facebook Ads Extension for OpenCart v2.0.1.1 to v2.3.0.2
====

Source code for Facebook Ads Extension for OpenCart. This is for
OpenCart v2.0.1.1 to v2.3.0.2 edition.

# Features:
  1. Auto-injection of Facebook Pixel events to all store webpages in
     traditional/response web design. Pixel events fired include:
     - PageView
     - ViewContent
     - AddToCart
     - Purchase
     - InitiateCheckout
     - Search
     - ViewCategory
     - AddToWishlist
     - CompleteRegistration
     - Lead

  2. Auto synchronize OpenCart products to Facebook catalog.

  3. Sending of Personal Identifiable Information in the pixel events in the form of email address, first name, last name and telephone. This option is disabled by default and can be change during the plugin installation stage.

# Installing Facebook Ads Extension on a running OpenCart website
Note: Prior to installing the extension, remove all existing pixel implementations from the website. Otherwise, it may cause duplicate pixel events fired.

   1. To install the Facebook Ads Extension, you can either

      - Option 1: Enable FTP option.
          - Go to the admin panel of OpenCart and click on Menu -> Settings.
          - Click on Edit button of your store.
          - Go to FTP tab and setup the details.

      - Option 2: Install Local copy OCMOD by iSenseLabs, which allows administrators to upload and install extensions without the need to enable FTP. [ Link ] ( https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=18892&filter_search=Local%20copy%20OCMOD%20by%20iSenseLabs )

   2. Click on Menu -> Extension Installer. Click on Upload button and choose the facebookadsextension.ocmod.zip file. Click on Continue button.

   3. Click on Menu -> Modifications and click on the Refresh button on the top right.

   4. [Only for OpenCart v3.x] Click on Menu -> Extensions -> Extension. Select "Modules" from the Extension type dropdown list. Locate "Facebook Ads Extension" and click on the Install button on the right (this is a green button with "+")

   5. Click on Menu -> Facebook Ads Extension.

   6. Follow the onboarding wizard instructions to select the Page and Pixel.

   7. Upon success, the plugin installation is complete.

## Building Facebook Ads Extension for OpenCart
  1. Zip up the entire folder and rename it to facebook_ads_extension.ocmod.zip

## Frequently asked questions
#### 1. What should I do if I already have the Facebook pixel installed on my OpenCart website?

  If you've already installed the Facebook pixel and are running Facebook dynamic ads, you don't have to use the Facebook Ads Extension in OpenCart.

  If you've set up the Facebook pixel but not dynamic ads, or you think you may have set up the Facebook pixel incorrectly, you should use the Facebook Ads Extension to get everything set up. Keep in mind you'll have to manually remove your existing Facebook pixel code from your website before starting, otherwise you'll have 2 versions of your pixel on your website.

  Having 2 versions on the Facebook pixel can lead to:

    - Campaign results doubling (ex: 2x the number of actual conversions)    
    - Cost per result being halved in your reports

  If you remove your existing pixel and start over with the OpenCart plugin, we recommend pausing your active campaigns first and re-installing right away. This way, you'll minimize any impact on your website Custom Audiences and conversion counts.

#### 2. I am getting an error message saying that the OpenCart installation failed or Could not upload file.

The plugin requires you to give write access to these folders on your OpenCart server. Please check with your server administrator to enable the write access rights.

    - <opencart root folder>/admin/controller
    - <opencart root folder>/admin/language
    - <opencart root folder>/admin/lanugage/en-gb
    - <opencart root folder>/admin/language/english
    - <opencart root folder>/admin/model
    - <opencart root folder>/admin/view
    - <opencart root folder>/admin/view/image
    - <opencart root folder>/admin/view/javascript
    - <opencart root folder>/admin/view/stylesheet
    - <opencart root folder>/catalog/controller
    - <opencart root folder>/catalog/view/javascript
    - <opencart root folder>/system/library

#### 3. I have installed the OpenCart plugin but I am unable to see the Facebook Ads Extension module from the menu bar.

You will need to refresh the server modifications cache for the changes to be reflected. Select Extensions and click on Modifications. Click on the refresh button, which is a blue icon, on the top right of the screen.

#### 4. I am getting an error message saying “Unable to access permissions” when I clicked on the Facebook Ads Extension.

Facebook Ads Extension is a new module added to your OpenCart platform and you will need to give permissions access for your OpenCart login account. By default, the “Administrator” user group is given full access to the Facebook Ads Extension module upon installation.

    - Select System, Settings and click Users.
    - Locate your user account and click on Edit button. Identify the user group.
    - Select System, Settings, and click User Groups.
    - Locate your user group and click on Edit button. Select the facebook/facebookadsextension in the Access and Modify permissions.
    - Click on save button.

#### 5. I see an error that says "We're unable to proceed since the selected page has already been configured for the Facebook Ads Extension."

If you see this error, it means that your Page is associated with another store. You'll need to decide whether you want to keep the Facebook Page associated with your old OpenCart store, or associate it with a different store. Here's how you can change your Page's association:

    - Select Remove connection with {your page name}.
    - Click OK.
    - You can now select your Page from the drop-down menu.
    - Select Next and continue with the normal installation process.

#### 6. Why am I seeing "There is an error with Facebook Ads Extension setup. Please click on Facebook Ads Extension, Manage Settings, go to Advanced options and click on Delete Settings to restart the setup"?

Your Facebook Ads Extension setup did not complete as planned. Please download and install the latest version of the plugin, delete the existing setup and restart the setup again.

#### 7. Will Facebook dynamic ads stay up to date with my stock changes?

Yes, we'll sync with your OpenCart site immediately when you make any modifications to the products.

#### 8. How many product catalogs can I use with this setup?

You may only use 1 product catalog.

#### 9. I want to also reach people who have not visited my website or app, what should I do?

You can drive new potential customers to visit your website, or to take a specific action, by creating an ad campaign using the website conversions objective. Learn more about how to optimize your ad sets for conversions at https://www.facebook.com/business/help/1082085278508457?helpref=faq_content

#### 10. My advertiser pays a third party to manage their OpenCart site, what should I do?

That's fine. Just be sure that the third party is added as an administrator to the advertiser's Facebook page and ad account.

## License
Facebook Ads Extension for OpenCart is Platform-licensed, as found in the LICENSE file.
