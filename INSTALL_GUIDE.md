Installation Guide for Facebook Business Extension v3.0.0
====
- The latest version of the plugin can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)
- The latest version of the README can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/README.md)
- The latest version of the INSTALL_GUIDE can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/INSTALL_GUIDE.md)
- The previous version of INSTALL_GUIDE before v3.0.0 can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/INSTALL_GUIDE_2.x.x.md)
- The latest version of the FAQ can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/FAQ.md)
- The previous version of the FAQ before v3.0.0 can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/FAQ_2.x.x.md)
- For other questions or bug reporting, please create a ticket at our [github](https://github.com/facebookincubator/Facebook-For-OpenCart/issues)

====
# Pre-requisites prior to installing the plugin
  1. The plugin supports these OpenCart versions - 2.0.1.1, 2.0.2.0, 2.0.3.1, 2.1.0.1, 2.1.0.2, 2.2.0.0, 2.3.0.0, 2.3.0.1, 2.3.0.2, 3.0.2.0 and 3.0.3.1.

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

  7. Ensure that the OpenCart database user has the permission rights to CREATE_TABLE, ALTER_TABLE, SELECT, UPDATE and DELETE. Our plugin requires these permissions to create a new database table to link the association of the OpenCart products with Facebook products and store the settings of the Facebook Business Extension.

  8. Our plugin is designed based on the default OpenCart folder structure as admin, catalog and system. If your folder structure is not the same, you will need to modify the plugin yourself to make it compatible with your folder structure. The changes will include:
      - changing the folder names of the plugin.
      - Updating the method getRequiredFiles() in the admin/controller/extension/facebookadsextension.php file.
      - Updating the method getRequiredFiles() in the catalog/controller/extension/facebookeventparameters.php file.
      - Updating the install.xml file with references on admin, catalog and system.

  9. Our plugin will create the catalog feed of all your products to be uploaded to Facebook after completing the setup for Facebook pixel and catalog. Depending on your web server and database server configurations, you may experience issues with your database server if you have a large product catalog, eg more than 5000 products. You may need to increase the memory settings for your webserver and database server. Refer to the [FAQ](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/FAQ.md) for more details.

  10. Our plugin internally used Facebook Business SDK, the plugin size could exceed your pre-defined upload_max_filesize. You may need to increas the value of upload_max_filesize for your webserver in order to install the plugin.

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
      - Scroll down the list to locate Facebook Business Extension and click on Install button. [Screenshot](https://drive.google.com/open?id=1sfryAfbG9rUyF0skyZ4BI7nLma8uM1KI)
  
  3. Setup the permission rights for Facebook Business Extension if you encounter "Permission Denied". [Screenshot.](https://drive.google.com/open?id=1wgBr11M5ikAVNXtxYw0bkYMsksTGW2ri) [Video guide](https://drive.google.com/open?id=1JUwZPlUNIhFO7I8U0slbJSQNjyu4al_J)
      - Ensure you are on the latest version of the Facebook Business Extension plugin. [Latest version](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)
      - If you are on OpenCart v3.x, ensure that you have performed the additional installation step. [Video guide](https://drive.google.com/open?id=1-ljN_pNcyZBbN2LoXtxwaJEy0dfnxAum)
      - The Facebook Business Extension plugin will automatically enable the permission access for the default Administrator group. For other user groups, you will need to provide the permission access manually.
      - Follow the below steps to provide the permission access:
        - Go to the admin panel and click on Menu -> Settings -> Users -> User Groups. Locate your user group and click on the Edit button. [Screenshot](https://drive.google.com/open?id=1qNQQN4bFAk41CgW73rz6Dg5W_HIpnjMo)
        - Locate the Access and Modify permissions, extension/facebookadsextension. Ensure the permissions are selected and click on Save button. [Screenshot](https://drive.google.com/open?id=1GdwPxVE2xBz-R__1EVtsYeJylhhHMr-t)
        - Access the Facebook Business Extension to verify that you are able to view the plugin.

# Setup for Facebook business manager, page, pixel and catalog
  1. Access the Facebook Business Extension to setup business manager, page, pixel and catalog.
      - Go to the admin panel of OpenCart and click on Menu -> Facebook Business Extension -> Facebook Business Extension. [Screenshot](https://drive.google.com/open?id=1SlQlEMvn5XJ8Tk58gJffZqy7o_7Ihwhn)
      - Click on the Get Started button. [Screenshot](https://drive.google.com/open?id=11rel4BoOcxcmU_aqB6Pn-CIUwFVNTi2w)
      - Facebook login popup requests you to log in first. [Screenshot](https://drive.google.com/file/d/1ORUR8DvO3KWRI0T-aFVkXwVC8-gq36QD/view?usp=sharing)
      - Read the welcome text and click Continue button. [Screenshot](https://drive.google.com/file/d/1cGU6Gyw6aMqyI44UHkiK73ULiU1IjhUB/view?usp=sharing)
      - The Confirm Settings screen lets you configure the assets which you want to connect. [Screenshot](https://drive.google.com/file/d/1_vllIHcR30uDIUdnxqy0QmXyciX0-HDL/view?usp=sharing)
      - Select an existing Facebook Business Manager or create a new Facebook Business Manager and click on Continue button. [Screenshot](https://drive.google.com/open?id=1nU1kGUke4pHMNX_XqZfQviJAJ49sUlZ-)
      - Select an existing Facebook Page or create a new Facebook Page and click on Continue button. [Screenshot](https://drive.google.com/open?id=1OlxR6EP7usoIH_Yy-5Z_gtFeOlkjvT6M)
      - Select an existing Facebook Catalog or create a new Facebook Catalog and click on Continue. [Screenshot](https://drive.google.com/file/d/1h7jJmErWYlKthy-bXv-lBAEJVaksY0YD/view?usp=sharing)
      - Select an existing Facebook Ad Account or create a new Facebook Ad Account and click on Continue. [Screenshot](https://drive.google.com/file/d/1YzD8CoZwUiTJ6-VfrOd7xsl2I4WkNuRN/view?usp=sharing)
      - Select the Facebook pixel you wish to use for your OpenCart store website. Toggle Advanced matching if you wish to active Facebook Advanced matching and click on Continue button. [Screenshot](https://drive.google.com/open?id=1yQBJ3IvhtgH-pdVUkLfxGHidayg7NCC3)
      - Verify the Business you selected is checked by default. It'll grant OpenCart the permission to manage your business. [Screenshot](https://drive.google.com/file/d/1P68fH48uf79aBl57IqJerwT3FDCv60Gm/view?usp=sharing)
      - Click Next button to grant user permissions. [Screenshot](https://drive.google.com/open?id=120uo9sUrtdnW-HEQBY2rp6XhKn5oWfxO). You'll be asked for more permissions if on the latest version. [Screenshot](https://drive.google.com/file/d/1NfbEuZQcAmaJnty0a7tekY76fukiHfU0/view?usp=sharing)
      - Observe that the Facebook Business Extension setup is completed successfully. Click on Done. [Screenshot](https://drive.google.com/file/d/1YQYUt2cKkD3YHRwSTvWlX3-aeIpO6IcD/view?usp=sharing)
      - Close the popup, and wait for the FBE page to refresh automatically. [Screenshot](https://drive.google.com/open?id=1GMZr3YVwq6V2Wq8-DwleZq9z3ml6Ov4S)
  
  2. If you are an existing user of previous versions of Facebook Business Extension, you should see a different screen on which all of your previously connected assets were automatically populated. Click Continue button to finish the setup. [Screenshot](https://drive.google.com/open?id=1QdOM1ZdcoY8YfrJJPD6MeN76XpLRH6bm)

# Launch Management View
In Management View, you can add more features such as enable Facebook Page Shop plugin, Facebook Messenger Chat plugin and etc.
  1. Access the Facebook Business Extension Management View.
      - Ensure you are on versions 3.0.0 and above of the Facebook Business Extension plugin. [Latest version](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)
      - You must have already completed the setup for business manager, page, pixel and catalog for Facebook Business Extension. Steps [here](#setup-for-facebook-business-manager-page-pixel-and-catalog).
      - Go to the admin panel and click on Menu -> Facebook Business Extension -> Facebook Business Extension. Click on Manage Settings button. [Screenshot](https://drive.google.com/open?id=1mJu7poPn2rBtnkg2UgfSYi2cGdFDztMp)
  2. (Optional) Follow steps [here](#setup-for-facebook-page-shop) to enable Facebook Page Shop plugin.
  3. (Optional) Follow steps [here](#setup-for-facebook-messenger-chat) to enable Facebook Messenger Chat plugin.

# Setup for Facebook Page Shop
  1. Enable the Facebook Page Shop plugin.
      - In the Management View popup window, ensure you are on Home tab, locate the feature section and click on View button next to Create a Shop on Your Page. [Screenshot](https://drive.google.com/open?id=100qDRgpxA0Rr2HyV49xSBPoRUWsnxLtR)
      - In the popup, click on Add plugin button. [Screenshot](https://drive.google.com/open?id=1ivRBVCrJTu3ND-azhkdV4s-_IOsQdIvv)
      - If added successfully, the Page Shop plugin status is shown as Added. [Screenshot](https://drive.google.com/open?id=1EcaufbbkJh0dtg3lWIatA08n9IKZrn3m)
  2. Verify that your Facebook Catalog is correctly connected to your Page Shop. 
      - In the Managment View popup window, click Catalog tab, and click Open Catalog Manager button. [Screenshot](https://drive.google.com/open?id=18OCYzQMBtbQxW0II2-JtxzasA1_TD7e1)
      - In the Catalog Manager, click Settings tab, verify that your Page Shop is correctly connected. [Screenshot](https://drive.google.com/open?id=1P2LrqzLhzIBcHphNHt_L6KE-E9M4H3TT)
  3. Disable the Facebook Page Shop plugin.
      - Ensure you have enabled the Facebook Page Shop plugin, i.e. the Page Shop Plugin status is shown as Added. [Screenshot](https://drive.google.com/open?id=1EcaufbbkJh0dtg3lWIatA08n9IKZrn3m)
      - Click on View button next to Create a Shop on Your Page. [Screenshot](https://drive.google.com/open?id=12XRUxhzN4_9u3ojgZA_mHQD9LhAE17Zi)
      - In the popup, click on Delete button. [Screenshot](https://drive.google.com/open?id=1J_kOcyIWBZeO3YJZMJFMQlWui5eSn3Kq)

# Setup for Facebook Messenger chat
Currently not available in v3.0.0. Please use previous versions if you need the feature.

# Delete the existing settings for Facebook for OpenCart
  1. Delete the existing settings. [Video guide](https://drive.google.com/open?id=1_rJZrqnVQNS_dFngiCI6Jh2iaDW8tAqh) 
      - You must have already completed the setup for business manager, page, pixel and catalog for Facebook Business Extension. Steps [here](#setup-for-facebook-business-manager-page-pixel-and-catalog).
      - Go to the admin panel of OpenCart and click on Menu -> Facebook Business Extension -> Facebook Business Extension. Click on the Uninstall button. [Screenshot](https://drive.google.com/open?id=1uE7PXtt0iKca60zCSra3k2vuvoe0alji)
      - In the alert window, click on the OK button to proceed with deletion. [Screenshot](https://drive.google.com/open?id=1-1zedtOwlSG7L9a_h2hihD8dRL8cE08H)
  2. Once deleted successfully, you should see the page with 'Get Started' button. [Screenshot](https://drive.google.com/open?id=11rel4BoOcxcmU_aqB6Pn-CIUwFVNTi2w)

# Uninstall the plugin
  1. [Video guide](https://drive.google.com/open?id=1aPxqEcH1J3tT3bG0vMIC5DLnkDN7fo_d)
  
  2. You must have already installed the Facebook for OpenCart plugin on your OpenCart server.
  
  3. Go to the admin panel of OpenCart and click on Menu -> Extensions -> Modifications. [Screenshot](https://drive.google.com/open?id=1H5ppQPXnx2UYo6v82d5comDJKPu064X2)
  
  4. Locate and select the Facebook Business Extension plugin. Click on the Delete button on the top right of the screen. [Screenshot](https://drive.google.com/open?id=1cWMe0ChoDbTFm9on-9g89r7G_vZPylJP)
  
  5. Click on Ok button to delete the plugin. [Screenshot](https://drive.google.com/open?id=1swxbD99bfJxXGHfYPNYyZ3oaa6P7_rkY)
  
  6. Click on Refresh button to refresh the existing plugins on your OpenCart server. [Screenshot](https://drive.google.com/open?id=1Mfr49CzavKogSrOvZurJIvadfCwtR_6p)

# Upgrade the plugin to a later version
  1. [Video guide](https://drive.google.com/open?id=12dX2wYTcE3Y7Wf-ZBD_6X4EAZU2L-8vp)

  2. You must have already installed the Facebook Business Extension plugin on your OpenCart server.
  
  3. Delete the existing Facebook for OpenCart plugin. [Video guide](https://drive.google.com/open?id=1aPxqEcH1J3tT3bG0vMIC5DLnkDN7fo_d)

  4. Install the later version plugin. Verify that the Facebook for OpenCart version is shown as the later version. [Screenshot.](https://drive.google.com/open?id=19Nfp_1x9cQbGCk-rMmi3PkLy3NEPFHdS) [Video guide](https://drive.google.com/open?id=1V4Nu8nlmHX5ppKqsjcR-xR05Rozb7MKN)

# Cookie bar on the webstore
  1. Disable the cookie bar.
      - Ensure you are on the versions 2.0.3 and above of the Facebook Business Extension plugin. [Latest version](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)
      - Go to the admin panel of OpenCart and click on Menu -> Facebook Business Extension -> Facebook Business Extension. [Screenshot](https://drive.google.com/open?id=1xC5hQLqn-6AR7mxPME3y-safDTY-LFya)
      - Uncheck the Show cookie bar on store website option and click on Save button. [Screenshot](https://drive.google.com/open?id=1cdzTmI9pIqKx2olKku0-bjH1XMPEKcbn)

  2. Enable the cookie bar.
      - Ensure you are on the versions 2.0.3 and above of the Facebook Business Extension plugin. [Latest version](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)
      - Go to the admin panel and click on Menu -> Facebook Business Extension -> Facebook Business Extension. Click on Manage Settings button. [Screenshot](https://drive.google.com/open?id=1nUNSsphp7ID8Ma4_5ESWI8DR_eQ4-IfI)
      - Check the Show cookie bar on store website option and click on Save button. [Screenshot](https://drive.google.com/open?id=1CSeaZ0BPsue6eNKsLHXjahodXWgNh5ss)

# Product special price to be used as discount in Facebook catalog
  1. Disable the special price to be used as discount.
      - Ensure you are on the versions 2.1.11 and above of the Facebook Business Extension plugin. [Latest version](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)
      - Go to the admin panel of OpenCart and click on Menu -> Facebook Business Extension -> Facebook Business Extension. [Screenshot](https://drive.google.com/open?id=1xC5hQLqn-6AR7mxPME3y-safDTY-LFya)
      - Uncheck the Sync product special price as discount option and click on Save button. [Screenshot](https://drive.google.com/open?id=1cdzTmI9pIqKx2olKku0-bjH1XMPEKcbn)
      - Click on Resync Products to Facebook button to resync the product details to Facebook.

  2. Enable the special price to be used as discount.
      - Ensure you are on the versions 2.1.11 and above of the Facebook Business Extension plugin. [Latest version](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)
      - Go to the admin panel and click on Menu -> Facebook Business Extension -> Facebook Business Extension. Click on Manage Settings button. [Screenshot](https://drive.google.com/open?id=1nUNSsphp7ID8Ma4_5ESWI8DR_eQ4-IfI)
      - Check the Sync product special price as discount option and click on Save button. [Screenshot](https://drive.google.com/open?id=1CSeaZ0BPsue6eNKsLHXjahodXWgNh5ss)


