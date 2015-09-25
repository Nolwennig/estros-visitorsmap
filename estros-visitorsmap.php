<?php
/**
 * Plugin Name: Visitors to MAP Plugin
 * Plugin URI: http://estros.gr
 * Description: Shows all your visitors in a map based on their ids.
 * Version: 1.0
 * Author: Estros.gr
 * Author URI: http://estros.gr
 * License: GPL2
 */

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

function visitorsmapform($atts, $content){
  $buttontext = "Sign me up!";
  if(!empty($content))
    $buttontext = $content;
  extract( shortcode_atts( array(
    'class' => 'visitorsmap-button',
  ), $atts, 'estros-visitorsmap' ) );
  ?>
  <?php 
	include 'frontend-form.php'; 
}

function visitorsmapwptuts_scripts_important()
{
  wp_enqueue_script('jquery');
  wp_register_style( 'visitorsmapStyle', plugins_url( '/css/visitorsmap.css', __FILE__ ), array(), '20120208', 'all' );
  wp_enqueue_style( 'visitorsmapStyle' );
}
add_action('admin_menu', 'visitorsmap_admin');
add_action( 'admin_enqueue_scripts', 'visitorsmap_load_admin_style' );

function visitorsmap_load_admin_style() {
  wp_register_style( 'admin_css', plugins_url( '/css/visitorsmap.css', __FILE__ ), false, '1.0.0' );
  wp_enqueue_script('jquery');
 }
 
function visitorsmap_admin(){
  add_menu_page( 'Visitors to Map', 'VMAP', 'administrator', 'estros-visitorsmap', 'visitorsmap_init', 'dashicons-admin-site' );
}
 
