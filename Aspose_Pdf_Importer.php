<?php
/*
Plugin Name: Aspose.PDF Importer
Plugin URI:
Description: Aspose.PDF Importer is a plugin for reading content from the PDF file and then inserting it in the editor using Aspose.PDF Cloud APIs
Version: 3.2
Author: aspose.cloud Marketplace
Author URI: https://www.aspose.cloud/

*/

#### INSTALLATION PROCESS ####
/*
1. Download the plugin and extract it.
2. Upload the directory '/aspose-pdf-importer/' to the '/wp-content/plugins/' directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Click on 'Aspose.PDF Importer' link under Settings menu to access the admin section.
5. Click `Enable Free and Unlimited Access`. No Sign Up required.
*/

add_filter('plugin_action_links', 'AsposePdfImporterPluginLinks', 10, 2);

define("ASPOSE_PDF_IMPORTER_PLUGIN_FILE", __FILE__);


/**
 * Create the settings link for this plugin
 * @param $links array
 * @param $file string
 * @return $links array
 */
function AsposePdfImporterPluginLinks($links, $file) {
     static $this_plugin;

     if (!$this_plugin) {
		$this_plugin = plugin_basename(__FILE__);
     }

     if ($file == $this_plugin) {
		$settings_link = '<a href="' . admin_url('options-general.php?page=AsposePdfImporterAdminMenu') . '">' . __('Settings', 'Aspose-Pdf-Importer') . '</a>';
		array_unshift($links, $settings_link);
     }

     return $links;
}

register_activation_hook(__FILE__, 'SetOptionsAsposePdfImporter');

/**
 * Basic options function for the plugin settings
 * @param no-param
 * @return void
 */
function SetOptionsAsposePdfImporter() {

     // Adding options for the like post plugin
//     add_option('wti_like_post_drop_settings_table', '0', '', 'yes');

}

/**
 * For dropping the table and removing options
 * @param no-param
 * @return no-return
 */
function UnsetOptionsAsposePdfImporter() {
    // Deleting the added options on plugin uninstall
    delete_option('wti_like_post_drop_settings_table');
	// Removing older version Keys
    delete_option('aspose_pdf_importer_app_sid');
    delete_option('aspose_pdf_importer_app_key');
	// Removing the keys
    delete_option('aspose-cloud-app-sid');
    delete_option('aspose-cloud-app-key');	
}

register_uninstall_hook(__FILE__, 'UnsetOptionsAsposePdfImporter');

function AsposePdfImporterAdminRegisterSettings() {
     // Registering the settings

     register_setting('aspose_pdf_importer_options', 'aspose-cloud-app-sid');
     register_setting('aspose_pdf_importer_options', 'aspose-cloud-app-key');

}

add_action('admin_init', 'AsposePdfImporterAdminRegisterSettings');


if (is_admin()) {
	// Include the file for loading plugin settings
	require_once('aspose_pdf_importer_admin.php');
}

