<?php

function strim($str,$charlist=" ",$option=0){
    if(is_string($str))
    {
        // Translate HTML entities
        $return = strtr($str, array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)));
        // Remove multi whitespace
        $return = preg_replace("@\s+\s@Ui"," ",$return);
        // Choose trim option
        switch($option)
        {
            // Strip whitespace (and other characters) from the begin and end of string
            default:
            case 0:
                $return = trim($return,$charlist);
            break;
            // Strip whitespace (and other characters) from the begin of string
            case 1:
                $return = ltrim($return,$charlist);
            break;
            // Strip whitespace (and other characters) from the end of string
            case 2:
                $return = rtrim($return,$charlist);
            break;
               
        }
    }
    return $return;
}
function bundle_css($css_files){
    $content = '';
    foreach ($css_files as $key => $value) {
        $content .= file_get_contents($value);
    }
    file_put_contents(FCPATH.'\assets\bundle.css', minifyCss($content));
}
function bundle_css_append($css_files){
    $content = file_get_contents(base_url().'assets/bundle.css');
    foreach ($css_files as $key => $value) {
        $content .= file_get_contents($value);
    }
    file_put_contents(FCPATH.'\assets\bundle.css', minifyCss($content));
}

function bundle_js($js_files){

    $expressions = array(
        // 'MULTILINE_COMMENT'     => '\Q/*\E[\s\S]+?\Q*/\E',
        'SINGLELINE_COMMENT'    => '(?:http|ftp)s?://(*SKIP)(*FAIL)|//.+',
        'WHITESPACE'            => '^\s+|\R\s*'
    );
    $content = '';
    foreach ($js_files as $key => $value) {

        $url = $value;
        $value = file_get_contents($value);

        
        // remove single line comments
        $value = preg_replace('#^\s*//.+$#m', "", $value);

        //remove multi line comments
        // $value = preg_replace('!/\*.*?\*/!s', '', $value);
        $value = preg_replace('/\n\s*\n/', "\n", $value);
        $value = preg_replace('/(\s+)\/\*([^\/]*)\*\/(\s+)/s', "\n", $value);
        // if(!isset($value_arr[1]) ){
            // foreach ($expressions as $key => $expr) {
            //     $value = preg_replace('~'.$expr.'~m', '', $value);
            // }
        // }
        // $value .= '/******'.$url.'********
        // *****/';
        $content .=$value;
    }
    file_put_contents(FCPATH.'\assets\bundle.js', $content);
}

function bundle_js_append($js_files= []){
    $content = '';
    foreach ($js_files as $key => $value) {
        $content .= file_get_contents($value);
    }
    $expressions = array(
        'MULTILINE_COMMENT'     => '\Q/*\E[\s\S]+?\Q*/\E',
        'SINGLELINE_COMMENT'    => '\/\/[^;)]*$',
        'WHITESPACE'            => '^\s+|\R\s*'
    );

    foreach ($expressions as $key => $expr) {
        $data = preg_replace('~'.$expr.'~m', '', $content);
    }
    file_put_contents(FCPATH.'\assets\bundle.js', $content.PHP_EOL , FILE_APPEND | LOCK_EX);
    // file_put_contents(FCPATH.'\assets\bundle.js', $data);
}


function minifyCss($css) {
  // some of the following functions to minimize the css-output are directly taken
  // from the awesome CSS JS Booster: https://github.com/Schepp/CSS-JS-Booster
  // all credits to Christian Schaefer: http://twitter.com/derSchepp
  // remove comments
  $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
  // backup values within single or double quotes
  preg_match_all('/(\'[^\']*?\'|"[^"]*?")/ims', $css, $hit, PREG_PATTERN_ORDER);
  for ($i=0; $i < count($hit[1]); $i++) {
    $css = str_replace($hit[1][$i], '##########' . $i . '##########', $css);
  }
  // remove traling semicolon of selector's last property
  $css = preg_replace('/;[\s\r\n\t]*?}[\s\r\n\t]*/ims', "}\r\n", $css);
  // remove any whitespace between semicolon and property-name
  $css = preg_replace('/;[\s\r\n\t]*?([\r\n]?[^\s\r\n\t])/ims', ';$1', $css);
  // remove any whitespace surrounding property-colon
  $css = preg_replace('/[\s\r\n\t]*:[\s\r\n\t]*?([^\s\r\n\t])/ims', ':$1', $css);
  // remove any whitespace surrounding selector-comma
  $css = preg_replace('/[\s\r\n\t]*,[\s\r\n\t]*?([^\s\r\n\t])/ims', ',$1', $css);
  // remove any whitespace surrounding opening parenthesis
  $css = preg_replace('/[\s\r\n\t]*{[\s\r\n\t]*?([^\s\r\n\t])/ims', '{$1', $css);
  // remove any whitespace between numbers and units
  $css = preg_replace('/([\d\.]+)[\s\r\n\t]+(px|em|pt|%)/ims', '$1$2', $css);
  // shorten zero-values
  $css = preg_replace('/([^\d\.]0)(px|em|pt|%)/ims', '$1', $css);
  // constrain multiple whitespaces
  $css = preg_replace('/\p{Zs}+/ims',' ', $css);
  // remove newlines
  $css = str_replace(array("\r\n", "\r", "\n"), '', $css);
  // Restore backupped values within single or double quotes
  for ($i=0; $i < count($hit[1]); $i++) {
    $css = str_replace('##########' . $i . '##########', $hit[1][$i], $css);
  }
  return $css;
}



