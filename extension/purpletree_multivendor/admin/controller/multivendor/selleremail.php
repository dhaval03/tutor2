<?php
namespace Opencart\Admin\Controller\Extension\PurpletreeMultivendor\Multivendor;
class Selleremail extends \Opencart\System\Engine\Controller {
		private $error = array();		
		public function index() {	
		$validateSeller = $this->load->controller('extension/purpletree_multivendor/multivendor/config');
			if (!$validateSeller) {
				$this->load->language('extension/purpletree_multivendor/multivendor/ptsmultivendor');
				$this->session->data['error_warning'] = $this->language->get('error_license');				
			}
			
			$this->load->language('extension/purpletree_multivendor/multivendor/selleremail');
			
			$this->document->setTitle($this->language->get('heading_title'));
			
			$this->load->model('extension/purpletree_multivendor/multivendor/selleremail');
			
			$this->getList();
		}
		
		public function resetTemplate() {
			
		    //echo "<pre>"; print_r($this->request->get); die;
			$validateSeller = $this->load->controller('extension/purpletree_multivendor/multivendor/config');
			if (!$validateSeller) {
				$this->load->language('extension/purpletree_multivendor/multivendor/ptsmultivendor');
				$this->session->data['error_warning'] = $this->language->get('error_license');
				$this->response->redirect($this->url->link('extension/purpletree_multivendor/multivendor/selleremail', 'user_token=' . $this->session->data['user_token'] . $url, true));
			}
			$this->load->language('extension/purpletree_multivendor/multivendor/selleremail');
			
			$this->document->setTitle($this->language->get('heading_title'));
			
			$this->load->model('extension/purpletree_multivendor/multivendor/selleremail');
			//echo "<pre>"; print_r($this->request->post); die;
			if (($this->request->server['REQUEST_METHOD'] == 'POST')  && $this->validateForm() ) {
				
				$this->model_extension_purpletree_multivendor_multivendor_selleremail->resetSelleremail($this->request->get['id']);
				
				$this->session->data['success'] = $this->language->get('text_success');
				
				$url = '';
				
				if (isset($this->request->get['sort'])) {
					$url .= '&sort=' . $this->request->get['sort'];
				}
				
				if (isset($this->request->get['order'])) {
					$url .= '&order=' . $this->request->get['order'];
				}
				
				if (isset($this->request->get['page'])) {
					$url .= '&page=' . $this->request->get['page'];
				}
				
				$this->response->redirect($this->url->link('extension/purpletree_multivendor/multivendor/selleremail', 'user_token=' . $this->session->data['user_token'] . $url, true));
			}
			
			$this->getForm();
			}
		public function edit() {
			$validateSeller = $this->load->controller('extension/purpletree_multivendor/multivendor/config');
			if (!$validateSeller) {
				$this->load->language('extension/purpletree_multivendor/multivendor/ptsmultivendor');
				$this->session->data['error_warning'] = $this->language->get('error_license');
				$this->response->redirect($this->url->link('extension/purpletree_multivendor/multivendor/selleremail', 'user_token=' . $this->session->data['user_token'] . $url, true));
			}
			$this->load->language('extension/purpletree_multivendor/multivendor/selleremail');
			
			$this->document->setTitle($this->language->get('heading_title'));
			
			$this->load->model('extension/purpletree_multivendor/multivendor/selleremail');
			//echo "<pre>"; print_r($this->request->post); die;
			if (($this->request->server['REQUEST_METHOD'] == 'POST')  && $this->validateForm() ) {
				
				$this->model_extension_purpletree_multivendor_multivendor_selleremail->editSelleremail($this->request->get['id'], $this->request->post);
				
				$this->session->data['success'] = $this->language->get('text_success');
				
				$url = '';
				
				if (isset($this->request->get['sort'])) {
					$url .= '&sort=' . $this->request->get['sort'];
				}
				
				if (isset($this->request->get['order'])) {
					$url .= '&order=' . $this->request->get['order'];
				}
				
				if (isset($this->request->get['page'])) {
					$url .= '&page=' . $this->request->get['page'];
				}
				
				$this->response->redirect($this->url->link('extension/purpletree_multivendor/multivendor/selleremail', 'user_token=' . $this->session->data['user_token'] . $url, true));
			}
			
			$this->getForm();
		}
		
		
		protected function getList() {
			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
				} else {
				$page = 1;
			}
			$url = '';
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			$data['selleremails'] = array();
			///Help code///	
			//$data['helplink'] = "https://www.purpletreesoftware.com/knowledgebase/tag/opencart-multivendor-email";
			$data['helplink'] = "https://cutt.ly/SCo0rgd";
			if (defined ('DISABLED_PTS_HELP')){if(DISABLED_PTS_HELP == 0){$data['helpcheck'] = 1;}else{$data['helpcheck'] = 0;}}else{$data['helpcheck'] = 1;}
			$data['helpimage'] = HTTP_CATALOG . '/extension/purpletree_multivendor/admin/view/image/help.png';
			/// End Help code///
			$data['breadcrumbs'] = array();
			
