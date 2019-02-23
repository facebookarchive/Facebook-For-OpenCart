Installation Guide for Facebook Ads Extension
====
- The latest version of the plugin can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)
- The latest version of the README can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/README.md)
- The latest version of the INSTALL_GUIDE can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/INSTALL_GUIDE.md)
- The latest version of the FAQ can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/FAQ.md)
- For other questions or bug reporting, please create a ticket at our [github](https://github.com/facebookincubator/Facebook-For-OpenCart/issues)

====
# Pre-requisites prior to installing the plugin
  1. The plugin supports these OpenCart versions - 2.0.1.1, 2.0.2.0, 2.0.3.1, 2.1.0.1, 2.1.0.2, 2.2.0.0, 2.3.0.0, 2.3.0.1, 2.3.0.2 and 3.0.2.0.

  2. Remove all existing pixel implementations from the website. Duplicate pixel events may be fired if there are existing pixel implementations.

  3. Installation of the plugin is via OCMOD.
    - For OpenCart 2.x, you need to either:
      - Option 1: Enable FTP option. [Screenshot](https://drive.google.com/open?id=1TuxkIjoZgj2f3tK0ZnX1LSqlJfsEdTN_)
        - Go to the admin panel of OpenCart and click on Menu -> Settings.
        - Click on Edit button of your store.
        - Click on FTP tab and setup the FTP details.

      - Option 2: Install QuickFix: Extensions Installer issue when FTP support disabled. 
        - [Download Link](https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=18892)

    - For Opencart 3.x, there is no additional setup required to use OCMOD.

    - VQMOD installation is NOT supported.

  4. Give read+write+execute permissions to the admin, catalog and system folders and their child folders on your OpenCart web server. Our plugin will need these permissions to copy over some new files to your web server. [Screenshot](https://drive.google.com/open?id=1igyGa2mWpdjPylhiRuiD4hmK20oCAEGC)

  5. For Opencart 3.x, you need to disable the theme cache, as we are making modifications to the header.twig file. [Screenshot](https://drive.google.com/open?id=1bY-bworYxX36b88HDvFW0_32C3Wtq_Tm)
    - Go to the admin panel of OpenCart and click on Menu -> Dashboard.
    - Click on the Settings button on the top right.
    - Click on Off option for Theme and click on the Refresh button.

  6. Download the latest version of the plugin file, facebook_ads_extension.ocmod.zip.
    - You can get the latest version of the plugin from these websites:
      - [OpenCart marketplace](https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=32336)
      - [Github latest release](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)

    - Ensure that the plugin file ends with .ocmod.zip extension. [Screenshot](https://drive.google.com/open?id=19hCCtDKLY12uiKu4WOJ_KGv_5_PNlS7m)

  7. Ensure that the OpenCart database user has the permission rights to CREATE_TABLE, ALTER_TABLE, SELECT, UPDATE and DELETE. Our plugin requires these permissions to create a new database table to link the association of the OpenCart products with Facebook products and store the settings of the Facebook Ads Extension.

  8. Our plugin is designed based on the default OpenCart folder structure as admin, catalog and system. If your folder structure is not the same, you will need to modify the plugin yourself to make it compatible with your folder structure. The changes will include:
    - changing the folder names of the plugin.
    - Updating the method getRequiredFiles() in the admin/controller/extension/facebookadsextension.php file.
    - Updating the method getRequiredFiles() in the catalog/controller/extension/facebookeventparameters.php file.
    - Updating the install.xml file with references on admin, catalog and system.

  9. Our plugin will create the catalog feed of all your products to be uploaded to Facebook after completing the setup for Facebook pixel and catalog. Depending on your web server and database server configurations, you may experience issues with your database server if you have a large product catalog, eg more than 5000 products. You may need to increase the memory settings for your webserver and database server. Refer to the [FAQ](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/FAQ.md) for more details.

# Plugin installation
  1. Install the Facebook for OpenCart plugin via OCMOD. [Video guide](https://drive.google.com/open?id=1V4Nu8nlmHX5ppKqsjcR-xR05Rozb7MKN)
    - Go to the admin panel of OpenCart and click on:
      - Menu -> Extensions -> Extension Installer. (For OpenCart v2.x) [Screenshot](https://drive.google.com/open?id=18acMNnESWauvK6A7EJIeewM7TsqpAmYa)
      - Menu -> Extensions -> Installer. (For OpenCart v3.x) [Screenshot](https://drive.google.com/open?id=1by3jIljlrz7sYJAAI1KovABOayRrnHSW)
    - Click on the Upload button and select the plugin file. Click on the Continue button if required. [Screenshot](https://drive.google.com/open?id=1iOyZNFow9qUiJITH4N60heml7-lZmCnF)
    - Go to the admin panel of OpenCart and click on Menu -> Extensions -> Modifications. [Screenshot](https://drive.google.com/open?id=1H5ppQPXnx2UYo6v82d5comDJKPu064X2)
    - Click on the Refresh button. [Screenshot](https://drive.google.com/open?id=1qy-ipwK1HCk8oSnUmGuy6MCJxQdUyGfw)

  2. For OpenCart 3.x, you need to perform an additional installation step. [Video guide](https://drive.google.com/open?id=1-ljN_pNcyZBbN2LoXtxwaJEy0dfnxAum)

    - Go to the admin panel of OpenCart and click on Menu -> Extensions -> Extensions. [Screenshot](https://drive.google.com/open?id=1ftZTL2M8S11g4XKqUIESalLraltFHlC-)
    - Click on the Extension type dropdown list and select Modules. [Screenshot](https://drive.google.com/open?id=1xQ-1yp22x6khgfduPw8CrtVpIHtPrS4T)
    - Scroll down the list to locate Facebook Ads Extension and click on Install button. [Screenshot](https://drive.google.com/open?id=1sfryAfbG9rUyF0skyZ4BI7nLma8uM1KI)

# Setup for Facebook pixel, catalog and page shop
  1. Access the Facebook Ads Extension to install pixel, catalog and page shop. [Video guide](https://drive.google.com/open?id=1JPXQ0mS1pGk2Bat9RV02vYNdbRfCLP9r)
    - Go to the admin panel of OpenCart and click on Menu -> Facebook Ads Extension -> Facebook Ads Extension. [Screenshot](https://drive.google.com/open?id=1xC5hQLqn-6AR7mxPME3y-safDTY-LFya)
    - Click on the Get Started button. [Screenshot](https://drive.google.com/open?id=1NOmU1ujQS98PCrSe_lN9g4MTa-ig7CZy)
    - Read the welcome text and click on Next button. [Screenshot](https://drive.google.com/open?id=1k7eEaao8zznOHGtvc4yUs0-8w4YymzPf)
    - Select an existing Facebook Page or create a new Facebook Page and click on Next button. [Screenshot](https://drive.google.com/open?id=1hc66N_wa1GgqEP4yUgHuFxbeDXpqVl_b)
    - Select the Facebook pixel you wish to use for your OpenCart store website. Toggle Advanced matching if you wish to active Facebook Advanced matching and click on Next button. [Screenshot](https://drive.google.com/open?id=1nood2Oq1YSWdHsFXBQtF6PBTmOoy5tDr)
    - Toggle Page shop if you wish to enable the catalog on your Facebook Page. Click on Finish to complete the setup. [Screenshot](https://drive.google.com/open?id=1LqmQYAjuvgdsz6YTeRsIXTayIWYu_kQu)
    - Click on Continue button and Close button to close the popup screen. [Screenshot](https://drive.google.com/open?id=1yDn2AstNadjfCAGtcVY13mi1S0fTjwNt)
    - Observe that the Facebook Ads Extension setup is completed successfully. [Screenshot](https://drive.google.com/open?id=1QYqAsRRym3uxxZWfT-ZsQXCmAAqXShvu)

  2. Setup the permission rights for Facebook Ads Extension if you encounter "Permission Denied". [Screenshot.](https://drive.google.com/open?id=1wgBr11M5ikAVNXtxYw0bkYMsksTGW2ri) [Video guide](https://drive.google.com/open?id=1JUwZPlUNIhFO7I8U0slbJSQNjyu4al_J)
    - Ensure you are on the latest version of the Facebook Ads Extension plugin. [Latest version](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)
    - If you are on OpenCart v3.x, ensure that you have performed the additional installation step. [Video guide](https://drive.google.com/open?id=1-ljN_pNcyZBbN2LoXtxwaJEy0dfnxAum)
    - The Facebook Ads Extension plugin will automatically enable the permission access for the default Administrator group. For other user groups, you will need to provide the permission access manually.
    - Follow the below steps to provide the permission access:
      - Go to the admin panel and click on Menu -> Settings -> Users -> User Groups. Locate your user group and click on the Edit button. [Screenshot](https://drive.google.com/open?id=1qNQQN4bFAk41CgW73rz6Dg5W_HIpnjMo)
      - Locate the Access and Modify permissions, extension/facebookadsextension. Ensure the permissions are selected and click on Save button. [Screenshot](https://drive.google.com/open?id=1GdwPxVE2xBz-R__1EVtsYeJylhhHMr-t)
      - Access the Facebook Ads Extension to verify that you are able to view the plugin.

# Delete the existing settings for Facebook for OpenCart
  1. [Video guide](https://drive.google.com/open?id=1PenBy_xizQGszdiS5BrmVFPypn5HKnkL)
  
  2. You must have already completed the setup for pixel, catalog for Facebook Ads Extension.
  
  3. Go to the admin panel of OpenCart and click on Menu -> Facebook Ads Extension -> Facebook Ads Extension. Click on the Manage Settings button. [Screenshot](https://drive.google.com/open?id=1nUNSsphp7ID8Ma4_5ESWI8DR_eQ4-IfI)
  
  4. In the popup window, click on the Advanced options and click on the Delete Settings button. [Screenshot](https://drive.google.com/open?id=1MteMvqhAlnt44uowXeTlDBPM0bHT1dmB)
  
  5. Read the confirmation message and click on Confirm button to proceed with the deletion. [Screenshot](https://drive.google.com/open?id=1iwrOTZOw9xt5h_mhUSY5qi-TfkOduUht)

# Uninstall the plugin
  1. [Video guide](https://drive.google.com/open?id=1aPxqEcH1J3tT3bG0vMIC5DLnkDN7fo_d)
  
  2. You must have already installed the Facebook for OpenCart plugin on your OpenCart server.
  
  3. Go to the admin panel of OpenCart and click on Menu -> Extensions -> Modifications. [Screenshot](https://drive.google.com/open?id=1H5ppQPXnx2UYo6v82d5comDJKPu064X2)
  
  4. Locate and select the Facebook Ads Extension plugin. Click on the Delete button on the top right of the screen. [Screenshot](https://drive.google.com/open?id=1cWMe0ChoDbTFm9on-9g89r7G_vZPylJP)
  
  5. Click on Ok button to delete the plugin. [Screenshot](https://drive.google.com/open?id=1swxbD99bfJxXGHfYPNYyZ3oaa6P7_rkY)
  
  6. Click on Refresh button to refresh the existing plugins on your OpenCart server. [Screenshot](https://drive.google.com/open?id=1Mfr49CzavKogSrOvZurJIvadfCwtR_6p)

# Upgrade the plugin to a later version
  1. [Video guide](https://drive.google.com/open?id=12dX2wYTcE3Y7Wf-ZBD_6X4EAZU2L-8vp)

  2. You must have already installed the Facebook Ads Extension plugin on your OpenCart server.
  
  3. Delete the existing Facebook for OpenCart plugin. [Video guide](https://drive.google.com/open?id=1aPxqEcH1J3tT3bG0vMIC5DLnkDN7fo_d)

  4. Install the later version plugin. Verify that the Facebook for OpenCart version is shown as the later version. [Screenshot.](https://drive.google.com/open?id=19Nfp_1x9cQbGCk-rMmi3PkLy3NEPFHdS) [Video guide](https://drive.google.com/open?id=1V4Nu8nlmHX5ppKqsjcR-xR05Rozb7MKN)

# Cookie bar on the webstore
  1. Disable the cookie bar.
    - Ensure you are on the versions 2.0.3 and above of the Facebook Ads Extension plugin. [Latest version](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)
    - Go to the admin panel and click on Menu -> Facebook Ads Extension -> Facebook Ads Extension. Click on Manage Settings button. [Screenshot](https://drive.google.com/open?id=1nUNSsphp7ID8Ma4_5ESWI8DR_eQ4-IfI)
    - Uncheck the Show cookie bar on store website option. [Screenshot](https://drive.google.com/open?id=1cdzTmI9pIqKx2olKku0-bjH1XMPEKcbn)

  2. Enable the cookie bar.
    - Ensure you are on the versions 2.0.3 and above of the Facebook Ads Extension plugin. [Latest version](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)
    - Go to the admin panel and click on Menu -> Facebook Ads Extension -> Facebook Ads Extension. Click on Manage Settings button. [Screenshot](https://drive.google.com/open?id=1nUNSsphp7ID8Ma4_5ESWI8DR_eQ4-IfI)
    - Check the Show cookie bar on store website option. [Screenshot](https://drive.google.com/open?id=1CSeaZ0BPsue6eNKsLHXjahodXWgNh5ss)

# Setup for Facebook Messenger chat
  1. Enable the Facebook Messenger chat plugin. [Video guide](https://drive.google.com/open?id=1XubpAUFYw6m7lB9A8RqrdOPq3IjEQvIq)
    - Ensure you are on versions 2.1.0 and above of the Facebook Ads Extension plugin. [Latest version](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)
    - You must have already completed the setup for pixel, catalog for Facebook Ads Extension. [Video guide](https://drive.google.com/open?id=1JPXQ0mS1pGk2Bat9RV02vYNdbRfCLP9r)
    - Go to the admin panel and click on Menu -> Facebook Ads Extension -> Facebook Ads Extension. Click on Manage Settings button. [Screenshot](https://drive.google.com/open?id=1nUNSsphp7ID8Ma4_5ESWI8DR_eQ4-IfI)
    - In the popup window, locate the Messenger Chat plugin and click on View button. [Screenshot](https://drive.google.com/open?id=1Bs1bYqZFGWr2Da56vbAgcNQBsL1yd0qo)
    - Click on Add plugin button. [Screenshot](https://drive.google.com/open?id=1FOH4JgAtYUeK1vnkrhYC7TDdRwRmfTcJ)
    - Setup your language settings and click on Next button. [Screenshot](https://drive.google.com/open?id=1qtYfnjoIeLfcuQ2rxYR0hwEfbxCnXZaT)
    - Setup your appearance settings and click on Finish button. [Screenshot](https://drive.google.com/open?id=1tf70_6uqrhJyKlCE-1Od1Ozo_EwjEeqb)
    - Verify that the Messenger chat plugin has been added to your web store. [Screenshot](https://drive.google.com/open?id=1QRfvlPiFkywJEAqEIlQz0oxQTCStQMhS)

  2. Edit the Facebook Messenger chat plugin settings.
    - Ensure you have enabled the Facebook Messenger chat plugin.
    - Go to the admin panel and click on Menu -> Facebook Ads Extension -> Facebook Ads Extension. Click on Manage Settings button. [Screenshot](https://drive.google.com/open?id=1nUNSsphp7ID8Ma4_5ESWI8DR_eQ4-IfI)
    - In the popup window, locate the Messenger Chat plugin and click on View button. [Screenshot](https://drive.google.com/open?id=1Bs1bYqZFGWr2Da56vbAgcNQBsL1yd0qo)
    - CLick on Edit button. Complete your changes and click on Finish button. [Screenshot](https://drive.google.com/open?id=1GZE4EGTrUVdFLwC7FCt98S_Bzynas21a)

  3. Disable the Facebook Messenger chat plugin settings.
    - Ensure you have enabled the Facebook Messenger chat plugin.
    - Go to the admin panel and click on Menu -> Facebook Ads Extension -> Facebook Ads Extension. Click on Manage Settings button. [Screenshot](https://drive.google.com/open?id=1nUNSsphp7ID8Ma4_5ESWI8DR_eQ4-IfI)
    - In the popup window, locate the Messenger Chat plugin and click on View button. [Screenshot](https://drive.google.com/open?id=1Bs1bYqZFGWr2Da56vbAgcNQBsL1yd0qo)
    - CLick on Disable button. Complete your changes and click on Finish button. [Screenshot](https://drive.google.com/open?id=1J_kOcyIWBZeO3YJZMJFMQlWui5eSn3Kq)