function AssociativeArrayToStr($array, $key_to_concat, $sep) {
    $i = 0;
    $string = "";
    foreach ($array as $key => $values) {
        foreach ($values as $k => $v) {
            if($k == $key_to_concat) {
                if($i == 0)
                    $string = $v;
                else
                    $string .= $sep .$v;
                $i++;
            }
        }
    }
    return $string;
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


function objectToArray ($object) {
    if(!is_object($object) && !is_array($object))
        return $object;

    return array_map('objectToArray', (array) $object);
}

function get_random_number($digits_needed){
    $count=0;
    $random_number = '';
    while ( $count < $digits_needed ) {
        $random_digit = mt_rand(0, 9);
        $random_number .= $random_digit;
        $count++;
    }
    return $random_number;
}

function tj_array_column($assoc_array, $column) {
    $i = 0;
    $return_array = array();
    if (is_array($assoc_array)) {
        foreach ($assoc_array as $array) {
            $return_array[$i] = $array[$column];
            $i++;
        }
    }
    
    return $return_array;
}


function myUrlDecode($string) {
    $entities =     array('!26','!26', '!2A', '!27', '!28', '!29', '!3B', '!3A', '!40', '!3D', '!2B', '!24', '!2C', '!2F', '!3F', '!25', '!23', '!5B', '!5D', '!6A', '!6B');
    $replacements = array("&amp;",'&', '*',   "'",   "(",   ")",   ";",   ":",   "@",    "=",   "+",   "$",   ",",   "/",   "?",   "%",   "#",   "[",   "]",   ' '  , '.' );
    return str_replace($entities, $replacements, $string);
}

function myUrlEncode($string) {
    $replacements =  array('!26', '!26', '!2A', '!27', '!28', '!29', '!3B', '!3A', '!40', '!3D', '!2B', '!24', '!2C', '!2F', '!3F', '!25', '!23', '!5B', '!5D', '!6A', '!6B');
    $entities     =  array('&amp; ','&',   '*',   "'",   "(",   ")",   ";",   ":",   "@",    "=",   "+",   "$",   ",",   "/",   "?",   "%",   "#",   "[",   "]",   ' '  , '.' );
    return str_replace($entities, $replacements, $string);
}

function print_array($array){
    echo "<pre>";
    print_r($array);
    exit;
}

function print_view($array){
    echo "<pre>";
    print_r($array);
}

function getResultArray($Q){
    $data=array();
    if($Q->num_rows()>0){
        foreach($Q->result_array() as $row){
            $data[]=$row;
        }
    }
    return $data;
}

function flatten(array $array) {
    $return = array();
    array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
    return $return;
}

function SimpleArray($search_array){
    $data=array();
    foreach($search_array as $row){
        foreach($row as $k => $v)
        $data[]=$v;
    }
    return $data;
}

function getResultArraySimpleArray($Q){
    $data=array();
    if($Q->num_rows()>0){
        foreach($Q->result_array() as $row){
            foreach($row as $k => $v) $data[]=$v;
        }
    }
    return $data;
}

function upto2Decimal($price=''){
   return number_format((float)$price, 2, '.', '');
}

function getCurrentDate() {
    $now = date("Y-m-d h:i:s");
    return $now;
}

function getMonthsDD() {
    $month_array=array(
            '0'=>"Month",
            '1'=>"January",
            '2'=>"February",
            '3'=>"March",
            '4'=>"April",
            '5'=>"May",
            '6'=>"June",
            '7'=>"July",
            '8'=>"August",
            '9'=>"September",
            '10'=>"October",
            '11'=>"November",
            '12'=>"December");
    return $month_array;
}

function getMonthsSmall(){
    $month_array=array(
            '6'=>"Jun",
            '7'=>"Jul",
            '8'=>"Aug",
            '9'=>"Sep",
            '10'=>"Oct",
            '11'=>"Nov",
            '12'=>"Dec",
            '1'=>"Jan",
            '2'=>"Feb",
            '3'=>"Mar",
            '4'=>"Apr",
            '5'=>"May",);
    return $month_array;
}


/*

|--------------------------------------------------------------------------

| Following Functions Created By Paperplane @rv!nd

|--------------------------------------------------------------------------

*/


function findKey($array, $keySearch) {
    foreach ($array as $key => $item) {
        if ($key == $keySearch) {
            return 1;
        } else {
            if (isset($array[$key]))
                findKey($array[$key], $keySearch);
        }
    }
    return 0;
}

function filter_unique_array($arrs, $id) {
    foreach($arrs as $k => $v) 
    {
        foreach($arrs as $key => $value) 
        {
            if($k != $key && $v[$id] == $value[$id])
            {
                 unset($arrs[$k]);
            }
        }
    }
    return $arrs;
}


/*END OF @rv!nd*/

function copyImage($imageSrc, $imageDest) {
    $src = urldecode($imageSrc);
    $len1 = strlen($src);
    $len2 = strlen(base_url());
    $fpath = substr($src, $len2 - 1, $len1);
    $pathParts = explode('/', $src);
    $oldFileName = $pathParts[count($pathParts) - 1];
    $ext = explode('.',$oldFileName);
    $newFileName = generateRandomCode().'.'.$ext[1];
    $srcCopy = $_SERVER['DOCUMENT_ROOT'].$fpath;
    $destCopy = $_SERVER['DOCUMENT_ROOT'].$imageDest.$newFileName;
    
    $copy = copy($srcCopy, $destCopy);
    unlink($srcCopy);
    if($copy == false) {
        return false;
    }
    return $newFileName;
}

function generateRandomCode() {
    $d=date ("d");
    $m=date ("m");
    $y=date ("Y");
    $t=time();
    $dmt=$d+$m+$y+$t;
    $ran= rand(0,10000000);
    $dmtran= $dmt+$ran;
    $un=  uniqid();
    $dmtun = $dmt.$un;
    $mdun = md5($dmtran.$un);
    $sort=substr($mdun, 16); // if you want sort length code.
    return $mdun;
}

/**
 *
 * Function to make URLs into links
 *
 * @param string The url string
 *
 * @return string
 *
 **/
function makeLink($string, $label){

    /*** make sure there is an http:// on all URLs ***/
    $string = preg_replace("/([^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/i", "$1http://$2",$string);
    /*** make all URLs links ***/
    $string = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<a target=\"_blank\" href=\"$1\">.$label.</a>",$string);
    /*** make all emails hot links ***/
    $string = preg_replace("/([\w-?&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/i","<a href=\"mailto:$1\">$1</a>",$string);

    return $string;
}

