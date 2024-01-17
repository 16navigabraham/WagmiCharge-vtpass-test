<?php

if(isset($_POST["topup"])){
$operator = trim(htmlspecialchars($_POST["operator"]));
$customer = trim(htmlspecialchars($_POST["phone"]));
$amount = trim(htmlspecialchars($_POST["amount"]));

//Integrate VTPass
date_default_timezone_set('Africa/Lagos');

//Get the current timestamp
 $current_time = new DateTime();
 $formated_time = $current_time->format("YmdHi");

 //Generate More alpha-numeric character
 $additional_chars = "89htyyo";

 //Concatenate the two variables for the request IDs above ($formated_time and additional chars)
 $request_id = $formated_time . $additional_chars;
 while(strlen($request_id) < 12){
     $request_id .= "x";
 }

 //API Integration Proper
 $apiUrl = "https://sandbox.vtpass.com/api/pay";

 $data = array(
     "request_id" => $request_id,
     "serviceID" => $operator,
     "amount" => $amount,
     "phone" => $customer
 );

 //Set up some cURL config
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
 "api-key: b64a50d68e194a6d201dd391e04043e0",
    "secret-key: SK_31126dd675991f997d3bb431cfdb313a10479b87b5a",
    "Content-Type: application/json"
));
// After executing the request
$response = curl_exec($ch);
$result = json_decode($response);
//print_r($response);
if($result->content->transactions->status === "delivered"){
   header("Location: success.php"); 
   exit;
}
else{
    die("Error: The transaction Failed!");
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>VTPass Airtime and Data</title>
     <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        h1 {
            background-color: #007BFF;
            color: #fff;
            padding: 20px;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            max-width: 400px;
        }

        select,
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        select {
            height: 40px;
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>VTPass Airtime and Data</h1>
    <form action="" method="POST">
    <select name="operator" required>
        <option>select a Network</option>
        <option value="mtn">MTN Airtime</option>
        <option value="airtel">Airtel Airtime</option>
        <option value="glo">Glo Airtime</option>
        <option value="9mobile">9Mobile Airtime</option>
    </select>

    <input type="number" name="amount" required placeholder="How much airtime do you want?">
    <input type="number" name="phone" required placeholder="Enter the phone number that will receive this airtime">
    <input type="submit" name="topup" required value="Top-Up">
</body>
</html>