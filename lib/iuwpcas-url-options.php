<?php

function iuwpcas_url_options() {
	
?>

<!-- options for cas url settings -->
<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2>IU Wordpress CAS Admin</h2>
	<form action="options.php" method="post">
		<fieldset>CAS Logout Options
			<?php settings_fields('iucas-options');?>
			<h3>CAS CASSVC Preferences</h3>
			<p>What campus should be used for the CASSVC parameter? <em>(Defaults to "IU")</em></p>
			<ul>
				<li>
					<label for="cassvc">IU CASSVC</label>
					<input 
						type="text" 
						name="cassvc" 
						value="<?php echo get_option('cassvc')?>"
						id="cassvc" 
						/>
					
				</li>
				
			</ul>
			<p>
				<input type="submit" class="button-primary" name="Save"
				value="<?php _e('Save Options');?>"
				id="submitbutton" />
				<a href="https://github.com/mtheoryx/iuwpcas" class="button-secondary" title="help">Help</a>
			</p>
		</fieldset>
	</form>
</div>

<?php
} //close admin options page
?>