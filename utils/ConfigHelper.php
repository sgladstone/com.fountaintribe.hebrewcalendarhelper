<?php

class ConfigHelper{
	

function create_option_group($op_grp_title,  $op_grp_name, &$all_options ){
	
	$tmp_option_group_id = "";
	//$tmp_options_name = strtolower(  str_replace( " ", "_", $op_grp_title ) ) ;
	$params = array(
			'version' => 3,
			'sequential'  => 1,
			'title' =>  $op_grp_title,
			'name' => $op_grp_name,

	);
	$result = civicrm_api('OptionGroup', 'get', $params);
	if( $result['count'] == 0 ){
		// print "<br>\nOption group needs to be created:  ".$op_grp_title;
		$params = array(
				'version' => 3,
				'sequential' => 1,
				'is_active'       => 1,
				'title' => $op_grp_title ,
				'name' => $op_grp_name,
				'is_reserved'  => 1,

		);
		$result = civicrm_api('OptionGroup', 'create', $params);
		if( $result['is_error'] == 0 ) {
			$tmp_option_group_id = $result['id'];
			// print "\n Option group created. ID:".$tmp_option_group_id;
			
			//print "\n  Time to add options to new option group";
			foreach( $all_options as $value => $label){
					
				$tmp_name = str_replace( " ", "_" , $value) ;
				$params_option = array(
						'version' => 3,
						'sequential' => 1,
						'option_group_id' => $tmp_option_group_id,
						'label' => $label,
						'value' => $value,
						'name' => $tmp_name,
				);
					
				$result = civicrm_api('OptionValue', 'create',  $params_option );
				


			}



		}else{
			// TODO: return error message
			//print "\nError creating OptionGroup '".$op_grp_name."'";
			//print_r( $result);
		}
			
	}else{
		// print "\nGood news: Option group already exists";
		$tmp_option_group_id   = $result['id'];

	}
	// print "<br>\nAbout to return option group id: ".$tmp_option_group_id;
	return $tmp_option_group_id ;



}

}