<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Pincode_model extends CI_Model
{
    var $data_table = 'pincode';
    var $column_order = array('postal_code', 'city_name', 'state','international_service','domestic_service','oda_opa','code_service'); //set column field database for datatable orderable
    var $column_search = array('postal_code','city_name','state','international_service','domestic_service','oda_opa','code_service'); //set column field database for datatable searchable 
    var $order = array('id' => 'desc'); 
    public function __construct()
    {
    }
    public function count_all($data= array())
    {
        // print_r($data);die;
        $i = 0;
     
        foreach ($this->column_search as $item) // loop column 
        {
            if(@$_POST['search']['value']) // if datatable send POST for search
            {
                 
                if($i===0) // first loop
                {
                    $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                    $this->db->like($item, $_POST['search']['value']);
                }
                else
                {
                    $this->db->or_like($item, $_POST['search']['value']);
                }
 
                if(count($this->column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
         
        if(isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } 
        else if(isset($this->order))
        {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
        
        $this->db->from('pincode');
        $this->db->where('company_id',$data['company_id']);
        return $this->db->count_all_results();
	}
	
	function count_filtered($data=array())
    {
        
        $this->db->from('pincode');
        $this->db->where('company_id',$data['company_id']);
        $query = $this->db->get();
        return $query->num_rows();
    }
}