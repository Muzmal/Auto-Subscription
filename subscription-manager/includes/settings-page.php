<div class="wrap">
    <?php
        if (isset($_GET['updated']) && $_GET['updated'] == 'true') {
            echo '<div class="notice notice-success is-dismissible"><p>Saved successfully!</p></div>';
        }
    ?>
    <form method="post" action="">
    <?php wp_nonce_field('myplugin_form_action', 'myplugin_form_nonce'); ?>
        <table class="form-table">
            <tr>
                <th scope="row">Remove subscription reminder messages</th>
                <td>
                <input type="checkbox" name="muz_sub_reminder" value="1" <?php checked( get_option( 'muz_sub_reminder', '0' ), '1' ); ?> />
                    <label for="muz_sub_reminder">Enable/Disable</label>
                </td>
            </tr>

            <tr>
                <th scope="row">Add subscription product to user after sign up</th>
                <td>
                    <input type="checkbox" id="muz_sub_add_pro" name="muz_sub_add_pro" value="1" <?php checked( get_option( 'muz_sub_add_pro', '0' ), '1' ); ?> />
                    <label for="muz_sub_add_pro">Enable/Disable</label>
                </td>
            </tr>
            <tr style="display:<?php echo get_option( 'muz_sub_add_pro') ? 'block' : 'none'; ?>"  class="muz-sub-pro">
                <th scope="row">Add Subscription Product ID </th> 
                <td>
                    <input type="text" name="muz_sub_add_pro_id" value="<?php echo get_option( 'muz_sub_add_pro_id'); ?>"  />
                    <label for="muz_sub_add_pro_id">Product type = subscription</label>
                </td>
            </tr>
        </table>

        <input type="submit" name="save_muz_sub_manager_setting" value="Save Changes" class="button-primary" />
    </form>
</div>
