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
 *  @copyright 2018 e-satisfaction SA
 *  @license   https://opensource.org/licenses
 *}
<!-- E-sat order confirmation code -->
{literal}
<script>
    Esat.updateMetadata("{/literal}{$checkout_quest_id|escape:'htmlall':'UTF-8'}{literal}", {
        responder: {
            "email": "{/literal}{$customer_email|escape:'htmlall':'UTF-8'}{literal}",
            "phone_number": "{/literal}{$customer_phone|escape:'htmlall':'UTF-8'}{literal}"
        },
        questionnaire: {
            "transaction_id": "{/literal}{$order_id|escape:'htmlall':'UTF-8'}{literal}",
            "transaction_date": "{/literal}{$order_date|escape:'htmlall':'UTF-8'}{literal}",
            "store_pickup": {/literal}{$is_store_pickup|escape:'htmlall':'UTF-8'}{literal}
        }
    });
</script>
{/literal}
<div id="esat-checkout-questionnaire-container"></div>
<!-- /E-sat confirmation code -->