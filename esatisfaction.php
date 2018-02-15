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
 * @copyright 2016 e-satisfaction SA
 * @license   https://opensource.org/licenses
 * @version 0.2.9
 */

class Esatisfaction extends Module
{
    /**
     * @var string $www Χρησιμοποιείται μόνο για testing
     * για να καλείται το sandbox του API από το live.
     */
    public $www;

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

    /**
     * Ο constructor του class
     *
     * @author e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @return void
     */
    public function __construct()
    {
        $this->name = 'esatisfaction';
        $this->tab = 'other';
        $this->version = '0.2.9';
        $this->author = 'e-satisfaction SA';
        $this->tab = 'analytics_stats';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->module_key = '5cd651fbc7befadc249391eb1ef2bf7d';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('e-satisfaction tracking module');
        $this->description = $this->l('Adds the code necessary to gather customer satisfactiond data');
        $this->www = 'www';
        $this->id_shop = Configuration::get('ESATISFACTION_SITE_ID');
        if (!defined('_PS_VERSION_')) {
            exit;
        }
    }

    /**
     * Απεγκατάσταση του module
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @return bool
     */
    public function uninstall()
    {
        if (parent::uninstall() &&
                $this->uninstallBackOfficeTabs()) {
            return true;
        }

        return false;
    }

