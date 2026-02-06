<?php
   // Get the verification and token options.
        $elefgs_verify = get_option('elefgs_verify');
        $elefgs_token = get_option('elefgs_token');
        $elefgs_token_manual = get_option('elefgs_token_manual');
        $elefgs_manual_setting = get_option('elefgs_manual_setting');
        $selected_method =''; 
        if($elefgs_manual_setting == 0){
        $selected_method = __('Use Existing Client/Secret Key (Auto Google API Configuration)', 'gsheetconnector-for-elementor-forms');
        }
        elseif($elefgs_manual_setting == 1){
        $selected_method = __('Use Manual Client/Secret Key (Use Your Google API Configuration)', 'gsheetconnector-for-elementor-forms');
        }
      $show_setting = 0;
        // check user is authenticated when save existing api method
      if ((!empty($elefgs_token) && $elefgs_verify == 'valid' && $elefgs_manual_setting == 0) ) {
        $show_setting = 1;
    }
    //check user is authenticated when save manual api method
    elseif(!empty($elefgs_token_manual) && $elefgs_verify == 'valid' && $elefgs_manual_setting == 1){
       $show_setting = 1;
      }
      else{
        echo "<p class='elementor-gs-display-note'>".__('<strong>Make Sure, your selected Method is:</strong>  '.$selected_method.'<br><br> <strong>Authentication Required:</strong>
                  You must have to <a href="admin.php?page=gsheetconnector-elementor-config&tab=integration" target="_blank">Authenticate using your Google Account</a> along with Google Drive and Google Sheets Permissions in order to enable the settings for configuration.</p>', 'gsheetconnector-for-elementor-forms')."</h3>";

 }
      

if( $show_setting == 1 ){
     $forms = get_posts(array(
         'post_type' => 'metform-form',
         'numberposts' => -1
      ));
      ?>
<div class="mf-formSelect">
    <h3><?php echo __('Select Form', 'gsheetconnector-for-elementor-forms'); ?></h3>
</div>
<div class="mf-select">
    <select id="metforms_select" name="metforms">
        <option value=""><?php echo __('Select Form', 'gsheetconnector-for-elementor-forms'); ?></option>
        <?php 
     if(!empty($forms)){
        foreach ($forms as $form) { ?>
        <option value="<?php echo $form->ID; ?>"><?php echo $form->post_title; ?></option>
        <?php } 
           }
        ?>
    </select>
    <span class="mf-loading-sign-select">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
    <input type="hidden" name="wp-ajax-nonce" id="wp-ajax-nonce" value="<?php echo wp_create_nonce('wp-ajax-nonce'); ?>" />
</div>
<div class="wrap gs-form">
    <div class="wp-parts">

        <div class="card" id="metform-gs">
            <form method="post">
                <h2 class="title"><?php echo __('MetForm - Google Sheet Settings', 'gsheetconnector-for-elementor-forms'); ?>
                </h2>
               <hr class="divide">
                  <br class="clear">

                  <div id="inside">

                  </div>

               
            </form>
        </div>

    </div>
</div>

 <?php  include( GS_CONN_ELE_ROOT . "/includes/pages/admin-footer.php" ) ;?>
<?php } 
  ?>