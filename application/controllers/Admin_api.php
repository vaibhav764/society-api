<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_api extends CI_Controller {

		function get_token(){
			$token = $this->model->getValue('ci_sessions','token',$_POST);
			if(!empty($token)){
				$token = decodeToken($token);
		        $token['timestamp'] = now();
		        $token = generateToken($token);
		        $this->model->updateData('ci_sessions',['token'=>$token],['session_id'=>$_POST['session_id']]);
			}
			return $token;
		}
	/********************************** Admin Login *****************************************/
		function sign_in(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}

			if (empty($_POST['email'])){
				$response['message'] = 'Email Required';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			if (empty($_POST['password'])){
				$response['message'] = 'Password Required';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			$isExistEmail = $this->model->isExist('login','email',$_POST['email']);
			if(!$isExistEmail){
				$response['message'] = 'Incorrect Email';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			$select = '*';
			if(!empty($_POST['select']) && isset($_POST['select'])){
				$select = $_POST['select'];
				unset($_POST['select']);
			}

			$admin = $this->model->getData('login',['email'=>$_POST['email'],'password'=>encyrpt_password($_POST['password'])]);
			if(empty($admin)){
				$response['message'] = 'Incorrect Password';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}

			$_POST['timestamp'] = now();
			$token = generateToken($_POST);

			$agent = get_agent();
			$session_id = encyrpt_password($admin[0]['id'].'-'.$agent);

            $sessions = array('session_id' => $session_id, 'token' => $token,'logged_in'=>true,'created_by'=>$admin[0]['id'],'agent'=>$agent);
			$isExist = $this->model->isExist('ci_sessions','session_id',$session_id);
			if($isExist){
				$this->model->updateData('ci_sessions',$sessions,['session_id'=>$session_id]);
			}
			else{
				$this->model->insertData('ci_sessions',$sessions);
			}
			
			$response['token'] = $token;
			$response['data'] = $admin[0];
			$response['session_id'] = $session_id;
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			// echo"<pre>";
			// print_r($response);die;
			echo json_encode($response);
		}
	/********************************** Change Password *****************************************/

		function change_password(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			if ($_POST['new_password'] != $_POST['confirm_password']){
				$response['message'] = 'Password mismatch';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			$isExistEmail = $this->model->isExist('login','email',$_POST['email']);
			if(!$isExistEmail){
				$response['message'] = 'Email incorrect';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			$isExist = $this->model->getData('login',['email'=>$_POST['email'],'password'=>encyrpt_password($_POST['old_password'])] );
			if(empty($isExist)){
				$response['message'] = 'Password incorrect';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			$this->model->updateData('login',['password'=>encyrpt_password($_POST['new_password'])],['email'=>$_POST['email']]);
			$response['message'] = 'Password Changed';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		} 

	/********************************** Forgot Password *****************************************/
		function forgot_password(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['username'])){
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$login = $this->model->getData('login',['email'=>$_POST['username']]);
						$login2 = $this->model->getData('login',['phone'=>$_POST['username']]);
						if(!empty($login)){
							$login_data = $login[0];
						}
						else if(!empty($login2)){
							$login_data = $login2[0];
						}
						if(!empty($login) || !empty($login2)){
							$otp = get_random_number(6);
							$this->model->updateData('login',['otp'=>$otp],['id'=>$login_data['id']]);
							$subject = 'One Time Password';
							$message = '';
							$message .= '<p>'.$otp.' is your one time password(OTP). Please enter the OTP to proceed.</p><br>';
							$message .= '<p>Thank You</p>';
							$message .= '<p>Team Softonauts</p>';
							
							
							if($login_data['usertype'] == 'company'){
								sendEmail('info@softonauts.com',$login_data['email'],$subject,$message);
							}
							else if($login_data['usertype'] == 'customer'){
								$company_id = $this->model->getValue('customer','company_id',['id'=>$login_data['fk_id']]);
								$company_email = $this->model->getValue('login','email',['fk_id'=>$company_id]);
								sendEmail($company_email,$login_data['email'],$subject,$message);
							}
							$response['message'] = 'success';
							$response['code'] = 200;
							$response['status'] = true;
						}
						else{
							$response['message'] = 'Email/Phone is incorrect';
							$response['code'] = 203;
						}
					}
				}
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function verify_otp_for_password(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['otp'])){
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$otp = $this->model->getData2('login',['email'=>$_POST['username']],['phone'=>$_POST['username']],'otp,id');
						if(!empty($otp)){
							$id = $otp[0]['id'];
							$otp = $otp[0]['otp'];
						}
						if(!empty($otp) && $_POST['otp'] == $otp){
							$password = $this->model->getValue('login','password',['id'=>$id]);
							$email = $this->model->getValue('login','email',['id'=>$id]);
							$key = $email.','.$password;
							$key = encyrpt_password($key);
							$response['key'] = $key;
							$response['message'] = 'success';
							$response['code'] = 200;
							$response['status'] = true;
						}
						else{
							$response['message'] = 'Otp Incorrect';
							$response['code'] = 203;
						}
					}
				}
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

	/********************************** Reset Password *****************************************/

		function reset_password(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if ($_POST['new_password'] != $_POST['confirm_password']){
						$response['message'] = 'Password Mismatch';
						$response['code'] = 201;
					}
					else{
						$isExist = $this->model->getData('login',['email'=>$_POST['email'],'password'=>$_POST['old_password']]);
						if(!empty($isExist)){
							$this->model->updateData('login',['password'=>encyrpt_password($_POST['new_password'])],['email'=>$_POST['email']]);
							$response['message'] = 'Password Updated';
							$response['code'] = 200;
							$response['status'] = true;
							$subject = 'Password Updated';
							$message = '';
							$message .= '<p>Hello,'.$isExist[0]['username'].'.</p>';
							$message .= '<p>		We have received your request for forgot password.</p>';
							$message .= '<p>We have updated your password</p>';
							$message .= '<p>Thank You</p>';
							$message .= '<p>Team Softonauts</p>';
							if($isExist[0]['usertype'] == 'company'){
								sendEmail('info@softonauts.com',$isExist[0]['email'],$subject,$message);
							}
							else if($isExist[0]['usertype'] == 'customer'){
								$company_id = $this->model->getValue('customer','company_id',['id'=>$isExist[0]['fk_id']]);
								$company_email = $this->model->getValue('login','email',['fk_id'=>$company_id]);
								sendEmail($company_email,$isExist[0]['email'],$subject,$message);
							}
							sendEmail('info@softonauts.com',$isExist[0]['email'],$subject,$message);
						}
						else{
							$response['message'] = 'Incorrect Email/Password';
							$response['code'] = 203;
						}
					}
				}
				else{
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

	/********************************** Countries *****************************************/

		function get_countries(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])){
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$countries = $this->model->getData('countries',[],$select);
					$response['countries'] = $countries;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

	/********************************** States *****************************************/

		function get_states(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = 'id,name';
					if(!empty($_POST['select']) && isset($_POST['select'])){
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$states = $this->model->getData('states',[],$select);
					$response['states'] = $states;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_states_by_countries(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = 'id,name';
					if(!empty($_POST['select']) && isset($_POST['select'])){
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$state = $this->model->getData('states',['country_id'=>$_POST['country_id']],$select);
					if(count($state)>0)
					{
						$pro_select_box = '';
						// $pro_select_box .= '<option value="">Select State</option>';
						foreach ($state as $states) {
							$pro_select_box .='<option value="'.$states['id'].'">'.$states['name'].'</option>';
						}
					}

					$response['states'] = $pro_select_box;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

	/********************************** Cities *****************************************/

		function get_cities(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			$select = '*';
			if(!empty($_POST['select']) && isset($_POST['select'])){
				$select = $_POST['select'];
				unset($_POST['select']);
			}
			$cities = $this->model->getData('cities',$_POST,$select);
			if(empty($cities)){
				$response['message'] = 'No Data';
				$response['code'] = 201;
				echo json_encode($response);
				return;	
			}
			$response['cities'] = $cities;
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

		function get_cities2(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			$select = '*';
			if(!empty($_POST['select']) && isset($_POST['select'])){
				$select = $_POST['select'];
				unset($_POST['select']);
			}
			$cities = $this->model->getData('cities',$_POST,$select);
			if(empty($cities)){
				$response['message'] = 'No Data';
				$response['code'] = 201;
				echo json_encode($response);
				return;	
			}
			foreach ($cities as $key => $value) {
				if(!empty($value['state_id'])){
					$cities[$key]['country_id'] = $this->model->getValue('states','country_id',['id'=>$value['state_id']]);
				}
			}
			$response['cities'] = $cities;
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

		function get_cities_by_state(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = 'id,city,pincode';
					if(!empty($_POST['select']) && isset($_POST['select'])){
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$cities = $this->model->getData('cities',['state_id'=>$_POST['state_id']],$select);
					$pro_select_box = '';
					if(count($cities)>0)
					{
						// $pro_select_box .= '<option value="">Select City</option>';
						foreach ($cities as $cities) {
							$pro_select_box .='<option value="'.$cities['id'].'">'.$cities['city'].' ('.$cities['pincode'].')</option>';
						}
					}

					$response['cities'] = $pro_select_box;
					$response['cities2'] = $cities;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

	/********************************** Company *****************************************/
		function add_company(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$id = $this->model->getValue('login','id',['id'=>$_POST['created_by'],'usertype'=>'admin']);
					if (empty($id)) {
						$response['message'] = 'Wrong Parameters';
						$response['code'] = 201;
					}
					else if (empty($_POST['name']) && empty($_POST['email']) && empty($_POST['contact'])){
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{

						$isExist = $this->model->isExist('company','email',$_POST['email']);
						$isExist2 = $this->model->isExist('company','prsn_email',$_POST['prsn_email']);
						$isExist3 = $this->model->isExist('login','phone',$_POST['contact']);
						$isExist4 = $this->model->isExist('login','email',$_POST['email']);
						if($isExist || $isExist2 || $isExist4){
							$response['message'] = 'Email Exists';
							$response['code'] = 201;
						}
						else if($isExist3 ){
							$response['message'] = 'Mobile Exists';
							$response['code'] = 201;
						}
						else{
							$company_id = $this->model->insertData('company',$_POST);
							if(!empty($company_id)){
								$password = generateRandomString(8);
								$login = [];
								$login['fk_id'] = $company_id;
								$login['username'] = $_POST['name'];
								$login['phone'] = $_POST['contact'];
								$login['email'] = $_POST['email'];
								$login['logo'] = $_POST['logo'];
								$login['password'] = encyrpt_password($password);
								$login['usertype'] = 'company';
								$login['status'] = 1;
								$login['created_by'] = $_POST['created_by'];
								$this->model->insertData('login',$login);

								// $email_txt = $_POST['name'].'Thank you for Registration with us.';
				    //             $txt = " Your new password is : " . $password . "";
				    //             $email_data = array('email_txt' => $email_txt, 'txt' => $txt);
				    //             $subject = "Your password";
				    //             $message = $this->load->view('Email-template', $email_data, true);
								// sendEmail('piyush.nerkar@softonauts.com',$login['email'],$subject,$message);

								$subject = 'Welcome Message';
								$message = '';
								$message .= 'Hello,'.$login['username'];
								$message .= '<p>Welcome on board. We would like to inform you that your work';
								$message .= 'efficiency defineatly will grow</p>';

								$message .= '<p>Your User Id: '.$login['email'].'</p>';
								$message .= '<p>Your Password: '.$password.'</p>';
								$message .= '<p>Team Softonauts</p>';
								$message .= '<p>Help@softonauts.com</p>';
								
								sendEmail('info@softonauts.com',$login['email'],$subject,$message);
							}
							$response['message'] = 'Company Added';
							$response['code'] = 200;
							$response['status'] = true;
						}
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_all_company(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			$select = '*';
			if(!empty($_POST['select']) && isset($_POST['select'])){
				$select = $_POST['select'];
				unset($_POST['select']);
			}
			$order_by = [];
			if(!empty($_POST['order_by']) && isset($_POST['order_by'])){
				$order_by_arr = explode('=', $_POST['order_by']);
				$order_by[$order_by_arr[0]] = $order_by_arr[1];
				unset($_POST['order_by']);
			}
			$company = $this->model->getData('company',$_POST,$select,$order_by);
			if(!empty($company)){
				foreach ($company as $key => $value) {
					if(!empty($value['country_id'])){
						$company[$key]['country_name']=$this->model->getValue('countries','name',['id'=>$value['country_id']]);
					}
					if(!empty($value['city_id'])){
						$company[$key]['city_name']=$this->model->getValue('cities','city',['id'=>$value['city_id']]);
					}
					if(!empty($value['state_id'])){
						$company[$key]['state_name']=$this->model->getValue('states','name',['id'=>$value['state_id']]);
					}
				}
			}
			else{
				$company = [];
			}

			$response['next_id'] = $this->model->generate_next_id('company','autoid','COM','3');
			$response['companies'] = $company;
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

		function get_company(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					// $id = $this->model->getValue('login','id',['id'=>$_POST['created_by'],'usertype'=>'admin']);
					// if (empty($id)) {
					// 	$response['message'] = 'Admin id is required';
					// 	$response['code'] = 201;
					// }
					// else 
					if (empty($_POST['id'])){
						$response['message'] = 'Wrong Parameters';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])){
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$company = $this->model->getData('company',$_POST,$select);
						$response['company'] = $company;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function update_company(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Wrong Parameters';
						$response['code'] = 201;
					}
					else{
						$company = $this->model->updateData('company',$_POST,['id'=>$_POST['id']]);
						if(!empty($_POST['logo'])){
							$company = $this->model->updateData('login',['logo'=>$_POST['logo']],['fk_id'=>$_POST['id'],'usertype'=>'company']);
						}
						$response['message'] = 'Company Updated';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function delete_company(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Wrong Parameters';
						$response['code'] = 201;
					}
					else{
						$company = $this->model->deleteData('company',['id'=>$_POST['id']]);
						$company = $this->model->deleteData('login',['fk_id'=>$_POST['id'],'usertype'=>'company']);
						$response['message'] = 'Company Deleted';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}
	/***********************************Company setting************************************/
		function companySetting(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			$isExist = $this->model->isExist('company_setting','company_id',$_POST['company_id']);
			if($isExist){
				$this->model->updateData('company_setting',$_POST,['company_id'=>$_POST['company_id']]);
			}
			else{
				$this->model->insertData('company_setting',$_POST);
			}			
			$response['message'] = 'Setting Saved';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}	

		function getCompanySetting(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
			 	return;
			}
			if (empty($_POST['company_id'])){
				$response['message'] = 'Wrong Parameters';
				$response['code'] = 201;
				echo json_encode($response);
			 	return;
			}
			$select = '*';
			if(!empty($_POST['select']) && isset($_POST['select'])) {
				$select = $_POST['select'];
				unset($_POST['select']);
			}
			$isExist = $this->model->isExist('company_setting','company_id',$_POST['company_id']);
			if($isExist){
				$data = $this->model->getData('company_setting',['company_id'=>$_POST['company_id']],$select)[0];
			}
			$response['data'] = $data;
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}
		
		function getCompSetting(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
			 	return;
			}
			if (empty($_POST['company_id'])){
				$response['message'] = 'Wrong Parameters';
				$response['code'] = 201;
				echo json_encode($response);
			 	return;
			}
			/*
			*example $_POST['select'] = ['modes','transport_types','customer_types']
			*/
			if(empty($_POST['select'])){
				$response['message'] = 'Wrong Parameters';
				$response['code'] = 201;
				echo json_encode($response);
			 	return;
			}
			$_POST['select'] = explode(',', $_POST['select']);
			foreach ($_POST['select'] as $key => $fieldName) {
				$ids = $this->model->getValue('company_setting',$fieldName,['company_id'=>$_POST['company_id']]);
				if($fieldName == 'modes'){
					$ids = explode(',', $ids);
					$modes = $this->model->getData('mode',[],'id,mode_name',[],['id'=>$ids]);
					$response['modes'] = $modes;
				}
				if($fieldName == 'customer_types'){
					$ids = explode(',', $ids);
					$customer_types = $this->model->getData('customer_types',[],'id,type',[],['id'=>$ids]);
					$response['customer_types'] = $customer_types;
				}
				if($fieldName == 'transport_types'){
					$ids = explode(',', $ids);
					$transport_types = $this->model->getData('transport_type',[],'id,type',[],['id'=>$ids]);
					$response['transport_types'] = $transport_types;
				}
				if($fieldName == 'vendor_types'){
					$ids = explode(',', $ids);
					$vendor_types = $this->model->getData('vendor_types',[],'id,type',[],['id'=>$ids]);
					$response['vendor_types'] = $vendor_types;
				}
				if($fieldName == 'volumetric_weight'){
					$volumetric_weight = $ids;
					$response['volumetric_weight'] = json_decode($volumetric_weight,true);
				}
				if($fieldName == 'cft'){
					$cft = $ids;
					$response['cft'] = json_decode($cft,true);
				}
			}
			
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

	/********************************** Branch *****************************************/
		function add_branch(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['manager_name'])){
						$response['message'] = 'Wrong Parameters';
						$response['code'] = 201;
					}
					else{
						$isExist = $this->model->isExist('branch','email',$_POST['email']);
						$isExist2 = $this->model->isExist('branch','contact',$_POST['contact']);
						$isExist3 = $this->model->isExist('login','phone',$_POST['contact']);
						$isExist4 = $this->model->isExist('login','email',$_POST['email']);
						if($isExist || $isExist2 || $isExist3 || $isExist4){
							$response['message'] = 'Email/Contact Exists';
							$response['code'] = 201;
						}
						else{
							$branch_id = $this->model->insertData('branch',$_POST);
							if(!empty($branch_id)){
								
								$login = [];
								$login['fk_id'] = $branch_id;
								$login['username'] = $_POST['manager_name'];
								$login['phone'] = $_POST['contact'];
								$login['email'] = $_POST['email'];
								$login['usertype'] = 'branch';
								$login['status'] = 1;
								$login['created_by'] = $_POST['created_by'];
								$this->model->insertData('login',$login);
							}
							$response['message'] = 'Branch Added';
							$response['code'] = 200;
							$response['status'] = true;
						}
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_all_branches(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])){
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$branches = $this->model->getData('branch',$_POST,$select);
					if(!empty($branches))
					{
						foreach ($branches as $key => $value) {
							
							$branches[$key]['company_name']=$this->model->getValue('company','name',['id'=>$value['company_id']]);
							$branches[$key]['city_name']=$this->model->getValue('cities','city',['id'=>$value['city_id']]);
							$branches[$key]['state_name']=$this->model->getValue('states','name',['id'=>$value['state_id']]);
						}
					}
					$response['branches'] = $branches;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_branch(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Wrong Parameters';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$branch = $this->model->getData('branch',$_POST,$select);
						$response['branch'] = $branch;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function update_branch(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Wrong Parameters';
						$response['code'] = 201;
					}
					else if (empty($_POST['manager_name'])){
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						// echo '<pre>'; print_r($_POST); exit;
						$branch = $this->model->updateData('branch',$_POST,['id'=>$_POST['id']]);
						$response['message'] = 'Branch Updated';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function delete_branch(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Wrong Parameters';
						$response['code'] = 201;
					}
					else{
						$company = $this->model->deleteData('branch',['id'=>$_POST['id']]);
						$company = $this->model->deleteData('login',['fk_id'=>$_POST['id'],'usertype'=>'branch']);
						$response['message'] = 'Branch Deleted';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

	/********************************** Transport Type *****************************************/

		function get_transport_types(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$transport_types = $this->model->getData('transport_type',$_POST,$select);
					
					$response['transport_types'] = $transport_types;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
					
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function transport_types(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			$_POST['types']  = json_decode($_POST['types'],true);
			$_POST['type_ids']  = json_decode($_POST['type_ids'],true);
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['created_by']) || empty($_POST['types'])) {
						$response['message'] = 'Created_by is required';
						$response['code'] = 201;
					}
					else{
						$transport_types = $this->model->getData('transport_type',[],'id');
						if(!empty($transport_types)){
							$db_ids = array_column($transport_types, 'id');
							if(!empty($db_ids)){
								foreach ($db_ids as $key => $id) {
									if(!in_array($id, $_POST['type_ids'])){
										$this->model->deleteData2('transport_type',['id'=>$id]);
									}
								}
							}
						}
						
						if(!empty($_POST['types'])){
							foreach ($_POST['types'] as $key => $type) {
								
								if(isset($_POST['type_ids'][$key]) && !empty($_POST['type_ids'][$key])){
									$this->model->updateData('transport_type',['updated_by'=>$_POST['updated_by'],'type'=>trim($type)],['id'=>$_POST['type_ids'][$key]]);
								}
								else{
									$isExist = $this->model->getValue('transport_type','type',['created_by'=>$_POST['created_by'],'type'=>trim($type)]);
									if(empty($isExist)){
										if(!empty($type)){
											$this->model->insertData('transport_type',['created_by'=>$_POST['created_by'],'type'=>$type]);
										}
									}
									else{
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
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

	/********************************** Customer Types *****************************************/
		function customer_types(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			$_POST['types']  = json_decode($_POST['types'],true);
			$_POST['type_ids']  = json_decode($_POST['type_ids'],true);
			// echo"<pre>";
			// print_r($_POST);die;
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['created_by']) || empty($_POST['types'])) {
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$types = $this->model->getData('customer_types',[],'id');
						// $count = count($types);
						// // echo"<pre>";
						// // print_r($count);die;
						// // print_r($types);die;
						// if($count > 1){
							
						// }
						if(!empty($types)){
							$db_ids = array_column($types, 'id');
							if(!empty($db_ids)){
								foreach ($db_ids as $key => $id) {
									if(!in_array($id, $_POST['type_ids'])){
										$this->model->deleteData2('customer_types',['id'=>$id]);
									}
								}
							}
						}
						
						if(!empty($_POST['types'])){
							foreach ($_POST['types'] as $key => $type) {
								
								if(isset($_POST['type_ids'][$key]) && !empty($_POST['type_ids'][$key])){
									$this->model->updateData('customer_types',['updated_by'=>$_POST['updated_by'],'type'=>trim($type)],['id'=>$_POST['type_ids'][$key]]);
								}
								else{
									$isExist = $this->model->getValue('customer_types','type',['created_by'=>$_POST['created_by'],'type'=>$type]);
									if(empty($isExist)){
										if(!empty($type)){
											$this->model->insertData('customer_types',['created_by'=>$_POST['created_by'],'type'=>trim($type)]);
										}
									}
									else{
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
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_customer_types(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// print_r($_POST);die;
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$customer_types = $this->model->getData('customer_types',$_POST,$select);
					$response['customer_types'] = $customer_types;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

	/********************************** Status Types *****************************************/

		function status_types(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			$_POST['types']  = json_decode($_POST['status_name'],true);
			$_POST['type_ids']  = json_decode($_POST['type_ids'],true);
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['created_by']) || empty($_POST['types'])) {
						$response['message'] = 'Created_by is required';
						$response['code'] = 201;
					}
					else{
						$tbl_status_masters = $this->model->getData('tbl_status_master',[],'id');
						if(!empty($tbl_status_masters)){
							$db_ids = array_column($tbl_status_masters, 'id');
							if(!empty($db_ids)){
								foreach ($db_ids as $key => $id) {
									if(!in_array($id, $_POST['type_ids'])){
										$this->model->deleteData2('tbl_status_master',['id'=>$id]);
									}
								}
							}
						}
						
						if(!empty($_POST['types'])){
							foreach ($_POST['types'] as $key => $status_name) {
								
								if(isset($_POST['type_ids'][$key]) && !empty($_POST['type_ids'][$key])){
									$this->model->updateData('tbl_status_master',['updated_by'=>$_POST['updated_by'],'status_name'=>trim($status_name)],['id'=>$_POST['type_ids'][$key]]);
								}
								else{
									$isExist = $this->model->getValue('tbl_status_master','status_name',['created_by'=>$_POST['created_by'],'status_name'=>trim($status_name)]);
									if(empty($isExist)){
										if(!empty($status_name)){
											$this->model->insertData('tbl_status_master',['created_by'=>$_POST['created_by'],'status_name'=>$status_name]);
										}
									}
									else{
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
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_status_types(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// print_r($_POST);die;
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$status_types = $this->model->getData('tbl_status_master',$_POST,$select);
					$response['status_types'] = $status_types;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}	

	/********************************** Customer *****************************************/
		function add_customer(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				 return;
			}
			$isExist = $this->model->isExist('customer','email',$_POST['email']);
			$isExist2 = $this->model->isExist('login','email',$_POST['email']);
			if($isExist || $isExist2){
				$response['message'] = 'Email Exist';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			// $customer_contacts = [];
			// if(!empty($_POST['customer_contacts'])){
			// 	$customer_contacts = json_decode($_POST['customer_contacts'],true);
			// }
			// unset($_POST['customer_contacts']);
			$_POST['autoid'] = $this->model->generate_next_id('customer','autoid','CUST','3');

			$customer_id = $this->model->insertData('customer',$_POST);
			if(empty($customer_id)){
				$response['message'] = 'System Error';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			// if(!empty($customer_contacts)){
			// 	foreach ($customer_contacts as $key => $value) {
			// 		$value['customer_id'] = $customer_id;
			// 		$this->model->insertData('customer_contacts',$value);
			// 	}
			// }
			$password = generateRandomString(8);
			$customer = [];
			$customer['fk_id'] = $customer_id;
			$customer['username'] = $_POST['name'];
			$customer['phone'] = $_POST['contact'];
			$customer['email'] = $_POST['email'];
			$customer['usertype'] = 'customer';
			$customer['status'] = 1;
			$customer['created_by'] = $_POST['created_by'];
			$customer['password'] = encyrpt_password($password);
			$this->model->insertData('login',$customer);

			// $email_txt = $_POST['name'].'Thank you for Registration with us.';
   //          $txt = " Your new password is : " . $password . "";
   //          $email_data = array('email_txt' => $email_txt, 'txt' => $txt);
   //          $subject = "Your password";
   //          $message = $this->load->view('Email-template', $email_data, true);
			// sendEmail('piyush.nerkar@softonauts.com',$customer['email'],$subject,$message);

			$subject = 'Welcome Message';
			$message = '';
			$message .= 'Hello,'.$customer['username'];
			$message .= '<p>Welcome on board. We would like to inform you that your work';
			$message .= 'efficiency defineatly will grow</p>';

			$message .= '<p>Your User Id: '.$customer['email'].'</p>';
			$message .= '<p>Your Password: '.$password.'</p>';
			$message .= '<p>Team Softonauts</p>';
			$message .= '<p>Help@softonauts.com</p>';

			$company_email = $this->model->getValue('login','email',['fk_id'=>$_POST['company_id']]);
			sendEmail($company_email,$customer['email'],$subject,$message);

			$response['message'] = 'Customer Added';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

		function get_all_customers(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			$select = '*';
			$select2 = '*';
			$contacts = '0';
			if(!empty($_POST['select']) && isset($_POST['select'])) {
				$select = $_POST['select'];
				unset($_POST['select']);
			}
			if(!empty($_POST['select2']) && isset($_POST['select2'])) {
				$select2 = $_POST['select2'];
				unset($_POST['select2']);
			}
			if(!empty($_POST['contacts']) && isset($_POST['contacts'])) {
				$contacts = $_POST['contacts'];
				unset($_POST['contacts']);
			}
			$order_by = [];
			if(!empty($_POST['order_by']) && isset($_POST['order_by'])){
				$order_by_arr = explode('=', $_POST['order_by']);
				$order_by[$order_by_arr[0]] = $order_by_arr[1];
				unset($_POST['order_by']);
			}
			$customer = $this->model->getData('customer',$_POST,$select,$order_by);
			if(empty($customer)){
				$response['next_id'] = $this->model->generate_next_id('customer','autoid','CUST','3');
				$response['customer'] = [];
				$response['message'] = 'No Data';
				$response['code'] = 201;
				echo json_encode($response);
				return;	
			}
			foreach ($customer as $key => $value) {
				if($contacts == '1'){
					if(!empty($value['id'])){
						$customer[$key]['contacts'] = $this->model->getData('customer_contacts',['customer_id'=>$value['id']],$select2);
					}
				}
				if(!empty($value['mapped_customers'])){
					$customer[$key]['mapped_customers'] = explode(',', $value['mapped_customers']);
				}
				if(!empty($value['volumetric_weight'])){
					$customer[$key]['volumetric_weight'] = json_decode($value['volumetric_weight'],true);
				}
				if(!empty($value['cft'])){
					$customer[$key]['cft'] = json_decode($value['cft'],true);
				}
			}
			
			$response['next_id'] = $this->model->generate_next_id('customer','autoid','CUST','3');
			$response['customer'] = $customer;
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

		function getMappedCustomers(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			$mapped_customers = $this->model->getValue('customer','mapped_customers',$_POST);
			if(empty($mapped_customers)){
				$response['message'] = 'No Data';
				$response['code'] = 200;
				echo json_encode($response);
				return;	
			}
			$mapped_customers = explode(',', $mapped_customers);
			$mapped_customers2 = [];
			foreach ($mapped_customers as $key => $customer_id) {
				$mapped_customers2[$key]['id'] = $customer_id;
				$mapped_customers2[$key]['name'] = $this->model->getValue('customer','name',['id'=>$customer_id]);
			}
			$response['data'] = $mapped_customers2;
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

		function get_customer(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Wrong Parameters';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						$select2 = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							if(!empty($_POST['select']['contacts']) && isset($_POST['select']['contacts'])) {
								$select2 = $_POST['select']['contacts'];
								unset($_POST['select']['contacts']);
							}
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$customer = $this->model->getData('customer',$_POST,$select);
						if(!empty($customer)){
							foreach ($customer as $key => $value) {
								if(!empty($value['volumetric_weight'])){
									$customer[$key]['volumetric_weight'] = json_decode($value['volumetric_weight'],true);
								}
								if(!empty($value['cft'])){
									$customer[$key]['cft'] = json_decode($value['cft'],true);
								}
								$customer[$key]['contacts'] = $this->model->getData('customer_contacts',['customer_id'=>$value['id']],$select2);
							}
						}
						$response['customer'] = $customer;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function update_customer(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			if (empty($_POST['id'])){
				$response['message'] = 'Wrong Parameters';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			// $v_customer_contacts = [];
			// if(!empty($_POST['customer_contacts'])){
			// 	$_POST['customer_contacts'] = json_decode($_POST['customer_contacts'],true);
			// 	$v_customer_contacts = $_POST['customer_contacts'];
			// }
			
			unset($_POST['customer_contacts']);
			$customer = $this->model->updateData('customer',$_POST,['id'=>$_POST['id']]);

			// if(!empty($v_customer_contacts)) {
			// 	$v_ids = array_column($v_customer_contacts, 'id');
			// }
			// else{
			// 	$v_ids = [];
			// }
			// $db_customer_contacts = $this->model->getData('customer_contacts',['customer_id'=>$_POST['id']]);
			// if(!empty($db_customer_contacts)){
			// 	$db_ids = array_column($db_customer_contacts, 'id');
			// }
			// else{
			// 	$db_ids = [];
			// }
			// if(!empty($v_customer_contacts)){
			// 	foreach ($v_customer_contacts as $key => $value) {
			// 		if(!in_array($value['id'], $db_ids)) {
			// 			//insert
			// 			$value['customer_id'] = $_POST['id'];
			// 			$this->model->insertData('customer_contacts',$value);
			// 		}
			// 		else{
			// 			//update
			// 			$value['customer_id'] = $_POST['id'];
			// 			$this->model->updateData('customer_contacts',$value,['id'=>$value['id']]);

			// 		}
			// 	}
			// }
			// if(!empty($db_customer_contacts)){
			// 	foreach ($db_customer_contacts as $key => $value){
			// 		if(!in_array($value['id'], $v_ids)){
			// 			$this->model->deleteData('customer_contacts',['id'=>$value['id']]);
			// 		}
			// 	}
			// }

			$response['message'] = 'Customer Updated';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

		function delete_customer(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			if (empty($_POST['id'])){
				$response['message'] = 'Wrong Parameters';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			$this->model->deleteData('customer',['id'=>$_POST['id']]);
			// $customer = $this->model->deleteData('customer_contacts',['customer_id'=>$_POST['id']]);
			$this->model->deleteData('login',['fk_id'=>$_POST['id'],'usertype'=>'customer']);
			$response['message'] = 'Customer Deleted';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

	/********************************** Customer Contacts*****************************************/
		function add_customer_contact(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['customer_id'])) {
						$response['message'] = 'Customer id is required';
						$response['code'] = 201;
					}
					else{
						$this->model->insertData('customer_contacts',$_POST);
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function addCustomerContact(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			if(empty($_POST['customer_id']) || empty($_POST['type']) || empty($_POST['name'])|| empty($_POST['contact'])|| empty($_POST['address1']) || empty($_POST['pincode']) || empty($_POST['country_id']) || empty($_POST['state_id']) || empty($_POST['city_id']) ){
				$response['message'] = 'Wrong Parameters';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			$customer_name = $this->model->getValue('customer','name',['id'=> $_POST['customer_id']]);
			$_POST['city'] = !empty($_POST['city']) ? $_POST['city'] : '';
			$_POST['customer_name'] = $customer_name.' ('.$_POST['city'].')';
			if(isset($_POST['default_address']) && $_POST['default_address'] =='1'){
				$this->model->updateData('customer_contacts',['default_address'=>0],['customer_id'=>$_POST['customer_id']]);
				$_POST['default_address'] = '1';
			}
			$this->model->insertData('customer_contacts',$_POST);
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

		function getCustomerContacts(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			$select = '*';
			if(!empty($_POST['select']) && isset($_POST['select'])) {
				$select = $_POST['select'];
				unset($_POST['select']);
			}
			$_POST = empty($_POST) ? [] : $_POST;
			$contacts = $this->model->getData('customer_contacts',$_POST,$select);
			if(!empty($contacts)){
				foreach ($contacts as $key => $value) {
					$contacts[$key]['city_name'] = '';
					$contacts[$key]['state_name'] = '';
					$contacts[$key]['country_name'] = '';
					if(!empty($value['city_id'])){
						$contacts[$key]['city_name']=$this->model->getValue('cities','city',['id'=>$value['city_id']]);	
					}
					if(!empty($value['state_id'])){
						$contacts[$key]['state_name']=$this->model->getValue('states','name',['id'=>$value['state_id']]);	
					}
					if(!empty($value['country_id'])){
						$contacts[$key]['country_name']=$this->model->getValue('countries','name',['id'=>$value['country_id']]);	
					}
				}
			}
			if(empty($contacts)) $contacts = [];
			$response['contacts'] = $contacts;
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

		function updateCustomerContact(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			if (empty($_POST["id"])) {
				$response['message'] = 'Wrong Parameters';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			if(empty($_POST['customer_id']) || empty($_POST['type']) || empty($_POST['name'])|| empty($_POST['contact'])|| empty($_POST['address1']) || empty($_POST['pincode']) || empty($_POST['country_id']) || empty($_POST['state_id']) || empty($_POST['city_id']) ){
				$response['message'] = 'Wrong Parameters';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			$this->model->updateData('customer_contacts',$_POST,['id'=>$_POST['id']]);
			$response['message'] = 'Address Updated';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

		function deleteCustomerContact(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			if (empty($_POST["id"])) {
				$response['message'] = 'Wrong Parameters';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			$this->model->deleteData('customer_contacts',['id'=>$_POST['id']]);
			$response['message'] = 'Address Deleted';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}
	/********************************** Designation *****************************************/
		function designations(){
			$response = array('code' => -1, 'status' => false, 'message' => '');

			$_POST['designations']  = json_decode($_POST['designations'],true);
			$_POST['designation_ids']  = json_decode($_POST['designation_ids'],true);
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['created_by']) || empty($_POST['designations'])){
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$designations = $this->model->getData('designation',[],'id');
						if(!empty($designations)){
							$db_ids = array_column($designations, 'id');
							if(!empty($db_ids)){
								foreach ($db_ids as $key => $id) {
									if(!in_array($id, $_POST['designation_ids'])){
										$this->model->deleteData('designation',['id'=>$id]);
									}
								}
							}
						}
						if(!empty($_POST['designations'])){
							foreach ($_POST['designations'] as $key => $designation) {
							
								if(isset($_POST['designation_ids'][$key]) && !empty($_POST['designation_ids'][$key])){
									$this->model->updateData('designation',['updated_by'=>$_POST['updated_by'],'designation'=>$designation],['id'=>$_POST['designation_ids'][$key]]);
								}
								else{
									$isExist = $this->model->getValue('designation','designation',['created_by'=>$_POST['created_by'],'designation'=>$designation]);
									if(empty($isExist)){
										if(!empty($designation)){
											$this->model->insertData('designation',['created_by'=>$_POST['created_by'],'designation'=>$designation]);
										}
									}
									else{
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
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_designations(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$designations = $this->model->getData('designation',$_POST,$select);
					$response['designations'] = $designations;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

	/********************************** Employee *****************************************/
		function add_employee(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// echo"<pre>";
			// print_r($_POST);die;
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['name'])){
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$isExist = $this->model->isExist('employee','email',$_POST['email']);
						if(!$isExist){
							$employee_id = $this->model->insertData('employee',$_POST);
							if(!empty($employee_id)){
								$employee = [];
								$employee['fk_id'] = $employee_id;
								$employee['username'] = $_POST['name'];
								$employee['phone'] = $_POST['contact'];
								$employee['email'] = $_POST['email'];
								$employee['logo'] = $_POST['photo'];
								$employee['usertype'] = 'employee';
								$employee['status'] = 1;
								$employee['created_by'] = $_POST['created_by'];
								$this->model->insertData('login',$employee);
							}
							$response['message'] = 'Employee Added';
							$response['code'] = 200;
							$response['status'] = true;
						}
						else{
							$response['message'] = 'Email Exist';
							$response['code'] = 201;
						}
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_all_employee(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$employees = $this->model->getData('employee',$_POST,$select);
					if(!empty($employees))
					{
						foreach ($employees as $key => $value) {
							if(!empty($value['company_id'])){
								$employees[$key]['company_name']=$this->model->getValue('company','name',['id'=>$value['company_id']]);
							}
							if(!empty($value['city_id'])){
								$employees[$key]['city_name']=$this->model->getValue('cities','city',['id'=>$value['city_id']]);
							}
							if(!empty($value['state_id'])){
								$employees[$key]['state_name']=$this->model->getValue('states','name',['id'=>$value['state_id']]);
							}
							if(!empty($value['designation_id'])){
								$employees[$key]['designation']=$this->model->getValue('designation','designation',['id'=>$value['designation_id']]);
							}
							
						}
					}
					$response['next_id'] = $this->model->generate_next_id('employee','autoid','EMP','3');
					$response['employees'] = $employees;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_employee(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Employee id is required';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$employee = $this->model->getData('employee',$_POST,$select);
						$response['employee'] = $employee;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function update_employee(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Wrong Parameters';
						$response['code'] = 201;
					}
					else{
						$employee = $this->model->updateData('employee',$_POST,['id'=>$_POST['id']]);

						$employee = $this->model->updateData('login',['logo'=>$_POST['photo']],['fk_id'=>$_POST['id'],'usertype'=>'employee']);

						$response['message'] = 'Employee Updated';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function delete_employee(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Wrong Parameters';
						$response['code'] = 201;
					}
					else{
						$this->model->deleteData('employee',['id'=>$_POST['id']]);
						$this->model->deleteData('login',['fk_id'=>$_POST['id'],'usertype'=>'employee']);
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}	

	/********************************** Vendor Types *****************************************/
		function vendor_types(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			$_POST['types']  = json_decode($_POST['types'],true);
			$_POST['type_ids']  = json_decode($_POST['type_ids'],true);
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['created_by']) || empty($_POST['types'])) {
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$types = $this->model->getData('vendor_types',[],'id');
						if(!empty($types)){
							$db_ids = array_column($types, 'id');
							if(!empty($db_ids)){
								foreach ($db_ids as $key => $id) {
									if(!in_array($id, $_POST['type_ids'])){
										$this->model->deleteData2('vendor_types',['id'=>$id]);
									}
								}
							}
						}
						
						if(!empty($_POST['types'])){
							foreach ($_POST['types'] as $key => $type) {
								
								if(isset($_POST['type_ids'][$key]) && !empty($_POST['type_ids'][$key])){
									$this->model->updateData('vendor_types',['updated_by'=>$_POST['updated_by'],'type'=>trim($type)],['id'=>$_POST['type_ids'][$key]]);
								}
								else{
									$isExist = $this->model->getValue('vendor_types','type',['created_by'=>$_POST['created_by'],'type'=>$type]);
									if(empty($isExist)){
										if(!empty($type)){
											$this->model->insertData('vendor_types',['created_by'=>$_POST['created_by'],'type'=>trim($type)]);
										}
									}
									else{
										$response['message'] = 'Already Exist';
										$response['code'] = 201;
									}
								}
							}
						}
						
						
						
						$response['message'] = 'Data Updated';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_vendor_types(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$vendor_types = $this->model->getData('vendor_types',$_POST,$select);
					$response['vendor_types'] = $vendor_types;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

	/********************************** Vendor *****************************************/
		function add_vendor(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['contact_prsn_name'])){
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$isExist = $this->model->isExist('vendor','email',$_POST['email']);
						$isExist2 = $this->model->isExist('login','email',$_POST['prsn_email']);
						if(!$isExist && !$isExist2){
							$vendor_id = $this->model->insertData('vendor',$_POST);
							if(!empty($vendor_id)){
								$vendor = [];
								$vendor['fk_id'] = $vendor_id;
								$vendor['username'] = $_POST['contact_prsn_name'];
								$vendor['phone'] = $_POST['contact'];
								$vendor['email'] = $_POST['email'];
								$vendor['logo'] = $_POST['logo'];
								$vendor['usertype'] = 'vendor';
								$vendor['status'] = 1;
								$vendor['created_by'] = $_POST['created_by'];
								$this->model->insertData('login',$vendor);
							}
							$response['message'] = 'success';
							$response['code'] = 200;
							$response['status'] = true;
						}
						else{
							$response['message'] = 'Vendor email is already exist';
							$response['code'] = 201;
						}
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_all_vendors(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$vendors = $this->model->getData('vendor',$_POST,$select);
					if(!empty($vendors))
					{
						foreach ($vendors as $key => $value) {
							
							$vendors[$key]['city_name']=$this->model->getValue('cities','city',['id'=>$value['city_id']]);
							$vendors[$key]['state_name']=$this->model->getValue('states','name',['id'=>$value['state_id']]);
							$vendors[$key]['country_name']=$this->model->getValue('countries','name',['id'=>$value['country_id']]);
							$vendors[$key]['vendor_type']=$this->model->getValue('vendor_types','type',['id'=>$value['type_id']]);

						}
					}
					$response['next_id'] = $this->model->generate_next_id('vendor','autoid','VEN',3);
					$response['vendors'] = $vendors;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_vendor(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Vendor id is required';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$vendor = $this->model->getData('vendor',$_POST,$select);
						$response['vendor'] = $vendor;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function update_vendor(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Vendor id is required';
						$response['code'] = 201;
					}
					else{
						$vendor = $this->model->updateData('vendor',$_POST,['id'=>$_POST['id']]);
						$vendor = $this->model->updateData('login',['logo'=>$_POST['logo']],['fk_id'=>$_POST['id'],'usertype'=>'vendor']);

						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function delete_vendor(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Vendor id is required';
						$response['code'] = 201;
					}
					else{
						$this->model->deleteData('vendor',['id'=>$_POST['id']]);
						$this->model->deleteData('login',['fk_id'=>$_POST['id'],'usertype'=>'vendor']);
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

	/********************************** Vehicle *****************************************/
		function add_vehicle(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['regno'])){
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$isExist = $this->model->isExist('vehicle','regno',$_POST['regno']);
						if(!$isExist){
							$vehicle_id = $this->model->insertData('vehicle',$_POST);
							
							$response['message'] = 'success';
							$response['code'] = 200;
							$response['status'] = true;
						}
						else{
							$response['message'] = 'Registration no is already exist';
							$response['code'] = 201;
						}
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_all_vehicles(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$order_by = [];
					if(!empty($_POST['order_by']) && isset($_POST['order_by'])){
						$order_by_arr = explode('=', $_POST['order_by']);
						$order_by[$order_by_arr[0]] = $order_by_arr[1];
						unset($_POST['order_by']);
					}
					$vehicles = $this->model->getData('vehicle',$_POST,$select,$order_by);
					if(!empty($vehicles))
					{
						foreach ($vehicles as $key => $value) {

							$vehicles[$key]['company_name']=$this->model->getValue('company','name',['id'=>$value['company_id']]);
							
						}
					}
					$response['vehicles'] = $vehicles;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_vehicle(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Vehicle id is required';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$vehicle = $this->model->getData('vehicle',$_POST,$select);
						$response['vehicle'] = $vehicle;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function update_vehicle(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Wrong Parameters';
						$response['code'] = 201;
					}
					else{
						$vehicle = $this->model->updateData('vehicle',$_POST,['id'=>$_POST['id']]);

						$response['message'] = 'Data Updated';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function delete_vehicle(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Vehicle id is required';
						$response['code'] = 201;
					}
					else{
						$this->model->deleteData('vehicle',['id'=>$_POST['id']]);
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}	

	/********************************** Zone *****************************************/

		function add_zone(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// echo"<pre>";
			// print_r($_POST);die;
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['transport_type']) || empty($_POST['zone_code']) || empty($_POST['zone'])){
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$isExist = $this->model->getValue('zone','zone',['created_by'=>$_POST['created_by'],'zone'=>$_POST['zone']]);
						if(empty($isExist)){
							// $_POST['cities'] = implode(',', $_POST['cities']);
							// $_POST['countries'] = implode(',', $_POST['countries']);
							$zone_id = $this->model->insertData('zone',$_POST);
							$response['message'] = 'success';
							$response['code'] = 200;
							$response['status'] = true;
						}
						else{
							$response['message'] = 'Zone is already exist';
							$response['code'] = 201;
						}
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_all_zones(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// print_r($_POST);die;
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$zones = $this->model->getData('zone',$_POST,$select);
					if(empty($zones)) $zones = [];
					$response['zone'] = $zones;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_zone(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Zone id is required';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$zone = $this->model->getData('zone',$_POST,$select);
						if(!empty($zone)){
							foreach ($zone as $key => $value) {
								$zone[$key]['cities'] = explode(',', $value['cities']);
								$zone[$key]['countries'] = explode(',', $value['countries']);
							}
						}
						$response['zone'] = $zone;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function update_zone(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Zone id is required';
						$response['code'] = 201;
					}
					// else if (empty($_POST['transport_type']) || empty($_POST['zone_code']) || empty($_POST['zone'])){
					// 	$response['message'] = 'Less Parameters';
					// 	$response['code'] = 201;
					// }
					else{
						// $_POST['cities'] = implode(',', $_POST['cities']);
						// $_POST['countries'] = implode(',', $_POST['countries']);
						$zone = $this->model->updateData('zone',$_POST,['id'=>$_POST['id']]);
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function delete_zone(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Zone id is required';
						$response['code'] = 201;
					}
					else{
						$zone = $this->model->deleteData('zone',['id'=>$_POST['id']]);
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

	/********************************** Zone Areas *****************************************/

		// function add_zone_areas(){
		// 	$response = array('code' => -1, 'status' => false, 'message' => '');
		// 	$validate = validateToken();
		// 	if($validate){
		// 		if ($_SERVER["REQUEST_METHOD"] == "POST"){
		// 			if (empty($_POST['state_id']) || empty($_POST['company_id']) || empty($_POST['zone_id'] || empty($_POST['area_name']))){
		// 				$response['message'] = 'Less Parameters';
		// 				$response['code'] = 201;
		// 			}
		// 			else{
		// 				$isExist = $this->model->isExist('zone_areas','area_name',$_POST['area_name']);
		// 				if(!$isExist){
		// 					$this->model->insertData('zone_areas',$_POST);
		// 					$response['message'] = 'success';
		// 					$response['code'] = 200;
		// 					$response['status'] = true;
		// 				}
		// 				else{
		// 					$response['message'] = 'Area name is already exist';
		// 					$response['code'] = 201;
		// 				}
		// 			}
		// 		} 
		// 		else {
		// 			$response['message'] = 'Invalid Request';
		// 			$response['code'] = 204;
		// 		}
		// 	}
		// 	else{
		// 		$response['message'] = 'Authentication required';
		// 		$response['code'] = 203;
		// 	} 
		// 	echo json_encode($response);
		// }

		// function get_company_zone_areas(){
		// 	$response = array('code' => -1, 'status' => false, 'message' => '');
		// 	$validate = validateToken();
		// 	if($validate){
		// 		if ($_SERVER["REQUEST_METHOD"] == "POST"){
		// 			if (empty($_POST['company_id'])){
		// 				$response['message'] = 'Company id is required';
		// 				$response['code'] = 201;
		// 			}
		// 			else{
		// 				$zone_areas = $this->model->getData('zone_areas',['company_id'=>$_POST['company_id']]);
		// 				$response['zone_areas'] = $zone_areas;
		// 				$response['message'] = 'success';
		// 				$response['code'] = 200;
		// 				$response['status'] = true;
		// 			}
		// 		} 
		// 		else {
		// 			$response['message'] = 'Invalid Request';
		// 			$response['code'] = 204;
		// 		}
		// 	}
		// 	else{
		// 		$response['message'] = 'Authentication required';
		// 		$response['code'] = 203;
		// 	} 
		// 	echo json_encode($response);
		// }

		// function get_state_zone_areas(){
		// 	$response = array('code' => -1, 'status' => false, 'message' => '');
		// 	$validate = validateToken();
		// 	if($validate){
		// 		if ($_SERVER["REQUEST_METHOD"] == "POST"){
		// 			if (empty($_POST['company_id'])){
		// 				$response['message'] = 'Company id is required';
		// 				$response['code'] = 201;
		// 			}
		// 			else if (empty($_POST['state_id'])){
		// 				$response['message'] = 'State id is required';
		// 				$response['code'] = 201;
		// 			}
		// 			else{
		// 				$zone_areas = $this->model->getData('zone_areas',$_POST);
		// 				$response['zone_areas'] = $zone_areas;
		// 				$response['message'] = 'success';
		// 				$response['code'] = 200;
		// 				$response['status'] = true;
		// 			}
		// 		} 
		// 		else {
		// 			$response['message'] = 'Invalid Request';
		// 			$response['code'] = 204;
		// 		}
		// 	}
		// 	else{
		// 		$response['message'] = 'Authentication required';
		// 		$response['code'] = 203;
		// 	} 
		// 	echo json_encode($response);
		// }

		// function get_zone_areas(){
		// 	$response = array('code' => -1, 'status' => false, 'message' => '');
		// 	$validate = validateToken();
		// 	if($validate){
		// 		if ($_SERVER["REQUEST_METHOD"] == "POST"){
		// 			if (empty($_POST['zone_id'])){
		// 				$response['message'] = 'Zone id is required';
		// 				$response['code'] = 201;
		// 			}
		// 			else{
		// 				$zone_areas = $this->model->getData('zone_areas',$_POST);
		// 				$response['zone_areas'] = $zone_areas;
		// 				$response['message'] = 'success';
		// 				$response['code'] = 200;
		// 				$response['status'] = true;
		// 			}
		// 		} 
		// 		else {
		// 			$response['message'] = 'Invalid Request';
		// 			$response['code'] = 204;
		// 		}
		// 	}
		// 	else{
		// 		$response['message'] = 'Authentication required';
		// 		$response['code'] = 203;
		// 	} 
		// 	echo json_encode($response);
		// }

		// function update_zone_areas(){
		// 	$response = array('code' => -1, 'status' => false, 'message' => '');
		// 	$validate = validateToken();
		// 	if($validate){
		// 		if ($_SERVER["REQUEST_METHOD"] == "POST"){
		// 			if (empty($_POST['id'])){
		// 				$response['message'] = 'Zone Areas id is required';
		// 				$response['code'] = 201;
		// 			}
		// 			else if (empty($_POST['state_id']) || empty($_POST['company_id']) || empty($_POST['zone_id']) || empty($_POST['area_name'])){
		// 				$response['message'] = 'Less Parameters';
		// 				$response['code'] = 201;
		// 			}
		// 			else{
		// 				$zone_areas = $this->model->updateData('zone_areas',$_POST,['id'=>$_POST['id']]);
		// 				$response['message'] = 'success';
		// 				$response['code'] = 200;
		// 				$response['status'] = true;
		// 			}
		// 		} 
		// 		else {
		// 			$response['message'] = 'Invalid Request';
		// 			$response['code'] = 204;
		// 		}
		// 	}
		// 	else{
		// 		$response['message'] = 'Authentication required';
		// 		$response['code'] = 203;
		// 	} 
		// 	echo json_encode($response);
		// }

		// function delete_zone_areas(){
		// 	$response = array('code' => -1, 'status' => false, 'message' => '');
		// 	$validate = validateToken();
		// 	if($validate){
		// 		if ($_SERVER["REQUEST_METHOD"] == "POST"){
		// 			if (empty($_POST['id'])){
		// 				$response['message'] = 'Zone Areas id is required';
		// 				$response['code'] = 201;
		// 			}
		// 			else{
		// 				$zone_areas = $this->model->deleteData('zone_areas',['id'=>$_POST['id']]);
		// 				$response['message'] = 'success';
		// 				$response['code'] = 200;
		// 				$response['status'] = true;
		// 			}
		// 		} 
		// 		else {
		// 			$response['message'] = 'Invalid Request';
		// 			$response['code'] = 204;
		// 		}
		// 	}
		// 	else{
		// 		$response['message'] = 'Authentication required';
		// 		$response['code'] = 203;
		// 	} 
		// 	echo json_encode($response);
		// }

	/********************************** Pincode *****************************************/

		function add_pincode(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['pincode'])) {
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$isExist = $this->model->getValue('pincode','pincode',['created_by'=>$_POST['created_by'],'pincode'=>$_POST['pincode']]);
						if(empty($isExist)){
							$this->model->insertData('pincode',$_POST);
							$response['message'] = 'success';
							$response['code'] = 200;
							$response['status'] = true;
						}
						else{
							$response['message'] = 'Pincode is already exist';
							$response['code'] = 201;
						}
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_all_pincodes(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['created_by'])){
						$response['message'] = 'Created_by is required';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$pincode = $this->model->getData('pincode',['created_by'=>$_POST['created_by']],$select);
						$response['pincode'] = $pincode;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_pincode(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Pincode id is required';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$pincode = $this->model->getData('pincode',$_POST,$select);
						$response['pincode'] = $pincode;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				// } 
				// else {
				// 	$response['message'] = 'Invalid Request';
				// 	$response['code'] = 204;
				// }
			}
			else{
				$response['message'] = 'Authentication required';
				$response['code'] = 203;
			} 
			echo json_encode($response);
		}

		function update_pincode(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Pincode id is required';
						$response['code'] = 201;
					}
					else if (empty($_POST['created_by']) || empty($_POST['pincode'])) {
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$pincode = $this->model->updateData('pincode',$_POST,['id'=>$_POST['id']]);
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function delete_pincode(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Pincode id is required';
						$response['code'] = 201;
					}
					else{
						$pincode = $this->model->deleteData('pincode',['id'=>$_POST['id']]);
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

	/********************************** Mode *****************************************/
		// function add_mode(){
		// 	$response = array('code' => -1, 'status' => false, 'message' => '');
		// 	// $validate = validateToken();
		// 	// if($validate){
		// 		if ($_SERVER["REQUEST_METHOD"] == "POST"){
		// 			if (empty($_POST['created_by']) || empty($_POST['mode_name']) ){
		// 				$response['message'] = 'Less Parameters';
		// 				$response['code'] = 201;
		// 			}
		// 			else{
		// 				$isExist = $this->model->getValue('mode','mode_name',['created_by'=>$_POST['created_by'],'zone'=>$_POST['zone']]);
		// 				if(empty($isExist)){
		// 					$this->model->insertData('mode',$_POST);
		// 					$response['message'] = 'success';
		// 					$response['code'] = 200;
		// 					$response['status'] = true;
		// 				}
		// 				else{
		// 					$response['message'] = 'Mode is already exist';
		// 					$response['code'] = 201;
		// 				}
		// 			}
		// 		} 
		// 		else {
		// 			$response['message'] = 'Invalid Request';
		// 			$response['code'] = 204;
		// 		}
		// 	// }
		// 	// else{
		// 	// 	$response['message'] = 'Authentication required';
		// 	// 	$response['code'] = 203;
		// 	// } 
		// 	echo json_encode($response);
		// }

		function get_all_modes(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$mode = $this->model->getData('mode',$_POST,$select);
					$response['mode'] = $mode;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		// function get_mode(){
		// 	$response = array('code' => -1, 'status' => false, 'message' => '');
		// 	$validate = validateToken();
		// 	if($validate){
		// 		if ($_SERVER["REQUEST_METHOD"] == "POST"){
		// 			if (empty($_POST['id'])){
		// 				$response['message'] = 'Mode id is required';
		// 				$response['code'] = 201;
		// 			}
		// 			else{
		// 				$mode = $this->model->getData('mode',$_POST);
		// 				$response['mode'] = $mode;
		// 				$response['message'] = 'success';
		// 				$response['code'] = 200;
		// 				$response['status'] = true;
		// 			}
		// 		} 
		// 		else {
		// 			$response['message'] = 'Invalid Request';
		// 			$response['code'] = 204;
		// 		}
		// 	}
		// 	else{
		// 		$response['message'] = 'Authentication required';
		// 		$response['code'] = 203;
		// 	} 
		// 	echo json_encode($response);
		// }

		// function update_mode(){
		// 	$response = array('code' => -1, 'status' => false, 'message' => '');
		// 	$validate = validateToken();
		// 	if($validate){
		// 		if ($_SERVER["REQUEST_METHOD"] == "POST"){
		// 			if (empty($_POST['id'])){
		// 				$response['message'] = 'Mode id is required';
		// 				$response['code'] = 201;
		// 			}
		// 			else if (empty($_POST['company_id']) || empty($_POST['mode_name'])){
		// 				$response['message'] = 'Less Parameters';
		// 				$response['code'] = 201;
		// 			}
		// 			else{
		// 				$mode = $this->model->updateData('mode',$_POST,['id'=>$_POST['id']]);
		// 				$response['message'] = 'success';
		// 				$response['code'] = 200;
		// 				$response['status'] = true;
		// 			}
		// 		} 
		// 		else {
		// 			$response['message'] = 'Invalid Request';
		// 			$response['code'] = 204;
		// 		}
		// 	}
		// 	else{
		// 		$response['message'] = 'Authentication required';
		// 		$response['code'] = 203;
		// 	} 
		// 	echo json_encode($response);
		// }

		// function delete_mode(){
		// 	$response = array('code' => -1, 'status' => false, 'message' => '');
		// 	$validate = validateToken();
		// 	if($validate){
		// 		if ($_SERVER["REQUEST_METHOD"] == "POST"){
		// 			if (empty($_POST['id'])){
		// 				$response['message'] = 'Mode id is required';
		// 				$response['code'] = 201;
		// 			}
		// 			else{
		// 				$mode = $this->model->deleteData('mode',['id'=>$_POST['id']]);
		// 				$response['message'] = 'success';
		// 				$response['code'] = 200;
		// 				$response['status'] = true;
		// 			}
		// 		} 
		// 		else {
		// 			$response['message'] = 'Invalid Request';
		// 			$response['code'] = 204;
		// 		}
		// 	}
		// 	else{
		// 		$response['message'] = 'Authentication required';
		// 		$response['code'] = 203;
		// 	} 
		// 	echo json_encode($response);
		// }

		function modes(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			$_POST['mode_name']  = json_decode($_POST['mode_name'],true);
			$_POST['mode_ids']  = json_decode($_POST['mode_ids'],true);
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['created_by']) || empty($_POST['mode_name'])) {
						$response['message'] = 'Created_by is required';
						$response['code'] = 201;
					}
					else{
						$modes = $this->model->getData('mode',[],'id');
						if(!empty($modes)){
							$db_ids = array_column($modes, 'id');
							if(!empty($db_ids)){
								foreach ($db_ids as $key => $id) {
									if(!in_array($id, $_POST['mode_ids'])){
										$this->model->deleteData2('mode',['id'=>$id]);
									}
								}
							}
						}
						if(!empty($_POST['mode_name'])){
							foreach ($_POST['mode_name'] as $key => $mode_name) {
								// $isExist = $this->model->getValue('mode','mode_name',['created_by'=>$_POST['created_by'],'mode_name'=>$mode_name]);
								// if(empty($isExist)){
								// 	$this->model->insertData('mode',['created_by'=>$_POST['created_by'],'mode_name'=>$mode_name,'mode_type'=>$_POST['mode_types'][$key]]);
								// }

								if(isset($_POST['mode_ids'][$key]) && !empty($_POST['mode_ids'][$key])){
									$this->model->updateData('mode',['updated_by'=>$_POST['updated_by'],'mode_name'=>trim($mode_name)],['id'=>$_POST['mode_ids'][$key]]);
								}
								else{
									$isExist = $this->model->getValue('mode','mode_name',['created_by'=>$_POST['created_by'],'mode_name'=>$mode_name]);
									if(empty($isExist)){
										if(!empty($mode_name)){
											$this->model->insertData('mode',['created_by'=>$_POST['created_by'],'mode_name'=>trim($mode_name)]);
										}
									}
									else{
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
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

	/********************************** Global Rates *****************************************/
		function global_rates(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['company_id']) || empty($_POST['kg_or_box']) || empty($_POST['transport_type_id']) || empty($_POST['mode_id']) || empty($_POST['global_rates']) ) {
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{

						foreach ($_POST['global_rates'] as $from_zone => $value) {
							foreach ($value as $to_zone => $rate) {
								$isExist = $this->model->getValue('global_rates','rate',['company_id'=>$_POST['company_id'],'transport_type_id'=>$_POST['transport_type_id'],'mode_id'=>$_POST['mode_id'],'from_zone_id'=>$from_zone,'to_zone_id'=>$to_zone,'kg_or_box'=>$_POST['kg_or_box']]);
								if(!empty($isExist)){
									$this->model->updateData('global_rates',['min_price_for_kg'=>$_POST['min_price_for_kg'],'rate'=>$rate],['company_id'=>$_POST['company_id'],'transport_type_id'=>$_POST['transport_type_id'],'mode_id'=>$_POST['mode_id'],'from_zone_id'=>$from_zone,'to_zone_id'=>$to_zone,'kg_or_box'=>$_POST['kg_or_box']]);
								}
								else{
									$this->model->insertData('global_rates',['min_price_for_kg'=>$_POST['min_price_for_kg'],'kg_or_box'=>$_POST['kg_or_box'],'company_id'=>$_POST['company_id'],'transport_type_id'=>$_POST['transport_type_id'],'mode_id'=>$_POST['mode_id'],'from_zone_id'=>$from_zone,'to_zone_id'=>$to_zone,'rate'=>$rate]);
								}
							}
						}
						// $this->model->insertData('mode',$_POST);
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_global_rates(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['cust_id']) && empty($_POST['mode_id']) && $_POST['transport_type_id']){
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$global_rates = $this->global_ratesl->getData('global_rates',$_POST,$select);
						$response['global_rates'] = $global_rates;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}
	
	/********************************** Customer Rates *****************************************/
		function customer_rates(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['company_id']) || empty($_POST['cust_id']) || empty($_POST['kg_or_box']) || empty($_POST['transport_type_id']) || empty($_POST['mode_id']) || empty($_POST['customer_rates']) ) {
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{

						foreach ($_POST['customer_rates'] as $from_zone => $value) {
							foreach ($value as $to_zone => $rate) {
								$isExist = $this->model->getValue('customer_rates','rate',['company_id'=>$_POST['company_id'],'cust_id'=>$_POST['cust_id'],'transport_type_id'=>$_POST['transport_type_id'],'mode_id'=>$_POST['mode_id'],'from_zone_id'=>$from_zone,'to_zone_id'=>$to_zone,'kg_or_box'=>$_POST['kg_or_box']]);
								if(!empty($isExist)){
									$this->model->updateData('customer_rates',['min_price_for_kg'=>$_POST['min_price_for_kg'],'rate'=>$rate],['company_id'=>$_POST['company_id'],'cust_id'=>$_POST['cust_id'],'transport_type_id'=>$_POST['transport_type_id'],'mode_id'=>$_POST['mode_id'],'from_zone_id'=>$from_zone,'to_zone_id'=>$to_zone,'kg_or_box'=>$_POST['kg_or_box']]);
								}
								else{
									$this->model->insertData('customer_rates',['min_price_for_kg'=>$_POST['min_price_for_kg'],'kg_or_box'=>$_POST['kg_or_box'],'company_id'=>$_POST['company_id'],'cust_id'=>$_POST['cust_id'],'transport_type_id'=>$_POST['transport_type_id'],'mode_id'=>$_POST['mode_id'],'from_zone_id'=>$from_zone,'to_zone_id'=>$to_zone,'rate'=>$rate]);
								}
							}
						}
						// $this->model->insertData('mode',$_POST);
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_custmer_rates(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			$validate = validateToken();
			if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['cust_id']) && empty($_POST['mode_id']) && $_POST['transport_type_id']){
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$customer_rates = $this->customer_ratesl->getData('customer_rates',$_POST,$select);
						$response['customer_rates'] = $customer_rates;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			}
			else{
				$response['message'] = 'Authentication required';
				$response['code'] = 203;
			} 
			echo json_encode($response);
		}

	/********************************** Rates *****************************************/
		function save_rates(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
			 	return;
			}
			// if (empty($_POST['customer_type']) || empty($_POST['company_id']) || empty($_POST['kg_or_box']) || empty($_POST['transport_type_id']) || empty($_POST['mode_id']) || empty($_POST['global_rates'])  ) {
			// 	$response['message'] = 'Less Parameters';
			// 	$response['code'] = 201;
			// 	echo json_encode($response);
	 		// 		return;
			// }
			$insert = $_POST;
			unset($insert['rate']);
			unset($insert['from_zone_id']);
			unset($insert['to_zone_id']);
			unset($insert['customer_type']);

			if(strtolower($_POST['customer_type']) == 'normal'){
				unset($insert['customer_id']);
				$rates = $this->model->getValue('global_rates','rates',['company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']]);
				if(!empty($rates)){
					$rates = unserialize($rates);
					$rates[$_POST['from_zone_id']][$_POST['to_zone_id']] = $_POST['rate'];
					$insert['rates'] = serialize($rates);
					$this->model->updateData('global_rates',$insert,['company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']]);
				}
				else{
					$rate = array($_POST['from_zone_id']=>[$_POST['to_zone_id']=>$_POST['rate']]);
					$insert['rates'] = serialize($rate);
					$this->model->insertData('global_rates',$insert);
				}
				$response['message'] = 'Rates Updated';
				$response['code'] = 200;
				$response['status'] = true;
				echo json_encode($response);
				return;
			}
			if(!empty($_POST['customer_id'])){
				$rates = $this->model->getValue('customer_rates','rates',['company_id'=>$_POST['company_id'],'customer_id'=>$_POST['customer_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']]);
				if(!empty($rates)){
					$rates = unserialize($rates);
					$rates[$_POST['from_zone_id']][$_POST['to_zone_id']] = $_POST['rate'];
					$insert['rates'] = serialize($rates);
					$this->model->updateData('customer_rates',$insert,['company_id'=>$_POST['company_id'],'customer_id'=>$_POST['customer_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']]);
				}
				else{
					$rate = array($_POST['from_zone_id']=>[$_POST['to_zone_id']=>$_POST['rate']]);
					$insert['rates'] = serialize($rate);
					$this->model->insertData('customer_rates',$insert);
				}
				$response['message'] = 'Rates Updated';
				$response['code'] = 200;
				$response['status'] = true;
				echo json_encode($response);
				return;
			}
		}
		function saveAllRates(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
			 	return;
			}
			// if (empty($_POST['customer_type']) || empty($_POST['company_id']) || empty($_POST['kg_or_box']) || empty($_POST['transport_type_id']) || empty($_POST['mode_id']) || empty($_POST['global_rates'])  ) {
			// 	$response['message'] = 'Less Parameters';
			// 	$response['code'] = 201;
			// 	echo json_encode($response);
	 		// 		return;
			// }
			$insert = $_POST;
			unset($insert['customer_type']);

			if(strtolower($_POST['customer_type']) == 'normal'){
				unset($insert['customer_id']);
				$rates = $this->model->getValue('global_rates','rates',['company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']]);
				if(!empty($rates)){
					$this->model->updateData('global_rates',$insert,['company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']]);
				}
				else{
					$this->model->insertData('global_rates',$insert);
				}
				$response['message'] = 'Rates Updated';
				$response['code'] = 200;
				$response['status'] = true;
				echo json_encode($response);
				return;
			}
			if(strtolower($_POST['customer_type']) == 'prime' && !empty($_POST['customer_id'])){
				$rates = $this->model->getData('customer_rates',['company_id'=>$_POST['company_id'],'customer_id'=>$_POST['customer_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']],'id,rates');
				if(!empty($rates)){
					// $rates = unserialize($rates);
					// $p_rates = unserialize($_POST['rates']);
					// if(!empty($p_rates)){
					// 	foreach ($p_rates as $from_zone_id => $value) {
					// 		if(!empty($value)){
					// 			foreach ($value as $to_zone_id => $rate) {
					// 				$rates[$from_zone_id][$to_zone_id] = $rate;
					// 			}
					// 		}
					// 	}
					// }
					// $insert['rates'] = unserialize($_POST['rates']);
					$this->model->updateData('customer_rates',['rates'=>$insert['rates']],['id'=>$rates[0]['id']]);
				}
				else{
					$this->model->insertData('customer_rates',$insert);
				}
				$response['message'] = 'Rates Updated';
				$response['code'] = 200;
				$response['status'] = true;
				echo json_encode($response);
				return;
			}
			echo json_encode($response);
		}
		function get_rates(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
			 	return;
			}
			$rates = $this->model->getData('global_rates',['company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']],'rates')[0]['rates'];
			if(!empty($rates)){
				$rates = unserialize($rates);
			}
			else{
				$rates = [];
			}
			if($_POST['customer_type'] == 'prime'){
				if(empty($_POST['customer_id'])){
					$response['message'] = 'Wrong Parameters';
					$response['code'] = 204;
					echo json_encode($response);
					return;
				}
				$customer_rates = $this->model->getData('customer_rates',['customer_id'=>$_POST['customer_id'],'company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']],'rates')[0]['rates'];
				$customer_rates = unserialize($customer_rates);
				if(empty($customer_rates)) {
				}
				else{
					$rates = $customer_rates;
				}
				// foreach($customer_rates as $from_zone_id => $value){
				// 	if(empty($value)) $value = [];
				// 	foreach ($value as $to_zone_id => $rate) {
				// 		if(!empty($customer_rates[$from_zone_id][$to_zone_id])){
				// 			if(isset($rates[$from_zone_id][$to_zone_id])){
				// 				$rates[$from_zone_id][$to_zone_id] = $rate;
				// 			}
				// 			else{
				// 				$rates[$from_zone_id] = [];
				// 				$rates[$from_zone_id][$to_zone_id] = $rate;
				// 			}
				// 		}
				// 	}
				// }
			}
			
			$response['rates'] = $rates;
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}
	/********************************** TAT *****************************************/
		function tat(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['company_id']) || empty($_POST['transport_type_id']) || empty($_POST['mode_id']) || empty($_POST['tat']) ) {
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{

						foreach ($_POST['tat'] as $from_zone => $value) {
							foreach ($value as $to_zone => $time) {
								$isExist = $this->model->getValue('tat','time',['company_id'=>$_POST['company_id'],'transport_type_id'=>$_POST['transport_type_id'],'mode_id'=>$_POST['mode_id'],'from_zone_id'=>$from_zone,'to_zone_id'=>$to_zone]);
								if(!empty($isExist)){
									$this->model->updateData('tat',['time'=>$time],['company_id'=>$_POST['company_id'],'transport_type_id'=>$_POST['transport_type_id'],'mode_id'=>$_POST['mode_id'],'from_zone_id'=>$from_zone,'to_zone_id'=>$to_zone]);
								}
								else{
									$this->model->insertData('tat',['company_id'=>$_POST['company_id'],'transport_type_id'=>$_POST['transport_type_id'],'mode_id'=>$_POST['mode_id'],'from_zone_id'=>$from_zone,'to_zone_id'=>$to_zone,'time'=>$time]);
								}
							}
						}
						// $this->model->insertData('mode',$_POST);
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}
		function getTat(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
			 	return;
			}
			$time = $this->model->getData('tat',['company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'transport_mode'=>$_POST['transport_mode'],'type'=>$_POST['type']],'time')[0]['time'];
			if(!empty($time)){
				$time = unserialize($time);
			}
			else{
				$time = [];
			}			
			$response['time'] = $time;
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}
		function saveTat(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
			 	return;
			}
			$insert = $_POST;
			unset($insert['from_zone_id']);
			unset($insert['to_zone_id']);
			$time = $this->model->getValue('tat','time',['company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'transport_mode'=>$_POST['transport_mode'],'type'=>$_POST['type']]);
			if(!empty($time)){
				$time = unserialize($time);
				if(!is_array($time)) $time = [];
				if(!isset($time[$_POST['from_zone_id']])) $time[$_POST['from_zone_id']] = [];
				$time[$_POST['from_zone_id']][$_POST['to_zone_id']] = $_POST['time'];
				$insert['time'] = serialize($time);
				$this->model->updateData('tat',$insert,['company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'transport_mode'=>$_POST['transport_mode'],'type'=>$_POST['type']]);
			}
			else{
				$time = array($_POST['from_zone_id']=>[$_POST['to_zone_id']=>$_POST['time']]);
				$insert['time'] = serialize($time);
				$this->model->insertData('tat',$insert);
			}
			$response['message'] = 'TAT Updated';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
			return;
			
		}
		function saveAllTat(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
			 	return;
			}
			$insert = $_POST;
			$time = $this->model->getValue('tat','time',['company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'transport_mode'=>$_POST['transport_mode'],'type'=>$_POST['type']]);
			if(!empty($time)){
				$this->model->updateData('tat',$insert,['company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'transport_mode'=>$_POST['transport_mode'],'type'=>$_POST['type']]);
			}
			else{
				$this->model->insertData('tat',$insert);
			}
			$response['message'] = 'TAT Updated';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
			return;
		}
	/********************************** Quotations *****************************************/
		function saveQuotation(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
			 	return;
			}
			$insert = $_POST;
			unset($insert['rate']);
			unset($insert['from_zone_id']);
			unset($insert['to_zone_id']);
			$rates = $this->model->getValue('quotation','rates',['quotation_number'=>$_POST['quotation_number'],'company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']]);
			if(!empty($rates)){
				$rates = unserialize($rates);
				$rates[$_POST['from_zone_id']][$_POST['to_zone_id']] = $_POST['rate'];
				$insert['rates'] = serialize($rates);
				$this->model->updateData('quotation',$insert,['company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']]);
				$response['message'] = 'Quotation Updated';
			}
			else{
				$rate = array($_POST['from_zone_id']=>[$_POST['to_zone_id']=>$_POST['rate']]);
				$insert['rates'] = serialize($rate);
				$this->model->insertData('quotation',$insert);
				$response['message'] = 'Quotation Added';
			}
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
			return;
		}
		function addQuotations(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
			 	return;
			}
			$insert = $_POST;
			$where = [];
			$where['transport_type'] = $_POST['transport_type'];
			$where['transport_mode'] = $_POST['transport_mode'];
			$where['transport_speed'] = $_POST['transport_speed'];
			$where['delivery_type'] = $_POST['delivery_type'];
			$where['quotation_number'] = $_POST['quotation_number'];
			$isExist = $this->model->getData('quotation',$where);
			if(!empty($isExist)){
				$response['message'] = 'Quotation Exists';
				$response['code'] = 201;
				echo json_encode($response);
			 	return;
			}
			$insert = $_POST;
			$this->model->insertData('quotation',$insert);
			$response['message'] = 'Quotation Added';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}
		function getQuotations(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
			 	return;
			}
			$where = ['company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'transport_speed'=>$_POST['transport_speed'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']];
			
			$rates = $this->model->getData('global_rates',$where,'rates')[0]['rates'];
			if(!empty($rates)){
				$rates = unserialize($rates);
			}
			else{
				$rates = [];
			}

			if(!empty($_POST['quotation_number'])){
				$where['quotation_number'] = $_POST['quotation_number'];
			}
			$rates2 = $this->model->getData('quotation',$where,'rates')[0]['rates'];
			if(!empty($rates2)){
				$rates = unserialize($rates2);				
			}
			
			$response['rates'] = $rates;
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}
		function getQuotation(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
			 	return;
			}
			$select = '*';
			if(!empty($_POST['select']) && isset($_POST['select'])){
				$select = $_POST['select'];
				unset($_POST['select']);
			}
			$_POST = empty($_POST) ? [] : $_POST;

			$quotation = $this->model->getData('quotation',$_POST,$select);
			if(empty($quotation)){
				$quotation = [];			
			}
			else{
				foreach ($quotation as $key => $value) {
					if(empty($value['rates'])) continue;
					$rates = unserialize($value['rates']);
					if(empty($rates)) continue;
					$rates2 = [];
					$all_empty = false;
					foreach ($rates as $key3 => $value3) {
						if(empty($value3)) continue;
						$from_zone = $this->model->getValue('zone','zone',['id'=>$key3]);
						$rates2[$from_zone] = [];
						foreach ($value3 as $key2 => $rate) {
							if(empty($rate)){
								$all_empty = true;
								continue;
							}
							else{
								$all_empty = false;
							}
							$to_zone = $this->model->getValue('zone','zone',['id'=>$key2]);
							$rates2[$from_zone][$to_zone] = $rate;
						}
					}
					$quotation[$key]['rates'] = $rates2;
					if($all_empty) unset($quotation[$key]);
				}
			}
			
			$response['quotation'] = $quotation;
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}
	/********************************** Ship(Order) *****************************************/

		function placeOrder(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			if (empty($_POST["shipping_charges"]) || $_POST["shipping_charges"] < 1) {
				$response['message'] = 'Incorrect Order';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			
			$shipper = json_decode($_POST['shipper'],true);
			$recepient = json_decode($_POST['recepient'],true);
			$ship_dimensions = json_decode($_POST['dimensions'],true);
			$new_sender = isset($_POST['new_sender']) ? $_POST['new_sender'] : '';
			$default_sender = isset($_POST['default_sender']) ? $_POST['default_sender'] : '';
			$new_recipient = isset($_POST['new_recipient']) ? $_POST['new_recipient'] : '';
			$residential_address = isset($_POST['residential_address']) ? $_POST['residential_address'] : '';
			unset($_POST['shipper']);
			unset($_POST['recepient']);
			unset($_POST['dimensions']);
			unset($_POST['default_sender']);
			unset($_POST['new_sender']);
			unset($_POST['residential_address']);
			unset($_POST['new_recipient']);
			if(empty($shipper) || empty($recepient)){
				$response['message'] = 'Wrong Parameters';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			$where = ['pincode'=>$shipper['pincode'],'customer_id'=>$shipper['customer_id']];
			$where['type'] = 'sender';
			$shipper['type'] = 'sender';
			$shipper['name'] = explode('(', $shipper['name'])[0];
			$id = $this->model->getValue('customer_contacts','id',$where);
			if(!empty($default_sender)){
				$this->model->updateData('customer_contacts',['default_address'=>0],['customer_id'=>$shipper['customer_id']]);
				$shipper['default_address'] = '1';
			}
			if($id != ''){
				$this->model->updateData('customer_contacts',$shipper,$where);
			}
			else /*if(!empty($new_sender))*/{
				$id = $this->model->insertData('customer_contacts',$shipper);
			}
			

			$_POST['shipper_contact'] = $id;
			$where = ['pincode'=>$recepient['pincode'],'customer_id'=>$recepient['customer_id']];
			$where['type'] = 'recepient';
			$recepient['type'] = 'recepient';
			$recepient['name'] = explode('(', $recepient['name'])[0];
			$id = $this->model->getValue('customer_contacts','id',$where);
			if(!empty($residential_address)){
				$this->model->updateData('customer_contacts',['default_address'=>0],['customer_id'=>$recepient['customer_id']]);
				$recepient['default_address'] = '1';
			}
			if($id != ''){
				$this->model->updateData('customer_contacts',$recepient,$where);
			}
			else /*if(!empty($new_recipient))*/{
				$id = $this->model->insertData('customer_contacts',$recepient);
			}
			$_POST['recepient_contact'] = $id;

			$ship_id = $this->model->insertData('ship',$_POST);
			if(!empty($ship_dimensions)) {
				foreach ($ship_dimensions as $key => $ship_dimension){
					$ship_dimension['ship_id'] = $ship_id;
					$this->model->insertData('ship_dimensions',$ship_dimension);
				}
			}
			
			$response['ship_id'] = $ship_id;
			$response['message'] = 'Order Placed';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

		function getShipment(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			$select = '*';
			if(!empty($_POST['select']) && isset($_POST['select'])) {
				$select = $_POST['select'];
				unset($_POST['select']);
			}
			$_POST = empty($_POST) ? [] : $_POST;

			$ship = $this->model->getData('ship',$_POST,$select);
			$response['ship'] = $ship;
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

		function updateShipment(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			$_POST = empty($_POST) ? [] : $_POST;
			$this->model->updateData('ship',$_POST,['id'=>$_POST['id']]);
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

		function ship_history(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			$select = '*';
			if(!empty($_POST['select']) && isset($_POST['select'])) {
				$select = $_POST['select'];
				unset($_POST['select']);
			}
			$ship_history = $this->model->getData('ship',$_POST,$select,['id'=>'DESC']);
			if(!empty($ship_history)){
				foreach ($ship_history as $key => $value) {
					if(isset($value['shipper_contact'])){
						$ship_history[$key]['shipper'] = $this->model->getValue('customer_contacts','customer_name',['id'=>$value['shipper_contact']]);
					}
					if(isset($value['recepient_contact'])){
						$ship_history[$key]['recepient'] = $this->model->getValue('customer_contacts','customer_name',['id'=>$value['recepient_contact']]);
					}
					if(!empty($value['id'])){
						$ship_history[$key]['ship_dimensions'] = $this->model->getData('ship_dimensions',['ship_id'=>$value['id']]);
						$ship_history[$key]['ship_payment'] = $this->model->getData('ship_payment',['ship_id'=>$value['id']]);	
					}
				}
			}
			else{
				$ship_history = [];
			}
			$response['ship_history'] = $ship_history;
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

		function getShipCharges(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			$rate = [];
			if(($_POST['bill_to'] == 'sender' || $_POST['bill_to'] != 'recepient' || $_POST['bill_to'] != 'third_party') && !empty($_POST['sender_id'])) {
	    		$customer_type = $this->model->getValue('customer','type',['id'=>$_POST['sender_id']]);
	    		$customer_type = strtolower($customer_type);
	    		$customer_id = $_POST['sender_id'];
	    	}
	    	else if($_POST['bill_to'] == 'recepient' && !empty($_POST['recepient_id'])){
	    		$customer_type = $this->model->getValue('customer','type',['id'=>$_POST['recepient_id']]);
	    		$customer_type = strtolower($customer_type);
	    		$customer_id = $_POST['recepient_id'];
	    	}
	    	$rate['company_id'] = $_POST['company_id'];
	    	$rate['transport_type'] = $_POST['transport_type'];
	    	$rate['transport_speed'] = $_POST['transport_speed'];
	    	$rate['delivery_type'] = $_POST['delivery_type'];
	    	$rate['transport_mode'] = $_POST['transport_mode'];
	    	$rate['kg_or_box'] = $_POST['weight_unit'];
	    	$rates = $this->model->getValue('global_rates','rates',$rate);
	    	if(!empty($rates)){
	    		$rates = unserialize($rates);
	    	}
	    	else{
	    		$rates = [];
	    	}
			$is_prime = false;
	    	if($customer_type == 'prime'){
	    		$start_date = $this->model->getValue('customer','start_date',['id'=>$customer_id]);
	    		$end_date = $this->model->getValue('customer','end_date',['id'=>$customer_id]);
	    		$current_date = date('d/m/Y');
	    		if($current_date <= $end_date){
	    			$is_prime = true;
	    			$rate['customer_id'] = $customer_id;
	    			$customer_rates = $this->model->getValue('customer_rates','rates',$rate);
	    			$customer_rates = unserialize($customer_rates);
	    			if(empty($customer_rates)) $customer_rates = [];
	    			foreach($customer_rates as $key => $value){
	    				if(empty($value)) $value = [];
	    				foreach ($value as $key2 => $value2) {
	    					if(!empty($customer_rates[$key][$key2])){
	    						$rates[$key][$key2] = $value2;
	    					}
	    				}
	    			}
	    		}
	    	}
	    	$from_zones = $this->model->getSqlData('SELECT id,zone_type,customer_id,zone FROM zone WHERE FIND_IN_SET('.$_POST['sender_city_id'].',cities) > 0');
	    	if(empty($from_zones)){
	    		$response['message'] = 'Incorrect Pincode';
				$response['code'] = 201;
				$response['status'] = false;
				echo json_encode($response);
				return;
	    	}
	    	$from_zone_id = $from_zones[0]['id'];
	    	if(($_POST['bill_to'] == 'sender' || $_POST['bill_to'] != 'recepient' || $_POST['bill_to'] != 'third_party') && !empty($_POST['sender_id'])){ 
	    		foreach ($from_zones as $key => $value) {
	    			if($is_prime && $value['zone_type'] == 'customized' && $value['customer_id'] == $customer_id){
	    				$from_zone_id = $value['id'];
	    			}
	    		}
	    	}
	    	
	    	$to_zones = $this->model->getSqlData('SELECT id,zone_type,customer_id,zone FROM zone WHERE FIND_IN_SET('.$_POST['recepient_city_id'].',cities) > 0');
	    	if(empty($to_zones)){
	    		$response['message'] = 'Incorrect Pincode';
				$response['code'] = 201;
				$response['status'] = false;
				echo json_encode($response);
				return;
	    	}
	    	$to_zone_id = $to_zones[0]['id'];
	    	if($_POST['bill_to'] == 'recepient'){
	    		foreach ($to_zones as $key => $value) {
	    			if($is_prime && $value['zone_type'] == 'customized' && $value['customer_id'] == $customer_id){
	    				$to_zone_id = $value['id'];
	    			}
	    		}
	    	}
	    	
	    	$rate = isset($rates[$from_zone_id][$to_zone_id]) ? $rates[$from_zone_id][$to_zone_id] : '';
	    	$response['customer_type'] = $customer_type;
	    	$response['rate'] = $rate;
	    	$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
	    	echo json_encode($response);
		}

		function getOtherCharges(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			$customer = [];
			$customer_type = '';
			if(($_POST['bill_to'] == 'sender' || $_POST['bill_to'] != 'recepient' || $_POST['bill_to'] != 'third_party') && !empty($_POST['sender_id'])) {
	    		$customer_type = $this->model->getValue('customer','type',['id'=>$_POST['sender_id']]);
	    		$customer = $this->model->getData('customer',['id'=>$_POST['sender_id']],'insurance_charges,bilty_charges,toll_charges,fuel_charges');
	    		$customer_type = strtolower($customer_type);
	    		$customer_id = $_POST['sender_id'];
	    	}
	    	else if($_POST['bill_to'] == 'recepient' && !empty($_POST['recepient_id'])){
	    		$customer_type = $this->model->getValue('customer','type',['id'=>$_POST['recepient_id']]);
	    		$customer = $this->model->getData('customer',['id'=>$_POST['recepient_id']],'insurance_charges,bilty_charges,toll_charges,fuel_charges');
	    		$customer_type = strtolower($customer_type);
	    		$customer_id = $_POST['recepient_id'];
	    	}
	    	if(empty($customer)){
	    		$response['message'] = 'Wrong Parameters';
	    		$response['code'] = 203;
	    		echo json_encode($response);
	    		return;
	    	}
		   	$response['customer'] = $customer[0];
			$is_prime = false;
    		$start_date = $this->model->getValue('customer','start_date',['id'=>$customer_id]);
    		$end_date = $this->model->getValue('customer','end_date',['id'=>$customer_id]);
    		$current_date = date('d/m/Y');
    		if($customer_type == 'prime'){
	    		if($current_date <= $end_date){
	    			$is_prime = true;
	    		}
    		}
	    	$response['is_prime'] = $is_prime;
	    	$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
	    	echo json_encode($response);
		}

		function getCityZoneId(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			$select = '*';
			if(!empty($_POST['select']) && isset($_POST['select'])) {
				$select = $_POST['select'];
				unset($_POST['select']);
			}
	    	$zone_id = $this->model->getSqlData('SELECT id FROM zone WHERE FIND_IN_SET('.$_POST['city_id'].',cities) > 0');
	    	if(!isset($zone_id[0])){
		    	$response['message'] = 'City Unavailable';
				$response['code'] = 201;
				$response['status'] = false;
		    	echo json_encode($response);
		    	return;
	    	}
			$zone_id = $zone_id[0]['id'];
	    	$response['zone_id'] = $zone_id;
	    	$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
	    	echo json_encode($response);
		}

		function generateInvoice() {
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if(!$validate){
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			//  	echo json_encode($response);
			//  	return;
			// }
			if ($_SERVER["REQUEST_METHOD"] != "POST") {
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
				echo json_encode($response);
				return;
			}
			// $pickup_city, $drop_city, $grand_total, $pickup_address, $drop_address, $shipping_mode, $pickup_email, $drop_email = '')
			// $select = 'company_id,shipper_contact,recepient_contact,total_actual_weight,total_charge_weight,no_of_boxes,transport_mode,total_invoice_value,e_way_bill_no';
			$ship = $this->model->getData('ship',['id'=>$_POST['id']])[0];
			$ship_dimensions = $this->model->getData('ship_dimensions',['ship_id'=>$_POST['id']]);
			$logo  = $this->model->getValue('company','logo',['id'=>$ship['company_id']]);
			$company_name  = $this->model->getValue('company','name',['id'=>$ship['company_id']]);
			$ship['logo'] = $logo;
			$ship['company_name'] = $company_name;
			$post = [];
			// $select = 'id,name,contacts,city,email,address1,address2,pincode,country_id,state_id,city_id';
			$post['id'] = $ship['company_id'];
			$company = $this->model->getData('company',$post)[0];
			if(!empty($company)){
				$company['city'] = $this->model->getValue('cities','city',['id'=>$company['city_id']]);
				$company['state'] = $this->model->getValue('states','name',['id'=>$company['state_id']]);
			}
			$post['id'] = $ship['shipper_contact'];
			$shipper = $this->model->getData('customer_contacts',$post)[0];
			$post['id'] = $ship['recepient_contact'];
			$recepient = $this->model->getData('customer_contacts',$post)[0];
			$file_name = $_POST['id'] . "_" . mt_rand(100000, 999999) . "_" . "air_way";
        	$this->db->update('ship', array('airway_file_name' => $file_name . ".pdf"), array('id' => $_POST['id']));

			$response['ship'] = $ship;
			$response['ship_dimensions'] = $ship_dimensions;
			$response['shipper'] = $shipper;
			$response['recepient'] = $recepient;
			$response['company'] = $company;
			$response['file_name'] = $file_name;
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
	        echo json_encode($response);
	    }

	/********************************** Delivery boys *****************************************/
		function add_delivery_boy(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['contact'])){
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$isExist = $this->model->isExist('delivery_boys','contact',$_POST['contact']);
						if(!$isExist){
							$delivery_boys_id = $this->model->insertData('delivery_boys',$_POST);
							
							$response['message'] = 'success';
							$response['code'] = 200;
							$response['status'] = true;
						}
						else{
							$response['message'] = 'Contact is already exist';
							$response['code'] = 201;
						}
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_all_delivery_boys(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$delivery_boyss = $this->model->getData('delivery_boys',['created_by'=>$_POST['created_by']],$select);
					$response['delivery_boyss'] = $delivery_boyss;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_delivery_boy(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Delivery boys id is required';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$delivery_boys = $this->model->getData('delivery_boys',$_POST,$select);
						$response['delivery_boy'] = $delivery_boys;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function update_delivery_boy(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Delivery boy id is required';
						$response['code'] = 201;
					}
					else{
						$delivery_boys = $this->model->updateData('delivery_boys',$_POST,['id'=>$_POST['id']]);

						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function delete_delivery_boy(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Delivery boys id is required';
						$response['code'] = 201;
					}
					else{
						$this->model->deleteData('delivery_boys',['id'=>$_POST['id']]);
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}	

	/********************************** Driver *****************************************/
		function add_driver(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['contact'])){
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$isExist = $this->model->isExist('driver','contact',$_POST['contact']);
						if(!$isExist){
							$driver_id = $this->model->insertData('driver',$_POST);
							
							$response['message'] = 'success';
							$response['code'] = 200;
							$response['status'] = true;
						}
						else{
							$response['message'] = 'Contact is already exist';
							$response['code'] = 201;
						}
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_all_drivers(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$drivers = $this->model->getData('driver',['created_by'=>$_POST['created_by']],$select);
					$response['drivers'] = $drivers;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_driver(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Driver id is required';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$driver = $this->model->getData('driver',$_POST,$select);
						$response['driver'] = $driver;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function update_driver(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Driver id is required';
						$response['code'] = 201;
					}
					else{
						$driver = $this->model->updateData('driver',$_POST,['id'=>$_POST['id']]);

						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function delete_driver(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Driver id is required';
						$response['code'] = 201;
					}
					else{
						$this->model->deleteData('driver',['id'=>$_POST['id']]);
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}	

	/********************************** Sales executive *****************************************/
		function add_sales_executive(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['contact'])){
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$isExist = $this->model->isExist('sales_executive','contact',$_POST['contact']);
						if(!$isExist){
							 $this->model->insertData('sales_executive',$_POST);
							
							$response['message'] = 'success';
							$response['code'] = 200;
							$response['status'] = true;
						}
						else{
							$response['message'] = 'Contact is already exist';
							$response['code'] = 201;
						}
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_all_sales_executives(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$sales_executives = $this->model->getData('sales_executive',['created_by'=>$_POST['created_by']],$select);
					$response['sales_executives'] = $sales_executives;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_sales_executive(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Sales executive id is required';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$sales_executive = $this->model->getData('sales_executive',$_POST,$select);
						$response['sales_executive'] = $sales_executive;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function update_sales_executive(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Sales executive id is required';
						$response['code'] = 201;
					}
					else{
						$sales_executive = $this->model->updateData('sales_executive',$_POST,['id'=>$_POST['id']]);

						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function delete_sales_executive(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Sales executive id is required';
						$response['code'] = 201;
					}
					else{
						$this->model->deleteData('sales_executive',['id'=>$_POST['id']]);
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}	

	/********************************** Sales charge *****************************************/
		function add_sales_charge(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['type'])){
						$response['message'] = 'Less Parameters';
						$response['code'] = 201;
					}
					else{
						$isExist = $this->model->isExist('sales_charges','type',$_POST['type']);
						if(!$isExist){
							$sales_charges_id = $this->model->insertData('sales_charges',$_POST);
							
							$response['message'] = 'success';
							$response['code'] = 200;
							$response['status'] = true;
						}
						else{
							$response['message'] = 'Contact is already exist';
							$response['code'] = 201;
						}
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_all_sales_charges(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$sales_chargess = $this->model->getData('sales_charges',['created_by'=>$_POST['created_by']],$select);
					$response['sales_chargess'] = $sales_chargess;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_sales_charge(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Sales charge id is required';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$sales_charges = $this->model->getData('sales_charges',$_POST,$select);
						$response['sales_charge'] = $sales_charges;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function update_sales_charge(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Sales charge id is required';
						$response['code'] = 201;
					}
					else{
						$sales_charges = $this->model->updateData('sales_charges',$_POST,['id'=>$_POST['id']]);

						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function delete_sales_charge(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Sales charge id is required';
						$response['code'] = 201;
					}
					else{
						$this->model->deleteData('sales_charges',['id'=>$_POST['id']]);
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}	

	/********************************** Documents *****************************************/

		function get_documents(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$documents = $this->model->getData('documents',$_POST,$select);
					$response['documents'] = $documents;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function documents(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
	
					$documents = $this->model->getData('document',[],'id');
					if(!empty($documents)){
						$db_ids = array_column($documents, 'id');
						if(!empty($db_ids)){
							foreach ($db_ids as $key => $id) {
								if(!in_array($id, $_POST['doc_ids'])){
									$this->model->deleteData('document',['id'=>$id]);
								}
							}
						}
					}
					
					foreach ($_POST['docs'] as $key => $name) {
						$isExist = $this->model->getValue('document','name',['created_by'=>$_POST['created_by'],'name'=>$name]);
						if(empty($isExist)){
							$this->model->insertData('document',['created_by'=>$_POST['created_by'],'name'=>$name]);
						}
					}
					
					
					$response['message'] = 'Documents Updated';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

	//**************************************Track********************************************/
		public function Map_barcode(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
			
				$o_id=$this->input->post('o_id');
				$awb_no=$this->input->post('awb_no');
				$barcode_no=$this->input->post('barcode_no');
				$date = date('d/m/Y');
			
				if (empty($awb_no)) {
					$response['message'] = 'Awb No is required.';
					$response['code'] = 201;
				} else if (empty($barcode_no)) {
					$response['message'] = 'Barcode is required.';
					$response['code'] = 201;
				} 
				else if (empty($o_id)) {
					$response['message'] = 'Id is required.';
					$response['code'] = 201;
				}                     
				else
				{
					$order_data=$this->Adminapi_Model->get_scan_count($o_id,$awb_no);
					$status_name="Pickup Scan";
					$status_details=$this->Adminapi_Model->get_status_info($status_name);
					if($order_data['is_submit'])
					{
						$response['message']="Already Scanning completed";
						$response['is_submit']=true;
						$response['code'] = 201;
												
					}
					else
					{
							$data =array(
								'o_id'=>$o_id,
								'awb_no'=>$awb_no,
								'barcode_no'=>$barcode_no,
								'total_order'=>$order_data['total_order'],
								'scan_count'=>$order_data['scan_count']+1,
								'pickup_date'=>$date
							);
						
							$response1 = $this->Adminapi_Model->check_data($barcode_no);
							if($response1['status']==0)
							{
								$response = $this->Adminapi_Model->common_data_ins('map_barcode',$data);
								$order_data_latest=$this->Adminapi_Model->get_scan_count($o_id,$awb_no);
								$location=$this->Adminapi_Model->get_pickup_scan_location($awb_no);
							
								if($order_data_latest['is_submit']==true)
								{
									if($order_data_latest['total_order']==$order_data_latest['scan_count'])
									{
										if($order_data_latest['total_order']==1)
										{
											// $date = date('d/m/Y');
										$pickupscan_status=array(
													'fk_oid'=>$location['id'],
													'fk_userid'=>$location['fk_id'],
													'awb_no'=>$awb_no,
													'order_status'=>$status_details['id'],
													'status_description'=>"Pickup Scanning",
													'order_location'=>$location['pickup_city'],
													'expected_date'=>$date,
													'total_order'=>$order_data_latest['total_order'],
													'scan_count'=>$order_data_latest['scan_count']
										);  
										$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$pickupscan_status);
											$response['message1']="Scanning completed";
											$response['status']=true;
											$response['code'] = 200;
											$response['message']="success";
											$response['scanning_count']=$order_data_latest['scan_count'];
											$response['total_count']=$order_data_latest['total_order'];
											$response['is_submit']=$order_data_latest['is_submit'];
										
										}
										else
										{
											$update=array(
											'status_description'=>"Pickup Scanning Completed",
											'scan_count'=>$order_data_latest['scan_count']
											);
											$this->db->update('tbl_order_status',$update,array('fk_oid'=>$o_id));
											$response['message1']="Scanning completed";
												$response['status']=true;
												$response['code'] = 200;
												$response['message']="success";
												$response['scanning_count']=$order_data_latest['scan_count'];
												$response['total_count']=$order_data_latest['total_order'];
												$response['is_submit']=$order_data_latest['is_submit'];
										}
									}
								}
								else
								{
								
									$this->db->select('*');
									$this->db->from('tbl_order_status');
									$this->db->where('fk_oid',$o_id);
									$query=$this->db->get();
									if ($query->num_rows() > 0) 
									{
										$update=array(
											//'total_order'=>$order_data_latest['total_order'],
											'scan_count'=>$order_data_latest['scan_count']
										);
										$this->db->update('tbl_order_status',$update,array('fk_oid'=>$o_id));
									}
									else
									{
											$date = date('d/m/Y');
											$pickupscan_status=array(
													'fk_oid'=>$location['id'],
													'fk_userid'=>$location['fk_id'],
													'awb_no'=>$awb_no,
													'order_status'=>$status_details['id'],
													'status_description'=>"Pickup Scanning",
													'order_location'=>$location['pickup_city'],
													'expected_date'=>$date,
													'total_order'=>$order_data_latest['total_order'],
													'scan_count'=>$order_data_latest['scan_count']
										);  
										$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$pickupscan_status);
									}                                        
										$response['status']=true;
										$response['code'] = 200;
										$response['message']="success";
										$response['scanning_count']=$order_data_latest['scan_count'];
										$response['total_count']=$order_data_latest['total_order'];
										$response['is_submit']=$order_data_latest['is_submit'];
								}                          
							
							}
							else
							{
								$response['code'] = 201;
								$response['message']="Barcode Already exist";
							}
					
					}
				}
			}
			else
			{
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
			}
			echo json_encode($response);
		}
		// inscan
 		public function inscan(){
				$response = array('code' => -1, 'status' => false, 'message' => '');
				if ($_SERVER["REQUEST_METHOD"] == "POST")
				{
				
						$emp_id = $this->input->post('emp_id');
						$barcode_no = $this->input->post('barcode_no');
						$type=$this->input->post('type');
						$date = date('d/m/Y');
						
						if (empty($emp_id)) {
							$response['message'] = 'Employee Id is required.';
							$response['code'] = 201;
						} else if (empty($barcode_no)) {
							$response['message'] = 'Barcode is required.';
							$response['code'] = 201;
						}else if (empty($type)) {
							$response['message'] = 'Type is required.';
							$response['code'] = 201;
						}
						else
						{
							$emp_id = trim($emp_id);
							$barcode_no = trim($barcode_no);
							$type = trim($type);
							
							$status_name="Received In HUB";
							$status_details=$this->Adminapi_Model->get_status_info($status_name);  
							$result=$this->Adminapi_Model->get_awbno_by_barcode($barcode_no);
							if($result)
							{
									$awb_no = $result['awb_no'];
									$data=$this->Adminapi_Model->get_details_on_awb_no($awb_no);
									$employee=$this->Adminapi_Model->get_employee_details($emp_id);
									if($type=="Source")
									{
										$order_data=$this->Adminapi_Model->get_inscan_count($awb_no);                                    
									}
									else
									{
										$order_data=$this->Adminapi_Model->get_destination_count($awb_no);
									}
											$data1= array(
												'emp_id'=>$emp_id,
												'c_id'=>$data['c_id'],
												'barcode_no'=>$barcode_no,
												'awb_no'=>$result['awb_no'],
												'total_order'=>$order_data['total_order'],
												'scan_count'=>$order_data['scan_count']+1,
												'inscan_date'=>$date
											);
										
											$response1 = $this->Adminapi_Model->check_data1($barcode_no,$type);
											if($response1['status']==0)
											{
													if($type=='Source')
													{
															$response = $this->Adminapi_Model->common_data_ins('source_inscan',$data1);
															$order_data_latest=$this->Adminapi_Model->get_inscan_count($awb_no);
															if($order_data_latest['is_submit']==true)
															{
																if( $order_data_latest['total_order']==$order_data_latest['scan_count'])
																{
																		if($order_data_latest['total_order']==1)
																		{
																				
																					$inscan_status=array(
																							'fk_oid'=>$data['id'],
																							'fk_userid'=>$data['fk_id'],
																							'awb_no'=>$data['AWBno'],
																							'order_status'=>$status_details['id'],
																							'status_description'=>"Source Inscan Completed",
																							'order_location'=>$employee['work_area_location'],
																							'expected_date'=>$date,
																							'total_order'=>$order_data_latest['total_order'],
																							'scan_count'=>$order_data_latest['scan_count']

																				);  
																				
																				$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$inscan_status);
																					$response['message1']="Scanning completed";
																				$response['status']=true;
																				$response['code'] = 200;
																				$response['message']="success";
																				$response['scanning_count']=$order_data_latest['scan_count'];
																				$response['total_count']=$order_data_latest['total_order'];
																				$response['is_submit']=$order_data_latest['is_submit'];
																		}
																		else
																		{
																				$update=array(
																						'status_description'=>"Source Inscan Completed",
																						'scan_count'=>$order_data_latest['scan_count']
																				);
																				$this->db->update('tbl_order_status',$update,array('fk_oid'=>$data['id'],'order_status'=>$status_details['id']));   
																				$response['message1']="Scanning completed";
																				$response['status']=true;
																				$response['code'] = 200;
																				$response['message']="success";
																				$response['scanning_count']=$order_data_latest['scan_count'];
																				$response['total_count']=$order_data_latest['total_order'];
																				$response['is_submit']=$order_data_latest['is_submit'];
																		}
																}
															}
															else
															{
																$this->db->select('*');
																$this->db->from('tbl_order_status');
																$this->db->where('fk_oid',$data['id']);
																$this->db->where('order_status',$status_details['id']);
																$query=$this->db->get();
																if ($query->num_rows() > 0) 
																{
																	$update=array(
																			'status_description'=>"Source Inscan",
																			'scan_count'=>$order_data_latest['scan_count']
																	);
																	$this->db->update('tbl_order_status',$update,array('fk_oid'=>$data['id'],'order_status'=>$status_details['id']));
																}
																else
																{
																		$date = date('d/m/Y');
																		$inscan_status=array(
																				'fk_oid'=>$data['id'],
																				'fk_userid'=>$data['fk_id'],
																				'awb_no'=>$data['AWBno'],
																				'order_status'=>$status_details['id'],
																				'status_description'=>"Source Inscan",
																				'order_location'=>$employee['work_area_location'],
																				'expected_date'=>$date,
																				'total_order'=>$order_data_latest['total_order'],
																				'scan_count'=>$order_data_latest['scan_count']
																	);  
																	$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$inscan_status);
																	// $fk_order_status_id=$this->db->insert_id();
																}       
																$response['status']=true;
																$response['code'] = 200;
																$response['message']="success";
																$response['scanning_count']=$order_data_latest['scan_count'];
																$response['total_count']=$order_data_latest['total_order'];
																$response['is_submit']=$order_data_latest['is_submit'];
															}                                                                       
													}
													else if($type=='Destination')
													{
														$response = $this->Adminapi_Model->common_data_ins('destination_inscan',$data1);
														$order_data_latest1=$this->Adminapi_Model->get_destination_count($awb_no);
														$status_name="Shipment Received In Destination";
														$status_details=$this->Adminapi_Model->get_status_info($status_name);
															if($order_data_latest1['is_submit']==true)
															{
																if($order_data_latest1['total_order']==$order_data_latest1['scan_count'])
																{
																		if($order_data_latest1['total_order']==1)
																		{
																			
																					$date = date('d/m/Y');
																					$inscan_des_status=array(
																							'fk_oid'=>$data['id'],
																							'fk_userid'=>$data['fk_id'],
																							'awb_no'=>$data['AWBno'],
																							'order_status'=>$status_details['id'],
																							'status_description'=>"Destination InScan Completed",
																							'order_location'=>$employee['work_area_location'],
																							'expected_date'=>$date,
																							'total_order'=>$order_data_latest1['total_order'],
																							'scan_count'=>$order_data_latest1['scan_count']

																				);  
																				$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$inscan_des_status);
																					$response['message1']="Destination InScan completed";
																					$response['status']=true;
																					$response['code'] = 200;
																					$response['message']="success";
																					$response['scanning_count']=$order_data_latest1['scan_count'];
																					$response['total_count']=$order_data_latest1['total_order'];
																					$response['is_submit']=$order_data_latest1['is_submit'];
																		}
																		else
																		{
																				$update=array(
																						'status_description'=>"Destination InScan Completed",
																						'scan_count'=>$order_data_latest1['scan_count']
																				);
																				$this->db->update('tbl_order_status',$update,array('fk_oid'=>$data['id'],'order_status'=>$status_details['id']));
																					
																					$response['message1']="Destination InScan completed";
																					$response['status']=true;
																					$response['code'] = 200;
																					$response['message']="success";
																					$response['scanning_count']=$order_data_latest1['scan_count'];
																					$response['total_count']=$order_data_latest1['total_order'];
																					$response['is_submit']=$order_data_latest1['is_submit'];
																		}
																}                                                                            
															}
															else
															{
																$this->db->select('*');
																$this->db->from('tbl_order_status');
																$this->db->where('fk_oid',$data['id']);
																$this->db->where('order_status',$status_details['id']);
																$query=$this->db->get();
																if ($query->num_rows() > 0) 
																{
																	$update=array(
																			'status_description'=>"Destination InScan",
																			'scan_count'=>$order_data_latest1['scan_count']
																	);
																	$this->db->update('tbl_order_status',$update,array('fk_oid'=>$data['id'],'order_status'=>$status_details['id']));
																}
																else
																{
																		$date = date('d/m/Y');
																		$inscan_des_status=array(
																				'fk_oid'=>$data['id'],
																				'fk_userid'=>$data['fk_id'],
																				'awb_no'=>$data['AWBno'],
																				'order_status'=>$status_details['id'],
																				'status_description'=>"Destination InScan",
																				'order_location'=>$employee['work_area_location'],
																				'expected_date'=>$date,
																				'total_order'=>$order_data_latest1['total_order'],
																				'scan_count'=>$order_data_latest1['scan_count']
																	);  
																	$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$inscan_des_status);
																}       
																$response['status']=true;
																$response['code'] = 200;
																$response['message']="success";
																$response['scanning_count']=$order_data_latest1['scan_count'];
																$response['total_count']=$order_data_latest1['total_order'];
																$response['is_submit']=$order_data_latest1['is_submit'];
															}
													}
													else
													{
														$response['code'] = 201;
														$response['message']="Cannot Insert Data";
													}
											}
											else
											{
												$response['code'] = 201;
												$response['message']="Barcode Already exist";
											}
										}
										else
										{
											$response['code']=200;
											$response['message']="Barcode is Not Available";
											$response['status']=0;
										}
						} 
				}
				else 
				{
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
				echo json_encode($response);
		}
		// outscan
    	public function outscan(){
				$response = array('code' => -1, 'status' => false, 'message' => '');
				if ($_SERVER["REQUEST_METHOD"] == "POST") {
				
					$emp_id = $this->input->post('emp_id');
					$barcode_no = $this->input->post('barcode_no');
					$vehicle_no = $this->input->post('vehicle_no');
					$type=$this->input->post('type');
					$date = date('d/m/Y');
					if (empty($emp_id)) {
						$response['message'] = 'Employee Id is required.';
						$response['code'] = 201;
					} else if (empty($barcode_no)) {
						$response['message'] = 'Barcode is required.';
						$response['code'] = 201;
					}
					else {
						$emp_id = trim($emp_id);
						$barcode_no = trim($barcode_no);                
						$result=$this->Adminapi_Model->get_awbno_by_barcode_no($barcode_no);
						$data=$this->Adminapi_Model->get_details_on_vechile($vehicle_no);
						$status_name="Shipment Forwarded to Destination";
						$status_details=$this->Adminapi_Model->get_status_info($status_name);  
						
						if($result)
						{
							$awb_no = $result['awb_no'];
							// $data3=$this->Adminapi_Model->get_details_on_awb_no($awb_no);
							$city=$this->Adminapi_Model->get_city_by_awb_no($awb_no);  
							$employee=$this->Adminapi_Model->get_employee_details($emp_id);
							if($type=="Source")
							{
								$order_data=$this->Adminapi_Model->get_outscan_count($awb_no);
							}
							else
							{
								$order_data=$this->Adminapi_Model->get_destination_outscan_count($awb_no);
							}
							
							
											
							$data1= array(
								'emp_id'=>$emp_id,
								'barcode_no'=>$barcode_no,
								'awb_no'=>$result['awb_no'],
								'vehicle_id'=>$data['id'],
								'source_city'=>$city['pickup_city'],
								'city'=>$city['drop_city'],
								'date'=>$date,
								'total_order'=>$order_data['total_order'],
								'scan_count'=>$order_data['scan_count']+1
							);    
							$response1 = $this->Adminapi_Model->check_data2($barcode_no,$type);
							if($response1['status']==0)
							{      
								if($type=='Source')
								{         
									$response = $this->Adminapi_Model->common_data_ins('source_outscan',$data1);
									$this->db->update('source_inscan',array('status'=>'0'),array('id'=>$result['id']));
									$order_data_latest=$this->Adminapi_Model->get_outscan_count($awb_no);
									if($order_data_latest['is_submit']==true)
									{
										if( $order_data_latest['total_order']==$order_data_latest['scan_count'])
										{
											if($order_data_latest['total_order']==1)
											{
														$date = date('d/m/Y');
														$inscan_des_status=array(
																'fk_oid'=>$city['id'],
																'fk_userid'=>$city['fk_id'],
																'awb_no'=>$city['AWBno'],
																'order_status'=>$status_details['id'],
																'status_description'=>"Source Outscan Completed",
																'order_location'=>$employee['work_area_location'],
																'expected_date'=>$date,
																'total_order'=>$order_data_latest['total_order'],
																'scan_count'=>$order_data_latest['scan_count']
														);  
														$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$inscan_des_status);
														$response['message1']="Scanning completed";
														$response['status']=true;
														$response['code'] = 200;
														$response['message']="success";
														$response['scanning_count']=$order_data_latest['scan_count'];
														$response['total_count']=$order_data_latest['total_order'];
														$response['is_submit']=$order_data_latest['is_submit']; 
											}
											else
											{
												$update=array(
														'status_description'=>"Source Outscan Completed",
														'scan_count'=>$order_data_latest['scan_count']
													);
													$this->db->update('tbl_order_status',$update,array('fk_oid'=>$city['id'],'order_status'=>$status_details['id']));
														$response['message1']="Scanning completed";
														$response['status']=true;
														$response['code'] = 200;
														$response['message']="success";
														$response['scanning_count']=$order_data_latest['scan_count'];
														$response['total_count']=$order_data_latest['total_order'];
														$response['is_submit']=$order_data_latest['is_submit']; 
											}
										}
									}
									else
									{
											$this->db->select('*');
											$this->db->from('tbl_order_status');
											$this->db->where('fk_oid',$city['id']);
											$this->db->where('order_status',$status_details['id']);
											$query=$this->db->get();
											if ($query->num_rows() > 0) 
											{
												$update=array(
														'status_description'=>"Source Outscan",
														'scan_count'=>$order_data_latest['scan_count']
												);
												$this->db->update('tbl_order_status',$update,array('fk_oid'=>$city['id'],'order_status'=>$status_details['id']));
											}
											else
											{
												$date = date('d/m/Y');
												$inscan_des_status=array(
														'fk_oid'=>$city['id'],
														'fk_userid'=>$city['fk_id'],
														'awb_no'=>$city['AWBno'],
														'order_status'=>$status_details['id'],
														'status_description'=>"Source Outscan",
														'order_location'=>$employee['work_area_location'],
														'expected_date'=>$date,
														'total_order'=>$order_data_latest['total_order'],
														'scan_count'=>$order_data_latest['scan_count']
												);  
												$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$inscan_des_status);
											}       
											$response['status']=true;
											$response['code'] = 200;
											$response['message']="success";
											$response['scanning_count']=$order_data_latest['scan_count'];
											$response['total_count']=$order_data_latest['total_order'];
											$response['is_submit']=$order_data_latest['is_submit'];
									}
								}
								else if($type=='Destination')
								{
									$response = $this->Adminapi_Model->common_data_ins('destination_outscan',$data1);
									$this->db->update('destination_inscan',array('status'=>'0'),array('id'=>$result['id']));
									$order_data_latest1=$this->Adminapi_Model->get_destination_outscan_count($awb_no);
									$status_name="Shipment Out For Delivery";
									$status_details=$this->Adminapi_Model->get_status_info($status_name);
									if($order_data_latest1['is_submit']==true)
									{
										if( $order_data_latest1['total_order']==$order_data_latest1['scan_count'])
										{
											if($order_data_latest1['total_order']==1)
											{
													$date = date('d/m/Y');
													$outscan_status=array(
															'fk_oid'=>$city['id'],
															'fk_userid'=>$city['fk_id'],
															'awb_no'=>$city['AWBno'],
															'order_status'=>$status_details['id'],
															'status_description'=>"Destination Outscan Completed",
															'order_location'=>$employee['work_area_location'],
															'expected_date'=>$date,
															'total_order'=>$order_data_latest1['total_order'],
															'scan_count'=>$order_data_latest1['scan_count']
													);  
													$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$outscan_status);
														$response['message1']="Scanning completed";
														$response['status']=true;
														$response['code'] = 200;
														$response['message']="success";
														$response['scanning_count']=$order_data_latest1['scan_count'];
														$response['total_count']=$order_data_latest1['total_order'];
														$response['is_submit']=$order_data_latest1['is_submit'];      
											}
											else
											{
												$update=array(
														'status_description'=>"Destination Outscan Completed",
														'scan_count'=>$order_data_latest1['scan_count']
												);
												$this->db->update('tbl_order_status',$update,array('fk_oid'=>$city['id'],'order_status'=>$status_details['id']));       
												$response['message1']="Scanning completed";
												$response['status']=true;
												$response['code'] = 200;
												$response['message']="success";
												$response['scanning_count']=$order_data_latest1['scan_count'];
												$response['total_count']=$order_data_latest1['total_order'];
												$response['is_submit']=$order_data_latest1['is_submit'];      
												
											}
												
										}
									}
									else
									{
											$this->db->select('*');
											$this->db->from('tbl_order_status');
											$this->db->where('fk_oid',$city['id']);
											$this->db->where('order_status',$status_details['id']);
											$query=$this->db->get();
											if ($query->num_rows() > 0) 
											{
												$update=array(
														'status_description'=>"Destination Outscan",
														'scan_count'=>$order_data_latest1['scan_count']
												);
												$this->db->update('tbl_order_status',$update,array('fk_oid'=>$city['id'],'order_status'=>$status_details['id']));
											}
											else
											{
													$date = date('d/m/Y');
													$outscan_status=array(
															'fk_oid'=>$city['id'],
															'fk_userid'=>$city['fk_id'],
															'awb_no'=>$city['AWBno'],
															'order_status'=>$status_details['id'],
															'status_description'=>"Destination Outscan",
															'order_location'=>$employee['work_area_location'],
															'expected_date'=>$date,
															'total_order'=>$order_data_latest1['total_order'],
															'scan_count'=>$order_data_latest1['scan_count']
													);  
													$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$outscan_status);
											}
											$response['status']=true;
											$response['code'] = 200;
											$response['message']="success";
											$response['scanning_count']=$order_data_latest1['scan_count'];
											$response['total_count']=$order_data_latest1['total_order'];
											$response['is_submit']=$order_data_latest1['is_submit'];
									}
									
								}
								else
								{
									$response['code'] = 201;
									$response['message']="Cannot Insert Data";
								}
							}
							else
							{
								$response['code'] = 201;
								$response['message']="Barcode Already exist";
							}
						}
					else
					{
									$response['code']=200;
									$response['message']="Barcode is Not Available";
									$response['status']=0;
					}
					} 
				} else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
				echo json_encode($response);
		}

		function get_order_status_by_awbno(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$order_status = $this->model->getData('tbl_order_status',$_POST,$select);
						$response['order_status'] = $order_status;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				// } 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			echo json_encode($response);
		}

		function get_all_status_master(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$status_master = $this->model->getData('tbl_status_master',$_POST,$select);
					// echo"<pre>";
					// print_r($status_master);die;
					$response['status_master'] = $status_master;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		public function pod_upload()
		{
			$response = array('code' => -1, 'status' => false, 'message' => '');
			if ($_SERVER["REQUEST_METHOD"] == "POST")
			{
				$id  = $this->input->post('emp_id');
				$awb_no  = $this->input->post('awb_no');  
				
					$status_name="Delivered Successfully";
					$status_details=$this->Adminapi_Model->get_status_info($status_name); 
					$data1=$this->Adminapi_Model->get_details_on_awb_no($awb_no);
					$employee=$this->Adminapi_Model->get_employee_details($id);

				if (empty($id)) {
					$response['message'] = 'Id is required.';
					$response['code'] = 201;
				} else if(empty($awb_no)){
					$response['message'] = 'Awb No is required.';
					$response['code'] = 201;
				}else if(empty($_FILES['pod']['name'][0])){
					$response['message'] = 'POD image is required.';
					$response['code'] = 201;
				} 
				else
				{			
					$this->load->library('upload');
							$config['upload_path']          = 'uploads';
							$config['allowed_types']        = 'gif|jpg|png|jpeg';
							$config['max_size']             = 10000;
							$config['max_width']            = 1024;
							$config['max_height']           = 768;
							$config['overwrite']            = true;
							
							$this->upload->initialize($config);
							$this->load->library('upload', $config);
							$this->upload->do_upload('pod');
							$path=$this->upload->data('file_name');

							$data=array(
									'pod_upload'=>$path
							);
							$response = $this->Adminapi_Model->common_data_update('tbl_order_booking',$data,$awb_no,'AWBno');
							
								$date = date('d/m/Y');
								$inscan_status=array(
									'fk_oid'=>$data1['id'],
									'fk_userid'=>$data1['fk_id'],
									'awb_no'=>$awb_no,
									'order_status'=>$status_details['id'],
									'status_description'=>"Delivered Successfully",
									'order_location'=>$employee['work_area_location'],
									'expected_date'=>$date
								);  
								$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$inscan_status);

							$pickup_name = $data1['pickup_name'];
							$pickup_email = $data1['pickup_email'];
							$pickup_contact = $data1['pickup_contact'];
							$drop_name = $data1['drop_name'];
							$drop_email = $data1['drop_email'];
							$drop_contact = $data1['drop_contact'];
							
							// $email_txt=".$pickup_name.";
							// $txt="Your Product is Delivered Successfully Thank You";
							// $email_data = array(
							//          'email_txt'=>$email_txt,
							//          'txt'=>$txt
							//         );
							// $subject="Product Delivered";
							// $message= $this->load->view('Email-template',$email_data,true);
							// $this->send_email($pickup_email,$message,$subject);
							// $smstext = "Your Product is Delivered Successfully, Thank you Apexworld Logistics Pvt Ltd.";
							// $this->sendsms($pickup_contact,$smstext);

							// if($drop_email!='')
							// {
							//         $email_txt=".$pickup_name.";
							//         $txt="Your Product is Delivered Successfully Thank You";
							//         $email_data = array(
							//                  'email_txt'=>$email_txt,
							//                  'txt'=>$txt
							//                 );
							//         $subject="Product Delivered";
							//         $message= $this->load->view('Email-template',$email_data,true);
							//         $this->send_email($drop_email,$message,$subject);
							// }

							// if($drop_contact!='')
							// {
							//      $smstext = "Your Product is Delivered Successfully, Thank you Apexworld Logistics Pvt Ltd.";
							//     $this->sendsms($drop_contact,$smstext);
							// }                           
				}
				
			}
			else
			{
				$response['message'] = 'No direct script is allowed.';
				$response['code'] = 204;
			}
			echo json_encode($response);
		}


	//***************************************manual Tracking****************************************/ 
		function getNoBoxesByAWB(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$no_of_boxes = $this->model->getData('ship',$_POST,$select);
					$response['no_of_boxes'] = $no_of_boxes;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}
		
		function getBarcodeByAWB(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$barcodes = $this->model->getData('map_barcode',$_POST,$select);
					$response['barcodes'] = $barcodes;
					$response['message'] = 'success';
					$response['code'] = 200;
					$response['status'] = true;
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_status(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Driver id is required';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$staus_master = $this->model->getData('tbl_status_master',$_POST,$select);
						$response['staus_master'] = $staus_master;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		public function add_map_barcode(){
				$response = array('code' => -1, 'status' => false, 'message' => '');
				if ($_SERVER["REQUEST_METHOD"] == "POST") 
				{
						$awb_no['AWBno']=$this->input->post('awb_no');
						$o_id = $this->model->getData('ship',$awb_no,'id');
						$o_id = $o_id[0]['id'];
						$awb_no=$awb_no['AWBno'];
						$barcode_no = json_decode($_POST['barcode_no'],true);
						$emp_id = $this->input->post('emp_id');
						$date = date('d/m/Y');
						
						for($i=0; $i<count($barcode_no); $i++) {
							if (empty($awb_no)) {
								$response['message'] = 'Awb No is required.';
								$response['code'] = 201;
							} else if (empty($barcode_no[$i])) {
								$response['message'] = 'Barcode is required.';
								$response['code'] = 201;
							} 
							else if (empty($o_id)) {
								$response['message'] = 'Id is required.';
								$response['code'] = 201;
							}                     
							else
							{
									$order_data=$this->Adminapi_Model->get_scan_count($o_id,$awb_no);
									
									$status_name="Pickup Scan";
									$status_details=$this->Adminapi_Model->get_status_info($status_name);
									
									if($order_data['is_submit'])
									{
										$response['message']="Already Scanning completed";
										$response['is_submit']=true;
										$response['code'] = 201;
																
									}

									else
									{
											$data =array(
												'o_id'=>$o_id,
												'emp_id'=>$emp_id,
												'awb_no'=>$awb_no,
												'barcode_no'=>$barcode_no[$i],
												'total_order'=>$order_data['total_order'],
												'scan_count'=>$order_data['scan_count']+1,
												'pickup_date'=>$date
											);

											$response1 = $this->Adminapi_Model->check_data($barcode_no[$i]);
											if($response1['status']==0)
											{

												$response = $this->Adminapi_Model->common_data_ins('map_barcode',$data);
												// echo"<pre>";
												// print_r($status_details);die;
												$order_data_latest=$this->Adminapi_Model->get_scan_count($o_id,$awb_no);

												$location=$this->Adminapi_Model->get_pickup_scan_location($awb_no);

												if($order_data_latest['is_submit']==true)
												{
													if($order_data_latest['total_order']==$order_data_latest['scan_count'])
													{
														if($order_data_latest['total_order']==1)
														{
															// $date = date('d/m/Y');
														$pickupscan_status=array(
																	'fk_oid'=>$location['id'],
																	'fk_userid'=>$location['shipper_id'],
																	'awb_no'=>$awb_no,
																	'order_status'=>$status_details['id'],
																	'status_description'=>"Pickup Scanning",
																	'order_location'=>$location['pickup_city'],
																	'expected_date'=>$date,
																	'total_order'=>$order_data_latest['total_order'],
																	'scan_count'=>$order_data_latest['scan_count']
														);  
														$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$pickupscan_status);
															$response['message1']="Scanning completed";
															$response['status']=true;
															$response['code'] = 200;
															$response['message']="success";
															$response['scanning_count']=$order_data_latest['scan_count'];
															$response['total_count']=$order_data_latest['total_order'];
															$response['is_submit']=$order_data_latest['is_submit'];
														
														}
														else
														{
															$update=array(
															'status_description'=>"Pickup Scanning Completed",
															'scan_count'=>$order_data_latest['scan_count']
															);
															$this->db->update('tbl_order_status',$update,array('fk_oid'=>$o_id));
															$response['message1']="Scanning completed";
																$response['status']=true;
																$response['code'] = 200;
																$response['message']="success";
																$response['scanning_count']=$order_data_latest['scan_count'];
																$response['total_count']=$order_data_latest['total_order'];
																$response['is_submit']=$order_data_latest['is_submit'];
														}
													}
												}
												else
												{
												
													$this->db->select('*');
													$this->db->from('tbl_order_status');
													$this->db->where('fk_oid',$o_id);
													$query=$this->db->get();
													if ($query->num_rows() > 0) 
													{
														$update=array(
															//'total_order'=>$order_data_latest['total_order'],
															'scan_count'=>$order_data_latest['scan_count']
														);
														$this->db->update('tbl_order_status',$update,array('fk_oid'=>$o_id));
													}
													else
													{
															$date = date('d/m/Y');
															$pickupscan_status=array(
																	'fk_oid'=>$location['id'],
																	'fk_userid'=>$location['shipper_id'],
																	'awb_no'=>$awb_no,
																	'order_status'=>$status_details['id'],
																	'status_description'=>"Pickup Scanning",
																	'order_location'=>$location['pickup_city'],
																	'expected_date'=>$date,
																	'total_order'=>$order_data_latest['total_order'],
																	'scan_count'=>$order_data_latest['scan_count']
														);  
														$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$pickupscan_status);
													}                                        
														$response['status']=true;
														$response['code'] = 200;
														$response['message']="success";
														$response['scanning_count']=$order_data_latest['scan_count'];
														$response['total_count']=$order_data_latest['total_order'];
														$response['is_submit']=$order_data_latest['is_submit'];
												} 

											}
											else
											{
												$response['code'] = 201;
												$response['message']="Barcode Already exist";
											}	
											
									}
								// }
							}
						}
				}
				else
				{
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
				echo json_encode($response);
		}

		public function inscan_manualy(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			if ($_SERVER["REQUEST_METHOD"] == "POST")
			{
			
				$emp_id = $this->input->post('emp_id');
				$barcode_no = json_decode($_POST['barcode_no'],true);
				$type=$this->input->post('type');
				$date = date('d/m/Y');
				
				for($i=0; $i<count($barcode_no); $i++) {
					if (empty($emp_id)) {
						$response['message'] = 'Employee Id is required.';
						$response['code'] = 201;
					} else if (empty($barcode_no[$i])) {
						$response['message'] = 'Barcode is required.';
						$response['code'] = 201;
					}else if (empty($type)) {
						$response['message'] = 'Type is required.';
						$response['code'] = 201;
					}
					else
					{
						$emp_id = trim($emp_id);
						// $barcode_no = trim($barcode_no[$i]);
						$type = trim($type);
						
						$status_name="Received In HUB";
						$status_details=$this->Adminapi_Model->get_status_info($status_name);  
						$result=$this->Adminapi_Model->get_awbno_by_barcode($barcode_no[$i]);
						
						if($result)
						{
								$awb_no = $result['awb_no'];
								$data=$this->Adminapi_Model->get_details_on_awb_no($awb_no);
								
								$employee=$this->Adminapi_Model->get_employee_details($emp_id);
								
								if($type=="Source")
								{
									$order_data=$this->Adminapi_Model->get_inscan_count($awb_no);
																
								}
								else
								{
									$order_data=$this->Adminapi_Model->get_destination_count($awb_no);
								}
								// echo"<pre>";
								// print_r($order_data);die;
									
										$data1= array(
											'emp_id'=>$emp_id,
											'c_id'=>$data['company_id'],
											'barcode_no'=>$barcode_no[$i],
											'awb_no'=>$result['awb_no'],
											'total_order'=>$order_data['no_of_boxes'],
											'scan_count'=>$order_data['scan_count']+1,
											'inscan_date'=>$date
										);
									
										$response1 = $this->Adminapi_Model->check_data1($barcode_no[$i],$type);
										if($response1['status']==0)
										{
												if($type=='Source')
												{
														$response = $this->Adminapi_Model->common_data_ins('source_inscan',$data1);
														
														$order_data_latest=$this->Adminapi_Model->get_inscan_count($awb_no);
														// echo"<pre>";
														// print_r($order_data_latest);die;
														if($order_data_latest['is_submit']==true)
														{
															if( $order_data_latest['no_of_boxes']==$order_data_latest['scan_count'])
															{
																	if($order_data_latest['no_of_boxes']==1)
																	{
																			
																				$inscan_status=array(
																						'fk_oid'=>$data['id'],
																						'fk_userid'=>$data['shipper_id'],
																						'awb_no'=>$data['AWBno'],
																						'order_status'=>$status_details['id'],
																						'status_description'=>"Source Inscan Completed",
																						'order_location'=>$employee['work_area_location'],
																						'expected_date'=>$date,
																						'total_order'=>$order_data_latest['no_of_boxes'],
																						'scan_count'=>$order_data_latest['scan_count']

																			);  
																			
																			$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$inscan_status);
																				$response['message1']="Scanning completed";
																			$response['status']=true;
																			$response['code'] = 200;
																			$response['message']="success";
																			$response['scanning_count']=$order_data_latest['scan_count'];
																			$response['total_count']=$order_data_latest['no_of_boxes'];
																			$response['is_submit']=$order_data_latest['is_submit'];
																	}
																	else
																	{
																			$update=array(
																					'status_description'=>"Source Inscan Completed",
																					'scan_count'=>$order_data_latest['scan_count']
																			);
																			$this->db->update('tbl_order_status',$update,array('fk_oid'=>$data['id'],'order_status'=>$status_details['id']));   
																			$response['message1']="Scanning completed";
																			$response['status']=true;
																			$response['code'] = 200;
																			$response['message']="success";
																			$response['scanning_count']=$order_data_latest['scan_count'];
																			$response['total_count']=$order_data_latest['no_of_boxes'];
																			$response['is_submit']=$order_data_latest['is_submit'];
																	}
															}
														}
														else
														{
															$this->db->select('*');
															$this->db->from('tbl_order_status');
															$this->db->where('fk_oid',$data['id']);
															$this->db->where('order_status',$status_details['id']);
															$query=$this->db->get();
															if ($query->num_rows() > 0) 
															{
																$update=array(
																		'status_description'=>"Source Inscan",
																		'scan_count'=>$order_data_latest['scan_count']
																);
																$this->db->update('tbl_order_status',$update,array('fk_oid'=>$data['id'],'order_status'=>$status_details['id']));
															}
															else
															{
																	$date = date('d/m/Y');
																	$inscan_status=array(
																			'fk_oid'=>$data['id'],
																			'fk_userid'=>$data['shipper_id'],
																			'awb_no'=>$data['AWBno'],
																			'order_status'=>$status_details['id'],
																			'status_description'=>"Source Inscan",
																			'order_location'=>$employee['work_area_location'],
																			'expected_date'=>$date,
																			'total_order'=>$order_data_latest['no_of_boxes'],
																			'scan_count'=>$order_data_latest['scan_count']
																);  
																$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$inscan_status);
																// $fk_order_status_id=$this->db->insert_id();
															}       
															$response['status']=true;
															$response['code'] = 200;
															$response['message']="success";
															$response['scanning_count']=$order_data_latest['scan_count'];
															$response['total_count']=$order_data_latest['no_of_boxes'];
															$response['is_submit']=$order_data_latest['is_submit'];
														}                                                                       
												}
												else if($type=='Destination')
												{
													$response = $this->Adminapi_Model->common_data_ins('destination_inscan',$data1);
													$order_data_latest1=$this->Adminapi_Model->get_destination_count($awb_no);
													$status_name="Shipment Received In Destination";
													$status_details=$this->Adminapi_Model->get_status_info($status_name);
														if($order_data_latest1['is_submit']==true)
														{
															if($order_data_latest1['no_of_boxes']==$order_data_latest1['scan_count'])
															{
																	if($order_data_latest1['no_of_boxes']==1)
																	{
																		
																				$date = date('d/m/Y');
																				$inscan_des_status=array(
																						'fk_oid'=>$data['id'],
																						'fk_userid'=>$data['shipper_id'],
																						'awb_no'=>$data['AWBno'],
																						'order_status'=>$status_details['id'],
																						'status_description'=>"Destination InScan Completed",
																						'order_location'=>$employee['work_area_location'],
																						'expected_date'=>$date,
																						'total_order'=>$order_data_latest1['no_of_boxes'],
																						'scan_count'=>$order_data_latest1['scan_count']

																			);  
																			$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$inscan_des_status);
																				$response['message1']="Destination InScan completed";
																				$response['status']=true;
																				$response['code'] = 200;
																				$response['message']="success";
																				$response['scanning_count']=$order_data_latest1['scan_count'];
																				$response['total_count']=$order_data_latest1['no_of_boxes'];
																				$response['is_submit']=$order_data_latest1['is_submit'];
																	}
																	else
																	{
																			$update=array(
																					'status_description'=>"Destination InScan Completed",
																					'scan_count'=>$order_data_latest1['scan_count']
																			);
																			$this->db->update('tbl_order_status',$update,array('fk_oid'=>$data['id'],'order_status'=>$status_details['id']));
																				
																				$response['message1']="Destination InScan completed";
																				$response['status']=true;
																				$response['code'] = 200;
																				$response['message']="success";
																				$response['scanning_count']=$order_data_latest1['scan_count'];
																				$response['total_count']=$order_data_latest1['no_of_boxes'];
																				$response['is_submit']=$order_data_latest1['is_submit'];
																	}
															}                                                                            
														}
														else
														{
															$this->db->select('*');
															$this->db->from('tbl_order_status');
															$this->db->where('fk_oid',$data['id']);
															$this->db->where('order_status',$status_details['id']);
															$query=$this->db->get();
															if ($query->num_rows() > 0) 
															{
																$update=array(
																		'status_description'=>"Destination InScan",
																		'scan_count'=>$order_data_latest1['scan_count']
																);
																$this->db->update('tbl_order_status',$update,array('fk_oid'=>$data['id'],'order_status'=>$status_details['id']));
															}
															else
															{
																	$date = date('d/m/Y');
																	$inscan_des_status=array(
																			'fk_oid'=>$data['id'],
																			'fk_userid'=>$data['shipper_id'],
																			'awb_no'=>$data['AWBno'],
																			'order_status'=>$status_details['id'],
																			'status_description'=>"Destination InScan",
																			'order_location'=>$employee['work_area_location'],
																			'expected_date'=>$date,
																			'total_order'=>$order_data_latest1['no_of_boxes'],
																			'scan_count'=>$order_data_latest1['scan_count']
																);  
																$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$inscan_des_status);
															}       
															$response['status']=true;
															$response['code'] = 200;
															$response['message']="success";
															$response['scanning_count']=$order_data_latest1['scan_count'];
															$response['total_count']=$order_data_latest1['no_of_boxes'];
															$response['is_submit']=$order_data_latest1['is_submit'];
														}
												}
												else
												{
													$response['code'] = 201;
													$response['message']="Cannot Insert Data";
												}
										}
										else
										{
											$response['code'] = 201;
											$response['message']="Barcode Already exist";
										}
									}
									else
									{
										$response['code']=200;
										$response['message']="Barcode is Not Available";
										$response['status']=0;
									}
					} 
				}	
			}
			else 
			{
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
			}
			echo json_encode($response);
		}

		public function outscan_manualy(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
			
					$emp_id = $this->input->post('emp_id');
					$barcode_no = json_decode($_POST['barcode_no'],true);
					$vehicle_no = $this->input->post('vehicle_id');
					
					$type=$this->input->post('type');
					$date = date('d/m/Y');
					for($i=0; $i<count($barcode_no); $i++) {
					if (empty($emp_id)) {
						$response['message'] = 'Employee Id is required.';
						$response['code'] = 201;
					} else if (empty($barcode_no)) {
						$response['message'] = 'Barcode is required.';
						$response['code'] = 201;
					}
					else 
					{
						$emp_id = trim($emp_id);
						// $barcode_no = trim($barcode_no);                
						$result=$this->Adminapi_Model->get_awbno_by_barcode_no($barcode_no[$i]);
						
						$data=$this->Adminapi_Model->get_details_on_vechile($vehicle_no);
						// echo"<pre>";
						// print_r($data);die;	
						$status_name="Shipment Forwarded to Destination";
						$status_details=$this->Adminapi_Model->get_status_info($status_name);  
						
						
						if($result)
						{
							$awb_no = $result['awb_no'];
							// $data3=$this->Adminapi_Model->get_details_on_awb_no($awb_no);
							$city=$this->Adminapi_Model->get_city_by_awb_no($awb_no);  
							$employee=$this->Adminapi_Model->get_employee_details($emp_id);
							if($type=="Source")
							{
								$order_data=$this->Adminapi_Model->get_outscan_count($awb_no);
							}
							else
							{
								$order_data=$this->Adminapi_Model->get_destination_outscan_count($awb_no);
							}
							// echo"<pre>";
							// print_r($order_data);die;			
							$data1= array(
								'emp_id'=>$emp_id,
								'barcode_no'=>$barcode_no[$i],
								'awb_no'=>$result['awb_no'],
								'vehicle_id'=>$data['id'],
								'source_city'=>$city['pickup_city'],
								'city'=>$city['drop_city'],
								'date'=>$date,
								'total_order'=>$order_data['no_of_boxes'],
								'scan_count'=>$order_data['scan_count']+1
							); 
								   
							$response1 = $this->Adminapi_Model->check_data2($barcode_no[$i],$type);
							if($response1['status']==0)
							{      
								if($type=='Source')
								{         
									$response = $this->Adminapi_Model->common_data_ins('source_outscan',$data1);
									$this->db->update('source_inscan',array('status'=>'0'),array('id'=>$result['id']));
									$order_data_latest=$this->Adminapi_Model->get_outscan_count($awb_no);
									// echo"<pre>";
									// print_r($order_data_latest);die;
									if($order_data_latest['is_submit']==true)
									{
										if( $order_data_latest['no_of_boxes']==$order_data_latest['scan_count'])
										{
											if($order_data_latest['no_of_boxes']==1)
											{
														$date = date('d/m/Y');
														$inscan_des_status=array(
																'fk_oid'=>$city['id'],
																'fk_userid'=>$city['shipper_id'],
																'awb_no'=>$city['AWBno'],
																'order_status'=>$status_details['id'],
																'status_description'=>"Source Outscan Completed",
																'order_location'=>$employee['work_area_location'],
																'expected_date'=>$date,
																'total_order'=>$order_data_latest['no_of_boxes'],
																'scan_count'=>$order_data_latest['scan_count']
														);  
														$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$inscan_des_status);
														$response['message1']="Scanning completed";
														$response['status']=true;
														$response['code'] = 200;
														$response['message']="success";
														$response['scanning_count']=$order_data_latest['scan_count'];
														$response['total_count']=$order_data_latest['no_of_boxes'];
														$response['is_submit']=$order_data_latest['is_submit']; 
											}
											else
											{
												$update=array(
														'status_description'=>"Source Outscan Completed",
														'scan_count'=>$order_data_latest['scan_count']
													);
													$this->db->update('tbl_order_status',$update,array('fk_oid'=>$city['id'],'order_status'=>$status_details['id']));
														$response['message1']="Scanning completed";
														$response['status']=true;
														$response['code'] = 200;
														$response['message']="success";
														$response['scanning_count']=$order_data_latest['scan_count'];
														$response['total_count']=$order_data_latest['no_of_boxes'];
														$response['is_submit']=$order_data_latest['is_submit']; 
											}
										}
									}
									else
									{
											$this->db->select('*');
											$this->db->from('tbl_order_status');
											$this->db->where('fk_oid',$city['id']);
											$this->db->where('order_status',$status_details['id']);
											$query=$this->db->get();
											if ($query->num_rows() > 0) 
											{
												$update=array(
														'status_description'=>"Source Outscan",
														'scan_count'=>$order_data_latest['scan_count']
												);
												$this->db->update('tbl_order_status',$update,array('fk_oid'=>$city['id'],'order_status'=>$status_details['id']));
											}
											else
											{
												$date = date('d/m/Y');
												$inscan_des_status=array(
														'fk_oid'=>$city['id'],
														'fk_userid'=>$city['shipper_id'],
														'awb_no'=>$city['AWBno'],
														'order_status'=>$status_details['id'],
														'status_description'=>"Source Outscan",
														'order_location'=>$employee['work_area_location'],
														'expected_date'=>$date,
														'total_order'=>$order_data_latest['no_of_boxes'],
														'scan_count'=>$order_data_latest['scan_count']
												);  
												$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$inscan_des_status);
											}       
											$response['status']=true;
											$response['code'] = 200;
											$response['message']="success";
											$response['scanning_count']=$order_data_latest['scan_count'];
											$response['total_count']=$order_data_latest['no_of_boxes'];
											$response['is_submit']=$order_data_latest['is_submit'];
									}
								}
								else if($type=='Destination')
								{
									$response = $this->Adminapi_Model->common_data_ins('destination_outscan',$data1);
									$this->db->update('destination_inscan',array('status'=>'0'),array('id'=>$result['id']));
									$order_data_latest1=$this->Adminapi_Model->get_destination_outscan_count($awb_no);
									// echo"<pre>";
									// print_r($order_data_latest1);die;
									$status_name="Shipment Out For Delivery";
									$status_details=$this->Adminapi_Model->get_status_info($status_name);
									if($order_data_latest1['is_submit']==true)
									{
										if( $order_data_latest1['no_of_boxes']==$order_data_latest1['scan_count'])
										{
											if($order_data_latest1['no_of_boxes']==1)
											{
													$date = date('d/m/Y');
													$outscan_status=array(
															'fk_oid'=>$city['id'],
															'fk_userid'=>$city['shipper_id'],
															'awb_no'=>$city['AWBno'],
															'order_status'=>$status_details['id'],
															'status_description'=>"Destination Outscan Completed",
															'order_location'=>$employee['work_area_location'],
															'expected_date'=>$date,
															'total_order'=>$order_data_latest1['no_of_boxes'],
															'scan_count'=>$order_data_latest1['scan_count']
													);  
													$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$outscan_status);
														$response['message1']="Scanning completed";
														$response['status']=true;
														$response['code'] = 200;
														$response['message']="success";
														$response['scanning_count']=$order_data_latest1['scan_count'];
														$response['total_count']=$order_data_latest1['no_of_boxes'];
														$response['is_submit']=$order_data_latest1['is_submit'];      
											}
											else
											{
												$update=array(
														'status_description'=>"Destination Outscan Completed",
														'scan_count'=>$order_data_latest1['scan_count']
												);
												$this->db->update('tbl_order_status',$update,array('fk_oid'=>$city['id'],'order_status'=>$status_details['id']));       
												$response['message1']="Scanning completed";
												$response['status']=true;
												$response['code'] = 200;
												$response['message']="success";
												$response['scanning_count']=$order_data_latest1['scan_count'];
												$response['total_count']=$order_data_latest1['no_of_boxes'];
												$response['is_submit']=$order_data_latest1['is_submit'];      
												
											}
												
										}
									}
									else
									{
											$this->db->select('*');
											$this->db->from('tbl_order_status');
											$this->db->where('fk_oid',$city['id']);
											$this->db->where('order_status',$status_details['id']);
											$query=$this->db->get();
											if ($query->num_rows() > 0) 
											{
												$update=array(
														'status_description'=>"Destination Outscan",
														'scan_count'=>$order_data_latest1['scan_count']
												);
												$this->db->update('tbl_order_status',$update,array('fk_oid'=>$city['id'],'order_status'=>$status_details['id']));
											}
											else
											{
													$date = date('d/m/Y');
													$outscan_status=array(
															'fk_oid'=>$city['id'],
															'fk_userid'=>$city['shipper_id'],
															'awb_no'=>$city['AWBno'],
															'order_status'=>$status_details['id'],
															'status_description'=>"Destination Outscan",
															'order_location'=>$employee['work_area_location'],
															'expected_date'=>$date,
															'total_order'=>$order_data_latest1['no_of_boxes'],
															'scan_count'=>$order_data_latest1['scan_count']
													);  
													$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$outscan_status);
											}
											$response['status']=true;
											$response['code'] = 200;
											$response['message']="success";
											$response['scanning_count']=$order_data_latest1['scan_count'];
											$response['total_count']=$order_data_latest1['no_of_boxes'];
											$response['is_submit']=$order_data_latest1['is_submit'];
									}
									
								}
								else
								{
									$response['code'] = 201;
									$response['message']="Cannot Insert Data";
								}
							}
							else
							{
								$response['code'] = 201;
								$response['message']="Barcode Already exist";
							}
						}
						else
						{
							$response['code']=200;
							$response['message']="Barcode is Not Available";
							$response['status']=0;
						}
					}
				} 
			} 
			else 
			{
				$response['message'] = 'Invalid Request';
				$response['code'] = 204;
			}
			echo json_encode($response);
		}

		public function pod_upload_manualy()
		{
			$response = array('code' => -1, 'status' => false, 'message' => '');
			if ($_SERVER["REQUEST_METHOD"] == "POST")
			{
				$id  = $this->input->post('emp_id');
				$awb_no  = $this->input->post('awb_no'); 
				$pod  = $this->input->post('pod');  
				
					$status_name="Delivered Successfully";
					$status_details=$this->Adminapi_Model->get_status_info($status_name); 
					$data1=$this->Adminapi_Model->get_details_on_awb_no($awb_no);
					// echo"<pre>";
					// print_r($data1);die;
					$employee=$this->Adminapi_Model->get_employee_details($id);

				if (empty($id)) {
					$response['message'] = 'Id is required.';
					$response['code'] = 201;
				} else if(empty($awb_no)){
					$response['message'] = 'Awb No is required.';
					$response['code'] = 201;
				}else if(empty($pod)){
					$response['message'] = 'POD image is required.';
					$response['code'] = 201;
				} 
				else
				{			
							// $this->load->library('upload');
							// $config['upload_path']          = 'uploads';
							// $config['allowed_types']        = 'gif|jpg|png|jpeg';
							// $config['max_size']             = 10000;
							// $config['max_width']            = 1024;
							// $config['max_height']           = 768;
							// $config['overwrite']            = true;
							
							// $this->upload->initialize($config);
							// $this->load->library('upload', $config);
							// $this->upload->do_upload('pod');
							// $path=$this->upload->data('file_name');

							// $data=array(
							// 		'pod_upload'=>$path
							// );
							$response = $this->Adminapi_Model->common_data_update('ship',['pod_upload'=>$pod],$awb_no,'AWBno');
							
								$date = date('d/m/Y');
								$inscan_status=array(
									'fk_oid'=>$data1['id'],
									'fk_userid'=>$data1['shipper_id'],
									'awb_no'=>$awb_no,
									'order_status'=>$status_details['id'],
									'status_description'=>"Delivered Successfully",
									'order_location'=>$employee['work_area_location'],
									'expected_date'=>$date
								);  
								$response = $this->Adminapi_Model->common_data_ins('tbl_order_status',$inscan_status);

							// $pickup_name = $data1['pickup_name'];
							// $pickup_email = $data1['pickup_email'];
							// $pickup_contact = $data1['pickup_contact'];
							// $drop_name = $data1['drop_name'];
							// $drop_email = $data1['drop_email'];
							// $drop_contact = $data1['drop_contact'];
							
							// $email_txt=".$pickup_name.";
							// $txt="Your Product is Delivered Successfully Thank You";
							// $email_data = array(
							//          'email_txt'=>$email_txt,
							//          'txt'=>$txt
							//         );
							// $subject="Product Delivered";
							// $message= $this->load->view('Email-template',$email_data,true);
							// $this->send_email($pickup_email,$message,$subject);
							// $smstext = "Your Product is Delivered Successfully, Thank you Apexworld Logistics Pvt Ltd.";
							// $this->sendsms($pickup_contact,$smstext);

							// if($drop_email!='')
							// {
							//         $email_txt=".$pickup_name.";
							//         $txt="Your Product is Delivered Successfully Thank You";
							//         $email_data = array(
							//                  'email_txt'=>$email_txt,
							//                  'txt'=>$txt
							//                 );
							//         $subject="Product Delivered";
							//         $message= $this->load->view('Email-template',$email_data,true);
							//         $this->send_email($drop_email,$message,$subject);
							// }

							// if($drop_contact!='')
							// {
							//      $smstext = "Your Product is Delivered Successfully, Thank you Apexworld Logistics Pvt Ltd.";
							//     $this->sendsms($drop_contact,$smstext);
							// }                           
				}
				
			}
			else
			{
				$response['message'] = 'No direct script is allowed.';
				$response['code'] = 204;
			}
			echo json_encode($response);
		}
	//***************************************Manifest Report****************************************/ 
		function get_all_manifest(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					// if (empty($_POST['id'])){
					// 	$response['message'] = 'Vehicle id is required';
					// 	$response['code'] = 201;
					// }
					// else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$manifest_report = $this->model->getData('tbl_manifest_reports',$_POST,$select);
						if(!empty($manifest_report))
						{
							foreach ($manifest_report as $key => $value) {

								$manifest_report[$key]['vehicle_name']=$this->model->getValue('vehicle','name',['id'=>$value['vechile_id']]);
								
							}
						}
						$response['manifest_report'] = $manifest_report;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					// }
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function get_manifest_details(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// echo"<pre>";
			// print_r($_POST);die;
			$id = $_POST['id'];
			$city = $_POST['city'];
			$vehicle = $_POST['vehicle'];
			$date_from = $_POST['date_from'];
			$date_to = $_POST['date_to'];
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Driver id is required';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$manifest = $this->model->get_manifest_details($city, $vehicle, $date_from, $date_to, $id);
						if(!empty($manifest))
						{
							foreach ($manifest as $key => $value) {

								$manifest[$key]['pickup_city']=$this->model->getValue('customer_contacts','city',['id'=>$value['shipper_contact']]);
								$manifest[$key]['drop_city']=$this->model->getValue('customer_contacts','city',['id'=>$value['shipper_contact']]);
								
							}
						}
						

						$response['manifest'] = $manifest;
						$response['message'] = 'success';
						$response['code'] = 200;
						$response['status'] = true;
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}

		function add_manifest_report(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// echo"<pre>";
			// print_r($_POST);die;
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['city'])){
						$response['message'] = 'Please fill required fields';
						$response['code'] = 201;
					}
					else{
						$isExist = $this->model->isExist('tbl_manifest_reports','manifest_no',$_POST['manifest_no']);
						// $isExist2 = $this->model->isExist('login','email',$_POST['prsn_email']);
						if(!$isExist ){
							$manifest_id = $this->model->insertData('tbl_manifest_reports',$_POST);
							// echo"hi";die;
							$response['message'] = 'success';
							$response['code'] = 200;
							$response['status'] = true;
						}
						else{
							$response['message'] = 'Manifest Number is already exist';
							$response['code'] = 201;
						}
					}
				} 
				else {
					$response['message'] = 'Invalid Request';
					$response['code'] = 204;
				}
			// }
			// else{
			// 	$response['message'] = 'Authentication required';
			// 	$response['code'] = 203;
			// } 
			echo json_encode($response);
		}
	}