function highlightkeyword($str, $search) {
    $highlightcolor = "#daa732";
    $occurrences = substr_count(strtolower($str), strtolower($search));
    $newstring = $str;
    $match = array();
 
    for ($i=0;$i<$occurrences;$i++) {
        $match[$i] = stripos($str, $search, $i);
        $match[$i] = substr($str, $match[$i], strlen($search));
        $newstring = str_replace($match[$i], '[#]'.$match[$i].'[@]', strip_tags($newstring));
    }
 
    $newstring = str_replace('[#]', '<span style="color: '.$highlightcolor.';">', $newstring);
    $newstring = str_replace('[@]', '</span>', $newstring);
    return $newstring;
 
}

function whatever($array, $key, $val) {
    foreach ($array as $item)
        if (isset($item[$key]) && $item[$key] == $val)
            return true;
    return false;
}

function whatever_return($array, $key, $val) {
    foreach ($array as $item)
        if (isset($item[$key]) && $item[$key] == $val)
            return $item;
    return false;
}

function find_key_in_array($array, $key) {
    $result = array();
    foreach ($array as $item){
        if (isset($item[$key])){
            $result[]=$item;
        }
    }
    return $result;
}

function custom_sort($array,$order){ //length of order and array to be same
    usort($array, function ($a, $b) use ($order) {
        $pos_a = array_search($a, $order);
        $pos_b = array_search($b, $order);
        return $pos_a - $pos_b;
    });
    return $array;
}

