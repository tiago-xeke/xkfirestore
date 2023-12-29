<?php
	class xkfirestore{
	    public $projectID;
	    public $apiKey;

	    public function __construct($ID,$key){
	        $this->projectID = $ID;
	        $this->apiKey = $key;
	    }

	    public function newDocument($table,$fields){
	    	$apiUrl = "https://firestore.googleapis.com/v1/projects/$this->projectID/databases/(default)/documents/$table?key=$this->apiKey";

	    	$data = array(
			    "fields" => $fields
			);

			$jsonData = json_encode($data);

			$ch = curl_init($apiUrl);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"POST");
			curl_setopt($ch,CURLOPT_POSTFIELDS,$jsonData);
			curl_setopt($ch,CURLOPT_HTTPHEADER,array(
			    "Content-Type: application/json",
			    "Content-Length: ".strlen($jsonData)
			));

			$response = curl_exec($ch);
			curl_close($ch);

			return "success";
	    }

	    public function updateDocument($table,$documentID,$fields){
		    $apiUrl = "https://firestore.googleapis.com/v1/projects/$this->projectID/databases/(default)/documents/$table/$documentID?key=$this->apiKey";

		    $fieldPaths = array_map(function($field){
		        return $field;
		    },array_keys($fields));

		    $updateMask = implode(",",$fieldPaths);

		    $data = array(
		        "fields" => $fields
		    );

		    $jsonData = json_encode($data);

		    $ch = curl_init($apiUrl."&updateMask.fieldPaths=".urlencode($updateMask));
		    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		    curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"PATCH");
		    curl_setopt($ch,CURLOPT_POSTFIELDS,$jsonData);
		    curl_setopt($ch,CURLOPT_HTTPHEADER,array(
		        "Content-Type: application/json",
		        "Content-Length: ".strlen($jsonData)
		    ));

		    $response = curl_exec($ch);
		    curl_close($ch);

		    return "Success";
		}


	    public function newQuery($table,$fieldName,$operator,$fieldValue){
	    	$apiUrl = "https://firestore.googleapis.com/v1/projects/$this->projectID/databases/(default)/documents:runQuery?key=$this->apiKey";

	    	$query = [
			    "structuredQuery" => [
			        "from" => [
			            [
			                "collectionId" => $table
			            ]
			        ],
			        "where" => [
			            "fieldFilter" => [
			                "field" => [
			                    "fieldPath" => $fieldName
			                ],
			                "op" => $operator == "<"   ? "LESS_THAN"             :
			                		($operator == ">"  ? "GREATER_THAN"          :
			                		($operator == "<=" ? "LESS_THAN_OR_EQUAL"    :
			                		($operator == ">=" ? "GREATER_THAN_OR_EQUAL" :
			                		($operator == "==" ? "EQUAL"                 :
			                		($operator == "!=" ? "NOT_EQUAL"             :
			                		"EQUAL"))))),
			                "value" => [
			                    "stringValue" => $fieldValue
			                ]
			            ]
			        ]
			    ]
			];

			$jsonQuery = json_encode($query);

			$ch = curl_init($apiUrl);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"POST");
			curl_setopt($ch,CURLOPT_POSTFIELDS,$jsonQuery);
			curl_setopt($ch,CURLOPT_HTTPHEADER,array(
			    "Content-Type: application/json",
			    "Content-Length: ".strlen($jsonQuery),
			));

			$response = json_decode(curl_exec($ch));
			curl_close($ch);

			return $response;
	    }

	    public function has($response){
	    	if(count($response) > 0){
	    		return property_exists($response[0],"document");
	    	}else{
	    		return false;
	    	}
	    }
	}
?>