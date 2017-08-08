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
 *  @author    e-satisfaction SA
 *  @copyright 2016 e-satisfaction SA
 *  @license   https://opensource.org/licenses
 */

require_once dirname(__FILE__) . '/../../config/config.inc.php';
require_once dirname(__FILE__) . '/../../init.php';
require_once dirname(__FILE__) . '/esatisfaction.php';
if (Tools::encrypt('esatmodule') != Tools::getValue('token')) {
    die('Bad token');
}
header('Content-Type: application/vnd.ms-excel');
header('Content-disposition: attachment; filename=e-satisfaction.xls');
$question_id = filter_input(INPUT_POST, 'question_id', FILTER_SANITIZE_NUMBER_INT);
$stage = filter_input(INPUT_POST, 'stage', FILTER_SANITIZE_STRING);
switch ($stage) {
    case 'Browse':
        $stage = 'miniform_custom_answers';
        break;
    case 'Checkout':
        $stage = 'custom_checkout_answers_text';
        break;
}
$esatisfactionModule = new eSatisfaction();
$url = 'https://' . $esatisfactionModule->www . '.e-satisfaction.gr/api/v2/prestashop/exported_question_section/';
$url .= (int) $esatisfactionModule->id_shop . '?quest_id=' . (int) $question_id . '&stage=' . $stage;
if ($res = $esatisfactionModule->makeApiCall($url)) {
    $res = Tools::jsonDecode($res);
}
echo 'Question ID' . "\t" . 'Answer' . "\t" . 'Completed Date' . "\n";
foreach ($res as $line) {
    $questionId = $line->customquest_pg_qanswers_qid;
    $answer = $line->customquest_pg_qanswers_answer;
    $completed_date = $line->customquest_pg_qanswers_qdate;
    echo $questionId . "\t" . $answer . "\t" . $completed_date . "\n";
}
