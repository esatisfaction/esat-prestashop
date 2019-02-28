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
<!-- E-sat header code -->
{literal}
<script>
    (function (win, doc, application_id, jq, collection) {
        // Define e-satisfaction collection configuration
        win.esat_config = {application_id: application_id, collection: collection || {}};

        // Update metadata
        win.Esat = win.Esat || {};
        win.Esat.updateMetadata = function (questionnaireId, metadata) {
            win.esat_config.collection[questionnaireId] = win.esat_config.collection[questionnaireId] || {};
            win.esat_config.collection[questionnaireId].metadata = metadata;
        };

        // Setup script
        doc.addEventListener('DOMContentLoaded', function () {
            var body = doc.getElementsByTagName('body')[0], script = doc.createElement('script');
            script.async = true;
            script.src = 'https://collection.e-satisfaction.com/dist/js/integration' + (!!jq ? '' : '.jq') + '.min.js';
            body.appendChild(script);
        });
    })(window, document, "{/literal}{$app_id}{literal}", false, {});
</script>
{/literal}
<!-- /E-sat header code -->