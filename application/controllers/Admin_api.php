<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// error_reporting(0);
   require APPPATH . '/libraries/REST_Controller.php';

class Admin_api extends CI_Controller {
    public function __construct() {
        parent::__construct();
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Type: application/json; charset=utf-8');
        date_default_timezone_set('Asia/Kolkata');
        $this->load->library('upload');
    }
    /*
    200 = OK
    201 = Bad Request (Required param is missing)
    202 = No Valid Auth key
    204 = No post data
    203 = Generic Error
    205 = Form Validation failed
    206 = Queury Failed
    207 = Already Logged-In Error
    208 = Curl Failed
    */
    public function index() {
        $response = array('status' => false, 'msg' => 'Oops! Please try again later.', 'code' => 200);
        echo json_encode($response);
    }
    // public function signup() {
    //     $response = array('code' => - 1, 'status' => false, 'message1' => '');
    //         if ($_SERVER["REQUEST_METHOD"] == "POST")
    //         {
    //             $name = $this->input->post('name');
    //             $email = $this->input->post('email');
    //             $phone = $this->input->post('phone');
    //             $password = $this->input->post('password');
    //             if(empty($name)){
    //                 $response['message1']="Name is required";
    //                 $response['code']=201;
    //             }elseif(empty($email)){
    //                 $response['message1']="Email is required";
    //                 $response['code']=201;
    //             }elseif(empty($phone)){
    //                 $response['message1']="Contact No is required";
    //                 $response['code']=201;
    //             }elseif(empty($password)){
    //                 $response['message1']="Password is required";
    //                 $response['code']=201;
    //             }else{
    //                //   $isExistEmail = $this->model->isExist('tbl_login', 'email', $email);
    //                //   // echo '<pre>'; print_r($isExistEmail); exit;
    //                // if (!$isExistEmail) {
    //                //      $response['message'] = 'Incorrect Email';
    //                //      $response['code'] = 201;
    //                //      echo json_encode($response);
    //                //      return;
    //                //  }else{
    //                     $data = array("name" => $name, "email" => $email, 'contact_no' => $phone, "password" => dec_enc('encrypt', $password),"user_type"=>"Admin");
    //                     // echo '<pre>'; print_r($data); exit;
    //                      $this->model->insertData('tbl_login', $data);
    //                     // $response = $this->model->common_data_ins('tbl_login', $data);
    //                     // $login_id = $this->db->insert_id();
    //                     // $get_user_data = $this->model->get_data('tbl_login','id',$login_id);
    //                     // $get_user_data = json_decode(json_encode($get_user_data), true);
    //                     // $receiver_id='';
    //                     // $response['user_data']= $get_user_data['data'][0];
    //                     $response['code'] = 200;
    //                     // }
    //             }
    //         }
    //         else
    //         {
    //             $response['message1'] = 'No direct script is allowed.';
    //             $response['code'] = 204;
    //         }
    //     echo json_encode($response);
    // }
    /********************************** Admin Login *****************************************/
    public function login() {
        $response = array('code' => - 1, 'status' => false, 'message1' => '');
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $this->input->post('email');
            $password = $this->input->post('password');
            if (empty($email)) {
                $response['message1'] = 'Email is required.';
                $response['code'] = 201;
            } else if (empty($password)) {
                $response['message1'] = 'Password is required.';
                $response['code'] = 201;
            } else {
                // $encpass = dec_enc('encrypt', $password);
                $data = array("email" => $email, "password" => $password);
                $session_data = $this->Admin_model->login($data, "tbl_login");
                // echo '<pre>'; print_r($session_data); exit;
                if (empty($session_data)) {
                    $response['message1'] = 'Wrong Credentials';
                    $response['code'] = 201;
                } else {
                    $response['message1'] = 'success';
                    $response['status'] = true;
                    $response['code'] = 200;
                    $response['user_data'] = $session_data;
                }
            }
        } else {
            $response['message1'] = 'No direct script is allowed.';
            $response['code'] = 204;
        }
        echo json_encode($response);
    }
    // /********************************** Change Password *****************************************/
    function change_password() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        $validate = validateToken();
        // if($validate){
        // if(!$validate){
        //   $response['message'] = 'Authentication required';
        //   $response['code'] = 203;
        //   echo json_encode($response);
        //   return;
        // }
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            $response['message'] = 'Invalid Request';
            $response['code'] = 204;
            echo json_encode($response);
            return;
        }
        if ($_POST['new_password'] != $_POST['confirm_password']) {
            $response['message'] = 'Password mismatch';
            $response['code'] = 201;
            echo json_encode($response);
            return;
        }
        $isExistEmail = $this->model->isExist('tbl_login', 'email', $_POST['email']);
        if (!$isExistEmail) {
            $response['message'] = 'Email incorrect';
            $response['code'] = 201;
            echo json_encode($response);
            return;
        }
        $isExist = $this->model->getData('tbl_login', ['email' => $_POST['email'], 'password' => dec_enc('encrypt', $_POST['old_password']) ]);
        if (empty($isExist)) {
            $response['message'] = 'Password incorrect';
            $response['code'] = 201;
            echo json_encode($response);
            return;
        }
        $this->model->updateData('tbl_login', ['password' => dec_enc('encrypt', $_POST['new_password']) ], ['email' => $_POST['email']]);
        // $subject = 'Password Updated';
        // $message = '';
        // $message.= '<p>Hello,' . $isExist[0]['username'] . '.</p>';
        // $message.= '<p>We have received your request for forgot password.</p>';
        // $message.= '<p>We have updated your password</p>';
        // $message.= '<p>Thank You</p>';
        // $message.= '<p>Team Softonauts</p>';
        // if ($isExist[0]['usertype'] == 'company') {
        //     sendEmail('info@softonauts.com', $isExist[0]['email'], $subject, $message);
        // } else if ($isExist[0]['usertype'] == 'customer') {
        //     $company_id = $this->model->getValue('customer', 'company_id', ['id' => $isExist[0]['fk_id']]);
        //     $company_email = $this->model->getValue('tbl_login', 'email', ['fk_id' => $company_id]);
        //     sendEmail($company_email, $isExist[0]['email'], $subject, $message);
        // }
        $response['message'] = 'Password Changed';
        $response['code'] = 200;
        $response['status'] = true;
        echo json_encode($response);
    }
    // /********************************** Forgot Password *****************************************/
    // function forgot_password() {
    //     $response = array('code' => - 1, 'status' => false, 'message' => '');
    // $validate = validateToken();
    // if($validate){
    //     if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //         if (empty($_POST['username'])) {
    //             $response['message'] = 'Less Parameters';
    //             $response['code'] = 201;
    //         } else {
    //             $login = $this->model->getData('login', ['email' => $_POST['username']]);
    //             $login2 = $this->model->getData('login', ['phone' => $_POST['username']]);
    //             if (!empty($login)) {
    //                 $login_data = $login[0];
    //             } else if (!empty($login2)) {
    //                 $login_data = $login2[0];
    //             }
    //             if (!empty($login) || !empty($login2)) {
    //                 $otp = get_random_number(6);
    //                 $this->model->updateData('login', ['otp' => $otp], ['id' => $login_data['id']]);
    //                 $subject = 'One Time Password';
    //                 $message = '';
    //                 $message.= '<p><b>' . $otp . '</b> is your <b>one time password(OTP)</b>. Please enter the OTP to proceed.</p><br>';
    //                 $message.= '<p>Thank You</p>';
    //                 $message.= '<p>Team Softonauts</p>';
    //                 if ($login_data['usertype'] == 'company') {
    //                     sendEmail('info@softonauts.com', $login_data['email'], $subject, $message);
    //                 } else if ($login_data['usertype'] == 'customer') {
    //                     $company_id = $this->model->getValue('customer', 'company_id', ['id' => $login_data['fk_id']]);
    //                     $company_email = $this->model->getValue('login', 'email', ['fk_id' => $company_id]);
    //                     sendEmail($company_email, $login_data['email'], $subject, $message);
    //                 }
    //                 $response['message'] = 'success';
    //                 $response['code'] = 200;
    //                 $response['status'] = true;
    //             } else {
    //                 $response['message'] = 'Email/Phone is incorrect';
    //                 $response['code'] = 203;
    //             }
    //         }
    //     } else {
    //         $response['message'] = 'Invalid Request';
    //         $response['code'] = 204;
    //     }
    //     // }
    //     // else{
    //     //   $response['message'] = 'Authentication required';
    //     //   $response['code'] = 203;
    //     // }
    //     echo json_encode($response);
    // }
    // function verify_otp_for_password() {
    //     $response = array('code' => - 1, 'status' => false, 'message' => '');
    // $validate = validateToken();
    // if($validate){
    //     if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //         if (empty($_POST['otp'])) {
    //             $response['message'] = 'Less Parameters';
    //             $response['code'] = 201;
    //         } else {
    //             $otp = $this->model->getData2('login', ['email' => $_POST['username']], ['phone' => $_POST['username']], 'otp,id');
    //             if (!empty($otp)) {
    //                 $id = $otp[0]['id'];
    //                 $otp = $otp[0]['otp'];
    //             }
    //             if (!empty($otp) && $_POST['otp'] == $otp) {
    //                 $password = $this->model->getValue('login', 'password', ['id' => $id]);
    //                 $email = $this->model->getValue('login', 'email', ['id' => $id]);
    //                 $key = $email . ',' . $password;
    //                 $key = dec_enc($key);
    //                 $response['key'] = $key;
    //                 $response['message'] = 'success';
    //                 $response['code'] = 200;
    //                 $response['status'] = true;
    //             } else {
    //                 $response['message'] = 'Otp Incorrect';
    //                 $response['code'] = 203;
    //             }
    //         }
    //     } else {
    //         $response['message'] = 'Invalid Request';
    //         $response['code'] = 204;
    //     }
    //     // }
    //     // else{
    //     //   $response['message'] = 'Authentication required';
    //     //   $response['code'] = 203;
    //     // }
    //     echo json_encode($response);
    // }
    // /********************************** Reset Password *****************************************/
    // function reset_password() {
    //     $response = array('code' => - 1, 'status' => false, 'message' => '');
    // $validate = validateToken();
    // if($validate){
    //     if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //         if ($_POST['new_password'] != $_POST['confirm_password']) {
    //             $response['message'] = 'Password Mismatch';
    //             $response['code'] = 201;
    //         } else {
    //             $isExist = $this->model->getData('login', ['email' => $_POST['email'], 'password' => $_POST['old_password']]);
    //             if (!empty($isExist)) {
    //                 $this->model->updateData('login', ['password' => dec_enc($_POST['new_password']) ], ['email' => $_POST['email']]);
    //                 $response['message'] = 'Password Updated';
    //                 $response['code'] = 200;
    //                 $response['status'] = true;
    //                 $subject = 'Password Updated';
    //                 $message = '';
    //                 $message.= '<p>Hello,' . $isExist[0]['username'] . '</p>';
    //                 $message.= '<p>      We have received your request for forgot password.</p>';
    //                 $message.= '<p>We have updated your password</p>';
    //                 $message.= '<p>Thank You</p>';
    //                 $message.= '<p>Team Softonauts</p>';
    //                 if ($isExist[0]['usertype'] == 'company') {
    //                     sendEmail('info@softonauts.com', $isExist[0]['email'], $subject, $message);
    //                 } else if ($isExist[0]['usertype'] == 'customer') {
    //                     $company_id = $this->model->getValue('customer', 'company_id', ['id' => $isExist[0]['fk_id']]);
    //                     $company_email = $this->model->getValue('login', 'email', ['fk_id' => $company_id]);
    //                     sendEmail($company_email, $isExist[0]['email'], $subject, $message);
    //                 }
    //                 //sendEmail('info@softonauts.com',$isExist[0]['email'],$subject,$message);
    //             } else {
    //                 $response['message'] = 'Incorrect Email/Password';
    //                 $response['code'] = 203;
    //             }
    //         }
    //     } else {
    //         $response['message'] = 'Invalid Request';
    //         $response['code'] = 204;
    //     }
    //     // }
    //     // else{
    //     //   $response['message'] = 'Authentication required';
    //     //   $response['code'] = 203;
    //     // }
    //     echo json_encode($response);
    // }
    public function get_state() {
        $response = array('code' => - 1, 'status' => false, 'message1' => '');
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // echo '<pre>'; print_r($_POST); exit;
            $select = '*';
            if (!empty($_POST['select']) && isset($_POST['select'])) {
                $select = $_POST['select'];
                unset($_POST['select']);
            }
            $states = $this->model->getData('states', [], $select);
            $response['state'] = $states;
            $response['message'] = 'success';
            $response['code'] = 200;
            $response['status'] = true;
        } else {
            $response['message'] = 'Invalid Request';
            $response['code'] = 204;
        }
        echo json_encode($response);
    }
    public function get_cities_by_state() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $select = 'id,name';
            if (!empty($_POST['select']) && isset($_POST['select'])) {
                $select = $_POST['select'];
                unset($_POST['select']);
            }
            $cities = $this->model->getData('cities', ['state_id' => $_POST['state_id']], $select);
            $pro_select_box = '';
            if (count($cities) > 0) {
                $pro_select_box.= '<option value=""></option>';
                if (!empty($cities)) {
                    foreach ($cities as $cities) {
                        $pro_select_box.= '<option value="' . $cities['name'] . '">' . $cities['name'] . '</option>';
                    }
                }
            }
            $response['cities'] = $pro_select_box;
            // $response['cities2'] = $cities;
            $response['message'] = 'success';
            $response['code'] = 200;
            $response['status'] = true;
        } else {
            $response['message'] = 'Invalid Request';
            $response['code'] = 204;
        }
        echo json_encode($response);
    }
    // ************************************** Society Master *******************************************************
    public function add_company() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['name']) && empty($_POST['email']) && empty($_POST['contact'])) {
                    $response['message'] = 'Less Parameters';
                    $response['code'] = 201;
                } else {
                    $isExist = $this->model->isExist('tbl_soc_master', 'email', $_POST['email']);
                    // $isExist2 = $this->model->isExist('company', 'prsn_email', $_POST['prsn_email']);
                    $isExist3 = $this->model->isExist('tbl_login', 'contact_no', $_POST['contact']);
                    $isExist4 = $this->model->isExist('tbl_login', 'email', $_POST['email']);
                    if ($isExist || $isExist4) {
                        $response['message'] = 'Email Exists';
                        $response['code'] = 201;
                    } else if ($isExist3) {
                        $response['message'] = 'Mobile Exists';
                        $response['code'] = 201;
                    } else {
                        $company_id = $this->model->insertData('tbl_soc_master', $_POST);
                        if (!empty($company_id)) {
                            $password = generateRandomString(8);
                            $login = [];
                            $login['fk_id'] = $company_id;
                            $login['name'] = $_POST['soc_name'];
                            $login['contact_no'] = $_POST['contact'];
                            $login['email'] = $_POST['email'];
                            $login['logo'] = $_POST['logo'];
                            $login['password'] = dec_enc('encrypt', $password);
                            $login['user_type'] = 'society';
                            $login['status'] = 1;
                            // $login['created_by'] = $_POST['created_by'];
                            $this->model->insertData('tbl_login', $login);
                            $subject = 'Welcome Message';
                            $message = '';
                            $message.= 'Hello, ' . $login['name'];
                            $message.= '<p>Welcome To E-Society Management</p>';
                            $message.= '<p>Your User Id: <b>' . $login['email'] . '</b></p>';
                            $message.= '<p>Your Password: <b>' . $password . '</b></p>';
                            $message.= '<p>Team Society-Management</p>';
                            sendEmail('donotreply@gmail.com', $login['email'], $subject, $message);
                        }
                        $response['message'] = 'Company Added';
                        $response['code'] = 200;
                        $response['status'] = true;
                    }
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_all_soc_master() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            //  $response['message'] = 'Authentication required';
            //  $response['code'] = 203;
            //      echo json_encode($response);
            //      return;
            // }
            if ($_SERVER["REQUEST_METHOD"] != "POST") {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
                echo json_encode($response);
                return;
            }
            $select = '*';
            if (!empty($_POST['select']) && isset($_POST['select'])) {
                $select = $_POST['select'];
                unset($_POST['select']);
            }
            $order_by = [];
            if (!empty($_POST['order_by']) && isset($_POST['order_by'])) {
                $order_by_arr = explode('=', $_POST['order_by']);
                $order_by[$order_by_arr[0]] = $order_by_arr[1];
                unset($_POST['order_by']);
            }
            $company = $this->model->getData('tbl_soc_master', $_POST, $select, $order_by);
            $response['society'] = $company;
            $response['message'] = 'success';
            $response['code'] = 200;
            $response['status'] = true;
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_company() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $select = '*';
                    if (!empty($_POST['select']) && isset($_POST['select'])) {
                        $select = $_POST['select'];
                        unset($_POST['select']);
                    }
                    $company = $this->model->getData('tbl_soc_master', $_POST, $select);
                    if (empty($company)) {
                        $response['message'] = 'No Data';
                        $response['code'] = 201;
                        echo json_encode($response);
                        return;
                    }
                    foreach ($company as $key => $value) {
                        if (!empty($value['city'])) {
                            $company[$key]['city_name'] = $this->model->getValue('cities', 'name', ['id' => $value['city']]);
                        }
                        if (!empty($value['state'])) {
                            $company[$key]['state_name'] = $this->model->getValue('states', 'name', ['id' => $value['state']]);
                        }
                    }
                    $response['company'] = $company;
                    $response['message'] = 'success';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function update_society() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->updateData('tbl_soc_master', $_POST, ['id' => $_POST['id']]);
                    $login = [];
                    if (!empty($_POST['soc_name'])) {
                        $login['name'] = $_POST['soc_name'];
                    }
                    if (!empty($_POST['logo'])) {
                        $login['logo'] = $_POST['logo'];
                    }
                    $this->model->updateData('tbl_login', $login, ['fk_id' => $_POST['id'], 'user_type' => 'society']);
                    $response['message'] = 'Society Updated';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function delete_society() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->deleteData('tbl_soc_master', ['id' => $_POST['id']]);
                    $company = $this->model->deleteData('tbl_login', ['fk_id' => $_POST['id'], 'user_type' => 'society']);
                    $response['message'] = 'Society Deleted';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }    
    // ***************************************Owner Master*****************************************
    public function add_owner() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['name']) && empty($_POST['email']) && empty($_POST['contact'])) {
                    $response['message'] = 'Less Parameters';
                    $response['code'] = 201;
                } else {
                    $isExist = $this->model->isExist('tbl_owner_master', 'email', $_POST['email']);
                    $isExist3 = $this->model->isExist('tbl_owner_master', 'contact', $_POST['contact']);
                    if ($isExist) {
                        $response['message'] = 'Email Exists';
                        $response['code'] = 201;
                    } else if ($isExist3) {
                        $response['message'] = 'Mobile Exists';
                        $response['code'] = 201;
                    } else {
                        $company_id = $this->model->insertData('tbl_owner_master', $_POST);
                        $response['message'] = 'Owner Added';
                        $response['code'] = 200;
                        $response['status'] = true;
                    }
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_all_owner() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            //
            // }
            if ($_SERVER["REQUEST_METHOD"] != "POST") {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
                echo json_encode($response);
                return;
            }
            $select = '*';
            if (!empty($_POST['select']) && isset($_POST['select'])) {
                $select = $_POST['select'];
                unset($_POST['select']);
            }
            $order_by = [];
            if (!empty($_POST['order_by']) && isset($_POST['order_by'])) {
                $order_by_arr = explode('=', $_POST['order_by']);
                $order_by[$order_by_arr[0]] = $order_by_arr[1];
                unset($_POST['order_by']);
            }
            $owners = $this->model->getData('tbl_owner_master', $_POST, $select, $order_by);
            // echo '<pre>'; print_r($secretary); exit;
            $response['owners'] = $owners;
            $response['message'] = 'success';
            $response['code'] = 200;
            $response['status'] = true;
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_owner() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $select = '*';
                    if (!empty($_POST['select']) && isset($_POST['select'])) {
                        $select = $_POST['select'];
                        unset($_POST['select']);
                    }
                    $owner = $this->model->getData('tbl_owner_master', $_POST, $select);
                    if (empty($owner)) {
                        $response['message'] = 'No Data';
                        $response['code'] = 201;
                        echo json_encode($response);
                        return;
                    }
                    foreach ($owner as $key => $value) {
                        if (!empty($value['city'])) {
                            $owner[$key]['city_name'] = $this->model->getValue('cities', 'name', ['id' => $value['city']]);
                        }
                        if (!empty($value['state'])) {
                            $owner[$key]['state_name'] = $this->model->getValue('states', 'name', ['id' => $value['state']]);
                        }
                    }
                    $response['owner'] = $owner;
                    $response['message'] = 'success';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function update_owner() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->updateData('tbl_owner_master', $_POST, ['id' => $_POST['id']]);
                    $response['message'] = 'Owner Updated';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function delete_owner() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->deleteData('tbl_owner_master', ['id' => $_POST['id']]);
                    $response['message'] = 'Owner Deleted';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    // ***************************************Rental Master***********************************************************
    public function add_rental() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['name']) && empty($_POST['email']) && empty($_POST['contact'])) {
                    $response['message'] = 'Less Parameters';
                    $response['code'] = 201;
                } else {
                    $isExist = $this->model->isExist('tbl_rental_master', 'email', $_POST['email']);
                    $isExist3 = $this->model->isExist('tbl_rental_master', 'contact', $_POST['contact']);
                    if ($isExist) {
                        $response['message'] = 'Email Exists';
                        $response['code'] = 201;
                    } else if ($isExist3) {
                        $response['message'] = 'Mobile Exists';
                        $response['code'] = 201;
                    } else {
                        $curl_data =array(
                           'society_id' =>$_POST['society_id'],
                           'name' =>$_POST['name'],
                           'contact' =>$_POST['contact'],
                           'email' =>$_POST['email'],
                           'address' =>$_POST['address'],
                           'state' =>$_POST['state'],
                           'city' =>$_POST['city'],
                           'pincode' =>$_POST['pincode'],
                           'dob' =>$_POST['dob'],
                           'pan_no' =>$_POST['pan_no'],
                           'aadhar_no' =>$_POST['aadhar_no'],
                           'alternate_no' =>$_POST['alternate_no'],
                           'photo' =>$_POST['photo'],
                           'documents' =>$_POST['documents'],
                           'flat_no' =>$_POST['flat_no'],
                           'floor' =>$_POST['floor'],
                           'flat_area' =>$_POST['flat_area'],
                           'flat_photo' =>$_POST['flat_photo'],
                           'flat_doc' =>$_POST['flat_doc'],
                           'date' => date('d/m/Y'),
                        );
                        $fk_rental_id = $this->model->insertData('tbl_rental_master', $curl_data);
                        if (!empty($fk_rental_id)) {
                            $aggrement_data = [];
                            $aggrement_data['fk_rental_id'] = $fk_rental_id;
                            $aggrement_data['period_of_taken_year'] = $_POST['period_of_taken_year'];
                            $aggrement_data['aggrement_from_date'] = $_POST['aggrement_from_date'];
                            $aggrement_data['aggrement_to_date'] = $_POST['aggrement_to_date'];
                            $aggrement_data['monthly_rent'] = $_POST['monthly_rent'];
                            $aggrement_data['aggrement_doc'] = $_POST['aggrement_doc'];

                            $this->model->insertData('tbl_rental_aggrement', $aggrement_data);

                        }
                        $response['message'] = 'Rental Added';
                        $response['code'] = 200;
                        $response['status'] = true;
                    }
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_all_rental() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] != "POST") {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
                echo json_encode($response);
                return;
            }
            $select = '*';
            if (!empty($_POST['select']) && isset($_POST['select'])) {
                $select = $_POST['select'];
                unset($_POST['select']);
            }
            $order_by = [];
            if (!empty($_POST['order_by']) && isset($_POST['order_by'])) {
                $order_by_arr = explode('=', $_POST['order_by']);
                $order_by[$order_by_arr[0]] = $order_by_arr[1];
                unset($_POST['order_by']);
            }
            $rentals = $this->model->getData('tbl_rental_master', $_POST, $select, $order_by);
            // echo '<pre>'; print_r($secretary); exit;
            $response['rentals'] = $rentals;
            $response['message'] = 'success';
            $response['code'] = 200;
            $response['status'] = true;
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_rental() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $select = '*';
                    if (!empty($_POST['select']) && isset($_POST['select'])) {
                        $select = $_POST['select'];
                        unset($_POST['select']);
                    }
                    $rental = $this->model->get_rental($_POST['id']);
                    if (empty($rental)) {
                        $response['message'] = 'No Data';
                        $response['code'] = 201;
                        echo json_encode($response);
                        return;
                    }
                    foreach ($rental as $key => $value) {
                        if (!empty($value['city'])) {
                            $rental[$key]['city_name'] = $this->model->getValue('cities', 'name', ['id' => $value['city']]);
                        }
                        if (!empty($value['state'])) {
                            $rental[$key]['state_name'] = $this->model->getValue('states', 'name', ['id' => $value['state']]);
                        }
                    }
                    $response['rental'] = $rental;
                    $response['message'] = 'success';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function update_rental() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                     $curl_data =array(
                           // 'society_id' =>$_POST['society_id'],
                           'name' =>$_POST['name'],
                           'contact' =>$_POST['contact'],
                           'email' =>$_POST['email'],
                           'address' =>$_POST['address'],
                           'state' =>$_POST['state'],
                           'city' =>$_POST['city'],
                           'pincode' =>$_POST['pincode'],
                           'dob' =>$_POST['dob'],
                           'pan_no' =>$_POST['pan_no'],
                           'aadhar_no' =>$_POST['aadhar_no'],
                           'alternate_no' =>$_POST['alternate_no'],
                           'photo' =>$_POST['photo'],
                           'documents' =>$_POST['documents'],
                           'flat_no' =>$_POST['flat_no'],
                           'floor' =>$_POST['floor'],
                           'flat_area' =>$_POST['flat_area'],
                           'flat_photo' =>$_POST['flat_photo'],
                           'flat_doc' =>$_POST['flat_doc'],
                           // 'date' => date('d/m/Y'),
                        );
                    $this->model->updateData('tbl_rental_master', $curl_data, ['id' => $_POST['id']]);

                    $aggrement_data = [];
                    $aggrement_data['fk_rental_id'] = $_POST['id'];
                    $aggrement_data['period_of_taken_year'] = $_POST['period_of_taken_year'];
                    $aggrement_data['aggrement_from_date'] = $_POST['aggrement_from_date'];
                    $aggrement_data['aggrement_to_date'] = $_POST['aggrement_to_date'];
                    $aggrement_data['monthly_rent'] = $_POST['monthly_rent'];
                    $aggrement_data['aggrement_doc'] = $_POST['aggrement_doc'];

                    $this->model->insertData('tbl_rental_aggrement', $aggrement_data);

                
                    $response['message'] = 'Rental Updated';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function delete_rental() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->deleteData('tbl_rental_master', ['id' => $_POST['id']]);
                    $response['message'] = 'Owner Deleted';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    // ***************************************Vehicle Master***********************************************************
    public function add_vehicle() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['name']) && empty($_POST['email']) && empty($_POST['contact'])) {
                    $response['message'] = 'Less Parameters';
                    $response['code'] = 201;
                } else {
                    $isExist = $this->model->isExist('tbl_vehicle_master', 'email', $_POST['email']);
                    $isExist3 = $this->model->isExist('tbl_vehicle_master', 'contact', $_POST['contact']);
                    if ($isExist) {
                        $response['message'] = 'Email Exists';
                        $response['code'] = 201;
                    } else if ($isExist3) {
                        $response['message'] = 'Mobile Exists';
                        $response['code'] = 201;
                    } else {
                        $company_id = $this->model->insertData('tbl_vehicle_master', $_POST);
                        $response['message'] = 'Vehicle Added';
                        $response['code'] = 200;
                        $response['status'] = true;
                    }
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_all_vehicle() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            //  $response['message'] = 'Authentication required';
            //  $response['code'] = 203;
            //      echo json_encode($response);
            //      return;
            // }
            if ($_SERVER["REQUEST_METHOD"] != "POST") {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
                echo json_encode($response);
                return;
            }

            $society_id = $this->input->post('society_id');
            
            $vehicles = $this->model->get_all_vehicle($society_id);
            $response['vehicles'] = $vehicles;
            $response['message'] = 'success';
            $response['code'] = 200;
            $response['status'] = true;
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_data_on_type()
    {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $society_id = $this->input->post('society_id');
                $type = $this->input->post('type');
                if (empty($society_id)) {
                   $response['message'] = "Society Id Is required";
                   $response['code'] =201;
                } else if (empty($type)) {
                    $response['message']="Type is required";
                    $response['code']=201;
                }else{
                    if($type=="Owner"){
                        $data = $this->model->get_customer_data('tbl_owner_master', array('society_id'=>$society_id,'type'=>$type));
                    }elseif ($type=="Rental") {
                       $data = $this->model->get_customer_data('tbl_rental_master', array('society_id'=>$society_id,'type'=>$type));
                    }
                }
                $response['status']=true;
                $response['code']=200;
                $response['customer_data'] = $data;
                
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }

    public function get_data_on_customer_id()
    {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $society_id = $this->input->post('society_id');
                $type = $this->input->post('type');
                $id = $this->input->post('id');
                if (empty($society_id)) {
                   $response['message'] = "Society Id Is required";
                   $response['code'] =201;
                } else if (empty($type)) {
                    $response['message']="Type is required";
                    $response['code']=201;
                }else if (empty($id)) {
                    $response['message']="Id is required";
                    $response['code']=201;
                }else{
                    if($type=="Owner"){
                        $data = $this->model->get_customer_data_on_id('tbl_owner_master', array('society_id'=>$society_id,'type'=>$type,'id'=>$id));
                    }elseif ($type=="Rental") {
                       $data = $this->model->get_customer_data_on_id('tbl_rental_master', array('society_id'=>$society_id,'type'=>$type,'id'=>$id));
                    }
                }
                $response['status']=true;
                $response['code']=200;
                $response['customer_data'] = $data;
                
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_vehicle() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $select = '*';
                    if (!empty($_POST['select']) && isset($_POST['select'])) {
                        $select = $_POST['select'];
                        unset($_POST['select']);
                    }
                    $vehicle = $this->model->getData('tbl_vehicle_master', $_POST, $select);
                    // echo '<pre>'; print_r($vehicle); exit;
                    if ($vehicle[0]['type']=="Owner") {
                        $vehicle[0]['owner_names'] = $this->model->getValue('tbl_owner_master', 'name', ['id' => $vehicle[0]['owner_name']]);
                         $data = $this->model->get_customer_data('tbl_owner_master', array('society_id'=>$vehicle[0]['society_id'],'type'=>$vehicle[0]['type']));
                    } else {
                          $vehicle[0]['owner_names'] = $this->model->getValue('tbl_rental_master', 'name', ['id' => $vehicle[0]['owner_name']]);
                          $data = $this->model->get_customer_data('tbl_rental_master', array('society_id'=>$vehicle[0]['society_id'],'type'=>$vehicle[0]['type']));
                    }
                     
                    if (empty($vehicle)) {
                        $response['message'] = 'No Data';
                        $response['code'] = 201;
                        echo json_encode($response);
                        return;
                    }
                    $response['vehicle'] = $vehicle;
                    $response['customer_data'] = $data;
                   
                    $response['message'] = 'success';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function update_vehicle() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->updateData('tbl_vehicle_master', $_POST, ['id' => $_POST['id']]);
                    $response['message'] = 'Vehicle Updated';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function delete_vehicle() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->deleteData('tbl_vehicle_master', ['id' => $_POST['id']]);
                    $response['message'] = 'Vehicle Deleted';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    // ***************************************Vehicle Master***********************************************************
    public function add_vendor() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['name']) && empty($_POST['email']) && empty($_POST['contact'])) {
                    $response['message'] = 'Less Parameters';
                    $response['code'] = 201;
                } else {
                    $isExist = $this->model->isExist('tbl_vendor_master', 'email', $_POST['email']);
                    $isExist3 = $this->model->isExist('tbl_vendor_master', 'contact', $_POST['contact']);
                    if ($isExist) {
                        $response['message'] = 'Email Exists';
                        $response['code'] = 201;
                    } else if ($isExist3) {
                        $response['message'] = 'Mobile Exists';
                        $response['code'] = 201;
                    } else {
                        $company_id = $this->model->insertData('tbl_vendor_master', $_POST);
                        $response['message'] = 'Vendor Added';
                        $response['code'] = 200;
                        $response['status'] = true;
                    }
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_all_vendor() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] != "POST") {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
                echo json_encode($response);
                return;
            }
            $select = '*';
            if (!empty($_POST['select']) && isset($_POST['select'])) {
                $select = $_POST['select'];
                unset($_POST['select']);
            }
            $order_by = [];
            if (!empty($_POST['order_by']) && isset($_POST['order_by'])) {
                $order_by_arr = explode('=', $_POST['order_by']);
                $order_by[$order_by_arr[0]] = $order_by_arr[1];
                unset($_POST['order_by']);
            }
            $vendors = $this->model->getData('tbl_vendor_master', $_POST, $select, $order_by);
            // echo '<pre>'; print_r($secretary); exit;
            $response['vendors'] = $vendors;
            $response['message'] = 'success';
            $response['code'] = 200;
            $response['status'] = true;
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_vendor() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $select = '*';
                    if (!empty($_POST['select']) && isset($_POST['select'])) {
                        $select = $_POST['select'];
                        unset($_POST['select']);
                    }
                    $vendor = $this->model->getData('tbl_vendor_master', $_POST, $select);
                    if (empty($vendor)) {
                        $response['message'] = 'No Data';
                        $response['code'] = 201;
                        echo json_encode($response);
                        return;
                    }
                    $response['vendor'] = $vendor;
                    $response['message'] = 'success';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function update_vendor() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->updateData('tbl_vendor_master', $_POST, ['id' => $_POST['id']]);
                    $response['message'] = 'Vendor Updated';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function delete_vendor() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->deleteData('tbl_vendor_master', ['id' => $_POST['id']]);
                    $response['message'] = 'Vendor Deleted';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    // ******************************************** Security Master ****************************************************
    public function add_security() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['name']) && empty($_POST['email']) && empty($_POST['contact'])) {
                    $response['message'] = 'Less Parameters';
                    $response['code'] = 201;
                } else {
                    $isExist = $this->model->isExist('tbl_security_master', 'email', $_POST['email']);
                    $isExist3 = $this->model->isExist('tbl_security_master', 'contact_no', $_POST['contact_no']);
                    if ($isExist) {
                        $response['message'] = 'Email Exists';
                        $response['code'] = 201;
                    } else if ($isExist3) {
                        $response['message'] = 'Mobile Exists';
                        $response['code'] = 201;
                    } else {
                        $company_id = $this->model->insertData('tbl_security_master', $_POST);
                        $response['message'] = 'Security Added';
                        $response['code'] = 200;
                        $response['status'] = true;
                    }
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_all_security() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] != "POST") {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
                echo json_encode($response);
                return;
            }
            $select = '*';
            if (!empty($_POST['select']) && isset($_POST['select'])) {
                $select = $_POST['select'];
                unset($_POST['select']);
            }
            $order_by = [];
            if (!empty($_POST['order_by']) && isset($_POST['order_by'])) {
                $order_by_arr = explode('=', $_POST['order_by']);
                $order_by[$order_by_arr[0]] = $order_by_arr[1];
                unset($_POST['order_by']);
            }
            $securitys = $this->model->getData('tbl_security_master', $_POST, $select, $order_by);
            $response['securitys'] = $securitys;
            $response['message'] = 'success';
            $response['code'] = 200;
            $response['status'] = true;
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_security() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $select = '*';
                    if (!empty($_POST['select']) && isset($_POST['select'])) {
                        $select = $_POST['select'];
                        unset($_POST['select']);
                    }
                    $security = $this->model->getData('tbl_security_master', $_POST, $select);
                    if (empty($security)) {
                        $response['message'] = 'No Data';
                        $response['code'] = 201;
                        echo json_encode($response);
                        return;
                    }
                    foreach ($security as $key => $value) {
                        if (!empty($value['city'])) {
                            $security[$key]['city_name'] = $this->model->getValue('cities', 'name', ['id' => $value['city']]);
                        }
                        if (!empty($value['state'])) {
                            $security[$key]['state_name'] = $this->model->getValue('states', 'name', ['id' => $value['state']]);
                        }
                    }
                    $response['security'] = $security;
                    $response['message'] = 'success';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function update_security() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->updateData('tbl_security_master', $_POST, ['id' => $_POST['id']]);
                    $response['message'] = 'Security Updated';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function delete_security() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->deleteData('tbl_security_master', ['id' => $_POST['id']]);
                    $response['message'] = 'Security Deleted';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    // ***************************************Vehicle Master***********************************************************
    public function add_emergency() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['ambulance']) && empty($_POST['fire']) && empty($_POST['police']) && empty($_POST['plumber']) && empty($_POST['carpainter']) && empty($_POST['key_maker'])) {
                    $response['message'] = 'Less Parameters';
                    $response['code'] = 201;
                } else {
                    $company_id = $this->model->insertData('tbl_emergency_contacts_master', $_POST);
                    $response['message'] = 'Emergency Contact Added';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_all_emergency() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            //  $response['message'] = 'Authentication required';
            //  $response['code'] = 203;
            //      echo json_encode($response);
            //      return;
            // }
            if ($_SERVER["REQUEST_METHOD"] != "POST") {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
                echo json_encode($response);
                return;
            }
            $select = '*';
            if (!empty($_POST['select']) && isset($_POST['select'])) {
                $select = $_POST['select'];
                unset($_POST['select']);
            }
            $order_by = [];
            if (!empty($_POST['order_by']) && isset($_POST['order_by'])) {
                $order_by_arr = explode('=', $_POST['order_by']);
                $order_by[$order_by_arr[0]] = $order_by_arr[1];
                unset($_POST['order_by']);
            }
            $emergencys = $this->model->getData('tbl_emergency_contacts_master', $_POST, $select, $order_by);
            // echo '<pre>'; print_r($secretary); exit;
            $response['emergencys'] = $emergencys;
            $response['message'] = 'success';
            $response['code'] = 200;
            $response['status'] = true;
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_emergency() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $select = '*';
                    if (!empty($_POST['select']) && isset($_POST['select'])) {
                        $select = $_POST['select'];
                        unset($_POST['select']);
                    }
                    $emergency = $this->model->getData('tbl_emergency_contacts_master', $_POST, $select);
                    if (empty($emergency)) {
                        $response['message'] = 'No Data';
                        $response['code'] = 201;
                        echo json_encode($response);
                        return;
                    }
                    $response['emergency'] = $emergency;
                    $response['message'] = 'success';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function update_emergency() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->updateData('tbl_emergency_contacts_master', $_POST, ['id' => $_POST['id']]);
                    $response['message'] = 'Emergency Updated';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function delete_emergency() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->deleteData('tbl_emergency_contacts_master', ['id' => $_POST['id']]);
                    $response['message'] = 'Emergency Deleted';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    function get_designations() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $select = '*';
                if (!empty($_POST['select']) && isset($_POST['select'])) {
                    $select = $_POST['select'];
                    unset($_POST['select']);
                }
                $designations = $this->model->getData('tbl_designation_master', $_POST, $select);
                $response['designations'] = $designations;
                $response['message'] = 'success';
                $response['code'] = 200;
                $response['status'] = true;
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function add_designations() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        $_POST['designations'] = json_decode($_POST['designations'], true);
        $_POST['designation_ids'] = json_decode($_POST['designation_ids'], true);
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['society_id']) || empty($_POST['designations'])) {
                    $response['message'] = 'Less Parameters';
                    $response['code'] = 201;
                } else {
                    $designations = $this->model->getData('tbl_designation_master', [], 'id');
                    if (!empty($designations)) {
                        $db_ids = array_column($designations, 'id');
                        if (!empty($db_ids)) {
                            foreach ($db_ids as $key => $id) {
                                if (!in_array($id, $_POST['designation_ids'])) {
                                    $this->model->deleteData2('tbl_designation_master', ['id' => $id]);
                                }
                            }
                        }
                    }
                    if (!empty($_POST['designations'])) {
                        foreach ($_POST['designations'] as $key => $designation) {
                            if (isset($_POST['designation_ids'][$key]) && !empty($_POST['designation_ids'][$key])) {
                                if (empty($designation)) {
                                    $this->model->deleteData2('tbl_designation_master', ['id' => $_POST['designation_ids'][$key]]);
                                }
                                $this->model->updateData('tbl_designation_master', ['designation' => $designation], ['id' => $_POST['designation_ids'][$key]]);
                            } else {
                                $isExist = $this->model->getValue('tbl_designation_master', 'designation', ['society_id' => $_POST['society_id'], 'designation' => $designation]);
                                if (empty($isExist)) {
                                    if (!empty($designation)) {
                                        $this->model->insertData('tbl_designation_master', ['society_id' => $_POST['society_id'], 'designation' => $designation]);
                                    }
                                } else {
                                    $response['message'] = 'Already Exist';
                                    $response['code'] = 201;
                                }
                            }
                        }
                    }
                    $response['message'] = 'success';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function add_setting() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['flat_type']) && empty($_POST['flat_area']) && empty($_POST['maintenance']) && empty($_POST['intrest_in']) && empty($_POST['addon_intrest']) && empty($_POST['billing_cycle']) && empty($_POST['from_date']) && empty($_POST['to_date']) && empty($_POST['extension_or_grade_period'])) {
                    $response['message'] = 'Less Parameters';
                    $response['code'] = 201;
                } else {
                    $company_id = $this->model->insertData('tbl_setting_page', $_POST);
                    $response['message'] = 'Setting Added';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_all_setting_page() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        $validate = validateToken();
        if (!$validate) {
            $response['message'] = 'Authentication required';
            $response['code'] = 203;
            echo json_encode($response);
            return;
        }
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            $response['message'] = 'Invalid Request';
            $response['code'] = 204;
            echo json_encode($response);
            return;
        }
        $select = '*';
        if (!empty($_POST['select']) && isset($_POST['select'])) {
            $select = $_POST['select'];
            unset($_POST['select']);
        }
        $order_by = [];
        if (!empty($_POST['order_by']) && isset($_POST['order_by'])) {
            $order_by_arr = explode('=', $_POST['order_by']);
            $order_by[$order_by_arr[0]] = $order_by_arr[1];
            unset($_POST['order_by']);
        }
        $setting_page = $this->model->getData('tbl_setting_page', $_POST, $select, $order_by);
        $response['setting_page'] = $setting_page;
        $response['message'] = 'success';
        $response['code'] = 200;
        $response['status'] = true;
        echo json_encode($response);
    }
    public function get_setting_page() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $select = '*';
                    if (!empty($_POST['select']) && isset($_POST['select'])) {
                        $select = $_POST['select'];
                        unset($_POST['select']);
                    }
                    $setting_page = $this->model->getData('tbl_setting_page', $_POST, $select);
                    if (empty($setting_page)) {
                        $response['message'] = 'No Data';
                        $response['code'] = 201;
                        echo json_encode($response);
                        return;
                    }
                    $response['setting_page'] = $setting_page;
                    $response['message'] = 'success';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function update_setting_page() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->updateData('tbl_setting_page', $_POST, ['id' => $_POST['id']]);
                    $response['message'] = 'Security Updated';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function delete_setting_page() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->deleteData('tbl_setting_page', ['id' => $_POST['id']]);
                    $response['message'] = 'Security Deleted';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    function get_transaction_type() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $select = '*';
                if (!empty($_POST['select']) && isset($_POST['select'])) {
                    $select = $_POST['select'];
                    unset($_POST['select']);
                }
                $transaction_type = $this->model->getData('tbl_transaction_type', $_POST, $select);
                $response['transaction_type'] = $transaction_type;
                $response['message'] = 'success';
                $response['code'] = 200;
                $response['status'] = true;
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function add_invoice_series() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['invoice_no'])) {
                    $response['message'] = 'Less Parameters';
                    $response['code'] = 201;
                } else {
                    $isExist = $this->model->isExist('tbl_invoice', 'soc_id', $_POST['soc_id']);
                    if ($isExist) {
                        $response['message'] = 'Invoice No Already Exist';
                        $response['code'] = 201;
                    } else {
                        $_POST['extension_no'] = 1;
                        $company_id = $this->model->insertData('tbl_invoice', $_POST);
                        $response['message'] = 'Invoice Series Added';
                        $response['code'] = 200;
                        $response['status'] = true;
                    }
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    function get_invoice_series() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $select = '*';
                if (!empty($_POST['select']) && isset($_POST['select'])) {
                    $select = $_POST['select'];
                    unset($_POST['select']);
                }
                $invoice_series = $this->model->getData('tbl_invoice', $_POST, $select);
                $response['invoice_series'] = $invoice_series;
                $response['message'] = 'success';
                $response['code'] = 200;
                $response['status'] = true;
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_invoice_series_on_id() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $select = '*';
                    if (!empty($_POST['select']) && isset($_POST['select'])) {
                        $select = $_POST['select'];
                        unset($_POST['select']);
                    }
                    $invoice_series = $this->model->getData('tbl_invoice', $_POST, $select);
                    if (empty($invoice_series)) {
                        $response['message'] = 'No Data';
                        $response['code'] = 201;
                        echo json_encode($response);
                        return;
                    }
                    $response['invoice_series'] = $invoice_series;
                    $response['message'] = 'success';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function update_invoice_series() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->updateData('tbl_invoice', $_POST, ['id' => $_POST['id']]);
                    $response['message'] = 'Invoice Updated';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function delete_invoice_series() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->deleteData('tbl_invoice', ['id' => $_POST['id']]);
                    $response['message'] = 'Invoice Series Deleted';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_invoice_no($soc_id='') {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
             if ($_SERVER["REQUEST_METHOD"] == "POST") {
            //     if (empty($_POST['soc_id'])) {
            //         $response['message'] = 'Id id required';
            //         $response['code'] = 201;
            //     } else {
                    $select = '*';
                    if (!empty($_POST['select']) && isset($_POST['select'])) {
                        $select = $_POST['select'];
                        unset($_POST['select']);
                    }
                    $invoice_no = $this->model->getData('tbl_invoice', ['soc_id'=>$soc_id], $select);
                    if (empty($invoice_no)) {
                        $response['message'] = 'No Data';
                        $response['code'] = 201;
                        echo json_encode($response);
                        return;
                    } else {
                        $invoice_structure = $invoice_no[0]['invoice_no'];
                        $extension_no = $invoice_no[0]['extension_no'];
                        $invoice_structure_last = rtrim($invoice_structure, $extension_no);
                        $extension_no = $extension_no + 1;
                        $final_invoice_no = $invoice_structure_last . $extension_no;
                        $this->db->update('tbl_invoice', array('extension_no' => $extension_no, 'status' => 2, 'invoice_no' => $final_invoice_no), array('invoice_no' => $invoice_structure));
                        
                    }
                    $response['final_invoice_no'] = $final_invoice_no;
                    $response['message'] = 'success';
                    $response['code'] = 200;
                    $response['status'] = true;
                // }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_invoice_data()
    {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['soc_id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    // $company = $this->model->deleteData('tbl_invoice', ['id' => $_POST['id']]);
                    $invoice_no = $this->model->getData('tbl_invoice', $_POST);
                    $owner_name = $this->model->getData('tbl_owner_master', ['society_id'=>$_POST['soc_id']]);
                    $invoice_data = $this->model->get_invoice_data($_POST);

                    // tbl_invoice_data
                    $response['invoice_no'] =$invoice_no;
                    $response['owner_name'] =$owner_name;
                    $response['invoice_data'] =$invoice_data;
                    // $response['message'] = 'Invoice Series Deleted';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function add_invoice_data() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['fk_owner_id'])) {
                    $response['message'] = 'Less Parameters';
                    $response['code'] = 201;
                } else {
                        $company_id = $this->model->insertData('tbl_invoice_data', $_POST);
                        $response = $this->get_invoice_no($_POST['fk_society_id']);
                        $response['message'] = 'Invoice Added';
                        $response['code'] = 200;
                        $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function get_invoice_on_id()
    {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $select = '*';
                    if (!empty($_POST['select']) && isset($_POST['select'])) {
                        $select = $_POST['select'];
                        unset($_POST['select']);
                    }
                    $invoice = $this->model->getData('tbl_invoice_data', $_POST, $select);
                    if (empty($invoice)) {
                        $response['message'] = 'No Data';
                        $response['code'] = 201;
                        echo json_encode($response);
                        return;
                    }
                    $response['invoice'] = $invoice;
                    $response['message'] = 'success';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
    public function update_invoice_data() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->updateData('tbl_invoice_data', $_POST, ['id' => $_POST['id']]);
                    $response['message'] = 'Invoice Updated';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }

    public function delete_invoice() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->deleteData('tbl_invoice_data', ['id' => $_POST['id']]);
                    $response['message'] = 'Invoice Deleted';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }

    public function get_invoice_pdf()
    {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $id = $this->input->post('id');
                if (empty($id)) {
                    $response['message'] = 'Id is required';
                    $response['code'] = 201;
                } else {
                    $invoice_data = $this->model->get_invoice_data_pdf($id);
                    $response['status']="true";
                    $response['code']=200;
                    $response['data']=$invoice_data;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
        
    }

    public function total_society_count()
    {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $society_id = $this->input->post('society_id');
                if (empty($society_id)) {
                    $response['message'] = 'society_id is required';
                    $response['code'] = 201;
                } else {
                    $total_designation = $this->model->CountWhereRecord('tbl_designation_master',array('society_id'=>$society_id,'status!='=>'deleted'));
                    $total_owner = $this->model->CountWhereRecord('tbl_owner_master',array('society_id'=>$society_id,'status!='=>'deleted'));
                    $total_rental = $this->model->CountWhereRecord('tbl_rental_master',array('society_id'=>$society_id,'status!='=>'deleted'));
                    $total_vechile = $this->model->CountWhereRecord('tbl_vehicle_master',array('society_id'=>$society_id,'status!='=>'deleted'));
                    $total_vendor = $this->model->CountWhereRecord('tbl_vendor_master',array('society_id'=>$society_id,'status!='=>'deleted'));
                    $total_security = $this->model->CountWhereRecord('tbl_security_master',array('society_id'=>$society_id,'status!='=>'deleted'));
                    $response['status']="true";
                    $response['code']=200;
                    $response['total_designation']=$total_designation;
                    $response['total_owner']=$total_owner;
                    $response['total_rental']=$total_rental;
                    $response['total_vechile']=$total_vechile;
                    $response['total_vendor']=$total_vendor;
                    $response['total_security']=$total_security;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
      public function total_admin_count()
    {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
              
                    $total_society = $this->model->CountWhereRecord('tbl_soc_master',array('status!='=>'deleted'));
                    
                    $response['status']="true";
                    $response['code']=200;
                    $response['total_society']=$total_society;
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }

    public function add_expenses_data() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['vendor_name'])) {
                    $response['message'] = 'Less Parameters';
                    $response['code'] = 201;
                } else {
                        $company_id = $this->model->insertData('tbl_expenses', $_POST);
                        $response['message'] = 'Expenses Added';
                        $response['code'] = 200;
                        $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }

    public function get_expenses_data()
    {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['fk_society_id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $expenses_data = $this->model->getData('tbl_expenses', $_POST);
                  
                    $response['expenses_data'] =$expenses_data;
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }

    public function get_expenses_on_id()
    {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $select = '*';
                    if (!empty($_POST['select']) && isset($_POST['select'])) {
                        $select = $_POST['select'];
                        unset($_POST['select']);
                    }
                    $expenses = $this->model->getData('tbl_expenses', $_POST, $select);
                    if (empty($expenses)) {
                        $response['message'] = 'No Data';
                        $response['code'] = 201;
                        echo json_encode($response);
                        return;
                    }
                    $response['expenses'] = $expenses;
                    $response['message'] = 'success';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }

    public function update_expenses_data()
    {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->updateData('tbl_expenses', $_POST, ['id' => $_POST['id']]);
                    $response['message'] = 'Invoice Updated';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }

    public function delete_expenses() {
        $response = array('code' => - 1, 'status' => false, 'message' => '');
        // $validate = validateToken();
        // if ($validate) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST['id'])) {
                    $response['message'] = 'Wrong Parameters';
                    $response['code'] = 201;
                } else {
                    $company = $this->model->deleteData('tbl_expenses', ['id' => $_POST['id']]);
                    $response['message'] = 'Expenses Deleted';
                    $response['code'] = 200;
                    $response['status'] = true;
                }
            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
        // } else {
        //     $response['message'] = 'Authentication required';
        //     $response['code'] = 203;
        // }
        echo json_encode($response);
    }
}
