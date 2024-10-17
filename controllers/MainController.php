<?php

class MainController {

    private $model; // Define a property for model

    public function __construct($modelClass) {
        include "models/{$modelClass}.php"; // Include the model file
        $this->model = new $modelClass(); // Instantiate the model class
    }

    public function upload_parts_sequentially($data) {

        global $QueryTransformer;

        $retData = array(
            "model" => $data['table'],
            "action" => "retrieve",
            "retrieve" => array("id", "content"),
            "condition" => $data['condition']
        );

        $retrieveQueryParams = $QueryTransformer->prepareQuery($retData);
        $retrieve = $this->model->executeQuery('retrieve', $retrieveQueryParams['query'], $retrieveQueryParams['params']);

        if ($retrieve['code'] == 200 && !empty($retrieve['data'])) :

            $current_content = $retrieve['data'][0]['content'];
            $new_content = $current_content.$data['parts'];
            
            $updateData = array(
                "model" => $data['table'],
                "action" => "update",
                "update" => array(
                    "content" => $new_content
                ),
                "condition" => array(
                    "id" => $retrieve['data'][0]['id']
                )
            );

            $updateQueryParams = $QueryTransformer->prepareQuery($updateData);
            return $update = $this->model->executeQuery('update', $updateQueryParams['query'], $updateQueryParams['params']);

        endif;
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