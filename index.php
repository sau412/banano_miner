<?php
require_once("settings.php");
require_once("db.php");
require_once("html.php");
require_once("core.php");
require_once("coinimp.php");

db_connect();

if(isset($_POST['address']) && isset($_POST['ref_uid'])) {
        $address=stripslashes($_POST['address']);
        $address_html=html_escape($address);
        $ref_uid=stripslashes($_POST['ref_uid']);
        $user_id=get_user_id_by_address($address,$ref_uid);
        header("Location: ./?address=$address_html");
        die();
}

if(isset($_POST['action']) && $_POST['action']=='withdraw') {
        $address=stripslashes($_POST['address']);
        $address_html=html_escape($address);
        $address_escaped=db_escape($address);

        db_query("LOCK TABLES `stats` WRITE, `payouts` WRITE");
        $currency_balance=db_query_to_variable("SELECT `balance` FROM `stats` WHERE `address`='$address_escaped'");

        //echo "Withdraw $grc_balance to address $address\n";
        if($currency_balance>$min_payout) {
                $currency_balance_escaped=db_escape($currency_balance);
                $address_escaped=db_escape($address);

                db_query("UPDATE `stats` SET `balance`=0 WHERE `address`='$address_escaped'");
                db_query("INSERT INTO `payouts` (`address`,`amount`) VALUES ('$address_escaped','$currency_balance_escaped')");
        }
        db_query("UNLOCK TABLES");

        header("Location: ./?address=$address_html");
        die();
}

if(isset($_GET['json'])) {
        $address=stripslashes($_GET['address']);
        $address_html=html_escape($address);
        $address_escaped=db_escape($address);

        $user_id=get_user_id_by_address($address);

        $hashes_balance=coinimp_get_user_balance("web",$user_id);
        user_balance_add($address,$hashes_balance);
        /*$hashes_escaped=db_escape($hashes_balance);
        //var_dump($hashes_balance);

        $prev_hashes=db_query_to_variable("SELECT `hashes` FROM `stats` WHERE `address`='$address_escaped'");
        if($hashes_balance>$prev_hashes) {
                $hashes_delta=$hashes_balance-$prev_hashes;
                $currency_balance_delta=$coin_per_mhash*$hashes_delta/1000000;
                $currency_balance_delta_escaped=db_escape($currency_balance_delta);
                db_query("UPDATE `stats` SET `hashes`='$hashes_escaped',`balance`=`balance`+'$currency_balance_delta_escaped' WHERE `address`='$address_escaped'");
        }*/
        $currency_balance=db_query_to_variable("SELECT `balance` FROM `stats` WHERE `address`='$address_escaped'");

        $currency_balance=sprintf("%0.8f",$currency_balance);

        if($currency_balance>$min_payout) {
                $withdraw_form=html_withdraw_form($address);
        } else {
                $withdraw_form="min payout is $min_payout $currency_symbol";
        }
        echo <<<_END
<table class=data_table>
<tr><td align=right>Address:</td><td>$address_html</td></tr>
<!--<tr><td align=right>Hashes mined:</td><td>$hashes_balance</td></tr>-->
<!--<tr><td align=right>$currency_name per Mhash:</td><td>$coin_per_mhash</td></tr>-->
<tr><td align=right>Current balance:</td><td>$currency_balance $currency_symbol $withdraw_form</td></tr>
</table>
<br>

_END;
        die();
}

// Standard page beginning
echo html_page_begin($site_name);
echo <<<_END
<h1><img src='logo-horizontal.png' alt='$site_name' width=50%></h1>

_END;

if(isset($_GET['address'])) {
        $address=stripslashes($_GET['address']);
        $address_html=html_escape($address);

        echo "<div id=balance>Loading data, please wait...</div>\n";

        $user_id=get_user_id_by_address($address);

        echo html_coinimp_frame("web",$user_id);

        echo html_ref_section($address);

} else {
        if(isset($_GET['r'])) {
                $ref_uid=stripslashes($_GET['r']);
                if(!is_numeric($ref_uid)) $ref_uid=0;
        } else {
                $ref_uid=0;
        }
        echo <<<_END
<p><a href='https://banano.cc/'><img src='bananosite.jpg' height=120px></a> <a href='https://banano.how/'><img src='bananohow.jpg' height=120px></a></p>

<p>Welcome to BANANO Miner!<br>
This is a community-made faucet you can use to "mine" some fresh BANANO.<br>
You might know that BANANO itself doesn't require mining, the faucet actually mines webchain by using your CPU,<br>
automatically converts rewards to BANANO and sends it to your wallet.</p>

<p>This wouldn't be very efficient normally, but this faucet is subsidized by the BANANO team,<br>
meaning you will actually get much more rewards than the coins you mine.<br>
The proof-of-work provided here is rather used to avoid exploits and to make BANANO distribution fair.</p>

<p>Disclaimer: this page embeds third-party script (from coinimp), use it on your own risk.</p>

<p>Enter address carefully, you cannot move mined funds to another address.</p>

<form name=address method=POST>
<input type=hidden name=ref_uid value='$ref_uid'>
<p>Your $currency_name address: <input type=text size=80 name=address> <input type=submit value='Open miner page'></p>
</form>

<p>New to BANANO? Check the <a href='https://banano.cc/'>official website</a>! If you need help getting started visit <a href='https://banano.how/'>banano.how</a></p>

_END;
}

echo html_payouts();

echo html_page_end();

?>