function sortArrayByArray($array,$orderArray) {
    $ordered = array();
    foreach($orderArray as $key => $value) {
        if(array_key_exists($key,$array)) {
                $ordered[$key] = $array[$key];
                unset($array[$key]);
        }
    }
    return $ordered+$array;
}

function convert_number_to_words($number) {
    
    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = array(
        0                   => 'zero',
        1                   => 'one',
        2                   => 'two',
        3                   => 'three',
        4                   => 'four',
        5                   => 'five',
        6                   => 'six',
        7                   => 'seven',
        8                   => 'eight',
        9                   => 'nine',
        10                  => 'ten',
        11                  => 'eleven',
        12                  => 'twelve',
        13                  => 'thirteen',
        14                  => 'fourteen',
        15                  => 'fifteen',
        16                  => 'sixteen',
        17                  => 'seventeen',
        18                  => 'eighteen',
        19                  => 'nineteen',
        20                  => 'twenty',
        30                  => 'thirty',
        40                  => 'fourty',
        50                  => 'fifty',
        60                  => 'sixty',
        70                  => 'seventy',
        80                  => 'eighty',
        90                  => 'ninety',
        100                 => 'hundred',
        1000                => 'thousand',
        1000000             => 'million',
        1000000000          => 'billion',
        1000000000000       => 'trillion',
        1000000000000000    => 'quadrillion',
        1000000000000000000 => 'quintillion'
    );
    
    if (!is_numeric($number)) {
        return false;
    }
    
    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . convert_number_to_words(abs($number));
    }
    
    $string = $fraction = null;
    
    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }
    
    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convert_number_to_words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convert_number_to_words($remainder);
            }
            break;
    }
    
    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }
    
    return $string;
}

function FindImageURL($image_url='',$imagename=''){
    //$imageurl = $image_url.$imagename;
    //$url=getimagesize($imageurl);
    $imageurl = '';
    list($width, $height, $type, $attr) = getimagesize($_SERVER['DOCUMENT_ROOT'] . '/contourtek/crawled_image/'.$imagename);  
    //echo 'height:'.$width; exit;
    //print_array($_SERVER['DOCUMENT_ROOT']);exit;

    if(isset($width) && $width!=''){
        $imageurl = $image_url.$imagename;//$_SERVER['DOCUMENT_ROOT'] . '/contourtek/crawled_image/'.$imagename;
    }else{
        $imageurl = base_url('assets/images').'/default_img.gif';
    }
    
    return $imageurl;
}

function calc_per($numerator="",$denomenator="",$precision='0'){
    if($denomenator>0){
        $res = round( ($numerator / $denomenator) * 100, $precision );
        return $res;
    }else{
        return 0;
    }
}

function get_product_drpdwn_qty($unit_id=""){
    $array_1000k = array('1','2','3','6'); //1:kg,2:Gram,3:Liter,6:Mili liter
    $array_1pcs = array('4','7'); //4:Piece, 7:Bunch,
    $array_12dozen = array('5'); //5:Dozen
    $str_option = "";
    if($unit_id!=""){
        if(in_array($unit_id, $array_1000k)){ 
            $str_option = '<option value="250">250 gm</option><option value="500">500 gm</option><option value="1000" selected>1 Kg</option>'; //<option value="100">100 gm</option>
        }else if(in_array($unit_id, $array_1pcs)){ 
            $str_option = '<option value="1" selected>1 pcs/pkt</option><option value="2">2 pcs/pkt</option><option value="3">3 pcs/pkt</option><option value="4">4 pcs/pkt</option><option value="5">5 pcs/pkt</option>';
        }else if(in_array($unit_id, $array_12dozen)){
            $str_option = '<option value="6">6 pcs</option><option value="12" selected>1 dozen</option>';
        }else{
            $str_option = '<option value="" selected>No Unit</option>';
        } 
    }else{
        $str_option = '';
    }
    return $str_option;
}

function gram_to_kg($unit_id='',$qty=''){
    $array_1000k = array('1','2','3','6'); //1:kg,2:Gram,3:Liter,6:Mili liter
    $array_1pcs = array('4','7'); //4:Piece, 7:Bunch,
    $array_12dozen = array('5'); //5:Dozen

    $kg_values = $qty;
    if($unit_id!='' && $qty>0){
        if(in_array($unit_id, $array_1000k)){
            $kg_values = upto2Decimal($qty / 1000);
        }else if(in_array($unit_id, $array_12dozen)){
            $kg_values = upto2Decimal($qty / 12);
        }
    }
    return $kg_values;
}

