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
 * @version   1.1.0
 */

class Esatisfaction extends Module
{
    /**
     * @var string
     */
    public $defaultImage = '../modules/esatisfaction/views/img/esat_32x32.png';

    /**
     * @var int
     */
    public $app_id;

    /**
     * Esatisfaction constructor.
     */
    public function __construct()
    {
        $this->name = 'esatisfaction';
        $this->tab = 'other';
        $this->version = '1.1.0';
        $this->author = 'e-satisfaction SA';
        $this->tab = 'analytics_stats';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');
        $this->module_key = '5cd651fbc7befadc249391eb1ef2bf7d';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('E-satisfaction Module');
        $this->description = $this->l('Adds the code necessary to gather customer satisfactiond data');
        $this->app_id = Configuration::get('ESATISFACTION_APP_ID');
        if (!defined('_PS_VERSION_')) {
            exit;
        }
    }

    /**
     * Install module and register it for hooks displayOrderConfirmation,
     * actionOrderStatusPostUpdate, displayHeader,
     * displayBackOfficeHeader
     *
     * @author        e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     * @return bool
     */
    public function install()
    {
        Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'esat_order_stat` (
        `order_id` INT( 11 ) NOT NULL,
        `item_id` VARCHAR(100) NOT NULL,
        PRIMARY KEY (`order_id`)
        ) ENGINE = InnoDB');

