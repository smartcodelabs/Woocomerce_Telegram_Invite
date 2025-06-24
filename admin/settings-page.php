<?php

add_action('admin_menu', 'tig_register_settings_page');
add_action('admin_init', 'tig_register_settings');

function tig_register_settings_page()
{
    add_menu_page('Telegram Invite Generator', 'Telegram Invite Generator', 'manage_options', 'telegram-invite-generator', 'tig_settings_page', 'dashicons-paperclip');
}

function tig_register_settings()
{
    // Register main settings for auth token
    register_setting('tig_settings_group', 'tig_access_token');

    // Register separate settings for channels
    register_setting('tig_channels_group', 'tig_channels', 'sanitize_tig_channels');
}

function tig_settings_page()
{
?>
    <div class="wrap">
        <h1>Telegram Invite Generator</h1>
        <form method="post" action="options.php">
            <?php settings_fields('tig_settings_group'); ?>
            <?php do_settings_sections('tig_settings_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Auth Token</th>
                    <td><input type="text" name="tig_access_token" value="<?php echo esc_attr(get_option('tig_access_token')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <!-- Separate form for Channels -->
        <h2>Telegram Kanäle</h2>
        <form method="post" action="options.php">
            <?php settings_fields('tig_channels_group'); ?>
            <?php do_settings_sections('tig_channels_group'); ?>
            <table class="form-table" id="tig-channels-table">
                <tr valign="top">
                    <th scope="row">Kanal Name/ID</th>
                    <td>
                        <?php
                        $channels = get_option('tig_channels', []);
                        if (empty($channels)) {
                            $channels = ['' => ''];
                        }
                        foreach ($channels as $channel_name => $channel_id) {
                            echo '<div class="tig-channel-row" style="display: flex; align-items: center; margin-bottom: 10px;">';
                            echo '<input type="text" name="tig_channels_names[]" value="' . esc_attr($channel_name) . '" placeholder="Kanal Name" style="margin-right: 10px; width: 45%;" />';
                            echo '<input type="text" name="tig_channels_ids[]" value="' . esc_attr($channel_id) . '" placeholder="Kanal ID" style="width: 45%;" />';
                            echo '</div>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <button type="button" class="button" id="add-channel">Neuen Kanal hinzufügen</button>
            <?php submit_button(); ?>
        </form>

        <h2>Anleitung</h2>
        <p>Füge hier die Kanalnamen und IDs hinzu. Kanalnamen werden automatisch bearbeitet, um Leerzeichen durch Unterstriche zu ersetzen. Unerlaubte Zeichen werden durch einen Bindestrich ersetzt.</p>
    </div>

    <script type="text/javascript">
        document.getElementById('add-channel').addEventListener('click', function() {
            var table = document.getElementById('tig-channels-table');
            var newRow = document.createElement('div');
            newRow.classList.add('tig-channel-row');
            newRow.style.display = 'flex';
            newRow.style.alignItems = 'center';
            newRow.style.marginBottom = '10px';
            newRow.innerHTML = '<input type="text" name="tig_channels_names[]" placeholder="Kanal Name" style="margin-right: 10px; width: 45%;" /> <input type="text" name="tig_channels_ids[]" placeholder="Kanal ID" style="width: 45%;" />';
            table.appendChild(newRow);
        });
    </script>
<?php
}

add_action('pre_update_option_tig_channels', 'sanitize_tig_channels');
function sanitize_tig_channels($new_value)
{
    $cleaned_channels = [];
    if (!empty($_POST['tig_channels_names']) && !empty($_POST['tig_channels_ids'])) {
        foreach ($_POST['tig_channels_names'] as $index => $name) {
            $name = sanitize_text_field($name);
            $id = sanitize_text_field($_POST['tig_channels_ids'][$index]);

            // Sonderzeichen ersetzen, Leerzeichen durch Unterstriche ersetzen
            $name = preg_replace('/[^a-zA-Z0-9_]/', '-', $name);  // Sonderzeichen durch Bindestriche ersetzen
            $name = str_replace(' ', '_', $name);  // Leerzeichen durch Unterstriche ersetzen

            if (!empty($name) && !empty($id)) {
                $cleaned_channels[$name] = $id;
            }
        }
    }
    return $cleaned_channels;
}
?>