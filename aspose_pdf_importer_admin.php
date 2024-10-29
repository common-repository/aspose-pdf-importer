<?php
require __DIR__.'/vendor/autoload.php';

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
 
/**
 * Create the admin menu for this plugin
 * @param no-param
 * @return no-return
 */
function AsposePdfImporterAdminMenu() {
     add_options_page('Aspose.PDF Importer', __('Aspose.PDF Importer', 'aspose-pdf-importer'), 'activate_plugins', 'AsposePdfImporterAdminMenu', 'AsposePdfImporterAdminContent');
}

add_action('admin_menu', 'AsposePdfImporterAdminMenu');


/**
 * Add the javascript for the plugin
 * @param no-param
 * @return string
 */
function AsposePdfUpdatedImporterEnqueueScripts() {

    wp_register_script( 'aspose_pdf_importer_script', plugins_url( 'js/aspose_pdf_importer.js', __FILE__ ), array('jquery') );

    $upload_path = wp_upload_dir();
    $params = array(
        'appSID'            => get_option('aspose-cloud-app-sid'),
        'appKey'            => get_option('aspose-cloud-app-key'),
        'uploadpath'        => $upload_path['path'],
        'uploadURI'         => $upload_path['url'],
        'insert_pdf_url'    => plugins_url( 'getAsposePdfContent.php', __FILE__ ),
        'aspose_files_url'    => plugins_url( 'getAsposeFiles.php', __FILE__ ),

    );
    wp_localize_script( 'aspose_pdf_importer_script', 'AsposePdfParams', $params );

    wp_enqueue_script( 'jquery-ui-dialog' );
    wp_enqueue_script( 'jquery-ui-tabs' );
    wp_enqueue_script( 'aspose_pdf_importer_script' );

    wp_register_style( 'AsposePdfImporterStyle', plugins_url( 'css/style.css', __FILE__), array(), '' );

    wp_enqueue_style( 'AsposePdfImporterStyle');
    wp_enqueue_style( 'jquery-ui-tabs');
    wp_enqueue_style( 'wp-jquery-ui-dialog');



}

add_action('init', 'AsposePdfUpdatedImporterEnqueueScripts');

// Defineing the Activator URL
if (!defined("ASPOSE_CLOUD_MARKETPLACE_ACTIVATOR_URL")) {
	define("ASPOSE_CLOUD_MARKETPLACE_ACTIVATOR_URL","https://activator.marketplace.aspose.cloud/activate");
}
// Setting up Secret key	
if(!get_option("aspose-cloud-activation-secret")){
	update_option("aspose-cloud-activation-secret", bin2hex(random_bytes(64)));						
}

/**
 * Pluing settings page
 * @param no-param
 * @return jwt based token
 */	
    function getToken() {
        if (!array_key_exists("token", $_REQUEST) || !get_option("aspose-cloud-activation-secret")) {
            return null;
        }
        try {
            $token = (new Parser())->parse($_REQUEST["token"]);
        } catch (Exception $x) {
            return null;
        }
        if (!($token->hasClaim("iss")) || $token->getClaim("iss") !== "https://activator.marketplace.aspose.cloud/") {
            return null;
        }
        $signer = new Sha256();
        $key = new Key(get_option("aspose-cloud-activation-secret"));
        if (!$token->verify($signer, $key)) {
            update_option("aspose-cloud-activation-secret", null);
            wp_die("Unable to verify token signature.");
        }
        return $token;
    }	
/**
 * Pluing settings page
 * @param no-param
 * @return no-return
 */
