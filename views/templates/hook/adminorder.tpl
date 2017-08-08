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
{if !$newVersion}
    <div class="row">
        <div class="col-lg-12">
        {/if}
        <div id="eSatisfactionPanel" class="panel">
            <div class="panel-heading">
                {if isset($apidata.ordr_pg_esat_logo)}
                    <i class="icon"><img src="{$apidata.ordr_pg_esat_logo|escape:'htmlall':'UTF-8'}" alt="{l s='e-satisfaction' mod='esatisfaction'}" width="20" height="20"/></i>
                    {/if}
                    {l s='e-satisfaction' mod='esatisfaction'}
                <span class="badge"></span>
            </div>
            {if $errors}
                <p class="alert alert-warning">{$errors|escape:'htmlall':'UTF-8'}</p>
            {else if $apidata}
                {if $confirmation}
                    <p class="alert alert-success">{$confirmation|escape:'htmlall':'UTF-8'}</p>
                {/if}
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th colspan="4">{l s='Basic informations & General satisfaction' mod='esatisfaction'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="center">{l s='Checkout' mod='esatisfaction'}</td>
                                <td class="center">{l s='After Sales' mod='esatisfaction'}</td>
                                <td class="center">{l s='Recommendation' mod='esatisfaction'}</td>
                                <td class="center">{l s='Would buy again?' mod='esatisfaction'}</td>
                            </tr>
                            <tr>
                                <td class="center"><input type="text" class="form-control fixed-width-sm" value="{if $apidata.ordr_pg_general_satisf_checkout}{$apidata.ordr_pg_general_satisf_checkout|escape:'htmlall':'UTF-8'}{else}-{/if}" disabled="disabled"/></td>
                                <td class="center"><input type="text" class="form-control fixed-width-sm" value="{if $apidata.ordr_pg_general_satisf_aftersales}{$apidata.ordr_pg_general_satisf_aftersales|escape:'htmlall':'UTF-8'}{else}-{/if}" disabled="disabled"/></td>
                                <td class="center">
                                    {if $apidata.ordr_pg_recommend == '-'}
                                        <input type="text" class="form-control fixed-width-sm" value="{$apidata.ordr_pg_recommend|escape:'htmlall':'UTF-8'}" disabled="disabled" />
                                    {else}
                                        <input type="image" class="form-control fixed-width-sm image" src="{$apidata.ordr_pg_recommend|escape:'htmlall':'UTF-8'}" disabled="disabled" />
                                    {/if}
                                </td>
                                <td class="center"><input type="text" class="form-control fixed-width-sm" value="{if $apidata.ordr_pg_buyagain == '1'}{l s='Yes' mod='esatisfaction'}{elseif $apidata.ordr_pg_buyagain == '0'}{l s='No' mod='esatisfaction'}{else}-{/if}"disabled="disabled"/></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p>&nbsp;</p>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            {if $apidata.ordr_pg_checkout_comments_text != '-' && !empty($apidata.ordr_pg_checkout_comments_text)}
                                <tr>
                                    <th colspan="2">{l s='Comments at Checkout Stage:' mod='esatisfaction'}</th>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-group">
                                            <img class="col-lg-1" src="{$apidata.ordr_pg_icon_checkout|escape:'htmlall':'UTF-8'}" alt="{l s='Checkout' mod='esatisfaction'}" />
                                            <div class="col-lg-11">
                                                <textarea class="form-control textarea-autosize" disabled="disabled">{$apidata.ordr_pg_checkout_comments_text|escape:'htmlall':'UTF-8'}</textarea>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-group">
                                            <form action="{$link->getAdminLink('AdminOrders', true)|escape:'htmlall':'UTF-8'}" method="POST">
                                                <label class="control-label col-lg-2">{l s='Reply message:' mod='esatisfaction'}</label>
                                                <div class="col-lg-8">
                                                    <textarea class="form-control textarea-autosize" name="reply_msg"></textarea>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type="submit" class="btn btn-primary pull-right" name="submitMessageFromSatisfaction">{l s='Send message' mod='esatisfaction'}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            {/if}
                            {if $apidata.ordr_pg_aftersales_comments_text != '-' && !empty($apidata.ordr_pg_aftersales_comments_text)}
                                <tr>
                                    <th colspan="2">{l s='Comments at After Sales Stage:' mod='esatisfaction'}</th>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-group">
                                            <img class="col-lg-1" src="{$apidata.ordr_pg_icon_aftersales|escape:'htmlall':'UTF-8'}" alt="{l s='After Sales' mod='esatisfaction'}" />
                                            <div class="col-lg-11">
                                                <textarea class="form-control textarea-autosize" disabled="disabled">{$apidata.ordr_pg_aftersales_comments_text|escape:'htmlall':'UTF-8'}</textarea>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-group">
                                            <form action="{$link->getAdminLink('AdminOrders', true)|escape:'htmlall':'UTF-8'}&amp;id_order={$smarty.get.id_order|escape:'htmlall':'UTF-8'}&amp;vieworder" method="POST">
                                                <label class="control-label col-lg-2">{l s='Reply message:' mod='esatisfaction'}</label>
                                                <div class="col-lg-8">
                                                    <textarea class="form-control textarea-autosize" name="reply_msg"></textarea>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type="submit" class="btn btn-primary pull-right" name="submitMessageFromSatisfaction">{l s='Send message' mod='esatisfaction'}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            {/if}
                        </tbody>
                    </table>
                </div>
                <p>&nbsp;</p>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th colspan="2">{l s='Detailed Information' mod='esatisfaction'}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <ul class="nav nav-tabs" id="tabSatisDetails">
                    <li class="active">
                        <a href="#checkout">
                            <i class="icon"><img src="{$apidata.ordr_pg_icon_checkout|escape:'htmlall':'UTF-8'}" alt="{l s='Checkout' mod='esatisfaction'}" width="38" height="38"/></i>
                                {l s='Checkout' mod='esatisfaction'}
                        </a>
                    </li>
                    <li class="">
                        <a href="#aftersales">
                            <i class="icon"><img src="{$apidata.ordr_pg_icon_aftersales|escape:'htmlall':'UTF-8'}" alt="{l s='After Sales' mod='esatisfaction'}" width="38" height="38"/></i>
                                {l s='After Sales Surveys' mod='esatisfaction'}
                        </a>
                    </li>
                </ul>
                <div class="tab-content panel">
                    <div class="tab-pane active" id="checkout">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-horizontal">
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='General Satisfaction' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            <p class="form-control-static">{if $apidata.ordr_pg_general_satisf_checkout}{$apidata.ordr_pg_general_satisf_checkout|escape:'htmlall':'UTF-8'}{else}-{/if}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='Product Range' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            <p class="form-control-static">{if $apidata.ordr_pg_checkout_prodrange}{$apidata.ordr_pg_checkout_prodrange|escape:'htmlall':'UTF-8'}{else}-{/if}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='Availability' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            <p class="form-control-static">{if $apidata.ordr_pg_checkout_availability}{$apidata.ordr_pg_checkout_availability|escape:'htmlall':'UTF-8'}{else}-{/if}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='Prices' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            <p class="form-control-static">{if $apidata.ordr_pg_checkout_prices}{$apidata.ordr_pg_checkout_prices|escape:'htmlall':'UTF-8'}{else}-{/if}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='Safety' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            <p class="form-control-static">{if $apidata.ordr_pg_checkout_safety}{$apidata.ordr_pg_checkout_safety|escape:'htmlall':'UTF-8'}{else}-{/if}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='Usability' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            <p class="form-control-static">{if $apidata.ordr_pg_checkout_usability}{$apidata.ordr_pg_checkout_usability|escape:'htmlall':'UTF-8'}{else}-{/if}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='Product Presentation' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            <p class="form-control-static">{if $apidata.ordr_pg_checkout_prodpresentation}{$apidata.ordr_pg_checkout_prodpresentation|escape:'htmlall':'UTF-8'}{else}-{/if}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-horizontal">
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='Recommendation' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            {if $apidata.ordr_pg_checkout_recommend=='-'}-{else}<img src="{$apidata.ordr_pg_checkout_recommend|escape:'htmlall':'UTF-8'}" alt="{l s='Recommendation' mod='esatisfaction'}" />{/if}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='Scheduled' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            <p class="form-control-static">{if $apidata.ordr_pg_checkout_scheduled == '1'}{l s='Yes' mod='esatisfaction'}{elseif $apidata.ordr_pg_checkout_scheduled == '0'}{l s='No' mod='esatisfaction'}{else}-{/if}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="aftersales">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-horizontal">
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='General Satisfaction' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            <p class="form-control-static">{if $apidata.ordr_pg_general_satisf_aftersales}{$apidata.ordr_pg_general_satisf_aftersales|escape:'htmlall':'UTF-8'}{else}-{/if}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='Delivery Time' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            <p class="form-control-static">{if $apidata.ordr_pg_aftersales_delivtime}{$apidata.ordr_pg_aftersales_delivtime|escape:'htmlall':'UTF-8'}{else}-{/if}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='Delivery Cost' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            <p class="form-control-static">{if $apidata.ordr_pg_aftersales_delivcost}{$apidata.ordr_pg_aftersales_delivcost|escape:'htmlall':'UTF-8'}{else}-{/if}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='Product Condition' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            <p class="form-control-static">{if $apidata.ordr_pg_aftersales_prodcond}{$apidata.ordr_pg_aftersales_prodcond|escape:'htmlall':'UTF-8'}{else}-{/if}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='Possibility of Receiving' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            <p class="form-control-static">{if $apidata.ordr_pg_aftersales_receive}{$apidata.ordr_pg_aftersales_receive|escape:'htmlall':'UTF-8'}{else}-{/if}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='Product Packaging' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            <p class="form-control-static">{if $apidata.ordr_pg_aftersales_prodbox}{$apidata.ordr_pg_aftersales_prodbox|escape:'htmlall':'UTF-8'}{else}-{/if}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='Store Service' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            <p class="form-control-static">{if $apidata.ordr_pg_aftersales_storeservice}{$apidata.ordr_pg_aftersales_storeservice|escape:'htmlall':'UTF-8'}{else}-{/if}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-horizontal">
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='Recommendation' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            {if $apidata.ordr_pg_aftersales_recommend=='-'}-{else}<img src="{$apidata.ordr_pg_aftersales_recommend|escape:'htmlall':'UTF-8'}" alt="{l s='Recommendation' mod='esatisfaction'}" />{/if}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-lg-6">{l s='Will Buy Again' mod='esatisfaction'}:</label>
                                        <div class="col-lg-6">
                                            <p class="form-control-static">{if $apidata.ordr_pg_aftersales_buyagain == '1'}{l s='Yes' mod='esatisfaction'}{elseif $apidata.ordr_pg_aftersales_buyagain == '0'}{l s='No' mod='esatisfaction'}{else}-{/if}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    {literal}
                        $('#tabSatisDetails a').click(function (e) {
                            e.preventDefault()
                            $(this).tab('show')
                        })
                    {/literal}
                </script>
                <p>&nbsp;</p>
                <div class="row">
                    <div class="col-lg-8">
                        <a href="https://www.e-satisfaction.gr/">
                            <img src="{$apidata.ordr_pg_banner_link|escape:'htmlall':'UTF-8'}" alt="" class="img-responsive"/>
                        </a>
                    </div>
                    <div class="col-lg-4">
                        <a href="https://www.e-satisfaction.gr/dashboard/" class="btn btn-primary" target="_blank">{l s='View More at e-satisfaction' mod='esatisfaction'}</a>
                    </div>
                </div>
            {/if}
        </div>
        {if !$newVersion}
        </div>
    </div>
{/if}