function visitorsmap_init(){
  ?>
  <div class="wrap">
    <h2>Guests to MAP Plugin</h2>
    <div style="padding:5px; display: inline-block; width:42%;">   
      <div class="welcome-panel">
        <a href="https://estros.gr" target="_blank">
          <img src="http://estros.gr/logo.png" height="45">
        </a>
        <div style="width:100%; font-size: 18px; font-weight: bold;">Plugin Description</div>
        <p>Simple visitor tracking wordpress plugin that presents in an openstreetmap the visitors of your website.</p>
      </div>
     
      <div class="welcome-panel">
        <?php
        if($_GET['settings-updated'])
        {
        ?>
          <div style="font-size: 12px; border: 2px #1EA1CC solid;padding:2px;background-color: #2EA2CC; color: white;">Your settings have been saved</div><br>
        <?php
        }
        ?>
        <?php include 'estros-visitorsmap-admin-api-settings.php'; ?>
      </div>
  	</div>
    <div class="welcome-panel" style="vertical-align: top; width: 53%; display: inline-block;">
      <h2>How to use the shortcode</h2>
      <p>Add the shortcode to any page/post/product you want to track!.</p>
      <p>
        <code>
        	[estros-visitorsmap][/estros-visitorsmap]
        </code>
      </p>
			<style>  
        #visitorsmapmymapdiv
        {
          width:100%;
          height:600px;
        }
      </style>
      
      <p>
      <div id="visitorsmapmymapdiv"></div>
      <script src="http://www.openlayers.org/api/OpenLayers.js"></script>
      
      <script type="application/javascript">
       map = new OpenLayers.Map("visitorsmapmymapdiv");
  
			//map.addControl(new OpenLayers.Control.PanZoomBar());
			map.addControl(new OpenLayers.Control.LayerSwitcher({'ascending':false}));
			map.addControl(new OpenLayers.Control.MousePosition());
			map.addControl(new OpenLayers.Control.OverviewMap());
			map.addControl(new OpenLayers.Control.KeyboardDefaults());
			
			map.addLayer(new OpenLayers.Layer.OSM());
				
			epsg4326 =  new OpenLayers.Projection("EPSG:4326"); //WGS 1984 projection
			projectTo = map.getProjectionObject(); //The map projection (Spherical Mercator)
			var lonLat = new OpenLayers.LonLat(37.583000, 55.750000).transform(epsg4326, projectTo);
			var zoom=0;
			var vectorLayer;
			map.setCenter (lonLat, zoom);
			vectorLayer = new OpenLayers.Layer.Vector("ips", "", "ips");
			var feature;
			<?php
			
			function defineSize($visits)
			{	
				if($visits > 0 && $visits < 80)
					return array(25, 0.5);
				elseif($visits >=80 && $visits < 300)
					return array(30, 0.7);
				elseif($visits >=300 && $visits < 500)
					return array(35, 0.8);
				elseif($visits >=500 && $visits < 800)
					return array(40, 0.9);
				else
					return array(45, 1);
			}
			
			$fileuri = WP_PLUGIN_DIR.'/estros-visitorsmap/ips/ips.xml'; 
			$xmlfile = file_get_contents($fileuri) or die('Could not load file!');
			$xml = simplexml_load_string($xmlfile);
			foreach($xml->children() as $key => $value)  
			{
				$icon = defineSize($value->visits);
				$iconheight = $iconwidth = $icon[0];
				$iconopacity = $icon[1];
				
				echo "feature = new OpenLayers.Feature.Vector(";
				echo "new OpenLayers.Geometry.Point(".$value->longitude.",".$value->latitude.").transform(epsg4326, projectTo),
				{description:'<strong>".$value->city."</strong><br><strong>Country:</strong> ".$value->country."<br><strong>Population:</strong>".$value->population."<br><strong>Visits:</strong> ".$value->visits."'},
				{
					externalGraphic: '".plugins_url('/images/marker.png', __FILE__ )."',
					graphicWidth: $iconwidth,
					fillOpacity: $iconopacity
				}
				); ";  
				echo "vectorLayer.addFeatures(feature);";
				echo "map.addLayer(vectorLayer);";
			}
			
			?>
			
			//Add a selector control to the vectorLayer with popup functions
			var controls = {
				selector: new OpenLayers.Control.SelectFeature(vectorLayer, { onSelect: createPopup, onUnselect: destroyPopup })
			};
		
			function createPopup(feature) {
				feature.popup = new OpenLayers.Popup.FramedCloud("pop",
						feature.geometry.getBounds().getCenterLonLat(),
						null,
						'<div class="markerContent">'+feature.attributes.description+'</div>',
						null,
						true,
						function() { controls['selector'].unselectAll(); }
				);
				//feature.popup.closeOnMove = true;
				map.addPopup(feature.popup);
			}
		
			function destroyPopup(feature) {
				feature.popup.destroy();
				feature.popup = null;
			}
			
			map.addControl(controls['selector']);
			controls['selector'].activate();
      </script>
      <h3 style="margin-top:20px;">Statistics</h3>
      <?php 
				function stats($array) 
				{
					$temp = array();
					$check = array();
					for($i = 0; $i < sizeof($array); $i++)
					{
						if(!in_array($array[$i]['city'], $check))
						{
							$temp[] = array($array[$i]['value'], $array[$i]['city'], 1);
							$check[] = $array[$i]['city'];
							//echo $array[$i]['value']."-".$array[$i]['city']."<br>";
						}
						else
						{
							//echo $array[$i]['value']."-".$array[$i]['city']."<br>";
							for($j = 0; $j < sizeof($temp); $j++)
							{
								if($temp[$j][1] == $array[$i]['city'])
								{
									$temp[$j][2]++;
								}
							}
						}
					}
					return $temp;
				}
				
				$xmlstats = simplexml_load_string($xmlfile, "SimpleXMLElement", LIBXML_NOCDATA);
				$jsonstats = json_encode($xmlstats);
				$arraystats = json_decode($jsonstats, TRUE);
				
				$pageviews = 0;
				for($i = 0; $i < sizeof($arraystats['ip']); $i++)
				{
					$pageviews += $arraystats['ip'][$i]['visits'];
				}
				?>
        <table style="width:100%">
        	<tr>
          	<td><?php echo "<strong>Page Views:</strong> ".$pageviews; ?></td>
            <td></td>
          </tr>
        	<tr>
          	<td><?php echo "<strong>Cities (".sizeof($arraystats['ip'])."):</strong>"; ?></td>
            <td>
            	<?php
            	for($i = 0; $i < sizeof($arraystats['ip']); $i++)
              {
                echo $arraystats['ip'][$i]['city']." (".$arraystats['ip'][$i]['visits'].")<br>"; //city
                
              }
							?>
            </td>
          </tr>
        </table> 
      </p>
  	</div>
  </div>
  <?php
}

add_action( 'admin_init', 'visitorsmap_plugin_settings' );
function visitorsmap_plugin_settings() {
  register_setting( 'visitorsmap-plugin-settings-group', 'visitorsmap_map' );
}
add_action( 'wp_enqueue_scripts', 'visitorsmapwptuts_scripts_important', 5 );
add_shortcode('estros-visitorsmap', 'visitorsmapform');