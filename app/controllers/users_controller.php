<?php
	class UsersController extends AppController{
		var $name = 'Users';
		var $uses = array('Member', 'Electorate', 'House', 'Portfolio', 'Pcode', 'Party', 'Address');
		var $components = array('Auth', 'RequestHandler');
		var $helpers = array('Html', 'Form', 'Session');
		function index(){
			$this->Auth->loginRedirect = array('controller' => 'users', 'action' => 'index');
		}
		function login(){
		}
		function logout(){
			$this->redirect($this->Auth->logout());
		}
		function upload(){
			if(!empty($this->data)){
//				debug($this->data);
				$csv = fopen($this->data['Member']['submittedfile']['tmp_name'], 'r');
				$j = 0;
				while(!feof($csv)){
					$member_keys = array_keys($this->Member->_schema);
					$line = fgetcsv($csv, 0, ';', '"');
					if($line[1] !== NULL){
						$i = 0;
						foreach($member_keys as $key){
							$member['Member'][$key] = $line[$i];
							$i++;
						}
						
						// see if Electorate exists. Create it if it doesn't, return the id if it does
						$member['Member']['electorate_id'] = $this->Electorate->return_electorate($line[7], $this->data['User']['House']);
						// see if Party exists. Create it if it doesn't, return the id if it does
						
						$member['Member']['party_id'] = $this->Party->return_party($line[8]);
						
						// over ride all members in an electorate??
						if($this->data['Member']['over_ride'] == 1){
							$this->Member->deleteAll(array('electorate_id' => $member['Member']['electorate_id'], 'second_name' => $member['Member']['second_name']));
						}
						
						// add portfolos - easier to add them manually now
						
						if($line[8] !== ''){
							$member['Portfolio']['Portfolio'] = explode(',', $line[9]);
						}
						
						// unset member id so that a new record is created
						unset($member['Member']['id']);
						
						// save
						
						$this->Member->create();
						$this->Member->save($member, array('validate' => false));
						$id = $this->Member->id;
						// add addresses
						$k = 12;
						while(isset($line[$k])){
							if($line[$k] != ''){
								$address['Address'] = array(
									'member_id' => $id,
									'address_type_id' => $line[$k++],
									'postal' => $line[$k++],
									'address1' => $line[$k++],
									'address2' => $line[$k++],
									'suburb' => $line[$k++],
									'state' => $line[$k++],
									'pcode' => $line[$k++],
									'phone' => @$line[$k++],
									'tollfree' => @$line[$k++],
									'fax' => @$line[$k++]
								);
								$this->Address->create();
								$this->Address->save($address);
							}
							else{
								$k = $k + 10;
							}
						}
						
						// unset member to avoid duplication
						
						unset($member);
						unset($address);
						$j++;
					}
				}
				$this->Session->setFlash($j . ' lines executed');
			}
			else{
				$houses = $this->House->find('all');
				$house_array = array();
				foreach($houses as $house){
					$house_array[$house['House']['id']] = $house['House']['name'] . ' (' . $house['House']['state'] . ')';
				}
				$this->set('houses', $house_array);
			}
		}
		function export(){
			if($this->data['users']['House']){ // search results
				$options['conditions'] = array('electorates.house_id' => $this->data['users']['House']);
				$options['contain'] = array('Electorate', 'House');
				$options['joins'] = array(
					array(
						'table' => 'electorates',
						'type' => 'INNER',
						'conditions' => array('members.electorate_id = electorates.id')
					),
					array(
						'table' => 'houses',
						'type' => 'INNER',
						'conditions' => array('electorates.house_id = houses.id')
					)
				);
				$members = $this->Member->Electorate->find('all', $options);
				debug($members);
				$csv_values = array();
				foreach($members as $member){

					$portfolio_ids = array();
					foreach($member['Portfolio'] as $portfolio){
						$portfolio_ids[] = $portfolio['id'];
					}
					$portfolio_ids = join(',', $portfolio_ids);
					
					$i = 1;
					foreach($member['Address'] as $address){
						$addresses['address_type_' . $i] = $address['address_type_id'];
						$addresses['postal_' . $i] = $address['postal'];
						$addresses['address1_' . $i] = $address['address1'];
						$addresses['address2_' . $i] = $address['address2'];
						$addresses['state_' . $i] = $address['state'];
						$addresses['suburb_' . $i] = $address['suburb'];
						$addresses['pcode_' . $i] = $address['pcode'];
						$addresses['phone_' . $i] = $address['phone'];
						$addresses['tollfree_' . $i] = $address['tollfree'];
						$addresses['fax_' . $i] = $address['fax'];
						$i++;
					}

					$line = array(
						'id' => $member['Member']['id'],
						'title' => $member['Member']['title'],
						'first_name' => $member['Member']['first_name'],
						'second_name' => $member['Member']['second_name'],
						'job' => $member['Member']['job'],
						'email' => $member['Member']['email'],
						'party' => $member['Party']['abbreviation'],
						'electorate' => $member['Electorate']['name'],
						'house',
						'state',
						'portfolio' => $portfolio_ids
					);
					$csv_values[] = array_merge($line, $addresses);
				}
				$this->set('members', $csv_values);
			}
			else{
				$houses = $this->House->find('all');
				foreach($houses as $house){
					$house_array[$house['House']['id']] = $house['House']['name'] . ' (' . $house['House']['state'] . ')';
				}
				$this->set('houses', $house_array);
			}
		}
		function mass_action(){
			if($this->data['users']['House']){ // search results
				$this->set('members', $this->Member->find('all', array('conditions' => array('Electorate.house_id' => $this->data['users']['House']))));
			}
			else{
				$houses = $this->House->find('all');
				foreach($houses as $house){
					$house_array[$house['House']['id']] = $house['House']['name'] . ' (' . $house['House']['state'] . ')';
				}
				$this->set('houses', $house_array);
			}
		}
		function mass_delete(){
			foreach($this->data['users'] as $id){
				if($id === 1){
					$this->Member->delete($id);
				}
			}
			$this->redirect($this->referer());
		}
		function csv_template(){
			$this->layout = 'csv';
		}
	}
?>