function AsposePdfImporterAdminContent() {

     // Creating the admin configuration interface
?>
<div class="wrap">
     <h2><?php echo __('Aspose.PDF Importer Options', 'aspose-pdf-importer');?></h2>
     <br class="clear" />
	
	<div class="metabox-holder has-right-sidebar" id="poststuff">
		<div class="inner-sidebar" id="side-info-column">
			<div class="meta-box-sortables ui-sortable" id="side-sortables">
				<div id="AsposePdfImporterOptions" class="postbox">
					<div title="Click to toggle" class="handlediv"><br /></div>
					<h3 class="hndle"><?php echo __('Support / Manual', 'aspose-pdf-importer'); ?></h3>
					<div class="inside">
						<p style="margin:15px 0px;"><?php echo __('For any suggestion / query / issue / requirement, please feel free to drop an email to', 'aspose-pdf-importer'); ?> <a href="mailto:marketplace@aspose.cloud?subject=Aspose.PDF Importer">marketplace@aspose.cloud</a>.</p>						
					</div>
				</div>

				<div id="AsposePdfImporterOptions" class="postbox">
					<div title="Click to toggle" class="handlediv"><br /></div>
					<h3 class="hndle"><?php echo __('Review', 'aspose-pdf-importer'); ?></h3>
					<div class="inside">
						<p style="margin:15px 0px;">
							<?php echo __('Please feel free to add your reviews on', 'aspose-pdf-importer'); ?> <a href="http://wordpress.org/support/view/plugin-reviews/aspose-pdf-importer" target="_blank"><?php echo __('Wordpress', 'aspose-pdf-importer');?></a>.</p>
						</p>

					</div>
				</div>
			</div>
		</div>

		<div id="post-body">
			<div id="post-body-content">
			                <div class="postbox">
                    <h3 class="hndle">aspose.cloud Subscription</h3>
                    <div class="inside">
                        <p>
						<?php if (array_key_exists("token", $_REQUEST) ){
								if (!(getToken()->hasClaim("aspose-cloud-app-sid")) || !(getToken()->hasClaim("aspose-cloud-app-key"))) {
									wp_die("The token has some invalid data");
								}
								update_option("aspose-cloud-app-sid", getToken()->getClaim("aspose-cloud-app-sid"));
								update_option("aspose-cloud-app-key", getToken()->getClaim("aspose-cloud-app-key"));
								update_option("aspose-cloud-activation-secret", null);
							}
							?>                            
                        </p>
                        <?php if (strlen(get_option("aspose-cloud-app-sid")) < 1): ?>
                            <p>
                                <a class="button-primary" href="<?php echo ASPOSE_CLOUD_MARKETPLACE_ACTIVATOR_URL; ?>?callback=<?php echo urlencode(site_url()."/wp-admin/options-general.php?page=AsposePdfImporterAdminMenu"); ?>&secret=<?php echo get_option("aspose-cloud-activation-secret"); ?>">
                                    <b>Enable FREE and Unlimited Access</b>
                                </a>
                            </p>
                            <p style="font-size: xx-small">
                                Your website URL
                                <i><?php echo site_url(); ?></i>
                                and admin email
                                <i><?php echo get_bloginfo("admin_email"); ?></i>
                                will be sent to
                                <i>aspose.cloud</i>
                                during the process.
                            </p>
                        <?php else: ?>
                            <h4>
                                <button disabled="disabled">FREE Unlimited Access is enabled</button>                                
                            </h4>
                            <p style="font-size: xx-small">
							App SID:<?php echo get_option("aspose-cloud-app-sid"); ?><br>
                                You can disable FREE Unlimited Access by deactivating/uninstalling the plugin.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
				<!--div id="WtiLikePostOptions" class="postbox">
					<h3><?php echo __('Configuration / Settings', 'aspose-pdf-importer'); ?></h3>

					<div class="inside">
						<!--form method="post" action="options.php">
							<?php settings_fields('aspose_pdf_importer_options'); ?>
							<table class="form-table">



                                <tr valign="top">
                                    <td colspan="2">
                                        <p> If you don't have an account with Aspose Cloud, <a target="_blank" href="https://dashboard.aspose.cloud/"> Click here </a> to Sign Up.</p>
                                    </td>

                                </tr>

                                <tr valign="top">
									<th scope="row"><label><?php _e('App SID', 'aspose-pdf-importer'); ?></label></th>
									<td>	
										<input type="text" size="40" name="aspose-cloud-app-sid" id="aspose-cloud-app-sid" value="<?php echo get_option('aspose-cloud-app-sid'); ?>" />
										<span class="description"><?php _e('Aspose for Cloud App sID.', 'aspose-pdf-importer');?></span>
									</td>
								</tr>

                                <tr valign="top">
                                    <th scope="row"><label><?php _e('App key', 'aspose-pdf-importer'); ?></label></th>
                                    <td>
                                        <input type="text" size="40" name="aspose-cloud-app-key" id="aspose-cloud-app-key" value="<?php echo get_option('aspose-cloud-app-key'); ?>" />
                                        <span class="description"><?php _e('Aspose for Cloud App Key.', 'aspose-pdf-importer');?></span>
                                    </td>
                                </tr>


								<tr valign="top">
									<th scope="row"></th>
									<td>
										<input class="button-primary" type="submit" name="Save" value="<?php _e('Save Options', 'aspose-pdf-importer'); ?>" />
										<input class="button-secondary" type="reset" name="Reset" value="<?php _e('Reset', 'aspose-pdf-importer'); ?>" />
									</td>
								</tr>
							</table>
						</form-->
					</div>
				</div-->
			</div>		
		</div>
<?php
}

