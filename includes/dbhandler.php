<?php
// Datenbank initialisieren
function initDB()
{
    global $wpdb;
    return $wpdb;
}

// Installationsfunktion für die Tabelle
function tig_install()
{
    global $wpdb;

    $table_name = $wpdb->prefix . "tig_invites";
    $charset_collate = $wpdb->get_charset_collate();

    // SQL-Befehl zum Erstellen der Tabelle
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        chat_id BIGINT(20) NOT NULL,
        chat_name VARCHAR(255) NOT NULL,
        user_id BIGINT(20) NOT NULL,
        user_firstname VARCHAR(255) NOT NULL,
        invite_link TEXT NOT NULL,
        creator_id BIGINT(20) NOT NULL,
        creator_name VARCHAR(255) NOT NULL,
        creator_is_bot TINYINT(1) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expire_at TIMESTAMP NOT NULL,
        PRIMARY KEY (user_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Überprüfen, ob die Tabelle erfolgreich erstellt wurde
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
        return true; // Erfolgreich
    } else {
        return false; // Fehler
    }
}


// Funktion zum Speichern der JSON-Daten in der Datenbank
function save_invite_data($json_data)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'tig_invites';

    // JSON-Daten parsen
    $data = json_decode($json_data, true);

    if (isset($data['chat_join_request'])) {
        $chat = $data['chat_join_request']['chat'];
        $from = $data['chat_join_request']['from'];
        $invite_link = $data['chat_join_request']['invite_link'];
        $creator = $invite_link['creator'];

        $chat_id = $chat['id'];
        $chat_name = $chat['title'];
        $user_id = $from['id'];
        $user_firstname = $from['first_name'];
        $invite_link_url = str_replace('\\', '', $invite_link['invite_link']);
        $creator_id = $creator['id'];
        $creator_name = $creator['first_name'] . ' ' . ($creator['last_name'] ?? '');
        $creator_is_bot = $creator['is_bot'];
        $expire_at = date("Y-m-d H:i:s", $invite_link['expire_date']);

        // Daten in die Datenbank einfügen
        $wpdb->replace($table_name, [
            'chat_id' => $chat_id,
            'chat_name' => $chat_name,
            'user_id' => $user_id,
            'user_firstname' => $user_firstname,
            'invite_link' => $invite_link_url,
            'creator_id' => $creator_id,
            'creator_name' => $creator_name,
            'creator_is_bot' => $creator_is_bot,
            'expire_at' => $expire_at,
        ]);
    }
}


//Eintragung beim Kauf vom Shop in die DB
function saveInviteToDatabasee($username, $invite_link, $chat_id, $month)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'tig_invites';  // Tabelle mit Prefix

    // Validierung des Benutzernamens und der Chat-ID, um SQL-Injection zu verhindern
    $username = sanitize_text_field($username); 
    $chat_id = sanitize_text_field($chat_id);

    // Überprüfen, ob der Benutzer bereits existiert
    $existing_invite = $wpdb->get_row($wpdb->prepare(
        "SELECT expire_at FROM $table_name WHERE user_firstname = %s AND chat_id = %s",
        $username,
        $chat_id
    ));


    if ($existing_invite) {
        // Benutzer existiert, Ablaufdatum verlängern
        $current_expire_date = new DateTime($existing_invite->expire_at);
        $current_date = new DateTime();

        // Falls das Ablaufdatum in der Vergangenheit liegt, setze es auf das aktuelle Datum
        if ($current_expire_date < $current_date) {
            $current_expire_date = $current_date;
        }

        // Addiere die neuen Monate zum aktuellen Ablaufdatum
        $new_expire_date = $current_expire_date->modify('+' . $month . ' months')->format('Y-m-d H:i:s');

        // Aktualisiere den Eintrag in der Datenbank
        $wpdb->update(
            $table_name,
            [
                'invite_link' => $invite_link,
                'expire_at'   => $new_expire_date
            ],
            [
                'user_firstname' => $username,
                'chat_id' => $chat_id
            ],
            ['%s', '%s'], // Datentypen für die neuen Werte
            ['%s', '%s']       // Datentyp für die WHERE-Bedingung
        );
    } else {
        // Neuer Benutzer, Eintrag erstellen mit dem Ablaufdatum + Monate
        $expire_date = (new DateTime())->modify('+' . $month . ' months')->format('Y-m-d H:i:s');

        $wpdb->insert(
            $table_name,
            [
                'user_firstname'    => $username,
                'invite_link' => $invite_link,
                'expire_at'   => $expire_date,
                'chat_id'   => $chat_id,
            ],
            ['%s', '%s', '%s', '%s'] 
        );
    }
}
