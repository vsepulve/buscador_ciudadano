<?php

//--------------------XML Parser--------------------

$error;
$query=array();
$suggestions=array();
$countSuggestions=0;
$results=array();
$countResults=0;
$state='';
$inSuggestions=false;
$inResults=false;

function startElementHandler($parser, $name, $attrib){
	
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
			$error = $attrib["ERROR"];
			break;
		}
		case $name=="QUERY" : {
			$query["AND_OR"] = $attrib["AND_OR"];
			$query["ACCENTS"] = $attrib["ACCENTS"];
			$query["FEEDBACK"] = $attrib["FEEDBACK"];
			break;
		}
		case $name=="SUGGESTIONS" : {
			$inSuggestions=true;
			$suggestions["TOTAL"] = $attrib["TOTAL"];
			break;
		}
		case $name=="RESULTS" : {
			$inResults=true;
			$results["FROM"] = $attrib["FROM"];
			$results["TO"] = $attrib["TO"];
			$results["TOTAL"] = $attrib["TOTAL"];
			break;
		}
		case $name=="RESULT" : {
			$results[$countResults]["RANK"] = $attrib["RANK"];
			$results[$countResults]["DOC_ID"] = $attrib["DOC_ID"];
			break;
		}
		default : {
			$state=$name;
			break;
		}
	}
}

function endElementHandler($parser, $name){
	
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
			$countSuggestions++;
			break;
		}
		case $name=="RESULT" : {
			$countResults++;
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
		$query["TEXT"] = $data;
	}
	else if ($state=="NEW_QUERY" and $inSuggestions){
		$suggestions[$countSuggestions]["NEW_QUERY"] = $data;
	}
	else if ($state=="NEW_QUERY" and $inResults){
		$results[$countResults]["NEW_QUERY"] = $data;
	}
	else if ($state=="TYPE"){
		$results[$countResults]["TYPE"] = $data;
	}
	else if ($state=="TITLE"){
		$results[$countResults]["TITLE"] = $data;
	}
	else if ($state=="LANGUAGE"){
		$results[$countResults]["LANGUAGE"] = $data;
	}
	else if ($state=="SUMMARY"){
		$results[$countResults]["SUMMARY"] = $data;
	}
	else if ($state=="SIZE"){
		$results[$countResults]["SIZE"] = $data;
	}
	else if ($state=="URL"){
		$results[$countResults]["URL"] = $data;
	}
	else if ($state=="SHORT_URL"){
		$results[$countResults]["SHORT_URL"] = $data;
	}
	
}

//--------------------Fin XML Parser--------------------

?>
