<?php
require_once("settings.php");
require_once("db.php");
require_once("html.php");
require_once("core.php");
require_once("coinimp.php");

db_connect();

if(isset($_POST['action']) && $_POST['action']=='withdraw') {
        $address=stripslashes($_POST['address']);
        $address_html=html_escape($address);
        $address_escaped=db_escape($address);

        $currency_balance=db_query_to_variable("SELECT `balance` FROM `stats` WHERE `address`='$address_escaped'");

        //echo "Withdraw $grc_balance to address $address\n";
        if($currency_balance>$min_payout) {
                $currency_balance_escaped=db_escape($currency_balance);
                $address_escaped=db_escape($address);

                db_query("UPDATE `stats` SET `balance`=0 WHERE `address`='$address_escaped'");
                db_query("INSERT INTO `payouts` (`address`,`amount`) VALUES ('$address_escaped','$currency_balance_escaped')");
        }

        header("Location: ./?address=$address_html");
        die();
}

if(isset($_GET['json'])) {
        $address=stripslashes($_GET['address']);
        $address_html=html_escape($address);
        $address_escaped=db_escape($address);

        $user_id=get_user_id_by_address($address);

        $hashes_balance=coinimp_get_user_balance("web",$user_id);
        $hashes_escaped=db_escape($hashes_balance);
        //var_dump($hashes_balance);

        $prev_hashes=db_query_to_variable("SELECT `hashes` FROM `stats` WHERE `address`='$address_escaped'");
        if($hashes_balance>$prev_hashes) {
                $hashes_delta=$hashes_balance-$prev_hashes;
                $currency_balance_delta=$coin_per_mhash*$hashes_delta/1000000;
                $currency_balance_delta_escaped=db_escape($currency_balance_delta);
                db_query("UPDATE `stats` SET `hashes`='$hashes_escaped',`balance`=`balance`+'$currency_balance_delta_escaped' WHERE `address`='$address_escaped'");
        }
        $currency_balance=db_query_to_variable("SELECT `balance` FROM `stats` WHERE `address`='$address_escaped'");

        $currency_balance=sprintf("%0.8f",$currency_balance);

        if($currency_balance>$min_payout) {
                $withdraw_form=<<<_END
<form name=withdraw method=POST style='display:inline;'>
<input type=hidden name=action value='withdraw'>
<input type=hidden name=address value='$address_html'>
<input type=submit value='withdraw'>
</form>

_END;
        } else {
                $withdraw_form="min payout is $min_payout $currency_symbol";
        }
        echo <<<_END
<table class=data_table>
<tr><td align=right>Address:</td><td>$address_html</td></tr>
<!--<tr><td align=right>Hashes mined:</td><td>$hashes_balance</td></tr>-->
<!--<tr><td align=right>$currency_name per Mhash:</td><td>$coin_per_mhash</td></tr>-->
<tr><td align=right>Confirmed balance:</td><td>$currency_balance $currency_symbol $withdraw_form</td></tr>
</table>
<br>

_END;
        die();
}

// Standard page beginning
echo html_page_begin($site_name);
echo <<<_END
<h1>$site_name</h1>
<p>Disclaimer: this page embeds third-party script (from coinimp), use it on your own risk.</p>
_END;

if(isset($_GET['address'])) {
        $address=stripslashes($_GET['address']);
        $address_html=html_escape($address);

        echo "<div id=balance>Loading data, please wait...</div>\n";

        $user_id=get_user_id_by_address($address);

        echo html_coinimp_frame("web",$user_id);
} else {
        echo <<<_END
<form name=address method=GET>
<p>Enter address carefully, you cannot move mined funds to another address.</p>
<p>Your $currency_name address: <input type=text size=80 name=address> <input type=submit value='Open miner page'></p>

</form>

_END;
}

echo html_payouts();

echo html_page_end();

?>
