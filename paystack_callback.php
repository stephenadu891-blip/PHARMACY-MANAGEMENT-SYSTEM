<?php
require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/config/paystack.php";
require_login();
$reference=trim($_GET["reference"]??"");
if($reference==="" || !preg_match('/^[A-Za-z0-9_\-]+$/',$reference)){ flash("danger","Invalid payment reference."); header("Location: sales/index.php"); exit; }
try {
 $st=$conn->prepare("SELECT * FROM paystack_payments WHERE reference=? AND user_id=?"); $uid=(int)$_SESSION["user_id"]; $st->bind_param("si",$reference,$uid); $st->execute(); $payment=$st->get_result()->fetch_assoc();
 if(!$payment) throw new Exception("Payment record was not found.");
 if($payment["status"]==="success"){ flash("success","Payment already confirmed. Receipt #".$payment["sale_id"]); header("Location: sales/index.php"); exit; }
 $ch=curl_init("https://api.paystack.co/transaction/verify/".rawurlencode($reference));
 curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>["Authorization: Bearer ".PAYSTACK_SECRET_KEY,"Cache-Control: no-cache"],CURLOPT_TIMEOUT=>30]);
 $raw=curl_exec($ch); $http=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch); $result=json_decode((string)$raw,true);
 if($http!==200 || empty($result["status"]) || ($result["data"]["status"]??"")!=="success" || ($result["data"]["reference"]??"")!==$reference || (int)($result["data"]["amount"]??0)!==(int)round((float)$payment["amount"]*100) || ($result["data"]["currency"]??"")!=="GHS") {
   $st=$conn->prepare("UPDATE paystack_payments SET status='failed',gateway_response=? WHERE reference=?"); $msg=(string)($result["data"]["gateway_response"]??$result["message"]??"Verification failed"); $st->bind_param("ss",$msg,$reference); $st->execute(); throw new Exception("Payment could not be verified. No sale was recorded.");
 }
 $conn->begin_transaction();
 $st=$conn->prepare("SELECT quantity,selling_price,expiry_date FROM medicines WHERE id=? FOR UPDATE"); $st->bind_param("i",$payment["medicine_id"]); $st->execute(); $med=$st->get_result()->fetch_assoc();
 if(!$med || $med["quantity"]<(int)$payment["quantity"] || ($med["expiry_date"] && $med["expiry_date"]<date("Y-m-d")) || round((float)$med["selling_price"]*(int)$payment["quantity"],2)!==(float)$payment["amount"]) throw new Exception("Stock or price changed. Please contact the pharmacy administrator for assistance.");
 $method="mobile_money"; $channel=$result["data"]["channel"]??""; if($channel==="card") $method="card";
 $cid=$payment["customer_id"];
 if($cid){$st=$conn->prepare("INSERT INTO sales(customer_id,user_id,total_amount,payment_method) VALUES(?,?,?,?)");$st->bind_param("iids",$cid,$payment["user_id"],$payment["amount"],$method);}
 else{$st=$conn->prepare("INSERT INTO sales(customer_id,user_id,total_amount,payment_method) VALUES(NULL,?,?,?)");$st->bind_param("ids",$payment["user_id"],$payment["amount"],$method);}
 $st->execute();$sale_id=$conn->insert_id;
 $st=$conn->prepare("INSERT INTO sale_items(sale_id,medicine_id,quantity,unit_price) VALUES(?,?,?,?)");$unit=(float)$med["selling_price"];$st->bind_param("iiid",$sale_id,$payment["medicine_id"],$payment["quantity"],$unit);$st->execute();
 $st=$conn->prepare("UPDATE medicines SET quantity=quantity-? WHERE id=?");$st->bind_param("ii",$payment["quantity"],$payment["medicine_id"]);$st->execute();
 $response=(string)($result["data"]["gateway_response"]??"Successful");$st=$conn->prepare("UPDATE paystack_payments SET status='success',sale_id=?,gateway_response=? WHERE reference=?");$st->bind_param("iss",$sale_id,$response,$reference);$st->execute();
 $conn->commit(); flash("success","Paystack payment confirmed. Sale receipt #".$sale_id);
} catch(Throwable $e) { if($conn->errno || $conn->connect_errno===0){ try{$conn->rollback();}catch(Throwable $ignored){} } flash("danger",$e->getMessage()); }
header("Location: sales/index.php"); exit;
