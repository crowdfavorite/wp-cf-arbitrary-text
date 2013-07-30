<?php
/**
 * Template for the admin post edit meta box
 */

// Fix issue with package names containing '&' not selecting correctly
$options['name'] = str_replace('&', '&amp;', $options['name']);
?>
<p>Enable package: <input type="checkbox" name="cf-arbitrary-text-post[enable]" <?php checked($enabled) ?> value="1"></p>
<?php if (!empty($packages)) { ?>
<p>Select the Package you want to add to this post</p>
<select name="cf-arbitrary-text-post[name]">
<?php

	foreach ((array)$packages as $name => $package) {
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
<?php if (!$enabled && !empty($auto_package)) { ?>
<p>Auto-enabled package: <b><?php echo esc_html($auto_package); ?></b><br/>
Disable auto-enable: <input type="checkbox" name="cf-arbitrary-text-post[auto-disable]" <?php checked($auto_disabled) ?> value="1"></p>
<?php }
}
else {
?>
<p>There are currently no packages available for your site.</p>
<?php 
} 

$override = get_post_meta( get_the_ID(), '_cf-arbitrary-text-paragraph-override', true);
?>

Bottom paragraph override. (Blank for none.)<br>Global setting: <b><?php echo get_option( '_cfat_bottom_paragraphs') == false ? 0 : get_option( '_cfat_bottom_paragraphs'); ?></b> 
<input type="text" name="cf-arbitrary-text-paragraph-override" value="<?php echo $override; ?>" size="3">
