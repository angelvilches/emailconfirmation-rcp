<?php
/*
Plugin Name: Email Confirmation for Restrict Content Pro
Plugin URI: https://github.com/angelvilches/emailconfirmation-rcp
Description: This plugin allow you to add a confirmation email field for Restrict Content Pro register form and use the email as username.
Version: 1.0
Author: Ángel Vilches
Author URI: https://angelvilches.com
License: GPLv2 or later
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  emailconfirmation-rcp
Domain Path:  /languages
*/

if (!defined('ABSPATH'))
	exit;

if (!defined('EMAILCONFIRMATION_RCP_TEXTDOMAIN'))
	define('EMAILCONFIRMATION_RCP_TEXTDOMAIN', 'av_ecrcp');

add_action( 'admin_init', 'av_ecrcp_child_plugin_has_parent_plugin' );
	function av_ecrcp_child_plugin_has_parent_plugin() {
		if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'restrict-content-pro/restrict-content-pro.php' ) ) {
			add_action( 'admin_notices', 'av_ecrcp_child_plugin_notice' );
	
			deactivate_plugins( plugin_basename( __FILE__ ) ); 
	
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}
	
	function av_ecrcp_child_plugin_notice(){
		?><div class="error"><p><?php __('Sorry, but Email confirmation for Restrict Content Pro requires Restrict Content Pro plugin to be installed and active.','emailconfirmation-rcp');?></p></div>
		<?php
	}

//Archivo de idioma
//Language file
add_action('plugins_loaded', 'av_ecrcp_load_textdomain');

	function av_ecrcp_load_textdomain() {
		
		$text_domain	= 'emailconfirmation-rcp';
		$path_languages = basename(dirname(__FILE__)).'/languages/';
	
		 load_plugin_textdomain($text_domain, false, $path_languages );
	}

//Carga el archivo con los estilos css
//Load the file with css.
add_action('wp_enqueue_scripts', 'av_ecrcp_cargacss');
function av_ecrcp_cargacss()
{
	wp_enqueue_style('av_cargacss', plugin_dir_url(__FILE__) . 'css/emailconfirmation-rcp.css');
}


//Añadimos un campo de email

function av_ecrcp_add_email() {

    $alternate_email = get_user_meta( get_current_user_id(), 'rcp_alt_email', true );
    ?>
    <p>
        <label for="rcp_alt_email"><?php _e( 'Email', 'emailconfirmation-rcp' ); ?></label>
        <input type="email" id="rcp_alt_email" name="rcp_alt_email" value="<?php echo esc_attr( $alternate_email ); ?>"/>
    </p>

    <?php
}
add_action( 'rcp_before_register_form_fields', 'av_ecrcp_add_email' );


/**
 * Valida si los emails de ambos campos son idénticos
 */
function av_ecrcp_validate_email( $posted ) {

    if ( is_user_logged_in() ) {
        return;
    }

    // Si no coincide con el email del campo rcp_user_email, da error
    if (  $posted['rcp_alt_email']  !== $posted['rcp_user_email'] ) {
		rcp_errors()->add( 'invalid_alt_email', __( 'Your emails do not match', 'emailconfirmation-rcp' ), 'register' );
    }

}
add_action( 'rcp_form_errors', 'av_ecrcp_validate_email', 10 );
