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
			$response['message'] = 'success';
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
						$response['message'] = 'Please fill required fields';
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
							// sendEmail('nerkar.piyush16@gmail.com',$login_data['email'],'otp','otp is'.$otp);
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
						$response['message'] = 'Please fill required fields';
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
							$response['message'] = 'Otp is incorrect';
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
						$response['message'] = 'Confirm password mismatch';
						$response['code'] = 201;
					}
					else{
						$isExist = $this->model->getData('login',['email'=>$_POST['email'],'password'=>$_POST['old_password']]);
						if(!empty($isExist)){
							$this->model->updateData('login',['password'=>encyrpt_password($_POST['new_password'])],['email'=>$_POST['email']]);
							$response['message'] = 'success';
							$response['code'] = 200;
							$response['status'] = true;
						}
						else{
							$response['message'] = 'Email or password is incorrect';
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
						$pro_select_box .= '<option value="">Select State</option>';
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
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					if(!empty($_POST['select']) && isset($_POST['select'])){
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$cities = $this->model->getData('cities',$_POST,$select);
					$response['cities'] = $cities;
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

		function get_cities_by_state(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = 'id,city';
					if(!empty($_POST['select']) && isset($_POST['select'])){
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					$cities = $this->model->getData('cities',['state_id'=>$_POST['state_id']],$select);
					
					if(count($cities)>0)
					{
						$pro_select_box = '';
						$pro_select_box .= '<option value="">Select City</option>';
						foreach ($cities as $cities) {
							$pro_select_box .='<option value="'.$cities['id'].'">'.$cities['city'].'</option>';
						}
					}

					$response['cities'] = $pro_select_box;
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
						$response['message'] = 'Admin id is required';
						$response['code'] = 201;
					}
					else if (empty($_POST['name']) && empty($_POST['email']) && empty($_POST['contact'])){
						$response['message'] = 'Please fill required fields';
						$response['code'] = 201;
					}
					else{

						$isExist = $this->model->isExist('company','email',$_POST['email']);
						$isExist2 = $this->model->isExist('company','prsn_email',$_POST['prsn_email']);
						$isExist3 = $this->model->isExist('login','phone',$_POST['prsn_contact']);
						$isExist4 = $this->model->isExist('login','email',$_POST['prsn_email']);
						if($isExist || $isExist2){
							$response['message'] = 'Company email or contact person email is already exist';
							$response['code'] = 201;
						}
						else if($isExist3 || $isExist4){
							$response['message'] = 'Contact person email or phone is already exist';
							$response['code'] = 201;
						}
						else{
							$company_id = $this->model->insertData('company',$_POST);
							if(!empty($company_id)){
								
								$login = [];
								$login['fk_id'] = $company_id;
								$login['username'] = $_POST['name'];
								$login['phone'] = $_POST['contact'];
								$login['email'] = $_POST['email'];
								$login['usertype'] = 'company';
								$login['status'] = 1;
								$login['created_by'] = $_POST['created_by'];
								$this->model->insertData('login',$login);
							}
							$response['message'] = 'success';
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
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					// $id = $this->model->getValue('login','id',['id'=>$_POST['created_by']]);
					// if (empty($id)) {
					// 	$response['message'] = 'Admin id is required';
					// 	$response['code'] = 201;
					// }
					// if{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])){
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$company = $this->model->getData('company',$_POST,$select);
						if(!empty($company))
					{
						foreach ($company as $key => $value) {

							$company[$key]['country_name']=$this->model->getValue('countries','name',['id'=>$value['country_id']]);
							$company[$key]['city_name']=$this->model->getValue('cities','city',['id'=>$value['city_id']]);
							$company[$key]['state_name']=$this->model->getValue('states','name',['id'=>$value['state_id']]);
						}
					}

						$response['next_id'] = $this->model->generate_next_id('company','autoid','com','3');
						$response['companies'] = $company;
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
						$response['message'] = 'Company id is required';
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
					$id = $this->model->getValue('login','id',['id'=>$_POST['created_by']]);
					if (empty($id)) {
						$response['message'] = 'Admin id is required';
						$response['code'] = 201;
					}
					else if (empty($_POST['id'])){
						$response['message'] = 'Company id is required';
						$response['code'] = 201;
					}
					else if (empty($_POST['name'])){
						$response['message'] = 'Please fill required fields';
						$response['code'] = 201;
					}
					else{
						$company = $this->model->updateData('company',$_POST,['id'=>$_POST['id']]);
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

		function delete_company(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$id = $this->model->getValue('login','id',['id'=>$_POST['created_by'],'usertype'=>'admin']);
					if (empty($id)) {
						$response['message'] = 'Admin id is required';
						$response['code'] = 201;
					}
					else if (empty($_POST['id'])){
						$response['message'] = 'Company id is required';
						$response['code'] = 201;
					}
					else{
						$company = $this->model->deleteData('company',['id'=>$_POST['id']]);
						$company = $this->model->deleteData('login',['fk_id'=>$_POST['id'],'usertype'=>'company']);
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
			$response['message'] = 'success';
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
				$response['message'] = 'Invalid Request';
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
				$response['message'] = 'Company id is required';
				$response['code'] = 201;
				echo json_encode($response);
			 	return;
			}
			/*
			*example $_POST['select'] = ['modes','transport_types','customer_types']
			*/
			if(empty($_POST['select'])){
				$response['message'] = 'select array is required';
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
						$response['message'] = 'Please fill required fields';
						$response['code'] = 201;
					}
					else{
						$isExist = $this->model->isExist('branch','email',$_POST['email']);
						$isExist2 = $this->model->isExist('branch','contact',$_POST['contact']);
						$isExist3 = $this->model->isExist('login','phone',$_POST['contact']);
						$isExist4 = $this->model->isExist('login','email',$_POST['email']);
						if($isExist || $isExist2 || $isExist3 || $isExist4){
							$response['message'] = 'Email or contact is already exist';
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
							$response['message'] = 'success';
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
						$response['message'] = 'Branch id is required';
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
						$response['message'] = 'Branch id is required';
						$response['code'] = 201;
					}
					else if (empty($_POST['manager_name'])){
						$response['message'] = 'Please fill required fields';
						$response['code'] = 201;
					}
					else{
						// echo '<pre>'; print_r($_POST); exit;
						$branch = $this->model->updateData('branch',$_POST,['id'=>$_POST['id']]);
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

		function delete_branch(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Company id is required';
						$response['code'] = 201;
					}
					else{
						$company = $this->model->deleteData('branch',['id'=>$_POST['id']]);
						$company = $this->model->deleteData('login',['fk_id'=>$_POST['id'],'usertype'=>'branch']);
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
										$this->model->deleteData('transport_type',['id'=>$id]);
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
										$this->model->insertData('transport_type',['created_by'=>$_POST['created_by'],'type'=>$type]);
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
						$response['message'] = 'Please fill required fields';
						$response['code'] = 201;
					}
					else{
						$types = $this->model->getData('customer_types',[],'id');
						if(!empty($types)){
							$db_ids = array_column($types, 'id');
							if(!empty($db_ids)){
								foreach ($db_ids as $key => $id) {
									if(!in_array($id, $_POST['type_ids'])){
										$this->model->deleteData('customer_types',['id'=>$id]);
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
										$this->model->insertData('customer_types',['created_by'=>$_POST['created_by'],'type'=>trim($type)]);
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
			$customer_contacts = [];
			if(!empty($_POST['customer_contacts'])){
				$customer_contacts = json_decode($_POST['customer_contacts'],true);
			}
			unset($_POST['customer_contacts']);
			$customer_id = $this->model->insertData('customer',$_POST);
			if(empty($customer_id)){
				$response['message'] = 'System Error';
				$response['code'] = 201;
				echo json_encode($response);
				return;
			}
			if(!empty($customer_contacts)){
				foreach ($customer_contacts as $key => $value) {
					$value['customer_id'] = $customer_id;
					$this->model->insertData('customer_contacts',$value);
				}
			}
			
			$customer = [];
			$customer['fk_id'] = $customer_id;
			$customer['username'] = $_POST['name'];
			$customer['phone'] = $_POST['contact'];
			$customer['email'] = $_POST['email'];
			$customer['usertype'] = 'customer';
			$customer['status'] = 1;
			$customer['created_by'] = $_POST['created_by'];
			$this->model->insertData('login',$customer);

			$response['message'] = 'Customer Added';
			$response['code'] = 200;
			$response['status'] = true;
			echo json_encode($response);
		}

		function get_all_customers(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// print_r($_POST);die; 
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					$select = '*';
					$select2 = '*';
					$contacts = '0';
					if(!empty($_POST['select']) && isset($_POST['select'])) {
						$select = $_POST['select'];
						unset($_POST['select']);
					}
					if(!empty($_POST['contacts']) && isset($_POST['contacts'])) {
						$contacts = $_POST['contacts'];
						unset($_POST['contacts']);
					}
					$customer = $this->model->getData('customer',$_POST,$select);
					if(!empty($customer)){
						foreach ($customer as $key => $value) {
							if($contacts == '1'){
								if(!empty($value['id'])){
									$customer[$key]['contacts'] = $this->model->getData('customer_contacts',['customer_id'=>$value['id']],$select2);
								}
							}
						}
					}
					else{
						$customer = [];
					}
					$response['next_id'] = $this->model->generate_next_id('customer','autoid','cust','3');
					$response['customer'] = $customer;
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
			$contacts = $this->model->getData('customer_contacts',$_POST,$select);
			if(!empty($contacts)){
				foreach ($contacts as $key => $value) {
					if(!empty($value['customer_id'])){
						$contacts[$key]['customer_name']=$this->model->getValue('customer','name',['id'=>$value['customer_id']]);
					}
					if(!empty($value['city_id'])){
						$contacts[$key]['city_name']=$this->model->getValue('cities','city',['id'=>$value['city_id']]);	
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
			$this->model->updateData('customer_contacts',$_POST,['id'=>$_POST['id']]);
			$response['message'] = 'success';
			$response['code'] = 200;
			$response['status'] = true;
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
			$v_customer_contacts = [];
			if(!empty($_POST['customer_contacts'])){
				$_POST['customer_contacts'] = json_decode($_POST['customer_contacts'],true);
				$v_customer_contacts = $_POST['customer_contacts'];
			}
			
			unset($_POST['customer_contacts']);
			$customer = $this->model->updateData('customer',$_POST,['id'=>$_POST['id']]);

			if(!empty($v_customer_contacts)) {
				$v_ids = array_column($v_customer_contacts, 'id');
			}
			else{
				$v_ids = [];
			}
			$db_customer_contacts = $this->model->getData('customer_contacts',['customer_id'=>$_POST['id']]);
			if(!empty($db_customer_contacts)){
				$db_ids = array_column($db_customer_contacts, 'id');
			}
			else{
				$db_ids = [];
			}
			if(!empty($v_customer_contacts)){
				foreach ($v_customer_contacts as $key => $value) {
					if(!in_array($value['id'], $db_ids)) {
						//insert
						$value['customer_id'] = $_POST['id'];
						$this->model->insertData('customer_contacts',$value);
					}
					else{
						//update
						$value['customer_id'] = $_POST['id'];
						$this->model->updateData('customer_contacts',$value,['id'=>$value['id']]);

					}
				}
			}
			if(!empty($db_customer_contacts)){
				foreach ($db_customer_contacts as $key => $value){
					if(!in_array($value['id'], $v_ids)){
						$this->model->deleteData('customer_contacts',['id'=>$value['id']]);
					}
				}
			}

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

	/********************************** Designation *****************************************/
		function designations(){
			$response = array('code' => -1, 'status' => false, 'message' => '');

			$_POST['designations']  = json_decode($_POST['designations'],true);
			$_POST['designation_ids']  = json_decode($_POST['designation_ids'],true);
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['created_by']) || empty($_POST['designations'])){
						$response['message'] = 'Please fill required fields';
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
										$this->model->insertData('designation',['created_by'=>$_POST['created_by'],'designation'=>$designation]);
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
						$response['message'] = 'Please fill required fields';
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
								$employee['usertype'] = 'employee';
								$employee['status'] = 1;
								$employee['created_by'] = $_POST['created_by'];
								$this->model->insertData('login',$employee);
							}
							$response['message'] = 'success';
							$response['code'] = 200;
							$response['status'] = true;
						}
						else{
							$response['message'] = 'Employee email is already exist';
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

							$employees[$key]['company_name']=$this->model->getValue('company','name',['id'=>$value['company_id']]);
							$employees[$key]['city_name']=$this->model->getValue('cities','city',['id'=>$value['city_id']]);
							$employees[$key]['state_name']=$this->model->getValue('states','name',['id'=>$value['state_id']]);
							$employees[$key]['designation']=$this->model->getValue('designation','designation',['id'=>$value['designation_id']]);
						}
					}
					$response['next_id'] = $this->model->generate_next_id('employee','autoid','emp','3');
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
						$response['message'] = 'Employee id is required';
						$response['code'] = 201;
					}
					else{
						$employee = $this->model->updateData('employee',$_POST,['id'=>$_POST['id']]);

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

		function delete_employee(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['id'])){
						$response['message'] = 'Employee id is required';
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
						$response['message'] = 'Please fill required fields';
						$response['code'] = 201;
					}
					else{
						$types = $this->model->getData('vendor_types',[],'id');
						if(!empty($types)){
							$db_ids = array_column($types, 'id');
							if(!empty($db_ids)){
								foreach ($db_ids as $key => $id) {
									if(!in_array($id, $_POST['type_ids'])){
										$this->model->deleteData('vendor_types',['id'=>$id]);
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
										$this->model->insertData('vendor_types',['created_by'=>$_POST['created_by'],'type'=>trim($type)]);
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
					$vendor_types = $this->model->getData('vendor_types',['created_by'=>$_POST['created_by']],$select);
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
						$response['message'] = 'Please fill required fields';
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
					$response['next_id'] = $this->model->generate_next_id('vendor','autoid','ven',3);
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
						$response['message'] = 'Please fill required fields';
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
					$vehicles = $this->model->getData('vehicle',$_POST,$select);
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
						$response['message'] = 'Vehicle id is required';
						$response['code'] = 201;
					}
					else{
						$vehicle = $this->model->updateData('vehicle',$_POST,['id'=>$_POST['id']]);

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
						$response['message'] = 'Please fill required fields';
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
					else if (empty($_POST['transport_type']) || empty($_POST['zone_code']) || empty($_POST['zone'])){
						$response['message'] = 'Please fill required fields';
						$response['code'] = 201;
					}
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
		// 				$response['message'] = 'Please fill required fields';
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
		// 				$response['message'] = 'Please fill required fields';
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
						$response['message'] = 'Please fill required fields';
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
						$response['message'] = 'Please fill required fields';
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
		// 				$response['message'] = 'Please fill required fields';
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
						$mode = $this->model->getData('mode',['created_by'=>$_POST['created_by']],$select);
						$response['mode'] = $mode;
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
		// 				$response['message'] = 'Please fill required fields';
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
										$this->model->deleteData('mode',['id'=>$id]);
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
										$this->model->insertData('mode',['created_by'=>$_POST['created_by'],'mode_name'=>trim($mode_name)]);
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
						$response['message'] = 'Please fill required fields';
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
						$response['message'] = 'Please fill required fields';
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
						$response['message'] = 'Please fill required fields';
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
						$response['message'] = 'Please fill required fields';
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
			// 	$response['message'] = 'Please fill required fields';
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
				$rates = $this->model->getValue('global_rates','rates',['company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'mode'=>$_POST['mode'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']]);
				if(!empty($rates)){
					$rates = unserialize($rates);
					$rates[$_POST['from_zone_id']][$_POST['to_zone_id']] = $_POST['rate'];
					$insert['rates'] = serialize($rates);
					$this->model->updateData('global_rates',$insert,['company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'mode'=>$_POST['mode'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']]);
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
				$rates = $this->model->getValue('customer_rates','rates',['company_id'=>$_POST['company_id'],'customer_id'=>$_POST['customer_id'],'transport_type'=>$_POST['transport_type'],'mode'=>$_POST['mode'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']]);
				if(!empty($rates)){
					$rates = unserialize($rates);
					$rates[$_POST['from_zone_id']][$_POST['to_zone_id']] = $_POST['rate'];
					$insert['rates'] = serialize($rates);
					$this->model->updateData('customer_rates',$insert,['company_id'=>$_POST['company_id'],'customer_id'=>$_POST['customer_id'],'transport_type'=>$_POST['transport_type'],'mode'=>$_POST['mode'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']]);
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
			$rates = $this->model->getData('global_rates',['company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'mode'=>$_POST['mode'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']],'rates')[0]['rates'];
			if(!empty($rates)){
				$rates = unserialize($rates);
				if($_POST['customer_type'] == 'prime'){
					if(empty($_POST['customer_id'])){
						$response['message'] = 'Wrong Parameters';
						$response['code'] = 204;
						echo json_encode($response);
						return;
					}
					$customer_rates = $this->model->getData('customer_rates',['customer_id'=>$_POST['customer_id'],'company_id'=>$_POST['company_id'],'transport_type'=>$_POST['transport_type'],'mode'=>$_POST['mode'],'kg_or_box'=>$_POST['kg_or_box'],'transport_mode'=>$_POST['transport_mode'],'delivery_type'=>$_POST['delivery_type']],'rates')[0]['rates'];
					$customer_rates = unserialize($customer_rates);
					if(empty($customer_rates)) $customer_rates = [];
					foreach($customer_rates as $from_zone_id => $value){
						foreach ($value as $to_zone_id => $rate) {
							if(!empty($customer_rates[$from_zone_id][$to_zone_id])){
								$rates[$from_zone_id][$to_zone_id] = $rate;
							}
						}
					}
				}
			}
			else{
				$rates = [];
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
						$response['message'] = 'Please fill required fields';
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

		function get_tat(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			$validate = validateToken();
			if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['mode_id']) && $_POST['transport_type_id']){
						$response['message'] = 'Please fill required fields';
						$response['code'] = 201;
					}
					else{
						$select = '*';
						if(!empty($_POST['select']) && isset($_POST['select'])) {
							$select = $_POST['select'];
							unset($_POST['select']);
						}
						$tat = $this->tatl->getData('tat',$_POST,$select);
						$response['tat'] = $tat;
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
	
	/********************************** Ship(Order) *****************************************/

		function ship(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['shipper_cust_id'])) {
						$response['message'] = 'Please fill required fields';
						$response['code'] = 201;
					}
					else{
						// if($_POST['no_of_packages'] > 1){
						
						// }
						$from_zone_id = $this->model->getData('zone',[],'id',[],[],['cities'=>$_POST['shipper_city_id']])[0]['id'];
						$to_zone_id = $this->model->getData('zone',[],'id',[],[],['cities'=>$_POST['consinee_city_id']])[0]['id'];
						$current_date = date('Y-m-d');
						$is_prime = $this->model->getValue('customer','prime_member',['end_date >'=>$current_date,'id'=>$_POST['shipper_cust_id']]);
						
						if(!empty($is_prime)){
							$where = [];
							$where['cust_id'] = $_POST['shipper_cust_id'];
							$where['transport_type_id'] = $_POST['transport_type_id'];
							$where['mode_id'] = $_POST['mode_id'];
							$where['from_zone_id'] = $from_zone_id;
							$where['to_zone_id'] = $to_zone_id;
							$rate = $this->model->getValue('customer_rates','rate',$where);
						}
						else{
							$where = [];
							$where['transport_type_id'] = $_POST['transport_type_id'];
							$where['mode_id'] = $_POST['mode_id'];
							$where['from_zone_id'] = $from_zone_id;
							$where['to_zone_id'] = $to_zone_id;
							$rate = $this->model->getValue('global_rates','rate',$where);
						}
						$total_kg = 0;
						if(!empty($_POST['ship_dimensions'])){
							foreach ($_POST['ship_dimensions'] as $key => $value) {
								$total_kg = $value['weight'] * $value['qty'];
							}
						}
						$shipping_charges = $total_kg * $rate;

						$mode_name = $this->model->getValue('mode','mode_name',['id'=>$_POST['mode_id']]);
						$mode_name = strtolower($mode_name);

						$_POST['shipping_charges'] = $shipping_charges;

						if($mode_name == 'ftl'){
							$_POST['shipping_charges'] = $_POST['ftl_charges'] + $_POST['load_unload_charges'];
						}

						$payment_type = strtolower($_POST['ship_payment']['payment_type']);
						if($payment_type == 'cod'){
							$_POST['shipping_charges'] = $_POST['ship_payment']['cash_charges'];
						}

						$shiper_insurance_charges = $this->model->getValue('customer','insurance_charges',['id'=>$_POST['shipper_cust_id'] ]);
						$shiper_fuel_charges = $this->model->getValue('customer','fuel_charges',['id'=>$_POST['shipper_cust_id'] ]);
						$_POST['insurance_charges'] = $_POST['invoice_value'] * $shiper_insurance_charges;
						$_POST['fuel_charges'] = ($total_kg * $rate * $shiper_fuel_charges)/100;
						// echo '<pre>'; print_r($mode_name); exit;

						$ship_dimensions = $_POST['ship_dimensions'];
						$ship_payment = $_POST['ship_payment'];
						unset($_POST['ship_dimensions']);
						unset($_POST['ship_payment']);
						$ship_id = $this->model->insertData('ship',$_POST);
						if(!empty($ship_dimensions)) {
							foreach ($ship_dimensions as $key => $ship_dimension) {
								$ship_dimension['ship_id'] = $ship_id;
								$ship_dimension['dimensions'] = $ship_dimension['L'].'*'.$ship_dimension['W'].'*'.$ship_dimension['H'];
								$this->model->insertData('ship_dimensions',$ship_dimension);
							}
						}
						$ship_payment['ship_id'] = $ship_id;
						$this->model->insertData('ship_payment',$ship_payment);

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
			$ship_history = $this->model->getData('ship',$_POST,$select);
			if(!empty($ship_history)){
				foreach ($ship_history as $key => $value) {
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

	/********************************** Delivery boys *****************************************/
		function add_delivery_boy(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			// $validate = validateToken();
			// if($validate){
				if ($_SERVER["REQUEST_METHOD"] == "POST"){
					if (empty($_POST['contact'])){
						$response['message'] = 'Please fill required fields';
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
						$response['message'] = 'Please fill required fields';
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
						$response['message'] = 'Please fill required fields';
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
						$response['message'] = 'Please fill required fields';
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
				$response['message'] = 'No direct script is allowed.';
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
					$response['message'] = 'No direct script is allowed.';
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
					$vechile_no = $this->input->post('vechile_id');
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
						$data=$this->Adminapi_Model->get_details_on_vechile($vechile_no);
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
								'vechile_id'=>$data['id'],
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
					$response['message'] = 'No direct script is allowed.';
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

		public function add_map_barcode(){
			$response = array('code' => -1, 'status' => false, 'message' => '');
			if ($_SERVER["REQUEST_METHOD"] == "POST") {

				
				$awb_no['AWBno']=$this->input->post('awb_no');
				// $o_id=$this->input->post('o_id');
				$o_id = $this->model->getData('tbl_order_booking',$awb_no,'id');

				$awb_no=$awb_no['AWBno'];
				$barcode_no = json_decode($_POST['barcode_no'],true);
				$date = date('d/m/Y');

				// echo"<pre>";
				// print_r($awb_no);die;
			
				if (empty($awb_no)) {
					$response['message'] = 'Awb No is required.';
					$response['code'] = 201;
				} else if (empty($barcode_no)) {
					$response['message'] = 'Barcode is required.';
					$response['code'] = 201;
				} 
				// else if (empty($o_id)) {
				// 	$response['message'] = 'Id is required.';
				// 	$response['code'] = 201;
				// }                     
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
				$response['message'] = 'No direct script is allowed.';
				$response['code'] = 204;
			}
			echo json_encode($response);
		}

}