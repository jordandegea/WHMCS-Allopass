<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function allopass_MetaData() {
    return array(
        'DisplayName' => 'Allopass Payment',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false
    );
}

function allopass_config() {
    $configarray = array(
        "FriendlyName" => array("Type" => "System", "Value" => "Allopass"),
        "ids" => array("FriendlyName" => "Allopass ID Site", "Type" => "text", "Size" => "20",),
        "idp" => array("FriendlyName" => "Allopass ID Produit", "Type" => "text", "Size" => "20",),
    );
    return $configarray;
}

function allopass_link($params) {

    global $whmcs;

    $ids = $params['ids'];
    $idp = $params['idp'];
    $gatewaymodule = $params['FriendlyName'];
    $gatewaywallet = $params['ok_wallet'];
    $invoiceid = $params['invoiceid'];

    $code = '';
    $url = "https://payment.allopass.com/buy/buy.apu";

    $code = '<form action="' . $url . '" method="GET">'
            . '<input type="submit" value="'.Lang::trans('invoicespaynow').'" />'
            . '<input type="hidden" name="ids" value="' . $ids . '" />'
            . '<input type="hidden" name="idd" value="' . $idp . '" />'
            . '<input type="hidden" name="data" value="' . $invoiceid . '" />'
            . '</form>';

    return $code;
}
