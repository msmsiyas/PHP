<?php
/**
 *   (C) Copyright 1997-2018 hSenid International (pvt) Limited.
 *   All Rights Reserved.
 *
 *   These materials are unpublished, proprietary, confidential source code of
 *   hSenid International (pvt) Limited and constitute a TRADE SECRET of hSenid
 *   International (pvt) Limited.
 *
 *   hSenid International (pvt) Limited retains all title to and intellectual
 *   property rights in these materials.
 */

include_once '../../lib/sms/SmsReceiver.php';
include_once '../../lib/sms/SmsSender.php';
include_once '../log.php';
ini_set('error_log', 'sms-app-error.log');
ini_set('date.timezone', 'Asia/Colombo');

try {
    $receiver = new SmsReceiver(); // Create the Receiver object
    $version = $receiver->getVersion(); // get the version
    $content = $receiver->getMessage(); // get the message content
    $address = $receiver->getAddress(); // get the sender's address
    $requestId = $receiver->getRequestID(); // get the request ID
    $applicationId = $receiver->getApplicationId(); // get application ID
    $encoding = $receiver->getEncoding(); // get the encoding value

    logFile("[ content=$content, address=$address, requestId=$requestId, applicationId=$applicationId, encoding=$encoding, version=$version ]");

    $responseMsg;

    //your logic goes here......
    $split = explode(' ', $content);
    $responseMsg = bmiLogicHere($split);

    // Create the sender object server url
    $sender = new SmsSender("http://localhost:7000/sms/send");

    //sending a one message

    $applicationId = "APP_000001";
    $encoding = "240";
    $version =  "1.0";
    $password = "328efdff16e9vfe327dfdd5001cc6f0e";
    $sourceAddress = "";
    $deliveryStatusRequest = "1";
    $charging_amount = "";
    $destinationAddresses = $address;
    $binary_header = "";
    $res = $sender->sms($responseMsg, $destinationAddresses, $password, $applicationId, $sourceAddress, $deliveryStatusRequest, $charging_amount, $encoding, $version, $binary_header);

} catch (SmsException $ex) {
    //throws when failed sending or receiving the sms
    error_log("ERROR: {$ex->getStatusCode()} | {$ex->getStatusMessage()}");
}


/*
    BMI logic function
**/
function bmiLogicHere($split)
{
    if (sizeof($split) < 3) {
        $responseMsg = "Invalid message content. Please type XYZ <space> Your Weight <space> Your Height and send to 77111";
    } else {
        $weight = (float)$split[1];
        $height = (float)$split[2];

        $bmi = getBMIValue($weight, ($height / 100));
        $category = getCategory($bmi);

        $responseMsg = "Thank you for contacting us. Your BMI :" . round($bmi, 2) . ", Category :" . $category;
    }
    return $responseMsg;
}


//logFile("[response=$responseMsg, $responseMsg, $destinationAddresses, $password, $applicationId, $sourceAddress, $deliveryStatusRequest, $charging_amount, $encoding, $version]");
/*
    Get BMI value
**/

function getBMIValue($weight, $height)
{
    return ($weight / pow($height, 2));
}

/*
    Get category according to BMI value
**/

function getCategory($bmiValue)
{
    if ($bmiValue < 18.5) {
        return "Underweight";
    } else if ($bmiValue >= 18.5 && $bmiValue < 24.9) {
        return "Normal Weight";
    } else if ($bmiValue >= 25 && $bmiValue < 29.9) {
        return "Overweight";
    } else {
        return "Obesity";
    }
}

?>