<h2>Edit Package</h2>

<form action="options-general.php?page=cf-arbitrary-text-add-package" method="post" id="zonepackage">
	<?php settings_fields('cf-arbitrary-text-add-package'); ?>
	<input type="hidden" name="cfat_action" value="edit_package_process">

	Package Name: <input type="text" name="package[name]" value="<?php echo esc_attr($edit); ?>">

	<br>

<?php $x=1; 

foreach ($package as $zone) : ?>

	<div>
		<p id="zone[<?php echo $x; ?>]">
			Snippet name: 
			<select name="zones[<?php echo $x; ?>][snippet]">
			<?php foreach ($keys as $key) { ?>
				<option value="<?php echo esc_attr($key); ?>"<?php if ($key == $zone['snippet']) { ?> selected="selected" <?php } ?>><?php echo esc_html($key); ?></option>
			<?php } ?>
			<select> Position: <input type="text" size="5" name="zones[<?php echo $x; ?>][position]" value="<?php echo esc_attr($zone['position']); ?>">
			<span class="removezone button-secondary">Remove</span>	
		</p>
	</div>
<?php $x++;
endforeach; ?>

	<div id="cfat-placeholder">
		<p id="zone[xxx]">
			Snippet name: 
			<select name="zones[xxx][snippet]">
			<?php foreach ($keys as $key) { ?>
				<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($key); ?></option>
			<?php } ?>
			<select> Position: <input type="text" size="5" name="zones[xxx][position]">
			<span class="removezone button-secondary">Remove</span>	
		</p>
	</div>

	<p class="submit">
		<span id="addzone" class="button-secondary">Add a Zone</span>
	</p>

	<?php submit_button('Save this Package'); ?>
</form>

<style>
#cfat-placeholder {display:none;}
</style>
<script type="text/javascript">
	cfarbitrary.zones = <?php echo count($package); ?>;
</script>