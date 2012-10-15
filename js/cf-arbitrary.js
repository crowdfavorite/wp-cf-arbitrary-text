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
	//	e.preventDefault();

		return confirm('This will permanently remove this package. Are you sure?');

	});



});

function cfat_add_zone() {

	var new_zone = jQuery('#cfat-placeholder').clone();
	var html = new_zone.html();

	html = html.replace(/\[xxx\]/g, '[' + ++cfarbitrary.zones + ']');

	jQuery('#cfat-placeholder').before(html);

}
