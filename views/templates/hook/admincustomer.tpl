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
{if $ordersData && !empty($ordersData)}
    <div id="eSatisfactionPanel" class="panel">
        <div class="panel-heading">
            <i class="icon"><img class="ordr_pg_esat_logo" src="/modules/esatisfaction/views/img/esat_16x26.png" alt="{l s='e-satisfaction' mod='esatisfaction'}" width="20" height="20"/></i>
                {l s='e-satisfaction' mod='esatisfaction'}
            <span class="badge"></span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th></th>
                        <th class="center"><span class="title_box">{l s='Order Id' mod='esatisfaction'}</span></th>
                        <th class="center"><span class="title_box">{l s='General Satist' mod='esatisfaction'}</span></th>
                        <th class="center"><span class="title_box">{l s='NPS' mod='esatisfaction'}</span></th>
                        <th class="center"><span class="title_box">{l s='Comment' mod='esatisfaction'}</span></th>
                        <th class="center"><span class="title_box">{l s='Scheduled' mod='esatisfaction'}</span></th>
                        <th class="center"><span class="title_box">{l s='Date' mod='esatisfaction'}</span></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$ordersData item=api}
                        <tr class="clickable" data-id_order="{$api.id_order|escape:'htmlall':'UTF-8'}">
                            <td class="center"><img src="{$api.cstmr_pg_esat_custom_icon|escape:'htmlall':'UTF-8'}" alt=""/></td>
                            <td class="center">{$api.id_order|escape:'htmlall':'UTF-8'}</td>
                            <td class="center">{$api.cstmer_pg_generalsatisf|escape:'htmlall':'UTF-8'}</td>
                            <td class="center">{$api.cstmer_pg_nps|escape:'htmlall':'UTF-8'}</td>
                            <td class="center">{$api.cstmer_pg_comment|escape:'htmlall':'UTF-8'}</td>
                            <td class="center">{$api.cstmer_pg_scheduled|escape:'htmlall':'UTF-8'}</td>
                            <td class="center">{$api.date|date_format:"%d/%m/%Y"|escape:'htmlall':'UTF-8'}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
        <p>&nbsp;</p>
        <div class="row">
            <div class="col-lg-8">
                {if $api.0.cstmr_pg_banner}
                    <a href="https://www.e-satisfaction.gr/">
                        <img src="{$api.0.cstmr_pg_banner|escape:'htmlall':'UTF-8'}" alt="" class="img-responsive"/>
                    </a>
                {/if}
            </div>
            <div class="col-lg-4">
                <a href="https://www.e-satisfaction.gr/dashboard/" class="btn btn-primary pull-right" target="_blank">{l s='View More at e-satisfaction' mod='esatisfaction'}</a>
            </div>
        </div>
    </div>
    {if isset($api.0.cstmer_pg_client_type) && !empty($api.0.cstmer_pg_client_type)}
        <script>
            $(document).ready(function () {
                var iconClientType = "{$api.0.cstmer_pg_client_type|escape:'javascript':'UTF-8'}";
            {literal}
                    $('#container-customer .panel-heading > a').after('<img src="' + iconClientType + '" />');
            {/literal}
                });
        </script>
    {/if}
    <script>
        $(document).ready(function () {
            $('.clickable').click(function () {
                var id_order = $(this).data('id_order');
                if (id_order)
                    window.location = '{$link->getAdminLink('AdminOrders', true)|escape:'javascript':'UTF-8'}&vieworder&id_order=' + id_order;
            });
        });
    </script>
{/if}