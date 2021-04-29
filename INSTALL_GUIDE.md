# Installation Guide for Facebook Business Extension v4.0.0
  - The latest version of the plugin can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)
  - The latest version of the README can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/README.md)
  - The latest version of the INSTALL_GUIDE can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/INSTALL_GUIDE.md)
  - The previous version of INSTALL_GUIDE before v4.0.0 can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/INSTALL_GUIDE_3.x.x.md)
  - The previous version of INSTALL_GUIDE before v3.0.0 can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/INSTALL_GUIDE_2.x.x.md)
  - The latest version of the FAQ can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/FAQ.md)
  - The previous version of the FAQ before v4.0.0 can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/FAQ_3.x.x.md)
  - The previous version of the FAQ before v3.0.0 can be found at [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/FAQ_2.x.x.md)
  - For other questions or bug reporting **regarding the OpenCart plugin**, please open a ticket with us at our [helpdesk](https://marketinsg.zendesk.com/') or open a new issue on [github](https://github.com/facebookincubator/)
  - For Facebook related issues or enquiries, please open a ticket with Facebook Business Support at [Facebook Business Help Centre](https://www.facebook.com/business/help/support)

# Pre-requisites prior to installing the plugin
  1. The plugin supports these OpenCart versions - 2.0.x.x to 2.2.x.x, 2.3.x.x, 3.x.x.x and above.

  2. Remove all existing pixel implementations from the website. Duplicate pixel events may be fired if there are existing pixel implementations.

  3. If you were previously using the Facebook Business Extension plugin version 3.1.2 and below, please follow the Uninstall Guides below to uninstall the existing plugin before attempting to install the new plugin:
      - For Facebook Business Extension version 2.x.x, please click [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/INSTALL_GUIDE_2.x.x.md)
      - For Facebook Business Extension version 3.x.x, please click [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/INSTALL_GUIDE_3.x.x.md)

  4. Ensure that your web server uses PHP version 7.2 and above. This is because the plugin uses Facebook Business SDK internally and it requires PHP version 7.2 and above.
  
  5. Download the latest version of the plugin file, ```Facebook_Business_Extension.ocmod.zip```.
      - You can get the latest version of the plugin from these websites:
        - [OpenCart marketplace](https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=32336)
        - [Github latest release](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)

      - Ensure that the plugin file ends with ```.ocmod.zip``` extension. [Screenshot](https://drive.google.com/file/d/1fHks0Ab0Wlo42xGqaK09QjHi8QKK-aIi/view?usp=sharing)
  
  6. Installation of the plugin is via the OpenCart Extension Installer for all OpenCart versions. For OpenCart 2.0.x to 2.2.x, the plugin uses OCMOD (OpenCart Modifications) as well.
      - For OpenCart 2.0.x to 2.2.x:
        - You need to either
          - Option 1: Enable FTP option. [Screenshot](https://drive.google.com/file/d/1-QS-vZtpZgun5lJXnn5H0UGriAm_YujF/view?usp=sharing)
            - Go to the admin panel of OpenCart and click on Settings.
            - Click on Edit button of your store.
            - Click on FTP tab and setup your FTP details.
          - Option 2: Install QuickFix: Extensions Installer issue when FTP support disabled. 
            - [Download Link](https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=18892)
        - Installation of the plugin through manual upload of the plugin's files onto your web server is not recommended as the OCMOD script will not be installed correctly.
        - vQmod installation is NOT supported.
          
      - For OpenCart 2.3.x or OpenCart 3.x:
        - There is no additional setup required as the extension uses OpenCart Events instead of OCMOD.
        - Installation of the plugin through manual upload of the plugin's files onto your web server is supported.

  7. Our plugin uses the default OpenCart folder structure, i.e. admin, catalog and system. If your folder structure is not the same **and** you are performing the installation of the plugin through manual upload, you will need to change the directory names of the plugin accordingly or ensure that you are uploading into the correct directories.

  8. Our plugin will generate the catalog feed of all your products and upload it to Facebook after the setup for Facebook Pixel and Catalog has been completed. Depending on your web server and database server configurations, you may experience issues if you have a large product catalog, e.g. more than 5000 products. You may also need to increase the memory settings for your web server and database server. Please kindly refer to the [FAQ](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/FAQ.md) for more details.

  9. Since our plugin uses the Facebook Business SDK internally, the plugin file size might exceed your pre-defined PHP ```upload_max_filesize``` value. You may need to increase the value of ```upload_max_filesize``` for your web server in order to install the plugin.

# Plugin installation
  1. Download the Facebook for OpenCart plugin from either:
      - [OpenCart Marketplace](https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=32336)
      - [Github latest release](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)

  2. Install the Facebook for OpenCart plugin via the OpenCart Installer.
      - Video guides are available here:
        - For OpenCart version 2.0.x to 2.2.x: [Video Guide](https://drive.google.com/file/d/1abUNPAz2quGkDvq3gVau2_wz1zNcuu6_/view?usp=sharing)
        - For OpenCart version 2.3.x: [Video Guide](https://drive.google.com/file/d/146HbegvZz562qTRbYKueqkm_UkAgWOqB/view?usp=sharing)
        - For OpenCart version 3.x: [Video Guide](https://drive.google.com/file/d/1NdONuLUokc_Q0QBa_t41_doatbuUDDBA/view?usp=sharing)

      - Installation instructions:
        - Go to your OpenCart store's admin panel, and click on:
          - Extensions -> Extension Installer. (For OpenCart 2.x) [Screenshot](https://drive.google.com/open?id=18acMNnESWauvK6A7EJIeewM7TsqpAmYa)
          - Extensions -> Installer. (For OpenCart 3.x) [Screenshot](https://drive.google.com/open?id=1by3jIljlrz7sYJAAI1KovABOayRrnHSW)

        - Click on the 'Upload' button and select the plugin zip file (Please ensure that the zip file ends with ```.ocmod.zip```). Click on the 'Continue' button if required. [Screenshot](https://drive.google.com/open?id=1iOyZNFow9qUiJITH4N60heml7-lZmCnF)

        - Go to your OpenCart store's admin panel again, and click on:
          - Extensions -> Modules (For OpenCart 2.0.x to 2.2.x) [Screenshot](https://drive.google.com/file/d/1KVaoKsdzaVvP3NB_3nghMLqoJX6aDwUS/view?usp=sharing)
          - Extensions -> Extensions -> Choose 'Modules' from the Extension type dropdown list [Screenshot](https://drive.google.com/file/d/1FqI2tTCTCdAnNyTDkmcFGAIHlO0OJ0VF/view?usp=sharing)

        - Locate Facebook Business Extension and click on Install button. [Screenshot](https://drive.google.com/file/d/11-cvulIf9My1jYIHOs20wFSxxybEbiuw/view?usp=sharing)

        - (Additional Step) **Only for OpenCart 2.0.x to 2.2.x:**
          - Go to the admin panel of OpenCart and click on Extensions -> Modifications. [Screenshot](https://drive.google.com/open?id=1H5ppQPXnx2UYo6v82d5comDJKPu064X2)
          - Click on the Refresh button. [Screenshot](https://drive.google.com/open?id=1qy-ipwK1HCk8oSnUmGuy6MCJxQdUyGfw)

        - Installation has been completed. You can now proceed with integrating your OpenCart store with Facebook Business by clicking on the 'Get started with Facebook' button.

  
  3. Setup the permission rights for Facebook Business Extension if you encounter "Permission Denied". [Screenshot.](https://drive.google.com/open?id=1wgBr11M5ikAVNXtxYw0bkYMsksTGW2ri) [Video guide](https://drive.google.com/file/d/1jfOLd79zA-3wyGoiWopzf7ok0U0KaG6W/view?usp=sharing)
      - Ensure you are on the latest version of the Facebook Business Extension plugin. [Latest version](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)
      - The Facebook Business Extension plugin will automatically enable the permission access for the default Administrator group. For other user groups, you will need to provide the permission access manually.
      - Follow the below steps to provide the permission access:
        - Go to the admin panel and click on Menu -> Settings -> Users -> User Groups. Locate your user group and click on the Edit button. [Screenshot](https://drive.google.com/open?id=1qNQQN4bFAk41CgW73rz6Dg5W_HIpnjMo)
        - Locate ```extension/module/facebook_business``` for both Access and Modify permissions. Ensure the permissions are selected and click on Save button.
        - Access the Facebook Business Extension to verify that you are able to view the plugin.

# Setup for Facebook business manager, page, pixel and catalog
  1. Access the Facebook Business Extension to setup business manager, page, pixel and catalog.
      - Go to the admin panel of OpenCart and click on Menu -> Facebook Business Extension [Screenshot](https://drive.google.com/file/d/1DbKJ5i1-dA490VyUfQ-5d8cyGVYrup5Z/view?usp=sharing)
      - Click on the 'Get Started with Facebook' button. [Screenshot](https://drive.google.com/file/d/1SATwGk6_YLFLwDiNR4m7BFGgDXsy4l_b/view?usp=sharing)
      - Facebook login popup requests you to log in first. [Screenshot](https://drive.google.com/file/d/1h--LYUfTJFxQTKF7lf5OvdUxy84obBoZ/view?usp=sharing)
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
      - Close the popup, and wait for the FBE page to refresh automatically. [Screenshot](https://drive.google.com/file/d/1_rJiW7WIOMxdgQ7jMKVy-d42pkZLsLhk/view?usp=sharing)
  
  2. If you are an existing user of previous versions of Facebook Business Extension, you should see a different screen on which all of your previously connected assets were automatically populated. Click Continue button to finish the setup. [Screenshot](https://drive.google.com/open?id=1QdOM1ZdcoY8YfrJJPD6MeN76XpLRH6bm)

# Launch Management View
In Management View, you can add more features such as enable Facebook Page Shop plugin, Facebook Messenger Chat plugin and etc.
  1. Access the Facebook Business Extension Management View.
      - Ensure you are on versions 4.0.0 and above of the Facebook Business Extension plugin. [Latest version](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)
      - You must already have completed the setup for business manager, page, pixel and catalog for Facebook Business Extension. Steps [here](#setup-for-facebook-business-manager-page-pixel-and-catalog).
      - Go to the admin panel and:
        - Click on Facebook Business Extension (for all OpenCart versions) [Screenshot](https://drive.google.com/file/d/1DbKJ5i1-dA490VyUfQ-5d8cyGVYrup5Z/view?usp=sharing)
        - Or, (for OpenCart 2.0.x to 2.2.x) click on Extensions -> Modules -> Facebook Business Extension [Screenshot](https://drive.google.com/file/d/1hh0TCxsM6lOeWdxNxxZYYkzQqvRJsxbj/view?usp=sharing)
        - Or, (for OpenCart 2.3.x to 3.x.x) click on Extensions -> Extensions -> Choose 'Modules' from the Extension type dropdown list -> Facebook Business Extension [Screenshot](https://drive.google.com/file/d/1FqI2tTCTCdAnNyTDkmcFGAIHlO0OJ0VF/view?usp=sharing)
      - Click on Manage Settings button. [Screenshot](https://drive.google.com/file/d/1rTrSpim-OeQaVt8UmhGozi_9yd4Idll4/view?usp=sharing)

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
  1. Enable the Facebook Messenger chat plugin.
      - You must already have completed the setup for business manager, page, pixel and catalog for Facebook Business Extension. Steps [here](#setup-for-facebook-business-manager-page-pixel-and-catalog).
      - Go to the admin panel and:
        - Click on Facebook Business Extension (for all OpenCart versions) [Screenshot](https://drive.google.com/file/d/1DbKJ5i1-dA490VyUfQ-5d8cyGVYrup5Z/view?usp=sharing)
        - Or, (for OpenCart 2.0.x to 2.2.x) click on Extensions -> Modules -> Facebook Business Extension [Screenshot](https://drive.google.com/file/d/1hh0TCxsM6lOeWdxNxxZYYkzQqvRJsxbj/view?usp=sharing)
        - Or, (for OpenCart 2.3.x to 3.x.x) click on Extensions -> Extensions -> Choose 'Modules' from the Extension type dropdown list -> Facebook Business Extension [Screenshot](https://drive.google.com/file/d/1FqI2tTCTCdAnNyTDkmcFGAIHlO0OJ0VF/view?usp=sharing)
      - Click on the 'Manage Settings' button. [Screenshot](https://drive.google.com/file/d/1rTrSpim-OeQaVt8UmhGozi_9yd4Idll4/view?usp=sharing)
      - In the popup window, locate the Messenger Chat plugin and click on the Add button. [Screenshot](https://drive.google.com/file/d/16v9JqISCDSGV37LbnP1gY25eEb1Rn1U7/view?usp=sharing)
      - Click on Add plugin button. [Screenshot](https://drive.google.com/file/d/1rmGiH9O6XU58q5QTUCDChPlEDCfWbpyA/view?usp=sharing)
      - Setup and customise your Messenger Plugin's Language, Greeting Message and Appearance settings, then click on the 'Next' button when you are done. [Screenshot](https://drive.google.com/file/d/1_jD7gSDYVhjJe7wgHPnEcm79bZWWT3ft/view?usp=sharing)
      - Add your domain name, e.g. https://yourwebsite.com/ to the Whitelist so that your Messenger Plugin will appear. After adding your domain to the whitelist, click on 'Done'. [Screenshot](https://drive.google.com/file/d/1YXThy_1YT614-ozZ-5K02hjSnWjHNJDd/view?usp=sharing)
      - You can now close the popup window.
      - Click on the 'Manage Settings' button again to ensure that the settings are saved correctly into your OpenCart store. [Screenshot](https://drive.google.com/file/d/1rTrSpim-OeQaVt8UmhGozi_9yd4Idll4/view?usp=sharing)
      - Proceed to the homepage of your OpenCart store and you should see the Messenger chat plugin at the bottom-right corner. [Screenshot](https://drive.google.com/file/d/1FmORE1qvQsKCtc_8Hd7lj_5SJvgVFDzF/view?usp=sharing)

  2. Edit the Facebook Messenger chat plugin settings.
      - Ensure that you have enabled the Facebook Messenger chat plugin, and have already completed the above steps in (1).
      - Go to the admin panel and:
        - Click on Facebook Business Extension (for all OpenCart versions) [Screenshot](https://drive.google.com/file/d/1DbKJ5i1-dA490VyUfQ-5d8cyGVYrup5Z/view?usp=sharing)
        - Or, (for OpenCart 2.0.x to 2.2.x) click on Extensions -> Modules -> Facebook Business Extension [Screenshot](https://drive.google.com/file/d/1hh0TCxsM6lOeWdxNxxZYYkzQqvRJsxbj/view?usp=sharing)
        - Or, (for OpenCart 2.3.x to 3.x.x) click on Extensions -> Extensions -> Choose 'Modules' from the Extension type dropdown list -> Facebook Business Extension [Screenshot](https://drive.google.com/file/d/1FqI2tTCTCdAnNyTDkmcFGAIHlO0OJ0VF/view?usp=sharing)
      - Click on the 'Manage Settings' button. [Screenshot](https://drive.google.com/file/d/1rTrSpim-OeQaVt8UmhGozi_9yd4Idll4/view?usp=sharing)
      - In the popup window, locate the Messenger Chat plugin and click on View button. [Screenshot](https://drive.google.com/file/d/1PeSsW-7_JMSWBPIfFnBYr0RQ1fNkDdrZ/view?usp=sharing)
      - CLick on the 'Update Plugin' button to modify existing settings and configurations. [Screenshot](https://drive.google.com/file/d/10mX_T_RzAGhJp1jync0zGJBBnQAtE7oY/view?usp=sharing)
      - Complete your changes and click on the 'Done' button to save your settings.

  3. Disable the Facebook Messenger chat plugin settings.
      - Ensure that you have enabled the Facebook Messenger chat plugin, and have already completed the above steps in (1).
      - Go to the admin panel and:
        - Click on Facebook Business Extension (for all OpenCart versions) [Screenshot](https://drive.google.com/file/d/1DbKJ5i1-dA490VyUfQ-5d8cyGVYrup5Z/view?usp=sharing)
        - Or, (for OpenCart 2.0.x to 2.2.x) click on Extensions -> Modules -> Facebook Business Extension [Screenshot](https://drive.google.com/file/d/1hh0TCxsM6lOeWdxNxxZYYkzQqvRJsxbj/view?usp=sharing)
        - Or, (for OpenCart 2.3.x to 3.x.x) click on Extensions -> Extensions -> Choose 'Modules' from the Extension type dropdown list -> Facebook Business Extension [Screenshot](https://drive.google.com/file/d/1FqI2tTCTCdAnNyTDkmcFGAIHlO0OJ0VF/view?usp=sharing)
      - In the popup window, locate the Messenger Chat plugin and click on the 'Disable' button. [Screenshot](https://drive.google.com/file/d/178EWYukGfV0zOoEodiE76GQoh-9fN_P8/view?usp=sharing)

# Delete the existing settings for Facebook for OpenCart
  1. Delete the existing settings. [Video guide](https://drive.google.com/file/d/1bhClnD8vw9Kwoh6is2zkdOY0MrJ4l3oa/view?usp=sharing) 
      - You must already have completed the setup for business manager, page, pixel and catalog for Facebook Business Extension. Steps [here](#setup-for-facebook-business-manager-page-pixel-and-catalog).
      - Go to the admin panel and:
        - Click on Facebook Business Extension (for all OpenCart versions) [Screenshot](https://drive.google.com/file/d/1DbKJ5i1-dA490VyUfQ-5d8cyGVYrup5Z/view?usp=sharing)
        - Or, (for OpenCart 2.0.x to 2.2.x) click on Extensions -> Modules -> Facebook Business Extension [Screenshot](https://drive.google.com/file/d/1hh0TCxsM6lOeWdxNxxZYYkzQqvRJsxbj/view?usp=sharing)
        - Or, (for OpenCart 2.3.x to 3.x.x) click on Extensions -> Extensions -> Choose 'Modules' from the Extension type dropdown list -> Facebook Business Extension [Screenshot](https://drive.google.com/file/d/1FqI2tTCTCdAnNyTDkmcFGAIHlO0OJ0VF/view?usp=sharing)
      - You should see an 'Uninstall' button at your Facebook Business Extension plugin page. [Screenshot](https://drive.google.com/file/d/1SI7Fk5P17jr8OXB9vYoDX9WSKm1IDeVv/view?usp=sharing)
      - A pop up confirmation should appear. Click on the OK button to proceed with deletion. [Screenshot](https://drive.google.com/file/d/1QLGBrf7FfYfXptcmtMydNPdLpJnSu-Cz/view?usp=sharing)
  2. Once deleted successfully, you should see the page with 'Get Started' button. [Screenshot](https://drive.google.com/open?id=11rel4BoOcxcmU_aqB6Pn-CIUwFVNTi2w)

# Uninstall the plugin

  - If you are using Facebook Business Extension v3.x and below:

    1. [Video guide](https://drive.google.com/open?id=1aPxqEcH1J3tT3bG0vMIC5DLnkDN7fo_d)

    2. You must already have installed the Facebook for OpenCart plugin on your OpenCart website.

    3. Go to the admin panel of your OpenCart website and click on Menu -> Extensions -> Modifications. [Screenshot](https://drive.google.com/open?id=1H5ppQPXnx2UYo6v82d5comDJKPu064X2)

    4. Locate and select the Facebook Business Extension plugin. Click on the Delete button on the top right of the screen. [Screenshot](https://drive.google.com/open?id=1cWMe0ChoDbTFm9on-9g89r7G_vZPylJP)

    5. Click on Ok button to delete the plugin. [Screenshot](https://drive.google.com/open?id=1swxbD99bfJxXGHfYPNYyZ3oaa6P7_rkY)

    6. Click on Refresh button to refresh the existing plugins on your OpenCart website. [Screenshot](https://drive.google.com/open?id=1Mfr49CzavKogSrOvZurJIvadfCwtR_6p)

  - If you are using Facebook Business Extension v4.0.0 and above:

      - For OpenCart 2.0.x to 2.2.x:

        1. You must already have installed the Facebook for OpenCart plugin on your OpenCart website.

        2. Go to the admin panel of your OpenCart store and click on Extensions -> Modules -> Facebook Business Extension. Click on 'Uninstall'. [Screenshot](https://drive.google.com/file/d/1hh0TCxsM6lOeWdxNxxZYYkzQqvRJsxbj/view?usp=sharing)

        3. Then, proceed to Extensions -> Modifications, and locate 'Facebook Business Extension'. [Screenshot](https://drive.google.com/file/d/1wwhPvvf5AbULZqsrgPoYKoJx3rJvoCIN/view?usp=sharing)

        4. Click on the 'Uninstall' button for 'Facebook Business Extension'.

        5. Click on the 'Refresh' button to refresh the existing plugins on your OpenCart website. [Screenshot](https://drive.google.com/open?id=1Mfr49CzavKogSrOvZurJIvadfCwtR_6p)

        6. Do note that if you want to remove the plugin files from your web server, you will have to remove them manually. Please check on the location of the files at our GitHub Repository [here](https://github.com/facebookincubator/Facebook-For-OpenCart/)

      - For OpenCart 2.3.x:
      
        1. You must already have installed the Facebook for OpenCart plugin on your OpenCart website.

        2. Go to the admin panel of your OpenCart website and click on Extensions -> Extensions -> Modules -> Facebook Business Extension. Click on 'Uninstall'. [Screenshot](https://drive.google.com/file/d/1u_Yz5bj7xx6Cu53qC5k9Qo3nWKpxRXPB/view?usp=sharing)

        3. Do note that if you want to remove the plugin files from your web server, you will have to remove them manually. Please check on the location of the files at our GitHub Repository [here](https://github.com/facebookincubator/Facebook-For-OpenCart/)

      - For OpenCart 3 and above:

        1. You must already have installed the Facebook for OpenCart plugin on your OpenCart website.

        2. Go to the admin panel of your OpenCart website and click on Extensions -> Extensions -> Modules -> Facebook Business Extension. Click on 'Uninstall'. [Screenshot](https://drive.google.com/file/d/1FqI2tTCTCdAnNyTDkmcFGAIHlO0OJ0VF/view?usp=sharing)

        3. To remove the plugin files from your web server, proceed to Extensions -> Installer, and locate the ```.ocmod.zip``` file you uploaded previously to install the Facebook Business Extension plugin. Then, click on the 'Uninstall' button. [Screenshot](https://drive.google.com/file/d/1HYkedlOIf-kkkMqkx_KvP1qytu5D2HSM/view?usp=sharing)

# Upgrade the plugin to a newer version

  - If you are using Facebook Business Extension v3.x and below, and upgrading to Facebook Business Extension v4.x.x:
    
    1. You must already have installed the Facebook Business Extension plugin on your OpenCart website.

    2. Delete the existing Facebook Business Extension plugin. [Video guide](https://drive.google.com/open?id=1aPxqEcH1J3tT3bG0vMIC5DLnkDN7fo_d)

    3. Install the new Facebook Business Extension version 4.0.0 and above by following the steps [here](#plugin-installation).

  - If you are using Facebook Business Extension v4.x.x, and upgrading to Facebook Business Extension v4.x.x and above:
   
    1. You must already have installed the Facebook Business Extension plugin on your OpenCart website.

    2. To upgrade the existing Facebook Business Extension for versions 4.0.0 and above, you can choose one of the following:

        - Simply install the extension again through the Extension Installer. You can refer to the steps [here](#plugin-installation) for installation through the Extension Installer.
            - For OpenCart 2.0.x to 2.2.x, you need to uninstall the existing Facebook Business Extension OCMOD at Extensions -> Modifications. [Screenshot](https://drive.google.com/file/d/1wwhPvvf5AbULZqsrgPoYKoJx3rJvoCIN/view?usp=sharing)
            - For OpenCart 3 and above, you may want to uninstall the existig Facebook Business Extension at Extensions -> Installer. Simply locate the ```.ocmod.zip``` file you uploaded previously to install the Facebook Business Extension plugin. Then click on the 'Uninstall' button. [Screenshot](https://drive.google.com/file/d/1HYkedlOIf-kkkMqkx_KvP1qytu5D2HSM/view?usp=sharing)

        - Manually upload the contents of the 'upload' folder into your web server and replace the existing files.
            - Do note that this will only work with OpenCart v2.3.x and above.
    
    3. After the new version of the plugin has been uploaded, you will have to:

        - For OpenCart 2.0.x to 2.2.x:
            - Go to the admin panel of your OpenCart store and click on Extensions -> Modules -> Facebook Business Extension. Click on 'Uninstall'. [Screenshot](https://drive.google.com/file/d/1hh0TCxsM6lOeWdxNxxZYYkzQqvRJsxbj/view?usp=sharing)
            - Then, on the same page, click on 'Install' again.
            - This is to install/uninstall any Events or database changes that might have been made in the new version of the plugin. Do note that this will not remove your connection with Facebook Business but your OpenCart settings for the Facebook Business Extension (i.e. syncing of special prices as discount to Facebook Catalog option and enabling of cookie bar option) will be reset.
            - Go to the admin panel of OpenCart and click on Extensions -> Modifications. [Screenshot](https://drive.google.com/open?id=1H5ppQPXnx2UYo6v82d5comDJKPu064X2)
            - Click on the Refresh button. [Screenshot](https://drive.google.com/open?id=1qy-ipwK1HCk8oSnUmGuy6MCJxQdUyGfw)
        
        For OpenCart 2.3.x to 3.x.x:
            - Go to the admin panel of your OpenCart website and click on Extensions -> Extensions -> Modules -> Facebook Business Extension. Click on 'Uninstall'. [Screenshot](https://drive.google.com/file/d/1u_Yz5bj7xx6Cu53qC5k9Qo3nWKpxRXPB/view?usp=sharing)
            - Then, on the same page, click on 'Install' again.
            - This is to install/uninstall any Events or database changes that might have been made in the new version of the plugin. Do note that this will not remove your connection with Facebook Business but your OpenCart settings for the Facebook Business Extension (i.e. syncing of special prices as discount to Facebook Catalog option and enabling of cookie bar option) will be reset.

  - If you are using Facebook Business Extension v3.x and below, and upgrading to Facebook Business Extension v3.x and below:

    1. [Video guide](https://drive.google.com/open?id=12dX2wYTcE3Y7Wf-ZBD_6X4EAZU2L-8vp)

    2. You must already have installed the Facebook Business Extension plugin on your OpenCart website.
    
    3. Delete the existing Facebook for OpenCart plugin. [Video guide](https://drive.google.com/open?id=1aPxqEcH1J3tT3bG0vMIC5DLnkDN7fo_d)

    4. Install the later version plugin. Verify that the Facebook for OpenCart version is shown as the later version. [Screenshot.](https://drive.google.com/open?id=19Nfp_1x9cQbGCk-rMmi3PkLy3NEPFHdS) [Video guide](https://drive.google.com/open?id=1V4Nu8nlmHX5ppKqsjcR-xR05Rozb7MKN)

# Cookie bar on your OpenCart website
  1. Disable the cookie bar.
      - Ensure you are on the versions 2.0.3 and above of the Facebook Business Extension plugin. [Latest version](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)

      - For Facebook Business Extension version 3.1.2 and below:
        - Go to the admin panel of OpenCart and click on Menu -> Facebook Business Extension -> Facebook Business Extension. [Screenshot](https://drive.google.com/open?id=1xC5hQLqn-6AR7mxPME3y-safDTY-LFya)
        - Uncheck the Show cookie bar on store website option and click on Save button. [Screenshot](https://drive.google.com/open?id=1cdzTmI9pIqKx2olKku0-bjH1XMPEKcbn)

      - For Facebook Business Extension version 4.0.0 and above;
        - Go to the admin panel of your OpenCart website and click on Facebook Business Extension. [Screenshot](https://drive.google.com/file/d/1138L_BqxjilQE4TT8iPqd5Z5A8PGNuTz/view?usp=sharing)
          - Alternatively, go to:
            - Extensions -> Modules -> Facebook Business Extension (for OpenCart 2.0.x to 2.2.x) [Screenshot](https://drive.google.com/file/d/1KVaoKsdzaVvP3NB_3nghMLqoJX6aDwUS/view?usp=sharing)
            - Extensions -> Extensions -> choose 'Modules' from the Extension type dropdown list -> Facebook Business Extension (for OpenCart 2.3.x and above) [Screenshot](https://drive.google.com/file/d/1-jnNis1ZC0crNPqwVcAyA4-wCshBy3sc/view?usp=sharing)
        - Click on the 'Settings' tab and select 'Disable' for the 'Show cookie bar on store website' option. Then click on the Save button. [Screenshot](https://drive.google.com/file/d/1JY9gMSnopNJuDKdh1hwwUlzgZelcy0Fo/view?usp=sharing)

  2. Enable the cookie bar.
      - Ensure you are on the versions 2.0.3 and above of the Facebook Business Extension plugin. [Latest version](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)

      - For Facebook Business Extension version 3.1.2 and below:
        - Go to the admin panel and click on Menu -> Facebook Business Extension -> Facebook Business Extension. Click on Manage Settings button. [Screenshot](https://drive.google.com/open?id=1nUNSsphp7ID8Ma4_5ESWI8DR_eQ4-IfI)
        - Check the Show cookie bar on store website option and click on Save button. [Screenshot](https://drive.google.com/open?id=1CSeaZ0BPsue6eNKsLHXjahodXWgNh5ss)

      - For Facebook Business Extension version 4.0.0 and above;
        - Go to the admin panel of your OpenCart website and click on Facebook Business Extension. [Screenshot](https://drive.google.com/file/d/1138L_BqxjilQE4TT8iPqd5Z5A8PGNuTz/view?usp=sharing)
          - Alternatively, go to:
            - Extensions -> Modules -> Facebook Business Extension (for OpenCart 2.0.x to 2.2.x) [Screenshot](https://drive.google.com/file/d/1KVaoKsdzaVvP3NB_3nghMLqoJX6aDwUS/view?usp=sharing)
            - Extensions -> Extensions -> choose 'Modules' from the Extension type dropdown list -> Facebook Business Extension (for OpenCart 2.3.x and above) [Screenshot](https://drive.google.com/file/d/1-jnNis1ZC0crNPqwVcAyA4-wCshBy3sc/view?usp=sharing)
        - Click on the 'Settings' tab and select 'Enable' for the 'Show cookie bar on store website' option. Then click on the Save button. [Screenshot](https://drive.google.com/file/d/1JY9gMSnopNJuDKdh1hwwUlzgZelcy0Fo/view?usp=sharing)

# Using OpenCart Product Specials as Sale price in Facebook Catalog
  1. Disable the special price to be used as discount.
      - Ensure you are on the versions 2.1.11 and above of the Facebook Business Extension plugin. [Latest version](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)

      - For Facebook Business Extension version 3.1.2 and below:
        - Go to the admin panel of OpenCart and click on Menu -> Facebook Business Extension -> Facebook Business Extension. [Screenshot](https://drive.google.com/open?id=1xC5hQLqn-6AR7mxPME3y-safDTY-LFya)
        - Uncheck the Sync product special price as discount option and click on Save button. [Screenshot](https://drive.google.com/open?id=1cdzTmI9pIqKx2olKku0-bjH1XMPEKcbn)
        - Click on Resync Products to Facebook button to resync the product details to Facebook.
      
      - For Facebook Business Extension version 4.0.0 and above:
        - Go to the admin panel of your OpenCart website and click on Facebook Business Extension. [Screenshot](https://drive.google.com/file/d/1138L_BqxjilQE4TT8iPqd5Z5A8PGNuTz/view?usp=sharing)
          - Alternatively, go to:
            - Extensions -> Modules -> Facebook Business Extension (for OpenCart 2.0.x to 2.2.x) [Screenshot](https://drive.google.com/file/d/1KVaoKsdzaVvP3NB_3nghMLqoJX6aDwUS/view?usp=sharing)
            - Extensions -> Extensions -> choose 'Modules' from the Extension type dropdown list -> Facebook Business Extension (for OpenCart 2.3.x and above) [Screenshot](https://drive.google.com/file/d/1-jnNis1ZC0crNPqwVcAyA4-wCshBy3sc/view?usp=sharing)
        - Click on the 'Settings' tab and select 'Disable' for the 'Sync product special price as discount to Facebook catalog' option. Then click on the Save button. [Screenshot](https://drive.google.com/file/d/19VSEg3iuvOylXqL3gCNQTf-sWKrnhsLv/view?usp=sharing)
        - Force a resync of the products to Facebook Catalog by:
          - Click on the 'Connection' tab and click on 'Manage Settings' button. A popup window should appear. [Screenshot](https://drive.google.com/file/d/1rTrSpim-OeQaVt8UmhGozi_9yd4Idll4/view?usp=sharing)
          - In the popup window, look for the 'Catalog' card and click on the 'View' button. This should open a new window in your browser. [Screenshot](https://drive.google.com/file/d/12iItSI74oiQUDQrid0cunrcG9OZ0ljgg/view?usp=sharing)
          - In the new window, locate the 'Data Source' item in the left column menu and click on it. [Screenshot](https://drive.google.com/file/d/1l43EzX8n0gY04rsdMPWw5CY8yzVRz_19/view?usp=sharing)
          - Look for the Catalog Feed that belongs to your OpenCart store and click on its name. [Screenshot](https://drive.google.com/file/d/1CeZvVDsxHlQvCzqO5TiUIIavTzYs5xTu/view?usp=sharing)
          - Click on the 'Upload Now' button to force a resync of your products from your OpenCart store to Facebook Catalog. [Screenshot](https://drive.google.com/file/d/1xzuid30fUL3otyiDzp8t_Da37EmtpZDz/view?usp=sharing)
        

  2. Enable the special price to be used as discount.
      - Ensure you are on the versions 2.1.11 and above of the Facebook Business Extension plugin. [Latest version](https://github.com/facebookincubator/Facebook-For-OpenCart/releases/latest)

      - For Facebook Business Extension version 3.1.2 and below:
        - Go to the admin panel and click on Menu -> Facebook Business Extension -> Facebook Business Extension. Click on Manage Settings button. [Screenshot](https://drive.google.com/open?id=1nUNSsphp7ID8Ma4_5ESWI8DR_eQ4-IfI)
        - Check the Sync product special price as discount option and click on Save button. [Screenshot](https://drive.google.com/open?id=1CSeaZ0BPsue6eNKsLHXjahodXWgNh5ss)

      - For Facebook Business Extension version 4.0.0 and above:
        - Go to the admin panel of your OpenCart website and click on Facebook Business Extension. [Screenshot](https://drive.google.com/file/d/1138L_BqxjilQE4TT8iPqd5Z5A8PGNuTz/view?usp=sharing)
          - Alternatively, go to:
            - Extensions -> Modules -> Facebook Business Extension (for OpenCart 2.0.x to 2.2.x) [Screenshot](https://drive.google.com/file/d/1KVaoKsdzaVvP3NB_3nghMLqoJX6aDwUS/view?usp=sharing)
            - Extensions -> Extensions -> choose 'Modules' from the Extension type dropdown list -> Facebook Business Extension (for OpenCart 2.3.x and above) [Screenshot](https://drive.google.com/file/d/1-jnNis1ZC0crNPqwVcAyA4-wCshBy3sc/view?usp=sharing)
        - Click on the 'Settings' tab and select 'Enable' for the 'Sync product special price as discount to Facebook catalog' option. Then click on the Save button. [Screenshot](https://drive.google.com/file/d/19VSEg3iuvOylXqL3gCNQTf-sWKrnhsLv/view?usp=sharing)
        - Force a resync of the products to Facebook Catalog by:
          - Click on the 'Connection' tab and click on 'Manage Settings' button. A popup window should appear. [Screenshot](https://drive.google.com/file/d/1rTrSpim-OeQaVt8UmhGozi_9yd4Idll4/view?usp=sharing)
          - In the popup window, look for the 'Catalog' card and click on the 'View' button. This should open a new window in your browser. [Screenshot](https://drive.google.com/file/d/12iItSI74oiQUDQrid0cunrcG9OZ0ljgg/view?usp=sharing)
          - In the new window, locate the 'Data Source' item in the left column menu and click on it. [Screenshot](https://drive.google.com/file/d/1l43EzX8n0gY04rsdMPWw5CY8yzVRz_19/view?usp=sharing)
          - Look for the Catalog Feed that belongs to your OpenCart store and click on its name. [Screenshot](https://drive.google.com/file/d/1CeZvVDsxHlQvCzqO5TiUIIavTzYs5xTu/view?usp=sharing)
          - Click on the 'Upload Now' button to force a resync of your products from your OpenCart store to Facebook Catalog. [Screenshot](https://drive.google.com/file/d/1xzuid30fUL3otyiDzp8t_Da37EmtpZDz/view?usp=sharing)

