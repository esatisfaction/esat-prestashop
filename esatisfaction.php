<?php
/**
 * NOTICE OF LICENSE.
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    e-satisfaction SA
 * @copyright 2018 e-satisfaction SA
 * @license   https://opensource.org/licenses
 * @version 0.3.0
 */

class Esatisfaction extends Module
{

    /**
     * @var string $defaultImage Η βασική εικόνα που
     * χρησιμοποιείται όταν δεν βρίσκεται κάποια εικόνα.
     */
    public $defaultImage = '../modules/esatisfaction/views/img/esat_32x32.png';
    
    /**
    *
    * @var int $id_shop Το id του καταστήματος για τις
    * κλήσεις του API.
    */
    public $id_shop;
    
    public function __construct()
    {
        $this->name = 'esatisfaction';
        $this->tab = 'other';
        $this->version = '0.3.0';
        $this->author = 'e-satisfaction SA';
        $this->tab = 'analytics_stats';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->module_key = '5cd651fbc7befadc249391eb1ef2bf7d';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('e-satisfaction tracking module');
        $this->description = $this->l('Adds the code necessary to gather customer satisfactiond data');
        $this->id_shop = Configuration::get('ESATISFACTION_SITE_ID');
        if (!defined('_PS_VERSION_')) {
            exit;
        }
    }
    
