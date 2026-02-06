<?php
/*
 * Elementor Forms Google sheet connector Dashboard Widget
 * @since 1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
   exit();
}
?>
<div class="dashboard-content">
   <?php
   $elementorgs_connector_service = new GSC_Elementor_Integration();
   $forms_list = $elementorgs_connector_service->get_forms_connected_to_sheet();
   $forms_feeds_list = $elementorgs_connector_service->get_forms_feeds_connected_to_sheet();


   ?>
   <div class="main-content">
      <div>
         <h3><?php echo esc_html__("Elementor Forms Connected Sheets using Pagebuilder", 'gsheetconnector-for-elementor-forms'); ?></h3>          
          <style>
              .widget-table { border:1px solid #eee; width:100%; }
              .widget-table th { text-align: left; background: #eee; padding: 2px 3px; border-bottom: 1px solid #eee; }
              .widget-table td { text-align: left; background: #fff; padding: 2px 3px; word-wrap: break-word; }
              .widget-table td:nth-child(1) {width:50%;}
          </style>
          
          <table class="widget-table">
    <tbody>
        <tr>
            <th>Form Name</th>
            <th>Sheet URL</th>
        </tr>
        
        <?php
        if (!empty($forms_list)) {
            $i = 1;
            foreach ($forms_list as $form_key => $form_value) { // Renamed to avoid confusion with inner loop
                if (!empty($form_value->ID)) {
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo esc_url(admin_url('post.php?post=' . $form_value->ID . '&action=elementor')); ?>" target="_blank">
                                <?php echo esc_html($form_value->post_title); ?>
                            </a>
                        </td>
                        <td>
                            <?php 
                            // Check if there are form feeds for this form
                            if (!empty($forms_feeds_list)) {
                                foreach ($forms_feeds_list as $feed_key => $feed_value) { // Renamed variable for clarity
                                    $form_id = $feed_value->ID;
                                    $feed_id = !empty($feed_value->meta_id) ? $feed_value->meta_id : '';
                                    
                                    if (!empty($feed_id)) {
                                        $feed_data = get_post_meta($feed_id, 'gscele_form_feeds', true);
                                        $sheet_id = isset($feed_data['sheet-id']) ? esc_attr($feed_data['sheet-id']) : '';
                                        $tab_id = isset($feed_data['tab-id']) ? esc_attr($feed_data['tab-id']) : '';
                                        ?>
                                        
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=gsheetconnector-elementor-config&tab=form_feed_settings&form_id=' . $form_id . '&feed_id=' . $feed_id)); ?>" target="_blank">
                                            <?php echo esc_html($feed_value->meta_key); ?>
                                        </a> -- 
                                        <a href="<?php echo esc_url('https://docs.google.com/spreadsheets/d/' . rawurlencode($sheet_id) . '/edit#gid=' . rawurlencode($tab_id)); ?>" target="_blank">
                                            <?php echo esc_html__("Sheetlink", 'gsheetconnector-for-elementor-forms'); ?>
                                        </a>
                                        <br>
                                        <?php
                                    }
                                }
                            } else {
                                esc_html__("No Sheets are connected.", 'gsheetconnector-for-elementor-forms');
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                } else {
                    ?>
                    <tr>
                        <td colspan="2"><?php echo esc_html__("No Elementor Forms are connected with Google Sheets.", 'gsheetconnector-for-elementor-forms'); ?></td>
                    </tr>
                    <?php
                }
                $i++;
            }
        } else {
            ?>
            <tr>
                <td colspan="2"><?php esc_html__("No Elementor Forms are connected with Google Sheets.", 'gsheetconnector-for-elementor-forms'); ?></td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
 </div>
   </div> <!-- main-content end -->
</div> <!-- dashboard-content end -->
<style type="text/css">
.postbox-header .hndle {
justify-content: flex-start !important;
}
</style>