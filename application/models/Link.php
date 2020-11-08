<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Link extends CI_Model {

function hits($link,$request,$token='',$type = 1)
    {
        $Base_API = 'http://localhost/Society-Management/society_api/';
        $query = http_build_query($request);
        if ($type == 0) {
            $custom_type = 'GET';
            $url = $Base_API . $link . "?" . $query;
        } else {
            $custom_type = 'POST';
            $url = $Base_API . $link;
        }
      
        // $data = json_encode($data);
        $header = array("Authorization:".token_get());
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $custom_type);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response1 = curl_exec($ch);
        curl_close($ch);
        return $response1;

    }

    public function hit($link, $params, $type = 1, $headers = array()) {
        $query = http_build_query($params);
        $request = curl_init();
        if ($type == 0) {
            $data = '';
            foreach($params as $key=>$value)
                        $data .= $key.'='.$value.'&';
                 
                $data = trim($data, '&');
            $url = $link.'?'.$data;
        } else {
            $url = $link;
        }
        curl_setopt($request, CURLOPT_URL, $url);
        if (count($headers) > 0) {
            curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($request, CURLOPT_POSTFIELDS, $params);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($request, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
        $result = curl_exec($request);
        curl_close($request);
        return $result;
    } 
    
} 

