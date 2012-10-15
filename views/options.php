<?php if ($message) : ?>
<div class="updated">
	<p><?php echo $message; ?></p>
</div>
<?php endif; ?>

<div class="wrap">
	<h2>CF Arbitrary Text</h2>

	<form action="" method="post">
	<?php settings_fields('cf-arbitrary-text-options'); ?>
	<input type="hidden" name="cfat_action" value="save_post_types">

	<h3>Activate Post Types</h3>

	<p>Check the post types where you want the option of adding arbitrary text.</p>

	<?php foreach ($post_types as $post_type) { ?>
	<input id="cf-arbitrary-text-post-type-<?php echo esc_attr($post_type); ?>" 
		type="checkbox" value="1" name="cf-arbitrary-text[post_type][<?php echo esc_attr($post_type); ?>]"
		<?php if ($options['post_type'][esc_attr($post_type)] == 1) {
			?>checked="checked"<?php
		} ?>
		> 
		<label for="cf-arbitrary-text-post-type-<?php echo esc_attr($post_type); ?>"><?php echo esc_html($post_type); ?></label> &nbsp;
	<?php } ?>

	<?php submit_button(); ?>
		 
	</form>

	<?php if (is_array($packages) && count($packages) > 0) : ?>
		<table class="widefat form-table">
			<thead valign="top">
				<th scope="row">Packages</th>
				<th scope="row">Zones</th>
				<th scope="row">&nbsp;</th>
			</thead>
			<tbody>
			<?php foreach ($packages as $name => $zones) : ?>
				<tr valign="top">
					<td>
						<?php echo $name; ?>
					</td>
					<td>
						<?php echo count($zones); ?>
					</td>
					<td>
						<a href="options-general.php?page=cf-arbitrary-text-add-package&amp;cfat_action=edit&amp;package=<?php echo urlencode(esc_attr($name)); ?>" class="button-secondary">Edit</a> &nbsp;
						<a href="options-general.php?page=cf-arbitrary-text-add-package&amp;cfat_action=delete&amp;package=<?php echo urlencode(esc_attr($name)); ?>" class="delete-button button-secondary">Delete</a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php else: ?>
		<p>There currently are no packages.</p>
	<?php endif; ?>

	<p class="submit">
		<a id="submit" class="button-primary" href="options-general.php?page=cf-arbitrary-text-add-package">Add a Package</a>
	</p>


</div>