<?php
class Model extends CI_Model {
	
	function __construct() {
		parent::__construct();
	}
	
	function getData($tableName, $where_data=array(),$select ='*',$order_by=array(), $where_in = array(),$like = array(),$where_not_in = array()){
        try{
			if (isset($tableName) && isset($where_data)) {
				if (!$this->db->field_exists('status', $tableName)){
					$this->db->trans_start();
					$this->db->query("ALTER TABLE `".$tableName."` ADD `status` VARCHAR(25) NOT NULL");
					$this->db->trans_complete();
				}
				$where_data['status !='] = 'deleted';
				$this->db->trans_start();
				$this->db->select($select);
				if(!empty($where_data)){
					$this->db->where($where_data);
				}
				if(!empty($where_in)){
					foreach ($where_in as $field => $in_array) {
						$this->db->where_in($field, $in_array);
					}
				}
				if(!empty($order_by)){
					foreach ($order_by as $field => $order) {
						$this->db->order_by($field,$order);
					}
				}
				if(!empty($like)){
					foreach ($like as $field => $keyword) {
						$this->db->like($field,$keyword);
					}
				}
				if(!empty($where_not_in)){
					foreach ($where_not_in as $field => $in_array) {
						$this->db->where_not_in($field, $in_array);
					}
				}
				$query = $this->db->get($tableName);
                               
				$this->db->trans_complete();
				if ($query->num_rows() > 0){
					$rows = $query->result_array();
					return $rows;
				}else{
					return false;
				} 
			}else{
				return false;
			}
		} catch (Exception $e){
			return false;
		}
	}

	function getData2($tableName, $where_data=array(),$or_where_data=array(),$select ='*',$order_by=array(), $where_in = array(),$like = array(),$where_not_in = array()){
        try{
			if (isset($tableName) && isset($where_data)) {
				
				$this->db->trans_start();
				$this->db->select($select);
				if(!empty($where_data)){
					$this->db->where($where_data);
				}
				if(!empty($or_where_data)){
					$this->db->or_where($or_where_data);
				}
				if(!empty($where_in)){
					foreach ($where_in as $field => $in_array) {
						$this->db->where_in($field, $in_array);
					}
				}
				if(!empty($order_by)){
					foreach ($order_by as $field => $order) {
						$this->db->order_by($field,$order);
					}
				}
				if(!empty($like)){
					foreach ($like as $field => $keyword) {
						$this->db->like($field,$keyword);
					}
				}
				if(!empty($where_not_in)){
					foreach ($where_not_in as $field => $in_array) {
						$this->db->where_not_in($field, $in_array);
					}
				}
				$query = $this->db->get($tableName);
                               
				$this->db->trans_complete();
				if ($query->num_rows() > 0){
					$rows = $query->result_array();
					return $rows;
				}else{
					return false;
				} 
			}else{
				return false;
			}
		} catch (Exception $e){
			return false;
		}
	}

	function getDataLimit($tableName, $where_data, $limit='', $start=''){
		//echo '<pre>'; print_r($where_data); 
		//echo $tableName.' - '.$limit .' - '. $start;
		try{
			if (isset($tableName) && isset($where_data)) {
				
				$this->db->trans_start();
				$query = $this->db->get_where($tableName, $where_data, $limit, $start);
				
				$this->db->trans_complete();
				if ($query->num_rows() > 0){
					$rows = $query->result_array();
					return $rows;
				}else{
					return false;
				} 
			}else{
				return false;
			}
		} catch (Exception $e){
			return false;
		}
	}

	function get_like_data($tbl,$clm,$keyword) /*$wh_data,*/
	{
		$this->db->select('*');
		$this->db->from($tbl);
		/*$this->db->where($wh_data);*/
		$this->db->like($clm, $keyword);
		return $this->db->get()->result_array();
	}

	function getDataGroupBy($tableName,$group_by,$where_data) /*$wh_data,*/
	{
		try{
			if (isset($tableName) && isset($where_data)) {
				if (!$this->db->field_exists('status', $tableName)){
					$this->db->trans_start();
					$this->db->query("ALTER TABLE `".$tableName."` ADD `status` VARCHAR(25) NOT NULL");
					$this->db->trans_complete();
				}
				$where_data['status !='] = 'deleted';
				$this->db->trans_start();
				// $this->db->select($select);
				if(!empty($where_data)){
					$this->db->where($where_data);
				}
				if(!empty($group_by)){
					$this->db->group_by($group_by);
				}
				
				$query = $this->db->get($tableName);
                               
				$this->db->trans_complete();
				if ($query->num_rows() > 0){
					$rows = $query->result_array();
					return $rows;
				}else{
					return false;
				} 
			}else{
				return false;
			}
		} catch (Exception $e){
			return false;
		}
	}

