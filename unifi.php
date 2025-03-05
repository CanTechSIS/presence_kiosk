<?php

include_once __DIR__ . '/UniFi_API/UniFi_Client.php';
$config = include 'config.php';

// Unifi connection
$unifi_conn = new UniFi_Client(
    $config['unifi_username'],
    $config['unifi_password'],
    $config['unifi_serverurl'],
    $config['unifi_siteid'], 
    $config['unifi_version']
);

$unifi_conn->set_is_unifi_os(true);

function generateInternetCode($note) {
    global $unifi_conn;

    $set_debug_mode = $unifi_conn->set_debug(false);
    $loginresults   = $unifi_conn->login();

    // create the required number of vouchers with the requested expiration value
    $voucher_result = $unifi_conn->create_voucher(60*24, 1, 2, $note);

    // fetch the newly created vouchers by the create_time returned
    $vouchers = $unifi_conn->stat_voucher($voucher_result[0]->create_time);

    return $vouchers[0]->code;
}

?>