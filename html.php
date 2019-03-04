<?php

// Escape for html facade
function html_escape($variable) {
        $result=htmlspecialchars($variable);
        if($variable!='' && $result=='') {
                $result=iconv('WINDOWS-1251','UTF-8',$variable);
                $result=htmlspecialchars($result);
        }
        $result=str_replace("'","&apos;",$result);
        return $result;
}

// Standard page begin
function html_page_begin($title) {
        global $pool_name;

        if(isset($_GET['part'])) $part=stripslashes($_GET['part']);
        else $part="";

        $part_html=html_escape($part);

        return <<<_END
<!DOCTYPE html>
<html>
<head>
<title>$title</title>
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="icon" href="favicon.png" type="image/png">
<script src='jquery-3.3.1.min.js'></script>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<center>

_END;
}

function html_coinimp_frame($asset,$user_id) {
        global $coinimp_xmr_site_key;
        global $coinimp_web_site_key;

        if($asset=="xmr") { $site_key=$coinimp_xmr_site_key; $param="{throttle:0,ads:0}"; }
        if($asset=="web") { $site_key=$coinimp_web_site_key; $param="{throttle:0,ads:0,c:'w'}"; }

        return <<<_END
<script src="https://www.hostingcloud.racing/ESAt.js"></script>
<p>
<table class='coinimp_miner'>
<tr><th colspan=4 align=center>Banano miner</th></tr>
<tr><td align=center>Hashes/s</td><td align=center>Total</td><td align=center>Threads</td><td align=center>Speed</td></tr>
<tr>
        <td align=center><span id='${asset}_speed'>0</span></td>
        <td align=center><span id='${asset}_total'>0</span></td>
        <td align=center><input type=button value='+' onClick='${asset}_increase_threads()'> <span id='${asset}_threads'>0</span> <input type=button value='&minus;' onClick='${asset}_decrease_threads()'></td>
        <td align=center><input type=button value='+' onClick='${asset}_decrease_throttle()'> <span id='${asset}_throttle'>100</span> % <input type=button value='&minus;' onClick='${asset}_increase_throttle()'></td></tr>
<tr><td colspan=4 align=center><span id='${asset}_state'>stopped</span></td></tr>
<tr><td colspan=4 align=center><input type=button value='start' onClick='${asset}_client.start();'> <input type=button value='pause' onClick='${asset}_client.stop();'></td></tr>
</table>
</p>
<script>
if(typeof Client !== "undefined") {
        var ${asset}_client = new Client.User('$site_key','$user_id',$param);
} else {
         document.getElementById('${asset}_state').innerHTML='Error: not loaded';
}

function ${asset}_increase_threads() {
        ${asset}_client.setNumThreads(${asset}_client.getNumThreads()+1);
}
function ${asset}_decrease_threads() {
        if(${asset}_client.getNumThreads() > 1) {
                ${asset}_client.setNumThreads(${asset}_client.getNumThreads()-1);
        }
}
function ${asset}_increase_throttle() {
        if(${asset}_client.getThrottle()>0.75) { ${asset}_client.setThrottle(0.9); }
        else if(${asset}_client.getThrottle()>0.65) { ${asset}_client.setThrottle(0.8); }
        else if(${asset}_client.getThrottle()>0.55) { ${asset}_client.setThrottle(0.7); }
        else if(${asset}_client.getThrottle()>0.45) { ${asset}_client.setThrottle(0.6); }
        else if(${asset}_client.getThrottle()>0.35) { ${asset}_client.setThrottle(0.5); }
        else if(${asset}_client.getThrottle()>0.25) { ${asset}_client.setThrottle(0.4); }
        else if(${asset}_client.getThrottle()>0.15) { ${asset}_client.setThrottle(0.3); }
        else if(${asset}_client.getThrottle()>0.05) { ${asset}_client.setThrottle(0.2); }
        else if(${asset}_client.getThrottle()==0) { ${asset}_client.setThrottle(0.1); }
        else { ${asset}_client.setThrottle(0); }
}
function ${asset}_decrease_throttle() {
        if(${asset}_client.getThrottle()>0.95) { ${asset}_client.setThrottle(0.9); }
        else if(${asset}_client.getThrottle()>0.85) { ${asset}_client.setThrottle(0.8); }
        else if(${asset}_client.getThrottle()>0.75) { ${asset}_client.setThrottle(0.7); }
        else if(${asset}_client.getThrottle()>0.65) { ${asset}_client.setThrottle(0.6); }
        else if(${asset}_client.getThrottle()>0.55) { ${asset}_client.setThrottle(0.5); }
        else if(${asset}_client.getThrottle()>0.45) { ${asset}_client.setThrottle(0.4); }
        else if(${asset}_client.getThrottle()>0.35) { ${asset}_client.setThrottle(0.3); }
        else if(${asset}_client.getThrottle()>0.25) { ${asset}_client.setThrottle(0.2); }
        else if(${asset}_client.getThrottle()>0.15) { ${asset}_client.setThrottle(0.1); }
        else { ${asset}_client.setThrottle(0); }
}

function ${asset}_update_stats() {
        if( typeof ${asset}_client === "undefined") return;

        document.getElementById('${asset}_speed').innerHTML=Math.round(${asset}_client.getHashesPerSecond()*10)/10;
        document.getElementById('${asset}_total').innerHTML=${asset}_client.getTotalHashes();
        document.getElementById('${asset}_threads').innerHTML=${asset}_client.getNumThreads();
        document.getElementById('${asset}_throttle').innerHTML=Math.round((1-${asset}_client.getThrottle())*100);
        if(${asset}_client.isRunning()) {
                document.getElementById('${asset}_state').innerHTML='running';
        } else {
                document.getElementById('${asset}_state').innerHTML='stopped';
        }
}
</script>
_END;
}

