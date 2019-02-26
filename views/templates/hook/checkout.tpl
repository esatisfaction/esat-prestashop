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
    Esat.updateMetadata("{/literal}{$checkout_quest_id}{literal}", {
        responder: {
            "email": "{/literal}{$customer_email}{literal}",
            "phone_number": "{/literal}{$customer_phone}{literal}"
        },
        questionnaire: {
            "transaction_id": "{/literal}{$order_id}{literal}",
            "transaction_date": "{/literal}{$order_date}{literal}",
            "store_pickup": {/literal}{$is_store_pickup}{literal}
        }
    });
</script>
{/literal}
<div id="esat-checkout-questionnaire-container"></div>
<!-- /E-sat confirmation code -->