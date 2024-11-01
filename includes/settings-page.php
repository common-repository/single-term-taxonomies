<div class="wrap stt-settings">
	<div class="icon32"><br /></div>
	<h2><?php echo $this->get_plugin_name() ?></h2>

	<div>
		<h3><?php _e('Choose which taxonomies should be single-term:', 'stt') ?></h3>

		<?php
			$taxonomies = $this->get_taxonomies();
			$settings = $this->get_single_term_taxonomies();
		?>

		<form action="" method="POST">

			<?php wp_nonce_field( $this->get_nonce_action(), '_stt_settings_save_nonce' ) ?>
			
			<?php foreach ($taxonomies as $tax_slug => $tax_obj) : ?>
				<p>
					<label>
						<input type="checkbox" name="single_term_taxonomies[<?php echo $tax_slug ?>]" <?php echo in_array($tax_slug, $settings) ? 'checked' : '' ?> />
						<span><?php echo $tax_obj->labels->singular_name ?> (<?php echo $tax_slug ?>)</span>
					</label>
				</p>
			<?php endforeach ?>
			
			<p>
				<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'stt') ?>">
			</p>

		</form>
	</div>
</div>