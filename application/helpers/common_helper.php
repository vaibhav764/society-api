<?php
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;
function dec_enc($action, $string) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'society key';
    $secret_iv = 'society iv';
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ($action == 'encrypt') {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if ($action == 'decrypt') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}
function trim_string($string) {
    if (!empty($string)) {
        $string = trim($string);
        $string = preg_replace('/\s/', '', $string);
        return $string;
    } else {
        return false;
    }
}
function getUserIP()
{
    // Get real visitor IP behind CloudFlare network
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
              $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
              $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}
function send_sms_check($mobile, $smstext)
{
    $send_sms_data = array (
        'sender' => 'SIPLBD',
        'route' => '4',
        'country' => '91',
        'sms' => 
        array (
          0 => 
          array (
            'message' => $smstext,
            'to' => 
            array (
              0 => $mobile,
            ),
          ),
        ),
    );
    $authentication_key='162806Al0fmplkeo595153d9';
    $curl_sms = curl_init();
    curl_setopt_array($curl_sms, array(
    CURLOPT_URL => "https://api.msg91.com/api/v2/sendsms",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($send_sms_data),
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_HTTPHEADER => array(
        "authkey: $authentication_key",
        "content-type: application/json"
    ),
    ));

    $response1 = curl_exec($curl_sms);
    $err = curl_error($curl_sms);

    curl_close($curl_sms);
return $response1;
}
function smslog($sender_id='',$receiver_id='',$receiver_phone='',$content='',$date_time='',$ip_address='')
{
   $data = array('sender_id'=>$sender_id,'receiver_id'=>$receiver_id,'receiver_phone' =>$receiver_phone,'content'=>$content,'date_time'=>$date_time,'ip_address'=>$ip_address);
   return $data;
}

// function send_email($email, $subject, $message, $attach = '') {
//     $this->load->library('email');
//     $this->email->set_mailtype("html");
//     $this->email->from('donotreply@gmail.com', 'Bio-Data');
//     $this->email->to($email);
//     $this->email->subject($subject);
//     $this->email->message($message);
//     $this->email->attach($attach);
//     $this->email->send();
// }
function sendEmail($from,$to,$subject,$message,$attach=''){
    error_reporting(0);
    $CI = get_instance();
    $CI->load->library('email');
    $CI->email->set_mailtype("html");
    $CI->email->from($from);
    $CI->email->to($to);
    $CI->email->subject($subject);
    $CI->email->message($message);
    $CI->email->attach($attach);
    $CI->email->send();
    // // Always set content-type when sending HTML email
    // $headers = "MIME-Version: 1.0" . "\r\n";
    // $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    // // More headers
    // $headers .= 'From: '.$from. "\r\n";
    // // $headers .= 'Cc: myboss@example.com' . "\r\n";
    // mail($to,$subject,$message,$headers);
}
function set_upload_options()
{   
    //upload an image options
    $config = array();
    $config['upload_path'] = 'uploads/chat';
    $config['allowed_types'] = 'jpeg|jpg|png|doc|pdf|xls|csv|xlsx';
    $config['max_size']      = '10000000';
    $config['overwrite']     = FALSE;

    return $config;
}
function emaillog($sender_id='',$receiver_id='',$receiver_email='',$content='',$subject='',$attachment_type='',$attachment_data='',$date_time='',$ip_address='')
{
   $data = array('sender_id'=>$sender_id,'receiver_id'=>$receiver_id,'receiver_email' =>$receiver_email,'content'=>$content,'subject'=>$subject,'attachment_type'=>$attachment_type,'attachment_data'=>$attachment_data,'date_time'=>$date_time,'ip_address'=>$ip_address);
   return $data;
}
function generateRandomString($length = 10) 
{
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
function validateToken(){
    // echo '<pre>'; print_r($_SERVER); exit;
    $CI = get_instance();
    if (array_key_exists('HTTP_AUTHORIZATION', $_SERVER) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $decodedToken = AUTHORIZATION::validateToken($_SERVER['HTTP_AUTHORIZATION']);
        if ($decodedToken != false) {
           return true;
        } else {
            return false;
        }
    }
    return false;
}

?>