    /**
     * Εγκαθιστά το module στις θέσεις, displayOrderConfirmation,
     * actionOrderStatusPostUpdate,
     * displayBackOfficeHeader, displayFooter
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     * @return bool
     */
    public function install()
    {
        Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'esat_order_stat` (
        `id_order` INT( 11 ) NOT NULL,
        `item_id` VARCHAR(100) NOT NULL
        ) ENGINE = MYISAM');
        return parent::install() &&
                $this->registerHook('displayOrderConfirmation') &&
                $this->registerHook('actionOrderStatusPostUpdate') &&
                $this->registerHook('displayFooter') &&
                $this->registerHook('displayBackOfficeHeader') &&
                $this->registerHook('displayHeader') ;
    }
    
    /**
    * Απεγκατάσταση του module
    *
    * @author    e-satisfaction SA
    * @copyright (c) 2018, e-satisfaction SA
    * @return bool
    */
    public function uninstall()
    {
        if (parent::uninstall()) {
            return true;
        }

        return false;
    }
    
    /**
     * Εμφάνιση του διαχειριστικού του module.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     * @return string
     */
    public function getContent()
    {
        $output = null;
        if (Tools::isSubmit('submit' . $this->name)) {
            $site_id = Tools::getValue('ESATISFACTION_SITE_ID');
            $auth = Tools::getValue('ESATISFACTION_AUTH');
            $output = null;
            if (!$site_id || empty($site_id)) {
                $output .= $this->displayError($this->l('Site Id cannot be empty'));
            }

            if (!$auth || empty($auth)) {
                $output .= $this->displayError($this->l('Site Auth cannot be empty'));
            }
           
            if (empty($output)) {
                Configuration::updateValue('ESATISFACTION_SITE_ID', $site_id);
                Configuration::updateValue('ESATISFACTION_AUTH', $auth);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
            
            Configuration::updateValue(
            
                'ESATISFACTION_MANUAL_SEND',
                Tools::getValue('manual_send')
            );
            
            Configuration::updateValue(
            
                'ESATISFACTION_CHKOUTID',
                Tools::getValue('ESATISFACTION_CHKOUTID')
            );
            Configuration::updateValue(
                'ESATISFACTION_HOMEDLVID',
                Tools::getValue('ESATISFACTION_HOMEDLVID')
            );
            Configuration::updateValue(
                'ESATISFACTION_STRPICKID',
                Tools::getValue('ESATISFACTION_STRPICKID')
            );
            
            Configuration::updateValue(
            
                'ESATISFACTION_HOMEDLV_PIPE_ID',
                Tools::getValue('ESATISFACTION_HOMEDLV_PIPE_ID')
            );
            Configuration::updateValue(
                'ESATISFACTION_STRPICK_PIPE_ID',
                Tools::getValue('ESATISFACTION_STRPICK_PIPE_ID')
            );
            
            Configuration::updateValue(
            
                'ESATISFACTION_HOMEDLVID_DAYS',
                Tools::getValue('ESATISFACTION_HOMEDLVID_DAYS')
            );
            
            Configuration::updateValue(
            
                'ESATISFACTION_DELIVERED_DLV_OS_IDS',
                json_encode(Tools::getValue('delivered_dlv_os'))
            );
            Configuration::updateValue(
            
                'ESATISFACTION_CANCELED_DLV_OS_IDS',
                json_encode(Tools::getValue('canceled_dlv_os'))
            );
            
            Configuration::updateValue(
            
                'ESATISFACTION_STOREPICKUP_IDS',
                json_encode(Tools::getValue('store_pickup_carriers'))
            );
            Configuration::updateValue(
                'ESATISFACTION_COURIER_IDS',
                json_encode(Tools::getValue('courier_carriers'))
            );
            
            Configuration::updateValue(
            
                'ESATISFACTION_DELIVERED_STRPICK_OS_IDS',
                json_encode(Tools::getValue('delivered_strpick_os'))
            );
            Configuration::updateValue(
            
                'ESATISFACTION_CANCELED_STRPICK_OS_IDS',
                json_encode(Tools::getValue('canceled_strpick_os'))
            );
        }

        return $output . $this->displayForm();
    }
    
    /**
     * Εμφάνιση του διαχειριστικού του module
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     * @return string
     */
     
    public function displayForm()
    {
        $fields_form = null;
        // Get default Language
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        
        $carriers_raw = Carrier::getCarriers($default_lang, true, false);
        $carriers = array();
        foreach ($carriers_raw as $carrier) {
            $carriers[] = array(
                'val' => $carrier['id_reference'],
                'carrier_reference' => $carrier['id_reference'],
                'name' => $carrier['name']);
        }
        
        $order_statuses_raw = OrderState::getOrderStates($default_lang);
        $order_statuses = array();
        foreach ($order_statuses_raw as $order_status) {
            $order_statuses[] = array(
                'val' => $order_status['id_order_state'],
                'order_state_id' => $order_status['id_order_state'],
                'name' => $order_status['name']
            );
        }
        
        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Application'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Application Id'),
                    'name' => 'ESATISFACTION_SITE_ID',
                    'size' => 20,
                    'required' => true,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default',
            ),
        );
        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Checkout Questionnaire'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Questionnaire Id'),
                    'name' => 'ESATISFACTION_CHKOUTID',
                    'size' => 45,
                    'required' => true,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default',
            ),
        );
        
        $fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->l('Manually sending After Delivery / Store Pickup'),
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Send Manually'),
                    'name' => 'manual_send',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'manual_send_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'manual_send_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    )
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default',
            ),
        );
    
        $fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->l('After Delivery Questionnaire'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Questionnaire Id'),
                    'name' => 'ESATISFACTION_HOMEDLVID',
                    'size' => 45,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Pipeline Id'),
                    'name' => 'ESATISFACTION_HOMEDLV_PIPE_ID',
                    'size' => 45,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Days after to send questionnaire'),
                    'name' => 'ESATISFACTION_HOMEDLVID_DAYS',
                    'size' => 4,
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Order status(es) to send questionnaire'),
                    'name' => 'delivered_dlv_os[]',
                    'values'  => array(
                       'query' => $order_statuses,
                       'id' => 'order_state_id',
                       'name'  => 'name',
                    )
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Condition to determine when it’s after delivery'),
                    'name' => 'courier_carriers[]',
                    'desc' => 'Select the carriers that are used for after delivery',
                    'values'  => array(
                       'query' => $carriers,
                       'id' => 'carrier_reference',
                       'name'  => 'name'
                )),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Order status(es) to cancel questionnaire'),
                    'name' => 'canceled_dlv_os[]',
                    'values'  => array(
                       'query' => $order_statuses,
                       'id' => 'order_state_id',
                       'name'  => 'name',
                    )
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default',
            ),
        );
        $fields_form[4]['form'] = array(
            'legend' => array(
                'title' => $this->l('Store Pick Up Questionnaire'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Questionnaire Id'),
                    'name' => 'ESATISFACTION_STRPICKID',
                    'size' => 45,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Pipeline Id'),
                    'name' => 'ESATISFACTION_STRPICK_PIPE_ID',
                    'size' => 45,
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Order status(es) to send questionnaire'),
                    'name' => 'delivered_strpick_os[]',
                    'values'  => array(
                       'query' => $order_statuses,
                       'id' => 'order_state_id',
                       'name'  => 'name',
                    )
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Condition to determine when it’s store pickup'),
                    'name' => 'store_pickup_carriers[]',
                    'desc' => 'Select the carriers that are used for store pickup',
                    'values'  => array(
                       'query' => $carriers,
                       'id' => 'carrier_reference',
                       'name'  => 'name'
                )),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Order status(es) to cancel questionnaire'),
                    'name' => 'canceled_strpick_os[]',
                    'values'  => array(
                       'query' => $order_statuses,
                       'id' => 'order_state_id',
                       'name'  => 'name',
                    )
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default',
            ),
        );
            
        $fields_form[5]['form'] = array(
            'legend' => array(
                'title' => $this->l('API Authentication Token'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Token'),
                    'name' => 'ESATISFACTION_AUTH',
                    'size' => 45,
                    'required' => true,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default',
                ),
            );
        

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true; // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list'),
            ),
        );
        // Load current value
        $helper->fields_value['ESATISFACTION_SITE_ID'] = Configuration::get('ESATISFACTION_SITE_ID');
        $helper->fields_value['ESATISFACTION_AUTH'] = Configuration::get('ESATISFACTION_AUTH');
        $helper->fields_value['ESATISFACTION_CHKOUTID'] = Configuration::get('ESATISFACTION_CHKOUTID');
        $helper->fields_value['ESATISFACTION_HOMEDLVID'] = Configuration::get('ESATISFACTION_HOMEDLVID');
        $helper->fields_value['ESATISFACTION_HOMEDLVID_DAYS'] = Configuration::get('ESATISFACTION_HOMEDLVID_DAYS');
        
        $helper->fields_value['ESATISFACTION_STRPICKID'] = Configuration::get('ESATISFACTION_STRPICKID');
        
        $helper->fields_value['ESATISFACTION_HOMEDLV_PIPE_ID'] = Configuration::get('ESATISFACTION_HOMEDLV_PIPE_ID');
        $helper->fields_value['ESATISFACTION_STRPICK_PIPE_ID'] = Configuration::get('ESATISFACTION_STRPICK_PIPE_ID');
        
        $helper->fields_value['manual_send'] = Configuration::get('ESATISFACTION_MANUAL_SEND');
        
        $delivered_dlv_os = json_decode(Configuration::get('ESATISFACTION_DELIVERED_DLV_OS_IDS'));
        foreach ($delivered_dlv_os as $value) {
            $helper->fields_value['delivered_dlv_os[]_'.$value] = 1;
        }
        $courier_carriers = json_decode(Configuration::get('ESATISFACTION_COURIER_IDS'));
        foreach ($courier_carriers as $value) {
            $helper->fields_value['courier_carriers[]_'.$value] = 1;
        }
        $canceled_dlv_os = json_decode(Configuration::get('ESATISFACTION_CANCELED_DLV_OS_IDS'));
        foreach ($canceled_dlv_os as $value) {
            $helper->fields_value['canceled_dlv_os[]_'.$value] = 1;
        }
        
        $delivered_strpick_os = json_decode(Configuration::get('ESATISFACTION_DELIVERED_STRPICK_OS_IDS'));
        foreach ($delivered_strpick_os as $value) {
            $helper->fields_value['delivered_strpick_os[]_'.$value] = 1;
        }
        $store_pickup_carriers = json_decode(Configuration::get('ESATISFACTION_STOREPICKUP_IDS'));
        foreach ($store_pickup_carriers as $value) {
            $helper->fields_value['store_pickup_carriers[]_'.$value] = 1;
        }
        $canceled_strpick_os = json_decode(Configuration::get('ESATISFACTION_CANCELED_STRPICK_OS_IDS'));
        foreach ($canceled_strpick_os as $value) {
            $helper->fields_value['canceled_strpick_os[]_'.$value] = 1;
        }
        
        
       
        $this->context->smarty->assign(array(
            'icon' => $this->defaultImage,
            'name' => $this->name
        ));
        
        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl').
        $helper->generateForm($fields_form);
    }
    
    /**
    * Hook για την φόρτωση του js στη σελίδα διαχείρισης.
    *
    * @author    e-satisfaction SA
    * @copyright (c) 2018, e-satisfaction SA
    * @param array $params
    * @return boolean
    */
    public function hookDisplayBackOfficeHeader($params)
    {
        if (Tools::getValue('configure') != $this->name) {
            return;
        }
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->_path.'views/js/admin.js');
    }
    
    /**
     * Hook για την συγκέντρωση στατιστικών
     * κατά την δημιουργία της παραγγελίας.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     * @param array $params
     * @return boolean
     */
    public function hookDisplayOrderConfirmation($params)
    {
        if (isset($params['objOrder']) && Validate::isLoadedObject($params['objOrder'])) {
            $customer = new Customer($params['objOrder']->id_customer);
            $invoice_address = new Address($params['objOrder']->id_address_invoice);
            
            // Χρειάζεται carrier object γιατί αποθηκεύομε reference
            // και η order έχει το carrier_id
            $carrier = new Carrier($params['objOrder']->id_carrier);
            $is_store_pickup = (in_array(
                $carrier->id_reference,
                json_decode(Configuration::get('ESATISFACTION_STOREPICKUP_IDS'))
            )) ? true : false ;
            
            $siteid = Configuration::get('ESATISFACTION_SITE_ID');
            $quest_id = Configuration::get('ESATISFACTION_CHKOUTID');
            $this->context->smarty->assign(array(
                'order_id' => $params['objOrder']->id,
                'order_date' => $params['objOrder']->date_add,
                'siteid' => $siteid,
                'checkout_quest_id' => $quest_id,
                'customer_phone' => $invoice_address->phone_mobile,
                'is_store_pickup' => $is_store_pickup,
                'customer_email' => $customer->email,
            ));
            return $this->display(__FILE__, 'checkout.tpl');
        } else {
            return false;
        }
    }
    
    /**
     * Hook για την τοποθέτηση του javascript κωδικού στο footer
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     * @param array $params
     * @return string
     */
    public function hookDisplayFooter($params)
    {
        return $this->display(__FILE__, 'footer.tpl');
    }
    
    /**
     * Hook για την τοποθέτηση του javascript κωδικού στο header
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     * @param array $params
     * @return string
     */
    public function hookDisplayHeader($params)
    {
        $this->context->smarty->assign(array(
            'siteid' => Configuration::get('ESATISFACTION_SITE_ID'),
        ));

        return $this->display(__FILE__, 'header.tpl');
    }
    
    /**
     * Hook όταν αλλάζει το status μιας παραγγελίας.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     * @param array $params
     * @return void
     */
    public function hookActionOrderStatusPostUpdate($params)
    {
        if (Configuration::get('ESATISFACTION_MANUAL_SEND') == '1') {
            $order_obj = new Order((int) $params['id_order']);
            $customer = new Customer($order_obj->id_customer);
            
            $invoice_address = new Address($order_obj->id_address_invoice);
            $carrier = new Carrier($order_obj->id_carrier);
            $is_store_pickup = (in_array(
                $carrier->id_reference,
                json_decode(Configuration::get('ESATISFACTION_STOREPICKUP_IDS'))
            )) ? true : false ;
           
            if (in_array(
                $params['newOrderStatus']->id,
                json_decode(Configuration::get('ESATISFACTION_DELIVERED_DLV_OS_IDS'))
            ) ||
            in_array(
                $params['newOrderStatus']->id,
                json_decode(Configuration::get('ESATISFACTION_DELIVERED_STRPICK_OS_IDS'))
            )) {
                $this->sendQuestionnaire($order_obj, $customer, $invoice_address, $is_store_pickup);
            }
           
            if ((in_array(
                $params['newOrderStatus']->id,
                json_decode(Configuration::get('ESATISFACTION_CANCELED_DLV_OS_IDS'))
            ) && !$is_store_pickup) ||
            (in_array(
                $params['newOrderStatus']->id,
                json_decode(Configuration::get('ESATISFACTION_CANCELED_STRPICK_OS_IDS'))
            )
            && $is_store_pickup)) {
                $this->cancelQuestionnaire($order_obj);
            }
        }
    }
    
    /**
     * Γίνεται η κλήση στο API.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     * @param string $url
     * @return mixed
     */
    public function makeApiCall($url, $data, $expected_code, $method = null, $extra_options = null)
    {
        $auth = Configuration::get('ESATISFACTION_AUTH');
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array('esat-auth: '.$auth),
         ));
         
        if ($method) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        } else {
            curl_setopt($ch, CURLOPT_POST, 1) ;
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        if ($extra_options) {
            foreach ($extra_options as $option => $value) {
                curl_setopt($ch, $option, $value);
            }
        }
         
        $res  = curl_exec($ch);
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == $expected_code) {
            return $res;
        }
        return false;
    }
    
    /**
     * Βάση του channel προσθέτει στο queue το νέο item.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     * @param string $url
     * @return mixed
     */
    public function sendQuestionnaire($order_obj, $customer, $invoice_address, $is_store_pickup)
    {
        $url = 'https://api.e-satisfaction.com/v3.0/q/questionnaire/';
        if ($is_store_pickup) {
            $url .= Configuration::get('ESATISFACTION_STRPICKID').'/pipeline/'.
                Configuration::get('ESATISFACTION_STRPICK_PIPE_ID');
        } else {
            $url .= Configuration::get('ESATISFACTION_HOMEDLVID').'/pipeline/'.
                Configuration::get('ESATISFACTION_HOMEDLV_PIPE_ID');
        }
        $url .= '/queue/item';
        
        $data = array(
            'responder_channel_identifier' => $customer->email,
            'locale' => Language::getIsoById($customer->id_lang),
            'metadata' => array(
                'questionnaire' => array(
                    'transaction_id' => $order_obj->id,
                    'transaction_date' => $order_obj->date_add
                ),
                'responder' => array(
                      'email' => $customer->email,
                      'phone_number' => $invoice_address->phone_mobile
                )
            )
        );
        $res = $this->makeApiCall($url, $data, '201');
        if ($res) {
            $res_data = json_decode($res);
            $this->insertQueueItem($order_obj->id, $res_data->item_id);
        }
    }
    
    /**
     * Αφαιρεί το item από το queue
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     * @param string $url
     * @return mixed
     */
    public function cancelQuestionnaire($order_obj)
    {
        $url = 'https://api.e-satisfaction.com/v3.0/q/queue/item/';
        $item_id = $this->getQueueItem($order_obj->id);
        $extra_options = array(
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 4,
        );
        $res = $this->makeApiCall($url.$item_id, array(), '204', 'DELETE', $extra_options);
        if ($res !== false) {
            $this->deleteQueueItem($order_obj->id);
        }
    }
    
    /**
     * Δημιουργεί νέα εγγραφή item_id και order_id στη βάση
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     * @param string $url
     * @return mixed
     */
    public function insertQueueItem($order_id, $item_id)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->insert(
            'esat_order_stat',
            array( 'id_order' => $order_id ,'item_id' => $item_id ),
            false,
            true,
            Db::REPLACE
        );
    }
    
    /**
     * Επιστρέφει το item_id από τη βάση
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     * @param string $url
     * @return mixed
     */
    public function getQueueItem($order_id)
    {
        $sql = 'SELECT `item_id` FROM `'. _DB_PREFIX_ .'esat_order_stat` WHERE `id_order` = '.(int)$order_id ;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }
    
    /**
     * Διαγράφει το item_id από τη βάση
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     * @param string $url
     * @return mixed
     */
    public function deleteQueueItem($order_id)
    {
        Db::getInstance(_PS_USE_SQL_SLAVE_)->delete('esat_order_stat', 'id_order = '.$order_id);
    }
}