    function countrecord($tablename)
    {
    	$query = $this->db->get($tablename);
    	$count = $query->num_rows(); 
    	return $count;
    }

    function CountWhereRecord($tableName,$where_data)
    {
    	$query = $this->db->get_where($tableName, $where_data);
    	$count = $query->num_rows(); 
    	return $count;
    }
   	
   	function count_by_query($sql){
   		$query = $this->db->query($sql);
      	$count = $query->num_rows(); 
    	return $count;
   	}

	function insertData($tableName, $array_data){
		try{
			if (isset($tableName) && isset($array_data)) {
				
				$this->db->trans_start();

				$this->db->insert($tableName, $array_data);
				$globals_id = $this->db->insert_id();

				$this->db->trans_complete();

				return $globals_id;

			}else{
				return false;
			}
		} catch (Exception $e){
			return false;
		}
	}

	function getAllData($tableName){
		if (isset($tableName)) {
			
			$this->db->trans_start();	
			$query = $this->db->get($tableName);
			//$query = $this->db->get($tableName);
			$this->db->trans_complete();
			
			if ($query->num_rows() > 0){
				$rows = $query->result_array();
				return $rows;
			}else{
				return false;
			} 
		}else{
			return false;
		}
	}

	
	function selectData($tableName,$fields){
		if (isset($tableName)) {
			
			$this->db->trans_start();	
			$this->db->select($fields);
			$query = $this->db->get($tableName);
			$this->db->trans_complete();
			
			if ($query->num_rows() > 0){
				$rows = $query->result_array();
				return $rows;
			}else{
				return false;
			} 
			
		}else{
			return false;
		}
	}

	function selectDataNotIn($tableName,$selectField,$notInClmName,$notInData)
	{		
		if (isset($tableName)) {
			
			$this->db->trans_start();	
			$this->db->select($selectField);
			$this->db->where_not_in($notInClmName, $notInData);
			$query = $this->db->get($tableName);
			$this->db->trans_complete();
			
			if ($query->num_rows() > 0){
				$rows = $query->result_array();
				return $rows;
			}else{
				return false;
			} 
			
		}else{
			return false;
		}
	}


	function getReportData($tableName, $whereData ){
		//echo $tableName;print_r($whereData);
		if (isset($tableName) && isset($whereData)) {
			
			$del_clm = array('is_deleted' => '-1' ); //-1 : Record not deleted
			$whereData = array_merge($del_clm, $whereData);
			$this->db->trans_start();
			$query = $this->db->get_where($tableName, $whereData);
			$this->db->trans_complete();
			
			if ($query->num_rows() > 0){
				$rows = $query->result_array();
				return $rows;
			}else{
				return false;
			} 
			
		}else{
			return false;
		}
	}
	
	
	function getDataOrderBy($tableName, $whereData, $order_by, $ASC_DESC='ASC'){
		if (isset($tableName) && isset($whereData)) {
			
			$this->db->trans_start();	
			//$query = $this->db->get_where($tableName, $whereData)->order_by($order_by, $ASC_DESC);

			$this->db->from($tableName);
			$this->db->where($whereData);
			$this->db->order_by($order_by, $ASC_DESC);
			$query = $this->db->get(); 
			
			$this->db->trans_complete();
			
			if ($query->num_rows() > 0){
				$rows = $query->result_array();
				return $rows;
			}else{
				return false;
			} 
			
		}else{
			return false;
		}
	}

	function getReportDataWhereNotIn($tableName, $whereData, $whereColumn, $WhereInValues){
		$del_clm = array('is_deleted' => '-1' ); //-1 : Record not deleted
		$whereData = array_merge($del_clm, $whereData);
		
		$this->db->trans_start();	
		
		$this->db->from($tableName);
		$this->db->where($whereData);
		$this->db->where_not_in($whereColumn, $WhereInValues);
		
		$query = $this->db->get(); 
		
		$this->db->trans_complete();
		
		if ($query->num_rows() > 0){
			$rows = $query->result_array();
			return $rows;
		}else{
			return false;
		} 	
	}