    /**
     * Εγκαθιστά το module στις θέσεις, displayOrderConfirmation,
     * displayAdminCustomers, actionOrderStatusPostUpdate,
     * displayBackOfficeHeader, displayFooter και ανάλογα με την έκδοση στην
     * displayAdminOrderLeft ή στην displayAdminOrder
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @return bool
     */
    public function install()
    {
        $newVersion = false;
        if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
            $newVersion = true;
        }
        Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'esat_data` (
            `id_order` INT( 11 ) NOT NULL,
            `needs_update` TINYINT(4) NOT NULL,
            `last_update` DATETIME NOT NULL,
            `img` VARCHAR(200) NOT NULL
            ) ENGINE = MYISAM');
        return parent::install() &&
                $this->installBackOfficeTabs() &&
                $this->registerHook('displayOrderConfirmation') &&
                $this->registerHook('displayAdminCustomers') &&
                $this->registerHook('actionOrderStatusPostUpdate') &&
                $this->registerHook('displayBackOfficeHeader') &&
                $this->registerHook('displayFooter') &&
                $this->registerHook('actionAdminOrdersListingResultsModifier') &&
                ($newVersion) ? $this->registerHook('displayAdminOrderLeft') : $this->registerHook('displayAdminOrder');
    }

    /**
     * Εγκατάσταση του tab στο διαχειριστικό.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @return boolean
     */
    private function installBackOfficeTabs()
    {
        $tabs = array(
            array(
                'class' => 'EsatisfactionQuestionnaire',
                'name' => $this->l('Custom Questions'),
            ),
        );
        $this->installBackOfficeTab('EsatisfactionQuestionnaire', 'e-satisfaction', 0);
        $mainTabId = Tab::getIdFromClassName('EsatisfactionQuestionnaire');
        if ($mainTabId > 0) {
            foreach ($tabs as $tab) {
                $this->installBackOfficeTab($tab['class'], $tab['name'], $mainTabId);
            }
        }

        return true;
    }

    /**
     * Απεγκατάσταση του tab στο διαχειριστικό.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @return boolean
     */
    private function uninstallBackOfficeTabs()
    {
        $tabs = Tab::getCollectionFromModule($this->name);
        foreach ($tabs as $tab) {
            $tab->delete();
        }

        return true;
    }

    /**
     * Παίρνει από το API τα ερωτηματολόγια του καταστήματος.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @return array
     */
    public function getCustomQuestionApi()
    {
        $url = 'https://' . $this->www . '.e-satisfaction.gr/api/v2/prestashop/custom_question_section/';
        $url .= (int) $this->id_shop;

        return $this->makeApiCall($url);
    }

    /**
     * Ελέγχει εάν υπάρχουν δεδομένα στο API για το συγκεκριμένο id_order
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @param int $id_order
     * @return array
     */
    public function getAggregatedOrders($id_order)
    {
        $url = 'https://' . $this->www . '.e-satisfaction.gr/api/v2/prestashop/aggregated_order_page/';
        $url .= (int) $this->id_shop . '?order_id=' . (int) $id_order;

        return $this->makeApiCall($url);
    }

    /**
     * Εγκατάσταση του backoffice Tab
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @param string $class
     * @param string $name
     * @param int $parent
     * @return boolean
     */
    private function installBackOfficeTab($class, $name, $parent)
    {
        $languages = Language::getLanguages();
        $names = array();
        foreach ($languages as $language) {
            $names[$language['id_lang']] = $name;
        }
        $tab = new Tab();
        $tab->name = $names;
        $tab->class_name = $class;
        $tab->module = $this->name;
        $tab->id_parent = $parent;
        if (!$tab->save()) {
            return false;
        }

        return true;
    }

    /**
     * Εμφάνιση του διαχειριστικού του module.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @return string
     */
    public function getContent()
    {
        $output = null;
        if (Tools::isSubmit('submit' . $this->name)) {
            $site_id = (int) Tools::getValue('ESATISFACTION_SITE_ID');
            $auth = Tools::getValue('ESATISFACTION_AUTH');
            $privateKey = Tools::getValue('ESATISFACTION_PRIVATE_KEY');
            $publickKey = Tools::getValue('ESATISFACTION_PUBLIC_KEY');
            $output = null;
            if (!$site_id || empty($site_id)) {
                $output .= $this->displayError($this->l('Site Id cannot be empty'));
            }

            if (!Validate::isInt($site_id)) {
                $output .= $this->displayError($this->l('Site Id is not valid'));
            }

            if (!$auth || empty($auth)) {
                $output .= $this->displayError($this->l('Site Auth cannot be empty'));
            }
            if (!$publickKey || empty($publickKey)) {
                $output .= $this->displayError($this->l('Public Key cannot be empty'));
            }

            if (!$privateKey || empty($privateKey)) {
                $output .= $this->displayError($this->l('Private Key cannot be empty'));
            }
            if (empty($output)) {
                Configuration::updateValue('ESATISFACTION_SITE_ID', $site_id);
                Configuration::updateValue('ESATISFACTION_AUTH', $auth);
                Configuration::updateValue('ESATISFACTION_PUBLIC_KEY', $publickKey);
                Configuration::updateValue('ESATISFACTION_PRIVATE_KEY', $privateKey);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
            Configuration::updateValue('ESATISFACTION_BROWSEQ', Tools::getValue('ESATISFACTION_BROWSEQ'));
            Configuration::updateValue('ESATISFACTION_CHKOUTQ', Tools::getValue('ESATISFACTION_CHKOUTQ'));
            Configuration::updateValue('ESATISFACTION_AFTRSALES', Tools::getValue('ESATISFACTION_AFTRSALES'));
            $this->setConfigurationsToAPI();
        }

        return $output . $this->displayForm();
    }

    /**
     * Στέλνει προς αποθήκευση τις παραμέτρους
     * από το διαχειριστικό, στο API.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @return array
     */
    private function setConfigurationsToAPI()
    {
        $url = 'https://' . $this->www . '.e-satisfaction.gr/api/v2/prestashop/module_settings/' . (int) $this->id_shop;
        $url .= '?';
        $url .= 'config_pg_browse=' . (bool) Configuration::get('ESATISFACTION_BROWSEQ');
        $url .= '&config_pg_checkout=' . (bool) Configuration::get('ESATISFACTION_CHKOUTQ');
        $url .= '&config_pg_aftersales=' . (bool) Configuration::get('ESATISFACTION_AFTRSALES');
        if ($res = $this->makeApiCall($url)) {
            if ($res = Tools::jsonDecode($res)) {
                return $res;
            }
        }

        return null;
    }

    /**
     * Λαμβάνει τις παραμέτρους από το API για το διαχειριστικό.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @return array
     */
    private function getConfigurationFromAPI()
    {
        if (!$this->id_shop) {
            return false;
        }

        $url = 'https://' . $this->www . '.e-satisfaction.gr/api/v2/prestashop/get_module_settings/';
        $url .= (int) $this->id_shop;
        $apiData = array();
        if ($res = $this->makeApiCall($url)) {
            if ($res = Tools::jsonDecode($res)) {
                foreach ($res as $confs) {
                    if (is_object($confs)) {
                        foreach ($confs as $key => $conf) {
                            $apiData[$key] = $conf;
                        }
                    }
                }
            }
        }

        return $apiData;
    }

    /**
     * Εμφάνιση του διαχειριστικού του module
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @return string
     */
    public function displayForm()
    {
        $fields_form = null;
        // Get default Language
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('e-satisfaction settings'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Site ID'),
                    'name' => 'ESATISFACTION_SITE_ID',
                    'size' => 20,
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Site Authentication key'),
                    'name' => 'ESATISFACTION_AUTH',
                    'size' => 45,
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Public key'),
                    'name' => 'ESATISFACTION_PUBLIC_KEY',
                    'size' => 45,
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Private key'),
                    'name' => 'ESATISFACTION_PRIVATE_KEY',
                    'size' => 45,
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
                'title' => $this->l('Additional Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Browse Questions'),
                    'name' => 'ESATISFACTION_BROWSEQ',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('On'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Off'),
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Checkout Questionnaires'),
                    'name' => 'ESATISFACTION_CHKOUTQ',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('On'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Off'),
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('After Sales Survey'),
                    'name' => 'ESATISFACTION_AFTRSALES',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('On'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Off'),
                        ),
                    ),
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
        if ($dataFromAPI = $this->getConfigurationFromAPI()) {
            $helper->fields_value['ESATISFACTION_BROWSEQ'] = $dataFromAPI['config_pg_browse'];
            $helper->fields_value['ESATISFACTION_CHKOUTQ'] = $dataFromAPI['config_pg_checkout'];
            $helper->fields_value['ESATISFACTION_AFTRSALES'] = $dataFromAPI['config_pg_aftersales'];
        } else {
            $helper->fields_value['ESATISFACTION_BROWSEQ'] = Configuration::get('ESATISFACTION_BROWSEQ');
            $helper->fields_value['ESATISFACTION_CHKOUTQ'] = Configuration::get('ESATISFACTION_CHKOUTQ');
            $helper->fields_value['ESATISFACTION_AFTRSALES'] = Configuration::get('ESATISFACTION_AFTRSALES');
        }
        $helper->fields_value['ESATISFACTION_SITE_ID'] = Configuration::get('ESATISFACTION_SITE_ID');
        $helper->fields_value['ESATISFACTION_AUTH'] = Configuration::get('ESATISFACTION_AUTH');
        $helper->fields_value['ESATISFACTION_PUBLIC_KEY'] = Configuration::get('ESATISFACTION_PUBLIC_KEY');
        $helper->fields_value['ESATISFACTION_PRIVATE_KEY'] = Configuration::get('ESATISFACTION_PRIVATE_KEY');

        $icon = $this->defaultImage;
        if (isset($dataFromAPI['module_pg_banner_url'])) {
            $icon = $dataFromAPI['module_pg_banner_url'];
        }
        $this->context->smarty->assign(array(
            'icon' => $this->validateImage($icon),
            'name' => $this->name
        ));

        return $helper->generateForm($fields_form)
        . $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
    }

    /**
     * Hook για την συγκέντρωση στατιστικών
     * κατά την δημιουργία της παραγγελίας.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @param array $params
     * @return string
     */
    public function hookDisplayOrderConfirmation($params)
    {
        if (isset($params['objOrder']) && Validate::isLoadedObject($params['objOrder'])) {
            $url = 'https://' . $this->www . '.e-satisfaction.gr/miniquestionnaire/genkey.php?';
            $url .= 'site_auth=' . Configuration::get('ESATISFACTION_AUTH');
            $token = Tools::file_get_contents($url);
            $this->context->smarty->assign(array(
                'order_id' => $params['objOrder']->id,
                'token' => $token,
                'email' => $params['cookie']->email,
            ));

            return $this->display(__FILE__, 'questionnaire.tpl');
        } else {
            return '';
        }
    }

    /**
     * Hook για το orderlist.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @param array $params
     * @return boolean
     */
    public function hookActionAdminOrdersListingResultsModifier($params)
    {
        if (!Configuration::get('ESATISFACTION_SITE_ID')) {
            return false;
        }

        $page_list = array();
        $result_list = array();
        $id_string = "";
        //get order list order_ids and prepare tables
        foreach ($params['list'] as $list) {
            $oid = (int)$list['id_order'];
            $page_list[] = $oid;
            $id_string .= $oid . ",";
        }
        $id_string2 = trim($id_string, ",");

        $sql = 'SELECT id_order FROM ' . _DB_PREFIX_ . 'esat_data '
                . 'WHERE id_order IN (' . array_map('intval', $id_string2) . ') '
                . 'ORDER BY  `' . _DB_PREFIX_ . 'esat_data`.`id_order` DESC';
        $results = Db::getInstance()->executeS($sql);

        if (count($results) > 0) {
            foreach ($results as $resultvalue) {
                foreach ($resultvalue as $id) {
                    $result_list[] = $id;
                }
            }

            $array = array_diff($page_list, $result_list);
            //on the SQL select below we set on how often the updates
            //should take place (eg. for 30min updates change 3600 to 1800)
            $sql = 'SELECT id_order FROM ' . _DB_PREFIX_ . 'esat_data '
                    . 'WHERE id_order IN (' . array_map('intval', $id_string2) . ') '
                    . 'AND needs_update =1 AND UNIX_TIMESTAMP( NOW() ) - UNIX_TIMESTAMP( last_update ) > 3600 '
                    . 'ORDER BY  `' . _DB_PREFIX_ . 'esat_data`.`id_order` DESC';
            $need_update = Db::getInstance()->executeS($sql);
            foreach ($need_update as $row => $value) {
                foreach ($value as $id) {
                    array_push($array, $id);
                }
            }
        } else {
            $array = $page_list;
        }
        //get order list order_ids and prepare tables -end
        $decoded = array();
        if (count($array) > 100) {
            $arrays = array_chunk($array, 100);
            foreach ($arrays as $rows) {
                //prepare the json
                $jdata = '{"order":[';
                foreach ($rows as $row) {
                    $jdata .= '{"id":"' . $row . '"},';
                }
                $jdata = rtrim($jdata, ",");
                $jdata .= ']}';
                $jsondata = Esatisfaction::apiCallJson($jdata);
                foreach ($jsondata as $d) {
                    $decoded[] = $d;
                }
            }
        } else {
            //prepare the json
            $json_data_2 = '{"order":[';
            foreach ($array as $row) {
                $json_data_2 .= '{"id":"' . $row . '"},';
            }
            $json_data_2 = rtrim($json_data_2, ",");
            $json_data_2 .= ']}';

            $decoded = Esatisfaction::apiCallJson($json_data_2);
        }

        $list = array();
        // set proper header
        header("Content-type: text/html");
        foreach ($decoded as $row => $object) {
            // check cases of retured updates
            if (in_array($object->order_id, $result_list)) {
                Db::getInstance()->execute(
                    'UPDATE '._DB_PREFIX_.'esat_data '
                    .'SET needs_update = "'.(int)$object->need_update.'", '
                    .'last_update = NOW(), img = \''.pSQL($object->ordrlist_pg_imgurl).'\' '
                    .'WHERE id_order = '.(int)$object->order_id
                );
                $list[$object->order_id] = $object->ordrlist_pg_imgurl;
            } elseif ($object->need_update == null) {
                Db::getInstance()->execute(
                    'INSERT INTO '._DB_PREFIX_.'esat_data (id_order, needs_update, last_update, img) '
                    .'VALUES ("'.(int)$object->order_id.'","0",NOW(), "--") '
                );
                $list[$object->order_id] = '--';
            } else {
                Db::getInstance()->execute(
                    'INSERT INTO '. _DB_PREFIX_ .'esat_data '
                    . 'VALUES ("'.(int)$object->order_id.'", "'.(int)$object->need_update.' ",'
                    . 'NOW(), "'.pSQL($object->ordrlist_pg_imgurl).'")'
                );
                $list[$object->order_id] = $object->ordrlist_pg_imgurl;
            }
        }

        //update presta list to show
        if ($decoded != null) {
            foreach ($params['list'] as &$l) {
                $l['esatisfaction'] = $list[$l['id_order']];
            }
        }
    }

    /**
     * function για το aggregated_order_page_json
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @param array $json_data
     * @return string
     */
    public static function apiCallJson($json_data)
    {
        $site_id = (int) Configuration::get('ESATISFACTION_SITE_ID');
        $private_key = Configuration::get('ESATISFACTION_PRIVATE_KEY');
        $public_key = Configuration::get('ESATISFACTION_PUBLIC_KEY');

        $ch = curl_init();
        $url = "http://www.e-satisfaction.gr/api/v2/prestashop/aggregated_order_page_json/" . $site_id . "?";
        $timestamp = microtime(true);
        $method = 'GET';
        $base64_encode = 'base64_encode';
        $hash_hmac = 'hash_hmac';
        $hash = $hash_hmac(
                'sha256',
                $public_key.$timestamp.$method,
                $private_key,
                true
        );

        $hashInBase64 = $base64_encode($hash);

        curl_setopt($ch, CURLOPT_URL, $url. 'json_data=' .rawurlencode($json_data));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-HASH: ' .$hashInBase64,
            'X-Public: ' .$public_key,
            'X-Microtime: ' .$timestamp
        ));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        return Tools::jsonDecode($result);
    }

    /**
     * Hook για την τοποθέτηση του javascript κωδικού στο footer
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @param array $params
     * @return string
     */
    public function hookDisplayFooter($params)
    {
        $this->context->smarty->assign(array(
            'siteid' => (int) $this->id_shop,
            'www' => $this->www,
        ));

        return $this->display(__FILE__, 'esatisfaction.tpl');
    }

    /**
     * Εμφάνιση στατιστικών που λαμβάνονται
     * από το API στις παραγγελίες στο backoffice.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @param array $params
     * @return string
     */
    public function hookDisplayAdminOrderLeft($params)
    {
        if (!Configuration::get('ESATISFACTION_SITE_ID')) {
            return;
        }

        $errors = true;
        $id_order = (int) $params['id_order'];
        $newVersion = false;
        $confirmation = null;
        if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
            $newVersion = true;
        }
        if (Tools::isSubmit('submitMessageFromSatisfaction')) {
            $order = new Order((int) $params['id_order']);
            $customer = new Customer((int) $order->id_customer);
            $tplvars = array();
            $tplvars['{firstname}'] = $customer->firstname;
            $tplvars['{lastname}'] = $customer->lastname;
            $tplvars['{reply}'] = Tools::getValue('reply_msg');
            $tplvars['{link}'] = Tools::url($this->context->link->getPageLink('contact'));

            $mail_sent = @Mail::Send(
                (int) $order->id_lang,
                'reply_msg',
                $this->l('Re: Reply to your Message'),
                $tplvars,
                $customer->email,
                $customer->firstname.' '.$customer->lastname
            );

            if ($mail_sent) {
                $confirmation = $this->l('Your message was successfully sent.');
            } else {
                $this->errors[] = $this->l('An error occurred and your message could not be sent.');
            }
        }
        $url = 'https://' . $this->www . '.e-satisfaction.gr/api/v2/prestashop/order_details/';
        $url .= (int) $this->id_shop . '?order_id=' . $id_order;
        if ($apiRes = Tools::jsonDecode($this->makeApiCall($url))) {
            if (!isset($apiRes->error)) {
                $errors = false;
            }
        }
        $apidata = array();
        if (!$errors) {
            $apidata['ordr_pg_buyagain'] = null;
            $apidata['ordr_pg_general_satisf_aftersales'] = null;
            $apidata['ordr_pg_aftersales_comments_text'] = null;
            $apidata['ordr_pg_general_satisf_aftersales'] = null;
            $apidata['ordr_pg_aftersales_delivtime'] = null;
            $apidata['ordr_pg_aftersales_delivcost'] = null;
            $apidata['ordr_pg_aftersales_prodcond'] = null;
            $apidata['ordr_pg_aftersales_receive'] = null;
            $apidata['ordr_pg_aftersales_prodbox'] = null;
            $apidata['ordr_pg_aftersales_storeservice'] = null;
            $apidata['ordr_pg_aftersales_buyagain'] = null;
            $apidata['ordr_pg_aftersales_buyagain'] = null;
            foreach ($apiRes as $apiRe) {
                foreach ($apiRe as $key => $value) {
                    $apidata[$key] = ($value) ? $value : '-';
                }
            }

            $apidata['ordr_pg_esat_logo'] = $this->validateImage($apidata['ordr_pg_esat_logo']);
            $apidata['ordr_pg_icon_aftersales'] = $this->_path . 'views/img/after_sales_icon.png';
            $apidata['ordr_pg_icon_checkout'] = $this->_path . 'views/img/checkout_icon.png';
            $apidata['ordr_pg_banner_link'] = $this->validateImage($apidata['ordr_pg_banner_link']);
        }
        if (isset($apidata['ordr_pg_aftersales_recommend']) && !empty($apidata['ordr_pg_aftersales_recommend']) &&
                ($apidata['ordr_pg_aftersales_recommend'] != '-')) {
            $apidata['ordr_pg_recommend'] = $apidata['ordr_pg_aftersales_recommend'];
        } elseif (isset($apidata['ordr_pg_checkout_recommend']) && !empty($apidata['ordr_pg_checkout_recommend']) &&
                ($apidata['ordr_pg_checkout_recommend'] != '-')) {
            $apidata['ordr_pg_recommend'] = $apidata['ordr_pg_checkout_recommend'];
        } else {
            $apidata['ordr_pg_recommend'] = '-';
        }
        if ($errors === true) {
            $errors = $this->l('There are no e-satisfaction data for this order.');
        }
        $this->context->smarty->assign(array(
            'email_customer' => $this->context->customer->email,
            'apidata' => $apidata,
            'errors' => $errors,
            'confirmation' => $confirmation,
            'id_site' => (int) $this->id_shop,
            'newVersion' => $newVersion,
        ));

        return $this->display(__FILE__, 'adminorder.tpl');
    }

    /**
     * Hook για την συμβατότητα με παλιότερες εκδόσεις.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @param array $params
     * @return string
     */
    public function hookDisplayAdminOrder($params)
    {
        return $this->hookDisplayAdminOrderLeft($params);
    }

    /**
     * Εμφάνιση στατιστικών που λαμβάνονται από το
     * API στην καρτέλα του customer στο backoffice.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @param array $params
     * @return string
     */
    public function hookDisplayAdminCustomers($params)
    {
        $id_customer = (int) $params['id_customer'];
        $customerOrders = Order::getCustomerOrders($id_customer);
        $apiRes = null;
        $ordersData = array();
        if ($customerOrders) {
            foreach ($customerOrders as $order) {
                $id_order = (int) $order['id_order'];
                $date = $order['date_add'];
                $url = 'https://' . $this->www . '.e-satisfaction.gr/api/v2/prestashop/customer_page_details/';
                $url .= (int) $this->id_shop . '?order_id=' . $id_order;
                if ($apiRes = $this->makeApiCall($url)) {
                    if ($apiRes = Tools::jsonDecode($apiRes)) {
                        if (isset($apiRes->error)) {
                            continue;
                        }
                        $ordersData[] = array(
                            'id_order' => $id_order,
                            '_empty_' => $apiRes[0]->_empty_,
                            'cstmer_pg_comment' => $apiRes[0]->cstmer_pg_comment,
                            'cstmer_pg_generalsatisf' => $apiRes[0]->cstmer_pg_generalsatisf,
                            'cstmer_pg_scheduled' => $apiRes[0]->cstmer_pg_scheduled,
                            'cstmer_pg_nps' => $apiRes[0]->cstmer_pg_nps,
                            'date' => $date,
                            'cstmr_pg_esat_logo' => $apiRes[1]->cstmr_pg_esat_logo,
                            'cstmr_pg_esat_custom_icon' => $apiRes[2]->cstmr_pg_esat_custom_icon,
                            'cstmr_pg_banner' => $apiRes[3]->cstmr_pg_banner
                        );
                    }
                }
            }
        }
        $this->context->smarty->assign(array(
            'ordersData' => $ordersData,
        ));

        return $this->display(__FILE__, 'admincustomer.tpl');
    }

    /**
     * Γίνεται η κλήση στο API.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @param string $url
     * @return mixed
     */
    public function makeApiCall($url)
    {
        $public_key = Configuration::get('ESATISFACTION_PUBLIC_KEY');
        $private_key = Configuration::get('ESATISFACTION_PRIVATE_KEY');
        $timeStamp = microtime(true);
        $string = $public_key . $timeStamp . 'GET';
        $hash_hmac = 'hash_hmac';
        $hash = $hash_hmac('sha256', $string, $private_key, true);
        $base64_encode = 'base64_encode';
        $hashBase64 = $base64_encode($hash);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $headers = array();
        $headers[] = 'X-HASH: ' . $hashBase64;
        $headers[] = 'X-Microtime: ' . $timeStamp;
        $headers[] = 'X-Public: ' . $public_key;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        return curl_exec($ch);
    }

    /**
     * Στέλνει τη διαγραμμένη παραγγελία στο API.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @param array $params
     * @return void
     */
    public function hookActionOrderStatusPostUpdate($params)
    {
        if (isset($params['newOrderStatus']->template) && $params['newOrderStatus']->template == 'order_canceled') {
            $id_order = (int) $params['id_order'];
            $url = 'https://' . $this->www . '.e-satisfaction.gr/api/v2/prestashop/delete_aftersales_mail/';
            $url .= (int) $this->id_shop . '?order_id=' . $id_order;
            $this->makeApiCall($url);
        }
    }

    /**
     * Ελέγχει εάν η επιστροφή από το API είναι πραγματική
     * εικόνα και σε αντίθετη περίπτωση, επιστρέφει
     * την default εικόνα που έχει οριστεί στο class.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @param string$image
     * @return string
     */
    private function validateImage($image)
    {
        if (empty($image)) {
            return;
        }
        if ($imgArray = explode('.', $image)) {
            $imgArray = array_reverse($imgArray);
            $validateImageFormats = array('jpg', 'png', 'JPG', 'PNG');
            if (isset($imgArray[0]) && in_array($imgArray[0], $validateImageFormats)) {
                return $image;
            }
        }

        return $this->defaultImage;
    }

    /**
     * Προσθέτει το απαραίτητο CSS στο backoffice.
     *
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @return void
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (method_exists($this->context->controller, 'addCSS')) {
            $this->context->controller->addCSS($this->_path . 'views/css/esatisfaction.css', 'all');
        }
    }
}
