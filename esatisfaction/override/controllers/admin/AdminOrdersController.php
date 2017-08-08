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
 * @version 0.1.7
 * @author    e-satisfaction SA
 * @copyright 2016 e-satisfaction SA
 * @license   https://opensource.org/licenses
 */

class AdminOrdersController extends AdminOrdersControllerCore
{
    /**
     * Ο constructor του class
     * 
     * @author    e-satisfaction SA
     * @copyright 2016 e-satisfaction SA
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $part1 = array_slice($this->fields_list, 0, 1);
        $part2 = array_slice($this->fields_list, 1);
        $part1['esatisfaction'] = array(
            'title' => $this->l('e-satisfaction'),
            'width' => 100,
            'align' => 'center esat',
            'filter_key' => 'false',
            'filter_type' => 'false',
            'order_key' => 'false',
            'callback' => 'getLink'
        );
        $this->fields_list = array_merge($part1, $part2);

        $this->_select .= ",esat.`img` AS esatisfaction, ";

        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'esat_data` esat  ON a.`id_order` = esat.`id_order` ';
    }

    /**
     * Επιστρέφει την αντίστοιχη εικόνα με τον html κώδικα
     * 
     * @author    e-satisfaction SA
     * @copyright 2016 e-satisfaction SA
     * @param string $img
     * @return string
     */
    public function getLink($img)
    {
        if ($img != '--') {
            $this->context->smarty->assign('img', $img);
                $output = $this->context->smarty->fetch(
                    _PS_MODULE_DIR_.'esatisfaction/views/templates/hook/adminorderlist.tpl'
                );
            return $output;
        }
        return "--";
    }
}
