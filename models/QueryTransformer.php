<?php

class QueryTransformer {

	/*
	*
	*	Actions: insert, update, delete, retrieve
	*
	*/

	public function prepareQuery($data) {

	    $sql = "";
	    $model = $data['model'];
	    $params = []; // Array to store the values for bindParam
	    $additionalClauses = $data['additionalClauses'] ?? '';

	    switch ($data['action']):

	        case 'insert':

	            // Transform insert data and prepare params
	            if (isset($data['insert'])) {
	                list($insertFields, $insertPlaceholders, $params) = $this->transformDataWithParams($data['insert'], 'insert');
	                $sql = "INSERT INTO $model ($insertFields) VALUES ($insertPlaceholders)";
	            }

	            break;

	        case 'update':

			    // Transform update data and condition with params
			    if (isset($data['update'])) {
			        // Pass the second argument as 'update' to indicate the context
			        list($updateFields, $updatePlaceholders, $updateValues) = $this->transformDataWithParams($data['update'], 'update');
			        $updateString = implode(", ", array_map(function($field) {
			            return "$field = ?";
			        }, array_keys($data['update']))); // Create 'field = ?' pairs for update
			        
			        $params = array_merge($params, $updateValues); // Only merge the values array, not the placeholders
			    }

			    if (isset($data['condition'])) {
			        list($condition, $conditionValues) = $this->transformConditionWithParams($data['condition'], $additionalClauses);
			        $params = array_merge($params, $conditionValues); // Add condition values to params
			    }

			    // Use $updateString (SET clause) and $condition (WHERE clause)
			    $sql = "UPDATE $model SET $updateString $condition";

		    	break;

	        case 'delete':

	            if (isset($data['condition'])) {
	                list($condition, $conditionValues) = $this->transformConditionWithParams($data['condition'], $additionalClauses);
	                $params = array_merge($params, $conditionValues);
	            }
	            $sql = "DELETE FROM $model $condition";

	            break;

	        default: // for retrieve

	            $keys = $this->transformKeys($data['retrieve']);

	            // Initialize empty condition and parameters
			    $condition = '';
			    $conditionValues = [];

	            if (isset($data['condition'])) {
	                list($condition, $conditionValues) = $this->transformConditionWithParams($data['condition'], $additionalClauses);
	                $params = array_merge($params, $conditionValues);
	            }
	            $sql = "SELECT $keys FROM $model $condition";
	            break;

	    endswitch;

	    return ['query' => $sql, 'params' => $params];
	}

	private function transformDataWithParams($data, $type) {
	    $fields = [];
	    $placeholders = [];
	    $values = [];

	    foreach ($data as $column => $value) {
	        $fields[] = $column;
	        if ($type === 'insert') {
	            $placeholders[] = '?'; // For INSERT queries
	        } elseif ($type === 'update') {
	            $placeholders[] = "$column = ?"; // For UPDATE queries
	        }
	        $values[] = $value; // Add actual values for later binding
	    }

	    $insertFields = implode(", ", $fields);
	    $insertPlaceholders = implode(", ", $placeholders);

	    return [$insertFields, $insertPlaceholders, $values];
	}

	// private function transformConditionWithParams($conditions) {
	//     $conditionParts = [];
	//     $values = [];

	//     foreach ($conditions as $field => $value) {

	//         $conditionParts[] = "$field = ?";
	//         $values[] = $value; // Add the condition value for binding
	//     }

	//     $condition = "WHERE " . implode(" AND ", $conditionParts);
	//     return [$condition, $values];
	// }

	private function transformConditionWithParams($conditions, $additionalClauses = '') {
	    $conditionParts = [];
	    $values = [];

	    // Build the WHERE clause with placeholders and values
	    foreach ($conditions as $field => $value) {
	        if (is_array($value)) {
	            // Handle cases like "IN" or other array-based conditions
	            $placeholders = implode(", ", array_fill(0, count($value), "?"));
	            $conditionParts[] = "$field IN ($placeholders)";
	            $values = array_merge($values, $value);
	        } else {
	            $conditionParts[] = "$field = ?";
	            $values[] = $value; // Add the condition value for binding
	        }
	    }

	    // Construct the WHERE clause
	    $condition = $conditionParts ? "WHERE " . implode(" AND ", $conditionParts) : '';

	    // Append any additional clauses like LIMIT, ORDER BY, etc.
	    $finalQuery = trim("$condition $additionalClauses");

	    return [$finalQuery, $values];
	}

	private function transformKeys($data) {

		if (is_array($data)) $string = implode(", ", $data);
		else $string = $data;

		return $string;
	} 

	public function transformRecords($data) {

	    $records = [];

	    if (is_array($data)) {

	        foreach ($data as $record) {
	            // Ensure $record is an array and has a 'password' field
	            if (is_array($record) && isset($record['password'])) {
	                unset($record['password']); // Remove the password field
	            }
	            
	            $records[] = $record; // Add the modified record to $records
	        }

	    }

	    return $records;

	}
}