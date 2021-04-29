# Frequently Asked Questions for Facebook Business Extension

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


Always check that you have installed the latest version of the plugin as we are continously improving the plugin.


# Plugin Installation
1. Do you support installation via vQmod?
    - No, we do not support installation via vQmod. The mode of plugin installation is via the OpenCart Extension Installer and OpenCart Events (for OpenCart 2.3.x to 3.x.x and above) or OCMOD (for OpenCart 2.0.x to 2.2.x).

2. I am getting "Directory containing files to be uploaded could not be found!" error when I am installing the plugin zip file on OpenCart.
    - If you are creating the plugin zip file manually (i.e. downloading from GitHub and manually compressing the files), please ensure that there is no additional top level folders inside the plugin zip file. This means that when compressing the files into a zip file, there should not be any other folders other than the 'upload' folder. [Screenshot](https://drive.google.com/file/d/1bld3AYjB2JXyveM1uRbJ-Jqw26ZTaxQ_/view?usp=sharing)

3. I am getting "Invalid file type" error when I am installing the plugin zip file to OpenCart.
    - Please ensure that the plugin zip file you are uploading ends with the file extension ```.ocmod.zip```. For example ```facebook_business.ocmod.zip```. Files that do not end with this file extension will cause the "Invalid file type" error when installing through OpenCart's Extension Installer. [Screenshot](https://drive.google.com/file/d/1RZjlW4SJvL6lDFupHtg944kkTrgx7ls3/view?usp=sharing)

4. Can I manually upload the files into my web server to install the Facebook Business Extension plugin?
    - If you are using OpenCart version 2.0.x to 2.2.x:
      - No, it is highly recommended that you use the OpenCart extension Installer to install the plugin as it uses OCMOD.
      - However, in the event that using the OpenCart Extension Installer is not possible, you can still perform an installation by uploading the files directly through your web server but the installation of the OCMOD script has to be done manually or through a third-party extension.

    - If you are using OpenCart version 2.3.x to 3.x.x and above:
      - Yes, you may manually upload the contents of the 'upload' folder into your web server to perform an installation of the Facebook Business Extension plugin.



# Accessing the Facebook Business Extension module
1. I do not see the Facebook Business Extension under Extensions -> Extensions -> Modules.
    - This means that there was an error during the installation of the plugin.
    - If you are using OpenCart version 2.0.x to 2.3.x, please ensure that you have FTP enabled and the correct details filled up. [Screenshot](https://drive.google.com/file/d/1-QS-vZtpZgun5lJXnn5H0UGriAm_YujF/view?usp=sharing)
    - Please ensure that the file admin/controller/extension/module/facebook_business.php exists on your web server. If this file is not available, it means that installation of the plugin failed and you will need to perform a re-installation.
    - If subsequent re-installations still fail, please reach out to us via our [helpdesk](https://marketinsg.zendesk.com/) for assistance.

2. I do not see the menu for Facebook Business Extension in my admin panel after installing the plugin.
    - For OpenCart version 2.0.x to 2.2.x, please ensure the following points:
        - Facebook Business Extension is installed under Extensions -> Modules -> Facebook Business Extension.
        - Facebook Business Extension Modifications is installed and enabled under Extensions -> Modifications -> Facebook Business Extension. Please also ensure that you have refreshed your modifications cache (light blue gear icon button located at the top-right corner).
        - Alternatively, you should be able to access the extension at Extensions -> Modules -> Facebook Business Extension -> 'Edit'.

    - For OpenCart version 2.3.x to 3.x.x and above, please ensure the following points:
        - Facebook Business Extension is installed under Extensions -> Extensions -> Modules -> Facebook Business Extension. [Screenshot](https://drive.google.com/file/d/1WjIaYXA3bqqLGqAnRHp0OU_Dn9-oqK6s/view?usp=sharing)
        - Facebook Business Extension's Events under Extensions -> Events are present and not disabled. [Screenshot](https://drive.google.com/file/d/1kWN4ThDHLKMMPYMxMQWqSj4SePQOcBmj/view?usp=sharing)
        - If you are using OpenCart version 3 and above, you may also need to perform a refresh for the Twig/SASS cache. To do so, go to your OpenCart admin dashboard, click the light blue gear icon button located at the top-right corner, and then click the yellow refresh button next to Theme and SASS. You do not need to perform these actions if they are disabled. [Screenshot](https://drive.google.com/file/d/1xTKIXdgrN7g7UdbpsfK8Um1Kkd_hOLsK/view?usp=sharing)
        - Alternatively, you should be able to access the extension at Extensions -> Extensions -> Modules -> Facebook Business Extension -> 'Edit'. [Screenshot](https://drive.google.com/file/d/1-jnNis1ZC0crNPqwVcAyA4-wCshBy3sc/view?usp=sharing)

3. I do not see the Facebook tab in my product configurations. [Screenshot](https://drive.google.com/file/d/1Us0vPwSp-6jGjxp-W3hF4K2VLAZgtjWd/view?usp=sharing)
    - For OpenCart version 2.0.x to 2.2.x:
        - Please ensure that the Facebook Business Extension OCMOD is installed correctly under Extensions -> Modfications and that it is not disabled. Please also ensure that you have refreshed your modifications cache. [Screenshot](https://drive.google.com/file/d/12s7W1aD3AAV2lROdcUwQbeMoEwDx2lsL/view?usp=sharing)
    
    - For OpenCart version 2.3.x to 3.x.x and above:
        - Please ensure that Facebook Business Extension's Events under Extensions -> Events are present and not disabled. [Screenshot](https://drive.google.com/file/d/1kWN4ThDHLKMMPYMxMQWqSj4SePQOcBmj/view?usp=sharing)
    
    - If the above points do not work, please contact our [help desk](https://marketinsg.zendesk.com/) or open an issue with us to receive support from our development team.

4. I am getting "Permission Denied" error when accessing the Facebook Business Extension module. [Screenshot](https://drive.google.com/file/d/1z7HECXtRIZlw5SdIcXvMi3EF0XjvTYew/view?usp=sharing)
    - By default, the plugin will automatically enable the Access and Modify permission for the default OpenCart Adminstrator user group. Please give the required permissions if you are using a different user group. [Video guide](https://drive.google.com/file/d/1jfOLd79zA-3wyGoiWopzf7ok0U0KaG6W/view?usp=sharing)


# Setup for Facebook Business Manager, Page, Pixel, Catalog and Page Shop
1. I can't see my page.
    - Usually this is due to a lack of permissions to access the page.
    - Ensure that you are the admin of the page, or were added as an admin at least one week ago due to existing security control to prevent bad actor.
    - Ensure you own the Business Manager associated with the page. You can go to Page Settings, click Page Roles tab, under Page Owner section, check whether you have access to the Business Manager. [Screenshot](https://drive.google.com/open?id=1nZjt9oK8xIe3rBunsVnnRF6rmlNKhudr)

2. I am unable to get Facebook Pixel to fire on my webstore after completing the setup. [Screenshot](https://drive.google.com/open?id=1rSQYT9mQViGkloFkRJKpEmfxl4iOU161)
    - For OpenCart version 2.0.x to 2.2.x:
        - Please ensure that the Facebook Business Extension OCMOD is installed correctly under Extensions -> Modfications and that it is not disabled. Please also ensure that you have refreshed your modifications cache. [Screenshot](https://drive.google.com/file/d/12s7W1aD3AAV2lROdcUwQbeMoEwDx2lsL/view?usp=sharing)
        - If you are using any custom theme or third-party caching extensions on your OpenCart website, please clear the relevant cache.

    - For OpenCart version 2.3.x to 3.x.x and above:
        - Please ensure that the Facebook Business Extension's Events under Extensions -> Events are present and not disabled. [Screenshot](https://drive.google.com/file/d/1kWN4ThDHLKMMPYMxMQWqSj4SePQOcBmj/view?usp=sharing)
        - If you are using OpenCart v3.x, check that you have disabled the theme cache or you have refreshed your theme cache. [Screenshot](https://drive.google.com/open?id=1bY-bworYxX36b88HDvFW0_32C3Wtq_Tm)
        - If you are using any custom theme or third-party caching extensions on your OpenCart website, please clear the relevant cache.

    - If the above have been ensured and you are still unable to get your Facebook Pixel firing, do contact our [help desk](https://marketinsg.zendesk.com) or open an issue with us to receive support from our development team.

3. I see an error that says we're unable to proceed since the selected page has already been configured for the Facebook Business Extension.
    - If you see this error, it means that your Page is associated with another store. You'll need to decide whether you want to keep the Facebook Page associated with your old OpenCart store, or associate it with a different store. [Screenshot](https://drive.google.com/open?id=1gntxIF0YGa5XQEk2Pe-ichA-yQ5ZMr-J)

4. I'm getting "An error occured while fetching FBE feature config" error message when trying to setup Facebook Business Manager.
    - Please try to perform a quick reinstallation of the plugin by following the steps below.

    - For OpenCart 2.0.x to 2.2.x:
        - Go to your admin panel and proceed to Extensions -> Modules -> Facebook Business Extension. Click on 'Uninstall'. [Screenshot](https://drive.google.com/file/d/1hh0TCxsM6lOeWdxNxxZYYkzQqvRJsxbj/view?usp=sharing)
        - On the same page, click on 'Install' again for 'Facebook Business Extension'.

    - For OpenCart 2.3.x to 3.x.x and above:
        - Go to your admin panel and proceed to Extensions -> Extensions -> Modules -> Facebook Business Extension. Click on 'Uninstall'. [Screenshot](https://drive.google.com/file/d/1u_Yz5bj7xx6Cu53qC5k9Qo3nWKpxRXPB/view?usp=sharing)
        - On the same page, click on 'Install' again for 'Facebook Business Extension'.

# Synchronising of OpenCart products to Facebook catalog
1. How many product catalogs can I use with this setup?
    - You may only use 1 product catalog.

2. Why is Facebook catalog/Page shop not showing up my product images?
    - Ensure the file permission of the images on your website have read access. See this post for more details - https://github.com/facebookincubator/Facebook-For-OpenCart/issues/87
    - Check on the DNS and SSL certification for your web hosting company if it is set to Flexible and to change this to Full (Strict). Check this post for more details - https://github.com/facebookincubator/Facebook-For-OpenCart/issues/244

3. Why is the Checkout link from Page Shop not working?
    - Check if the issue is due to server handling the URL rewrite due to special characters in the URL. See this post for more details - https://github.com/facebookincubator/Facebook-For-OpenCart/issues/113


# Product configurations and settings
1. Facebook product catalog showing "This url is classified as malicious" warning message.
    - Facebook has detected that your website URL may be malicious. You can reach to Facebook team, share with us the context and submit an appeal to review your website URL again [here](https://www.facebook.com/help/contact/571927962827151).

2. How is the plugin synchronising OpenCart product special prices to Facebook catalog's sale prices?
    - The plugin pulls Special prices from your OpenCart products the same way OpenCart does. This means that the only special prices that are active (i.e. no Date Start and Date End configured **or** the current date is within the Date Start and Date End period) is pulled for the default customer group and based on priority.

3. Why is my OpenCart product options not configured to Facebook Page Shop?
    - This is because by default, OpenCart does not support product variants and instead, have product options. Since OpenCart product options do not have a unique product ID or SKU, the FBE plugin is unable to synchronise the OpenCart product options to Facebook Catalog. Instead, when a customer clicks on the checkout link on your Facebook page shop, the customer will then be brought to the product page on your OpenCart website to specify for product options.

4. The currency listed on the Facebook catalog is not the correct currency. How do I change it?
    - You need to create the right currency on OpenCart, and then select the currency on the Store Settings. [Screenshot](https://drive.google.com/open?id=11Sh6AiyPpQrv2ki7-oBAQvZnnlyxcyRV) [Screenshot](https://drive.google.com/open?id=1yL-RFUhWI-qX-Mo2u4dpNNW0B6nOHAvl)
    - For the existing Facebook catalog, you can delete away the existing Facebook Business Extension settings and re-setup again. [Video guide](https://drive.google.com/open?id=1PenBy_xizQGszdiS5BrmVFPypn5HKnkL)

5. How does the plugin handle item availability on Facebook catalog?
    - The OpenCart to Facebook product status mapping is as follow:
        - OpenCart product quantity of (1 or more) = Facebook Availability 'in stock'
        - OpenCart product quantity of (0 or less), with 'Subtract Stock' option for the product set to 'disabled' = Facebook Availability 'in stock'
        - OpenCart product quantity of (0 or less), with 'Subtract Stock' option for the product set to 'enabled' = Facebook Availability 'out of stock'

# Cookie Consent Bar on Web store
1. Why am I unable to see the option to disable cookie bar on web store?
    - Please check if you are using the version 2.1.0 and above for the Facebook for OpenCart plugin.
    - Please ensure that you've followed the steps [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/INSTALL_GUIDE.md#cookie-bar-on-your-opencart-website).

2. Why is my OpenCart website still showing the cookie consent bar when I have already disabled the cookie bar option in my Facebook Business Extension's settings?
    - Your web browser may be caching the previous copy of the Javascript files of the plugin. Please try clearing your browser cache.

# Facebook Messenger Chat plugin
1. I am unable to see the Facebook Messenger Chat plugin on my website
    - Please ensure that you've followed the steps [here](https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/INSTALL_GUIDE.md#setup-for-facebook-messenger-chat).

# Setting up for running ads on Facebook
1. What should I do if I have already set up Facebook Pixel on my OpenCart website?
    - If you already have existing Facebook Pixel implementations installed and are running Facebook Dynamic Ads, you do not have to use the Facebook Business Extension in OpenCart.
    - If you already have existing Facebook Pixel implementations installed but not dynamic ads, or you think you may have set up Facebook Pixel incorrectly on your OpenCart website, you can use the Facebook Business Extension plugin to get everything set up for your OpenCart website automatically.
        - Please note that you will have to manually remove and uninstall your existing Facebook Pixel implementations (i.e. third-party Facebook Pixel plugins or existing Facebook Pixel codes) from your OpenCart website. Otherwise, you will have multiple versions of Facebook Pixel on your website.
        - Having multiple versions of Facebook Pixel on your website can lead to:
            - Incorrect Campaign results (e.g. 2 times the number of actual conversions)
            - Cost per result being halved in your reports
        - If you are removing existing Facebook Pixel implementations on your website to start over with the Facebook Business Extension plugin, we highly recommend that you pause your active campaigns first. This way, you will minimise any impact on your website's Custom Audiences and conversion rates.

2. Will Facebook dynamic ads stay up to date with my stock changes?
    - Yes, your Facebook Catalog is automatically scheduled to update everyday.

3. I want to also reach people who have not visited my website or app, what should I do?
    - You can drive new potential customers to visit your website, or to take a specific action, by creating an ad campaign using the website conversions objective. Learn more about how to optimize your ad sets for conversions [here](https://www.facebook.com/business/help/416997652473726).

4. My advertiser pays a third party vendor to manage their OpenCart site, what should I do?
    - That's fine. Just be sure that the third party vendor is added as an administrator to the advertiser's Facebook page and ad account.