// For adding button for Aspose Cloud PDF Importer
add_action('media_buttons_context',  'add_aspose_pdf_updated_importer_button');

function add_aspose_pdf_updated_importer_button($context){
    //path to my icon

    $context .= '<a id="aspose_pdf_popup" title = "Aspose.PDF Importer" class="button-primary">Aspose.PDF Importer</a>';

    return $context;
}

add_action( 'admin_footer',  'aspose_pdf_updated_add_inline_popup_content' );
function aspose_pdf_updated_add_inline_popup_content() {
    ?>
	<style type="text/css">
        .ui-widget-overlay {
            z-index:999 !important;
        }
    </style>
    <div id="aspose_pdf_popup_container" title="Aspose.PDF Importer" style="display: none">
        <p>
        <?php
        if( get_option('aspose-cloud-app-sid') == '' || get_option('aspose-cloud-app-key') == '') { ?>
            <h3 style="color:red">Please go to settings page and get the FREE access!</h3>
        <?php
        } else { ?>
            <div id="tabs">
                <ul>
                    <li><a href="#tabs-1">Please select PDF file</a></li>
                </ul>
                <div id="tabs-1">
                    <table>
                        <tr>
                            <td>
							
                                <?php
                                $image_library_url = get_upload_iframe_src( );
                                $image_library_url = remove_query_arg( array('TB_iframe'), $image_library_url );
                                $image_library_url = add_query_arg( array( 'context' => 'Aspose-Pdf-Importer-Select-File', 'TB_iframe' => 0 ), $image_library_url );
                                ?>

                                <p id="p_selectfile">
                                    <a title="Select PDF File" href="<?php echo esc_url( $image_library_url ); ?>" id="select-pdf-file" class="button thickbox">Select PDF File</a>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<input type="text" name="pdf_file_name" style="width:250px; margin-right:10px;" id="pdf_file_name" readonly value="" />
								<input type="hidden" id="pdf_file_url" />
								<input type="hidden" id="aspose_pdf_importer_name" />
								<input type="hidden" id="aspose_pdf_importer_version" />
							</td>
                            <td style="margin-left:10px; vertical-align: top;"> <input type="button" class="button-primary" id="insert_pdf_content" value="Insert PDF to Editor" /> </td>
                        </tr>


                    </table>
                </div>
                <div id="target"></div>
            </div>
        <?php
        } ?>
        </p>
    </div>

    <div class="modal"></div>

<?php
}



if (check_upload_aspose_pdf_updated_context('Aspose-Pdf-Importer-Select-File')) {

    add_filter('media_upload_tabs', 'aspose_pdf_updated_importer_image_tabs', 10, 1);
    add_filter('attachment_fields_to_edit', 'aspose_pdf_updated_importer_action_button', 20, 2);
    add_filter('media_send_to_editor', 'aspose_pdf_updated_importer_file_selected', 10, 3);
    add_filter('upload_mimes', 'aspose_pdf_updated_importer_upload_mimes');
}

function aspose_pdf_updated_importer_image_tabs($_default_tabs) {

    unset($_default_tabs['type_url']);
    return($_default_tabs);
}

function aspose_pdf_updated_importer_upload_mimes ( $existing_mimes=array() ) {

    $existing_mimes = array();
      $existing_mimes['pdf'] = 'application/pdf';
    return $existing_mimes;
}

function aspose_pdf_updated_importer_action_button($form_fields, $post) {

    $send = "<input type='submit' class='button-primary' name='send[$post->ID]' value='" . esc_attr__( 'Use this PDF File For Importing' ) . "' />";

    $form_fields['buttons'] = array('tr' => "\t\t<tr class='submit'><td></td><td class='savesend'>$send</td></tr>\n");
    $form_fields['context'] = array( 'input' => 'hidden', 'value' => 'Aspose-Pdf-Importer-Select-File' );
    return $form_fields;
}


