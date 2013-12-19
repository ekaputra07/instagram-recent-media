<?php
// Add admin settings
function irm_admin_menu(){
    add_options_page( 'Instagram recent media', 'Instagram', 'manage_options', 'irm_settings', 'irm_settings');
}
add_action('admin_menu', 'irm_admin_menu');


// Render settings
function irm_settings(){

    if(isset($_POST['submit'])){
        $user_id = trim($_POST['user_id']);
        $client_id = trim($_POST['client_id']);
        update_option(IRM_UID_OPTION, $user_id);
        update_option(IRM_CLIENTID_OPTION, $client_id);

        irm_fetch_instagram();
    }else{
        $user_id = get_option(IRM_UID_OPTION);
        $client_id = get_option(IRM_CLIENTID_OPTION);
    }

    $user_data = get_option(IRM_UDATA_OPTION);
    ?>
    <div class="wrap">
    <div id="icon-options-general" class="icon32"><br></div><h2>Instagram settings</h2>
    <?php
    if(isset($_POST['submit'])){
        ?>
        <div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Settings saved.</strong></p></div>
        <?php
    }
    ?>
    <form name="irm_settings" method="post" action="">
    <table class="form-table">
    <tbody>
        <tr valign="top">
        <th scope="row"><strong>Instagram User ID</strong></th>
        <td><input name="user_id" id="user_id" value="<?php echo esc_attr($user_id);?>" class="regular-text ltr" type="text">
            <br><i>To know your Instagram user ID, use <a href="http://jelled.com/instagram/lookup-user-id" target="_blank">this tool</a>, just enter your username and you will get the user ID.
        </i>
        </td>
        </tr>

        <tr valign="top">
        <th scope="row"><strong>Instagram Client ID</strong></th>
        <td><input name="client_id" id="client_id" value="<?php echo esc_attr($client_id);?>" class="regular-text ltr" type="text">
        <br><i>You need to create an Instagram Client application <a href="http://instagram.com/developer/clients/manage/" target="_blank">here</a>, then you will get the client ID (don't use the client secret). <br>
        Note: When creating Instagram client, don't worry about the 'OAuth redirect_uri', just fill it with your website url.
        </i>
        </td>
        </tr>

        <tr valign="top">
        <th scope="row"></th>
        <td>
            <?php
            if($user_id && $client_id && count($user_data) > 0):
                foreach($user_data as $data){
                    echo '<a href="'.$data['link'].'" title="'.$data['caption']['text'].'" target="_blank"><img src="'.$data['images']['thumbnail']['url'].'" width="50"></a>';
                }
            endif;
            ?>
        </td>
        </tr>
    </tbody>
    </table>
    <p class="submit"><input name="submit" id="submit" class="button button-primary" value="Save Changes" type="submit"></p>
    </form>
    </div>
    <?php

}