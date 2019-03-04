<?php
require_once("settings.php");
require_once("db.php");
require_once("reddit_api.php");

db_connect();

reddit_access_token();

$send_data=db_query_to_array("SELECT `uid`,`address`,`amount`,`status` FROM `payouts` WHERE `status`='pending'");

foreach($send_data as $row) {
        $uid=$row['uid'];
        $address=$row['address'];
        $amount=$row['amount'];
        echo "Sending $amount to $address\n";
        $amount=sprintf("%0.8F",$amount);
        reddit_compose("Banano_Tipbot",$uid,"send $amount $address");
        $uid_escaped=db_escape($uid);
        db_query("UPDATE `payouts` SET `status`='checking' WHERE `uid`='$uid_escaped' AND `status`='pending'");
}

$inbox=reddit_inbox();

foreach($inbox->data->children as $element) {
        $author=$element->data->author;
        $id=$element->data->name;
        if($author!="Banano_Tipbot") {
                reddit_mark_read($id);
                continue;
        }

        $subject=$element->data->subject;
        $body=$element->data->body;

        // "subject"=>"re: uid"
        if(preg_match('/^re: ([0-9]+)/',$subject,$matches)) {
                $uid=$matches[1];
                echo "uid $uid\n";
                $uid_escaped=db_escape($uid);
                // [BananoVault](https://vault.banano.co.in/transaction/3755D76E646E57DD127C6FC8DF0E421393436D21314339D49FB4D067D2376174)"
                if(preg_match('/transaction\\/([^\\)]+)/',$body,$matches)) {
                        $txid=$matches[1];
                        $txid_escaped=db_escape($txid);
                        echo "uid $uid is sent with txid $txid\n";
                        //echo "UPDATE `payouts` SET `status`='sent`,txid='$txid_escaped' WHERE `uid`='$uid_escaped' AND `status`='checking'\n";
                        db_query("UPDATE `payouts` SET `status`='sent',txid='$txid_escaped' WHERE `uid`='$uid_escaped' AND `status`='checking'");
                } else if(preg_match('/Insufficient Banano left in your account to transfer/',$body)) {
                        echo "Insufficient Banano left in your account to transfer";
                        db_query("UPDATE `payouts` SET `status`='pending` WHERE `uid`='$uid_escaped' AND `status`='checking'");
                } else if(preg_match('/Invalid destination address/',$body)) {
                        echo "Invalid destination address";
                        db_query("UPDATE `payouts` SET `status`='error`,txid='' WHERE `uid`='$uid_escaped' AND `status`='checking'");
                } else {
                        echo "Unknown message: $body\n";
                }
        }
        reddit_mark_read($id);
}
?>