function unit_wise_price($unit_id='',$price=''){
    $array_1000k = array(1,2,3,6); //1:kg,2:Gram,3:Liter,6:Mili liter
    $array_1pcs = array(4,7); //4:Piece, 7:Bunch,
    $array_12dozen = array(5); //5:Dozen

    //echo $unit_id.'='.$price;

    $price_data = array();
    if($unit_id!=""){
        if(in_array($unit_id, $array_1000k)){

            $str_option = array(250,500,1000); //100
            foreach ($str_option as $key => $qty_value) {
                if($qty_value==='1000'){
                     $st_array = array(
                        'price_title'=> ' 1 Kg for Rs. '.$price,
                        'price'=>upto2Decimal($price),
                        'qty'=>$qty_value
                    );
                }else{
                    $qty_price = upto2Decimal(($price / 1000) * $qty_value);
                    $st_array = array(
                        'price_title'=> $qty_value .' gm for Rs. '.$qty_price,
                        'price'=>upto2Decimal($qty_price),
                        'qty'=>$qty_value
                    );
                }
                $price_data[$key] = $st_array;
                //array_push($price_data,$st_array);
            }

        }else if(in_array($unit_id, $array_1pcs)){ 
            
            $str_option = array(1,2,3,4,5);
            foreach ($str_option as $key => $qty_value) {
                if($qty_value==='1'){ // for single 
                     $st_array = array(
                        'price_title'=> ' 1 pcs/pkt for Rs. '.upto2Decimal($price),
                        'price'=>upto2Decimal($price),
                        'qty'=>$qty_value
                    );
                }else{
                    $qty_price = upto2Decimal($price * $qty_value);
                    $st_array = array(
                        'price_title'=> $qty_value .' pcs/pkt for Rs. '.$qty_price,
                        'price'=>upto2Decimal($qty_price),
                        'qty'=>$qty_value
                    );
                }
                $price_data[$key] = $st_array;
            }


        }else if(in_array($unit_id, $array_12dozen)){
            
            $str_option = array(0.5,1,2,3,4,5); // 0.5=> half dozen, default 1=> dozen

            foreach ($str_option as $key => $qty_value) {
                if($qty_value==='1'){
                     $st_array = array(
                        'price_title'=> ' 1 dozen for Rs. '.upto2Decimal($price),
                        'price'=>upto2Decimal($price),
                        'qty'=>$qty_value
                    );
                }else{
                    $qty_price = (upto2Decimal($price) / 12) *($qty_value);
                    $st_array = array(
                        'price_title'=> $qty_value .' pcs for Rs. '.upto2Decimal($qty_price),
                        'price'=>upto2Decimal($qty_price),
                        'qty'=>$qty_value
                    );
                }
                $price_data[$key] = $st_array;
            }


        } 
    }
    return $price_data;
}

function access( $module ='' ,$permission ='' )
{
    $CI = get_instance();
    $CI->load->model('model');
    $role_id = $CI->session->userdata('op_user_role');
   
    $user_role = $CI->model->getData('op_user_role',array('role_id'=>$role_id));
    $role_access_level = $CI->model->getData('role_access_level',array('role_id'=>$role_id));

    $access = array();
    foreach ($role_access_level as $key => $value) {
        $access[$value['module_name']] = $value;
    }

    if(isset($access[$module][$permission]) && $access[$module][$permission] == '1'){
        return true;
    }
    else{
        return false;
    }
    
}


function accessModule( $module ){
    $CI = get_instance();
    $CI->load->model('model');
    $role_id = $CI->session->userdata('op_user_role');
   
    $user_role = $CI->model->getData('op_user_role',array('role_id'=>$role_id));
    $role_access_level = $CI->model->getData('role_access_level',array('role_id'=>$role_id));

    $access = array();
    foreach ($role_access_level as $key => $value) {
        $access[$value['module_name']] = $value;
    }

    if(isset($access[$module])){
        $mod = $access[$module];
        if((!isset($mod['view_access']) || $mod['view_access'] == 0) && 
            (!isset($mod['update_access']) || $mod['update_access'] == 0 ) && 
            (!isset($mod['full_access']) || $mod['full_access'] == 0 )) {
            return false;
        }
        else{
            return true;
        }
    }
    else{
        return false;
    }   
}


