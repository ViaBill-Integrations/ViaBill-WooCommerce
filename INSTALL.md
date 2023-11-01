# ViaBill - seamless financing! 
## Plugin for WordPress

# Prerequisites

-  A compatible WordPress and WooCommerce version. Note that this plugin is compatible with WooCommerce 3.3 or newer and it has been tested with WordPress up to 6.4.
-  As with _all_ WordPress plugins, it is highly recommended to backup your site before installation and to install and test on a staging environment prior to production deployments.

# Installation

Before you start the installation of the plugin, make sure you meet the requirements set by your WordPress version. 

## Manual Installation

In order to install the plugin manually you need to download the repository on your local disk and then create a zip file named viabill-woocommerce.zip. Then, you need to login in the backend of the WordPress site and navigate to menu Plugins > Add New. Click on the “Upload Plugin” button and select the zip file viabill-woocommerce.zip you created previously from the repository. If everything goes smoothly, you will see the “Plugin installed successfully” success message. Click on the “Activate Plugin” button to complete the installation and continue with the configuration process.

## Installation via WordPress Plugins

Login in the backend of the WordPress site and navigate to menu Plugins > Add New and search for the keyword “Viabill”. Click on the installation button for the ViaBill > WooCommerce" plugin.

# Configuration

After a successful installation and activation of the Viabill plugin you will be transferred automatically to the configuration page in order to register or login into your existing Viabill account. If not, from the WordPress backend navigate to WooCommerce > Settings > Payments and then click on the Manage button next to the Viabill payment method. You may also have to click on the toggle button in order to enable the payment method first.

## New or Existing User

Before configuring the plugin, you need to create a new ViaBill account or sign in, if you already have an existing one.

## Module Configuration

Once you have successfully created your ViaBill account, or login into your existing one, you will be able to further configure the payment method. Please pay attention to the following settings:

| Parameter | Purpose |
| ------ | ------ |
| Show on Product page | Show the ViaBill's Price Tags on the product page |
| Show on Cart Summary | Show the ViaBill's Price Tags on the shopping cart |
| Show on Payment selection | Show the ViaBill's Price Tags on the checkout page |
| ViaBill Test Mode | If this parameter is set to “Yes”, no actual payment is made, therefore orders should not be shipped. Once you are ready to use ViaBill with real customers it's important to set this parameter to “No”. |
| Enable logging | This parameter is useful if something is not working as expected and it can provide valuable information to the tech support team. |

> Note that the PriceTags settings are available under the WooCommerce > Settings > ViaBill PriceTags tab.

# Upgrade Module

## Manual Upgrade

This method describes how to upgrade the module manually, without any references to the WordPress Plugins site. 

a) Make a backup of the following folder:
`{Wordpress root directory}`/wp-content/plugins/viabill-woocommerce
This is helpful in case something goes wrong and you want to restore the latest working version.
b) Download the repository files on your local disk.
c) Copy the contents of this repository into `{Wordpress root directory}`/wp-content/plugins/viabill-woocommerce and overwrite all existing files.

## WordPress Plugins Upgrade

If you have installed the ViaBill module via the WordPress Plugins site, every time a new version is available you will receive a notification and you will have the option to upgrade the module by simply clicking on the “Upgrade” button.

# Disable Module

If you wish to disable the ViaBill module without uninstalling it, you can simply go to the  login in the backend of the Wordpress site and then go to menu WooCommerce > Settings > Payments. Click on the toggle switch to disable the Viabill payment method.

# Uninstall Module

The proper way to uninstall the Viabill Payment module is by login in the backend of the Wordpress site and then navigate to menu Plugins > Installed Plugins. Locate the ViaBill - WooCommerce entry and click on the Deactivate link. Once you have done that, click on the Delete link for the plugin.

# Troubleshooting and Support

## ViaBill Module Support

If you are experiencing any technical issues, please navigate to WooCommerce > Settings > Payments section and click on the ViaBill's “Manage” button. Check the “Enable Logging” option and then try to replicate your issue by repeating the action which caused it. Finally, go to  WooCommerce > ViaBill Support page. Fill out the form and submit it to our technical support team. This contact form is auto-populated with vital information that will help us to resolve your issue faster.

Alternatively, contact us via email at tech@viabill.com.