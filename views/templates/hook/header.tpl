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
 *  @copyright 2020 e-satisfaction.com
 *  @license   https://opensource.org/licenses
 *}
<!-- E-sat header code -->
{literal}
<script>
    (function (w, d, id, jq, c) {
        // Define e-satisfaction collection configuration
        w.esat_config = {application_id: id, collection: c || {}};

        // Update metadata
        w.Esat = w.Esat || {};
        w.Esat.updateMetadata = function (q, m) {
            w.esat_config.collection[q] = w.esat_config.collection[q] || {};
            w.esat_config.collection[q].metadata = m;
        };

        // Setup script
        var l = function () {
            var r = d.getElementsByTagName('script')[0], s = d.createElement('script');
            s.async = true;
            s.src = 'https://collection.e-satisfaction.com/dist/js/integration' + (!!jq ? '.jq' : '') + '.min.js';
            r.parentNode.insertBefore(s, r);
        };

        // Attach script or run script if document is loaded
        "complete" === d.readyState ? l() : (w.attachEvent ? w.attachEvent("onload", l) : w.addEventListener("load", l, false));
    })(window, document, "{/literal}{$app_id|escape:'htmlall':'UTF-8'}{literal}", false, {});
</script>
{/literal}
<!-- /E-sat header code -->