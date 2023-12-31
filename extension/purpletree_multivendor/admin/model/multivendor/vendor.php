<?php
namespace Opencart\Admin\Model\Extension\PurpletreeMultivendor\Multivendor;
class Vendor extends \Opencart\System\Engine\Model {
	   ////// quick order //////
		public function getQucikOrderStatus($product_id){
			$query = $this->db->query("SELECT status FROM " . DB_PREFIX . "purpletree_vendor_quick_order_product WHERE product_id='".(int)$product_id."'");
			
			if($query->num_rows){
				return $query->row['status'];
				} else {
				return NULL;	
			}	
		}
		////// End quick order //////
		public function getSeller(array $data = []): array {
			$sql = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS name, cgd.name AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) RIGHT JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs  ON (pvs.seller_id = c.customer_id) ";
			
			if (!empty($data['filter_affiliate'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "customer_affiliate ca ON (c.customer_id = ca.customer_id)";
			}		
			
			$sql .= " WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			$sql .= " AND c.status=1";
			
			$implode = array();
			
			if (!empty($data['filter_name'])) {
				$implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape((string)$data['filter_name']) . "%'";
			}
			
			if (!empty($data['filter_email'])) {
				$implode[] = "c.email LIKE '" . $this->db->escape((string)$data['filter_email']) . "%'";
			}
			
			if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter'])) {
				$implode[] = "c.newsletter = '" . (int)$data['filter_newsletter'] . "'";
			}
			
			if (!empty($data['filter_customer_group_id'])) {
				$implode[] = "c.customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
			}
			
			if (!empty($data['filter_affiliate'])) {
				$implode[] = "ca.status = '" . (int)$data['filter_affiliate'] . "'";
			}
			
			if (!empty($data['filter_ip'])) {
				$implode[] = "c.customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($data['filter_ip']) . "')";
			}
			
			if (isset($data['filter_status']) && $data['filter_status'] !== '') {
				$implode[] = "c.status = '" . (int)$data['filter_status'] . "'";
			}
			
			if (!empty($data['filter_date_added'])) {
				$implode[] = "DATE(c.date_added) = DATE('" . $this->db->escape((string)$data['filter_date_added']) . "')";
			}
			
			if ($implode) {
				$sql .= " AND " . implode(" AND ", $implode);
			}
			
			$sort_data = array(
			'name',
			'c.email',
			'customer_group',
			'c.status',
			'c.ip',
			'c.date_added'
			);
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
				} else {
				$sql .= " ORDER BY name";
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
		public function getTotalVendors($data = array()) {		
			$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id AND c.status=1) JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON(pvs.seller_id=c.customer_id) ";
			$sql .= " WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			
			$implode = array();
			
			if (!empty($data['filter_name'])) {
				$implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
			}
			
			if (!empty($data['filter_email'])) {
				$implode[] = "c.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
			}
			
			if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
				$implode[] = "c.status = '" . (int)$data['filter_status'] . "'";
			}
			
			if (!empty($data['filter_date_added'])) {
				$implode[] = "DATE(c.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
			}
			
			if ($implode) {
				$sql .= " AND " . implode(" AND ", $implode);
			}
			
			$query = $this->db->query($sql);
			
