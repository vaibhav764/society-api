<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Admin_Model extends CI_Model {
    public function __construct() {
    }
    //=============================================================SignUp Part============================================
    public function check_table_user($email, $mobile, $table) {
        $response = array();
        $query = $this->db->query('select * from ' . $table . ' WHERE email_id = "' . $email . '" OR contact_no = "' . $mobile . '" ');
        $op = $query->row();
        if ($query->num_rows() >= 1) {
            $response['status'] = 0;
            return $response;
        } else {
            $response['status'] = 1;
            $response['message'] = 'New Customer';
            return $response;
        }
    }
    //====================================================
    public function check_table_user1($email_id, $emailid, $mobile_no, $contact, $table) {
        $response = array();
        $query = $this->db->query('select * from ' . $table . ' WHERE ' . $email_id . ' = "' . $emailid . '" OR ' . $contact . ' = "' . $mobile_no . '" ');
        $op = $query->row();
        if ($query->num_rows() >= 1) {
            $response['status'] = 0;
            $response['message'] = 'Email Already exist';
            return $response;
        } else {
            $response['status'] = 1;
            $response['message'] = 'New Customer';
            return $response;
        }
    }
    public function check_table_user12($c_pancard1, $c_pancard, $c_gst1, $c_gst, $table) {
        $response = array();
        $query = $this->db->query('select * from ' . $table . ' WHERE ' . $c_pancard1 . ' = "' . $c_pancard . '" OR ' . $c_gst1 . ' = "' . $c_gst . '" ');
        $op = $query->row();
        if ($query->num_rows() >= 1) {
            $response['status'] = 0;
            $response['message'] = 'Pan Or Gst No or Account No Already Exist';
            return $response;
        } else {
            $response['status'] = 1;
            $response['message'] = 'New Data';
            return $response;
        }
    }
    //==========================================================Verify Otp======================================================
    //  public function verify_otp($table,$c_id,$otp)
    // {
    //         $this->db->select('*');
    //         $this->db->from($table);
    //         $this->db->where('id', $c_id);
    //         $query = $this->db->get();
    //         $result = $query->result_array();
    //         $db_otp = $result[0]['otp'];
    //         if($db_otp == $otp)
    //         {
    //                 $this->db->set('status', '1'); //value that used to update column
    //                 $this->db->where('id', $c_id); //which row want to upgrade
    //                 $this->db->update($table);
    //                 $response['status']=1;
    //                 $response['message']='OTP verify successfully';
    //         }
    //         else
    //         {
    //                 $response['status']=0;
    //                 $response['message']='OTP mismatch';
    //         }
    //         return $response;
    // }
    //=====================================================Resend Otp===============================================================
    // public function resend_otp($mobile)
    // {
    //     $response = array();
    //     $this->db->select('*');
    //     $this->db->from('tbl_login');
    //     $this->db->where("mobile", $mobile);
    //     $query = $this->db->get();
    //     $result = $query->result_array();
    //     $op = $query->row();
    //     $num_user = $query->num_rows();
    //     if($num_user == 0)
    //     {
    //         $response['status'] = 0;
    //         $response['message'] = "User not found";
    //         return $response;
    //         die;
    //     }
    //     $otp = $result[0]['otp'];
    //     $c_id = $result[0]['id'];
    //       $response['status'] = 1;
    //       $response['message'] = "Otp send successfully";
    //       $response['otp'] = $otp;
    //       $response['c_id'] = $c_id;
    //      return $response;
    // }
    //=================================================Login Part=================================================
    public function login($data = array(), $tablename) {
        $response = array();
        $username = $data['email'];
        $password = $data['password'];
        $encryptedpassword = $this->dec_enc('encrypt', $password);
        $check = is_numeric($username);
        if ($check) {
            $this->db->select('*');
            $this->db->from($tablename);
            $this->db->where("contact_no", $username);
            $this->db->where("password", $encryptedpassword);
            $this->db->where("status", '1');
        } else {
            $this->db->select('*');
            $this->db->from($tablename);
            $this->db->where("email_id", $username);
            $this->db->where("status", '1');
            $this->db->where("password", $encryptedpassword);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $op = $query->row();
        $num_user = $query->num_rows();
        @$status = $result[0]['status'];
        @$id = $result[0]['id'];
        if (!$num_user) {
            $response['status'] = 0;
            $response['message'] = "User Not found";
            return $response;
        }
        $response['status'] = 1;
        $response['message'] = "Login Success";
        $response['data'] = $op;
        return $response;
    }
    //=============================================================Insert Data=========================================
    public function common_data_ins($tablename, $data) {
        $response = array();
        $status = $this->db->insert($tablename, $data);
        if ($status) {
            $response['id'] = $this->db->insert_id();
            $response['status'] = 1;
            $response['message'] = 'Success';
            return $response;
        } else {
            $response['status'] = 0;
            $response['message'] = 'Failed';
            return $response;
        }
    }
    //================================================================Get Data================================================
    public function get_data($tablename, $column, $data) {
        $response = array();
        $this->db->select('*');
        $this->db->from($tablename);
        $this->db->where($column, $data);
        $query = $this->db->get();
        $result = $query->result();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    //=============================================================Get Common Data================================================
    function fetch_data($table) {
        $response = array();
        $this->db->select('*');
        $this->db->from($table);
        $this->db->where('delete_status', '1');
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    function fetch_data1($table) {
        $response = array();
        $this->db->select('*');
        $this->db->from($table);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    //==========================================================Common Delete====================================================
    public function common_delete($table, $data = array()) {
        $response = array();
        $this->db->where('id', $data['id']);
        $this->db->delete($table);
        $response['status'] = 1;
        $response['message'] = 'success';
        return $response;
    }
    //=================================================Multiple Delete=============================================
    public function multiple_delete1($table1, $table2, $id1, $id2, $data) {
        $this->db->where($id1, $data['id']);
        $this->db->delete($table1);
        $this->db->where($id2, $data['id']);
        $this->db->delete($table2);
    }
    public function multiple_delete($table1, $table2, $table3, $table4, $id1, $id2, $id3, $id4, $data) {
        $this->db->where($id1, $data['id']);
        $this->db->delete($table1);
        $this->db->where($id2, $data['id']);
        $this->db->delete($table2);
        $this->db->where($id3, $data['id']);
        $this->db->delete($table3);
        $this->db->where($id4, $data['id']);
        $this->db->delete($table4);
    }
    //===========================================================Common Details On ID==============================================
    function get_common_detail($table, $data = array()) {
        $response = array();
        $this->db->select('*');
        $this->db->from($table);
        $this->db->where("id", $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    //===========================================================Common Details On ID==============================================
    function get_order_count($table, $empid) {
        $response = array();
        $this->db->select('COUNT(fk_empid) as count_order');
        $this->db->from($table);
        $this->db->where("fk_empid", $empid);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    //=================================================User Details On ID==================================================Common Updatte==================================================
    function get_user_details($data = array()) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_user');
        $this->db->where("id", $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    function get_prime_user_details($data = array()) {
        $response = array();
        $this->db->select('tbl_user.user_name,tbl_user.email,tbl_user.mobile_No,tbl_user.status,tbl_user.prime_member,tbl_prime_user_charges.*');
        $this->db->from('tbl_user');
        $this->db->join('tbl_prime_user_charges', 'tbl_user.id=tbl_prime_user_charges.fk_userid', 'inner');
        $this->db->where("tbl_user.id", $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    function get_prime_vendor_details($data = array()) {
        $response = array();
        $this->db->select('tbl_vendor_master.*,tbl_vendor_master.id as vendor_id,tbl_prime_rate.*,tbl_prime_rate.id as prime_vendor_charge_id,states.name,tbl_zone_master.zone');
        $this->db->from('tbl_vendor_master');
        $this->db->join('tbl_prime_rate', 'tbl_prime_rate.fk_vendor_id=tbl_vendor_master.id', 'left');
        $this->db->join('tbl_zone_master', 'tbl_prime_rate.from_zone_id=tbl_zone_master.id', 'left');
        $this->db->join('states', 'tbl_vendor_master.v_state=states.id', 'left');
        $this->db->where("tbl_vendor_master.id", $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_surface_rate($surface, $id) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_prime_rate');
        $this->db->where("transport_type", $surface);
        $this->db->where("fk_vendor_id", $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_rail_rate($rail, $id) {
        $response = array();
        $this->db->select('tbl_prime_rate.*,tbl_zone_master.id as zone_id1,Dropzone.id as zone_id2');
        $this->db->from('tbl_prime_rate');
        $this->db->join('tbl_zone_master', 'tbl_prime_rate.from_zone_id=tbl_zone_master.id', 'left');
        $this->db->join('tbl_zone_master AS Dropzone', 'tbl_prime_rate.to_zone_id=Dropzone.id', 'left');
        $this->db->where("tbl_prime_rate.transport_type", $rail);
        $this->db->where("tbl_prime_rate.fk_vendor_id", $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_air_rate($air, $id) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_prime_rate');
        $this->db->where("transport_type", $air);
        $this->db->where("fk_vendor_id", $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function prime_data($data = array()) {
        $this->db->select('tbl_prime_vendor_charges.*,tbl_vendor_master.id as vendor_id');
        $this->db->from('tbl_prime_vendor_charges');
        $this->db->join('tbl_vendor_master', 'tbl_prime_vendor_charges.fk_vendorid=tbl_vendor_master.id', 'left');
        $this->db->where("tbl_prime_vendor_charges.id", $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    //====================Common Updatte==================================================
    public function common_data_update($tablename, $data, $key, $id) {
        $response = array();
        $this->db->where($id, $key);
        $status = $this->db->update($tablename, $data);
        if ($status) {
            $response['status'] = 1;
            $response['message'] = 'Success';
            return $response;
        } else {
            $response['status'] = 0;
            $response['message'] = 'Failed';
            return $response;
        }
    }
    //======================================================Two Table Join API=======================================
    public function get_join_list($select, $table1, $table2, $id1, $id2) {
        $response = array();
        $this->db->select($select);
        $this->db->from($table1);
        $this->db->join($table2, $table2 . '.' . $id2 . '=' . $table1 . '.' . $id1, 'left');
        $this->db->where($table1 . '.' . 'delete_status', '1');
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_join_list1($select, $table1, $table2, $id1, $id2) {
        $response = array();
        $this->db->select($select);
        $this->db->from($table1);
        $this->db->join($table2, $table2 . '.' . $id2 . '=' . $table1 . '.' . $id1, 'left');
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_join_list_groupby() {
        $response = array();
        $this->db->select('GROUP_CONCAT(tbl_branch.branch_address)branch_address,GROUP_CONCAT(tbl_branch.branch_code)branch_code,GROUP_CONCAT(tbl_branch.id)id,GROUP_CONCAT(tbl_branch.branch_city)branch_city,GROUP_CONCAT(tbl_branch.branch_state)branch_state,GROUP_CONCAT(tbl_branch.branch_pin)branch_pin,GROUP_CONCAT(tbl_branch.status)status,GROUP_CONCAT(tbl_branch.branchmanager_name)branchmanager_name,GROUP_CONCAT(tbl_branch.email)email,GROUP_CONCAT(tbl_branch.contact_no)contact_no,tbl_company_master.c_name');
        $this->db->from('tbl_branch');
        $this->db->join('tbl_company_master', 'tbl_branch.c_id=tbl_company_master.id', 'left');
        $this->db->group_by('tbl_company_master.id');
        $this->db->where('tbl_branch.delete_status', 1);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_join_list_groupby1($data = array()) {
        $response = array();
        $this->db->select('GROUP_CONCAT(tbl_branch.branch_address)branch_address,GROUP_CONCAT(tbl_branch.branch_code)branch_code,GROUP_CONCAT(tbl_branch.id)id,GROUP_CONCAT(tbl_branch.branch_city)branch_city,GROUP_CONCAT(tbl_branch.branch_state)branch_state,GROUP_CONCAT(tbl_branch.branch_pin)branch_pin,GROUP_CONCAT(tbl_branch.status)status,GROUP_CONCAT(tbl_branch.branchmanager_name)branchmanager_name,GROUP_CONCAT(tbl_branch.email)email,GROUP_CONCAT(tbl_branch.contact_no)contact_no,tbl_company_master.c_name');
        $this->db->from('tbl_branch');
        $this->db->join('tbl_company_master', 'tbl_branch.c_id=tbl_company_master.id', 'left');
        $this->db->where('tbl_branch.c_id', $data['id']);
        $this->db->where('tbl_branch.delete_status', 1);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_join_list_on_id($select, $table1, $table2, $id1, $id2, $id) {
        $this->db->select($select);
        $this->db->from($table1);
        $this->db->join($table2, $table1 . '.' . $id1 . '=' . $table2 . '.' . $id2, 'left');
        $this->db->where($table1 . '.' . 'id', $id); //$id
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_rate_on_id($table, $id) {
        $this->db->select('tbl_rate.*,tbl_company_master.c_name');
        $this->db->from('tbl_rate');
        $this->db->join('tbl_company_master', 'tbl_rate.c_id=tbl_company_master.id', 'left');
        $this->db->where('tbl_rate.c_id', $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_zone_on_id($table, $id) {
        $this->db->select('tbl_zone_master.*,tbl_company_master.c_name');
        $this->db->from($table);
        $this->db->join('tbl_company_master', 'tbl_zone_master.c_id=tbl_company_master.id', 'left');
        $this->db->where('tbl_zone_master.c_id', $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_zone_list($table) {
        $this->db->select('tbl_zone_master.*,tbl_company_master.c_name');
        $this->db->from($table);
        $this->db->join('tbl_company_master', 'tbl_zone_master.c_id=tbl_company_master.id', 'left');
        // $this->db->where('tbl_zone_master.c_id',$id);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    function get_common_detail_on_id($table, $id, $data = array()) {
        $response = array();
        $this->db->select('*');
        $this->db->from($table);
        $this->db->where($id, $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    //===================================================Vendor details========================================
    public function get_vendor_details($data = array()) {
        $response = array();
        $this->db->select('tbl_vendor_master.*,tbl_vendor_master.id as vendor_id,tbl_prime_rate.*,tbl_prime_rate.id as prime_id,states.name,tbl_employee_master.emp_name');
        $this->db->from('tbl_vendor_master');
        $this->db->join('tbl_prime_rate', 'tbl_prime_rate.fk_vendor_id=tbl_vendor_master.id', 'left');
        $this->db->join('states', 'tbl_vendor_master.v_state=states.id', 'left');
        $this->db->join('tbl_employee_master', 'tbl_vendor_master.assign_employee=tbl_employee_master.id', 'left');
        $this->db->where('tbl_vendor_master.id', $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_vendor_details1($data = array()) {
        $response = array();
        $this->db->select('tbl_vendor_master.*,tbl_vendor_master.id as vendor_id,states.name,tbl_employee_master.emp_name');
        $this->db->from('tbl_vendor_master');
        $this->db->join('states', 'tbl_vendor_master.v_state=states.id', 'left');
        $this->db->join('tbl_employee_master', 'tbl_vendor_master.assign_employee=tbl_employee_master.id', 'left');
        $this->db->where('tbl_vendor_master.id', $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function simplejoin1($data) {
        $response = array();
        $this->db->select('id,c_name');
        $this->db->from('tbl_company_master');
        $this->db->where('tbl_company_master.id', $data);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_vendor_details_on_id($data = array()) {
        $this->db->select('tbl_vendor_master.id as vendor_id,tbl_vendor_master.*');
        $this->db->from('tbl_vendor_master');
        $this->db->where("tbl_vendor_master.c_id", $data['id']);
        $this->db->order_by('tbl_vendor_master.id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_vendor_details_on_branch($data = array()) {
        $this->db->select('tbl_vendor_master.id as vendor_id,tbl_vendor_master.*');
        $this->db->from('tbl_vendor_master');
        $this->db->where("tbl_vendor_master.branch_id", $data['id']);
        $this->db->order_by('tbl_vendor_master.id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    //=========================================================Company Status=======================================================
    public function company_status($email) {
        $this->db->select('status')->from('tbl_company_master')->where('c_prsn_email', $email);
        $query = $this->db->get();
        return $query->result();
    }
    public function get_company() {
        $query = $this->db->query('SELECT id,c_name FROM tbl_company_master WHERE status="1" AND delete_status="1"');
        return $query->result();
    }
    public function get_company_on_vechile() {
        $query = $this->db->query('SELECT id,c_name FROM tbl_company_master WHERE status="1" AND vechile_status="1"');
        return $query->result();
    }
    //====================================================Employee Status========================================
    public function employee_status($email) {
        $this->db->select('status')->from('tbl_employee_master')->where('emp_email', $email);
        $query = $this->db->get();
        return $query->result();
    }
    //================================================Vendor Status================================================
    public function vendor_status($email) {
        $this->db->select('status')->from('tbl_vendor_master')->where('v_prsn_email', $email);
        $query = $this->db->get();
        return $query->result();
    }
    public function view_employee_details($data = array()) {
        $response = array();
        $this->db->select('tbl_employee_master.*,tbl_employee_master.id as emp_id,tbl_company_master.c_name,tbl_designation_master.designation,tbl_employee_designation.*,tbl_employee_designation.id as emp_designation_id,states.name');
        $this->db->from('tbl_employee_master');
        $this->db->join('tbl_company_master', 'tbl_employee_master.c_id=tbl_company_master.id', 'left');
        $this->db->join('tbl_employee_designation', 'tbl_employee_designation.emp_id=tbl_employee_master.id', 'left');
        $this->db->join('tbl_designation_master', 'tbl_employee_designation.desg_id=tbl_designation_master.id', 'left');
        $this->db->join('states', 'tbl_employee_master.emp_state=states.id', 'left');
        $this->db->where("tbl_employee_master.id", @$data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function simplejoin2($data) {
        $response = array();
        $this->db->select('id,designation');
        $this->db->from('tbl_designation_master');
        $this->db->where('tbl_designation_master.id', $data);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    //==================================================Vehicle Status========================================
    public function vehicle_status($id) {
        $this->db->select('status')->from('tbl_vehicle_master')->where('id', $id);
        $query = $this->db->get();
        return $query->result();
    }
    //=====================================================Branch Status=======================================
    public function branch_status($email) {
        $this->db->select('status')->from('tbl_branch')->where('email', $email);
        $query = $this->db->get();
        return $query->result();
    }
    //=========================================================user Status=======================================================
    public function getState() {
        $query = $this->db->query('SELECT * FROM  states');
        return $query->result();
    }
    public function getCity() {
        $query = $this->db->query('SELECT * FROM  tbl_cities');
        return $query->result();
    }
    public function getcities1() {
        $response = array();
        $this->db->distinct();
        $this->db->select('city_name');
        $this->db->from('tbl_pincode');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function user_view() {
        $response = array();
        $this->db->select('tbl_order_booking.*,tbl_order_booking.id as order_id,tbl_order_booking.created_at as order_date,tbl_user.id as user_id,tbl_user.user_name,countries.name as country_name1,states.name as state_name1,Dropcountry.name as country_name2,Dropstate.name as state_name2,tbl_order_status.*,tbl_order_status.id as order_status');
        $this->db->from('tbl_order_booking');
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
        $this->db->join('tbl_order_status', 'tbl_order_status.fk_oid=tbl_order_booking.id', 'left');
        $this->db->join('tbl_user', 'tbl_order_booking.fk_id=tbl_user.id', 'left');
        $this->db->join('countries', 'tbl_order_booking.pickup_country=countries.id', 'left');
        $this->db->join('states', 'tbl_order_booking.pickup_state=states.id', 'left');
        $this->db->join('countries AS Dropcountry', 'tbl_order_booking.drop_country=Dropcountry.id', 'left');
        $this->db->join('states AS Dropstate', 'tbl_order_booking.drop_state=Dropstate.id', 'left');
        $this->db->where("tbl_order_booking.type", 'User');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function user_view1($data = array()) {
        $response = array();
        $this->db->select('tbl_order_booking.*,tbl_order_booking.id as order_id,tbl_order_booking.created_at as order_date,tbl_user.id as user_id,tbl_user.user_name,countries.name as country_name1,states.name as state_name1,Dropcountry.name as country_name2,Dropstate.name as state_name2,tbl_order_status.*,tbl_order_status.id as order_status');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_order_status', 'tbl_order_status.fk_oid=tbl_order_booking.id', 'left');
        $this->db->join('tbl_user', 'tbl_order_booking.fk_id=tbl_user.id', 'left');
        $this->db->join('countries', 'tbl_order_booking.pickup_country=countries.id', 'left');
        $this->db->join('states', 'tbl_order_booking.pickup_state=states.id', 'left');
        $this->db->join('countries AS Dropcountry', 'tbl_order_booking.drop_country=Dropcountry.id', 'left');
        $this->db->join('states AS Dropstate', 'tbl_order_booking.drop_state=Dropstate.id', 'left');
        $this->db->where("tbl_order_booking.fk_id", $data['id']);
        $this->db->where("tbl_order_booking.type", 'User');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function view_user_detail($data = array()) {
        $response = array();
        $this->db->select('tbl_order_booking.*,tbl_order_booking.id as order_id,tbl_user.id as user_id,tbl_user.user_name,countries.name as country_name,states.name as state_name,tbl_company_master.c_name');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_user', 'tbl_order_booking.fk_id=tbl_user.id', 'left');
        $this->db->join('countries', 'tbl_order_booking.pickup_country=countries.id', 'left');
        $this->db->join('tbl_company_master', 'tbl_order_booking.c_id=tbl_company_master.id', 'left');
        $this->db->join('states', 'tbl_order_booking.pickup_state=states.id', 'left');
        $this->db->where("tbl_order_booking.id", $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function customer_status($email) {
        $this->db->select('status')->from('tbl_user')->where('email', $email);
        $query = $this->db->get();
        return $query->result();
    }
    public function user_status($id) {
        $this->db->select('status')->from('tbl_user')->where('id', $id);
        $query = $this->db->get();
        return $query->result();
    }
    public function get_user() {
        $query = $this->db->query('SELECT * FROM tbl_user WHERE status="1"');
        return $query->result();
    }
    public function prime_user_status($data = array()) {
        $this->db->select('prime_member')->from('tbl_user')->where('id', $data['id']);
        $query = $this->db->get();
        return $query->result();
    }
    public function prime_user() {
        $query = $this->db->query('SELECT * FROM tbl_user WHERE status="1" and prime_member="1"');
        return $query->result();
    }
    public function prime_vendor_status($data = array()) {
        $this->db->select('prime_member')->from('tbl_vendor_master')->where('id', $data['id']);
        $query = $this->db->get();
        return $query->result();
    }
    public function prime_vendor() {
        $query = $this->db->query('SELECT * FROM tbl_vendor_master WHERE status="1" and prime_member="1"');
        return $query->result();
    }
    public function order_view() {
        $response = array();
        $this->db->select('tbl_order_booking.*,tbl_vendor_master.id as vendor_id,tbl_vendor_master.v_company,countries.name as country_name1,states.name as state_name1,Dropcountry.name as country_name2,Dropstate.name as state_name2');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_vendor_master', 'tbl_order_booking.fk_id=tbl_vendor_master.id', 'left');
        $this->db->join('countries', 'tbl_order_booking.pickup_country=countries.id', 'left');
        $this->db->join('states', 'tbl_order_booking.pickup_state=states.id', 'left');
        $this->db->join('countries AS Dropcountry', 'tbl_order_booking.drop_country=Dropcountry.id', 'left');
        $this->db->join('states AS Dropstate', 'tbl_order_booking.drop_state=Dropstate.id', 'left');
        $this->db->order_by('tbl_order_booking.id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function order_view1($data = array()) {
        $response = array();
        $this->db->select('tbl_order_booking.*,tbl_order_booking.id as order_id,tbl_order_booking.created_at as order_date,tbl_order_status.*,tbl_order_status.id as order_status_id,tbl_vendor_master.id as vendor_id,tbl_vendor_master.v_company,countries.name as country_name1,states.name as state_name1,Dropcountry.name as country_name2,Dropstate.name as state_name2,GROUP_CONCAT(tbl_order_status.order_status) as order_status_name,GROUP_CONCAT(tbl_status_master.status_name)as status_name');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_order_status', 'tbl_order_status.fk_oid=tbl_order_booking.id', 'left');
        $this->db->join('tbl_status_master', 'tbl_order_status.order_status=tbl_status_master.id', 'left');
        $this->db->join('tbl_vendor_master', 'tbl_order_booking.fk_id=tbl_vendor_master.id', 'left');
        $this->db->join('countries', 'tbl_order_booking.pickup_country=countries.id', 'left');
        $this->db->join('states', 'tbl_order_booking.pickup_state=states.id', 'left');
        $this->db->join('countries AS Dropcountry', 'tbl_order_booking.drop_country=Dropcountry.id', 'left');
        $this->db->join('states AS Dropstate', 'tbl_order_booking.drop_state=Dropstate.id', 'left');
        $this->db->where("tbl_order_booking.fk_id", $data['id']);
        // $this->db->where("tbl_order_booking.type", 'Vendor');
        $this->db->group_by('tbl_order_booking.AWBno');
        $this->db->order_by('tbl_order_booking.id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    // public function view_order_details($data = array())
    // {
    //         $response = array();
    //         $this->db->select('tbl_order_booking.*,tbl_order_booking.c_id as comp_id,tbl_order_booking.grand_total as total,tbl_order_booking.created_at as order_date,tbl_order_booking.id as order_id,tbl_order_details.*,tbl_order_details.id as details_id,tbl_vendor_master.id as vendor_id,tbl_vendor_master.v_company,tbl_prime_vendor_charges.*,countries.name as country_name,states.name as state_name,tbl_company_master.c_name,tbl_company_master.*,tbl_company_master.id as company_id,tbl_order_status.*,tbl_order_status.id as order_status_id');
    //         $this->db->from('tbl_order_booking');
    //         $this->db->join('tbl_vendor_master','tbl_order_booking.fk_id=tbl_vendor_master.id','left');
    //         $this->db->join('tbl_order_details','tbl_order_details.order_id=tbl_order_booking.id','left');
    //         $this->db->join('tbl_prime_vendor_charges','tbl_prime_vendor_charges.fk_vendorid=tbl_vendor_master.id','left');
    //         $this->db->join('tbl_order_status','tbl_order_status.fk_oid=tbl_order_booking.id','left');
    //         $this->db->join('countries','tbl_order_booking.pickup_country=countries.id','left');
    //         $this->db->join('states','tbl_order_booking.pickup_state=states.id','left');
    //         $this->db->join('tbl_company_master','tbl_order_booking.c_id=tbl_company_master.id','left');
    //         $this->db->where("tbl_order_booking.id",$data['id']);
    //         $this->db->order_by('tbl_order_booking.id','DESC');
    //         $query = $this->db->get();
    //         $result = $query->result_array();
    //         $response['status'] = 1;
    //         $response['message'] = 'success';
    //         $response['data'] = $result;
    //         return $response;
    // }
    // public function view_order_details($data = array())
    // {
    //         $response = array();
    //         $this->db->select('tbl_order_booking.*,tbl_order_booking.c_id as comp_id,tbl_order_booking.grand_total as total,tbl_order_booking.id as order_id,tbl_order_details.*,tbl_order_details.id as details_id,tbl_vendor_master.id as vendor_id,tbl_vendor_master.v_company,tbl_prime_vendor_charges.*,countries.name as country_name,states.name as state_name,tbl_company_master.c_name,tbl_company_master.*,tbl_company_master.id as company_id,tbl_order_status.*,tbl_order_status.id as order_status_id,tbl_status_master.status_name,tbl_billing_details.*');
    //         $this->db->from('tbl_order_booking');
    //         $this->db->join('tbl_vendor_master','tbl_order_booking.fk_id=tbl_vendor_master.id','left');
    //         $this->db->join('tbl_order_details','tbl_order_details.order_id=tbl_order_booking.id','left');
    //         $this->db->join('tbl_prime_vendor_charges','tbl_prime_vendor_charges.fk_vendorid=tbl_vendor_master.id','left');
    //         $this->db->join('tbl_order_status','tbl_order_status.fk_oid=tbl_order_booking.id','left');
    //         $this->db->join('countries','tbl_order_booking.pickup_country=countries.id','left');
    //         $this->db->join('states','tbl_order_booking.pickup_state=states.id','left');
    //         $this->db->join('tbl_company_master','tbl_order_booking.c_id=tbl_company_master.id','left');
    //         $this->db->join('tbl_status_master','tbl_order_status.order_status=tbl_status_master.id','left');
    //         $this->db->join('tbl_billing_details','tbl_billing_details.fk_order_id=tbl_order_booking.id','left');
    //         $this->db->where("tbl_order_booking.id",$data['id']);
    //         $this->db->order_by('tbl_order_booking.id','DESC');
    //         $query = $this->db->get();
    //         $result = $query->row_array();
    //         $response['status'] = 1;
    //         $response['message'] = 'success';
    //         $response['data'] = $result;
    //         return $response;
    // }
    public function view_order_details($data = array()) {
        $response = array();
        $this->db->select('tbl_order_booking.*,tbl_order_booking.c_id as comp_id,tbl_order_booking.grand_total as total,tbl_order_booking.id as order_id,tbl_vendor_master.id as vendor_id,tbl_vendor_master.v_company,tbl_prime_rate.*,tbl_company_master.c_name,tbl_company_master.*,tbl_company_master.id as company_id,tbl_billing_details.*');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_vendor_master', 'tbl_order_booking.fk_id=tbl_vendor_master.id', 'left');
        $this->db->join('tbl_prime_rate', 'tbl_prime_rate.fk_vendor_id=tbl_vendor_master.id', 'left');
        $this->db->join('tbl_company_master', 'tbl_order_booking.c_id=tbl_company_master.id', 'left');
        $this->db->join('tbl_billing_details', 'tbl_billing_details.fk_order_id=tbl_order_booking.id', 'left');
        $this->db->where("tbl_order_booking.id", $data['id']);
        $this->db->order_by('tbl_order_booking.id', 'DESC');
        $query = $this->db->get();
        $result = $query->row_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function view_map_barcode($awb_no) {
        $response = array();
        $this->db->select('tbl_map_barcode.*');
        $this->db->from('tbl_map_barcode');
        $this->db->where("awb_no", $awb_no);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    // public function view_order_details1($data = array())
    // {
    //     $response = array();
    //      $this->db->select('tbl_order_booking.*,tbl_order_booking.grand_total as total,tbl_order_booking.created_at as order_date,tbl_order_booking.id as order_id,tbl_order_details.*,tbl_order_details.id as details_id,tbl_vendor_master.id as vendor_id,tbl_vendor_master.v_company,tbl_prime_vendor_charges.*,countries.name as country_name,states.name as state_name,tbl_company_master.c_name,tbl_company_master.*,tbl_company_master.id as company_id,tbl_order_status.*,tbl_order_status.id as order_status_id');
    //         $this->db->from('tbl_order_booking');
    //         $this->db->join('tbl_vendor_master','tbl_order_booking.fk_id=tbl_vendor_master.id','left');
    //         $this->db->join('tbl_order_details','tbl_order_details.order_id=tbl_order_booking.id','left');
    //         $this->db->join('tbl_prime_vendor_charges','tbl_prime_vendor_charges.fk_vendorid=tbl_vendor_master.id','left');
    //         $this->db->join('tbl_order_status','tbl_order_status.fk_oid=tbl_order_booking.id','left');
    //         $this->db->join('countries','tbl_order_booking.pickup_country=countries.id','left');
    //         $this->db->join('states','tbl_order_booking.pickup_state=states.id','left');
    //         $this->db->join('tbl_company_master','tbl_order_booking.c_id=tbl_company_master.id','left');
    //         $this->db->where("tbl_order_booking.id",$data['id']);
    //         $this->db->order_by('tbl_order_booking.id','DESC');
    //     $query = $this->db->get();
    //     $result = $query->result_array();
    //     $response['status'] = 1;
    //     $response['message'] = 'success';
    //     $response['data'] = $result;
    //     return $response;
    // }
    public function view_order_details1($data = array()) {
        $response = array();
        $this->db->select('tbl_order_booking.*,tbl_order_booking.id as order_id,tbl_vendor_master.id as vendor_id,tbl_vendor_master.v_company,tbl_prime_vendor_charges.*,countries.name as country_name,states.name as state_name,tbl_company_master.*,tbl_company_master.id as company_id,tbl_order_status.*,tbl_order_status.id as order_status_id,tbl_billing_details.*');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_vendor_master', 'tbl_order_booking.fk_id=tbl_vendor_master.id', 'left');
        $this->db->join('tbl_order_details', 'tbl_order_details.order_id=tbl_order_booking.id', 'left');
        $this->db->join('tbl_prime_vendor_charges', 'tbl_prime_vendor_charges.fk_vendorid=tbl_vendor_master.id', 'left');
        $this->db->join('tbl_order_status', 'tbl_order_status.fk_oid=tbl_order_booking.id', 'left');
        $this->db->join('countries', 'tbl_order_booking.pickup_country=countries.id', 'left');
        $this->db->join('states', 'tbl_order_booking.pickup_state=states.id', 'left');
        $this->db->join('tbl_company_master', 'tbl_order_booking.c_id=tbl_company_master.id', 'left');
        $this->db->join('tbl_billing_details', 'tbl_billing_details.fk_order_id=tbl_order_booking.id', 'left');
        $this->db->where("tbl_order_booking.id", $data['id']);
        $this->db->order_by('tbl_order_booking.id', 'DESC');
        $query = $this->db->get();
        $result = $query->row_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function superadmin_order_view() {
        $response = array();
        $this->db->select('tbl_order_booking.*,tbl_vendor_master.id as vendor_id,tbl_vendor_master.v_company,countries.name as country_name1,states.name as state_name1,Dropcountry.name as country_name2,Dropstate.name as state_name2');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_vendor_master', 'tbl_order_booking.fk_id=tbl_vendor_master.id', 'left');
        $this->db->join('countries', 'tbl_order_booking.pickup_country=countries.id', 'left');
        $this->db->join('states', 'tbl_order_booking.pickup_state=states.id', 'left');
        $this->db->join('countries AS Dropcountry', 'tbl_order_booking.drop_country=Dropcountry.id', 'left');
        $this->db->join('states AS Dropstate', 'tbl_order_booking.drop_state=Dropstate.id', 'left');
        $this->db->order_by('tbl_order_booking.id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function total_order_view() {
        $response = array();
        $this->db->select('tbl_order_booking.*,tbl_company_master.c_name,tbl_employee_master.emp_name,tbl_vendor_master.id as vendor_id,tbl_vendor_master.v_company,countries.name as country_name1,states.name as state_name1,Dropcountry.name as country_name2,Dropstate.name as state_name2');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_vendor_master', 'tbl_order_booking.fk_id=tbl_vendor_master.id', 'left');
        $this->db->join('countries', 'tbl_order_booking.pickup_country=countries.id', 'left');
        $this->db->join('states', 'tbl_order_booking.pickup_state=states.id', 'left');
        $this->db->join('countries AS Dropcountry', 'tbl_order_booking.drop_country=Dropcountry.id', 'left');
        $this->db->join('states AS Dropstate', 'tbl_order_booking.drop_state=Dropstate.id', 'left');
        $this->db->join('tbl_employee_master', 'tbl_order_booking.fk_empid=tbl_employee_master.id', 'left');
        $this->db->join('tbl_company_master', 'tbl_order_booking.c_id=tbl_company_master.id', 'left');
        $this->db->order_by('tbl_order_booking.id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function total_order_view1($id) {
        $response = array();
        $this->db->select('tbl_order_booking.*,tbl_company_master.c_name,tbl_employee_master.emp_name,tbl_vendor_master.id as vendor_id,tbl_vendor_master.v_company,countries.name as country_name1,states.name as state_name1,Dropcountry.name as country_name2,Dropstate.name as state_name2');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_vendor_master', 'tbl_order_booking.fk_id=tbl_vendor_master.id', 'left');
        $this->db->join('countries', 'tbl_order_booking.pickup_country=countries.id', 'left');
        $this->db->join('states', 'tbl_order_booking.pickup_state=states.id', 'left');
        $this->db->join('countries AS Dropcountry', 'tbl_order_booking.drop_country=Dropcountry.id', 'left');
        $this->db->join('states AS Dropstate', 'tbl_order_booking.drop_state=Dropstate.id', 'left');
        $this->db->join('tbl_employee_master', 'tbl_order_booking.fk_empid=tbl_employee_master.id', 'left');
        $this->db->join('tbl_company_master', 'tbl_order_booking.c_id=tbl_company_master.id', 'left');
        $this->db->where('tbl_order_booking.c_id', $id);
        $this->db->where('tbl_order_booking.type!=', 'User');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
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
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            @$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
    public function getCountry() {
        $query = $this->db->query('SELECT * FROM  countries');
        return $query->result();
    }
    public function getStates($data) {
        $this->db->select('*');
        $this->db->from('states');
        $this->db->where($data);
        $result_set = $this->db->get();
        if ($result_set->num_rows() > 0) {
            return $result_set->result();
        } else {
            return FALSE;
        }
    }
    public function getcities($data) {
        $this->db->select('*');
        $this->db->from('cities');
        $this->db->where($data);
        $result_set = $this->db->get();
        if ($result_set->num_rows() > 0) {
            return $result_set->result();
        } else {
            return FALSE;
        }
    }
    public function get_cust_name_on_cid($data) {
        $this->db->select('*');
        $this->db->from('tbl_vendor_master');
        $this->db->where($data);
        $result_set = $this->db->get();
        if ($result_set->num_rows() > 0) {
            return $result_set->result();
        } else {
            return FALSE;
        }
    }
    public function get_user_type_details($data) {
        $this->db->select('*');
        $this->db->from('tbl_login');
        $this->db->where($data);
        $result_set = $this->db->get();
        if ($result_set->num_rows() > 0) {
            return $result_set->result();
        } else {
            return FALSE;
        }
    }
    public function get_superadmin_user_details($data) {
        $this->db->select('*');
        $this->db->from('tbl_vendor_master');
        $this->db->where($data);
        $result_set = $this->db->get();
        if ($result_set->num_rows() > 0) {
            return $result_set->result();
        } else {
            return FALSE;
        }
    }
    public function get_superadmin_details($pickup_city) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_zone_master');
        $this->db->where("FIND_IN_SET('$pickup_city',cities) !=", 0);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    function view_branch($table, $data = array()) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_branch');
        $this->db->where("id", $data['id']);
        $this->db->group_by('c_id');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    //===================================================Designation=============================
    public function get_designation() {
        $query = $this->db->query('SELECT * FROM tbl_designation_master');
        return $query->result();
    }
    public function getemployee() {
        $response = array();
        $this->db->select('tbl_employee_master.id,emp_name');
        $this->db->from('tbl_employee_master');
        $this->db->join('tbl_employee_designation', 'tbl_employee_master.id=tbl_employee_designation.emp_id', 'INNER');
        $this->db->join('tbl_designation_master', 'tbl_employee_designation.desg_id=tbl_designation_master.id', 'INNER');
        $this->db->where('tbl_designation_master.designation', "Field Executive");
        // $this->db->where('tbl_employee_master.c_id',$data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    //========================================Check Data====================================
    public function check_data($c_id, $trans_type, $kg_box) {
        $response = array();
        $query = $this->db->get_where('tbl_rate', array('c_id' => $c_id, 'kg_box' => $kg_box, 'types' => $trans_type));
        if ($query->num_rows() > 0) {
            $response['status'] = 1;
            $response['message'] = 'Already Exist';
            return $response;
        } else {
            $response['status'] = 0;
            $response['message'] = 'New Rate';
            return $response;
        }
    }
    //========================================Check Designation====================================
    public function check_designation($designation) {
        $response = array();
        $query = $this->db->get_where('tbl_designation_master', array('designation' => $designation));
        if ($query->num_rows() > 0) {
            $response['status'] = 1;
            $response['message'] = 'success';
            return $response;
        } else {
            $response['status'] = 0;
            $response['message'] = 'new user';
            return $response;
        }
    }
    //========================================Check Zone====================================
    public function check_zone($c_id, $zone_code, $zone) {
        $response = array();
        $query = $this->db->get_where('tbl_zone_master', array('c_id' => $c_id, 'zone_code' => $zone_code, 'zone' => $zone));
        if ($query->num_rows() > 0) {
            $response['status'] = 1;
            $response['message'] = 'Already Exist';
            return $response;
        } else {
            $response['status'] = 0;
            $response['message'] = 'new Zone';
            return $response;
        }
    }
    //==============================================Vendor Type===================================
    public function check_vendor_type($vendor_type) {
        $response = array();
        $query = $this->db->get_where('tbl_vendor_type', array('vendor_type' => $vendor_type));
        if ($query->num_rows() > 0) {
            $response['status'] = 1;
            $response['message'] = 'Already Exits';
            return $response;
        } else {
            $response['status'] = 0;
            $response['message'] = 'new Type';
            return $response;
        }
    }
    public function get_vendor_type() {
        $query = $this->db->query('SELECT * FROM  tbl_vendor_type');
        return $query->result();
    }
    //===================================================Rate Part==============================
    public function getzone() {
        $query = $this->db->query('SELECT * FROM  tbl_zone_master ');
        return $query->result();
    }
    public function getzone2($id) {
        $query = $this->db->query('SELECT * FROM  tbl_zone_master where c_id="' . $id . '"');
        return $query->result();
    }
    public function getzone1($data = array()) {
        $response = array();
        $this->db->select('id as id,zone as zone_data, cities as zone_cities, area');
        $this->db->from('tbl_zone_master');
        $this->db->where('c_id', $data['c_id']);
        $this->db->where('type', $data['type']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_details($data = array()) {
        $response = array();
        $this->db->select('tbl_zone_master.*');
        $this->db->from('tbl_zone_master');
        $this->db->where('id', $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_zone_rate($data = array()) {
        $response = array();
        $this->db->select('tbl_zone_master.*'); //,tbl_rate_master.*,tbl_rate_master.id as rate_id
        $this->db->from('tbl_zone_master');
        // $this->db->join('tbl_rate_master','tbl_rate_master.zone=tbl_zone_master.zone');
        $this->db->where('tbl_zone_master.zone', $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_rate_by_city1($pickup_city) {
        $response = array();
        $this->db->select('tbl_zone_master.*,tbl_prime_vendor_charges.*,tbl_prime_vendor_charges.id as vendor_charges_id,tbl_rate_master.*,tbl_rate_master.id as rate_master_id');
        $this->db->from('tbl_zone_master');
        $this->db->join('tbl_prime_vendor_charges', 'tbl_prime_vendor_charges.zone=tbl_zone_master.zone', 'left');
        $this->db->join('tbl_rate_master', 'tbl_rate_master.zone=tbl_zone_master.zone', 'left');
        $this->db->where("FIND_IN_SET('$pickup_city',tbl_zone_master.cities) !=", 0);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_rate_by_city($drop_city) {
        $response = array();
        $this->db->select('tbl_zone_master.*,tbl_prime_vendor_charges.*,tbl_prime_vendor_charges.id as vendor_charges_id,tbl_rate_master.*,tbl_rate_master.id as rate_master_id');
        $this->db->from('tbl_zone_master');
        $this->db->join('tbl_prime_vendor_charges', 'tbl_prime_vendor_charges.zone=tbl_zone_master.zone', 'left');
        $this->db->join('tbl_rate_master', 'tbl_rate_master.zone=tbl_zone_master.zone', 'left');
        $this->db->where("FIND_IN_SET('$drop_city',tbl_zone_master.cities) !=", 0);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_customer_details($id, $drop_city) {
        $response = array();
        $this->db->select('tbl_vendor_master.*,tbl_vendor_master.id as vendor_id,tbl_prime_vendor_charges.*,tbl_prime_vendor_charges.id as prime_id,tbl_zone_master.*,tbl_zone_master.id as zone_id,tbl_rate_master.*,tbl_rate_master.id as rate_id');
        $this->db->from('tbl_vendor_master');
        $this->db->join('tbl_prime_vendor_charges', 'tbl_prime_vendor_charges.fk_vendorid=tbl_vendor_master.id', 'left');
        $this->db->join('tbl_zone_master', 'tbl_prime_vendor_charges.zone=tbl_zone_master.zone', 'left');
        $this->db->join('tbl_rate_master', 'tbl_rate_master.zone=tbl_zone_master.zone', 'left');
        $this->db->where('tbl_vendor_master.id', $id);
        $this->db->where("FIND_IN_SET('Mumbai',tbl_zone_master.cities) !=", 0);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_customer_details1($drop_city) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_rate_master');
        $this->db->where("FIND_IN_SET('$drop_city',tbl_rate_master.cities) !=", 0);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_rate_by_city2($drop_city) {
        $response = array();
        $this->db->select('tbl_rate_master.*');
        $this->db->from('tbl_rate_master');
        $this->db->where("FIND_IN_SET('$drop_city',tbl_rate_master.cities) !=", 0);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_data_on_zone($drop_zone) {
        $this->db->select('tbl_zone_master.*,tbl_prime_vendor_charges.*,tbl_prime_vendor_charges.id as prime_vendor_charges,tbl_rate_master.*, tbl_rate_master.id as rate_id');
        $this->db->from('tbl_zone_master');
        $this->db->join('tbl_prime_vendor_charges', 'tbl_prime_vendor_charges.zone=tbl_zone_master.zone', 'left');
        $this->db->join('tbl_rate_master', 'tbl_rate_master.zone=tbl_zone_master.zone', 'left');
        $this->db->where('tbl_zone_master.zone', $drop_zone);
    }
    public function get_data_on_zone1($pickup_zone) {
        $this->db->select('tbl_zone_master.*,tbl_prime_vendor_charges.*,tbl_prime_vendor_charges.id as prime_vendor_charges,tbl_rate_master.*, tbl_rate_master.id as rate_id');
        $this->db->from('tbl_zone_master');
        $this->db->join('tbl_prime_vendor_charges', 'tbl_prime_vendor_charges.zone=tbl_zone_master.zone', 'left');
        $this->db->join('tbl_rate_master', 'tbl_rate_master.zone=tbl_zone_master.zone', 'left');
        $this->db->where('tbl_zone_master.zone', $pickup_zone);
    }
    public function get_customer() {
        $query = $this->db->query('SELECT * FROM  tbl_vendor_master WHERE c_id=0 AND status=1');
        return $query->result();
    }
    public function get_cust($id) {
        $query = $this->db->query('SELECT id, v_company FROM  tbl_vendor_master WHERE c_id=' . $id . '');
        return $query->result();
    }
    public function get_address($data = array()) {
        $response = array();
        $this->db->select('tbl_vendor_master.id,tbl_vendor_address.*,tbl_vendor_address.id as address_id,states.name');
        $this->db->from('tbl_vendor_master');
        $this->db->join('tbl_vendor_address', 'tbl_vendor_address.v_id=tbl_vendor_master.id', 'left');
        $this->db->join('states', 'tbl_vendor_address.v_state=states.id', 'left');
        $this->db->where('tbl_vendor_master.id', $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function view_add_detail($id) {
        $this->db->select('tbl_vendor_address.*,states.name');
        $this->db->from('tbl_vendor_address');
        $this->db->join('states', 'tbl_vendor_address.v_state=states.id', 'left');
        $this->db->where('tbl_vendor_address.id', $id);
        $query = $this->db->get();
        return $query->result();
    }
    public function get_company_rate_by_city($id, $drop_city, $c_id, $pickup_city) {
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
    public function get_company_rate_by_city1($drop_city, $c_id, $pickup_city) {
        $response = array();
        $this->db->select('tbl_rate_deatils.*,tbl_zone_master.cities,Pickupzone.id as pick_up,tbl_zone_master.id as zone_id');
        $this->db->from('tbl_rate_deatils');
        $this->db->join('tbl_zone_master AS Pickupzone', 'tbl_rate_deatils.source_zone_id=Pickupzone.id', 'left');
        $this->db->join('tbl_zone_master', 'tbl_rate_deatils.dest_zone_id=tbl_zone_master.id', 'left');
        $this->db->where('tbl_rate_deatils.c_id', $c_id);
        $this->db->where("FIND_IN_SET('$pickup_city',Pickupzone.cities) !=", 0);
        $this->db->where("FIND_IN_SET('$drop_city',tbl_zone_master.cities) !=", 0);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_customer_name($c_id) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_vendor_master');
        $this->db->where('c_id', $c_id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function superadmin_order_view1($data = array()) {
        $response = array();
        $this->db->select('tbl_order_booking.*,tbl_vendor_master.id as vendor_id,tbl_vendor_master.v_company,countries.name as country_name1,states.name as state_name1,Dropcountry.name as country_name2,Dropstate.name as state_name2');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_vendor_master', 'tbl_order_booking.fk_id=tbl_vendor_master.id', 'left');
        $this->db->join('countries', 'tbl_order_booking.pickup_country=countries.id', 'left');
        $this->db->join('states', 'tbl_order_booking.pickup_state=states.id', 'left');
        $this->db->join('countries AS Dropcountry', 'tbl_order_booking.drop_country=Dropcountry.id', 'left');
        $this->db->join('states AS Dropstate', 'tbl_order_booking.drop_state=Dropstate.id', 'left');
        $this->db->where('tbl_order_booking.c_id', $data['id']);
        $this->db->where('tbl_order_booking.type !=', 'User');
        $this->db->order_by('id', DESC);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function superadmin_order_view2($data = array()) {
        $this->db->select('tbl_order_booking.*,tbl_vendor_master.id as vendor_id,tbl_vendor_master.v_company,countries.name as country_name1,states.name as state_name1,Dropcountry.name as country_name2,Dropstate.name as state_name2');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_vendor_master', 'tbl_order_booking.fk_id=tbl_vendor_master.id', 'left');
        $this->db->join('countries', 'tbl_order_booking.pickup_country=countries.id', 'left');
        $this->db->join('states', 'tbl_order_booking.pickup_state=states.id', 'left');
        $this->db->join('countries AS Dropcountry', 'tbl_order_booking.drop_country=Dropcountry.id', 'left');
        $this->db->join('states AS Dropstate', 'tbl_order_booking.drop_state=Dropstate.id', 'left');
        $this->db->where('tbl_order_booking.c_id', $data['id']);
        $this->db->where('tbl_order_booking.type !=', 'User');
        $this->db->order_by('id', DESC);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_tax_details($id) {
        $response = array();
        $this->db->select('tbl_vendor_master.*,tbl_vendor_master.id as vendor_id, tbl_prime_vendor_charges.*, tbl_prime_vendor_charges.id as prime_id,tbl_order_booking.pickup_name');
        $this->db->from('tbl_vendor_master');
        $this->db->join('tbl_prime_vendor_charges', 'tbl_prime_vendor_charges.fk_vendorid=tbl_vendor_master.id', 'inner');
        $this->db->join('tbl_order_booking', 'tbl_order_booking.pickup_name=tbl_vendor_master.v_company', 'inner');
        $this->db->where('tbl_vendor_master.c_id', '1');
        // $this->db->where('tbl_order_booking.fk_id',);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function order_details($value = '') {
        if ($value['type'] == 'Prime') {
            $tablename = 'tbl_prime_vendor_charges';
        } else {
            $tablename = 'tbl_rate_master';
        }
        $this->db->select('*');
        $this->db->from($tablename);
        $this->db->where('c_id', $value['c_id']);
        $this->db->like('cities', $value['drop_city']);
        $data = $this->db->get();
        $data = $data->row_array();
        $insert_array = array('order_id' => @$value['order_id'], 'fk_id' => @$value['fk_id'], 'c_id' => @$value['c_id'], 'grand_total' => @$value['grand_total'], 'drop_city' => @$value['drop_city'], 'insurace_charges' => @$data['insurance_charges'], 'delivery_charges' => @$data['delivery_charges'], 'bilty_charges' => @$data['bilty_charges']);
        $response = $this->db->insert('tbl_order_details', $insert_array);
    }
    public function invoice($data = array()) {
        $this->db->select('*');
        $this->db->from('tbl_invoice');
        $this->db->where('c_id', $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_customer_details_on_id($data = array()) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_vendor_master');
        $this->db->where('id', $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_invoice_on_id($id) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_invoice');
        $this->db->where('id', $id); //
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    //===================================================Check exist zone of customer rate=====================================================
    public function check_prime_rate($zone, $id) {
        $query = $this->db->query('select zone from tbl_prime_vendor_charges WHERE zone="' . $zone . '" AND fk_vendorid = "' . $id . '"');
        if ($query->num_rows() >= 1) {
            $response['status'] = 0;
            $response['message'] = 'Already exist';
            return $response;
        } else {
            $response['status'] = 1;
            $response['message'] = 'New Zone';
            return $response;
        }
    }
    //===================================================================Bar Code Generate=======================================================
    public function get_awb_no() {
        $query = $this->db->query('SELECT AWBno FROM  tbl_order_booking');
        return $query->result();
    }
    public function user_profile() {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_rate_master');
        $this->db->where('c_id', 0);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_manifest_details($city, $vehicle, $date_from, $date_to, $id) {
        $response = array();
        $this->db->select('tbl_outscan.*,tbl_outscan.created_at as outscan_date,GROUP_CONCAT(tbl_outscan.scan_count)scan_count,tbl_order_booking.*,tbl_order_booking.id as order_id, tbl_vehicle_master.*, tbl_vehicle_master.id as vehicle_id,tbl_company_master.id as company_id');
        $this->db->from('tbl_outscan');
        $this->db->join('tbl_order_booking', 'tbl_outscan.awb_no=tbl_order_booking.AWBno', 'left');
        $this->db->join('tbl_vehicle_master', 'tbl_outscan.vechile_id=tbl_vehicle_master.id', 'left');
        $this->db->join('tbl_company_master', 'tbl_order_booking.c_id=tbl_company_master.id', 'left');
        $this->db->where('tbl_outscan.city', $city);
        $this->db->where('tbl_outscan.vechile_id', $vehicle);
        $this->db->where('tbl_outscan.date >=', $date_from);
        $this->db->where('tbl_outscan.date <=', $date_to);
        // $this->db->where('tbl_order_booking.c_id',$id);
        $this->db->group_by('tbl_order_booking.AWBno');
        $this->db->order_by('tbl_order_booking.order_date', 'ASC');
        //  $this->db->order_by('tbl_outscan.id','DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_vehicle() {
        $query = $this->db->query('SELECT id,vcl_name FROM tbl_vehicle_master Where status=1');
        return $query->result();
    }
    public function get_reports() {
        $this->db->select('tbl_manifest_reports.*,tbl_vehicle_master.vcl_name');
        $this->db->from('tbl_manifest_reports');
        $this->db->join('tbl_vehicle_master', 'tbl_manifest_reports.vechile_id=tbl_vehicle_master.id', 'left');
        $this->db->order_by('tbl_manifest_reports.id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_dsr_reports() {
        $this->db->select('tbl_dsr_reports.*,tbl_vehicle_master.vcl_name');
        $this->db->from('tbl_dsr_reports');
        $this->db->join('tbl_vehicle_master', 'tbl_dsr_reports.vechile_id=tbl_vehicle_master.id', 'left');
        $this->db->order_by('tbl_dsr_reports.id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    // public function generate_challan($vehicle,$date_from,$date_to)
    // {
    //     $response=array();
    //     $this->db->select('tbl_outscan.*,tbl_outscan.awb_no as awb,tbl_outscan.created_at as outscan_date,tbl_order_booking.*,tbl_order_booking.created_at as order_date,tbl_order_booking.id as order_id, tbl_vehicle_master.*, tbl_vehicle_master.id as vehicle_id');
    //     $this->db->from('tbl_outscan');
    //     $this->db->join('tbl_order_booking','tbl_outscan.awb_no=tbl_order_booking.AWBno','left');
    //     $this->db->join('tbl_vehicle_master','tbl_outscan.vechile_id=tbl_vehicle_master.id','left');
    //     $this->db->where('tbl_outscan.vechile_id',$vehicle);
    //     $this->db->where('tbl_outscan.date >=',$date_from);
    //     $this->db->where('tbl_outscan.date <=',$date_to);
    //     $query = $this->db->get();
    //     $result = $query->result_array();
    //     $response['status'] = 1;
    //     $response['message'] = 'success';
    //     $response['data'] = $result;
    //     return $response;
    // }
    public function generate_challan($vehicle, $date_from, $date_to) {
        $response = array();
        $this->db->select('tbl_outscan.*,tbl_outscan.awb_no as awb,tbl_outscan.created_at as outscan_date,tbl_order_booking.*,tbl_order_booking.created_at as order_date,tbl_order_booking.id as order_id, tbl_vehicle_master.*, tbl_vehicle_master.id as vehicle_id,tbl_manifest_reports.*,tbl_manifest_reports.id as manifest_id');
        $this->db->from('tbl_outscan');
        $this->db->join('tbl_order_booking', 'tbl_outscan.awb_no=tbl_order_booking.AWBno', 'left');
        $this->db->join('tbl_vehicle_master', 'tbl_outscan.vechile_id=tbl_vehicle_master.id', 'left');
        $this->db->join('tbl_manifest_reports', 'tbl_manifest_reports.vechile_id=tbl_vehicle_master.id', 'left');
        // $this->db->where('tbl_outscan.city',$city);
        $this->db->where('tbl_outscan.vechile_id', $vehicle);
        $this->db->where('tbl_outscan.date >=', $date_from);
        $this->db->where('tbl_outscan.date <=', $date_to);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_challan_reports() {
        $this->db->select('tbl_challan.*,tbl_vehicle_master.vcl_name');
        $this->db->from('tbl_challan');
        $this->db->join('tbl_vehicle_master', 'tbl_challan.vehicle_id=tbl_vehicle_master.id', 'left');
        $this->db->order_by('id', DESC);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    //Query to fetch cities  from database table
    public function get_city_query($pincode) {
        $query = $this->db->get_where('tbl_pincode', array('postel_code' => $pincode));
        return $query->result();
    }
    public function get_employee_detail_on_id($id) {
        $response = array();
        $this->db->select('tbl_employee_master.*,tbl_company_master.c_name');
        $this->db->from('tbl_employee_master');
        $this->db->join('tbl_company_master', 'tbl_employee_master.c_id=tbl_company_master.id', 'left');
        $this->db->where('tbl_employee_master.id', $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    //==============================Consignee Part==========================================================
    public function get_consignee() {
        $query = $this->db->query('SELECT * FROM  tbl_consignee_master');
        return $query->result();
    }
    public function get_consignee1($id) {
        $query = $this->db->query('SELECT * FROM  tbl_consignee_master where c_id="' . $id . '"');
        return $query->result();
    }
    public function consignee_status($id) {
        $this->db->select('status')->from('tbl_consignee_master')->where('id', $id);
        $query = $this->db->get();
        return $query->result();
    }
    public function get_consignee_details($data) {
        $this->db->select('*');
        $this->db->from('tbl_consignee_master');
        $this->db->where($data);
        $result_set = $this->db->get();
        if ($result_set->num_rows() > 0) {
            return $result_set->row_array();
        } else {
            return FALSE;
        }
    }
    public function get_consignee_details1($data = array()) {
        $this->db->select('*');
        $this->db->from('tbl_consignee_master');
        $this->db->where('id', $data['id']);
        $result_set = $this->db->get();
        if ($result_set->num_rows() > 0) {
            return $result_set->row_array();
        } else {
            return FALSE;
        }
    }
    public function get_employee_details($id) {
        $response = array();
        $this->db->select('tbl_employee_master.*,tbl_company_master.c_name');
        $this->db->from('tbl_employee_master');
        $this->db->join('tbl_company_master', 'tbl_employee_master.c_id=tbl_company_master.id', 'left');
        $this->db->where('tbl_employee_master.c_id', $id);
        $this->db->where('tbl_employee_master.delete_status', "1");
        $this->db->order_by('tbl_employee_master.id', DESC);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_employee_details1($id) {
        $response = array();
        $this->db->select('tbl_employee_master.*,tbl_company_master.c_name');
        $this->db->from('tbl_employee_master');
        $this->db->join('tbl_company_master', 'tbl_employee_master.c_id=tbl_company_master.id', 'left');
        $this->db->where('tbl_employee_master.branch_id', $id);
        $this->db->where('tbl_employee_master.delete_status', "1");
        $this->db->order_by('tbl_employee_master.id', DESC);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function consignee_on_id($id) {
        $response = array();
        $this->db->select('tbl_consignee_master.*,tbl_company_master.c_name');
        $this->db->from('tbl_consignee_master');
        $this->db->join('tbl_company_master', 'tbl_consignee_master.c_id=tbl_company_master.id', 'left');
        $this->db->where('tbl_consignee_master.c_id', $id);
        $this->db->where('tbl_consignee_master.delete_status', "1");
        $this->db->order_by('tbl_consignee_master.id', DESC);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function consignee_on_branch($id) {
        $response = array();
        $this->db->select('tbl_consignee_master.*,tbl_company_master.c_name');
        $this->db->from('tbl_consignee_master');
        $this->db->join('tbl_company_master', 'tbl_consignee_master.c_id=tbl_company_master.id', 'left');
        $this->db->where('tbl_consignee_master.branch_id', $id);
        $this->db->where('tbl_consignee_master.delete_status', "1");
        $this->db->order_by('tbl_consignee_master.id', DESC);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_vechile_details() {
        $response = array();
        $this->db->select('tbl_vehicle_master.*,tbl_company_master.c_name');
        $this->db->from('tbl_vehicle_master');
        $this->db->join('tbl_company_master', 'tbl_vehicle_master.c_id=tbl_company_master.id', 'left');
        $this->db->where('tbl_vehicle_master.delete_status', "1");
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_vechile_details_on_id($id) {
        $response = array();
        $this->db->select('tbl_vehicle_master.*,tbl_company_master.c_name');
        $this->db->from('tbl_vehicle_master');
        $this->db->join('tbl_company_master', 'tbl_vehicle_master.c_id=tbl_company_master.id', 'left');
        $this->db->where('tbl_vehicle_master.c_id', $id);
        $this->db->where('tbl_vehicle_master.delete_status', '1');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_daily_reports($date_from, $date_to, $city = '', $des_city = '', $cust = '', $id) {
        // $date_from = $date_from.' 00:00:01';
        // $date_to = $date_to.' 23:59:59';
        $response = array();
        $this->db->select('tbl_order_booking.*,tbl_order_booking.id as order_id,tbl_order_status.*,tbl_order_status.id as order_status_id,tbl_company_master.*,tbl_company_master.id as company_id');
        //tbl_order_booking.created_at as order_date
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_order_status', 'tbl_order_status.fk_oid=tbl_order_booking.id', 'left');
        $this->db->join('tbl_company_master', 'tbl_order_booking.c_id=tbl_company_master.id', 'left');
        $this->db->where('tbl_order_booking.order_date >=', $date_from);
        $this->db->where('tbl_order_booking.order_date <=', $date_to);
        if (!empty($city)) {
            $this->db->where('tbl_order_booking.pickup_city', $city);
        }
        if (!empty($des_city)) {
            $this->db->where('tbl_order_booking.drop_city', $des_city);
        }
        if (!empty($cust)) {
            $this->db->where('tbl_order_booking.pickup_name', $cust);
        }
        // $this->db->where('tbl_order_booking.c_id',$id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_pickup_scan_details($date_from, $date_to) {
        // $date_from = $date_from.' 00:00:01';
        // $date_to = $date_to.' 23:59:59';
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_map_barcode');
        $this->db->where('pickup_date >=', $date_from);
        $this->db->where('pickup_date <=', $date_to);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_in_scan_details($date_from, $date_to) {
        // $date_from = $date_from.' 00:00:01';
        // $date_to = $date_to.' 23:59:59';
        $response = array();
        $this->db->select('tbl_inscan.*, tbl_employee_master.emp_name');
        $this->db->from('tbl_inscan');
        $this->db->join('tbl_employee_master', 'tbl_inscan.emp_id=tbl_employee_master.id', 'left');
        $this->db->where('tbl_inscan.inscan_date >=', $date_from);
        $this->db->where('tbl_inscan.inscan_date <=', $date_to);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_out_scan_details($date_from, $date_to) {
        $response = array();
        $this->db->select('tbl_outscan.*,tbl_employee_master.emp_name');
        $this->db->from('tbl_outscan');
        $this->db->join('tbl_employee_master', 'tbl_outscan.emp_id=tbl_employee_master.id', 'left');
        $this->db->where('date >=', $date_from);
        $this->db->where('date <=', $date_to);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function total_destination_order_list() {
        $response = array();
        $this->db->select('tbl_destination_inscan.*,tbl_order_booking.*,tbl_company_master.c_name,tbl_employee_master.emp_name,Dropcountry.name as country_name2'); //tbl_employee_master.emp_name,tbl_vendor_master.id as vendor_id,tbl_vendor_master.v_company,countries.name as country_name1,states.name as state_name1,Dropcountry.name as country_name2,Dropstate.name as state_name2
        $this->db->from('tbl_destination_inscan');
        $this->db->join('tbl_order_booking', 'tbl_destination_inscan.awb_no=tbl_order_booking.AWBno', 'left');
        $this->db->join('countries AS Dropcountry', 'tbl_order_booking.drop_country=Dropcountry.id', 'left');
        $this->db->join('tbl_employee_master', 'tbl_order_booking.drop_fk_emp_id=tbl_employee_master.id', 'left');
        $this->db->join('tbl_company_master', 'tbl_order_booking.c_id=tbl_company_master.id', 'left');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    function get_destination_order_count($table, $data = array()) {
        $response = array();
        $this->db->select('COUNT(id) as count_order');
        $this->db->from($table);
        $this->db->where("drop_fk_emp_id", $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function daily_reports() {
        $this->db->select('*');
        $this->db->from('tbl_daily_report');
        $this->db->order_by('id', DESC);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    // public function daily_reports1($id)
    // {
    //     $this->db->select('*');
    //     $this->db->from('tbl_daily_report');
    //     $this->db->where('c_id',$id);
    //     $this->db->order_by('id',DESC);
    //     $query = $this->db->get();
    //     $result = $query->result_array();
    //         $response['status'] = 1;
    //         $response['message'] = 'success';
    //         $response['data'] = $result;
    //         return $response;
    // }
    //============================================================Monthly Invoice==========================================================
    public function get_custs($id) {
        $query = $this->db->query('SELECT id, v_company FROM  tbl_vendor_master WHERE vendor_type="Prime" AND status=1 AND c_id=' . $id . '');
        return $query->result();
    }
    public function monthly_invoice($date_from, $date_to, $cust, $city, $des_city, $id) {
        // $date_from = $date_from.' 00:00:01';
        // $date_to = $date_to.' 23:59:59';
        $response = array();
        $this->db->select('tbl_order_booking.*,tbl_order_booking.c_id as comp_id,tbl_order_booking.id as order_id,tbl_billing_details.*,tbl_billing_details.id as billing_id,tbl_company_master.*, tbl_company_master.id as company_id,tbl_vendor_master.*,tbl_vendor_master.id as cust_id');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_billing_details', 'tbl_billing_details.fk_order_id=tbl_order_booking.id', 'left');
        $this->db->join('tbl_company_master', 'tbl_order_booking.c_id=tbl_company_master.id', 'left');
        $this->db->join('tbl_vendor_master', 'tbl_billing_details.billing_id=tbl_vendor_master.id', 'left');
        $this->db->where('tbl_order_booking.order_date >=', $date_from);
        $this->db->where('tbl_order_booking.order_date <=', $date_to);
        $this->db->where('tbl_order_booking.pickup_name', $cust);
        // $this->db->where('tbl_order_booking.sac_code',$sac_code);
        if (!empty($city)) {
            $this->db->where('tbl_order_booking.pickup_city', $city);
        }
        if (!empty($des_city)) {
            $this->db->where('tbl_order_booking.drop_city', $des_city);
        }
        $this->db->where('tbl_order_booking.c_id', $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    //======================================Dashboard=====================================
    public function get_count_record($table) {
        $query = $this->db->count_all($table);
        return $query;
    }
    public function count_prime() {
        $this->db->select('id');
        $this->db->from('tbl_vendor_master');
        $this->db->where('vendor_type', 'Prime');
        $query = $this->db->get();
        return $query->num_rows();
    }
    public function count_normal() {
        $this->db->select('id');
        $this->db->from('tbl_vendor_master');
        $this->db->where('vendor_type', 'Normal');
        $query = $this->db->get();
        return $query->num_rows();
    }
    public function count_active($table) {
        $this->db->select('id');
        $this->db->from($table);
        $this->db->where('status', '1');
        $query = $this->db->get();
        return $query->num_rows();
    }
    public function count_inactive($table) {
        $this->db->select('id');
        $this->db->from($table);
        $this->db->where('status', '0');
        $query = $this->db->get();
        return $query->num_rows();
    }
    public function countRowonId($tablename, $id) {
        $this->db->select('count(id) AS num_of_columns');
        $this->db->from($tablename);
        $this->db->where("c_id", $id);
        $query = $this->db->get();
        return $query->result();
    }
    public function count_active_on_id($table, $id) {
        $this->db->select('id');
        $this->db->from($table);
        $this->db->where('c_id', $id);
        $this->db->where('status', '1');
        $query = $this->db->get();
        return $query->num_rows();
    }
    public function count_inactive_on_id($table, $id) {
        $this->db->select('id');
        $this->db->from($table);
        $this->db->where('c_id', $id);
        $this->db->where('status', '1');
        $query = $this->db->get();
        return $query->num_rows();
    }
    public function count_prime_on_id($id) {
        $this->db->select('id');
        $this->db->from('tbl_vendor_master');
        $this->db->where('c_id', $id);
        $this->db->where('vendor_type', 'Prime');
        $query = $this->db->get();
        return $query->num_rows();
    }
    public function count_normal_on_id($id) {
        $this->db->select('id');
        $this->db->from('tbl_vendor_master');
        $this->db->where('c_id', $id);
        $this->db->where('vendor_type', 'Normal');
        $query = $this->db->get();
        return $query->num_rows();
    }
    public function get_company_details_on_id($id) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_company_master');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_track_order($data = array()) {
        $response = array();
        $this->db->select('tbl_order_booking.*,tbl_order_status.*,tbl_order_status.id order_status_id,tbl_status_master.status_name');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_order_status', 'tbl_order_status.fk_oid=tbl_order_booking.id', 'left');
        $this->db->join('tbl_status_master', 'tbl_order_status.order_status=tbl_status_master.id', 'left');
        $this->db->where('tbl_order_booking.AWBno', $data['AWBno']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_forwarding_no($data = array()) {
        $response = array();
        $this->db->select('tbl_order_booking.AWBno,tbl_forwarding_master.id as master_id,tbl_forwarding_master.name,tbl_forwarding_master.forward_link,tbl_forwarding.awb_no as forward_awb_no,tbl_forwarding.vendor2,tbl_forwarding.vendor1,tbl_forwarding.id');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_forwarding', 'tbl_forwarding.awb_no=tbl_order_booking.AWBno', 'left');
        $this->db->join('tbl_forwarding_master', 'tbl_forwarding.vendor1=tbl_forwarding_master.id', 'left');
        $this->db->where('tbl_order_booking.AWBno', $data['AWBno']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_employee($id) {
        $this->db->select('tbl_employee_master.id,tbl_employee_master.emp_name, tbl_employee_designation.desg_id,tbl_designation_master.designation');
        $this->db->from('tbl_employee_master');
        $this->db->join('tbl_employee_designation', 'tbl_employee_designation.emp_id=tbl_employee_master.id', 'left');
        $this->db->join('tbl_designation_master', 'tbl_employee_designation.desg_id=tbl_designation_master.id', 'left');
        $this->db->where('tbl_designation_master.designation', "Field Executive");
        $this->db->where('tbl_employee_master.c_id', $id);
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }
    public function monthly_invoice_data($id) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_monthly_invoice');
        $this->db->where('c_id', $id);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function daily_reports1($id) {
        $this->db->select('*');
        $this->db->from('tbl_daily_report');
        $this->db->where('c_id', $id);
        $this->db->order_by('id', DESC);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function check_AWB_No1($AWB_no) {
        $response = array();
        $query = $this->db->get_where('tbl_order_booking', array('AWBno' => $AWB_no));
        if ($query->num_rows() > 0) {
            $response['status'] = 1;
            $response['message'] = 'Already Exist';
            return $response;
        } else {
            $response['status'] = 0;
            $response['message'] = 'new AWB No';
            return $response;
        }
    }
    public function check_AWB_No($table_name, $field_name, $id) {
        $response = array();
        $this->db->select('*');
        $this->db->from($table_name);
        $this->db->where($field_name, $id);
        $query = $this->db->get();
        $result = $query->result_array();
        if ($result) {
            $response['status'] = 1;
            $response['message'] = 'success';
            $response['data'] = $result;
        } else {
            $response['status'] = 0;
            $response['message'] = 'failed';
            $response['data'] = $result;
        }
        return $response;
    }
    public function check_status($status) {
        $response = array();
        $query = $this->db->get_where('tbl_status_master', array('status_name' => $status));
        if ($query->num_rows() > 0) {
            $response['status'] = 1;
            $response['message'] = 'Already Exist';
            return $response;
        } else {
            $response['status'] = 0;
            $response['message'] = 'new user';
            return $response;
        }
    }
    public function get_manifest_details1($source_city, $to_city, $date_from, $date_to, $id = '') {
        $response = array();
        $sql = 'SELECT tbl_outscan.*, GROUP_CONCAT(tbl_outscan.scan_count) as scan_count, tbl_order_booking.*,tbl_order_booking.id as order_id,tbl_company_master.id as company_id, tbl_vehicle_master.* FROM tbl_outscan LEFT JOIN tbl_order_booking ON tbl_outscan.awb_no=tbl_order_booking.AWBno LEFT JOIN tbl_company_master ON tbl_order_booking.c_id=tbl_company_master.id LEFT JOIN tbl_vehicle_master ON tbl_outscan.vechile_id=tbl_vehicle_master.id WHERE tbl_outscan.source_city="' . $source_city . '" AND tbl_outscan.city="' . $to_city . '" AND tbl_outscan.date>="' . $date_from . '" AND tbl_outscan.date<="' . $date_to . '" ';
        //$sql='SELECT tbl_outscan.*, GROUP_CONCAT(tbl_outscan.scan_count)scan_count, tbl_order_booking.*,tbl_order_booking.id as order_id,tbl_company_master.id as company_id, tbl_vehicle_master.* FROM tbl_outscan LEFT JOIN tbl_order_booking ON tbl_outscan.awb_no=tbl_order_booking.AWBno LEFT JOIN tbl_company_master ON tbl_order_booking.c_id=tbl_company_master.id LEFT JOIN tbl_vehicle_master ON tbl_outscan.vechile_id=tbl_vehicle_master.id WHERE tbl_outscan.source_city="BHIWANDI" AND tbl_outscan.city="VADODARA" AND tbl_outscan.date>="19/06/2019" AND tbl_outscan.date<="19/06/2019" AND tbl_order_booking.c_id="0"' AND tbl_order_booking.c_id="'.$id.'";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_mail_details1($city) {
        $response = array();
        $sql = "SELECT tbl_search_manifest.*,tbl_branch.email,tbl_employee_master.emp_email,tbl_employee_master.id as emp_id,tbl_employee_designation.emp_id,tbl_employee_designation.desg_id,tbl_designation_master.designation FROM tbl_search_manifest LEFT JOIN tbl_branch ON tbl_branch.branch_city=tbl_search_manifest.city LEFT JOIN tbl_employee_master ON tbl_employee_master.c_id=tbl_search_manifest.id LEFT JOIN tbl_employee_designation ON tbl_employee_designation.emp_id=tbl_employee_master.id LEFT JOIN tbl_designation_master ON tbl_employee_designation.desg_id=tbl_designation_master.id WHERE tbl_search_manifest.city='" . $city . "' AND tbl_designation_master.designation='CUSTOMER CARE EXICUTIVE'";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function track_order_deatails($track_order) {
        $response = array();
        $this->db->select('tbl_order_booking.*');
        $this->db->from('tbl_order_booking');
        $this->db->where('AWBno', $track_order);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function track_order_deatails1($track_order) {
        $response = array();
        $sql = 'select tbl_order_booking.id as order_id,tbl_manifest_reports.manifest_no from tbl_order_booking LEFT join tbl_manifest_reports on tbl_order_booking.c_id = tbl_manifest_reports.c_id where tbl_order_booking.order_date between tbl_manifest_reports.date_from and tbl_manifest_reports.date_to And tbl_order_booking.AWBno="' . $track_order . '"';
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function order_status_details($track_order) {
        $response = array();
        $this->db->select('tbl_order_booking.AWBno,tbl_order_booking.order_box, tbl_order_booking.id as order_id,tbl_order_status.*, tbl_order_status.id as order_status_id, tbl_status_master.status_name');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_order_status', 'tbl_order_status.fk_oid=tbl_order_booking.id', 'left');
        $this->db->join('tbl_status_master', 'tbl_order_status.order_status=tbl_status_master.id', 'left');
        $this->db->where('tbl_order_booking.AWBno', $track_order);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    //===========================================================MIS Report============================================================================================
    public function get_mis_report() {
        $end_date = date('Y-m-d', strtotime("-60 days"));
        //@$newformat = date('d/m/Y',$end_date);
        $response = array();
        $this->db->select('tbl_order_booking.*, tbl_order_booking.id as order_id,tbl_outscan.*,tbl_outscan.id as source_outscan,GROUP_CONCAT(tbl_outscan.date)out_scan_date,tbl_outscan.awb_no as out_scan_awb_no,tbl_destination_outscan.*,tbl_destination_outscan.awb_no as des_outscan_awb,GROUP_CONCAT(tbl_destination_outscan.date)out_destination_scan_date,tbl_destination_outscan.id as destination_outscan,tbl_forwarding.vendor2,GROUP_CONCAT(tbl_order_status.order_status) order_status,GROUP_CONCAT(tbl_status_master.status_name)status_name');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_outscan', 'tbl_outscan.awb_no=tbl_order_booking.AWBno', 'left');
        $this->db->join('tbl_destination_outscan', 'tbl_destination_outscan.awb_no=tbl_order_booking.AWBno', 'left');
        $this->db->join('tbl_forwarding', 'tbl_forwarding.awb_no=tbl_order_booking.AWBno', 'left');
        $this->db->join('tbl_order_status', 'tbl_order_status.fk_oid=tbl_order_booking.id', 'left');
        $this->db->join('tbl_status_master', 'tbl_order_status.order_status=tbl_status_master.id', 'left');
        $this->db->where("date_format(STR_TO_DATE(order_date,'%d/%m/%Y'),'%Y-%m-%d') >", $end_date);
        $this->db->group_by('tbl_order_booking.AWBno');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_mis_report_details($id) {
        $end_date = date('Y-m-d', strtotime("-60 days"));
        $response = array();
        $this->db->select('tbl_order_booking.*, tbl_order_booking.id as order_id,tbl_outscan.*,tbl_outscan.id as source_outscan_id,GROUP_CONCAT(tbl_outscan.date)out_scan_date,tbl_destination_outscan.*,GROUP_CONCAT(tbl_destination_outscan.date)out_destination_scan_date,tbl_destination_outscan.id as destination_outscantbl_forwarding.vendor2,GROUP_CONCAT(tbl_order_status.order_status) order_status,GROUP_CONCAT(tbl_status_master.status_name)status_name');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_outscan', 'tbl_outscan.awb_no=tbl_order_booking.AWBno', 'left');
        $this->db->join('tbl_destination_outscan', 'tbl_destination_outscan.awb_no=tbl_order_booking.AWBno', 'left');
        $this->db->join('tbl_forwarding', 'tbl_forwarding.awb_no=tbl_order_booking.AWBno', 'left');
        $this->db->join('tbl_order_status', 'tbl_order_status.fk_oid=tbl_order_booking.id', 'left');
        $this->db->join('tbl_status_master', 'tbl_order_status.order_status=tbl_status_master.id', 'left');
        $this->db->where("date_format(STR_TO_DATE(order_date,'%d/%m/%Y'),'%Y-%m-%d') < ", $end_date);
        $this->db->where("tbl_order_booking.fk_id", $id);
        $this->db->group_by('tbl_order_booking.AWBno');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_mis_on_cities($pickup_city, $drop_city) {
        $response = array();
        $sql = "SELECT tbl_tat_details.*,tbl_tat_details.id as tat_details_id,tbl_zone_master.cities, Dropzone_id.id as drop_zone_id FROM tbl_tat_details LEFT JOIN tbl_zone_master ON tbl_tat_details.source_zone_id=tbl_zone_master.id LEFT JOIN tbl_zone_master as Dropzone_id ON tbl_tat_details.destination_zone_id=Dropzone_id.id WHERE FIND_IN_SET('$drop_city', replace(tbl_zone_master.cities, ' ', '')) > 0 OR FIND_IN_SET('$pickup_city', replace(tbl_zone_master.cities, ' ', '')) > 0 ";
        $query = $this->db->query($sql);
        $result = $query->row_array();
        return $result;
    }
    public function get_email_details($id) {
        $response = array();
        $this->db->select('v_email,v_alternate_email');
        $this->db->from('tbl_vendor_master');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_track_details($awb_no) {
        $response = array();
        $this->db->select('order_date,drop_city');
        $this->db->from('tbl_order_booking');
        $this->db->where('AWBno', $awb_no);
        $query = $this->db->get();
        $result = $query->row_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_forwarding_details() {
        $response = array();
        $this->db->select('tbl_forwarding.awb_no,tbl_forwarding.booking_date,tbl_forwarding.destination,GROUP_CONCAT(tbl_forwarding.id)id,GROUP_CONCAT(tbl_forwarding.vendor1) forwarding_name,GROUP_CONCAT(tbl_forwarding.vendor2)forwarding_awb,GROUP_CONCAT(tbl_forwarding_master.name) name,GROUP_CONCAT(tbl_forwarding_master.forward_link) link,GROUP_CONCAT(tbl_forwarding_master.id) master_id');
        $this->db->from('tbl_forwarding');
        $this->db->join('tbl_forwarding_master', 'tbl_forwarding.vendor1=tbl_forwarding_master.id', 'left');
        $this->db->group_by('tbl_forwarding.awb_no');
        $this->db->where('tbl_forwarding.status', '1');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['data'] = $result;
        return $response;
    }
    public function view_forward_details($id) {
        $response = array();
        $this->db->select('tbl_forwarding.*,tbl_forwarding_master.name');
        $this->db->from('tbl_forwarding');
        $this->db->join('tbl_forwarding_master', 'tbl_forwarding.vendor1=tbl_forwarding_master.id', 'left');
        $this->db->where('tbl_forwarding.id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function check_forwarding_details1($name, $forwarding_link) {
        $response = array();
        $query = $this->db->query('select * from tbl_forwarding_master WHERE name = "' . $name . '" OR forward_link = "' . $forwarding_link . '" OR status="0" ');
        $op = $query->row();
        if ($query->num_rows() >= 1) {
            $response['status'] = 0;
            $response['message'] = 'Email Already exist';
            return $response;
        } else {
            $response['status'] = 1;
            $response['message'] = 'New Customer';
            return $response;
        }
    }
    public function check_forwarding_details($name, $forwarding_link) {
        $response = array();
        $query = $this->db->get_where('tbl_forwarding_master', array('name' => $name, 'forward_link' => $forwarding_link, 'status' => '1'));
        if ($query->num_rows() > 0) {
            $response['status'] = 1;
            $response['message'] = 'Already Exist';
            return $response;
        } else {
            $response['status'] = 0;
            $response['message'] = 'new user';
            return $response;
        }
    }
    public function get_all_forwarding_master($table) {
        $response = array();
        $this->db->select('*');
        $this->db->from($table);
        $this->db->where('status', '1');
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_pod_on_awb_no($awb_no) {
        $response = array();
        $this->db->select('pod_upload');
        $this->db->from('tbl_order_booking');
        $this->db->where('AWBno', $awb_no);
        $query = $this->db->get();
        $result = $query->row_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    function get_order_addresses($term) {
        $query = $this->db->query("SELECT drop_address FROM tbl_order_booking WHERE drop_address LIKE '%$term%'");
        $result = $query->result_array();
        return $result;
    }
    function get_order_name($term) {
        $query = $this->db->query("SELECT drop_name FROM tbl_order_booking WHERE drop_name LIKE '%$term%'");
        $result = $query->result_array();
        return $result;
    }
    function get_order_email($term) {
        $query = $this->db->query("SELECT drop_email FROM tbl_order_booking WHERE drop_email LIKE '%$term%'");
        $result = $query->result_array();
        return $result;
    }
    function get_order_contact($term) {
        $query = $this->db->query("SELECT drop_contact FROM tbl_order_booking WHERE drop_contact LIKE '%$term%'");
        $result = $query->result_array();
        return $result;
    }
    public function get_order_name_details($term) {
        $reesponse = array();
        $this->db->select('drop_email,drop_contact,drop_address');
        $this->db->from('tbl_order_booking');
        $this->db->where('drop_name', $term);
        $query = $this->db->get();
        $result = $query->row_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    function getData($tableName, $where_data = array(), $select = '*', $order_by = array(), $where_in = array(), $like = array(), $where_not_in = array()) {
        try {
            if (isset($tableName) && isset($where_data)) {
                $this->db->trans_start();
                $this->db->select($select);
                if (!empty($where_data)) {
                    $this->db->where($where_data);
                }
                if (!empty($where_in)) {
                    foreach ($where_in as $field => $in_array) {
                        $this->db->where_in($field, $in_array);
                    }
                }
                if (!empty($order_by)) {
                    foreach ($order_by as $field => $order) {
                        $this->db->order_by($field, $order);
                    }
                }
                if (!empty($like)) {
                    foreach ($like as $field => $keyword) {
                        $this->db->like($field, $keyword);
                    }
                }
                if (!empty($where_not_in)) {
                    foreach ($where_not_in as $field => $in_array) {
                        $this->db->where_not_in($field, $in_array);
                    }
                }
                $query = $this->db->get($tableName);
                $this->db->trans_complete();
                if ($query->num_rows() > 0) {
                    $rows = $query->result_array();
                    return $rows;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        catch(Exception $e) {
            return false;
        }
    }
    function insertData($tableName, $array_data) {
        try {
            if (isset($tableName) && isset($array_data)) {
                $this->db->trans_start();
                $this->db->insert($tableName, $array_data);
                $globals_id = $this->db->insert_id();
                $this->db->trans_complete();
                return $globals_id;
            } else {
                return false;
            }
        }
        catch(Exception $e) {
            return false;
        }
    }
    function deleteData($tableName, $whereData) {
        if (isset($tableName) && isset($whereData)) {
            $this->db->trans_start();
            $this->db->delete($tableName, $whereData);
            $this->db->trans_complete();
            if ($this->db->affected_rows() > 0) { // returns 1 ( == true) if successfuly deleted
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    function updateData($tableName, $updateData, $where) {
        //echo $tableName;print_r($updateData);print_r($where);exit;
        $this->db->trans_start();
        $query = $this->db->update($tableName, $updateData, $where);
        $this->db->trans_complete();
        $result = $query ? 1 : 0;
        return $result;
    }
    function isExist($tablename, $fieldname, $value) {
        if (!empty($value)) {
            $query = $this->db->select($fieldname)->from($tablename)->where($fieldname, $value)->get();
            $num_rows = $query->num_rows();
            if ($num_rows > 0) {
                return true;
            } else {
                return false;
            }
        }
    }
    public function get_zone_by_domestic($id, $type) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_zone_master');
        $this->db->where('c_id', $id);
        $this->db->where('type', $type);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    function getValue($tablename, $fieldname, $where = array()) {
        $query = $this->db->select($fieldname)->from($tablename)->where($where)->get();
        $data = $query->first_row();
        $data = (array)$data;
        return isset($data[$fieldname]) ? $data[$fieldname] : '';
    }
    function get_tat_details($data = array()) {
        $response = array();
        $this->db->select('tbl_tat.*,tbl_tat_details.id as tat_details_id,tbl_tat_details.trans_type');
        $this->db->from('tbl_tat');
        $this->db->join('tbl_tat_details', 'tbl_tat_details.tat_id=tbl_tat.id', 'left');
        $this->db->where("tbl_tat.id", $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_surface_tat($surface, $id) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_tat_details');
        $this->db->where("trans_type", $surface);
        $this->db->where("tat_id", $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_rail_tat($rail, $id) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_tat_details');
        $this->db->where("trans_type", $rail);
        $this->db->where("tat_id", $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_air_tat($air, $id) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_tat_details');
        $this->db->where("trans_type", $air);
        $this->db->where("tat_id", $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function check_tat($c_id, $trans_type, $vendor_type) {
        $response = array();
        $query = $this->db->get_where('tbl_tat', array('c_id' => $c_id, 'type' => $trans_type, 'vendor_type' => $vendor_type));
        if ($query->num_rows() > 0) {
            $response['status'] = 1;
            $response['message'] = 'Already Exist';
            return $response;
        } else {
            $response['status'] = 0;
            $response['message'] = 'New TAT';
            return $response;
        }
    }
    public function get_rate_details($data = array()) {
        $response = array();
        $this->db->select('tbl_rate.*,tbl_rate_deatils.id as rate_details_id,tbl_rate_deatils.rate_id,tbl_rate_deatils.min_rate,tbl_rate_deatils.insurance_charges');
        $this->db->from('tbl_rate');
        $this->db->join('tbl_rate_deatils', 'tbl_rate_deatils.rate_id=tbl_rate.id', 'left');
        $this->db->where('tbl_rate.id', $data['id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_rate_surface($surface, $id) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_rate_deatils');
        $this->db->where("trans_type", $surface);
        $this->db->where("rate_id", $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_rate_rail($rail, $id) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_rate_deatils');
        $this->db->where("trans_type", $rail);
        $this->db->where("rate_id", $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_rate_air($air, $id) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tbl_rate_deatils');
        $this->db->where("trans_type", $air);
        $this->db->where("rate_id", $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_status() {
        $query = $this->db->query('SELECT id,status_name FROM tbl_status_master');
        return $query->result();
    }
    public function get_mis_report_details_on_cust($from_date, $to_date, $id) {
        $response = array();
        $this->db->select('tbl_order_booking.*, tbl_order_booking.id as order_id,tbl_outscan.*,tbl_outscan.id as source_outscan_id,GROUP_CONCAT(tbl_outscan.date)out_scan_date,tbl_destination_outscan.*,GROUP_CONCAT(tbl_destination_outscan.date)out_destination_scan_date,tbl_destination_outscan.id as destination_outscan,tbl_forwarding.vendor2,GROUP_CONCAT(tbl_order_status.order_status) order_status,GROUP_CONCAT(tbl_status_master.status_name)status_name');
        $this->db->from('tbl_order_booking');
        $this->db->join('tbl_outscan', 'tbl_outscan.awb_no=tbl_order_booking.AWBno', 'left');
        $this->db->join('tbl_destination_outscan', 'tbl_destination_outscan.awb_no=tbl_order_booking.AWBno', 'left');
        $this->db->join('tbl_forwarding', 'tbl_forwarding.awb_no=tbl_order_booking.AWBno', 'left');
        $this->db->join('tbl_order_status', 'tbl_order_status.fk_oid=tbl_order_booking.id', 'left');
        $this->db->join('tbl_status_master', 'tbl_order_status.order_status=tbl_status_master.id', 'left');
        $this->db->where('tbl_order_booking.order_date >=', $from_date);
        $this->db->where('tbl_order_booking.order_date <=', $to_date);
        if (!empty($id)) {
            $this->db->where("tbl_order_booking.fk_id", $id);
        }
        $this->db->group_by('tbl_order_booking.AWBno');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
    public function get_cust1() {
        $query = $this->db->query('SELECT id, v_company FROM  tbl_vendor_master');
        return $query->result();
    }
    public function get_report_view() {
        $response = array();
        $this->db->select('tbl_mis_report.*,tbl_vendor_master.v_company');
        $this->db->from('tbl_mis_report');
        $this->db->join('tbl_vendor_master', 'tbl_mis_report.customer_name=tbl_vendor_master.id', 'left');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
    }
}
