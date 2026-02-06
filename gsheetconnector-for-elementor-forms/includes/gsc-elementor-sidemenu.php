<?php
use Elementor\Plugin;
use Elementor\Settings_Page;
use Elementor\Settings;
use Elementor\Utils;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class gsc_elementor_sidemenu extends Settings_Page {

    const PAGE_ID = 'gsheetconnector-elementor-config';

    public function __construct() {
        if (!Plugin::$instance->experiments->is_feature_active('admin_menu_rearrangement')) {
            add_action('admin_menu', [$this, 'register_admin_menu'], 100);
        }
    }

    protected function get_page_title() {
        return __('Google Sheet', 'gsheetconnector-for-elementor-forms');
    }

    public function register_admin_menu() {
        $sanitized_page_title = esc_html($this->get_page_title());

        add_submenu_page(
                Settings::PAGE_ID,
                $sanitized_page_title,
                $sanitized_page_title,
                'manage_options',
                self::PAGE_ID,
                [$this, 'display_settings_page']
        );
    }

    public function display_settings_page() {
        ?>
       <?php /*?> <div class="wrap"><h1 class="wp-heading-inline"><?php echo esc_html($this->get_page_title()); ?></h1></div>      <?php */?> 
        <?php
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe: tab selection used for UI only, no sensitive action
        $active_tab = ( isset($_GET['tab']) && sanitize_text_field(wp_unslash($_GET['tab'])) ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'integration';

        $active_tab_name = '';

if ($active_tab === 'integration') {
    $active_tab_name = esc_html( __('Integration', 'gsheetconnector-for-elementor-forms') );

} elseif ($active_tab === 'form_feed_settings') {
    $active_tab_name = esc_html( __('Form Feeds', 'gsheetconnector-for-elementor-forms') );

} elseif ($active_tab === 'System_Status') {
    $active_tab_name = esc_html( __('System Status', 'gsheetconnector-for-elementor-forms') );

} elseif ($active_tab === 'extensions') {
    $active_tab_name = esc_html( __('Extensions', 'gsheetconnector-for-elementor-forms') );
}


        $active_plugins = get_option('active_plugins');
        $parent_plugins_free1 = 'metform/metform.php';
        $met_active_plugins = in_array($parent_plugins_free1, $active_plugins) ? "true" : "false";

        $plugin_version = defined('GS_CONN_ELE_VERSION') ? GS_CONN_ELE_VERSION : 'N/A';
        ?>

        <div class="gsheet-header">
			<div class="gsheet-logo">
				<a href="https://www.gsheetconnector.com/"><i></i></a></div>
			<h1 class="gsheet-logo-text"><span><?php echo esc_html( __('GSheetConnector For Elementor Forms', 'gsheetconnector-for-elementor-forms' ) ); ?></span> <small><?php echo esc_html( __('Version :', 'gsheetconnector-for-elementor-forms' ) ); ?> <?php   echo esc_html($plugin_version, 'gsheetconnector-for-elementor-forms'); ?> </small></h1>
			
			
			<ul>
                <li><a href="<?php echo admin_url( 'admin.php?page=gsheetconnector-elementor-config&tab=extensions', 'gsheetconnector-gravityforms-pro' ); ?>" title="Extensions"> <i class="fa-solid fa-puzzle-piece"></i></a></li>
				<li><a href="https://wordpress.org/plugins/gsheetconnector-for-elementor-forms/" title="Document" target="_blank"><i class="fa-regular fa-file-lines"></i></a></li>
				<li><a href="https://www.gsheetconnector.com/support" title="Support" target="_blank"><i class="fa-regular fa-life-ring"></i></a></li>
				<li><a href="https://wordpress.org/plugins/gsheetconnector-for-elementor-forms/#developers" title="Changelog" target="_blank"><i class="fa-solid fa-bullhorn"></i></a></li>
			</ul>
			
			
		</div>
        <div class="breadcrumb">
             <span class="dashboard-gsc"><?php echo esc_html( __('DASHBOARD', 'gsheetconnector-for-elementor-forms' ) ); ?></span>
			<span class="divider-gsc"> / </span>
			<span class="modules-gsc"><?php echo esc_html($active_tab_name); ?></span>
        </div>
        
            <?php
           $tabs = array(
         'integration'        => esc_html__( 'Integration', 'gsheetconnector-for-elementor-forms' ),
         'form_feed_settings' => esc_html__( 'Form Feeds', 'gsheetconnector-for-elementor-forms' ),
         'System_Status'      => esc_html__( 'System Status', 'gsheetconnector-for-elementor-forms' ),
        'extensions'       => esc_html__( 'Extensions', 'gsheetconnector-for-elementor-forms' ),
       );


            echo '<div id="icon-themes" class="icon32"></div>';
            echo '<div class="nav-tab-wrapper">';
            foreach ($tabs as $tab => $name) {
                // Skip MetForm tab if plugin not active (assuming MetForm tab may be added later)
                if ($met_active_plugins === "false" && $name === __('MetForm Settings', 'gsheetconnector-for-elementor-forms')) {
                    continue;
                }

                $class = ($tab === $active_tab) ? ' nav-tab-active' : '';
                $url   = admin_url('admin.php?page=gsheetconnector-elementor-config&tab=' . urlencode($tab));

                echo '<a class="nav-tab' . esc_attr($class) . '" href="' . esc_url($url) . '">' . esc_html($name) . '</a>';
            }
            echo '</div><div class="wrap-gsc">';
			echo ' ';
			
            switch ($active_tab) {
                case 'integration':
                    include(GS_CONN_ELE_PATH . "includes/pages/gsc-integration.php");
                    break;
                case 'System_Status':
                    include(GS_CONN_ELE_PATH . "includes/pages/gsc-system-status.php");
                    break;
            case 'extensions' :
               include( GS_CONN_ELE_PATH . "includes/pages/extensions/extensions.php" );
             break; 
                case 'form_feed_settings':
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- External or internal link, no nonce available
                    if (isset($_GET['form_id']) && isset($_GET['feed_id'])) {
                        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- External or internal link, no nonce available
                        $form_id = intval($_GET['form_id']);
                        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- External or internal link, no nonce available
                        $feed_id = intval($_GET['feed_id']);
                        include(GS_CONN_ELE_PATH . "includes/pages/edit-sheet.php");
                    } else {
                        include(GS_CONN_ELE_PATH . "includes/pages/gsc-feed-google-sheet.php");
                    }
                    break;
            }
            ?>
        </div>
        <?php include( GS_CONN_ELE_PATH . "includes/pages/admin-footer.php" ) ;?>
  <?php
    }

    protected function create_tabs() {
        
    }

    public function get_elements_form_data( $form_data, $keyToFind ) {
        foreach ($form_data as $key => $value) {
            // If the key matches, return the value
            if ($key === $keyToFind) {
                return $value;
            }

            // If the value is an array, recurse into it
            if (is_array($value)) {
                $result = $this->get_elements_form_data($value, $keyToFind);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        // If the key is not found, return null
        return null;
    }

}

// Initialize the google sheet connector class
$gsc_elementor_sidemenu = new gsc_elementor_sidemenu();