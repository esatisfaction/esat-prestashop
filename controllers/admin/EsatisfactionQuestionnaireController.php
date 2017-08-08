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
 * Συγκέντρωση των ερωτηματολογίων. 
 * Επιπρόσθετο tab στο διαχειριστικό.
 *
 * @author    e-satisfaction SA
 * @copyright (c) 2016, e-satisfaction SA
 * @license   https://opensource.org/licenses
 */

class EsatisfactionQuestionnaireController extends AdminController
{
    /**
     * Ο constructor του class
     * 
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @return void
     */
    public function __construct()
    {
        require_once dirname(__FILE__).'/../../esatisfaction.php';
        $this->bootstrap = true;
        $this->context = Context::getContext();
        parent::__construct();
    }
    /**
     * Εμφάνιση των απαντημένων ερωτηματολογίων
     * 
     * @author    e-satisfaction SA
     * @copyright (c) 2016, e-satisfaction SA
     * @return mixed
     */
    public function display()
    {
        $this->errors = '';
        $tpl_path = _PS_MODULE_DIR_.'esatisfaction/views/templates/admin/questionnaire.tpl';
        $esatisfactionModule = new eSatisfaction();
        $apiRes = (array) Tools::jsonDecode($esatisfactionModule->getCustomQuestionApi());
        if (!$apiRes) {
            $this->errors .= $this->l('API response is empty');
        }
        if (isset($apiRes['error'])) {
            $this->errors .= $apiRes['error'];
        }
        if ($this->errors) {
            $apiRes = null;
        }
        $this->context->smarty->assign(array(
            'questionaries' => (array) $apiRes,
            'logo' => $esatisfactionModule->defaultImage,
        ));
        $this->context->smarty->assign(array('content' => $this->context->smarty->fetch($tpl_path)));

        return parent::display();
    }
}
