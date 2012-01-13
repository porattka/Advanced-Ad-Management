<?php 
    $adManage_plugin_data = Plugins::getInfo('advanced_ad_management/index.php');
    
    if(Params::getParam('addExistingAds') == 1){
    $conn = getConnection();
    $allItem = $conn->osc_dbFetchResults("SELECT DISTINCT * FROM %st_item_description", DB_TABLE_PREFIX);
     $dao_preference = new Preference(); 
    foreach($allItem as $itemB) {
        $r_secret = '';
        $r_secret = adManageRandomString();
        $conn->osc_dbExec("REPLACE INTO %st_item_adManage_limit (fk_i_item_id, r_secret, r_times) VALUES (%d, '%s', %d)", DB_TABLE_PREFIX, $itemB['fk_i_item_id'], $r_secret, 0 );
    }
    $dao_preference->update(array("s_value" => '1'), array("s_section" => "plugin-item_adManage", "s_name" => "adManageed_installed")) ;
    unset($dao_preference) ;
    echo '<script>location.href="' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=advanced_ad_management/admin.php"</script>';
    }// end of the addExistingAds section
      
    $expire_days            = '';
    $dao_preference = new Preference();
    if(Params::getParam('expire') != '') {
        $expire_days  = Params::getParam('expire');
    } else {
        $expire_days  = (osc_adManage_expire() != '') ? osc_adManage_expire() : '' ;
    }
    
    $payPost            = '';
    $dao_preference = new Preference();
    if(Params::getParam('payPost') != '') {
        $payPost  = Params::getParam('payPost');
    } else {
        $payPost  = (osc_adManage_payperpost() != '') ? osc_adManage_payperpost() : '' ;
    }
    
    $rTimes            = '';
    $dao_preference = new Preference();
    if(Params::getParam('rTimes') != '') {
        $rTimes  = Params::getParam('rTimes');
    } else {
        $rTimes  = (osc_adManage_repubTimes() != '') ? osc_adManage_repubTimes() : '' ;
    }
    
    $freeTimes            = '';
    $dao_preference = new Preference();
    if(Params::getParam('freeTimes') != '') {
        $freeTimes  = Params::getParam('freeTimes');
    } else {
        $freeTimes  = (osc_item_adManage_freeRepubs() != '') ? osc_item_adManage_freeRepubs() : '' ;
    }
    
    if( Params::getParam('option') == 'stepone' ) {
        $dao_preference->update(array("s_value" => $expire_days), array("s_section" => "plugin-item_adManage", "s_name" => "adManageed_expire")) ;
        $dao_preference->update(array("s_value" => $payPost), array("s_section" => "plugin-item_adManage", "s_name" => "adManageed_payperpost")) ;
        $dao_preference->update(array("s_value" => $rTimes), array("s_section" => "plugin-item_adManage", "s_name" => "adManageed_repubTimes")) ;
        $dao_preference->update(array("s_value" => $freeTimes), array("s_section" => "plugin-item_adManage", "s_name" => "adManageed_freeRepubs")) ;
        echo '<div style="text-align:center; font-size:22px; background-color:#00bb00;"><p>' . __('Settings Saved', 'adManage') . '.</p></div>';
    }
    unset($dao_preference) ;

?>

<form action="<?php osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="plugins" />
    <input type="hidden" name="action" value="renderplugin" />
    <input type="hidden" name="file" value="advanced_ad_management/admin.php" />
    <input type="hidden" name="option" value="stepone" />
    <fieldset>
    <h2><?php _e('Advanced Ad Management Configuration', 'adManage'); ?></h2> 
        <fieldset>
        <legend><?php _e('Step 1','adManage'); ?></legend>
        <?php if (!osc_item_adManage_installed() ==1) { 
               echo '<a href="' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=advanced_ad_management/admin.php?addExistingAds=1">' . __('Click here to finish the install','adManage') . '</a>';
          } else {_e('Done','adManage');} ?>
        </fieldset> 
        
        <fieldset>
        <legend><?php _e('Step 2','adManage'); ?></legend>
        <?php $conn = getConnection();
        $pCats = $conn->osc_dbFetchResults("SELECT * FROM %st_plugin_category WHERE s_plugin_name = '%s'", DB_TABLE_PREFIX, 'adManage');
        $catSet = count($pCats);
        if (($catSet < 1) && (osc_item_adManage_installed() ==1) ) {?> 
               <a href="<?php echo osc_admin_base_url(true) . '?page=plugins&action=configure&plugin=advanced_ad_management/index.php'; ?>" ><?php _e('Configure which categories you want your users to be able to adManage their ads in.','adManage'); ?></a>
        <?php } else { ?>
               <a href="<?php echo osc_admin_base_url(true) . '?page=plugins&action=configure&plugin=advanced_ad_management/index.php'; ?>" ><?php _e('Manage which categories you want your users to be able to adManage their ads in.','adManage'); ?></a> <br /><?php echo $catSet . ' ' . __('categoreies allow users to adManage ads','adManage');?>
              <?php } ?>
        </fieldset>
        
        <fieldset>
        <legend><?php echo _e('Item Republish Settings','adManage'); ?></legend>
        <?php if(osc_item_adManage_installed() ==1 && $catSet > 1) { ?>
        <label for="expire" style="font-weight: bold;"><?php _e('Number of days before item expires to send email?', 'adManage'); ?></label><br />
        <span style="font-size:small;color:gray;"><?php _e('Note: emails will only be sent on categories with <br />expiration days greater then 10', 'adManage'); ?>.</span><br />
        <input type="text" name="expire" value="<?php echo $expire_days; ?>" /> <?php _e('(default: 4)','adManage'); ?>
        <br />
        <br />
        <label for="rTimes" style="font-weight: bold;"><?php _e('Number of times an ad can be republished? (0 = unlimited)', 'adManage'); ?></label><br />
        <input type="text" name="rTimes" id="rTimes" value="<?php echo $rTimes; ?>" /><?php _e('(default: 5)','adManage');?>
        <br />
        <br />
        <fieldset>
        <legend><?php _e('Paypal republish settings','adManage'); ?></legend>
        <?php if(osc_adManage_paypalPaypost() == 1) { ?>
        <label for="payPost" style="font-weight: bold;"><?php _e('Require users to pay new item publish fee?', 'adManage'); ?></label>:<br />
        <select name="payPost" id="payPost"> 
        	<option <?php if($payPost == 1){echo 'selected="selected"';}?>value='1'>Yes</option>
        	<option <?php if($payPost == 0){echo 'selected="selected"';}?>value='0'>No</option>
        </select>
        <br />
        <br />
        <label for="freeTimes" style="font-weight: bold;"><?php _e('Number of free republishes before requiring fee to be paid?', 'adManage'); ?></label>:<br />
        <span style="font-size:small;color:gray;"><?php _e('Note: This number should be smaller then the "Number of times an <br />ad can be republished" unless it is set to zero.', 'adManage'); ?>.</span><br />
        <input type="text" name="freeTimes" id="freeTimes" value="<?php echo $freeTimes; ?>" /><?php _e('(0 = no free republishes)','adManage');?>
        <?php } else{ _e('Enable Paypal Pay Per Post option to see these settings','adManage');} ?>
        </fieldset>
        <input type="submit" value="<?php _e('Save', 'adManage'); ?>" /> 
        <?php } ?>    
        </fieldset>
        <?php echo __('Authors','adManage') . ' ' . '<a target="_blank" href="' . $adManage_plugin_data['plugin_uri'] . '">' . $adManage_plugin_data['author'] . '</a>'; ?>
    </fieldset> 
</form>

  