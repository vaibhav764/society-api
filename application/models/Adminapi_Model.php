<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Adminapi_Model extends CI_Model 
{

		public function __construct() 
		{
			
        }
        
        //=================================================Login Part=================================================
        public function login($data = array(),$tablename)
        {
        
            $response = array();
            $username = $data['email'];
            $password = $data['password'];
        
            
            // $user_data=$this->db->select('user_type')
            //                 ->from('tbl_login')
            //                 ->where('email',$username)
            //                 ->get()
            //                 ->result();
        
        
            //     if($user_data[0]->user_type=="Superadmin")
            //     {
            //         $encryptedpassword= md5($password);
            //     }
            //     else{
                     $encryptedpassword = $this->dec_enc('encrypt', $password);
                   
                //}
                
          
        
            $check = is_numeric($username);
        
            if($check)
            {
                
                $this->db->select('*');
                $this->db->from($tablename);
                $this->db->where("contact_no", $username);
                $this->db->where("password", $encryptedpassword);
                $this->db->where("status", '1');
        
        
            }
            else
            {
                $this->db->select('*');
        
                $this->db->from($tablename);
                $this->db->where("email_id", $username);
                $this->db->where("status", '1');
                $this->db->where("password", $encryptedpassword);
            }
            $query  = $this->db->get();
            $result = $query->result_array();
            $op       = $query->row();
            $num_user = $query->num_rows();
        
            
             @$status = $result[0]['status'];
             @$id = $result[0]['id'];
        
            if (!$num_user) 
            {
                $response['status']=0;
                $response['message']="User Not found";
                return $response;
        
            }
        
            // else {
            //         $token =  $username . "_" . date("Y-m-d_H-i-s", time());
            //         $result1 = $this->Admin_Model->check_table_single('tbl_login_info','fk_customer_id',$id);
            //         if($result1['result'] == true)
            //         {
        
            //              $data1   = array(
            //             'fk_customer_id' => $op->id,
            //             'token' => md5($token),
            //             'date' => date("Y-m-d_H-i-s", time())
            //         );
            //         $results = $this->Admin_Model->update_login_info('tbl_login_info', $data1);
            //         } 
            //         else {
            //             $data1   = array(
            //             'fk_customer_id' => $op->id,
            //             'token' => md5($token),
            //             'date' => date("Y-m-d_H-i-s", time())
            //         );
            //         $results = $this->Admin_Model->add_data_in_table('tbl_login_info', $data1);
        
        
            //  }
        
                    $response['status']=1;
                    $response['message']="Login Success";
                    $response['data'] = $op;
                //    $response['token'] = md5($token);
                    return $response;   
            
        
        }

        public function check_user($table,$email_id)
        {
            $response = array();
            $query    = $this->db->select('*')
                                 ->from($table)
                                 ->where('email_id',$email_id)
                                 ->get();
             $result = $query->result_array();
            if($query->num_rows() >= 1) {
                    $response['status']=0;
                    $response['message']='Email Already exist';
                    return $response;
            }
            else{
                 $response['status']=1;
                 return $response;
            }
        }
        public function get_name($table,$email_id)
        {
            $response = array();
            $query    = $this->db->select('username')
                                 ->from($table)
                                 ->where('email_id',$email_id)
                                 ->get();
             $result = $query->result_array();
             if($query->num_rows() >= 1) {
                    $response =$result; 
                    return $response;
            }
        }
        //==========================================================Verify Otp======================================================
        public function verify_otp($table,$c_id,$otp)
        {
    
                $this->db->select('*');
                $this->db->from($table);
                $this->db->where('id', $c_id);
                $query = $this->db->get();
                $result = $query->result_array();
                @$db_otp = $result[0]['otp'];
                if($db_otp == $otp)
                {
                        $this->db->set('status', '1'); //value that used to update column  
                        $this->db->where('id', $c_id); //which row want to upgrade  
                        $this->db->update($table);
                        $response['status']=1;
                        $response['message']='OTP verify successfully';
                }
                else
                {
                        $response['status']=0;
                        $response['message']='OTP mismatch';
                }
                return $response;
        }
     //=====================================================Resend Otp===============================================================
            public function resend_otp($mobile)
            {
                $response = array();
                $this->db->select('*');
                $this->db->from('tbl_login');
                $this->db->where("contact_no", $mobile);
    
    
                $query = $this->db->get();
                $result = $query->result_array();
                $op = $query->row();
                $num_user = $query->num_rows();
                
                if($num_user == 0)
                {
                    $response['status'] = 0;
                    $response['message'] = "User not found";
                    return $response;
                    die;
                }
                $otp = $result[0]['otp'];
                $c_id = $result[0]['id'];
    
                  $response['status'] = 1;
                  $response['message'] = "Otp send successfully";
                  $response['otp'] = $otp;
                  $response['id'] = $c_id;
                 return $response;    
                
            }
    //=========================================Reset Password============================================
            public function reset_password($email_id,$password)
            {
                    $response = array();
    
                    $pass= $this->Adminapi_Model->dec_enc('encrypt', $password);
                    $this->db->set('password',$pass);
                    $this->db->where('email_id', $email_id);  
                    $this->db->update('tbl_login');
    
                      $response['status'] = 1;
                      $response['message'] = "Password Reset Successfully";
                      return $response;
            }
    //===========================================Update Otp===============================================
        public function update_otp($data,$data1)
        {
            
            $this->db->where("(email_id='".$data['phone_no']."' OR contact_no ='".$data['phone_no']."')", NULL, FALSE);
            $this->db->update('tbl_login', $data1);
            $response['status']='1';
            $response['message']='Details send successfully';
            return $response;
        }
    
        public function countRowonId($tablename,$data)
        {
        
            $this->db->select('fk_empid');
            $this->db->from($tablename);
            $this->db->where('fk_empid',$data['fk_empid']);
            $query = $this->db->get();
            
            if($query->num_rows()>=1)
            {
                $response['status'] = 1;
                $response['message'] = 'success';
                $response['data'] = $query->num_rows(); 
                
            }
            else{
                $response['status'] = 0;
                $response['message'] = 'Id not found';
                $response['data'] = $query->num_rows(); 
            }
            return $response;
        }
    
        public function get_common_detail11($table,$data = array())
        {
               $response = array();
                $this->db->select('*');
                $this->db->from($table);
                $this->db->where("fk_empid",$data['fk_empid']);
                $this->db->like('AWBno', $data['AWBno']);
                $query = $this->db->get();
                $result = $query->result_array();
                if($query->num_rows() >= 1) {
                    $response['status'] = 1;
                    $response['message'] = 'success';
                    $response['data'] = $result;
                    return $response;
               }
               else
               {
                $response['status'] = 0;
                $response['message'] = 'Id not Found';
                $response['data'] = $result;
                return $response;
               }
        }
        public function get_data_on_awb_no($data = array())
        {
               $response = array();
                $this->db->select('tbl_order_booking.*, tbl_order_booking.id as order_id,tbl_order_status.*,tbl_order_status.id as status_id');
                $this->db->from('tbl_order_booking');
                $this->db->join('tbl_order_status','tbl_order_status.fk_oid=tbl_order_booking.id','left');
                $this->db->where("tbl_order_booking.AWBno",$data['AWBno']);
    
                $query = $this->db->get();
                $result = $query->result_array();
                if($query->num_rows() >= 1) {
                    $response['status'] = 1;
                    $response['message'] = 'success';
                    $response['data'] = $result;
                    return $response;
               }
               else
               {
                $response['status'] = 0;
                $response['message'] = 'AwbNo Does Not Match';
                $response['data'] = $result;
                return $response;
               }              
        }
        public function common_data_update($tablename,$data,$key,$id)
        {
                $response  = array();
                $this->db->where($id, $key);
                $status = $this->db->update($tablename, $data);

                if($status)

                    {
                        $response['status']=1;
                        $response['message']='Success';
                        return  $response;
                    }
                    else
                    {
                        $response['status']=0;
                        $response['message']='Failed';
                        return  $response;
                    }
        }
        public function common_data_ins($tablename,$data)
	    {

				$response = array();
                $status = $this->db->insert($tablename, $data);
		
				if($status)
				{
		
		       		$response['id'] = $this->db->insert_id();
		       		$response['status']=1;
		       		$response['message']='Success';
		       		return  $response;		
				}
				else
				{
					$response['status']=0;
					$response['message']='Failed';
					return  $response;
				}

        }

        public function get_data($data=array())
        {
            $response = array();
            $this->db->select('*');
            $this->db->from('map_barcode');
            $this->db->where("awb_no",$data['awb_no']);

            $query = $this->db->get();
            $result = $query->result_array();
            if($query->num_rows() >= 1) {
                $response['status'] = 1;
                $response['message'] = 'success';
                $response['data'] = $result;
                return $response;
           }
           else
           {
            $response['status'] = 0;
            $response['message'] = 'AwbNo Does Not Match';
            $response['data'] = $result;
            return $response;
           }   
        }
         public function get_awbno_by_barcode($barcode_no)
        {
            $this->db->select('awb_no');
            $this->db->from('map_barcode');
            $this->db->where("barcode_no",$barcode_no);
            $query = $this->db->get();
            $result = $query->row_array();
            if($query->num_rows() >= 1) {                
                return $result;
           }
           else
           {
                return $result;
           }   
        }
        
        public function get_pickup_scan_location($awb_no)
        {
            $this->db->select('*');
                $this->db->from('ship');
                $this->db->where('AWBno',$awb_no);    
                $query = $this->db->get();
                $result = $query->row_array();
                $result['pickup_city'] = $this->model->getValue('customer_contacts','city',['id'=>$result['shipper_contact']]);
                if($query->num_rows() >= 1) {                
                    return $result;
               }
               else
               {
                    return $result;
               }   
        }

        public function get_awbno_by_barcode_no($barcode_no)
        {
            $this->db->select('awb_no,id');
            $this->db->from('source_inscan');
            $this->db->where("barcode_no",$barcode_no);
            $query = $this->db->get();
            $result = $query->row_array();
            if($query->num_rows() >= 1) {                
                return $result;
           }
           else
           {
                return $result;
           }   
        }

        public function get_details_on_awb_no($awb_no)
        {
            $this->db->select('*');
            $this->db->from('ship');
            $this->db->where("AWBno",$awb_no);
            $query = $this->db->get();
            $result = $query->row_array();
            if($query->num_rows() >= 1) {                
                return $result;
           }
           else
           {
                return $result;
           }   
        }
        
        public function get_employee_details($emp_id)
        {
                $this->db->select('work_area_location');
                $this->db->from('employee');
                $this->db->where('id',$emp_id);    
                $query = $this->db->get();
                $result = $query->row_array();
                if($query->num_rows() >= 1) {                
                    return $result;
               }
               else
               {
                    return $result;
               }   
        }

        public function get_details_on_vechile($vechile_no)
        {
            $this->db->select('*');
            $this->db->from('vehicle');
            $this->db->where('id',$vechile_no);

            $query = $this->db->get();
            $result = $query->row_array();
            if($query->num_rows() >= 1) {                
                return $result;
           }
           else
           {
                return $result;
           }   
        }

        public function get_city_by_awb_no($awb_no)
        {
            $this->db->select('*');
            $this->db->from('ship');
            $this->db->where('AWBno',$awb_no);
            $query = $this->db->get();
            $result = $query->row_array();
            $result['pickup_city'] = $this->model->getValue('customer_contacts','city',['id'=>$result['shipper_contact']]);
            $result['drop_city'] = $this->model->getValue('customer_contacts','city',['id'=>$result['recepient_contact']]);
            if($query->num_rows() >= 1) {                
                return $result;
           }
           else
           {
                return $result;
           }   
        }
        public function getCountry()
        {
            $query = $this->db->query('SELECT * FROM  countries');
            return $query->result();
        }
        public function getStates($data)
        {
              $this->db->select('*');
              $this->db->from('states');
              $this->db->where($data);
              $result_set = $this->db->get();
              if($result_set->num_rows() > 0)
              {
                  return $result_set->result();
              }
              else 
              {
                   return FALSE;
              }
        }
        public function getcities($data)
        {
              $this->db->select('*');
              $this->db->from('cities');
              $this->db->where($data);
              $result_set = $this->db->get();
              if($result_set->num_rows() > 0)
              {
                  return $result_set->result();
              }
              else 
              {
                   return FALSE;
              }
        }
        public function order_details($value='')
        {
            if ($value['type']=='Prime') {
                $tablename = 'tbl_prime_vendor_charges';
            } else {
                $tablename = 'tbl_rate_master';
            }
            
            $this->db->select('*');
            $this->db->from($tablename);
            $this->db->where('c_id',$value['c_id']);
            $this->db->like('cities',$value['drop_city']);
            $data=$this->db->get();
            $data=$data->row_array();
            $insert_array = array(
                'order_id'=>@$value['order_id'],
                'fk_id'=>@$value['fk_id'],
                'c_id'=>@$value['c_id'],
                'grand_total'=>@$value['grand_total'],
                'drop_city'=>@$value['drop_city'],
                'insurace_charges'=>@$data['insurance_charges'],
                'delivery_charges'=>@$data['delivery_charges'],
                'bilty_charges'=>@$data['bilty_charges']
            );
            $response=$this->db->insert('tbl_order_details',$insert_array);
        }
    //     public function get_company_rate_by_city($id,$drop_city,$c_id)
    //     {
         
    //             $response = array();
    //             $this->db->select('tbl_vendor_master.*,tbl_vendor_master.id as vendor_id,tbl_prime_vendor_charges.*,tbl_prime_vendor_charges.id as prime_id');//,'tbl_zone_master.*,tbl_zone_master.id as zone_id,tbl_rate_master.*,tbl_rate_master.id as rate_id'
    //             $this->db->from('tbl_vendor_master');
    //             $this->db->join('tbl_prime_vendor_charges','tbl_prime_vendor_charges.fk_vendorid=tbl_vendor_master.id','left');
    //             // $this->db->join('tbl_zone_master','tbl_prime_vendor_charges.zone=tbl_zone_master.zone','left');
    //             // $this->db->join('tbl_rate_master','tbl_rate_master.zone=tbl_zone_master.zone','left');
    //             $this->db->where('tbl_vendor_master.id',$id);
    //             $this->db->where('tbl_prime_vendor_charges.c_id',$c_id);
    //           //  $this->db->where('tbl_rate_master.c_id',$c_id);
    //             //$this->db->where("FIND_IN_SET('$drop_city',tbl_rate_master.cities) !=", 0);
    //             $this->db->where("FIND_IN_SET('$drop_city',tbl_prime_vendor_charges.cities) !=", 0);

    //                 $query = $this->db->get();
    //                 $result = $query->row_array();
    //                 $response['status'] = 1;
    //                 $response['message'] = 'success';
    //                 $response['data'] = $result;
    //                 return $response;    

    // }
    //     public function get_company_rate_by_city1($drop_city,$c_id)
    //     {
             
    //             $response = array();
    //             $this->db->select('*');
    //             $this->db->from('tbl_rate_master');
    //             // $this->db->where('tbl_prime_vendor_charges.c_id',$c_id);
    //             $this->db->where('tbl_rate_master.c_id',$c_id);
    //             $this->db->where("FIND_IN_SET('$drop_city',tbl_rate_master.cities) !=", 0);
    //             $query = $this->db->get();
    //             $result = $query->result_array();
    //             $response['status'] = 1;
    //             $response['message'] = 'success';
    //             $response['data'] = $result;
    //             return $response;     
    
    //     }
    public function get_company_rate_by_city($id, $drop_city, $c_id, $pickup_city) 
    {
        $response = array();
        $this->db->select('tbl_vendor_master.*,tbl_vendor_master.id as vendor_id,tbl_prime_rate.*,tbl_prime_rate.*,tbl_zone_master.cities,Pickupzone.id as pick_up,tbl_zone_master.id as zone_id');
        $this->db->from('tbl_vendor_master');
        $this->db->join('tbl_prime_rate', 'tbl_prime_rate.fk_vendor_id=tbl_vendor_master.id', 'left');
        $this->db->join('tbl_zone_master AS Pickupzone', 'tbl_prime_rate.from_zone_id=Pickupzone.id', 'left');
        $this->db->join('tbl_zone_master', 'tbl_prime_rate.to_zone_id=tbl_zone_master.id', 'left');
        $this->db->where('tbl_vendor_master.id', $id);
        $this->db->where('tbl_prime_rate.c_id', $c_id);
        $this->db->where("FIND_IN_SET('$pickup_city',Pickupzone.cities) !=", 0);
        $this->db->where("FIND_IN_SET('$drop_city',tbl_zone_master.cities) !=", 0);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
        public function get_company_rate_by_city1($drop_city,$c_id,$pickup_city)
        {
                $response = array();
                $this->db->select('tbl_rate_deatils.*,tbl_zone_master.cities,Pickupzone.id as pick_up,tbl_zone_master.id as zone_id');
                $this->db->from('tbl_rate_deatils');
                $this->db->join('tbl_zone_master AS Pickupzone', 'tbl_rate_deatils.source_zone_id=Pickupzone.id', 'left');
                $this->db->join('tbl_zone_master', 'tbl_rate_deatils.dest_zone_id=tbl_zone_master.id', 'left');
                $this->db->where('tbl_rate_deatils.c_id',$c_id);
                $this->db->where("FIND_IN_SET('$pickup_city',Pickupzone.cities) !=", 0);
                $this->db->where("FIND_IN_SET('$drop_city',tbl_zone_master.cities) !=", 0);
                $query = $this->db->get();
                $result = $query->result_array();
                $response['status'] = 1;
                $response['message'] = 'success';
                $response['data'] = $result;
                return $response;
        }
        public function get_rate_by_city2($drop_city)
        {
          $response = array();
              $this->db->select('tbl_zone_master.*,tbl_rate_master.*,tbl_rate_master.id as rate_master_id');
              $this->db->from('tbl_zone_master');            
              $this->db->join('tbl_rate_master','tbl_rate_master.zone=tbl_zone_master.zone','left');
              $this->db->where("FIND_IN_SET('$drop_city',tbl_rate_master.cities) !=", 0);
    
              $query = $this->db->get();
              $result = $query->result_array();
              $response['status'] = 1;
              $response['message'] = 'success';
              $response['data'] = $result;
              return $response;
    
        }
        public function get_membership_type_user_type($id,$user_type,$membership_type,$c_id)
        {
              $response = array();
             $this->db->select('*');
             $this->db->from('tbl_login');
             $this->db->where('fk_id',$id);
             $this->db->where('user_type',$user_type);
             $this->db->where('membership_type',$membership_type);
             $this->db->where('c_id',$c_id);
              $query = $this->db->get();
              $result = $query->result_array();
              $response['status'] = 1;
              $response['message'] = 'success';
              $response['data'] = $result;
              return $response;
        }

    public function order_status($data)
    {
        $response = array();
        $this->db->select('tbl_order_booking.AWBno,tbl_order_status.order_status');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_order_status','tbl_order_status.fk_oid=tbl_order_booking.id');
        $this->db->where('tbl_order_booking.AWBno',$data['AWBno']);
       
         $query = $this->db->get();
         $result = $query->result_array();
         $response['status'] = 1;
         $response['message'] = 'success';
         $response['data'] = $result;
         return $response;
    }
    public function view_order_details($data = array())
        {
                $response = array();

                $this->db->select('tbl_order_booking.*,tbl_order_booking.grand_total as total,tbl_order_booking.id as order_id,tbl_order_details.*,tbl_order_details.id as details_id,tbl_vendor_master.id as vendor_id,tbl_vendor_master.v_company,tbl_prime_vendor_charges.*,countries.name as country_name,states.name as state_name,tbl_company_master.c_name,tbl_company_master.*,tbl_company_master.id as company_id,tbl_order_status.*,tbl_order_status.id as order_status_id,tbl_status_master.status_name,tbl_billing_details.*');
                $this->db->from('tbl_order_booking');
                $this->db->join('tbl_vendor_master','tbl_order_booking.fk_id=tbl_vendor_master.id','left');
                $this->db->join('tbl_order_details','tbl_order_details.order_id=tbl_order_booking.id','left');
                $this->db->join('tbl_prime_vendor_charges','tbl_prime_vendor_charges.fk_vendorid=tbl_vendor_master.id','left');
                $this->db->join('tbl_order_status','tbl_order_status.fk_oid=tbl_order_booking.id','left');
                $this->db->join('countries','tbl_order_booking.pickup_country=countries.id','left');
                $this->db->join('states','tbl_order_booking.pickup_state=states.id','left');
                $this->db->join('tbl_company_master','tbl_order_booking.c_id=tbl_company_master.id','left');
                $this->db->join('tbl_status_master','tbl_order_status.order_status=tbl_status_master.id','left');
                $this->db->join('tbl_billing_details','tbl_billing_details.fk_order_id=tbl_order_booking.id','left');
                $this->db->where("tbl_order_booking.id",$data['id']);
                $this->db->order_by('tbl_order_booking.id','DESC');


                $query = $this->db->get();
                $result = $query->result_array();

                $response['status'] = 1;
                $response['message'] = 'success';
                $response['data'] = $result;
                return $response;   
        }
    public function get_city_query($pincode)
    {
        $query = $this->db->get_where('tbl_pincode', array('postel_code' => $pincode));
        return $query->result();
    }
    public function get_shipper_name_details($c_id,$name)
    {
        $response = array();

        $this->db->select('*');
        $this->db->from('tbl_vendor_master');
        $this->db->where("c_id",$c_id);//
        $this->db->like('v_company',$name);
        $query = $this->db->get();
        $result = $query->result_array();

        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
       
        return $response; 
    }
    public function get_drop_name_details($c_id,$name)
    {
            $response = array();

            $this->db->select('*');
            $this->db->from('tbl_consignee_master');
            $this->db->where("c_id",$c_id);
            $this->db->like('name',$name);
            $query = $this->db->get();
            $result = $query->result_array();

            $response['status'] = 1;
            $response['message'] = 'success';
            $response['data'] = $result;
        
            return $response;
    }
    public function view_order_on_login_id($data=array())
    {
            $response = array();
            $this->db->select('tbl_order_booking.*,tbl_vendor_master.id as vendor_id,tbl_vendor_master.v_company,countries.name as country_name1,states.name as state_name1,Dropcountry.name as country_name2,Dropstate.name as state_name2');
            $this->db->from('tbl_order_booking');
            $this->db->join('tbl_vendor_master','tbl_order_booking.fk_id=tbl_vendor_master.id','left');
            $this->db->join('countries','tbl_order_booking.pickup_country=countries.id','left');
            $this->db->join('states','tbl_order_booking.pickup_state=states.id','left');
            $this->db->join('countries AS Dropcountry','tbl_order_booking.drop_country=Dropcountry.id','left');
            $this->db->join('states AS Dropstate','tbl_order_booking.drop_state=Dropstate.id','left');
            $this->db->where('tbl_order_booking.c_id',$data['id']);
                
            $query = $this->db->get();
            $result = $query->result_array();

            $response['status'] = 1;
            $response['message'] = 'success';
            $response['data'] = $result;
            return $response;
    }
    
    public function customer_order($data = array())
    {
            $response = array();
            $this->db->select('tbl_order_booking.*,tbl_order_booking.id as order_id,tbl_order_status.*,tbl_order_status.id as order_status_id,tbl_vendor_master.id as vendor_id,tbl_vendor_master.v_company,countries.name as country_name1,states.name as state_name1,Dropcountry.name as country_name2,Dropstate.name as state_name2');
            $this->db->from('tbl_order_booking');
            $this->db->join('tbl_order_status','tbl_order_status.fk_oid=tbl_order_booking.id','left');
            $this->db->join('tbl_vendor_master','tbl_order_booking.fk_id=tbl_vendor_master.id','left');
            $this->db->join('countries','tbl_order_booking.pickup_country=countries.id','left');
            $this->db->join('states','tbl_order_booking.pickup_state=states.id','left');
            $this->db->join('countries AS Dropcountry','tbl_order_booking.drop_country=Dropcountry.id','left');
            $this->db->join('states AS Dropstate','tbl_order_booking.drop_state=Dropstate.id','left');
            $this->db->where("tbl_order_booking.fk_id", $data['id']);  
            $query = $this->db->get();
            $result = $query->result_array();

            $response['status'] = 1;
            $response['message'] = 'success';
            $response['data'] = $result;
            return $response;
    }
     public function get_scan_count($o_id='',$awb_no='')
     {
            $box_count=$this->get_total_order($o_id);
            $query = $this->db->get_where('map_barcode',array('o_id'=>$o_id,'awb_no'=>$awb_no));
            $count = $query->num_rows();
            $response['total_order']=$box_count;
            $response['scan_count']=$count;
            if($count==$box_count)
            {
                $response['is_submit']=true;
            }
            else
            {
                $response['is_submit']=false;
            }
            return $response;
    }
    public function get_total_order($o_id)
    {
           
            $this->db->select('no_of_boxes');
            $this->db->from('ship');
            $this->db->where('id',$o_id);
            $query = $this->db->get();
            $result = $query->row_array();
            $result=$result['no_of_boxes'];           
            return $result;
    }
      public function check_data($barcode_no)
      {
            $response = array();
            $query = $this->db->get_where('map_barcode', array('barcode_no'=>$barcode_no));
            if ($query->num_rows() > 0){
                $response['status'] = 1;
                $response['message'] = 'Already Exist';
                return $response;
            }
            else
            {
                $response['status'] = 0;
                $response['message'] = 'new user';
                return $response;
            }
       
      }
     public function check_data1($barcode_no,$type)
      {
            if($type=='Source')
            {
                $table='source_inscan';
            }
            else if($type=='Destination')
            {
                $table='destination_inscan';
            }

            $response = array();

            $query = $this->db->get_where($table, array('barcode_no'=>$barcode_no));
            if ($query->num_rows() > 0){
                $response['status'] = 1;
                $response['message'] = 'Already Exist';
                return $response;
            }
            else
            {
                $response['status'] = 0;
                $response['message'] = 'new user';
                return $response;
            }
       
      }

      public function check_data2($barcode_no,$type)
      {
        if($type=='Source')
        {
            $table='source_outscan';
        }
        else if($type=='Destination')
        {
            $table='destination_outscan';
        }
            $response = array();
            $query = $this->db->get_where($table,array('barcode_no'=>$barcode_no));
            if ($query->num_rows() > 0){
                $response['status'] = 1;
                $response['message'] = 'Already Exist';
                return $response;
            }
            else
            {
                $response['status'] = 0;
                $response['message'] = 'new user';
                return $response;
            }
      }
      
      public function get_employee_profile($data=array())
      {
                $response = array();
                $this->db->select('tbl_employee_master.*,tbl_employee_master.id as emp_id,tbl_company_master.c_name,tbl_designation_master.designation,tbl_employee_designation.*,tbl_employee_designation.id as emp_designation_id,states.name');
                $this->db->from('tbl_employee_master');
                $this->db->join('tbl_company_master','tbl_employee_master.c_id=tbl_company_master.id','left');
                $this->db->join('tbl_employee_designation','tbl_employee_designation.emp_id=tbl_employee_master.id','left');
                $this->db->join('tbl_designation_master','tbl_employee_designation.desg_id=tbl_designation_master.id','left');
                $this->db->join('states','tbl_employee_master.emp_state=states.id','left');
                $this->db->where("tbl_employee_master.id",$data['id']);

                $query = $this->db->get();
                $result = $query->result_array();
                $response['status'] = 1;
                $response['message'] = 'success';
                $response['data'] = $result;
                return $response;
      }
      public function get_company_profile($data=array())
      {
                $response = array();
                $this->db->select('tbl_company_master.*');
                $this->db->from('tbl_company_master');              
                $this->db->where("tbl_company_master.id",$data['id']);

                $query = $this->db->get();
                $result = $query->result_array();
                $response['status'] = 1;
                $response['message'] = 'success';
                $response['data'] = $result;
                return $response;
      }
      public function get_customer_profile($data=array())
      {
        $response = array();
        $this->db->select('tbl_vendor_master.*,states.name');
        $this->db->from('tbl_vendor_master');       
        $this->db->join('states','tbl_vendor_master.v_state=states.id','left');
        $this->db->where('tbl_vendor_master.id',$data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
      }
      public function destination_pickup_list($table,$data = array())
      {
             $response = array();
              $this->db->select('*');
              $this->db->from($table);
              $this->db->where("drop_fk_emp_id",$data['drop_fk_emp_id']);
  
              $query = $this->db->get();
              $result = $query->result_array();
              if($query->num_rows() >= 1) {
                  $response['status'] = 1;
                  $response['message'] = 'success';
                  $response['data'] = $result;
                  return $response;
             }
             else
             {
                  $response['status'] = 0;
                  $response['message'] = 'Id not Found';
                  $response['data'] = $result;
                  return $response;
             }
      }
      
       public function get_customer_data($fk_id)
      {
                $response = array();
                $this->db->select('tbl_vendor_master.*,states.name');
                $this->db->from('tbl_vendor_master');       
                $this->db->join('states','tbl_vendor_master.v_state=states.id','left');
                $this->db->where('tbl_vendor_master.id',$fk_id);
                $query = $this->db->get();
                $result = $query->row_array();
                
                $response['status'] = 1;
                $response['message'] = 'success';
                $response['data'] = $result;
                return $response;
      }

      public function get_consignee_data($drop_id)
      {
                $response = array();
                $this->db->select('*');
                $this->db->from('tbl_consignee_master');       
                $this->db->where('id',$drop_id);
                $query = $this->db->get();
                $result = $query->row_array();
                
                $response['status'] = 1;
                $response['message'] = 'success';
                $response['data'] = $result;
                return $response;
      }
      
        public function get_inscan_count($awb_no)
            {
                    $box_count=$this->get_total_order1($awb_no);
                    $query = $this->db->get_where('source_inscan',array('awb_no'=>$awb_no));
                    $count = $query->num_rows();
                    $response['no_of_boxes']=$box_count;
                    $response['scan_count']=$count;
                    if($count==$box_count)
                    {
                        $response['is_submit']=true;
                    }
                    else
                    {
                        $response['is_submit']=false;
                    }
                    return $response;
            }
            
        public function get_destination_count($awb_no)
            {
                    $box_count=$this->get_total_order1($awb_no);
                    $query = $this->db->get_where('destination_inscan',array('awb_no'=>$awb_no));
                    $count = $query->num_rows();
                    $response['no_of_boxes']=$box_count;
                    $response['scan_count']=$count;
                    if($count==$box_count)
                    {
                        $response['is_submit']=true;
                    }
                    else
                    {
                        $response['is_submit']=false;
                    }
                    return $response;
            }

        public function get_outscan_count($awb_no)
            {
                    $box_count=$this->get_total_order1($awb_no);
                    $query = $this->db->get_where('source_outscan',array('awb_no'=>$awb_no));
                    $count = $query->num_rows();
                    $response['no_of_boxes']=$box_count;
                    $response['scan_count']=$count;
                    if($count==$box_count)
                    {
                        $response['is_submit']=true;
                    }
                    else
                    {
                        $response['is_submit']=false;
                    }
                    return $response;
            }

        public function get_destination_outscan_count($awb_no)
            {
                    $box_count=$this->get_total_order1($awb_no);
                    $query = $this->db->get_where('destination_outscan',array('awb_no'=>$awb_no));
                    $count = $query->num_rows();
                    $response['no_of_boxes']=$box_count;
                    $response['scan_count']=$count;
                    if($count==$box_count)
                    {
                        $response['is_submit']=true;
                    }
                    else
                    {
                        $response['is_submit']=false;
                    }
                    return $response;
            }

        public function get_total_order1($awb_no)
            {
                    
                    $this->db->select('no_of_boxes');
                    $this->db->from('ship');
                    $this->db->where('AWBno',$awb_no);
                    $query = $this->db->get();
                    $result = $query->row_array();
                    $result=$result['no_of_boxes'];           
                    return $result;
            }
      
        public function get_vehicle()
            {
                    $response = array();
                    $this->db->select('id,vcl_name');
                    $this->db->from('vehicle');       
                    $query = $this->db->get();
                    $result = $query->result_array();
                    
                    $response['status'] = 1;
                    $response['message'] = 'success';
                    $response['data'] = $result;
                    return $response;
            }
        
         public function check_vechile_data($vcl_regno)
            {
                    $response = array();
                    $query = $this->db->get_where('tbl_vehicle_master', array('vcl_regno'=>$vcl_regno));
                    if ($query->num_rows() > 0){
                        $response['status'] = 1;
                        $response['message'] = 'Already Exist';
                        return $response;
                    }
                    else
                    {
                        $response['status'] = 0;
                        $response['message'] = 'new vehicle';
                        return $response;
                    }
            
            }
        
          public function get_status_info($status_name)
            {
                    $this->db->select('*');
                    $this->db->from('tbl_status_master');
                    $this->db->where("status_name",$status_name);
                    $query = $this->db->get();
                    $result = $query->row_array();
                    if($query->num_rows() >= 1) {                
                        return $result;
                    }
                    else
                    {
                            return $result;
                    }   
            }
          
          public function check_AWB_No1($AWB_no)
            {
                $response = array();
                $query = $this->db->get_where('tbl_order_booking', array('AWBno'=>$AWB_no));
                if ($query->num_rows() > 0){
                    $response['status'] = 1;
                    $response['message'] = 'Already Exist';
                    return $response;
                }
                else
                {
                    $response['status'] = 0;
                    $response['message'] = 'new AWB No';
                    return $response;
                }
       
            }
  
    
        
//========================================================================Mcrypt Part============================================
    	public function dec_enc($action, $string) {
            $output = false;
        
            $encrypt_method = "AES-256-CBC";
            $secret_key = 'fd key';
            $secret_iv = 'fd iv';
        
            // hash
            $key = hash('sha256', $secret_key);
            
            // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
            $iv = substr(hash('sha256', $secret_iv), 0, 16);
        
            if( $action == 'encrypt' ) {
                $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
                $output = base64_encode($output);
            }
            else if( $action == 'decrypt' ){
                @$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
            }
        
            return $output;
        }

}