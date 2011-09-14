<?php

function iuwpcas_logout_options() {
	
?>

<!-- options for cas logout settings -->
<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2>IU Wordpress CAS Admin</h2>
	<form action="options.php" method="post">
		<fieldset>CAS Logout Options
			<?php settings_fields('iucas-options');?>
			<h3>Post-logout Preferences</h3>
			<p>When the user chooses to logout, would you like them to be logged out of just this website,
				or to also be logged out of the Central Authentication Service (CAS)?</p>
			<ul>
				<li>
					<input 
						type="radio" 
						name="logout_type" 
						value="site"
						id="r_site" 
						<?php checked('site', get_option('logout_type'));?> />
					<label for="r_site">Site Only</label>
				</li>
				<li>
					<input 
						type="radio" 
						name="logout_type" 
						value="cas"
						id="r_cas" 
						<?php checked('cas', get_option('logout_type'));?> />
					<label for="r_cas">Site &amp; CAS</label>	
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