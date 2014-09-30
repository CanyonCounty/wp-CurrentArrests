<?php
/*
Plugin Name: Current Arrest
Plugin URI: http://canyonco.org/
Description: Canyon County Current Arrest Wordpress plugin
Version: 1.0
Author: kwilcox
Author URI: http://canyonco.org/
License: WTFPL
*/

/* GLOBAL FUNCTIONS - Move to Include File */
$GLOBALS['ccdebug'] = false;

function cc_debug($msg) {
	if ($GLOBALS['ccdebug'] === true) {
		echo '<p class="ccdebug">'.$msg.'</p>';
	}
}

function cc_draw_current_arrest_row($row) {
	// ID, Name (First, Middle, Last), Arrest Date/Agency, Statute/Charge, Image
	//cc_debug("<tr></td>DRAW_ROW</td></tr>");
	echo '  <tr>'."\n";
	// ID
	echo '    <td>'.$row->IDNumber.'</td>'."\n";

	// Name
	$name = $row->FirstName;
	if ($row->MiddleName !== '')
		$name .= ' ' . $row->MiddleName;
	$name .= ' '. $row->LastName;
	echo '    <td>'.$name.'</td>'."\n";

	// Arrest Date/Agency
	$arr = "";
	foreach($row->Arrests as $arrest) {
		// If our string isn't empty add a break (second time around!)
		if ($arr !== '') {
			$arr .= '<br>'."\n";
		}

		$arr .= $arrest->ArrestDate . ' (' . $arrest->Agency . ')';
	}
	echo '    <td>'.$arr.'</td>'."\n";

	// Statute / Charge
	$chg = "";
	foreach($row->Charges as $charge) {
		if ($chg !== '') {
			$chg .= '<br>'."\n";
		}
		$chg .= $charge->StatuteCode . ' ' . $charge->StatuteDesc;
	}
	echo '    <td>'.$chg.'</td>'."\n";

	// Image
	echo '    <td><img src="'.$row->ImageThumb.'" alt="Image of '.$name.'" style="vertical-align: middle;"></td>'."\n";

	echo '  </tr>'."\n";
}

function cc_current_arrest_func($atts) {
	ob_start();
	cc_debug("Current Arrest -> 0");
	$ret = wp_remote_get( 'http://secret.canyonco.org/Sheriff/CurrentArrest' );
	if ($ret[response][code] === 200)
	{
		
		$data = json_decode($ret[body]);
		//var_dump($data);
		// TODO: REMOVE STYLE!
		//style="border-color:LightGrey;border-style:Solid;font-size:9pt;width:98%;border-collapse:collapse;display:inline-block;"
		echo '<table cellspacing="0" cellpadding="4">'."\n";
		// table header
		echo '<tr><th scope="col">ID #</th><th scope="col">Name</th><th scope="col">Arrest Date (Agency)</th><th scope="col">Statute/Charges</th><th scope="col">Image</th></tr>'."\n";
		foreach ($data as $value) {
		  cc_draw_current_arrest_row($value);
		  //echo $value->FirstName."<br />";
		  //var_dump($value);
		}
		echo '</table>'."\n";
		// This dumps the JSON to the HTML document
		//echo '<script type="text/javascript"> var current_arrest_data = ' . $ret[body] . "</script>";
		cc_debug("..1");

		
	}
	cc_debug("..2");
	return ob_get_clean();
}

add_shortcode('cc-current-arrest', 'cc_current_arrest_func');

/* CSS STUFF */
function debug_css() {
	// This makes sure that the positioning is also good for right-to-left languages
	echo "
	<style type='text/css'>
	.ccdebug {background-color:#00ff00;}
	</style>
	";
}

add_action( 'wp_head', 'debug_css' );
?>