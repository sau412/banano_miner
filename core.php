<?php

function get_user_id_by_address($address) {
        $address_escaped=db_escape($address);
        $id=db_query_to_variable("SELECT `id` FROM `stats` WHERE `address`='$address_escaped'");
        if($id=="") {
                $id=bin2hex(random_bytes(16));
                $id_escaped=db_escape($id);
                db_query("INSERT INTO `stats` (`id`,`address`) VALUES ('$id_escaped','$address_escaped')");
        }
        return $id;
}
?>
