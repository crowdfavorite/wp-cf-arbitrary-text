<?php
	$checked = '';

	// detect if package is enabled
	if ($options === null) {
		// assign universal default value to check
		$checked = 'checked="checked"';
	} else {
		if ($options['enable'] == 1) {
			$checked = 'checked="checked"';
		}
	}
?>
<p>Enable package: <input type="checkbox" name="cf-arbitrary-text-post[enable]" <?php echo $checked; ?>value="1"></p>

<p>Select the Package you want to add to this post</p>
<select name="cf-arbitrary-text-post[name]">
<?php
	foreach ($packages as $name => $package) {
		echo '<option value="' . esc_attr($name) . '"';
		if ($options['name'] == $name) {
			echo ' selected="selected"';
		}
		echo '>';
		echo $name;
		echo ' &nbsp;</option>';
	}
?>
</select>
