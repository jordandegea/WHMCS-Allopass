<?php

use WHMCS\Module\Gateway;
use WHMCS\Terminus;

include "../../../init.php";
include ROOTDIR . DIRECTORY_SEPARATOR . 'includes/functions.php';
include ROOTDIR . DIRECTORY_SEPARATOR . 'includes/gatewayfunctions.php';
include ROOTDIR . DIRECTORY_SEPARATOR . 'includes/invoicefunctions.php';

$gatewayModule = 'allopass';

$gateway = new Gateway();
if (!$gateway->isActiveGateway($gatewayModule) || !$gateway->load($gatewayModule)) {
    Terminus::getInstance()->doDie('Module not Active');
}

$GATEWAY = $gateway->getParams();

class SinencoCallbackAction {

    const NAME = "Allopass";

    private $admin = null;
    private $currencies = null;

    function SinencoCallbackAction() {
        
    }

    private function loadAdmin() {
        if ($this->admin != null) {
            return;
        }
        $result = mysql_query("SELECT username FROM tbladmins LIMIT 0,1");
        if (!$result) {
            die('Request Error: ' . mysql_error());
        }
        while ($row = mysql_fetch_assoc($result)) {
            $this->admin = $row["username"];
        }
        if ($this->admin == null) {
            die('Can\'t retrieve admin user');
        }
    }

    public function getInvoice($invoiceid) {
        $this->loadAdmin();
        $command = "getinvoice";
        $values["invoiceid"] = $invoiceid;


        $invoiceXML = localAPI($command, $values, $this->admin);

        if (!isset($invoiceXML["result"]) || $invoiceXML["result"] != "success") {
            echo "INVOICE_DOESNT_EXIST";
            die;
        }

        return $invoiceXML;
    }

    function getUserDetails($userid) {
        $this->loadAdmin();
        $command = "getclientsdetails";
        $values["clientid"] = $userid;
        $values["stats"] = true;
        $values["responsetype"] = "xml";

        $clientDetails = localAPI($command, $values, $this->admin);
        return $clientDetails["client"];
    }

    function getCurrencies() {
        $this->loadAdmin();
        if ($this->currencies == null) {
            $command = "getcurrencies";
            $currencies = localAPI($command, $values, $this->admin);
            $this->currencies = $currencies["currencies"];
        }
        return $this->currencies;
    }

    function addCredit($userid, $amount) {
        $this->loadAdmin();
        $command = "addcredit";
        $values["clientid"] = $userid;
        $values["description"] = "Adding funds via " . self::NAME;
        $values["amount"] = $amount;

        $results = localAPI($command, $values, $this->admin);
        return $results;
    }

    function applyCredit($invoiceid, $amount, $userBalance) {
        $this->loadAdmin();

        $command = "applycredit";
        $values["invoiceid"] = $invoiceid;
        $values["amount"] = ($userBalance < $amount) ? $userBalance : $amount;

        $results = localAPI($command, $values, $this->admin);
        return $results;
    }

    function convertAmountWithCurrencies($amount, $idTo, $codeFrom) {
        $this->loadAdmin();
        foreach ($this->getCurrencies() as $currencyRow) {
            if ($currencyRow["id"] == $idTo) {
                $invoice_currency = $currencyRow;
            }
            if ($currencyRow["code"] == $codeFrom) {
                $txn_currency = $currencyRow;
            }
        }

        if (isset($invoice_currency)) {
            if (isset($txn_currency)) {
                // convert
                $amount = round($amount * $txn_currency["rate"] / $invoice_currency["rate"], 2);
            } else {
                // !!problem, Transaction currency not found, writing the previous amount
            }
        } else {
            // !!problem, Invoice currency not found, writing the previous amount
        }
        return $amount;
    }
    
    function addLogContribution($user_id, $amount, $txn_id){
	$query = "INSERT INTO contribute_logs VALUE('','".$user_id."','".time()."','".$amount."','".$txn_id."')" ;
        $result = mysql_query($query);
        return $result;
    }

}


function checkCallback($datas, $currency, $amount, $txn_id) {
    $callbackAction = new SinencoCallbackAction();
    if (strstr($datas, "contribute")) {
        $userid = substr($datas, 11);
        $clientDetails = $callbackAction->getUserDetails($userid);
        $currency_id = $clientDetails["currency"];

        $finalAmount = $callbackAction->convertAmountWithCurrencies($amount, $currency_id, $currency);
        $callbackAction->addCredit($userid, $amount);
        $callbackAction->addLogContribution($userid, $amount, $txn_id);
    } else {
        $invoiceid = $datas;
        $invoiceDetails = $callbackAction->getInvoice($invoiceid);

        $userid = $invoiceDetails["userid"];
        $balance = $invoiceDetails["balance"];

        $clientDetails = $callbackAction->getUserDetails($userid);

        $currency_id = $clientDetails["currency"];

        $finalAmount = $callbackAction->convertAmountWithCurrencies($amount, $currency_id, $currency);

        $callbackAction->addCredit($userid, $amount);
        $callbackAction->applyCredit($invoiceid, $finalAmount, $balance);
    }
}

if (isset($_GET['action']) AND
        $_GET['action'] == "payment-confirm" AND
        isset($_GET['status_description']) AND
        $_GET['status_description'] == "success"
) {



    $date = substr($_GET['date'], 0, 10);
    $datas = $_GET['data'];
    $currency = $_GET['currency'];
    $amount = $_GET['amount'];
    $txn_id = $code = $_GET['code'];

    if ($_GET['test'] == true) {
        $amount = 0.5;
    }

    checkCallback($datas, $currency, $amount, $txn_id);

    echo 'OK';
} else {
    echo 'KO GET';
}
