<?php

//--------------------XML Parser--------------------

function startElementHandler($parser, $name, $attrib){

	global $consulta_actual;
	
	global $error;
	global $query;
	global $suggestions;
	global $countSuggestions;
	global $results;
	global $countResults;
	
	global $inSuggestions;
	global $inResults;
	global $state;

	switch ($name) {
		case $name=="RESPONSE" : {
			$error[$consulta_actual]=$attrib["ERROR"];
			break;
		}
		case $name=="QUERY" : {
			$query[$consulta_actual]["AND_OR"]=$attrib["AND_OR"];
			$query[$consulta_actual]["ACCENTS"]=$attrib["ACCENTS"];
			$query[$consulta_actual]["FEEDBACK"]=$attrib["FEEDBACK"];
			break;
		}
		case $name=="SUGGESTIONS" : {
			$inSuggestions=true;
			$suggestions[$consulta_actual]["TOTAL"]=$attrib["TOTAL"];
			break;
		}
		case $name=="RESULTS" : {
			$inResults=true;
			$results[$consulta_actual]["FROM"]=$attrib["FROM"];
			$results[$consulta_actual]["TO"]=$attrib["TO"];
			$results[$consulta_actual]["TOTAL"]=$attrib["TOTAL"];
			break;
		}
		case $name=="RESULT" : {
			$results[$consulta_actual][$countResults[$consulta_actual]]["RANK"] = $attrib["RANK"];
			$results[$consulta_actual][$countResults[$consulta_actual]]["DOC_ID"] = $attrib["DOC_ID"];
			break;
		}
		default : {
			$state=$name;
			break;
		}
	}
}

function endElementHandler($parser, $name){
	
	global $consulta_actual;
	
	global $error;
	global $query;
	global $suggestions;
	global $countSuggestions;
	global $results;
	global $countResults;
	
	global $inSuggestions;
	global $inResults;
	global $state;
	
	$state='';
	
	switch ($name) {
		case $name=="SUGGESTION" : {
			$countSuggestions[$consulta_actual]++;
			break;
		}
		case $name=="RESULT" : {
			$countResults[$consulta_actual]++;
			break;
		}
		case $name=="SUGGESTIONS" : {
			$inSuggestions=false;
			break;
		}
		case $name=="RESULTS" : {
			$inResults=false;
			break;
		}
	}
}

function characterDataHandler($parser, $data) {
	
	global $consulta_actual;
	
	global $error;
	global $query;
	global $suggestions;
	global $countSuggestions;
	global $results;
	global $countResults;
	
	global $inSuggestions;
	global $inResults;
	global $state;

	if (!$state){
		return;
	}
	else if ($state=="TEXT"){
		$query[$consulta_actual]["TEXT"]=$data;
	}
	else if ($state=="NEW_QUERY" and $inSuggestions){
		$suggestions[$consulta_actual][$countSuggestions[$consulta_actual]]["NEW_QUERY"]=$data;
	}
	else if ($state=="NEW_QUERY" and $inResults){
		$results[$consulta_actual][$countResults[$consulta_actual]]["NEW_QUERY"]=$data;
	}
	else if ($state=="TYPE"){
		$results[$consulta_actual][$countResults[$consulta_actual]]["TYPE"]=$data;
	}
	else if ($state=="TITLE"){
		$results[$consulta_actual][$countResults[$consulta_actual]]["TITLE"]=$data;
	}
	else if ($state=="LANGUAGE"){
		$results[$consulta_actual][$countResults[$consulta_actual]]["LANGUAGE"]=$data;
	}
	else if ($state=="SUMMARY"){
		$results[$consulta_actual][$countResults[$consulta_actual]]["SUMMARY"]=$data;
	}
	else if ($state=="SIZE"){
		$results[$consulta_actual][$countResults[$consulta_actual]]["SIZE"]=$data;
	}
	else if ($state=="URL"){
		$results[$consulta_actual][$countResults[$consulta_actual]]["URL"]=$data;
	}
	else if ($state=="SHORT_URL"){
		$results[$consulta_actual][$countResults[$consulta_actual]]["SHORT_URL"]=$data;
	}
	
}

//--------------------Fin XML Parser--------------------

?>
