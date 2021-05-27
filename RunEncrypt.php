<?php
    include "Algorithm.php";

    $obj = new Algorithm();
    $data = $_POST["Message"];
    $messEncrypted = $obj->aesCrypt($data);
    $image = "";
    if($_FILES["Image"]["name"] != ""){
        $image =$_FILES["Image"]["name"];
        $obj->stegano($image, $messEncrypted);
    }
    header("location:index.php");


    // write info to file

    $sk = "Your secret key: " . bin2hex($_SESSION["sk"]) . PHP_EOL . "Your iv_size key: " . bin2hex($_SESSION["ivs"]) . PHP_EOL .
        "Your iv key: " . bin2hex($_SESSION["ivk"]);
    $fp = fopen(time()."_"."secretInfo.txt","wb");
    fwrite($fp,$sk);
    fclose($fp);

    // show alert

//    echo '<script language="javascript">';
//    echo 'alert("message successfully encrypted")';
//    echo '</script>';
//    echo bin2hex($_SESSION["sk"]) . "<br>";
//    echo pack("H*", bin2hex($_SESSION["sk"])) . "<br>";

?>
