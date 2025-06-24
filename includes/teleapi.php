<?php

function tig_generate_telegram_invite($access_code, $channel_id)
{
    if ($access_code === get_option('tig_access_token')) {
        $apiToken = get_option('tig_access_token');

        $date = new DateTime();
        $date->modify('+12 month');
        $data = array(
            'chat_id' => $channel_id,
            'expire_date' => $date->getTimestamp(),
            'creates_join_request' => true // Stellt sicher, dass eine Beitrittsanfrage gesendet wird

        );

        $url = "https://api.telegram.org/bot$apiToken/createChatInviteLink";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // POST-Daten übergeben
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        // Verbindung schließen
        curl_close($ch);

        // Antwort parsen
        // $response = json_decode($result, true);
        // die("STERBE WEIL = $response");

        $response = json_decode($result, true);
        // Überprüfen, ob die Antwort erfolgreich ist
        if ($response && isset($response['ok']) && $response['ok'] === true) {
            // Invite-Link zurückgeben
            return $response['result']['invite_link'];
        } else {
            return "Fehler beim Erstellen des Invite-Links = $response";
        }
    } else {
        return "Ungültiger Authcode";
    }
}



/**
 * Funktion, um die Kanal-ID basierend auf dem Kanalnamen zu erhalten
 */
function tig_get_channel_id_by_name($channel_name)
{

    // Holen Sie sich die gespeicherten Kanäle aus der Option
    $channels = get_option('tig_channels', []);

    // Kanalname formatieren (falls Leerzeichen etc. vorhanden sind)
    $channel_name = preg_replace('/[^a-zA-Z0-9_]/', '-', $channel_name);  // Sonderzeichen durch Bindestriche ersetzen
    $channel_name = str_replace(' ', '_', $channel_name);  // Leerzeichen durch Unterstriche ersetzen

    // Überprüfen, ob der Kanalname in den gespeicherten Kanälen vorhanden ist
    if (isset($channels[$channel_name])) {
        return $channels[$channel_name]; // Rückgabe der Kanal-ID
    } else {
        return false; // Kanalname nicht gefunden
    }
}
