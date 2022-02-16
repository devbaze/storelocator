<div>
  <form method="post" action="options.php" enctype="multipart/form-data">
    <?php settings_fields( 'fws_options_group' ); 
    $fws_map_default_radius = get_option('fws_map_default_radius');
    $fws_map_type = get_option('fws_map_type');
    
    ?>
    <h1>Store Locator Settings</h1> 
    <table class="form-table" role="presentation">
    <tr>
    <th scope="row"><label for="fws_map_api_key">Google Map API Key</label></th>
    <td><input type="text" id="fws_map_api_key" name="fws_map_api_key" value="<?php echo get_option('fws_map_api_key'); ?>" /></td>
    </tr>
    <tr>
    <th scope="row"><label for="fws_map_default_radius">Google Map Default Radius (In Miles)</label></th>
    <td><input type="text" id="fws_map_default_radius" name="fws_map_default_radius" value="<?php if($fws_map_default_radius) { echo $fws_map_default_radius; } else { echo '20'; } ?>" /></td>
    </tr>
    <th scope="row"><label for="fws_map_type">Google Map Type</label></th>
    <td><select id="fws_map_type" name="fws_map_type">
    <option value="">Select Option</option>
    <option value="roadmap" <?php if($fws_map_type == 'roadmap') { echo 'selected="selected"'; } ?>>Roadmap</option>
    <option value="satellite" <?php if($fws_map_type == 'satellite') { echo 'selected="selected"'; } ?>>Satellite</option>
    <option value="hybrid" <?php if($fws_map_type == 'hybrid') { echo 'selected="selected"'; } ?>>Hybrid</option>
    <option value="terrain" <?php if($fws_map_type == 'terrain') { echo 'selected="selected"'; } ?>>Terrain</option>
    </select></td>
    </tr>
    </table>
    <?php  submit_button(); ?>
  </form>
</div>