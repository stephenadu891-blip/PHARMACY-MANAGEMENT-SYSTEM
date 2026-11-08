<?php
require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/config/paystack.php";
require_login();
if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: sales/index.php"); exit; }
verify_csrf();
$medicine_id=(int)($_POST["medicine_id"]??0); $qty=(int)($_POST["quantity"]??0);
$customer_id=(int)($_POST["customer_id"]??0); $email=filter_var(trim($_POST["payment_email"]??""), FILTER_VALIDATE_EMAIL);
if($qty<1 || !$email || strpos(PAYSTACK_SECRET_KEY,"REPLACE_WITH_")!==false){
 flash("danger", "Enter a valid payment email and quantity, and configure your Paystack secret key."); header("Location: sales/index.php"); exit;
}
try {
 $st=$conn->prepare("SELECT quantity,selling_price,expiry_date FROM medicines WHERE id=?"); $st->bind_param("i",$medicine_id); $st->execute(); $med=$st->get_result()->fetch_assoc();
 if(!$med || $med["quantity"]<$qty || ($med["expiry_date"] && $med["expiry_date"]<date("Y-m-d"))) throw new Exception("Medicine unavailable, expired or insufficient stock.");
 $amount=round($qty*(float)$med["selling_price"],2); if($amount<=0) throw new Exception("Invalid sale amount.");
 $reference="PHARM_".bin2hex(random_bytes(12));
 $st=$conn->prepare("INSERT INTO paystack_payments(reference,user_id,customer_id,medicine_id,quantity,amount,customer_email) VALUES(?,?,?,?,?,?,?)");
 $uid=(int)$_SESSION["user_id"]; $cid=$customer_id>0?$customer_id:null;
 $st->bind_param("siiiids",$reference,$uid,$cid,$medicine_id,$qty,$amount,$email); $st->execute();
 $payload=json_encode(["email"=>$email,"amount"=>(int)round($amount*100),"currency"=>"GHS","reference"=>$reference,"callback_url"=>PAYSTACK_CALLBACK_URL,"metadata"=>["system"=>"PharmacyCare"]]);
 $ch=curl_init("https://api.paystack.co/transaction/initialize");
 curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_HTTPHEADER=>["Authorization: Bearer ".PAYSTACK_SECRET_KEY,"Content-Type: application/json","Cache-Control: no-cache"],CURLOPT_TIMEOUT=>30]);
 $raw=curl_exec($ch); $http=curl_getinfo($ch,CURLINFO_HTTP_CODE); $err=curl_error($ch); curl_close($ch);
 if($raw===false || $http<200 || $http>=300) throw new Exception("Paystack could not initialize payment. ".($err?:"Check your API key and internet connection."));
 $result=json_decode($raw,true);
 if(empty($result["status"]) || empty($result["data"]["authorization_url"])) throw new Exception($result["message"]??"Paystack initialization failed.");
 header("Location: ".$result["data"]["authorization_url"]); exit;
} catch(Throwable $e) { flash("danger",$e->getMessage()); header("Location: sales/index.php"); exit; }
