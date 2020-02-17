public function get_mis_on_cities(){
            $response = array('code' => - 1, 'status' => false, 'message' => '');
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $transport_type = $this->input->post('transport_type');
                $transport_speed = $this->input->post('transport_speed');
                $transport_mode = $this->input->post('transport_mode');
                $comp_id = $this->input->post('comp_id');
                
                $response = $this->model->get_mis_on_cities($comp_id,$transport_type,$transport_speed,$transport_mode);
                $tat = unserialize($response[0]['time']);


                
                
                $all_zones = $this->model->getData('zone');
                $zone_cities = null;
                foreach($all_zones as $value){
                    $zone_cities[$value['id']] = $this->model->getValue('zone','cities',['id'=>$value['id']]);
                }

                $response['tat'] =$tat;
                $response['zone_cities'] =$zone_cities;


            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
            echo json_encode($response);
        }



        public function get_mis_on_cities(){
            $response = array('code' => - 1, 'status' => false, 'message' => '');
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $transport_type = $this->input->post('transport_type');
                $transport_speed = $this->input->post('transport_speed');
                $transport_mode = $this->input->post('transport_mode');
                $comp_id = $this->input->post('comp_id');
                
                $response = $this->model->get_mis_on_cities($comp_id,$transport_type,$transport_speed,$transport_mode);
                $tat = unserialize($response[0]['time']);
                $zone_ids = [];
                foreach($tat as $from_zone=>$value){
                    if(!in_array($from_zone,$zone_ids)){
                        $zone_ids[] = $from_zone;
                    }
                    foreach($value as $to_zone=>$tat){
                        if(!in_array($to_zone,$zone_ids)){
                            $zone_ids[] = $to_zone;
                        }
                    }
                }

                
                
                $all_zones = $this->model->getData('zone');
                $zone_cities = null;
                foreach($all_zones as $value){
                    $zone_cities[$value['id']] = $this->model->getValue('zone','cities',['id'=>$value['id']]);
                }

                

                $response['tat'] =$tat;
                $response['zone_cities'] =$zone_cities;


            } else {
                $response['message'] = 'Invalid Request';
                $response['code'] = 204;
            }
            echo json_encode($response);
        }