        return parent::install() &&
            $this->registerHook('displayOrderConfirmation') &&
            $this->registerHook('actionOrderStatusPostUpdate') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayHeader');
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        return (bool)parent::uninstall();
    }

    /**
     * Load the configuration page.
     *
     * @author        e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     * @return string
     */
    public function getContent()
    {
        $output = null;
        if (Tools::isSubmit('submit' . $this->name)) {
            $app_id = Tools::getValue('ESATISFACTION_APP_ID');
            $domain = Tools::getValue('ESATISFACTION_DOMAIN');
            $auth = Tools::getValue('ESATISFACTION_AUTH');
            $output = null;
            if (!$app_id || empty($app_id)) {
                $output .= $this->displayError($this->l('Site Id cannot be empty'));
            }

            if (empty($output)) {
                Configuration::updateValue('ESATISFACTION_APP_ID', $app_id);
                Configuration::updateValue('ESATISFACTION_DOMAIN', $domain);
                Configuration::updateValue('ESATISFACTION_AUTH', $auth);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }

            Configuration::updateValue('ESATISFACTION_MANUAL_SEND', Tools::getValue('manual_send'));
            Configuration::updateValue('ESATISFACTION_CHKOUTID', Tools::getValue('ESATISFACTION_CHKOUTID'));
            Configuration::updateValue('ESATISFACTION_HOMEDLVID', Tools::getValue('ESATISFACTION_HOMEDLVID'));
            Configuration::updateValue('ESATISFACTION_STRPICKID', Tools::getValue('ESATISFACTION_STRPICKID'));
            Configuration::updateValue('ESATISFACTION_HOMEDLV_PIPE_ID', Tools::getValue('ESATISFACTION_HOMEDLV_PIPE_ID'));
            Configuration::updateValue('ESATISFACTION_STRPICK_PIPE_ID', Tools::getValue('ESATISFACTION_STRPICK_PIPE_ID'));
            Configuration::updateValue('ESATISFACTION_HOMEDLVID_DAYS', Tools::getValue('ESATISFACTION_HOMEDLVID_DAYS'));
            Configuration::updateValue('ESATISFACTION_DELIVERED_DLV_OS_IDS', json_encode(Tools::getValue('delivered_dlv_os')));
            Configuration::updateValue('ESATISFACTION_CANCELED_DLV_OS_IDS', json_encode(Tools::getValue('canceled_dlv_os')));
            Configuration::updateValue('ESATISFACTION_STOREPICKUP_IDS', json_encode(Tools::getValue('store_pickup_carriers')));
            Configuration::updateValue('ESATISFACTION_COURIER_IDS', json_encode(Tools::getValue('courier_carriers')));
            Configuration::updateValue('ESATISFACTION_DELIVERED_STRPICK_OS_IDS', json_encode(Tools::getValue('delivered_strpick_os')));
            Configuration::updateValue('ESATISFACTION_CANCELED_STRPICK_OS_IDS', json_encode(Tools::getValue('canceled_strpick_os')));
        }

        return $output . $this->displayForm();
    }

    /**
     * Display the configuration form.
     *
     * @author        e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     * @return string
     */
    public function displayForm()
    {
        $fields_form = null;

        // Get default Language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $carriers_raw = Carrier::getCarriers($default_lang, true, false);
        $carriers = array();
        foreach ($carriers_raw as $carrier) {
            $carriers[] = array(
                'val' => $carrier['id_reference'],
                'carrier_reference' => $carrier['id_reference'],
                'name' => $carrier['name'], );
        }

        $order_statuses_raw = OrderState::getOrderStates($default_lang);
        $order_statuses = array();
        foreach ($order_statuses_raw as $order_status) {
            $order_statuses[] = array(
                'val' => $order_status['id_order_state'],
                'order_state_id' => $order_status['id_order_state'],
                'name' => $order_status['name'],
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
                    'name' => 'ESATISFACTION_APP_ID',
                    'size' => 20,
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Working Domain'),
                    'name' => 'ESATISFACTION_DOMAIN',
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
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'manual_send_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                ),
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
                    ),
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Condition to determine when it’s after delivery'),
                    'name' => 'courier_carriers[]',
                    'desc' => 'Select the carriers that are used for after delivery',
                    'values'  => array(
                       'query' => $carriers,
                       'id' => 'carrier_reference',
                       'name'  => 'name',
                ), ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Order status(es) to cancel questionnaire'),
                    'name' => 'canceled_dlv_os[]',
                    'values'  => array(
                       'query' => $order_statuses,
                       'id' => 'order_state_id',
                       'name'  => 'name',
                    ),
                ),
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
                    ),
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Condition to determine when it’s store pickup'),
                    'name' => 'store_pickup_carriers[]',
                    'desc' => 'Select the carriers that are used for store pickup',
                    'values'  => array(
                       'query' => $carriers,
                       'id' => 'carrier_reference',
                       'name'  => 'name',
                ), ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Order status(es) to cancel questionnaire'),
                    'name' => 'canceled_strpick_os[]',
                    'values'  => array(
                       'query' => $order_statuses,
                       'id' => 'order_state_id',
                       'name'  => 'name',
                    ),
                ),
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
        $helper->fields_value['ESATISFACTION_APP_ID'] = Configuration::get('ESATISFACTION_APP_ID');
        $helper->fields_value['ESATISFACTION_DOMAIN'] = Configuration::get('ESATISFACTION_DOMAIN');
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
            $helper->fields_value['delivered_dlv_os[]_' . $value] = 1;
        }
        $courier_carriers = json_decode(Configuration::get('ESATISFACTION_COURIER_IDS'));
        foreach ($courier_carriers as $value) {
            $helper->fields_value['courier_carriers[]_' . $value] = 1;
        }
        $canceled_dlv_os = json_decode(Configuration::get('ESATISFACTION_CANCELED_DLV_OS_IDS'));
        foreach ($canceled_dlv_os as $value) {
            $helper->fields_value['canceled_dlv_os[]_' . $value] = 1;
        }

        $delivered_strpick_os = json_decode(Configuration::get('ESATISFACTION_DELIVERED_STRPICK_OS_IDS'));
        foreach ($delivered_strpick_os as $value) {
            $helper->fields_value['delivered_strpick_os[]_' . $value] = 1;
        }
        $store_pickup_carriers = json_decode(Configuration::get('ESATISFACTION_STOREPICKUP_IDS'));
        foreach ($store_pickup_carriers as $value) {
            $helper->fields_value['store_pickup_carriers[]_' . $value] = 1;
        }
        $canceled_strpick_os = json_decode(Configuration::get('ESATISFACTION_CANCELED_STRPICK_OS_IDS'));
        foreach ($canceled_strpick_os as $value) {
            $helper->fields_value['canceled_strpick_os[]_' . $value] = 1;
        }

        $this->context->smarty->assign(array(
            'icon' => $this->defaultImage,
            'name' => $this->name,
        ));

        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl') . $helper->generateForm($fields_form);
    }

    /**
     * Load js file in the configuration page.
     *
     * @author        e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     *
     * @param array $params
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
     * Hook after an order is validated.
     *
     * @author        e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     *
     * @param array $params
     *
     * @return bool
     */
    public function hookDisplayOrderConfirmation($params)
    {
        if (!isset($params['objOrder']) || !Validate::isLoadedObject($params['objOrder'])) {
            return false;
        }

        $customer = new Customer($params['objOrder']->id_customer);
        $invoice_address = new Address($params['objOrder']->id_address_invoice);

        $carrier = new Carrier($params['objOrder']->id_carrier);
        $is_store_pickup = (in_array(
            $carrier->id_reference,
            json_decode(Configuration::get('ESATISFACTION_STOREPICKUP_IDS'))
        )) ? 'true' : 'false';

        $app_id = Configuration::get('ESATISFACTION_APP_ID');
        $quest_id = Configuration::get('ESATISFACTION_CHKOUTID');
        $this->context->smarty->assign(array(
            'order_id' => $params['objOrder']->id,
            'order_date' => $params['objOrder']->date_add,
            'app_id' => $app_id,
            'checkout_quest_id' => $quest_id,
            'customer_phone' => $invoice_address->phone_mobile,
            'is_store_pickup' => $is_store_pickup,
            'customer_email' => $customer->email,
        ));

        return $this->display(__FILE__, 'checkout.tpl');
    }

    /**
     * Add script in header
     *
     * @author        e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     *
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayHeader($params)
    {
        $this->context->smarty->assign(array(
            'app_id' => Configuration::get('ESATISFACTION_APP_ID'),
        ));

        return $this->display(__FILE__, 'header.tpl');
    }

    /**
     * Add or remove an item from the queue list if manual send is enabled
     *
     * @author        e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     *
     * @param array $params
     *
     * @throws Exception
     */
    public function hookActionOrderStatusPostUpdate($params)
    {
        if (Configuration::get('ESATISFACTION_MANUAL_SEND') == '1') {
            $order_obj = new Order((int)$params['id_order']);
            $customer = new Customer($order_obj->id_customer);

            $invoice_address = new Address($order_obj->id_address_invoice);
            $carrier = new Carrier($order_obj->id_carrier);
            $is_store_pickup = (in_array(
                $carrier->id_reference,
                json_decode(Configuration::get('ESATISFACTION_STOREPICKUP_IDS'))
            )) ? true : false;

            if (in_array($params['newOrderStatus']->id, json_decode(Configuration::get('ESATISFACTION_DELIVERED_DLV_OS_IDS')))
                || in_array($params['newOrderStatus']->id, json_decode(Configuration::get('ESATISFACTION_DELIVERED_STRPICK_OS_IDS')))) {
                $this->sendQuestionnaire($order_obj, $customer, $invoice_address, $is_store_pickup);
            }

            if (
                (in_array($params['newOrderStatus']->id, json_decode(Configuration::get('ESATISFACTION_CANCELED_DLV_OS_IDS')))
                    && !$is_store_pickup)
                ||
                (in_array($params['newOrderStatus']->id, json_decode(Configuration::get('ESATISFACTION_CANCELED_STRPICK_OS_IDS')))
                    && $is_store_pickup)
            ) {
                $this->cancelQuestionnaire($order_obj);
            }
        }
    }

    /**
     * Make the API call
     *
     * @author        e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     *
     * @param string $url
     * @param array  $data
     * @param string $expected_code
     * @param string $method
     * @param array  $extra_options
     *
     * @return mixed
     */
    public function makeApiCall($url, $data, $expected_code, $method = null, $extra_options = array())
    {
        // e-satisfaction API base url
        $baseUrl = 'https://api.e-satisfaction.com/v3.2';

        $auth = Configuration::get('ESATISFACTION_AUTH');
        $domain = Configuration::get('ESATISFACTION_DOMAIN');
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => sprintf('%s/%s', $baseUrl, $url),
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array('esat-auth: ' . $auth, 'esat-domain: ' . $domain),
        ));

        if ($method) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        } else {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        foreach ($extra_options as $option => $value) {
            curl_setopt($ch, $option, $value);
        }

        $res = curl_exec($ch);
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == $expected_code) {
            return $res;
        }

        return false;
    }

    /**
     * Send the questionnaire
     *
     * @author        e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     *
     * @param object $order_obj
     * @param object $customer
     * @param object $invoice_address
     * @param bool   $is_store_pickup
     *
     * @throws Exception
     */
    public function sendQuestionnaire($order_obj, $customer, $invoice_address, $is_store_pickup)
    {
        // Get questionnaire id and pipeline id
        $questionnaireId = $is_store_pickup ? Configuration::get('ESATISFACTION_STRPICKID') : Configuration::get('ESATISFACTION_HOMEDLVID');
        $pipelineId = $is_store_pickup ? Configuration::get('ESATISFACTION_STRPICK_PIPE_ID') : Configuration::get('ESATISFACTION_HOMEDLV_PIPE_ID');

        // Form url
        $url = sprintf('/q/questionnaire/%s/pipeline/%s/queue/item', $questionnaireId, $pipelineId);

        // Create data
        $data = array(
            'responder_channel_identifier' => $customer->email,
            'locale' => Language::getIsoById($customer->id_lang),
            'metadata' => array(
                'questionnaire' => array(
                    'transaction_id' => $order_obj->id,
                    'transaction_date' => $order_obj->date_add,
                ),
                'responder' => array(
                      'email' => $customer->email,
                      'phone_number' => $invoice_address->phone_mobile,
                ),
            ),
        );

        // Check for delay days
        $delayDays = Configuration::get('ESATISFACTION_HOMEDLVID_DAYS');
        if (!$is_store_pickup && $delayDays > 0) {
            $sendTime = (new DateTime())->add(new DateInterval(sprintf('P%sD', $delayDays)));
            $data['send_time'] = $sendTime->format(DateTime::ATOM);
        }

        // Make API Call
        $res = $this->makeApiCall($url, $data, '201');
        if ($res) {
            $res_data = json_decode($res);
            $this->insertQueueItem($order_obj->id, $res_data->item_id);
        }
    }

    /**
     * Remove item from queue
     *
     * @author        e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     *
     * @param object $order_obj
     */
    public function cancelQuestionnaire($order_obj)
    {
        $item_id = $this->getQueueItem($order_obj->id);
        $url = sprintf('/q/queue/item/%s', $item_id);
        $data = array(
            'status_id' => 5,
            'result' => 'Order cancelled from Prestashop Admin',
        );
        $res = $this->makeApiCall($url, $data, '200', 'PATCH');
        if ($res !== false) {
            $this->deleteQueueItem($order_obj->id);
        }
    }

    /**
     * Create or update item_id and order_id in the database
     *
     * @author        e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     *
     * @param int    $order_id
     * @param string $item_id
     *
     * @return bool
     */
    public function insertQueueItem($order_id, $item_id)
    {
        return Db::getInstance()->insert(
            'esat_order_stat',
            array('order_id' => $order_id, 'item_id' => $item_id),
            false,
            true,
            Db::REPLACE
        );
    }

    /**
     * Get the item_id from the database
     *
     * @author        e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     *
     * @param int $order_id
     *
     * @return bool
     */
    public function getQueueItem($order_id)
    {
        $sql = 'SELECT `item_id` FROM `' . _DB_PREFIX_ . 'esat_order_stat` WHERE `order_id` = ' . (int)$order_id;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    /**
     * Remove item_id from the database
     *
     * @author        e-satisfaction SA
     * @copyright (c) 2018, e-satisfaction SA
     *
     * @param int $order_id
     */
    public function deleteQueueItem($order_id)
    {
        Db::getInstance()->delete('esat_order_stat', 'order_id = ' . $order_id);
    }
}
