<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Paytm extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Welcomemodel','welcomemodel');
    }

    function paytm()
    {
        $this->load->view('TxnTest');
    }

    function paytmpost()
    {
        header("Pragma: no-cache");
        header("Cache-Control: no-cache");
        header("Expires: 0");

        // following files need to be included
        require_once(APPPATH . "third_party/paytmlib/config_paytm.php");
        require_once(APPPATH . "third_party/paytmlib/encdec_paytm.php");
        $checkSum = "";
        $paramList = array();
        $ORDER_ID = $_POST["ORDER_ID"];
        $CUST_ID = $_POST["CUST_ID"];
        $INDUSTRY_TYPE_ID = $_POST["INDUSTRY_TYPE_ID"];
        $CHANNEL_ID = $_POST["CHANNEL_ID"];
        $TXN_AMOUNT = $_POST["TXN_AMOUNT"];

        //print_r($_POST);die;
        if (isset($_POST) && !empty($_POST)){
            $bill_details = array(
                'billing_first_name' => $_POST['billing_first_name'],
                'tel' => $_POST['tel'],
                'billing_email' => $_POST['billing_email'],
                'billing_date' => $_POST['billing_date'],
                'time_slot' => $_POST['time_slot'],
                'billing_address' => $_POST['billing_address'],
            );
            $this->session->set_userdata('billing_data', $bill_details);
        }
     //   print_r($_SESSION['billing_data']);die;
        // Create an array having all required parameters for creating checksum.
        $paramList["MID"] = PAYTM_MERCHANT_MID;
        $paramList["ORDER_ID"] = $ORDER_ID;
        $paramList["CUST_ID"] = $CUST_ID;
        $paramList["INDUSTRY_TYPE_ID"] = $INDUSTRY_TYPE_ID;
        $paramList["CHANNEL_ID"] = $CHANNEL_ID;
        $paramList["TXN_AMOUNT"] = $TXN_AMOUNT;
        $paramList["WEBSITE"] = PAYTM_MERCHANT_WEBSITE;


        $paramList["CALLBACK_URL"] = base_url('paytm/pgResponse');
        // $paramList["MSISDN"] = $MSISDN; //Mobile number of customer
        // $paramList["EMAIL"] = $EMAIL; //Email ID of customer
        // $paramList["VERIFIED_BY"] = "EMAIL"; //
        // $paramList["IS_USER_VERIFIED"] = "YES"; //

        //Here checksum string will return by getChecksumFromArray() function.
        $checkSum = getChecksumFromArray($paramList,PAYTM_MERCHANT_KEY);
        //print_r($checkSum);die;
        echo "<html>
		<head>
		<title>Merchant Check Out Page</title>
		</head>
		<body>
		    <center><h1>Please do not refresh this page...</h1></center>
		        <form method='post' action='".PAYTM_TXN_URL."' name='f1'>
		<table border='1'>
		 <tbody>";
        // echo "<pre>";
        // print_r($paramList);die;

        foreach($paramList as $name => $value) {

            echo '<input type="hidden" name="' . $name .'" value="' . $value .         '">';
            // echo "<pre>";
            // print_r($name);
            // print_r($value);
        }//echo "<pre>";
        //die;
        //print_r($paramList);die;

        echo "<input type='hidden' name='CHECKSUMHASH' value='". $checkSum . "'>

		 </tbody>
		</table>
		<script type='text/javascript'>
		 document.f1.submit();
		</script>
		</form>
		</body>
		</html>";
    }

    public function pgResponse()
    {
        header("Pragma: no-cache");
        header("Cache-Control: no-cache");
        header("Expires: 0");

        // following files need to be included
        require_once(APPPATH . "third_party/paytmlib/config_paytm.php");
        require_once(APPPATH . "third_party/paytmlib/encdec_paytm.php");

        $paytmChecksum = "";
        $paramList = array();
        $isValidChecksum = "FALSE";

        $paramList = $_POST;
        $paytmChecksum = isset($_POST["CHECKSUMHASH"]) ? $_POST["CHECKSUMHASH"] : ""; //Sent by Paytm pg

        //Verify all parameters received from Paytm pg to your application. Like MID received from paytm pg is same as your application’s MID, TXN_AMOUNT and ORDER_ID are same as what was sent by you to Paytm PG for initiating transaction etc.
        $isValidChecksum = verifychecksum_e($paramList, PAYTM_MERCHANT_KEY, $paytmChecksum); //will return TRUE or FALSE string.


        if($isValidChecksum == "TRUE") {
            echo "<b>Checksum matched and following are the transaction details:</b>" . "<br/>";
            if ($_POST["STATUS"] == "TXN_SUCCESS") {
                echo "<b>Transaction status is success</b>" . "<br/>";
                //Process your transaction here as success transaction.
                //Verify amount & order id received from Payment gateway with your application's order id and amount.
            }
            else {
                echo "<b>Transaction status is failure</b>" . "<br/>";
            }

            if (isset($_POST) && count($_POST)>0 )
            {
                foreach($_POST as $paramName => $paramValue) {
                    echo "<br/>" . $paramName . " = " . $paramValue;
                }
            }


        }
        else {
            echo "<b>Checksum mismatched.</b>";
            //Process transaction as suspicious.
        }
    }
}