			$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			);
			
			$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/purpletree_multivendor/multivendor/selleremail', 'user_token=' . $this->session->data['user_token'] . $url, true)
			);
			$filter_data = array(			
			'start'                    => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit'                    => $this->config->get('config_pagination_admin')
			);
			$results = $this->model_extension_purpletree_multivendor_multivendor_selleremail->getSelleremail($filter_data);
			$total_results = $this->model_extension_purpletree_multivendor_multivendor_selleremail->getTotalSelleremail($filter_data);
			foreach ($results as $result) {
				
				$data['selleremails'][] = array(
				 'title' => $result['title'],
				 'subject'        => $result['new_subject'],
				 'type'        => $result['type'],				
				  'edit'        => $this->url->link('extension/purpletree_multivendor/multivendor/selleremail|edit', 'user_token=' . $this->session->data['user_token'] . '&id=' . $result['id'] . $url, true)			
				 );
			 } 
			
			if (isset($this->error['warning'])) {
				 $data['error_warning'] = $this->error['warning'];
				}elseif (isset($this->session->data['error_warning'])) {
				 $data['error_warning'] = $this->session->data['error_warning'];
				 unset($this->session->data['error_warning']);
				 } else {
				 $data['error_warning'] = '';
			  }
			
			
			if (isset($this->session->data['success'])) {
				 $data['success'] = $this->session->data['success'];
				
				 unset($this->session->data['success']);
				 } else {  
				$data['success'] = '';
	            }
			
			$url = '';
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			$url = '';

			$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $total_results,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link('extension/purpletree_multivendor/multivendor/selleremail', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true)
		]);
			$data['results'] = sprintf($this->language->get('text_pagination'), ($total_results) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($total_results - $this->config->get('config_pagination_admin'))) ? $total_results : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $total_results, ceil($total_results / $this->config->get('config_pagination_admin')));
			
			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');
			
			$this->response->setOutput($this->load->view('extension/purpletree_multivendor/multivendor/selleremail_list', $data));
		}
		
		protected function getForm() {
		    $this->document->addScript('view/javascript/ckeditor/ckeditor.js');
		    $this->document->addScript('view/javascript/ckeditor/adapters/jquery.js');
		$data['text_form'] = $this->language->get('text_edit');
			
			if (isset($this->error['warning'])) {
				$data['error_warning'] = $this->error['warning'];
				} else {
				$data['error_warning'] = '';
			}
			
			
			if (isset($this->error['name'])) {
				$data['error_name'] = $this->error['name'];
				} else {
				$data['error_name'] = array();
			}
			
			
			
			if (isset($this->error['no_of_product'])) {
				$data['error_no_of_product'] = $this->error['no_of_product'];
				} else {
				$data['error_no_of_product'] = '';
			}
			

			
			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
			
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			///Help code///	
			//$data['helplink'] = "https://www.purpletreesoftware.com/knowledgebase/tag/opencart-multivendor-email";
			$data['helplink'] = "https://cutt.ly/SCo0rgd";
			if (defined ('DISABLED_PTS_HELP')){if(DISABLED_PTS_HELP == 0){$data['helpcheck'] = 1;}else{$data['helpcheck'] = 0;}}else{$data['helpcheck'] = 1;}
			$data['helpimage'] = HTTP_CATALOG . '/extension/purpletree_multivendor/admin/view/image/help.png';
			/// End Help code///
			$data['breadcrumbs'] = array();
			
			$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			);
			
			$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/purpletree_multivendor/multivendor/selleremail', 'user_token=' . $this->session->data['user_token'] . $url, true)
			);
			$this->load->language('extension/purpletree_multivendor/multivendor/selleremail');
			
			if (isset($this->request->get['id'])) {
				$data['action'] = $this->url->link('extension/purpletree_multivendor/multivendor/selleremail|edit', 'user_token=' . $this->session->data['user_token'] . '&id=' . $this->request->get['id'] . $url, true);
				$data['action1'] = $this->url->link('extension/purpletree_multivendor/multivendor/selleremail/resetTemplate', 'user_token=' . $this->session->data['user_token'] . '&id=' . $this->request->get['id'] . $url, true);
			}
			
			
			$data['cancel'] = $this->url->link('extension/purpletree_multivendor/multivendor/selleremail', 'user_token=' . $this->session->data['user_token'] . $url, true);
			
			
			$data['user_token'] = $this->session->data['user_token'];
			
			$this->load->model('localisation/language');
			
			$data['languages'] = $this->model_localisation_language->getLanguages();
			
			if (isset($this->request->post['email'])) {
				$data['email'] = $this->request->post['email'];
				} elseif (isset($this->request->get['id'])) {
				$data['email'] = $this->model_extension_purpletree_multivendor_multivendor_selleremail->getemail($this->request->get['id']);
				} else {
				$data['email'] = array();
			}			
			//echo "<pre>"; print_r($data['email']);	
			//die;
			
			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');
			
			$this->response->setOutput($this->load->view('extension/purpletree_multivendor/multivendor/selleremail_form', $data)); 
		}
		
		protected function validateForm() {
			/* foreach ($this->request->post['subscription'] as $language_id => $value) {
				if ((strlen($value['name']) < 1) || (strlen($value['name']) > 255)) {
					$this->error['name'][$language_id] = $this->language->get('error_name');
				}
				
			}*/
			
			return !$this->error; 
		}		
		
}
?>