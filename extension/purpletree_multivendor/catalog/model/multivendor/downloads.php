<?php
namespace Opencart\Catalog\Model\Extension\PurpletreeMultivendor\Multivendor;
require(DIR_SYSTEM.'library/AWS/S3.php');
class Downloads extends \Opencart\System\Engine\Model {
		private $bucketName = 'tutor-flutter';
		public function addDownload($data) {
			$upload_result = $this->addS3Object($this->db->escape($data['mask']),$this->db->escape($data['filename']));
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "download SET url = '".$this->db->escape($upload_result->get("ObjectURL"))."', filename = '" . $this->db->escape($data['filename']) . "', mask = '" . $this->db->escape($data['mask']) . "', date_added = NOW()");
			
			$download_id = $this->db->getLastId();
			
			
			foreach ($data['download_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "download_description SET download_id = '" . (int)$download_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
			}
			$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_downloads SET download_id='" .$download_id. "', seller_id='".$this->customer->getId()."'");
			return $download_id;
		}
		
		public function editDownload($download_id, $data) {
			$upload_result = $this->addS3Object($this->db->escape($data['mask']),$this->db->escape($data['filename']));
			
			$this->db->query("UPDATE " . DB_PREFIX . "download SET url = '".$this->db->escape($upload_result->get("ObjectURL"))."', filename = '" . $this->db->escape($data['filename']) . "', mask = '" . $this->db->escape($data['mask']) . "' WHERE download_id = '" . (int)$download_id . "'");
			
			$this->db->query("DELETE FROM " . DB_PREFIX . "download_description WHERE download_id = '" . (int)$download_id . "'");
			
			foreach ($data['download_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "download_description SET download_id = '" . (int)$download_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
			}
		}
		
		public function deleteDownload($download_id) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "download WHERE download_id = '" . (int)$download_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "download_description WHERE download_id = '" . (int)$download_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_downloads WHERE seller_id ='". $this->customer->getId() ."' AND download_id = '" . (int)$download_id . "'");
			
		}
		
		public function getDownload($download_id) {
			$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "download d LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id) JOIN ". DB_PREFIX ."purpletree_vendor_downloads pvd ON (d.download_id = pvd.download_id) WHERE pvd.seller_id ='". $this->customer->getId() ."' AND d.download_id = '" . (int)$download_id . "' AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
			
			return $query->row;
		}
		
		public function getDownloads($data = array()) {
			$sql = "SELECT * FROM " . DB_PREFIX . "download d LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id) JOIN ". DB_PREFIX ."purpletree_vendor_downloads pvd ON (d.download_id = pvd.download_id) WHERE pvd.seller_id ='". $this->customer->getId() ."' AND  dd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			
			if (!empty($data['filter_name'])) {
				$sql .= " AND dd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
			}
			
			$sort_data = array(
			'dd.name',
			'd.date_added'
			);
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
				} else {
				$sql .= " ORDER BY dd.name";
			}
			
			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
				} else {
				$sql .= " ASC";
			}
			
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}
				
				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}
				
				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}
			
			$query = $this->db->query($sql);
			
			return $query->rows;
		}
		
		public function getDownloadDescriptions($download_id) {
			$download_description_data = array();
			
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "download_description dd JOIN ". DB_PREFIX ."purpletree_vendor_downloads pvd ON (dd.download_id = pvd.download_id) WHERE pvd.seller_id ='". $this->customer->getId() ."' AND dd.download_id = '" . (int)$download_id . "' ");
			
			foreach ($query->rows as $result) {
				$download_description_data[$result['language_id']] = array('name' => $result['name']);
			}
			
			return $download_description_data;
		}
		
		public function getSellerName() {
			
		}
		public function getTotalDownloads() {
			$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "download d JOIN ". DB_PREFIX ."purpletree_vendor_downloads pvd ON (d.download_id = pvd.download_id) WHERE pvd.seller_id ='". $this->customer->getId() ."'");
			
			return $query->row['total'];
		}
		public function getTotalProductsByDownloadId($download_id) {
			$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_download WHERE download_id = '" . (int)$download_id . "'");
			
			return $query->row['total'];
		}
		private function addS3Object($fileName,$file){
			$ext = pathinfo($fileName, PATHINFO_EXTENSION);
			if($ext == 'pdf'){
				$folder = 'vendors/'.$this->customer->getId().'/pdf/';
			}else{
				$folder = 'vendors/'.$this->customer->getId().'/videos/';
			}
			$s3 = new \Opencart\System\Library\AWS\S3($this->config,"us-east-1");
			$s3_result = $s3->putObject($folder.$fileName,$this->bucketName,DIR_DOWNLOAD.$file);
			return $s3_result;
		}
}