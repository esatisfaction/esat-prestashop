{*
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    e-satisfaction.com
 *  @copyright 2020, e-satisfaction.com
 *  @license   https://opensource.org/licenses
 *}
<div class="row">
    <div class="col-lg-12">
        <img src="{$icon|escape:'htmlall':'UTF-8'}" alt="{$name|escape:'htmlall':'UTF-8'}">
    </div>
    <div class="col-lg-12">
        <h2>Checkout Questionnaire container</h2>
        <p>On your e-satisfaction.com Questionnaire Distribution (integration tab), set the integration type to
            <b>embedded</b> and the position to <b>#esat-checkout-questionnaire-container</b>.</p>
        <h2>Manually Send After-delivery and Store Pickup</h2>
        <p>By default, e-satisfaction sends the after-delivery questionnaire after 10 days. If you wish to send your questionnaires manually,
            you should turn on the manual send switch and fill in the values accordingly.</p>
        <h2>Authentication Token</h2>
        <p>If you select manual send, you should provide a user authentication token so that we can make API calls to e-satisfaction API.
            To retrieve a user token, go to your
            <a href="https://app.e-satisfaction.com/user/settings/" target="_blank">User Settings</a> and create a new token in the
            <b>Authentication Tokens panel</b>.
            Copy your token and paste it in the proper field here.
        </p>
    </div>
</div>
