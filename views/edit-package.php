<h2>Edit Package</h2>

<form action="options-general.php?page=cf-arbitrary-text-add-package" method="post" id="zonepackage">
	<?php settings_fields('cf-arbitrary-text-add-package'); ?>
	<input type="hidden" name="cfat_action" value="edit_package_process">
	<input type="hidden" name="package[orig_name]" value="<?php echo esc_attr($edit); ?>" />

	Package Name: <input type="text" name="package[name]" value="<?php echo esc_attr($edit); ?>" class="validate-nonempty">

	<br />

<?php $x = 1;

foreach ($package as $zone) : ?>

	<div>
		<p id="zone[<?php echo $x; ?>]">
			Snippet name: 
			<select name="zones[<?php echo $x; ?>][snippet]">
			<?php foreach ($keys as $key) { ?>
				<option value="<?php echo esc_attr($key); ?>"<?php selected($key == $zone['snippet']) ?>><?php echo esc_html($key); ?></option>
			<?php } ?>
			</select> Position: <input type="text" size="5" class="validate-integer" name="zones[<?php echo $x; ?>][position]" data-validminvalue="1" value="<?php echo esc_attr($zone['position']); ?>">
			Align:
			<select name="zones[<?php echo $x; ?>][align]">
				<option value=""<?php selected(empty($zone['align'])); ?>>None</option>
			<?php foreach ($align_options as $align => $align_label) {
				?>
				<option value="<?php echo esc_attr($align); ?>"<?php selected($align == $zone['align']); ?>><?php echo esc_html($align_label); ?></option>
			<?php } ?>
			</select> Margin: <input type="text" name="zones[<?php echo $x; ?>][margin]" size="5" value="<?php echo esc_attr($zone['margin']); ?>"/>
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
			</select> Position: <input type="text" size="5" name="zones[xxx][position]" data-validminvalue="1">
			Align:
			<select name="zones[xxx][align]">
				<option value=""<?php selected(empty($zone['align'])); ?>>None</option>
			<?php foreach ($align_options as $align => $align_label) {
				?>
				<option value="<?php echo esc_attr($align); ?>"><?php echo esc_html($align_label); ?></option>
			<?php } ?>
			</select> Margin: <input type="text" name="zones[xxx][margin]" size="5" />
			<span class="removezone button-secondary">Remove</span>	
		</p>
	</div>

	<p class="submit">
		<span id="addzone" class="button-secondary">Add a Zone</span>
	</p>

	<p>
		<input type="checkbox" name="package[paragraph_limit_display]" id="paragraph_limit_display" value="1" <?php checked(!empty($package_options['paragraph_limit_display'])); ?> />
		<label for="paragraph_limit_display">Limit display of snippets when paragraph count is less than the position number.</label>
	</p>

	<?php submit_button('Save this Package'); ?>
</form>

<style>
#cfat-placeholder {display:none;}
</style>
<script type="text/javascript">
	cfarbitrary.zones = <?php echo count($package); ?>;
</script>