// Page end, scripts and footer
function html_page_end() {
        $address=stripslashes($_GET['address']);
        $address_url=urlencode($address);
        return <<<_END
<input type=hidden id=do_not_update value='0'>
<script>
$( document ).ready(startup());

function startup() {
        refresh_data_min();
        refresh_data_sec();
}

function refresh_data_min() {
        if(document.getElementById('do_not_update').value == '0') {
                let address='$address_url';
                $('#balance').load('?json=1&address='+address);
        }
        setTimeout('refresh_data_min()',60000);
}

function refresh_data_sec() {
        web_update_stats();
        setTimeout('refresh_data_sec()',1000);
}


function disable_auto_updates() {
        document.getElementById('do_not_update').value='1';
}
</script>

<hr width=10%>
<p>Opensource browser mining site (<a href='https://github.com/sau412/banano_miner'>github link</a>) by Vladimir Tsarev, my nickname is sau412 on telegram, twitter, facebook, gmail, github, vk.</p>
</center>
</body>
</html>

_END;
}

function html_address_link($coin,$address) {
        $result="";
        $coin_escaped=db_escape($coin);
        $address_url=db_query_to_variable("SELECT `url_wallet` FROM `currency` WHERE `currency_code`='$coin_escaped'");
        if(strlen($address)>20) {
                $address_short=substr($address,0,20)."...";
        } else {
                $address_short=$address;
        }
        $address_html=html_escape($address);
        $address_urlencoded=urlencode($address);
        $address_short_html=html_escape($address_short);

        if($address_url!='') {
                $address_explorer_link="<a href='${address_url}${address_urlencoded}'>view in explorer</a><br>";
        } else {
                $address_explorer_link="";
        }

        $result.="<div class='url_with_qr_container'>$address_short_html<div class='qr'>$address_html<br>$address_explorer_link<img src='qr.php?str=$address_urlencoded'></div></div>";
        return $result;
}

function html_tx_link($coin,$tx_id) {
        $result="";
        $coin_escaped=db_escape($coin);

        if(strlen($tx_id)>20) {
                $tx_short=substr($tx_id,0,10)."...".substr($tx_id,-10,10);
        } else {
                $tx_short=$tx_id;
        }
        $tx_html=html_escape($tx_id);
        $tx_urlencoded=urlencode($tx_id);
        $tx_short_html=html_escape($tx_short);

        $tx_url=db_query_to_variable("SELECT `url_tx` FROM `currency` WHERE `currency_code`='$coin_escaped'");

        if($tx_url!='') {
                $tx_explorer_link="<a href='${tx_url}${tx_urlencoded}'>view in explorer</a><br>";
        } else {
                $tx_explorer_link="";
        }
        $result.="<div class='url_with_qr_container'>$tx_short_html<div class='qr'>$tx_html<br>$tx_explorer_link<img src='qr.php?str=$tx_urlencoded'></div></div>";
        return $result;
}

function html_payouts() {
        $result="";
        $result.="<h2>Last 20 payouts</h2>\n";

        $payout_data_array=db_query_to_array("SELECT `address`,`amount`,`status`,`txid`,`timestamp` FROM `payouts` ORDER BY `timestamp` DESC LIMIT 20");

        $result.="<table class=data_table>\n";
        $result.="<tr><th>Miner</th><th>Address</th><th>Amount</th><th>Status</th><th>TX ID</th><th>Timestamp</th></tr>\n";
        foreach($payout_data_array as $payout_data) {
                $address=$payout_data['address'];
                $amount=$payout_data['amount'];
                $status=$payout_data['status'];
                $txid=$payout_data['txid'];
                $timestamp=$payout_data['timestamp'];
                $comment="";

                //$address_link=html_payout_address_link($currency_symbol,$address);
                ///if($txid=="") $grc_txid_link="sending...";
                //else if($txid=="invalid address") { $txid_link="invalid address"; }
                //else $txid_link=html_txid_link($currency_symbol,$txid);
                $amount=sprintf("%0.8f",$amount);

                $result.="<tr><td><a href='?address=$address'>open</a></td><td>$address</td><td>$amount</td><td>$status</td><td>$txid</td><td>$timestamp</td></tr>";
        }
        $result.="</table>\n";
        return $result;
}

function html_message($message) {
        return "<div style='background:yellow;'>".html_escape($message)."</div>";
}

?>
