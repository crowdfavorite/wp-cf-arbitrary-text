<h2>Add a Package</h2>

<form action="" method="post" id="zonepackage">
	<?php settings_fields('cf-arbitrary-text-add-package'); ?>
	<input type="hidden" name="cfat_action" value="add_package">

	Package Name: <input type="text" name="package[name]">

	<br>

	<div id="cfat-placeholder">
		<p id="zone[xxx]">
			Snippet name: 
			<select name="zones[xxx][snippet]">
			<?php foreach ($keys as $key) { ?>
				<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($key); ?></option>
			<?php } ?>
			<select> Position: <input size="5" type="text" name="zones[xxx][position]">
			<span class="removezone button-secondary">Remove</span>	
		</p>
	</div>
	<p class="submit">
		<span id="addzone" class="button-secondary">Add a Zone</span>
	</p>

	<?php submit_button('Add this Package'); ?>
</form>

<style>
#cfat-placeholder {display:none;}
</style>
<script type="text/javascript">
	cfat_add_zone();
</script>