	function getDataWhereIn($tableName, $whereData, $whereColumn, $WhereInValues){
		$this->db->trans_start();	
		
		$this->db->from($tableName);
		$this->db->where($whereData);
		$this->db->where_in($whereColumn, $WhereInValues);
		
		$query = $this->db->get(); 
		
		$this->db->trans_complete();
		
		if ($query->num_rows() > 0){
			$rows = $query->result_array();
			return $rows;
		}else{
			return false;
		} 	
	}


	/*
	function updateReportData($tableName, $report_data, $where){}
		$tableName   => Tablename
		$report_data => array format data which has to set
		$where => array format data for the column on which basis it will be updated.
			$where can be like "id = 4" for single condition
			$where can be like array('id' => 1005, 'sr_no'=> '10') for multiple condition
	*/

	function updateData($tableName, $updateData, $where){
		//echo $tableName;print_r($updateData);print_r($where);exit;
		
		$this->db->trans_start();	
		$query = $this->db->update($tableName, $updateData, $where);
		$this->db->trans_complete();

		$result = $query ? 1 : 0;
		return $result;
	}

	function deleteData($tableName, $whereData ,$updateData = ['status'=>'deleted']){
		if(isset($tableName) && isset($whereData)){
			$this->db->trans_start();	
			$this->db->update($tableName, $updateData, $whereData);
			$this->db->trans_complete();

			if($this->db->affected_rows() > 0){ // returns 1 ( == true) if successfuly deleted
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
		
	}

	function deleteData2($tableName,$whereData){
		if(isset($tableName) && isset($whereData)){
			
			$this->db->trans_start();	
			$this->db->delete($tableName, $whereData); 
			//$this->db->where($whrColumn, $whrValue);
			//$this->db->delete($tableName); 
			$this->db->trans_complete();

			if($this->db->affected_rows() > 0){ // returns 1 ( == true) if successfuly deleted
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
		
	}

	function getSqlData($sql){
		
       	$query = $this->db->query($sql);
      	$result=$query->result_array();
      	return $result;
	}
        
	
	function tableInsert($tablename,$val)
    {
    	
        $this->db->insert($tablename, $val);
        if($this->db->affected_rows() == 1){
         return True;
            }
        else
        {
         return False;
        }
    }

    function truncate_table($sql){
    	$this->db->from($sql); 
		$this->db->truncate(); 
    }


    function generate_next_id($tablename,$field,$series='req',$length='6'){
    	$query = $this->db->select($field)
    	->from($tablename)
    	->order_by($field,'DESC')
    	->like($field,$series)
    	->limit(1)
    	->get();
    	$data = $query->first_row();

    	if(empty($data)){
    		$zeros = '';
    		for($i=0;$i<$length-1;$i++){
    			$zeros .='0';
    		}
    		return $series.$zeros.'1';
    	}
    	else{
    		$last_id = $data->$field;
    		$number = substr($last_id,strlen($series));
    		$number = (int)$number + 1;
    		$next_id = $series.sprintf('%0'.$length.'s',$number);
    		return $next_id;
    	}
    }

    function generate_next_id2($last_id,$series= ''){
		$number = substr($last_id,strlen($series));
		$number = (int)$number + 1;
		$next_id = $series.sprintf('%06s',$number);
		return $next_id;
    }

    function get_last_id($tablename,$field){
    	$data = $this->db->select($field)
    	->from($tablename)
    	->order_by($field,'DESC')
    	->get()
    	->first_row();
    	return $data->$field;
    }

    function isExist($tablename,$fieldname,$value,$where = array()){
    	$where['status !=']='deleted';
    	if(!empty($value)){
    		$query = $this->db->select($fieldname)
    		->from($tablename)
    		->where($fieldname,$value)
    		->where($where)
    		->get();
    		$num_rows = $query->num_rows();
    		if($num_rows > 0){
    			return true;
    		}
    		else{
    			return false;
    		}
    	}
    }

    function getValue($tablename,$fieldname,$where =array()){
    	if (!$this->db->field_exists('status', $tablename)){
    		$this->db->trans_start();
    		$this->db->query("ALTER TABLE `".$tablename."` ADD `status` VARCHAR(25) NOT NULL");
    		$this->db->trans_complete();
    	}
    	$where['status !='] = 'deleted';
    	$query = $this->db->select($fieldname)
    	->from($tablename)
    	->where($where)
    	->get();
    	$data = $query->first_row();
    	$data = (array)$data;
    	return isset($data[$fieldname])?$data[$fieldname]:'';
    }


	function getMinMaxDate($date_arr=array()){
		$i= 0;
		foreach ($date_arr as $key => $value) {
		    if ($i == 0)
		    {
		        $data['max_date'] = date('Y-m-d h:i:s', strtotime($date_arr[$key]));
		        $data['min_date'] = date('Y-m-d h:i:s', strtotime($date_arr[$key]));
		        $data['max_date_key'] = $key;
		        $data['min_date_key'] = $key;
		    }
		    else if ($i != 0)
		    {
		        $new_date = date('Y-m-d h:i:s', strtotime($date_arr[$key]));
		        if ($new_date > $data['max_date'])
		        {
		            $data['max_date'] = $new_date;
		            $data['max_date_key'] = $key;
		        }
		        else if ($new_date < $data['min_date'])
		        {
		            $data['min_date'] = $new_date;
		            $data['min_date_key'] = $key;
		        }
		    }
		    $i++;
		}

		return $data;
	}

	public function get_manifest_details($city, $vehicle, $date_from, $date_to, $id) {
        
        $response = array();
        $this->db->select('source_outscan.*,source_outscan.source_city as city_source,GROUP_CONCAT(source_outscan.scan_count)scan_count,ship.*,ship.id as ship_id, vehicle.*, vehicle.id as v_id,company.contact,company.email,company.address,company.pincode,company.city_id,company.state_id,company.country_id,company.name as company_name,company.id as comp_id,customer_contacts.customer_name, ToCustomer.customer_name as to_customer,countries.name as country_name,states.name as state_name,cities.city as city_name');
        $this->db->from('source_outscan');
        $this->db->join('ship', 'source_outscan.awb_no=ship.AWBno', 'left');
        $this->db->join('vehicle', 'source_outscan.vehicle_id=vehicle.id', 'left');
        $this->db->join('company', 'ship.company_id=company.id', 'left');
        $this->db->join('customer_contacts', 'ship.shipper_id=customer_contacts.customer_id', 'left');
        $this->db->join('customer_contacts AS ToCustomer', 'ship.recepient_id=ToCustomer.customer_id', 'left');
        $this->db->join('countries', 'company.country_id=countries.id', 'left');
        $this->db->join('states', 'company.state_id=states.id', 'left');
        $this->db->join('cities', 'company.city_id=cities.id', 'left');
        $this->db->where('source_outscan.city', $city);
        $this->db->where('source_outscan.vehicle_id', $vehicle);
        $this->db->where('source_outscan.date >=', $date_from);
        $this->db->where('source_outscan.date <=', $date_to);
        $this->db->where('ship.company_id',$id);
        $this->db->group_by('ship.AWBno');
        $this->db->order_by('ship.ship_date', 'ASC');
         $this->db->order_by('source_outscan.id','DESC');
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
		$response['message'] = 'Data Not Found';
		return $response;
	   }
	}
	
	public function get_drs_details($city, $vehicle, $date_from, $date_to, $id) {
        
        $response = array();
        $this->db->select('destination_outscan.*,destination_outscan.source_city as city_source,GROUP_CONCAT(destination_outscan.scan_count)scan_count,ship.*,ship.id as ship_id, vehicle.*, vehicle.id as v_id,company.contact,company.email,company.address,company.pincode,company.city_id,company.state_id,company.country_id,company.name as company_name,company.id as comp_id,customer_contacts.name, ToCustomer.name as to_customer,countries.name as country_name,states.name as state_name,cities.city as city_name');
        $this->db->from('destination_outscan');
        $this->db->join('ship', 'destination_outscan.awb_no=ship.AWBno', 'left');
        $this->db->join('vehicle', 'destination_outscan.vehicle_id=vehicle.id', 'left');
        $this->db->join('company', 'ship.company_id=company.id', 'left');
        $this->db->join('customer_contacts', 'ship.shipper_id=customer_contacts.customer_id', 'left');
        $this->db->join('customer_contacts AS ToCustomer', 'ship.recepient_id=ToCustomer.customer_id', 'left');
        $this->db->join('countries', 'company.country_id=countries.id', 'left');
        $this->db->join('states', 'company.state_id=states.id', 'left');
        $this->db->join('cities', 'company.city_id=cities.id', 'left');
        $this->db->where('destination_outscan.city', $city);
        $this->db->where('destination_outscan.vehicle_id', $vehicle);
        $this->db->where('destination_outscan.date >=', $date_from);
        $this->db->where('destination_outscan.date <=', $date_to);
        $this->db->where('ship.company_id',$id);
        $this->db->group_by('ship.AWBno');
        $this->db->order_by('ship.ship_date', 'ASC');
         $this->db->order_by('destination_outscan.id','DESC');
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
		$response['message'] = 'Data Not Found';
		return $response;
	   }
    }

	//***************************************Daily Reports******************************************/
	public function getDailyReports($date_from='', $date_to='',$source_city='',$destination_city='',$customer_id='',$id) {
        
        $response = array();
        $this->db->select('ship.*,company.*');
		$this->db->from('ship');
		$this->db->join('company','company.id=ship.company_id', 'left');
		$this->db->where('ship.company_id',$id);
		
		if(!empty($date_from)){
			$this->db->where('ship.ship_date >=', $date_from);
		}
		if(!empty($date_to)){
			$this->db->where('ship.ship_date <=', $date_to);
		}
		if(!empty($source_city)){
			$this->db->where('ship.source_city', $source_city);
		}
		if(!empty($destination_city)){
			$this->db->where('ship.destination_city', $destination_city);
		}
		if(!empty($customer_id)){
			$this->db->where('ship.shipper_id', $customer_id);
		}
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response = $result;
        return $response;
	}
	
	public function get_daily_reports_cities()
	{
		$response = array();
        $this->db->select('*');
		$this->db->from('cities');
		$this->db->group_by('city'); 
		$query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response = $result;
        return $response;
	}

	public function pickupScanDetails($date_from, $date_to,$id) {
        
        $response = array();
        $this->db->select('*');
		$this->db->from('map_barcode');
		$this->db->where('map_barcode.company_id',$id);
		$this->db->where('map_barcode.pickup_date >=', $date_from);
		$this->db->where('map_barcode.pickup_date <=', $date_to);

        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response = $result;
        return $response;
	}

	public function inscanDetails($date_from, $date_to,$id) {
        
        $response = array();
        $this->db->select('source_inscan.*,employee.name');
		$this->db->from('source_inscan');
		$this->db->join('employee', 'employee.id=source_inscan.emp_id', 'left');
		$this->db->where('source_inscan.c_id',$id);
		$this->db->where('source_inscan.inscan_date >=', $date_from);
		$this->db->where('source_inscan.inscan_date <=', $date_to);

        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response = $result;
        return $response;
	}

	public function outscanDetails($date_from, $date_to,$id) {
        
        $response = array();
        $this->db->select('source_outscan.*,employee.name');
		$this->db->from('source_outscan');
		$this->db->join('employee', 'employee.id=source_outscan.emp_id', 'left');
		$this->db->where('source_outscan.company_id',$id);
		$this->db->where('source_outscan.date >=', $date_from);
		$this->db->where('source_outscan.date <=', $date_to);

        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response = $result;
        return $response;
	}
	
	public function get_challan_details($vehicle, $date_from, $date_to,$from_city,$to_city, $id){
		$response = array();
        $this->db->select('source_outscan.*,source_outscan.date,ship.*,ship.created_at as order_date,ship.id as order_id, vehicle.*, vehicle.id as vehicle_id,tbl_manifest_reports.manifest_no,tbl_manifest_reports.vechile_id,tbl_manifest_reports.id as manifest_id,company.contact,company.email,company.address,company.pincode,company.city_id,company.state_id,company.country_id,company.name as company_name,company.id as comp_id,company.logo');
        $this->db->from('source_outscan');
		$this->db->join('ship', 'source_outscan.awb_no=ship.AWBno', 'left');
		$this->db->join('company', 'ship.company_id=company.id', 'left');
        $this->db->join('vehicle', 'source_outscan.vehicle_id=vehicle.id', 'left');
        $this->db->join('tbl_manifest_reports', 'tbl_manifest_reports.vechile_id=vehicle.id', 'left');
        // $this->db->where('source_outscan.city',$city);
        $this->db->where('source_outscan.vehicle_id', $vehicle);
        $this->db->where('source_outscan.date >=', $date_from);
		$this->db->where('source_outscan.date <=', $date_to);
        $this->db->where('source_outscan.source_city', $from_city);
        $this->db->where('source_outscan.city', $to_city);
        $this->db->where('source_outscan.company_id', $id);
		$this->db->group_by('source_outscan.awb_no');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
	}
	public function get_mis_report($id) {
        $end_date = date('Y-m-d', strtotime("-60 days"));
        //@$newformat = date('d/m/Y',$end_date);
        $response = array();
		$this->db->select('ship.*, ship.id as order_id,source_outscan.*,source_outscan.id as source_outscan,GROUP_CONCAT(source_outscan.date)out_scan_date,source_outscan.awb_no as out_scan_awb_no,destination_outscan.*,destination_outscan.awb_no as des_outscan_awb,GROUP_CONCAT(destination_outscan.date)out_destination_scan_date,destination_outscan.id as destination_outscan,GROUP_CONCAT(tbl_order_status.order_status) order_status,GROUP_CONCAT(tbl_status_master.status_name)status_name,tbl_forwarding.vendor2,customer_contacts.customer_id,customer_contacts.city as source_city_name, Drop_city.city as destination_city_name,customer_contacts.city_id as source_city_id, Drop_city.city_id as destination_city_id,company.id as comp_id,FromCustomer.customer_name from_customer_name, ToCustomer.customer_name as to_customer_name');
        $this->db->from('ship');
        $this->db->join('source_outscan', 'source_outscan.awb_no=ship.AWBno', 'left');
        $this->db->join('destination_outscan', 'destination_outscan.awb_no=ship.AWBno', 'left');
        $this->db->join('tbl_forwarding', 'tbl_forwarding.awb_no=ship.AWBno', 'left');
        $this->db->join('tbl_order_status', 'tbl_order_status.fk_oid=ship.id', 'left');
		$this->db->join('tbl_status_master', 'tbl_order_status.order_status=tbl_status_master.id', 'left');
		$this->db->join('customer_contacts', 'customer_contacts.customer_id=ship.shipper_id', 'left');
        $this->db->join('customer_contacts AS Drop_city', 'Drop_city.customer_id=ship.recepient_id', 'left');
		$this->db->join('company', 'ship.company_id=company.id', 'left');
		$this->db->join('customer_contacts AS FromCustomer', 'ship.shipper_id=FromCustomer.customer_id', 'left');
        $this->db->join('customer_contacts AS ToCustomer', 'ship.recepient_id=ToCustomer.customer_id', 'left');
        $this->db->where("date_format(STR_TO_DATE(ship_date,'%d/%m/%Y'),'%Y-%m-%d') >", $end_date);
        $this->db->group_by('ship.AWBno');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response['data'] = $result;
        return $response;
	}
	public function get_mis_on_cities($comp_id,$transport_type,$transport_speed,$transport_mode) {
        $response = array();
        $this->db->select('*');
        $this->db->from('tat');
        $this->db->where('transport_type', $transport_type);
        $this->db->where('transport_speed', $transport_speed);
        $this->db->where('transport_mode', $transport_mode);
        $this->db->where('company_id', $comp_id);
        $query = $this->db->get();
        $result = $query->result_array();
        $response['status'] = 1;
        $response['message'] = 'success';
        $response = $result;
        return $response;
	}
	
	public function get_track_details($awb_no,$id) {
        $response = array();
        $this->db->select('ship.ship_date,customer_contacts.city');
		$this->db->from('ship');
		$this->db->join('customer_contacts','ship.shipper_id=customer_contacts.customer_id','left');
        $this->db->where('ship.AWBno', $awb_no);
        $this->db->where('ship.company_id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
		// return $result;
		if ($query->num_rows() > 0){
			$response['status'] = 1;
			$response['message'] = 'success';
			$response['data'] = $result;
			return $response;
		}
		else
		{
			$response['status'] = 0;
			$response['message'] = 'AWB No does not exist';
			return $response;
		}
	}
	public function get_forwarding_details($id) {
        $response = array();
        $this->db->select('tbl_forwarding.awb_no,tbl_forwarding.booking_date,tbl_forwarding.destination,GROUP_CONCAT(tbl_forwarding.id)id,GROUP_CONCAT(tbl_forwarding.vendor1) forwarding_name,GROUP_CONCAT(tbl_forwarding.vendor2)forwarding_awb,GROUP_CONCAT(tbl_forwarding_master.name) name,GROUP_CONCAT(tbl_forwarding_master.forward_link) link,GROUP_CONCAT(tbl_forwarding_master.id) master_id');
        $this->db->from('tbl_forwarding');
        $this->db->join('tbl_forwarding_master', 'tbl_forwarding.vendor1=tbl_forwarding_master.id', 'left');
        $this->db->group_by('tbl_forwarding.awb_no');
       
		$this->db->where('tbl_forwarding.company_id', $id);
		$this->db->where('tbl_forwarding.status', "1");
        // $this->db->order_by('tbl_forwarding.id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $response['data'] = $result;
        return $response;
    }

}//class ends here	