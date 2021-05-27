<?php
include "Algorithm.php";

$obj = new Algorithm();
$sk = pack("H*",$_POST["sk_key"]);
$iv = pack("H*",$_POST["iv_key"]);
$image = "";
if($_FILES["photo"]["name"] != ""){
    $image =$_FILES["photo"]["name"];
    $messSecret = $obj->steganoDecrypt($image);
}
echo $messSecret;
$result = $obj->aesDecrypt($messSecret,$sk,$iv);
header("location:index.php");

$ms = "Your message: " . $result;
$fp = fopen(time()."_"."InfoDecrypt.txt","wb");
fwrite($fp,$ms);
fclose($fp);

?>
