<?php
// Coinhive API URLs
$coinimp_get_balance_url="https://www.coinimp.com/api/v2/user/balance";
$coinimp_get_reward_info_url="https://www.coinimp.com/api/v2/hashes";
//$coinhive_get_stats_site_info_url="https://api.coinhive.com/stats/site";

//require_once("settings.php");
//var_dump(coinimp_get_user_balance_detail("xmr","adaa5cd2882fc5b3d448b444bc9bd9c4"));

// Get balance of specific user
// Returns class with total, withdrawn and balance
function coinimp_get_user_balance_detail($asset,$address) {
        global $coinimp_private_key;
        global $coinimp_public_key;
        global $coinimp_xmr_site_key;
        global $coinimp_web_site_key;
        global $coinimp_get_balance_url;

//      $address_html=html_escape($address);
        // Setup cURL
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_HTTPHEADER,array('X-API-ID:'.$coinimp_public_key,'X-API-KEY:'.$coinimp_private_key));
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
        curl_setopt($ch,CURLOPT_POST,FALSE);

        if($asset=="xmr") $site_key=$coinimp_xmr_site_key;
        if($asset=="web") $site_key=$coinimp_web_site_key;

        curl_setopt($ch,CURLOPT_URL,$coinimp_get_balance_url."?site-key=$site_key&user=$address");
        $result = curl_exec ($ch);
        if($result=="") return 0;
        $data=json_decode($result);
        return $data->message;
        //if(isset($data->balance) && $data->balance) return $data->balance;
        //else return 0;
}

// Returns only balance
function coinimp_get_user_balance($asset,$address) {
        $data=coinimp_get_user_balance_detail($asset,$address);
        if(is_object($data) && property_exists($data,"hashes") && $data->hashes!=0) return $data->hashes;
        else return 0;
}

// Returns only balance
function coinimp_get_user_balance_cached($address) {
        $address_escaped=db_escape($address);
        db_query("INSERT INTO `coinimp_cache` (`id`,`balance`,`need_update`) VALUES ('$address_escaped',0,1)
                        ON DUPLICATE KEY UPDATE `need_update`=1");
        return db_query_to_variable("SELECT `balance` FROM `coinimp_cache` WHERE `id`='$address_escaped'");
}

// Get reward info
function coinimp_get_reward_info() {
        global $coinimp_private_key;
        global $coinimp_public_key;
        global $coinimp_xmr_site_key;
        global $coinimp_web_site_key;
        global $coinimp_get_reward_info_url;

//      $address_html=html_escape($address);
        // Setup cURL
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_HTTPHEADER,array('X-API-ID:'.$coinimp_public_key,'X-API-KEY:'.$coinimp_private_key));
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
        curl_setopt($ch,CURLOPT_POST,FALSE);

        if($asset=="xmr") $site_key=$coinimp_xmr_site_key;
        if($asset=="web") $site_key=$coinimp_web_site_key;

        curl_setopt($ch,CURLOPT_URL,$coinimp_get_reward_info_url."?site-key=$coinimp_xmr_site_key&currency=XMR");
        $result = curl_exec ($ch);
//var_dump($result,$coinimp_get_reward_info_url."?site-key=".$coinimp_xmr_site_key);
        if($result=="") return 0;
        $data=json_decode($result);
        return $data->message;
}
/*
// Get site info
function coinhive_get_stats_site_info() {
        global $coinhive_private_key;
        global $coinhive_get_stats_site_info_url;

        // Setup cURL
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
        curl_setopt($ch,CURLOPT_POST,FALSE);
        curl_setopt($ch,CURLOPT_URL,$coinhive_get_stats_site_info_url."?secret=$coinhive_private_key");
        $result = curl_exec ($ch);
        if($result=="") return 0;
        $data=json_decode($result);
        return $data;
}
*/
?>
