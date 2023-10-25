<?php
/**
 * register.php
 *
 * Registration management
 *
 * @author     Opencart-api.com
 * @copyright  2017
 * @license    License.txt
 * @version    2.0
 * @link       https://opencart-api.com/product/shopping-cart-rest-api/
 * @documentations https://opencart-api.com/opencart-rest-api-documentations/
 */
// namespace Opencart\Catalog\Controller\Extension\RestApi\Rest;
// require_once(DIR_EXTENSION . 'engine/restcontroller.php');
namespace Opencart\Catalog\Controller\Extension\RestApi\Rest;
require_once(DIR_EXTENSION . 'restapi/system/engine/restcontroller.php');

class Register extends \RestController
{

    public function register()
    {

        $this->checkPlugin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //add customer
            $post = $this->getPost();
            $this->registerCustomer($post);
        } else {
            $this->statusCode = 405;
            $this->allowedHeaders = array("POST");
        }

        $this->sendResponse();
    }
 

    public function registerCustomer($data)
    {
			//echo'ff';exit;
        $this->language->load('checkout/checkout');
        $this->language->load('checkout/cart');
		$this->load->language('account/register');
        $this->load->model('account/customer');


        // Validate if customer is logged in.
        if ($this->customer->isLogged()) {
            $this->json['error'][] = "User is logged.";
            $this->statusCode = 400;
        } else {
            if ($this->opencartVersion >= 3100) {
                unset($this->session->data['guest']);
            }
            // Validate minimum quantity requirments.
            $products = $this->cart->getProducts();

            foreach ($products as $product) {
                $product_total = 0;

                foreach ($products as $product_2) {
                    if ($product_2['product_id'] == $product['product_id']) {
                        $product_total += $product_2['quantity'];
                    }
                }

                if ($product['minimum'] > $product_total) {
                    $this->json['error'][] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
                    break;
                }
            }
	
            if (empty($this->json['error'])) {
                if (empty($data['firstname']) && (!isset($data['firstname']) || ((trim($data['firstname'])) < 1) || ((trim($data['firstname'])) > 32))) {
                    $this->json['error'][] = $this->language->get('error_firstname');
                }

                if (empty($data['lastname']) && (!isset($data['lastname']) || ((trim($data['lastname'])) < 1) || ((trim($data['lastname'])) > 32))) {
                    $this->json['error'][] = $this->language->get('error_lastname');
                }

                if (empty($data['email']) && (!isset($data['email']) || (($data['email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $data['email']))) {
                    $this->json['error'][] = $this->language->get('error_email');
                } else {
                    if ($this->model_account_customer->getTotalCustomersByEmail($data['email'])) {
                        $this->json['error'][] = $this->language->get('error_exists');
                    }
                }

                if (empty($data['telephone']) && (!isset($data['telephone']) || (($data['telephone']) < 3) || (($data['telephone']) > 32))) {
                    $this->json['error'][] = $this->language->get('error_telephone');
                }

                if (empty($data['password']) && (!isset($data['password']) || (($data['password']) < 4) || (($data['password']) > 20))) {
                    $this->json['error'][] = $this->language->get('error_password');
                }
                if (empty($data['confirm']) && (!isset($data['confirm']) || $data['confirm'] != $data['password'])) {
                    $this->json['error'][] = $this->language->get('error_confirm');
                }

                if ($this->config->get('config_account_id')) {
                    $this->load->model('catalog/information');

                    $information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

                    if ($information_info && (!isset($data['agree']) || empty($data['agree']))) {
                        $this->json['error'][] = sprintf($this->language->get('error_agree'), $information_info['title']);
                    }
                }

                // Customer Group
                if (isset($data['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($data['customer_group_id'], $this->config->get('config_customer_group_display'))) {
                    $customer_group_id = $data['customer_group_id'];
                } else {
                    $customer_group_id = $this->config->get('config_customer_group_id');
                }

                $data['customer_groups'] = array();

                if (is_array($this->config->get('config_customer_group_display'))) {
                    $this->load->model('account/customer_group');
                    $customer_groups = $this->model_account_customer_group->getCustomerGroups();

                    foreach ($customer_groups as $customer_group) {
                        if (in_array($customer_group['customer_group_id'], $this->config->get('config_customer_group_display'))) {
                            $data['customer_groups'][] = $customer_group;
                        }
                    }
                }

                if (isset($data['customer_group_id'])) {
                    $data['customer_group_id'] = $data['customer_group_id'];
                } else {
                    $data['customer_group_id'] = $this->config->get('config_customer_group_id');
                }

                // Custom field validation
                $this->load->model('account/custom_field');

                $custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

                foreach ($custom_fields as $custom_field) {
                    if($this->opencartVersion < 2300) {
                        if ($custom_field['required'] && empty($data['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
                            $this->json['error'][] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                        }
                    } else {
                        if ($custom_field['location'] == 'account') {
                            if ($custom_field['required'] && empty($data['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
                                $this->json['error'][] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                            } elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) &&
                                !filter_var($data['custom_field'][$custom_field['location']][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
                                $this->json['error'][] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                            }
                        }
                    }
                }
            }


            if (empty($this->json['error'])) {


                if (!isset($data['company'])) {
                    $data["company"] = "";
                }

                $customer_id = $this->model_account_customer->addCustomer($data);

                $this->session->data['account'] = 'register';

                $this->load->model('account/customer_group');

                $customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

                if ($customer_group_info && !$customer_group_info['approval']) {
                    $this->customer->login($data['email'], $data['password']);
                    $data['customer_id'] = $customer_id;
                    $data['address_id'] = $this->customer->getAddressId();

                    unset($data['password']);
                    unset($data['confirm']);
                    unset($data['agree']);

                    $this->json['data'] = $data;

                    // Default Payment Address
                    $this->load->model('account/address');

                    $this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->isLogged(),$this->customer->getAddressId());

                    if (!empty($data['shipping_address'])) {
                        $this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
                    }
                }

                unset($this->session->data['guest']);
                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);

            }
        }

    }
}