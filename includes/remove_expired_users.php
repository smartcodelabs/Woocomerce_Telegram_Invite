<?php
add_action('tig_60min_event', 'removeExpiredSubscriptions');

// Funktion zum Entfernen eines Benutzers aus einer Telegram-Gruppe
function removeUserFromTelegram($chat_id, $user_id)
{
    $apiToken = get_option('tig_access_token');
    $url = "https://api.telegram.org/bot$apiToken/kickChatMember";
    $data = [
        'chat_id' => $chat_id,
        'user_id' => $user_id
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}


// Prüfen und Entfernen der abgelaufenen Abos
function removeExpiredSubscriptions()
{
    global $wpdb;  
    $currentDate = current_time('mysql'); 

    // Alle abgelaufenen Abos finden
    $expiredUsers = $wpdb->get_results($wpdb->prepare(
        "SELECT chat_id, user_id FROM {$wpdb->prefix}tig_invites WHERE expire_at < %s",
        $currentDate
    ));

    foreach ($expiredUsers as $user) {
        $chat_id = $user->chat_id;
        $user_id = $user->user_id;

        // Benutzer aus der Telegram-Gruppe entfernen
        $response = removeUserFromTelegram($chat_id, $user_id);

        // Erfolg oder Fehler loggen
        if ($response['ok']) {
            //echo "Benutzer $user_id erfolgreich aus Chat $chat_id entfernt.\n";

            // Benutzer aus der Datenbank entfernen
            $delete = $wpdb->delete(
                "{$wpdb->prefix}tig_invites",  // Tabellenname mit Präfix
                ['user_id' => $user_id, 'chat_id' => $chat_id],  // Bedingung
                ['%d', '%d']  // Datentypen für die Bedingung
            );

            if ($delete !== false) {
                //echo "Benutzer $user_id erfolgreich aus der Datenbank entfernt.\n";
            } else {
                //echo "Fehler beim Entfernen des Benutzers $user_id aus der Datenbank.\n";
            }

            $response = unbanUserFromTelegram($chat_id, $user_id);

            if ($response['ok']) {
                //echo "Benutzer $user_id erfolgreich wieder in Chat $chat_id freigegeben.\n";
            } else {
                //echo "Fehler beim Freigeben des Benutzers $user_id in Chat $chat_id: " . $response['description'] . "\n";
            }
        } else {
            //echo "Fehler beim Entfernen des Benutzers $user_id aus Chat $chat_id: " . $response['description'] . "\n";
        }
    }
}

// Funktion zum Freigeben eines Benutzers in einer Telegram-Gruppe
function unbanUserFromTelegram($chat_id, $user_id)
{
    $apiToken = get_option('tig_access_token');
    $url = "https://api.telegram.org/bot$apiToken/unbanChatMember";
    $data = [
        'chat_id' => $chat_id,
        'user_id' => $user_id
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}
