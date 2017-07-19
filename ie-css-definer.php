<?php
/*
Plugin Name: IE CSS Definer
Plugin URI: http://hebeisenconsulting.com/ie-css-definer/
Description: IE CSS Definer allows you to easily and quickly enter internet explorer version specific css code without adding extra css files separately.
Version: 1.2
Author: Hebeisen Consulting - R Bueno
Author URI: http://www.hebeisenconsulting.com
License: A "Slug" license name e.g. GPL2

   Copyright 2012 Hebeisen Consulting

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('wp_head', 'ie_css_head');
add_action('admin_menu', 'ie_css_menu');

function ie_css_install()
{
    global $wpdb;
    $table = $wpdb->prefix . "ie_css";
	if($wpdb->get_var("show tables like '$table'") != $table) {
	    $sql = "CREATE TABLE " . $table . " (
					  id int(11) NOT NULL AUTO_INCREMENT,
					  cond varchar(150) NOT NULL,
					  css text NOT NULL,
					  PRIMARY KEY (id)
					)";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	    dbDelta($sql);
	}
}
register_activation_hook(__FILE__, 'ie_css_install');

function ie_css_uninstall()
{
    global $wpdb;
    $table = $wpdb->prefix . "ie_css";
    $wpdb->query("DROP TABLE $table");
}
register_deactivation_hook(__FILE__, 'ie_css_uninstall');

function ie_css_head()
{
	global $wpdb;
	$table = $wpdb->prefix . "ie_css";
	
	$sql = $wpdb->get_results("select * from " . $table . " order by id desc"); // or die(mysql_error());
	
	if( $sql ):
	 echo "\n";
	 echo '<!-- START OF IE-CSS PLUGIN -->' . "\n\n";
	 for( $i=0; $i < count( $sql ); $i++ ):
		
		echo '<!--' . $sql[$i]->cond . ">\n";
		echo '<style type="text/css">' . "\n";
		echo $sql[$i]->css . "\n";
		echo '</style>' . "\n";
		echo '<![endif]-->' . "\n\n";
	 endfor;
	 echo '<!-- END OF IE-CSS PLUGIN-->' . "\n\n";
	endif;
}

function ie_css_menu()
{
	//$page = add_options_page('IE CSS', 'IE CSS', 'manage_options', 'ie-css-slug', 'ie_css_option');
	
	$page = add_submenu_page( 'tools.php', 'iE CSS Definer', 'iE CSS Definer', 'manage_options', 'ie-css-slug', 'ie_css_option');
	add_action( 'admin_head-' . $page, 'ie_css_admin_head' );
}

function ie_css_admin_head(){
	echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>';
}

function check_select( $items_to_check, $corr_value )
{	
	global $wpdb;
	$table = $wpdb->prefix . "ie_css";
	
	$sql = $wpdb->get_results( "SELECT * FROM $table" );
	
	$pattern = "/^$items_to_check/";
	
	$matches = preg_match(  $pattern, $sql[0]->cond );
	
	echo $pattern;
	
	//print_r( $sql );
	
	print_r($matches);
}

function ie_css_trim( $css )
{
	$count=str_word_count( $css, 0 );
	
	if( $count > 200 ){
		return substr( $css, 200 );
	}else{
		return $css;
	}
	
}

function ie_css_option()
{
	echo '<div class="wrap">';
	
	global $wpdb;
	$table = $wpdb->prefix . "ie_css";
	
	if( $_GET['delete'] == "true" ):
		$wpdb->query( "DELETE FROM " . $table . " WHERE id = " . $_GET['id'] );
	endif;
	
	if( $_GET['add'] == "true" ):
	
		switch( $_POST['compare'] )
		{
			case'=':
				$cond = "[if ";
			break;
			
			case'!=':
				$cond = "[if !";
			break;
			
			case'>':
				$cond = "[if gt ";
			break;
			
			case'<':
				$cond = "[if lt ";
			break;
			
			case'>=':
				$cond = "[if gte ";
			break;
			
			case'<=':
				$cond = "[if lte ";
			break;
		}
	
		$wpdb->insert( 
			$table, 
			array( 
				'cond' => $cond . $_POST['ie_version'] . " ]", 
				'css' => $_POST['css'] 
			)
		);// or die( mysql_error() );
		
		update_option( 'compare', $_POST['compare'] );
		update_option( 'ie_version', $_POST['ie_version'] );
		
	endif;	
	
	if( $_GET['edit'] == "true" ):
		
		if( $_GET['save'] == "true" ):
			
			switch( $_POST['compare'] )
			{
				case'=':
					$cond = "[if ";
				break;
				
				case'!=':
					$cond = "[if !";
				break;
				
				case'>':
					$cond = "[if gt ";
				break;
				
				case'<':
					$cond = "[if lt ";
				break;
				
				case'>=':
					$cond = "[if gte ";
				break;
				
				case'<=':
					$cond = "[if lte ";
				break;
			}
			
			$wpdb->update(
				$table,
				array( 
					'cond' => $cond . $_POST['ie_version'] . " ]", 
					'css' => $_POST['css'] 
				),
				array(
					'id' => $_GET['id'],
				)
			);
			
			update_option( 'compare', $_POST['compare'] );
			update_option( 'ie_version', $_POST['ie_version'] );
			
			//echo admin_url() . 'tools.php?page=ie-css-slug';
			//header( "Location: " . admin_url() . "tools.php?page=ie-css-slug" );
			echo '<meta http-equiv="REFRESH" content="0;url=' . admin_url() . 'tools.php?page=ie-css-slug' . '">';
		//echo '<h1>iE CSS Definer</h1>';
		//echo '<div class="updated"><p><strong>Updated!</strong> Click <a href="tools.php?page=ie-css-slug">here</a> to go back</p></div>';
		else:
			$result=$wpdb->get_results( "select * from " . $table . " where id = " . $_GET['id'] );
			
			echo '<h1>IE CSS</h1>';
			echo '<form id="ie-css-definer-form" method="post" action="tools.php?page=ie-css-slug&edit=true&save=true&id=' . $_GET['id'] . '">';
			//echo get_option( 'compare' );
			?>
			<p>
			  	When the Internet Explorer version being used is 
			  	 <select name= "compare">
			  	  <option value="=" <?php selected( get_option( 'compare' ), "="); ?>>=</option>
			  	  <option value="!=" <?php selected( get_option( 'compare' ), "!="); ?>>!=</option>
			  	  <option value=">" <?php selected( get_option( 'compare' ), ">"); ?>>></option>
			  	  <option value="<" <?php selected( get_option( 'compare' ), "<"); ?>><</option>
			  	  <option value=">=" <?php selected( get_option( 'compare' ), ">="); ?>>>=</option>
			  	  <option value="<=" <?php selected( get_option( 'compare' ), "<="); ?>><=</option>
			  	 </select>
			  	 
			  	 <select name = "ie_version">
			  	  <option value="IE 6" <?php selected( get_option( 'ie_version' ), "IE 6"); ?>>IE6</option>
			  	  <option value="IE 7" <?php selected( get_option( 'ie_version' ), "IE 7"); ?>>IE7</option>
			  	  <option value="IE 8" <?php selected( get_option( 'ie_version' ), "IE 8"); ?>>IE8</option>
			  	  <option value="IE 9" <?php selected( get_option( 'ie_version' ), "IE 9"); ?>>IE9</option>
			  	  <option value="IE" <?php selected( get_option( 'ie_version' ), "IE"); ?>>All Versions</option>
			  	 </select>
			     </p>
			<?php
			echo '<p>
				<textarea name="css" rows="20" cols="100">' . $result[0]->css . '</textarea>
			     </p>';
			echo '<p>
				<input type="submit" class="button-primary" value="Save">
			     </p>';
			echo '</form>';
		endif;
	else:
		echo '<h1>iE CSS Definer</h1>';
		echo '<form id="ie-css-definer-form" method="post" action="tools.php?page=ie-css-slug&add=true">';
		echo '<p>
		  	When the Internet Explorer version being used is 
		  	 <select name= "compare">
		  	  <option value="=">=</option>
		  	  <option value="!=">!=</option>
		  	  <option value=">">></option>
		  	  <option value="<"><</option>
		  	  <option value=">=">>=</option>
		  	  <option value="<="><=</option>
		  	 </select>
		  	 
		  	 <select name = "ie_version">
		  	  <option value="IE 6">IE6</option>
		  	  <option value="IE 7">IE7</option>
		  	  <option value="IE 8">IE8</option>
		  	  <option value="IE 9">IE9</option>
		  	  <option value="IE">All Versions</option>
		  	 </select>
		     </p>';
		echo '<p>
			<textarea name="css" rows="20" cols="100"></textarea>
		     </p>';
		echo '<p>
			<input type="submit" class="button-primary" value="Add conditional CSS statement">
		     </p>';
		echo '</form>';
		
	?>
		<form method="post" action="tools.php?page=ie-css-slug">
		<table class="widefat">
		<thead>
		    <tr>
		        <th>IE Version</th>       
		        <th>CSS Condition</th>
		        <th>Action</th>
		    </tr>
		</thead>
		<tfoot>
		    <tr>
			<th>IE Version</th>       
		        <th>CSS Condition</th>
		        <th>Action</th>
		    </tr>
		</tfoot>
		<tbody>	   
			<?php
				$dataFromDB = $wpdb->get_results( "SELECT * FROM " . $table . " order by id desc" );// or die( mysql_error());
				
				if( $dataFromDB ):
					for( $i=0; $i< count( $dataFromDB ); $i++ ):
						echo '<tr><td width="15%">' . $dataFromDB[$i]->cond . '</td><td width="50%"><p style="font-family: courier-new; font-size: 12px;">' . ie_css_trim( $dataFromDB[$i]->css ) . '</p></td><td width="15%" ><input type="button" id="ie-edit" onClick="location.href=\'tools.php?page=ie-css-slug&edit=true&id=' . $dataFromDB[$i]->id . '\';" class="button-primary" value="Edit"><input type="button" id="ie-delete" onClick="location.href=\'tools.php?page=ie-css-slug&delete=true&id=' . $dataFromDB[$i]->id . '\';" class="button-primary" value="Delete"></td></tr>';
					endfor;
				else:
					echo '<td colspan="3">No iE CSS rules defined.</td>';
				endif;
			?>	  
		</tbody>
		</table>
		</form>
	<?php	
	endif;
	
	echo '</div>';
}

?>