cfarbitrary = {
	zones : 0
};

jQuery(document).ready(function() {

	jQuery('body').on("click", '#addzone', function() {
		cfat_add_zone();
	});

	jQuery('body').on("click", '.removezone', function() {

		if (cfarbitrary.zones == 1) {
			alert("You cannot remove the only zone.");
			return false;
		}

		jQuery(this).parent().remove();
	});

	

	jQuery('.delete-button').click(function (e) {
		return confirm('This will permanently remove this package. Are you sure?');
	});

	jQuery('.auto-delete-button').click(function (e) {
		return confirm('This will remove this auto-enable setting. Are you sure?');
	});

	function validateForm(e) {
		var $form = jQuery(this),
		    validationSuccess = true;

		// Validate integer fields
		$form
			.find('input.validate-integer:visible')
				.each(function() {
					var $input = jQuery(this),
					    inputValue = $input.val(),
					    parsedInputValue = parseInt($input.val());
					    minValue = $input.data('validminvalue'),
					    parsedMinValue = parseInt(minValue),
					    maxValue = $input.data('validmaxvalue'),
					    parsedMaxValue = parseInt(maxValue),

					isValid = parsedInputValue == inputValue;
					if (isValid) {
						// Check value range if exists
						if (maxValue == parsedMaxValue) {
							isValid &= parsedMaxValue >= parsedInputValue;
						}
						if (minValue == parsedMinValue) {
							isValid &= parsedMinValue <= parsedInputValue;
						}
					}

					if (!isValid) {
						$input.addClass('validation-failed');
					}
					validationSuccess &= isValid;
				})
				.end()
			.find('input.validate-nonempty:visible')
				.each(function() {
					var $input = jQuery(this),
					    isValid = ($input.val().length > 0);
					if (!isValid) {
						$input.addClass('validation-failed');
					}
					validationSuccess &= isValid;
				})
				.end();

		// Stop submit if validation failed
		if (!validationSuccess) {
			e.stopPropagation();
			e.preventDefault();
			$form
				.find('p#validation-error')
					.html('Validation failed. Please correct highlighted items and submit again.');
		}
	}

	jQuery('form#zonepackage')
		.prepend('<p id="validation-error"></p>')
		.submit(validateForm)
		.find('input')
			.on('keyup change', function() {
				jQuery(this).removeClass('validation-failed');
			});

});

function cfat_add_zone() {

	var new_zone = jQuery('#cfat-placeholder').clone();
	var html = new_zone.html();

	html = html.replace(/\[xxx\]/g, '[' + ++cfarbitrary.zones + ']');

	jQuery('#cfat-placeholder').before(html);

}
