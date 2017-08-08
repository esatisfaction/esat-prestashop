{*
 * NOTICE OF LICENSE
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
 *}
<div class="panel" id="EsatisfactionQuestionaire">
    <div class="panel-heading">
        <i><img src="/modules/esatisfaction/views/img/esat_16x26.png"/></i>{l s='e-satisfaction Custom Questions' mod='esatisfaction'}
    </div>
    {if $questionaries}
        <table class="table">
            <thead>
                <tr>
                    <th>{l s='Question Status' mod='esatisfaction'}</th>
                    <th>{l s='Question Title' mod='esatisfaction'}</th>
                    <th>{l s='Question Stage' mod='esatisfaction'}</th>
                    <th>{l s='Number of Answers' mod='esatisfaction'}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$questionaries item=questionarie}
                    <tr>
                        <td class="status">{if $questionarie->customquest_pg_status == '1'}<span class="alert-success">{l s='Active' mod='esatisfaction'}</span>{else}<span class="alert-danger">{l s='Inactive' mod='esatisfaction'}</span>{/if}</td>
                        <td>{$questionarie->customquest_pg_qtitle|escape:'htmlall':'UTF-8'}</td>
                        <td>{$questionarie->customquest_pg_qstage|escape:'htmlall':'UTF-8'}</td>
                        <td>{$questionarie->customquest_pg_qanswersnumber|escape:'htmlall':'UTF-8'}</td>
                        <td>
                            <form action="../modules/esatisfaction/genExcel.php" name="export" id="export_custom" method="post">
                                <input type="hidden" name="question_id" value="{$questionarie->customquest_pg_qid|escape:'htmlall':'UTF-8'}">
                                <input type="hidden" name="stage" value="{$questionarie->customquest_pg_qstage|escape:'htmlall':'UTF-8'}">
                                <button type="submit" class="btn btn-medium table-export">
                                    <span class="glyphicon xls"></span> {l s='Export to Excel' mod='esatisfaction'}
                                </button>
                            </form>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    {/if}
</div>