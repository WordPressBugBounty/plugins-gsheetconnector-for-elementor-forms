<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
   exit();
}
$elementorForms_gs_tools_service = new GSC_Elementor_Init();
?>

<div class="system-statuswc">
   <div class="info-container">
    <h2 class="systemifo"><?php echo esc_html(__('System Info', 'gsheetconnector-for-elementor-forms')); ?></h2>
        <button onclick="copySystemInfo()" class="copy-system-info"><?php echo esc_html(__('Copy System Info to Clipboard', 'gsheetconnector-for-elementor-forms')); ?></button>
        <?php echo wp_kses_post($elementorForms_gs_tools_service->get_eleforms_system_info()); ?>
   </div>
</div>

<div class="system-Error">
    <div class="error-container">
        <h2 class="systemerror"><?php echo esc_html__( "Error Log", "gsheetconnector-for-elementor-forms" ); ?> </h2>
        <p>
          <?php echo esc_html__( "If you have", "gsheetconnector-for-elementor-forms" ); ?>
          <a href="https://www.gsheetconnector.com/how-to-enable-debugging-in-wordpress" target="_blank">
            <?php echo esc_html__( "WP_DEBUG_LOG", "gsheetconnector-for-elementor-forms" ); ?>
          </a>
          <?php echo esc_html__( "enabled, errors are stored in a log file. Here you can find the last 100 lines in reversed order so that you or the GSheetConnector support team can view it easily. The file cannot be edited here.", "gsheetconnector-for-elementor-forms" ); ?>
        </p>
        <button onclick="copyErrorLog()" class="copy-error-log"><?php echo esc_html__( "Copy Error Log to Clipboard", "gsheetconnector-for-elementor-forms" ); ?></button>
        <button class="wcgsc-clear-content-logs"><?php echo esc_html__( "Clear", "gsheetconnector-for-elementor-forms" ); ?></button>
        <input type="hidden" name="gs-ajax-nonce-ele" id="gs-ajax-nonce-ele" value="<?php echo esc_attr( wp_create_nonce( 'gs-ajax-nonce-ele' ) ); ?>" />
        <div class="copy-message" style="display: none;"><?php echo esc_html__( "Copied", "gsheetconnector-for-elementor-forms" ); ?></div>
        <?php echo wp_kses_post( $wc_free_system_log->display_error_log() ); ?>
    </div>
</div>

<?php  include( GS_CONN_ELE_ROOT . "/includes/pages/admin-footer.php" ) ;?>