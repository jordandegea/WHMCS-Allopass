<?php

function allopass_config() {
    $configarray = array(
        'name' => 'Allopass Addon',
        'description' => 'Use this addon with the gateway payment',
        'version' => '2.0', 'author' => 'Sinenco', 'fields' => array());
    return $configarray;
}

function allopass_activate() {
    return;
}

function allopass_deactivate() {
    return;
}

function allopass_clientarea($vars) {
    global $whmcs;

    if (isset($_GET['DATAS']) AND ! isset($_GET['action'])) {
        $datas = $_GET['DATAS'];
        /* Allopass can be use with the other product named contribution */
        if (strstr($datas, "contribute")) {
            $url = $whmcs->get_config("SystemURL") . '/index.php?m=contribute&account=' . substr($datas, 11);
        } else {
            $url = $whmcs->get_config("SystemURL") . '/viewinvoice.php?id=' . $datas;
        }

        return array('pagetitle' => Lang::trans("allopass_payment_complete"),
            'breadcrumb' => array(
                'index.php?m=allopass' => Lang::trans('allopass_payment')
            ),
            'templatefile' => 'allopass_client',
            'requirelogin' => false,
            'vars' => array(
                'url' => $url,
                '_lang' => $vars['_lang']
            )
        );
    }
}

function allopass_output($vars) {
    return;
}

