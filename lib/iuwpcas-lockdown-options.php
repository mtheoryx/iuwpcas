<?php

function iuwpcas_lockdown_options() {
	
?>

<!-- options for cas url settings -->
<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2>IU Wordpress CAS Admin</h2>
	<form action="options.php" method="post">
		<fieldset>CAS Lockdown Options
			<?php settings_fields('iucas-options');?>
			<h3>Lockdown Preferences</h3>
			<p>This setting determines whether to show the public part of the site to all visitors <em>(default)</em>, or only show it to CAS-Authenticated users.</p>
			<ul>
				<li>
					<input 
						type="radio" 
						name="lockdown" 
						value="false"
						id="lockdown_false" 
						<?php checked('false', get_option('lockdown'));?> />
					<label for="lockdown_false">Anyone can see the site.</label>
				</li>
				<li>
					<input 
						type="radio" 
						name="lockdown" 
						value="true"
						id="lockdown_true" 
						<?php checked('true', get_option('lockdown'));?> />
					<label for="lockdown_true">Only CAS-Authenticated Visitors</label>	
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