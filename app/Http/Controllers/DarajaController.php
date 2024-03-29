<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class DarajaController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'fname' => 'required',
            'lname' => 'required',
            'phoneNo' => 'required',
            'amount' => 'required',
            'houseNo' => 'required'
        ]);
        //--------GENERATING ACCESS TOKENS------------
        //API KEYS

        $consumerKey = "";
        $consumerSecret = "";
        //ACCESS URL
        $access_token_url = "";
        $headers = ['Content-Type:application/json; charset=utf8'];
        $curl = curl_init($access_token_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ":" . $consumerSecret);
        $result = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $result = json_decode($result);
        //echo 
        $access_token = $result->access_token;
        curl_close($curl);

        //----------STK PUSH--------------
        //INCLUDE THE ACCESS TOKEN FILE

        date_default_timezone_set('Africa/Nairobi');
        $processrequestUrl = '';
        $callbackurl = '';
        $passkey = "";
        $BusinessShortCode = '';
        $Timestamp = date('YmdHis');
        // ENCRYPT  DATA TO GET PASSWORD
        $Password = base64_encode($BusinessShortCode . $passkey . $Timestamp);
        $phone = ''; //phone number to receive the stk push
        $money = $_POST['amount'];
        $PartyA = $_POST['phoneNo'];
        $PartyB = '254708374149';
        $AccountReference = '';
        $TransactionDesc = 'stkpush test';
        $Amount = $money;
        $stkpushheader = ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token];
        //INITIATE CURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $processrequestUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $stkpushheader); //setting custom header
        $curl_post_data = array(
            //Fill in the request parameters with valid values
            'BusinessShortCode' => $BusinessShortCode,
            'Password' => $Password,
            'Timestamp' => $Timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $Amount,
            'PartyA' => $PartyA,
            'PartyB' => $BusinessShortCode,
            'PhoneNumber' => $PartyA,
            'CallBackURL' => $callbackurl,
            'AccountReference' => $AccountReference,
            'TransactionDesc' => $TransactionDesc
        );

        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        echo $curl_response = curl_exec($curl);

        Payment::create($data);
        return redirect(route('renters'));
    }
}
// //ECHO  RESPONSE
        // $data = json_decode($curl_response);
        // $CheckoutRequestID = $data->CheckoutRequestID;
        // $ResponseCode = $data->ResponseCode;
        // if ($ResponseCode == "0") {
        //     echo "The CheckoutRequestID for this transaction is : " . $CheckoutRequestID;
        // }