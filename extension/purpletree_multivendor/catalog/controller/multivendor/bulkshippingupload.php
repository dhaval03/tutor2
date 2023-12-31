<?php
namespace Opencart\Catalog\Controller\Extension\PurpletreeMultivendor\Multivendor;

require DIR_EXTENSION.'purpletree_multivendor/system/library/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Opencart\System\Library\Log;

class Bulkshippingupload extends \Opencart\System\Engine\Controller{
		private $error = array();
		private $ptsValidateSeller = false;
		public function index(){
			
			
			$this->load->language('extension/purpletree_multivendor/multivendor/bulkshippingupload');
			$this->load->model('extension/purpletree_multivendor/multivendor/bulkshippingupload');
			$this->document->setTitle($this->language->get('heading_title'));
			$this->load->model('extension/purpletree_multivendor/multivendor/dashboard');
			$this->model_extension_purpletree_multivendor_multivendor_dashboard->checkSellerApproval();
			if (!$this->customer->isLogged()) {
				$this->session->data['redirect'] = $this->url->link('extension/purpletree_multivendor/multivendor/bulkshippingupload','language=' . $this->config->get('config_language'), true);
				
				$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
			}
			$store_detail = $this->model_extension_purpletree_multivendor_multivendor_dashboard->isSeller($this->customer->getId());
			if(!isset($store_detail['store_status'])){
				$this->response->redirect($this->url->link('account/account', 'language=' . $this->config->get('config_language'), true));
				} else {
				$stores=array();
						if(isset($store_detail['multi_store_id'])){
							$stores=explode(',',$store_detail['multi_store_id']);
						}
						
					if(isset($store_detail['store_status']) && !in_array($this->config->get('config_store_id'),$stores)){	
					$this->response->redirect($this->url->link('account/account','language=' . $this->config->get('config_language'), true));
				}
			}
			$this->getData();
		}
		public function upload(){
			if (!$this->customer->isLogged()) {
				$this->session->data['redirect'] = $this->url->link('extension/purpletree_multivendor/multivendor/bulkshippingupload','language=' . $this->config->get('config_language'), true);
				
				$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
			}
			$this->load->model('extension/purpletree_multivendor/multivendor/dashboard');
			$this->model_extension_purpletree_multivendor_multivendor_dashboard->checkSellerApproval();
			$store_detail = $this->model_extension_purpletree_multivendor_multivendor_dashboard->isSeller($this->customer->getId());
			if(!isset($store_detail['store_status'])){
				$this->response->redirect($this->url->link('account/account', 'language=' . $this->config->get('config_language'), true));
				} else {
				$stores=array();
						if(isset($store_detail['multi_store_id'])){
							$stores=explode(',',$store_detail['multi_store_id']);
						}
				if(isset($store_detail['store_status']) && !in_array($this->config->get('config_store_id'),$stores)){		
					$this->response->redirect($this->url->link('account/account','language=' . $this->config->get('config_language'), true));
				}
			}
			$this->ptsValidateSeller = $this->load->controller('extension/purpletree_multivendor/multivendor/config');
			if(!$this->ptsValidateSeller) {
				$this->load->language('extension/purpletree_multivendor/multivendor/ptsmultivendor');
				$this->session->data['error_warning'] = $this->language->get('error_license');
				$this->response->redirect($this->url->link('extension/purpletree_multivendor/multivendor/bulkshippingupload','language=' . $this->config->get('config_language'), true));
			}
					
			$data= array();
			$logger = new Log('error.log');
			$this->load->model('extension/purpletree_multivendor/multivendor/bulkshippingupload');
			$this->load->language('extension/purpletree_multivendor/multivendor/bulkproductupload');
			if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
				$postSellerId=$this->customer->getId();
				if($_FILES["import"]['tmp_name'] != '') {	
					$mimes = array('application/vnd.ms-excel','text/xls','text/xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					if(in_array($_FILES["import"]["type"],$mimes)){
						$excelFileName=$_FILES["import"]['tmp_name'];
						$excelFileType = IOFactory::identify($excelFileName);
						$objReader = IOFactory::createReader($excelFileType);
						$objReader->setReadDataOnly(true);
						$objExcel = $objReader->load($excelFileName);
						$excelSheetNames = $objExcel->getSheetNames($excelFileName);
						$return = array();
						$excelSheetData = array();
						if(!empty($excelSheetNames)){
							foreach($excelSheetNames as $key => $sheetName){
								$objExcel->setActiveSheetIndexByName($sheetName);
								$excelSheetData[$sheetName] = $objExcel->getActiveSheet()->toArray(null, true,true,true);
							}
						}
						if(array_key_exists('Shipping',$excelSheetData))
						{
							$tbl=array();
							$shipping=array();
							
							$tbl=array(
							
							'A' => 'shipping_country',
							'B' => 'zipcode_from',
							'C' => 'zipcode_to',
							'D' => 'shipping_price',
							'E' => 'weight_from',
							'F' => 'weight_to'	
							);
							$shipping= $this->CompareTable($tbl,$excelSheetData['Shipping'][1],$logger,'Shipping');
							
							if(!empty($excelSheetData['Shipping']) && $shipping){
								$fetdata=array();
								$dd=array();
								foreach($excelSheetData['Shipping'] as $kk => $vv){
									$dd[]=	array_combine($tbl,$vv);
								}
								if(!empty($dd)){
									foreach($dd as $ke => $va){
										if($ke!=0){
											$fetdata[]=	$va;
										}							
									}
								}
								if(!empty($fetdata)){
								$this->model_extension_purpletree_multivendor_multivendor_bulkshippingupload->deleteshippingids($postSellerId);
									foreach($fetdata as $shippingData){
										$fetchShippingData=array();					
										$countryId = $this->model_extension_purpletree_multivendor_multivendor_bulkshippingupload->getCountryId($shippingData['shipping_country']);
										
										$fetchShippingData['shipping'][]=array(
										'seller_id'			=>  $postSellerId,
										'shipping_country_Id'	=>	$countryId,
										'zipcode_from'			=>	$shippingData['zipcode_from'],
										'zipcode_to'			=>	$shippingData['zipcode_to'],
										'shipping_price'		=>	$shippingData['shipping_price'],
										'weight_from'			=>	$shippingData['weight_from'],
										'weight_to'			=>	$shippingData['weight_to']
										);
										
										$validate=$this->validate($fetchShippingData['shipping'][0]['shipping_country_Id'],$fetchShippingData['shipping'][0]['zipcode_from'],$fetchShippingData['shipping'][0]['zipcode_to'],$fetchShippingData['shipping'][0]['shipping_price'],$fetchShippingData['shipping'][0]['weight_from'],$fetchShippingData['shipping'][0]['weight_to']);
										if($validate) {
												$this->model_extension_purpletree_multivendor_multivendor_bulkshippingupload->addShipping($fetchShippingData);
												$logger->write(" Shipping Tab Data Insert Successfully ");
											} else {
											$logger->write(" Shipping Tab Data Invalid !");
										} 
										
									}	
								}else {$logger->write(" Shipping Tab Data Invalid !");}
								
							}else {$logger->write(" Shipping Tab Data Invalid !");}	
						}else {$logger->write(" Shipping Tab Data Invalid !");}					
						$this->session->data['success'] = $this->language->get('text_bulkuploadsuccess');
						$url='';
						$this->response->redirect($this->url->link('extension/purpletree_multivendor/multivendor/bulkshippingupload', 'language=' . $this->config->get('config_language') . $url, true));
						} else {
						$this->session->data['error_warning'] = $this->language->get('text_invalidfile');
					}
					} else {
					$this->session->data['error_warning'] = $this->language->get('text_nofile');
				}	
			}
			$this->index();	
		}
		protected function validate($shipping_country,$zipcode_from,$zipcode_to,$shipping_price,$weight_from,$weight_to)
		{
			if ($shipping_country== '') {
				return false;
			}
			if ((strlen($zipcode_from) < 1) ) {
				return false;
			}
			
			if ((strlen($zipcode_to) < 1) ) {
				return false;
			}
			
			if( ! filter_var($shipping_price, FILTER_VALIDATE_FLOAT) && $shipping_price != '0') {
				return false;
			}
			if(strlen($shipping_price) < 1){
				return false;
			}
			
			if( ! filter_var($weight_from, FILTER_VALIDATE_FLOAT) && $weight_from != '0' ){
				return false;
			}
			if(strlen($weight_from) < 1){
				return false;
			}
			
			if( ! filter_var($weight_to, FILTER_VALIDATE_FLOAT) && $weight_to != '0' ){
				
				return false;
			}		
			if($weight_to < $weight_from) {
				return false;
			}
			if(strlen($weight_to ) < 1){
				return false;
			}
			return true;
		}
		public function CompareTable($tableFirst,$tableSecond,$logger,$tab){				
			$error=array();
			if(count($tableFirst)==count($tableSecond)){
				foreach($tableFirst as $key => $value ){
					if(in_array($value,$tableSecond)){
						continue;
					} else 
					{
						
						$logger->write($tab." Tab - ".$value." Column is Not match");
						return false;
					}
				}
				return true;
			}
			return false;
		}
		
		
		public function export(){
			if (!$this->customer->isLogged()) {
				$this->session->data['redirect'] = $this->url->link('extension/purpletree_multivendor/multivendor/bulkshippingupload','language=' . $this->config->get('config_language'), true);
				
				$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
			}
			$this->load->model('extension/purpletree_multivendor/multivendor/dashboard');
			$this->model_extension_purpletree_multivendor_multivendor_dashboard->checkSellerApproval();
			$store_detail = $this->model_extension_purpletree_multivendor_multivendor_dashboard->isSeller($this->customer->getId());
			if(!isset($store_detail['store_status'])){
				$this->response->redirect($this->url->link('account/account', 'language=' . $this->config->get('config_language'), true));
			}
			$this->ptsValidateSeller = $this->load->controller('extension/purpletree_multivendor/multivendor/config');
			if(!$this->ptsValidateSeller) {
				$this->load->language('extension/purpletree_multivendor/multivendor/ptsmultivendor');
				$this->session->data['error_warning'] = $this->language->get('error_license');
				$this->response->redirect($this->url->link('extension/purpletree_multivendor/multivendor/bulkshippingupload','language=' . $this->config->get('config_language'), true));
			}
			try {
				$this->load->language('extension/purpletree_multivendor/multivendor/bulkshippingupload');
				$this->load->model('extension/purpletree_multivendor/multivendor/bulkshippingupload');
				
				if($this->request->server['REQUEST_METHOD'] == 'POST'){
					$objPHPexl = new Spreadsheet();
					
					$fileName = 'PurpletreeshippingExport';
					$postSellerId=$this->customer->getId();
					//$postLanguageId=$this->request->post['language'];

					$objPHPexl->getProperties()->setCreator("PurpleTree")->setLastModifiedBy("purple")->setTitle("PurpleTree Software")->setSubject("Purpletree")->setDescription("PurpleTree")->setKeywords("Purple")->setCategory("Purple software");
					$this->exportExcelData($objPHPexl,'Shipping','purpletree_vendor_shipping',$postSellerId,0);		
					$objWriter = new Xlsx($objPHPexl);
					$objWriter->save(DIR_EXTENSION.'purpletree_multivendor/system/library/export/'.$fileName.'.xlsx'); 
					$attachment_location = DIR_EXTENSION.'purpletree_multivendor/system/library/export/'.$fileName.'.xlsx';
					
					if (file_exists($attachment_location)) {
						header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
						header("Cache-Control: public");
						header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
						header("Content-Transfer-Encoding: Binary");
						header("Content-Length:".filesize($attachment_location));
						header("Content-Disposition: attachment; filename=PurpletreeshippingExport.xlsx");
						readfile($attachment_location);
						$this->session->data['success'] = $this->language->get('text_success');
						die();
						} else {
						die("File not found.");
					}
				}
			}
			catch (Exception $e) {
				$logger->write(" Shipping Tab Data Invalid !");
			} 
			
			
		}
		public function exportExcelData($objPHPexl,$tabName,$tblName,$seller_id,$sheetIndex){
			$this->load->model('extension/purpletree_multivendor/multivendor/dashboard');
			$this->model_extension_purpletree_multivendor_multivendor_dashboard->checkSellerApproval();
			$objPHPexl->setActiveSheetIndex($sheetIndex);
			$tableName=array();
			$exportData=array();
			
			$tbl=$this->model_extension_purpletree_multivendor_multivendor_bulkshippingupload->getTableName($tblName);
			array_shift($tbl);
			//array_shift($tbl);
			//array_unshift($tbl,'id');
			array_pop($tbl);
			$Cell=array_slice(range('A','Z'),0,count($tbl),true);	
			$tableName=array();
			$tableName=array_combine($Cell,$tbl);
			//$tableName['B']='seller_store_name';
			$expData=$this->model_extension_purpletree_multivendor_multivendor_bulkshippingupload->getExportData($tblName,$seller_id);
			$exportData=array();
			if(!empty($expData)){
				foreach($expData as $key => $value){
					//$StoreName=$this->model_extension_purpletree_multivendor_multivendor_bulkshippingupload->getStoreName($value['seller_id']);
					$country=$this->model_extension_purpletree_multivendor_multivendor_bulkshippingupload->getCountry($value['shipping_country']);
					$exportData[]=array(
					'shipping_country'	=>	$country,
					'zipcode_from'		=>	$value['zipcode_from'],
					'zipcode_to'		=>	$value['zipcode_to'],
					'shipping_price'	=>	$value['shipping_price'],
					'weight_from'		=>	$value['weight_from'],
					'weight_to'			=>	$value['weight_to']
					);
				}	
			}
			if(!empty($tableName)){	
				foreach($tableName as $key => $tbName)
				{
					$objPHPexl->getActiveSheet()->setCellValue($key.'1',$tbName);	
					$objPHPexl->getActiveSheet($sheetIndex)->getColumnDimension($key)->setWidth(strlen($tbName)+4);
					$objPHPexl->getActiveSheet($sheetIndex)->getRowDimension(1)->setRowHeight(25);
					$objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
					$objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
					$objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getFill()->setFillType(Fill::FILL_SOLID);
					$objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getFill()->getStartColor()->setARGB('800080');
					$objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3'); 
					$objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getFont()->setBold(true)->setName('Verdana')->setSize(10)->getColor()->setRGB('FFFFFF');
				}
			}
			$objPHPexl->getActiveSheet()->freezePaneByColumnAndRow( 1, 2 );
			
			$i=0;
			if(!empty($exportData)){
				foreach($exportData as $key => $exportData){
					$ii = $i+2;	
					if($tableName!=NULL){
						foreach($tableName as $k=>$v){
							
							$objPHPexl->getActiveSheet()->setCellValue($k.''.$ii, $exportData[$v]);
						}
					}
					$i++;
				}
			}
			$objPHPexl->getActiveSheet()->setTitle($tabName);
		}
		public function getData(){	
			$url = '';
			///Help code///
			//$data['helplink'] = "https://www.purpletreesoftware.com/knowledgebase/tag/opencart-multivendor-shipping";
			$data['helplink'] = "https://cutt.ly/BCoBQ5a";
			if (defined ('DISABLED_PTS_HELP')){if(DISABLED_PTS_HELP == 0){$data['helpcheck'] = 1;}else{$data['helpcheck'] = 0;}}else{$data['helpcheck'] = 1;}
			$data['helpimage'] = HTTP_SERVER . 'extension/purpletree_multivendor/catalog/view/assets/image/help.png';
			/// End Help code///
			$data['breadcrumbs'] = array();
			
			$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard','language=' . $this->config->get('config_language'), true)
			);
			
			$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/purpletree_multivendor/multivendor/bulkshippingupload', '&language=' . $this->config->get('config_language') . $url, true)
			);
			
			$data['heading_title'] = $this->language->get('heading_title');
			$data['text_list'] = $this->language->get('text_list');		
			$data['text_no_results'] = $this->language->get('text_no_results');
			$data['text_confirm'] = $this->language->get('text_confirm');        
			$data['button_edit'] = $this->language->get('button_edit');
			$data['button_upload'] = $this->language->get('button_upload');
			$data['text_upload_info'] = $this->language->get('text_upload_info');
			
			//$data['user_token'] = $this->session->data['user_token'];
			
			$data['text_bulk_shipping_upload'] = $this->language->get('text_bulk_shipping_upload');
			
			$this->load->model('extension/purpletree_multivendor/multivendor/dashboard');
			$this->model_extension_purpletree_multivendor_multivendor_dashboard->checkSellerApproval();
			
			if (isset($this->error['warning'])) {
				$data['error_warning'] = $this->error['warning'];
				} else {
				$data['error_warning'] = '';
			}
			
			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];
				
				unset($this->session->data['success']);
				} else {
				$data['success'] = '';
			}
			
			if (isset($this->session->data['error_warning'])) {
				$data['error_warning'] = $this->session->data['error_warning'];
				
				unset($this->session->data['error_warning']);
				} else {
				$data['error_warning'] = '';
			}
			$data['max_time'] = ini_get("max_execution_time")/100;
			
			$data['memory_limit'] = ini_get("memory_limit");
			
			$data['export'] = $this->url->link('extension/purpletree_multivendor/multivendor/bulkshippingupload|export','language=' . $this->config->get('config_language'), true);	
			$data['upload'] = $this->url->link('extension/purpletree_multivendor/multivendor/bulkshippingupload|upload','language=' . $this->config->get('config_language'), true);	
			$data['column_left'] = $this->load->controller('extension/purpletree_multivendor/multivendor/common/column_left');
			$data['footer'] = $this->load->controller('extension/purpletree_multivendor/multivendor/common/footer');
			$data['header'] = $this->load->controller('extension/purpletree_multivendor/multivendor/common/header');
			
			
			$data['seller_id']=$this->model_extension_purpletree_multivendor_multivendor_bulkshippingupload->getAllSellerId();
			$this->response->setOutput($this->load->view('extension/purpletree_multivendor/multivendor/bulkshipping_upload', $data));
		}
	}
	if (! function_exists('array_column')) {
		function array_column(array $input, $columnKey, $indexKey = null) {
			$array = array();
			foreach ($input as $value) {
				if ( !array_key_exists($columnKey, $value)) {
					trigger_error("Key \"$columnKey\" does not exist in array");
					return false;
				}
				if (is_null($indexKey)) {
					$array[] = $value[$columnKey];
				}
				else {
					if ( !array_key_exists($indexKey, $value)) {
						trigger_error("Key \"$indexKey\" does not exist in array");
						return false;
					}
					if ( ! is_scalar($value[$indexKey])) {
						trigger_error("Key \"$indexKey\" does not contain scalar value");
						return false;
					}
					$array[$value[$indexKey]] = $value[$columnKey];
				}
			}
			return $array;
		}
	}
?>