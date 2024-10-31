<div class="wrap">
	<h1>Plagibot Settings</h1>

	<form method="post" action="options.php" novalidate="novalidate">
		<?php wp_nonce_field( 'wppbpc_nounce_action', 'wppbpc_setup_nonce_field' );  ?>
		<table class="form-table" role="presentation">
			<tbody>

				<tr>
					<th scope="row"><label for="plagibot_key">API Key</label></th>
					<td>
						<input name="plagibot_key" type="text" id="plagibot_key" required value="<?php echo esc_attr($this->options['api_key']);?>" class="regular-text">
						<p class="description" id="home-description"><a target="_blank" href="https://plagibot.com/app/home">Get one for free</a></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="plagibot_post_type">Post Types</label></th>
					<td>
						<?php 

							$selected_post_types = @$this->options['post_types']?: array(); 

							$post_args = array(
									'public'   => true,
									'_builtin' => false,
							);
							$availableCustomPostTypes = get_post_types($post_args);
							$availableCustomPostTypes['post'] = 'post';
							$availableCustomPostTypes['page'] = 'page';
						?>
						<select name="plagibot_post_type[]" id="plagibot_post_type" class="chosen-select regular-text" multiple required>


							<?php foreach($availableCustomPostTypes as $pt){ ?>
								
								<option  value="<?php echo esc_attr($pt); ?>" <?php if( in_array($pt, $selected_post_types) ) { ?> selected <?php } ?> ><?php echo esc_attr($pt); ?></option>

							<?php } ?>		

														
						</select>						
					</td>
				</tr>

			</tbody>
		</table>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
	</form>

</div>
<script>
	jQuery(".chosen-select").chosen();
</script>