<?php
class HGVerwaltungAdmin
{

    static function addAdminLinksToPlugin($links)
    {
        $links = array_merge(array(
            '<a href="' . esc_url(admin_url('/options-general.php?page=hgv_options')) . '">' . __('Settings', 'textdomain') . '</a>'
        ), $links);
        return $links;
    }
    static function addAdminPages()
    {
        add_options_page('HgVerwaltung Options', 'HG Verwaltung', 'manage_options', 'hgv_options', array("HGVerwaltungAdmin", 'plugin_options'));
    }
    static function plugin_options()
    {
        $plugin_name = "wp-hgverwaltung";
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        $connectionIsPossible = @file_get_contents("https://www.hgverwaltung.ch/api/1/" . get_option("wpv_code", "test") . "/mannschaften");
        $plugin_options_nonce = wp_create_nonce('hgv_plugin_options_nonce_form');
        if (isset($_GET["success"]) && strlen($connectionIsPossible) > 2) { ?>
            <div class="updated">
                <p><?php esc_html_e('Die Einstellungen wurden erfolgreich übernommen. ', 'text-domain'); ?></p>
            </div>
        <?php
        } else {
        ?>
            <div class="error">
                <p><?php esc_html_e('Die Einstellungen wurden übernommen, jedoch konnten keine Daten empfangen werden. Überprüfe bitte den Club Code.', 'text-domain'); ?></p>
            </div>
        <?php
        }
        if ($connectionIsPossible === FALSE) { ?>
            <div class="error">
                <p><?php esc_html_e('Es konnte keine Verbindung zur HG Verwaltung aufgebaut werden..', 'text-domain'); ?></p>
            </div>
        <?php
        }
        ?>
        <div class="wrap">
            <h1>Hornusser Verwaltung</h1>
            <p>Hier muss der Code eingegeben werden.</p>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="hgV-wpvCode" method="post">
                <input type="hidden" name="action" value="hgV_wpvCode">
                <input type="hidden" name="hgv_plugin_options_nonce" value="<?php echo $plugin_options_nonce ?>" />
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="<?php echo $plugin_name; ?>-club_webcode"> <?php _e('Bitte Club Code eingeben:', $plugin_name); ?> </label><br>
                        </th>
                        <td>
                            <input required id="<?php echo $plugin_name; ?>-club_webcode" type="text" name="hgv[club_webcode]" value="<?php echo (get_option("wpv_code")); ?>" placeholder="<?php _e('Club Web Code', $plugin_name); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Date Format') ?></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span><?php _e('Date Format') ?></span></legend>
                                <?php
                                $date_formats = array_unique(apply_filters('date_formats', array(__('F j, Y'), 'd.m.Y', 'd/m/Y', 'd. M Y')));

                                $custom = true;

                                foreach ($date_formats as $format) {
                                    echo "\t<label><input type='radio' name='hgv[date_format]' value='" . esc_attr($format) . "'";
                                    if (get_option('wpv_date_format') === $format) { // checked() uses "==" rather than "==="
                                        echo " checked='checked'";
                                        $custom = false;
                                    }
                                    echo ' /> <span class="date-time-text format-i18n">' . date_i18n($format) . '</span><code>' . esc_html($format) . "</code></label><br />\n";
                                }

                                echo '<label><input type="radio" name="hgv[date_format]" id="date_format_custom_radio" value="custom"';
                                checked($custom);
                                echo '/> <span class="date-time-text date-time-custom-text">' . __('Custom:') . '<span class="screen-reader-text"> ' . __('enter a custom date format in the following field') . '</span></span></label>' .
                                    '<label for="date_format_custom" class="screen-reader-text">' . __('Custom date format:') . '</label>' .
                                    '<input type="text" name="hgv[date_format_custom]" id="date_format_custom" value="' . esc_attr(get_option('wpv_date_format')) . '" />' .
                                    '<br />' .
                                    '<p><strong>' . __('Preview:') . '</strong> <span class="example">' . date_i18n(get_option('wpv_date_format')) . '</span>' .
                                    "<span class='spinner'></span>\n" . '</p>';
                                ?>
                            </fieldset>
                        </td>
                    </tr>
                </table>
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo _e("Speichern"); ?>"></p>
            </form>
        </div>
<?php
    }
    static function hgV_wpvCode_response($response)
    {
        if (isset($_POST['hgv_plugin_options_nonce']) && wp_verify_nonce($_POST['hgv_plugin_options_nonce'], 'hgv_plugin_options_nonce_form')) {
            $hgv_Webcode = sanitize_key($_POST['hgv']['club_webcode']);
            update_option("wpv_code", $hgv_Webcode);
            $hgv_date_format = sanitize_option("date_format", $_POST['hgv']['date_format']);
            if (!$hgv_date_format == "costum") {
                $hgv_date_format = sanitize_option("date_format", $_POST['hgv']['date_format']);
            }
            update_option("wpv_date_format", $hgv_date_format);
            wp_redirect(admin_url('/options-general.php?page=hgv_options&success'));
        }
    }
}
