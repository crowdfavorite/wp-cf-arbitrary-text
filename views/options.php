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

		<?php foreach ($post_types as $post_type => $post_label) { ?>
		<input id="cf-arbitrary-text-post-type-<?php echo esc_attr($post_type); ?>"
			type="checkbox" value="1" name="cf-arbitrary-text[post_type][<?php echo esc_attr($post_type); ?>]"
			<?php if ($options['post_type'][esc_attr($post_type)] == 1) {
				?>checked="checked"<?php
			} ?>
			>
			<label for="cf-arbitrary-text-post-type-<?php echo esc_attr($post_type); ?>"><?php echo esc_html($post_label); ?></label> &nbsp;
		<?php } ?>

		<?php submit_button(); ?>
		 
	</form>

	<h3>Manage Packages</h3>

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

	<h3>Post Type Auto-Enable</h3>

	<?php if (is_array($post_auto_enable) && count($post_auto_enable) > 0 ) : ?>

	<table class="widefat form-table">
		<thead valign="top">
			<th scope="row">Post Type</th>
			<th scope="row">Package</th>
			<th scope="row">&nbsp;</th>
		</thead>
		<tbody>
		<?php foreach ($post_auto_enable as $auto_enable_item) : ?>
			<?php if(!empty($auto_enable_item['post_type']) && !empty($auto_enable_item['package'])): ?>
			<tr valign="top">
				<td>
					<?php echo $auto_enable_item['post_type']; ?>
				</td>
				<td>
					<?php echo $auto_enable_item['package']; ?>
				</td>
				<td>
					<a href="options-general.php?page=cf-arbitrary-text&amp;cfat_action=delete_auto_enable&amp;auto_post_type=<?php echo urlencode(esc_attr($auto_enable_item['post_type'])); ?>&amp;package=<?php echo urlencode(esc_attr($auto_enable_item['package'])); ?>" class="auto-delete-button button-secondary">Delete</a>
				</td>
			</tr>
			<?php endif; ?>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php else: ?>
		<p>There currently are no post type auto-add packages.</p>
	<?php endif; ?>

	<br />

	<?php if (is_array($packages) && count($packages) > 0) { ?>

	<p>Select the post type and package to auto-enable:</p>

	<form action="" method="post">
		
	<?php settings_fields('cf-arbitrary-text-options'); ?>
		<input type="hidden" name="cfat_action" value="save_post_types_auto_enable" />
		<p>
			<select name="cf-arbitrary-text[post_type_auto_enable][auto_post_type]">
				<?php
				foreach ($post_types as $post_type => $post_label) {
					if ($options['post_type'][esc_attr($post_type)] == 1) { ?>
				<option value="<?php echo esc_attr($post_type); ?>"><?php echo esc_html($post_label); ?></option>
				<?php
					}
				}
				?>
			</select>

			<select name="cf-arbitrary-text[post_type_auto_enable][package]">
				<?php foreach ($packages as $name => $zones) { ?>
				<option value="<?php echo esc_attr($name); ?>"><?php echo esc_html($name); ?></option>
				<?php } ?>
			</select>

			<?php submit_button('Add Post Type Auto-Enable'); ?>
		</p>
	</form>

	<?php } ?>

	<h3>Short Story Zone Handling</h3>

	<form action="" method="post">
		
		<p>If a package zone is set to display at a paragraph number higher than the number of paragraphs in a post, and the 
			"Limit display of snippets when paragraph count is less than the position number." option is unchecked, this option
			sets the number of paragraphs from the bottom that the zone will display. This setting is universal, but can be 
			overridden on a per-post basis. If nothing is entered below, it will default to "0" which puts the zone at the
			bottom of the post.
		</p>

		<?php 
			settings_fields('cf-arbitrary-text-options'); 
			$bottom_paragraph = get_option( '_cfat_bottom_paragraphs');

			if ($bottom_paragraph === false) {
				$bottom_paragraph = 0;
			}
		?>
		<input type="hidden" name="cfat_action" value="save_paragraph_options">
		<input type="text" name="cfat_bottom_paragraphs" value="<?php echo $bottom_paragraph; ?>">
		<?php submit_button(); ?>
		 
	</form>

</div>
