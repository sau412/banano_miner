<?php
// Only for command line
if(!isset($argc)) die();

require_once("settings.php");
require_once("db.php");
require_once("html.php");
require_once("core.php");
require_once("coinimp.php");

db_connect();

$user_id_array=db_query_to_array("SELECT `id`,`address` FROM `stats`");

foreach($user_id_array as $row) {
        $address=$row['address'];
        $user_id=$row['id'];
        $user_id_escaped=db_escape($user_id);
        $need_update=db_query_to_variable("SELECT `need_update` FROM `coinimp_cache` WHERE `id`='$user_id_escaped'");

        if(!$need_update) continue;

        echo "$user_id\n";

        $hashes_balance=coinimp_get_user_balance("web",$user_id);
        $balance_escaped=db_escape($hashes_balance);
        db_query("INSERT INTO `coinimp_cache` (`id`,`balance`) VALUES ('$user_id_escaped','$balance_escaped')
                        ON DUPLICATE KEY UPDATE `balance`=VALUES(`balance`),`need_update`=0");
        //user_balance_add($address,$hashes_balance);
}
?>
