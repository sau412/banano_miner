<?php
// Reddit API related functions
$reddit_access_token_url="https://www.reddit.com/api/v1/access_token";
$reddit_me_url="https://oauth.reddit.com/api/v1/me";
//$reddit_inbox_url="https://oauth.reddit.com/message/inbox";
$reddit_inbox_url="https://oauth.reddit.com/message/unread";
$reddit_comment_url="https://oauth.reddit.com/api/comment";
$reddit_read_message_url="https://oauth.reddit.com/api/read_message";
$reddit_message_info_url="https://oauth.reddit.com/api/info";
$reddit_base_url="https://oauth.reddit.com/";
$reddit_compose_url="https://oauth.reddit.com/api/compose";

$reddit_access_token="";

// Send query to reddit
function reddit_send_query($url,$query,$type="get",$user_pass="") {
        global $reddit_access_token;
        global $reddit_useragent;

        $ch=curl_init();

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch,CURLOPT_USERAGENT,$reddit_useragent);
        if($reddit_access_token!='') {
                curl_setopt($ch,CURLOPT_HTTPHEADER,array("Authorization: bearer $reddit_access_token"));
        }

        if($user_pass!='') curl_setopt($ch,CURLOPT_USERPWD,$user_pass);

        if($type=="get") {
                curl_setopt($ch, CURLOPT_URL,$url."?".$query);
                curl_setopt($ch, CURLOPT_POST,FALSE);
        } else {
                curl_setopt($ch, CURLOPT_URL,$url);
                curl_setopt($ch, CURLOPT_POST,TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS,$query);
        }

        $result=curl_exec($ch);
        curl_close($ch);

        return $result;
}

// Get access token
function reddit_access_token() {
        global $reddit_access_token_url;
        global $reddit_access_token;

        global $reddit_http_login,$reddit_http_password;
        global $reddit_username,$reddit_password;

        $user_pass="$reddit_http_login:$reddit_http_password";
        $query="grant_type=password&username=$reddit_username&password=$reddit_password";
        $result=reddit_send_query($reddit_access_token_url,$query,"post",$user_pass);
//      var_dump($result);
        $data=json_decode($result);
//      var_dump($data);
        $reddit_access_token=$data->access_token;
        return $reddit_access_token;
}

/*

// Get own info
function reddit_me() {
        global $reddit_me_url;
        global $reddit_access_token;

        $result=reddit_send_query($reddit_me_url,"","get");
//      var_dump($result);
        $data=json_decode($result);
        return $data;
}
*/

// Get inbox
function reddit_inbox() {
        global $reddit_inbox_url;
        $result=reddit_send_query($reddit_inbox_url,"","get");
//      var_dump($result);
        $data=json_decode($result);
//      var_dump($data);
        return $data;
}

// Send public or private message
function reddit_comment($parent,$text) {
        global $reddit_comment_url;
        $parent_url=urlencode($parent);
        $text_url=urlencode($text);
        $query="parent=$parent_url&text=$text_url";
        $result=reddit_send_query($reddit_comment_url,$query,"post");
        //var_dump($result);
        $data=json_decode($result);
        if($data->success==TRUE) return TRUE;
        else return FALSE;
}

// Mark message as read
function reddit_mark_read($id) {
        global $reddit_read_message_url;
        $id_url=urlencode($id);
        $query="id=$id_url";
//echo "$query\n";
        $result=reddit_send_query($reddit_read_message_url,$query,"post");
        //var_dump($result);
        $data=json_decode($result);
        return $data;
}

// Get message info
function reddit_get_message_info($id) {
        global $reddit_message_info_url;
        $id_url=urlencode($id);
        $query="id=$id_url";
//echo "$query\n";
        $result=reddit_send_query($reddit_message_info_url,$query,"get");
        $data=json_decode($result);
//var_dump($data);
        return $data->data->children[0];
}

function reddit_get_message_tree($id) {
        global $reddit_base_url;

        if(preg_match("/^[^_]+_(.+)$/",$id,$matches)) {
                $id=$matches[1];
        }
        $url="${reddit_base_url}comments/$id";
        $query="depth=1000&limit=10000";
        $result=reddit_send_query($url,$query,"get");
        $data=json_decode($result);
//var_dump($data);
        return $data;
}

function reddit_get_new_posts($subreddit) {
        global $reddit_base_url;
        $url="${reddit_base_url}${subreddit}/new";
        $query="limit=100";
//echo "$query\n";
        $result=reddit_send_query($url,$query,"get");
//var_dump($result);
        $data=json_decode($result);
//var_dump($data);
        return $data->data->children[0];
}

// Send public or private message
function reddit_compose($to,$subject,$text) {
        global $reddit_compose_url;
        $to_url=urlencode($to);
        $subject_url=urlencode($subject);
        $text_url=urlencode($text);
        $query="api_type=json&subject=$subject_url&text=$text_url&to=$to_url";
        $result=reddit_send_query($reddit_compose_url,$query,"post");
        //var_dump($result);
        $data=json_decode($result);
        return $data;
}


?>
