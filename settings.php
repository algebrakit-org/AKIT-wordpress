<?php

//Settings form for the AlgebraKiT plugin

function algebrakit_register_settings() {
    add_option( 'akit_api_key', '');
    add_option( 'akit_theme', 'akit');
    register_setting( 'algebrakit_options_group', 'akit_api_key' );
    register_setting( 'algebrakit_options_group', 'akit_theme' );
}

function algebrakit_options_page() {
?>
    <div>
        <?php screen_icon(); ?>
        <h2>AlgebraKiT Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'algebrakit_options_group' ); ?>
            <p>Here you can set the global settings for the AlgebraKiT plugin</p>
            <table>
                <tr valign="top">
                    <th scope="row"><label for="akit_api_key">API Key</label></th>
                    <td><input type="text" id="akit_api_key" name="akit_api_key" value="<?php echo get_option('akit_api_key'); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="akit_api_key">Theme</label></th>
                    <td><input type="text" id="akit_theme" name="akit_theme" value="<?php echo get_option('akit_theme'); ?>" /></td>
                </tr>
            </table>
            <?php  submit_button(); ?>
        </form>
    </div>
<?php
}

function algebrakit_register_options_page() {
    add_options_page('AlgebraKiT Settings', 'AlgebraKiT', 'manage_options', 'algebrakit', 'algebrakit_options_page');
}

add_action('admin_init', 'algebrakit_register_settings' );
add_action('admin_menu', 'algebrakit_register_options_page');