function send_email($from,$email, $subject, $message, $attach = '') {
    $CI = get_instance();
    $CI->load->library('email');
    $CI->email->set_mailtype("html");
    $CI->email->from($from);
    $CI->email->to($email);
    $CI->email->subject($subject);
    $CI->email->message($message);
    $CI->email->attach($attach);
    $CI->email->send();
}

function sendEmail($from,$to,$subject,$message,$attach=''){
    // echo  $message;
    $CI = get_instance();
    $config = Array(
      'protocol' => 'smtp',
      'smtp_host' => 'ssl://smtp.googlemail.com',
      'smtp_port' => 465,
      'smtp_user' => 'piyush.nerkar@microlan.in', 
      'smtp_pass' => 'microlan@123', 
      'mailtype' => 'html',
      'charset' => 'iso-8859-1',
      'wordwrap' => TRUE
    );


    $CI->load->library('email',$config);
    $CI->email->set_newline("\r\n");
    $CI->email->from($from);
    $CI->email->to($to);
    $CI->email->subject($subject);
    $CI->email->message($message);
    if(!empty($attach)){
         $CI->email->attach($attach);
    }
    if($CI->email->send())
    {
     echo 'Email send.';
    }
    else
    {
     show_error($this->email->print_debugger());
    }
}

function sendSMS($message,$mobile_no){
    $message = urlencode($message);
    $url = "https://api.textlocal.in/send/?username=piyush.nerkar@microlan.in&hash=Krishna@123&sender=Microlan&numbers=91".$mobile_no."&message=".$message.".";
    /*$url = "http://sms2biz.microlan.in/sendSMS?username=dtfarm&message=".$smssubject."&sendername=DTFARM&smstype=TRANS&numbers=".$mobile_no."&apikey=510ba168-561a-4afc-8925-eeb3e8aa9b59";*/

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    $output = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
}




/****************************** JWT ***********************************************/
    
    function now(){
        return strtotime(date('Y-m-d h:i:s'));
    }
    
    function validateTimestamp($token)
    {
        $CI =& get_instance();
        $token = decodeToken($token);
        if ($token != false && (now() - $token->timestamp < ($CI->config->item('token_timeout') * 60))) {
            return $token;
        }
        return false;
    }

    function validateToken()
    {
        $CI = get_instance();
        $headers = apache_request_headers();
        if (array_key_exists('softo_auth', $headers) && !empty($headers['softo_auth'])){
            $decodedToken = decodeToken($headers['softo_auth']);
            if ((now() - $decodedToken['timestamp']) < ($CI->config->item('token_timeout') * 60)) {
                return true;
            }
        }
        return false;
    }

    function decodeToken($token)
    {
        $CI =& get_instance();
        $decodeToken = JWT::decode($token, $CI->config->item('jwt_key'));
        $decodeToken = objectToArray($decodeToken);
        if(isset($decodeToken['code']) && $decodeToken['code'] == 401){
            redirect('superadmin');
        }
        else{
            return $decodeToken;
        }
    }

    function generateToken($data)
    {
        $CI =& get_instance();
        return JWT::encode($data, $CI->config->item('jwt_key'));
    }

    function getHeaders(){
        $CI = get_instance();
        $session_id = $CI->session->userdata('session_id');
        $token = $CI->link->hit('admin_api/get_token',['session_id'=>$session_id,'logged_in'=>true]); 
        $headers = array('softo_auth:'.$token);
        return $headers;
    }

    function get_agent(){
        $CI = get_instance();
        if ($CI->agent->is_browser())
        {
                $agent = $CI->agent->browser().' '.$CI->agent->version();
        }
        elseif ($CI->agent->is_robot())
        {
                $agent = $CI->agent->robot();
        }
        elseif ($CI->agent->is_mobile())
        {
                $agent = $CI->agent->mobile();
        }
        else
        {
                $agent = 'Unidentified User Agent';
        }

        return $agent;
    }
/****************************** JWT ***********************************************/
function decrypt_password($password='')
{
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'portal key';
    $secret_iv = 'portal iv';
    // hash
    $key = hash('sha256', $secret_key);
    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    
    $output = openssl_decrypt(base64_decode($password), $encrypt_method, $key, 0, $iv);

    return $output;
}

function encyrpt_password($password='')
{
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'portal key';
    $secret_iv = 'portal iv';
    // hash
    $key = hash('sha256', $secret_key);
    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    $output = openssl_encrypt($password, $encrypt_method, $key, 0, $iv);
    $output = base64_encode($output);
    return $output;
}


?>