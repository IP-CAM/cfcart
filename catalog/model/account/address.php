<?php
class ModelAccountAddress extends Model {
	public function addAddress($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$this->customer->getId() . "', fullname = '" . $this->db->escape($data['fullname']) . "', company = '" . $this->db->escape($data['company']) . "', address = '" . $this->db->escape($data['address']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', city = '" . $this->db->escape($data['city']) . "', district_id = '" . (int)$data['district_id'] . "', city_id = '" . (int)$data['city_id'] . "', zone_id = '" . (int)$data['zone_id'] . "', country_id = '" . (int)$data['country_id'] . "', shipping_telephone = '" . $this->db->escape($data['shipping_telephone']) . "', custom_field = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "'");

		$address_id = $this->db->getLastId();
		
		$total_address = $this->getTotalAddresses();
		
		if($total_address == 1) {
			
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
			
		}else{

			if (!empty($data['default'])) {
				$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
			}
		
		}

		return $address_id;
	}

	public function editAddress($address_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "address SET fullname = '" . $this->db->escape($data['fullname']) . "', company = '" . $this->db->escape($data['company']) . "', address = '" . $this->db->escape($data['address']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', city = '" . $this->db->escape($data['city']) . "', district_id = '" . (int)$data['district_id'] . "', city_id = '" . (int)$data['city_id'] . "', zone_id = '" . (int)$data['zone_id'] . "', zone_id = '" . (int)$data['zone_id'] . "', country_id = '" . (int)$data['country_id'] . "', shipping_telephone = '" . $this->db->escape($data['shipping_telephone']) . "', custom_field = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "' WHERE address_id  = '" . (int)$address_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'");

		if (!empty($data['default'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
		}
		
	}

	public function deleteAddress($address_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "address WHERE address_id = '" . (int)$address_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'");
	}

	public function getAddress($address_id) {
		$address_query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "address WHERE address_id = '" . (int)$address_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'");

		if ($address_query->num_rows) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$address_query->row['country_id'] . "'");

			if ($country_query->num_rows) {
				$country = $country_query->row['name'];
				$iso_code_2 = $country_query->row['iso_code_2'];
				$iso_code_3 = $country_query->row['iso_code_3'];
				$address_format = $country_query->row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$address_query->row['zone_id'] . "'");

			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
				$zone_code = $zone_query->row['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}
			
			if ($address_query->row['country_id'] == 44) {
				$city_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "city` WHERE city_id = '" . (int)$address_query->row['city_id'] . "'");
	
				if ($city_query->num_rows) {
					$city = $city_query->row['name'];
				} else {
					$city = '';
				}
			} else {
				$city = $address_query->row['city'];
			}
			
			$district_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "district` WHERE district_id = '" . (int)$address_query->row['district_id'] . "'");

			if ($district_query->num_rows) {
				$district = $district_query->row['name'];
			} else {
				$district = '';
			}

			$address_data = array(
				'address_id'     => $address_query->row['address_id'],
				'fullname'       => $address_query->row['fullname'],
				'company'        => $address_query->row['company'],
				'address'        => $address_query->row['address'],
				'postcode'       => $address_query->row['postcode'],
				'shipping_telephone'       => $address_query->row['shipping_telephone'],
				'city'           => $city,
				'city_id'        => $address_query->row['city_id'],
				'district'    	 => $district,
				'district_id'    => $address_query->row['district_id'],
				'zone_id'        => $address_query->row['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => $address_query->row['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format,
				'custom_field'   => json_decode($address_query->row['custom_field'], true)
			);

			return $address_data;
		} else {
			return false;
		}
	}

	public function getAddresses() {
		$address_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		foreach ($query->rows as $result) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$result['country_id'] . "'");

			if ($country_query->num_rows) {
				$country = $country_query->row['name'];
				$iso_code_2 = $country_query->row['iso_code_2'];
				$iso_code_3 = $country_query->row['iso_code_3'];
				$address_format = $country_query->row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$result['zone_id'] . "'");

			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
				$zone_code = $zone_query->row['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}
			
			if ($result['country_id'] == 44) {
				$city_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "city` WHERE city_id = '" . (int)$result['city_id'] . "'");
	
				if ($city_query->num_rows) {
					$city = $city_query->row['name'];
				} else {
					$city = '';
				}
			} else {
				$city = $result['city'];
			}
			
			$district_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "district` WHERE district_id = '" . (int)$result['district_id'] . "'");

			if ($district_query->num_rows) {
				$district = $district_query->row['name'];
			} else {
				$district = '';
			}

			$address_data[$result['address_id']] = array(
				'address_id'     	=> $result['address_id'],
				'fullname'      	=> $result['fullname'],
				'company'        	=> $result['company'],
				'address'      		=> $result['address'],
				'postcode'       	=> $result['postcode'],
				'shipping_telephone'	=> $result['shipping_telephone'],
				'city'           	=> $city,
				'city_id'        	=> $result['city_id'],
				'district'    	 	=> $district,
				'district_id'    	=> $result['district_id'],
				'zone_id'        	=> $result['zone_id'],
				'zone'           	=> $zone,
				'zone_code'      	=> $zone_code,
				'country_id'     	=> $result['country_id'],
				'country'        	=> $country,
				'iso_code_2'     	=> $iso_code_2,
				'iso_code_3'     	=> $iso_code_3,
				'address_format' 	=> $address_format,
				'custom_field'   	=> json_decode($result['custom_field'], true)

			);
		}

		return $address_data;
	}

	public function getTotalAddresses() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		return $query->row['total'];
	}
}