function aspose_pdf_updated_importer_file_selected($html, $send_id) {

    $file_url = wp_get_attachment_url($send_id);
    $file_name = basename($file_url);
	
	if( !function_exists('get_plugin_data') ){
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	$plugin_data = get_plugin_data(ASPOSE_PDF_IMPORTER_PLUGIN_FILE);
	
    ?>
    <script type="text/javascript">
        /* <![CDATA[ */
        var win = window.dialogArguments || opener || parent || top;

        win.jQuery( '#pdf_file_name' ).val('<?php echo $file_name;?>');
		win.jQuery( '#pdf_file_url' ).val('<?php echo $file_url;?>');
		win.jQuery( '#aspose_pdf_importer_name' ).val('<?php echo $plugin_data["Name"];?>');
		win.jQuery( '#aspose_pdf_importer_version' ).val('<?php echo $plugin_data["Version"];?>');
		window.top.document.getElementById('TB_window').style.display = 'none';
		window.top.document.getElementById('TB_overlay').style.display = 'none';

    </script>
    <?php
    return '';
}

function add_aspose_pdf_updated_context_to_url($url, $type) {
    //if ($type != 'image') return $url;
    if (isset($_REQUEST['context'])) {
        $url = add_query_arg('context', $_REQUEST['context'], $url);
    }
    return $url;
}


function check_upload_aspose_pdf_updated_context($context) {
    if (isset($_REQUEST['context']) && $_REQUEST['context'] == $context) {
        add_filter('media_upload_form_url', 'add_aspose_pdf_updated_context_to_url', 10, 2);
        return TRUE;
    }
    return FALSE;
}



//Register Meta Box
function rm_register_meta_box_pdf_exp_importer() {

    $post_types = array ( 'post', 'page' );
        if(is_gutenberg_page() && (!is_meta_box_registered('rm-meta-box-id','post'))){
        add_meta_box( 'rm-meta-box-id', esc_html__( 'Aspose.PDF Importer', 'text-domain' ), 'rm_meta_box_pdf_exp_callback', $post_types, 'side', 'high' );
    }
}
add_action( 'add_meta_boxes', 'rm_register_meta_box_pdf_exp_importer');

//Add field
function rm_meta_box_pdf_exp_callback( $meta_id ) {

    $outline = '<a id="aspose_pdf_popup" title = "Aspose.PDF Importer" class="button-primary">Aspose.PDF Importer</a>';

    echo $outline;
}

function load_custom_wp_admin_style_pdf_exp_importer() {
    if(is_gutenberg_page()){
        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');
    }
}
add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style_pdf_exp_importer' );

if ( ! function_exists( 'is_gutenberg_page' ) ) :
function is_gutenberg_page() {
    $current_screen = get_current_screen();
    if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor()) {
        // Gutenberg page on 5+.
        return true;
    }
    return false;
}
endif;

if ( ! function_exists( 'is_meta_box_registered' ) ) :
function is_meta_box_registered( $meta_box_id, $post_type = false ){
    global $wp_meta_boxes, $grist_meta_box_found;
    $grist_meta_box_found = false; // assume not found by default
    // if meta boxes are not yet set up, let's issue an error
    if( empty( $wp_meta_boxes ) || ! is_array( $wp_meta_boxes ) )
        return new WP_Error( 'missing-meta-boxes', 'global $wp_meta_boxes is not the expected array' );
    // should we only look at meta boxes for a specific post type?
    if ( $post_type )
        $meta_boxes = $wp_meta_boxes[ $post_type ];
    else
        $meta_boxes = $wp_meta_boxes ;
    // step through each meta box registration and check if the supplied id exists
    array_walk_recursive( $meta_boxes, function( $value, $key, $meta ){
        global $grist_meta_box_found;
        if ( $key === 'id' && strtolower( $value ) === strtolower( $meta )  )
            $grist_meta_box_found = true;
    }, $meta_box_id );
    $return = $grist_meta_box_found; // temp store the return value
    unset( $grist_meta_box_found );  // remove var from from global space
    return $return;
}
endif;