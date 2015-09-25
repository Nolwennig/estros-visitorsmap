<?php

if (isset($_POST['visitorsmap_prefs']))
{
	update_option('visitorsmap_map', $_POST['visitorsmap_map']);	
}
?>
<h3>Preferences</h3>
<!--
<form name="visitorsmap" method="POST" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
  <strong>visitorsmap_map:</strong><br />
  <input type="text" value="<?php echo esc_attr( get_option('visitorsmap_map') ); ?>" id="visitorsmap_map" name="visitorsmap_map" ><br />
  <div style="font-size: 9px;">Seperate tags with commas ','. (Currently only up to 5 tags can be transmitted but you can add as many as you like.)</div>
	<input type="hidden" value="YES" id="visitorsmap_prefs" name="visitorsmap_prefs" >
  <?php submit_button("Save Preferences"); ?>
  <br />
</form>
-->