			return $query->row['total'];
		}
		
		public function getVendors(array $data = []): array {
			$sql = "SELECT *,pvs.seller_id, CONCAT(c.firstname, ' ', c.lastname) AS name, cgd.name AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id AND c.status=1) JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON(pvs.seller_id=c.customer_id) ";
			$sql .= " WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			
			$implode = array();
			
			if (!empty($data['filter_name'])) {
				$implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape((string)$data['filter_name']) . "%'";
			}
			
			if (!empty($data['filter_email'])) {
				$implode[] = "c.email LIKE '" . $this->db->escape((string)$data['filter_email']) . "%'";
			}
			
			if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
				$implode[] = "c.status = '" . (int)$data['filter_status'] . "'";
			}
			
			if (!empty($data['filter_date_added'])) {
				$implode[] = "DATE(c.date_added) = DATE('" . $this->db->escape((string)$data['filter_date_added']) . "')";
			}
			
			if ($implode) {
				$sql .= " AND " . implode(" AND ", $implode);
			}
			$sql .= "GROUP BY pvs.seller_id";
			$sort_data = array(
			'name',
			'c.email',
			'customer_group',
			'c.status',
			'c.approved',
			'c.ip',
			'c.date_added'
			);
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $this->db->escape($data['sort']);
				} else {
				$sql .= " ORDER BY name";
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
		
		public function addSeller($customer_id,$store_name,$filename = ''){
			$this->db->query("INSERT into " . DB_PREFIX . "purpletree_vendor_stores SET seller_id ='".(int)$customer_id."', store_name='".$this->db->escape($store_name)."', store_status='1', multi_store_id='".(int)($this->config->get('config_store_id'))."',store_created_at= NOW(), store_updated_at= NOW()");
		}
		
		public function editSeller($customer_id,$store_name,$become_seller,$filename = ''){
			
			$query = $this->db->query("SELECT id FROM " . DB_PREFIX . "purpletree_vendor_stores WHERE seller_id = '".(int)$customer_id."'");
			if($query->num_rows){
				$seller_status = (((int)$become_seller=="1")?'1':'0');
				$is_removed = (((int)$become_seller=="1"?'0':'1'));			
				if($is_removed){								 
					$this->db->query("DELETE FROM ". DB_PREFIX . "purpletree_vendor_stores WHERE seller_id ='".(int)$customer_id."'");
				}
				$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_stores SET store_name='".$this->db->escape($store_name)."', store_status='".(int)$seller_status."', is_removed='".(int)$is_removed."', store_updated_at= NOW() WHERE seller_id='".(int)$customer_id."'");
				} else {
				if($become_seller=="1"){
					$this->db->query("INSERT into " . DB_PREFIX . "purpletree_vendor_stores SET seller_id ='".(int)$customer_id."', store_name='".$this->db->escape($store_name)."', multi_store_id='".(int)($this->config->get('config_store_id'))."',store_status='1',store_created_at= NOW(), store_updated_at= NOW()");
				}
			}
		}
		
		public function getSellerStorename($store_name,$seller_id=NULL){
			
			$sql = "SELECT id FROM " . DB_PREFIX . "purpletree_vendor_stores where store_name='".$this->db->escape($store_name)."'";
			if(!empty($seller_id)){
				$sql .= "AND seller_id!='".(int)$seller_id."'";
			}
			$query = $this->db->query($sql);
			
			return $query->num_rows;
		}
		public function getSubscriptionplan($seller_id) {
			
			$query = $this->db->query("SELECT status_id FROM " . DB_PREFIX . "purpletree_vendor_plan_subscription WHERE seller_id='".(int)$seller_id."'");
			if($query->num_rows) {
				return $query->row['status_id'];
			}
			return '';
		}
		
	    public function disableSeller($seller_id) {
			
			$query = $this->db->query("UPDATE " . DB_PREFIX . "customer SET status=0 WHERE customer_id='".(int)$seller_id."'");		
			
			$query2 = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_products SET is_approved=0 WHERE seller_id='".(int)$seller_id."'");	
			
			$query3 = $this->db->query("UPDATE " . DB_PREFIX . "product SET status=0 WHERE product_id IN (SELECT product_id FROM " . DB_PREFIX . "purpletree_vendor_products WHERE seller_id='".(int)$seller_id."')");
			
			$query4 = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_plan_subscription SET status_id=0 WHERE seller_id='".(int)$seller_id."'");
			
			$query5 = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_stores SET store_status=0 WHERE seller_id='".(int)$seller_id."'");
			
		}
		
		public function checkCategoryassign($cat_id) {
			$query = $this->db->query("SELECT pvp.product_id FROM " . DB_PREFIX . "purpletree_vendor_products pvp JOIN " . DB_PREFIX . "product_to_category pc ON(pc.product_id=pvp.product_id) WHERE pc.category_id='".(int)$cat_id."'");
			return $query->num_rows;
		}
		
		public function getProductSeller($product_id){
			$query = $this->db->query("SELECT seller_id FROM " . DB_PREFIX . "purpletree_vendor_products WHERE product_id='".(int)$product_id."'");
			
			return $query->row;
		}
		public function getSubscriptionsubs($seller_id) {
			$query=$this->db->query("SELECT id FROM ". DB_PREFIX . "purpletree_vendor_plan_subscription  WHERE status_id=1 AND seller_id = '" . (int)$seller_id . "'");
			if($query->num_rows){
				if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
					$obj_plan=$this->db->query("SELECT pvpd.plan_name,pvp.plan_id FROM ". DB_PREFIX . "purpletree_vendor_plan_invoice pvpi LEFT JOIN ". DB_PREFIX . "purpletree_vendor_plan_description pvpd ON (pvpi.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX . "purpletree_vendor_plan pvp ON (pvp.plan_id=pvpd.plan_id) WHERE pvpi.status_id=2 AND pvpi.invoice_id IN (SELECT invoice_id FROM ". DB_PREFIX . "purpletree_vendor_seller_plan pvsp WHERE pvsp.new_status=1 AND pvpd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pvsp.seller_id='".(int)$seller_id."' ) AND pvp.status=1");
					} else {
					$obj_plan=$this->db->query("SELECT pvpd.plan_name,pvp.plan_id FROM ". DB_PREFIX . "purpletree_vendor_plan_invoice pvpi LEFT JOIN ". DB_PREFIX . "purpletree_vendor_plan_description pvpd ON (pvpi.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX . "purpletree_vendor_plan pvp ON (pvp.plan_id=pvpd.plan_id) WHERE pvpi.status_id=2 AND pvpi.invoice_id IN (SELECT invoice_id FROM ". DB_PREFIX . "purpletree_vendor_seller_plan pvsp WHERE pvsp.status=1 AND pvpd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pvsp.seller_id='".(int)$seller_id."' AND pvp.status=1 ) ");	
				}
				if($obj_plan->num_rows){
					return $obj_plan->rows;
					} else {
					return NULL;
				}
				
				} else {
				return NULL;	
			}
			
		}
		
		public function addsubs($data = array()){
			
			if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
				$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_subscription_products SET product_id = '" . (int)$data['product_id']."', product_plan_id = '".(int)$data['product_plan_id']."', featured_product_plan_id = '".(int)$data['featured_product_plan_id']."', category_featured_product_plan_id = '".(int)$data['category_featured_product_plan_id']."'");
			};
		}	
		public function editsubs($data = array()){
			
			if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
				$obj_product=$this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_subscription_products WHERE product_id = '" . (int)$product_id."'");
				if($obj_product->num_rows>0){
					$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_subscription_products SET product_plan_id = '".(int)$data['product_plan_id']."', featured_product_plan_id = '".(int)$data['featured_product_plan_id']."', category_featured_product_plan_id = '".(int)$data['category_featured_product_plan_id']."' WHERE product_id = '" . (int)$product_id."'");								
					} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_subscription_products SET product_id = '" . (int)$product_id."', product_plan_id = '".(int)$data['product_plan_id']."', featured_product_plan_id = '".(int)$data['featured_product_plan_id']."', category_featured_product_plan_id = '".(int)$data['category_featured_product_plan_id']."'");			
				}
				
			}
		}
		public function productPlanInfo($seller_id) {
			$query=$this->db->query("SELECT id FROM ". DB_PREFIX . "purpletree_vendor_plan_subscription  WHERE status_id=1 AND seller_id = '" . (int)$seller_id . "'");
			if($query->num_rows){
				if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
					$obj_plan_name=$this->db->query("SELECT pvpd.plan_name,pvp.plan_id FROM ". DB_PREFIX . "purpletree_vendor_plan_invoice pvpi LEFT JOIN ". DB_PREFIX . "purpletree_vendor_plan_description pvpd ON (pvpi.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX . "purpletree_vendor_plan pvp ON (pvp.plan_id=pvpd.plan_id) WHERE pvpi.status_id=2 AND pvpi.invoice_id IN (SELECT invoice_id FROM ". DB_PREFIX . "purpletree_vendor_seller_plan pvsp WHERE pvsp.new_status=1 AND pvpd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pvsp.seller_id='".(int)$seller_id."' ) AND pvp.status=1");
					} else {
					$obj_plan_name=$this->db->query("SELECT pvpd.plan_name,pvp.plan_id FROM ". DB_PREFIX . "purpletree_vendor_plan_invoice pvpi LEFT JOIN ". DB_PREFIX . "purpletree_vendor_plan_description pvpd ON (pvpi.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX . "purpletree_vendor_plan pvp ON (pvp.plan_id=pvpd.plan_id) WHERE pvpi.status_id=2 AND pvpi.invoice_id IN (SELECT invoice_id FROM ". DB_PREFIX . "purpletree_vendor_seller_plan pvsp WHERE pvsp.status=1 AND pvpd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pvsp.seller_id='".(int)$seller_id."' AND pvp.status=1 ) ");	
				}
				if($obj_plan_name->num_rows){
					return $obj_plan_name->rows;
					} else {
					return NULL;
				}
				
				} else {
				return NULL;	
			}
			
		}
		public function productPlanName($product_id) {
			$query=$this->db->query("SELECT product_plan_id FROM ". DB_PREFIX . "purpletree_vendor_subscription_products  WHERE product_id = '" . (int)$product_id . "'");
			if($query->num_rows){
				return $query->row['product_plan_id'];
				} else {
				return NULL;	
			}
			
		}
		public function featuredProductPlanName($product_id) {
			$query=$this->db->query("SELECT featured_product_plan_id  FROM ". DB_PREFIX . "purpletree_vendor_subscription_products  WHERE product_id = '" . (int)$product_id . "'");
			if($query->num_rows){
				return $query->row['featured_product_plan_id'];
				} else {
				return NULL;	
			}
		}
		public function categoryFeaturedProductPlanName($product_id) {
			$query=$this->db->query("SELECT category_featured_product_plan_id  FROM ". DB_PREFIX . "purpletree_vendor_subscription_products  WHERE product_id = '" . (int)$product_id . "'");
			if($query->num_rows){
				return $query->row['category_featured_product_plan_id'];
				} else {
				return NULL;	
			}
			
		}
		public function getsellerProducts($seller_id) {
			$sellerProducts = array();
			$query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "purpletree_vendor_products WHERE seller_id = '".(int)$seller_id."'");
			if($query->num_rows){
				$sellerProducts = $query->rows;
			}
			return $sellerProducts;
		}
		public function deleteAllinfoSeller($seller_id) {
			
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_blog_post_comment WHERE blog_post_id IN (SELECT blog_post_id FROM " . DB_PREFIX . "purpletree_vendor_blog_post WHERE seller_id = '" . (int)$seller_id . "')");	
			
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_blog_post_comment WHERE blog_post_id IN (SELECT blog_post_id FROM " . DB_PREFIX . "purpletree_vendor_blog_post WHERE seller_id = '" . (int)$seller_id . "')");	
			
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_subscription_products WHERE product_id IN (SELECT product_id FROM " . DB_PREFIX . "purpletree_vendor_products WHERE seller_id = '" . (int)$seller_id . "')");	
			
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_social_links WHERE store_id IN (SELECT id FROM " . DB_PREFIX . "purpletree_vendor_stores WHERE seller_id = '" . (int)$seller_id . "')");	
			
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice_item WHERE invoice_id IN (SELECT invoice_id FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice WHERE seller_id = '" . (int)$seller_id . "')");	
			
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice_history WHERE invoice_id IN (SELECT invoice_id FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice WHERE seller_id = '" . (int)$seller_id . "')");	
			
			
			$tables=array("purpletree_vendor_stores","purpletree_vendor_shipping","purpletree_vendor_seller_plan","purpletree_vendor_reviews","purpletree_vendor_products","purpletree_vendor_plan_subscription","purpletree_vendor_payments","purpletree_vendor_orders_history","purpletree_vendor_orders","purpletree_vendor_downloads","purpletree_vendor_contact","purpletree_vendor_commission_invoice_items","purpletree_vendor_commissions","purpletree_vendor_blog_post","purpletree_order_total"); 
			
			foreach($tables as $table){
				$this->db->query("DELETE FROM `" . DB_PREFIX . "".$table."` WHERE seller_id = '" . (int)$seller_id . "'");	
			} 
		}
		public function addAdmintemp($product_id,$data = array()) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_template SET product_id = '" . (int)$product_id . "', status = '" . (int)$data['status'] . "'");
		}	
		public function editAdmintemp($product_id,$data) {
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_template SET status = '" . (int)$data['status'] . "' WHERE product_id = '" . (int)$product_id . "'");
		}
		public function geAdmintempProduct($data = array()) {
			$sql = "SELECT CONCAT(c.firstname, ' ', c.lastname) AS seller_name,pvtp.seller_id AS template_seller_id,pvtp.quantity AS template_quantity,pvtp.price AS template_price,pvtp.status AS template_status,pvtp.stock_status_id AS template_stock_status_id, pvt.id as template_id ,pvt.product_id,pvt.status AS pvt_status, pd.*,p.* FROM " . DB_PREFIX . "purpletree_vendor_template pvt LEFT JOIN " . DB_PREFIX . "product p ON (pvt.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template_products pvtp ON (pvt.id = pvtp.template_id) LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id = pvtp.seller_id)";
			
			$sql.=" WHERE pd.language_id = '" . (int)$this->config->get('config_language_id')  . "' ";
			
			if (!empty($data['filter_name'])) {
				$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
			}
			
			if (!empty($data['filter_model'])) {
				$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
			}
			
			if (isset($data['filter_status']) && $data['filter_status'] !== '') {
				$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
			}
			
			$sql .= " GROUP BY p.product_id";
			
			$sort_data = array(
			'pd.name',
			'p.model',
			'p.status',
			'p.sort_order'
			);
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
				} else {
				$sql .= " ORDER BY pd.name";
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
		public function getTotalAdmintempProduct($data = array()) {
			$sql = "SELECT COUNT(*) AS total, CONCAT(c.firstname, ' ', c.lastname) AS seller_name,pvtp.seller_id AS template_seller_id,pvtp.quantity AS template_quantity,pvtp.price AS template_price,pvtp.status AS template_status,pvtp.stock_status_id AS template_stock_status_id, pvt.id as template_id ,pvt.product_id,pvt.status, pd.*,p.* FROM " . DB_PREFIX . "purpletree_vendor_template pvt LEFT JOIN " . DB_PREFIX . "product p ON (pvt.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template_products pvtp ON (pvt.id = pvtp.template_id) LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id = pvtp.seller_id)";
			
			$sql.=" WHERE pd.language_id = '" . (int)$this->config->get('config_language_id')  . "' ";
			
			if (!empty($data['filter_name'])) {
				$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
			}
			
			if (!empty($data['filter_model'])) {
				$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
			}
			
			if (isset($data['filter_status']) && $data['filter_status'] !== '') {
				$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
			}
			
			$sql .= " GROUP BY p.product_id";
			
			$sort_data = array(
			'pd.name',
			'p.model',
			'p.status',
			'p.sort_order'
			);
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
				} else {
				$sql .= " ORDER BY pd.name";
			}
			
			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
				} else {
				$sql .= " ASC";
			}			
			
			$query = $this->db->query($sql);
			
			if($query->num_rows) {
				return $query->num_rows;
				} else {
				return 0;
			}
		}
		public function deleteAdmintempProduct($product_id) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_template WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_temp_product_option WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_temp_product_option_value WHERE product_id = '" . (int)$product_id . "'");
		}		
		public function getTemplateDetail($id) { 
			
			$query = $this->db->query("SELECT pvtp.*,pvs.store_name FROM " . DB_PREFIX . "purpletree_vendor_template_products pvtp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON (pvs.seller_id = pvtp.seller_id) WHERE pvtp.id ='".(int)$id."'");
			
			return $query->row;
		}	
		public function getTemplateId($template_id) {
			$query = $this->db->query("SELECT id FROM " . DB_PREFIX . "purpletree_vendor_template WHERE id ='".(int)$template_id."'");
			
			return $query->row['id'];
		} 	
		public function getEditTemplateData($template_id,$seller_id,$id) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_template_products WHERE template_id = '" . (int)$template_id . "' AND seller_id = '" . (int)$seller_id . "' AND id!='".(int) $id."'");
			return $query->num_rows;
		} 
		
		public function getTemplateData($template_id,$seller_id) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_template_products WHERE template_id = '" . (int)$template_id . "' AND seller_id = '" . (int)$seller_id . "'");
			return $query->num_rows;
		} 
		public function editDetail($template_id,$data) {

			$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_template_products SET template_id = '" . (int)$data['template_id'] . "', seller_id = '" . (int)$data['seller_id_filter'] . "', quantity = '" . (int)$data['quantity'] . "', price = '" . (int)$data['price'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', subtract = '" . (int)$data['subtract'] . "', status = '" . (int)$data['status'] . "'");
			$template_product_id = $this->db->getLastId();
		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio') {
					if (isset($product_option['product_option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_temp_product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$data['product_id'] . "', temp_product_id = '" . (int)$template_product_id . "', option_id = '" . (int)$product_option['option_id'] . "'");

						$product_option_id = $this->db->getLastId();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_temp_product_option_value SET product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "', product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$data['product_id'] . "', temp_product_id = '" . (int)$template_product_id . "',seller_id = '" . (int)$data['seller_id_filter'] . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "',subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "'");
						}
					}
				}
			}
		}
		
		}  	
		public function addDetail($template_id,$data) {
			
			/* $query = $this->db->query("SELECT template_id FROM " . DB_PREFIX . "purpletree_vendor_template_products  WHERE  template_id = " . (int)$template_id . "");
			if($query->num_rows < 0){ */
			$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_template_products SET template_id = '" . (int)$data['template_id'] . "', seller_id = '" . (int)$data['vendor'] . "', quantity = '" . (int)$data['quantity'] . "', price = '" . (int)$data['price'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', subtract = '" . (int)$data['subtract'] . "', status = '" . (int)$data['status'] . "'");
			//}
		} 
		public function getAdmintempProductId($product_id) {
			$query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "purpletree_vendor_template WHERE product_id ='".(int)$product_id."'");
			
			return $query->row;
		} 
		public function geSellerproducttemp($data = array()) {
			$sql = "SELECT p.model,pvtps.status,pvs.store_name AS store_name,pd.name as product_name,pvtps.id,p.product_id,p.image,pvtps.status AS template_status,pvtps.quantity,pvtps.price,pvtps.stock_status_id,pvtps.template_id,ss.name FROM " . DB_PREFIX . "purpletree_vendor_template_products pvtps LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template pvt ON (pvt.id = pvtps.template_id) LEFT JOIN " . DB_PREFIX . "product p ON (pvt.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "stock_status ss ON (ss.stock_status_id = pvtps.stock_status_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON (pvs.seller_id = pvtps.seller_id)";
			
			$sql.=" WHERE pd.language_id = '" . (int)$this->config->get('config_language_id')  . "' AND ss.language_id='" . (int)$this->config->get('config_language_id')  . "'";
			
			if (!empty($data['filter_store'])) {
				$sql .= " AND pvs.store_name LIKE '" . $this->db->escape($data['filter_store']) . "%'";
			}		
			
			if (!empty($data['filter_name'])) {
				$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
			}
			
			if (!empty($data['filter_model'])) {
				$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
			}
			if (!empty($data['filter_price'])) {
				$sql .= " AND pvtps.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
			}		
			if (!empty($data['filter_quantity'])) {
				$sql .= " AND pvtps.quantity LIKE '" . $this->db->escape($data['filter_quantity']) . "%'";
			}
			
			if (isset($data['filter_status']) && $data['filter_status'] !== '') {
				$sql .= " AND pvtps.status = '" . (int)$data['filter_status'] . "'";
			}
			
			$sql .= " GROUP BY pvtps.id";
			
			$sort_data = array(
			'pvs.store_name',
			'pd.name',
			'p.model',
			'pvtps.status',
			'pvtps.price',
			'pvtps.quantity',
			'p.sort_order'
			);
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
				} else {
				$sql .= " ORDER BY pd.name";
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
		public function geSellerproducttemptotal($data = array()) {
			
			$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "purpletree_vendor_template_products pvtps LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template pvt ON (pvt.id = pvtps.template_id) LEFT JOIN " . DB_PREFIX . "product p ON (pvt.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "stock_status ss ON (ss.stock_status_id = pvtps.stock_status_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON (pvs.seller_id = pvtps.seller_id)";
			
			$sql.=" WHERE pd.language_id = '" . (int)$this->config->get('config_language_id')  . "' AND ss.language_id='" . (int)$this->config->get('config_language_id')  . "'";
			
			if (!empty($data['filter_name'])) {
				$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
			}
			
			if (!empty($data['filter_store'])) {
				$sql .= " AND pvs.store_name LIKE '" . $this->db->escape($data['filter_store']) . "%'";
			}	
			
			if (!empty($data['filter_model'])) {
				$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
			}
			
			if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
				$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
			}
			
			if (isset($data['filter_status']) && $data['filter_status'] !== '') {
				$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
			}
			
			$sql .= " GROUP BY p.product_id";
			
			$sort_data = array(
			'pvs.store_name',
			'pd.name',
			'p.model',
			'pvtps.status',
			'pvtps.price',
			'p.sort_order'
			);
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
				} else {
				$sql .= " ORDER BY pd.name";
			}
			
			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
				} else {
				$sql .= " ASC";
			}		
			$query = $this->db->query($sql);
			if($query->num_rows) {
				return $query->num_rows;
				} else {
				return 0;
			}
		}
		
		public function deletesellertempProduct($id) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_template_products WHERE id = '" . (int)$id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_temp_product_option WHERE temp_product_id = '" . (int)$id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_temp_product_option_value WHERE temp_product_id = '" . (int)$id . "'");
		}	
		public function getTemplateId1($id) {
			$query = $this->db->query("SELECT template_id FROM " . DB_PREFIX . "purpletree_vendor_template_products WHERE id ='".(int)$id."'");
			
			return $query->row['template_id'];
		}
		public function checkTempstatus($id) {
			$query = $this->db->query("SELECT id FROM " . DB_PREFIX . "purpletree_vendor_template WHERE product_id ='".(int)$id."'");
			
			 if($query->num_rows){
			     $temp_id = $query->row['id'];
				 }else{
			     $temp_id = 0;
				 }
			 
			 $query1 = $this->db->query("SELECT id FROM " . DB_PREFIX . "purpletree_vendor_template_products WHERE template_id ='".(int)$temp_id."'AND status = 1");
			 if($query1->num_rows) {
				return 1;
				} else {
				return 0;
				}
		}
		public function getProductid($id) {
			$query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "purpletree_vendor_template WHERE id ='".(int)$id."'");
			
			 return $query->row['product_id'];
		}
		
	public function editsellertempDetail($id,$data=array()) {
			$query = $this->db->query("SELECT id FROM " . DB_PREFIX . "purpletree_vendor_template_products WHERE id = '".(int)$id."'");
			if($query->num_rows > 0){
				$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_template_products SET template_id ='".$data['template_id']."', seller_id='".(int)$data['seller_id_filter']."',quantity='".(int)$data['quantity']."', price='".(float)$data['price']."',stock_status_id='".(int)$data['stock_status_id']."',subtract='".(int)$data['subtract']."',status='".(int)$data['status']."' WHERE id='".(int)$id."'");
			}  
	
		$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_temp_product_option WHERE temp_product_id = '" . (int)$id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_temp_product_option_value WHERE temp_product_id = '" . (int)$id . "'");

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio') {
					if (isset($product_option['product_option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_temp_product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$data['product_id'] . "', temp_product_id = '" . (int)$id . "', option_id = '" . (int)$product_option['option_id'] . "'");

						$product_option_id = $this->db->getLastId();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_temp_product_option_value SET product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "', product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$data['product_id'] . "', temp_product_id = '" . (int)$id . "',seller_id = '" . (int)$data['seller_id_filter'] . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "',subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "'");
						}
					}
				}
			}
		}
		}
		
		public function getProductOptions($temp_product_id) {
		$product_option_data = array();

		$product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "purpletree_temp_product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.temp_product_id = '" . (int)$temp_product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order ASC");

		foreach ($product_option_query->rows as $product_option) {
			$product_option_value_data = array();

			$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_temp_product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON(pov.option_value_id = ov.option_value_id) WHERE pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' ORDER BY ov.sort_order ASC");

			foreach ($product_option_value_query->rows as $product_option_value) {
				$product_option_value_data[] = array(
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'option_value_id'         => $product_option_value['option_value_id'],
					'quantity'                => $product_option_value['quantity'],
					'subtract'                   => $product_option_value['subtract'],
					'price'                   => $product_option_value['price'],
					'price_prefix'            => $product_option_value['price_prefix']
				);
			}
			$product_option_data[] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'value'                => $product_option['value']
			);
		}

		return $product_option_data;
	}
	 public function getTemplateProductStatus($product_id,$option_id) {
			$sql = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$option_id . "'");
			if($sql->num_rows){
			return $sql->row;
			}
			return null;
		}
		 public function getTemplateProductOption($data) {
			$sql = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_option_id = '" . (int)$data['product_option_id'] . "' AND product_id = '" . (int)$data['product_id'] . "' AND option_id = '" . (int)$data['option_id'] . "' AND option_value_id = '" . (int)$data['option_value_id'] . "'");
			if($sql->num_rows){
				return true;
			}
			return false; 	 	
		}
		
	 public function getOptionValuesByProductOptId($option_id,$product_id) {
			$option_value_data = array();

			$option_value_query = $this->db->query("SELECT pov.option_value_id,ovd.name,ov.image,ov.sort_order FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON(pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.option_id = '" . (int)$option_id . "' AND pov.product_id = '" . (int)$product_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order, ovd.name");


			foreach ($option_value_query->rows as $option_value) {
				$option_value_data[] = array(
					'option_value_id' => $option_value['option_value_id'],
					'name'            => $option_value['name'],
					'image'           => $option_value['image'],
					'sort_order'      => $option_value['sort_order']
				);
			}
			return $option_value_data;
		}
		
		public function getMinPrice($product_id) {
			$sql = $this->db->query("SELECT MIN(price) AS min_price,pvtp.quantity,pvtp.stock_status_id,pvtp.subtract,pvt.product_id FROM " . DB_PREFIX . "purpletree_vendor_template_products pvtp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template pvt ON (pvtp.template_id = pvt.id) WHERE pvt.product_id = '" . (int)$product_id . "'");
			
			return $sql->row;
		}
		
	public function getSellerSubscriptionPlan($seller_id) {	
		$status='status';
		if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
		$status='new_status';	
	  }
       $sql = "SELECT pvsp.plan_id,pvpd.plan_name FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp LEFT JOIN  " . DB_PREFIX . "purpletree_vendor_plan_subscription pvps ON(pvsp.seller_id=pvps.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_description pvpd ON(pvsp.plan_id=pvpd.plan_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan pvp ON(pvp.plan_id=pvpd.plan_id) WHERE pvsp.seller_id='".(int)$seller_id."' AND pvsp.".$status."=1 AND pvps.status_id=1 AND pvpd.language_id='" . (int)$this->config->get('config_language_id') . "' AND pvp.status=1";
		$query = $this->db->query($sql);
			if($query->num_rows>0){
					return $query->rows;
			}else {
				return NULL;
			}
	}
	public function getSellerProductPlan($product_id) {	
       $sql = "SELECT product_plan_id,featured_product_plan_id,category_featured_product_plan_id FROM " . DB_PREFIX . "purpletree_vendor_subscription_products WHERE product_id='".(int)$product_id."'";
		$query = $this->db->query($sql);
			if($query->num_rows>0){
					return $query->row;
			}else {
				return NULL;
			}
	}

			

	public function getTemplateprod() {
		$query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "purpletree_vendor_template");
			if($query->num_rows>0){
		return $query->rows;
			}
	}
}
?>