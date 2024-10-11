<?php

class MainController {

    private $model; // Define a property for model

    public function __construct($modelClass) {
        include "models/{$modelClass}.php"; // Include the model file
        $this->model = new $modelClass(); // Instantiate the model class
    }

    public function test($data) {
        
        global $QueryTransformer;

        $hashedPassword = password_hash("password123", PASSWORD_DEFAULT);

        $insertData = array(
        	"model" => "user",
        	"action" => "insert",
        	"insert" => array(
        		"name" => "Admin 1",
        		"email" => "admin@email.com",
        		"password" => $hashedPassword
        	)
        );

        $insertQuery = $QueryTransformer->prepareQuery($insertData);
        return $insert = $this->model->executeQuery('insert', $insertQuery['query'], $insertQuery['params']);

    }


    public function getUserCsrfToken($uid) {

    	return $this->model->get_csrf_token_by_uid($uid);

    }

    // Method to verify CSRF token based on headers
    public function verifyCsrfToken($headers) {
        // Retrieve the user ID and CSRF token from headers
        $userId = isset($headers['X-User-ID']) ? $headers['X-User-ID'] : null;
        $csrfToken = isset($headers['X-CSRF-Token']) ? $headers['X-CSRF-Token'] : null;

        if ($userId && $csrfToken) :
            $storedToken = $this->getUserCsrfToken($userId); // Get the stored CSRF token for the user
            if ($storedToken && $csrfToken === $storedToken) return true; // Valid token
        endif;

        return false; // Invalid token
    } 
}