<?php
// Funktion zum Generieren von Telegram-Einladungslinks
function tig_generate_telegram_invite_link($order_id)
{
    if (!isset($order_id) || !$order_id) {
        die("Keine Order ID!");
    }

    $order = wc_get_order($order_id);
    $billing_first_name = $order->get_billing_first_name(); // Vorname
    $billing_last_name = $order->get_billing_last_name();   // Nachname
    foreach ($order->get_items() as $item_id => $item) {



        $product = $item->get_product();
        // Überprüfen, ob das Produkt eine Variante ist
        if ($product->is_type('variation')) {
            // Hol das übergeordnete Produkt
            $parent_product = wc_get_product($product->get_parent_id());
            $tags = wp_get_post_terms($parent_product->get_id(), 'product_tag', array("fields" => "names"));
        } else {
            // Für einfache Produkte
            $tags = wp_get_post_terms($product->get_id(), 'product_tag', array("fields" => "names"));
        }
        $telegram_username = null;
        $laufzeit_string = null;
        $laufzeit_int = null; // Neue Variable für den numerischen Wert

        foreach ($item->get_meta_data() as $meta) {
            if ($meta->key === 'Telegram Username') {
                $telegram_username = $meta->value;
            }
            if (strtolower($meta->key) === 'dauer') {
                $laufzeit_string = $meta->value;

                // Verwende einen regulären Ausdruck, um den numerischen Teil zu extrahieren
                if (preg_match('/(\d+)/', $laufzeit_string, $matches)) {
                    $laufzeit_int = (int) $matches[1]; // Konvertiere den gefundenen Wert in einen Integer
                }
            }

            // Wenn beide Werte gefunden sind, brechen wir die Schleife ab
            if ($telegram_username !== null && $laufzeit_int !== null) {
                break;
            }
        }


        foreach ($tags as $tag) {

            // Prüfen, ob der Tag mit "telegram:" beginnt
            if (strpos($tag, 'telegram:') === 0) {
                // Kanalnamen extrahieren (alles nach "telegram:")
                $channel_name = str_replace('telegram:', '', $tag);

                // Telegram-Einladungslink generieren
                $invite_link = tig_generate_telegram_invite(get_option('tig_access_token'), tig_get_channel_id_by_name($channel_name));

                if ($invite_link) {
                    $order->add_order_note('Telegram Invite Link für ' . esc_html($channel_name) . ' (Benutzername: ' . esc_html($telegram_username) . '): ' . esc_url($invite_link));


                    //wp_mail($order->get_billing_email(), 'Ihr Telegram-Einladungslink für ' . esc_html($channel_name),  ' Hier ist Ihr Einladungslink: ' . esc_url($invite_link). " Laufzeit: $laufzeit_string ");
                    // Sende die E-Mail als HTML-E-Mail
                    $headers = array('Content-Type: text/html; charset=UTF-8');

                    wp_mail(
                        $order->get_billing_email(),
                        'Ihr Telegram-Einladungslink für ' . esc_html($channel_name),
                        generate_mail(esc_url($invite_link), $laufzeit_int, $billing_first_name),
                        $headers // Hier werden die Header übergeben
                    );
                    saveInviteToDatabasee(esc_html($telegram_username), esc_url($invite_link), tig_get_channel_id_by_name($channel_name), $laufzeit_int);
                } else {
                    $order->add_order_note('Fehler beim Generieren des Telegram-Einladungslinks für ' . esc_html($channel_name) . ' (Benutzername: ' . esc_html($telegram_username) . ').');
                }
            }
        }
    }
}
