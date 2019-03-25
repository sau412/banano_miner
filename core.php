<?php

function get_user_id_by_address($address,$ref_uid=0) {
        $address_escaped=db_escape($address);
        $id=db_query_to_variable("SELECT `id` FROM `stats` WHERE `address`='$address_escaped'");
        if($id=="") {
                $id=bin2hex(random_bytes(16));
                $id_escaped=db_escape($id);
                $ref_uid_escaped=db_escape($ref_uid);
                db_query("INSERT INTO `stats` (`id`,`address`,`ref_uid`) VALUES ('$id_escaped','$address_escaped','$ref_uid_escaped')");
        }
        return $id;
}

function user_balance_add($address,$hashes_balance) {
        global $coin_per_mhash;
        global $ref_level_1;
        global $ref_level_2;
        global $boost_mult;
        global $boost_balance_max;

        if($hashes_balance==='') return;
        $address_escaped=db_escape($address);
        $hashes_escaped=db_escape($hashes_balance);

        //db_query("LOCK TABLES `stats` WRITE,`ip_stats` WRITE");
        $prev_hashes=db_query_to_variable("SELECT `hashes` FROM `stats` WHERE `address`='$address_escaped'");
        if($hashes_balance>$prev_hashes) {
                $user_ip=$_SERVER['REMOTE_ADDR'];
                $user_ip_escaped=db_escape($user_ip);

                $ip_balance=db_query_to_variable("SELECT `balance` FROM `ip_stats` WHERE `ip`='$user_ip_escaped'");

                if($ip_balance<$boost_balance_max) {
                        $mult=$boost_mult;
                } else {
                        $mult=1;
                }

                $hashes_delta=$hashes_balance-$prev_hashes;
                $currency_balance_delta=$coin_per_mhash*$mult*$hashes_delta/1000000;
                $currency_balance_delta_escaped=db_escape($currency_balance_delta);
                db_query("UPDATE `stats` SET `hashes`='$hashes_escaped',`balance`=`balance`+'$currency_balance_delta_escaped' WHERE `address`='$address_escaped'");
                db_query("INSERT INTO `ip_stats` (`ip`,`balance`) VALUES ('$user_ip_escaped','$currency_balance_delta_escaped')
                                ON DUPLICATE KEY UPDATE `balance`=`balance`+VALUES(`balance`)");

                $ref_uid=db_query_to_variable("SELECT `ref_uid` FROM `stats` WHERE `address`='$address_escaped'");
                if($ref_uid!=0) {
                        $ref_uid_escaped=db_escape($ref_uid);
                        $ref_level_1_address=db_query_to_variable("SELECT `address` FROM `stats` WHERE `uid`='$ref_uid_escaped'");
                        $ref_level_1_address_escaped=db_escape($ref_level_1_address);
                        $ref_level_1_balance_delta=$currency_balance_delta*$ref_level_1;
                        $ref_level_1_balance_delta_escaped=db_escape($ref_level_1_balance_delta);
                        db_query("UPDATE `stats` SET `balance`=`balance`+'$ref_level_1_balance_delta_escaped' WHERE `address`='$ref_level_1_address_escaped'");

                        $ref_uid2=db_query_to_variable("SELECT `ref_uid` FROM `stats` WHERE `address`='$ref_level_1_address'");
                        if($ref_uid2!=0) {
                                $ref_uid2_escaped=db_escape($ref_uid2);
                                $ref_level_2_address=db_query_to_variable("SELECT `address` FROM `stats` WHERE `uid`='$ref_uid2_escaped'");
                                $ref_level_2_address_escaped=db_escape($ref_level_2_address);
                                $ref_level_2_balance_delta=$currency_balance_delta*$ref_level_2;
                                $ref_level_2_balance_delta_escaped=db_escape($ref_level_2_balance_delta);
                                db_query("UPDATE `stats` SET `balance`=`balance`+'$ref_level_2_balance_delta_escaped' WHERE `address`='$ref_level_2_address_escaped'");
                        }
                }
        }
        //db_query("UNLOCK TABLES");
}

function auth_log($message) {
//      echo $message;
}

?>
