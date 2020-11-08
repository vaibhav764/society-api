<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Admin_model extends CI_Model {
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
    //=================================================Login Part=================================================
   public function login($data = array(), $tablename) {
        $response = array();
        $username = $data['email'];
        $password = $data['password'];
        $encryptedpassword = dec_enc('encrypt', $password);
        $check = is_numeric($username);
        if ($check) {
            $this->db->select('*');
            $this->db->from($tablename);
            $this->db->where("contact_no", $username);
            $this->db->where("password", $encryptedpassword);
            // $this->db->where("status", '1');
        } else {
            $this->db->select('*');
            $this->db->from($tablename);
            $this->db->where("email", $username);
            // $this->db->where("status", '1');
            $this->db->where("password", $encryptedpassword);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $op = $query->row();
        $num_user = $query->num_rows();
        // @$status = $result[0]['status'];
        // @$id = $result[0]['id'];
        // if (!$num_user) {
        //     $response['status'] = 0;
        //     $response['message1'] = "User Not found";
        //     return $response;
        // }
        // $response['status'] = 1;
        // $response['message1'] = "Login Success";
        // $response['data'] = $op;
        return $op;
    }
    //=============================================================Insert 
   
  
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



}
