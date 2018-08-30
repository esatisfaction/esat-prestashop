/**
 * NOTICE OF LICENSE.
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    e-satisfaction SA
 * @copyright 2018 e-satisfaction SA
 * @license   https://opensource.org/licenses
 * @version 0.3.0
 */

jQuery(document).ready(function(){
	
	$('input[type=radio][name=manual_send]').change(function() {
		if (this.value == '1') {
			$('#fieldset_3_3').slideDown(400);
			$('#fieldset_4_4').slideDown(400);
		}
		else if (this.value == '0') {
			$('#fieldset_3_3').slideUp(400);
			$('#fieldset_4_4').slideUp(400);
		}
	});
	
	if ($('input[type=radio][name=manual_send]').val() = '1'){
		$('#fieldset_3_3').slideDown(400);
		$('#fieldset_4_4').slideDown(400);
	}else{
		$('#fieldset_3_3').slideUp(400);
		$('#fieldset_4_4').slideUp(400);
	}
});
