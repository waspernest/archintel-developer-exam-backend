<?php

/**
 * Follow this format for every controllers to be created.
 * Always include the MainController for CSRF token verification before submitting or performing action of every request
 * 
 * **/

class AuthController {

    private $model; // Define a property for model
    private $mainController;    // Main controller
    private $headers;
    private $QueryTransformer;

    public function __construct($modelClass) {
        include "models/{$modelClass}.php"; // Include the model file
        $this->model = new $modelClass(); // Instantiate the model class

        include "controllers/MainController.php"; 
        $this->mainController = new MainController('Main'); // Instantiate MainController

        // Get the headers from the request
        $this->headers = getallheaders();

        //Instantiate QueryTransformer Helper
        $this->QueryTransformer = new QueryTransformer();
    }

    public function auth_login($data) {

        $user_auth_id = 0;

        $userData = array(
            "model" => "user",
            "action" => "retrieve",
            "retrieve" => "*",
            "condition" => array(
                "first_name" => $data['fields']['first_name'],
                "last_name" => $data['fields']['last_name']
            ),
            "additionalClauses" => "ORDER BY id DESC LIMIT 1"
        );

        $loginQueryParams = $this->QueryTransformer->prepareQuery($userData);
        $authLogin = $this->model->auth_login($loginQueryParams['query'], $loginQueryParams['params']);

        if ($authLogin['code'] == 200 && !empty($authLogin['data'])):

            //get user access from defaults/acl.json
            $aclFile = $_SERVER['DOCUMENT_ROOT'].'/defaults/acl.json';
            $aclData = file_get_contents($aclFile);
            $accessControl = json_decode($aclData, true);

            //return response data to client side
            $response = array(
                "code" => 200,
                "status" => "success",
                "message" => "User found. Logging in...",
                "user_acl" => $accessControl,
                "data" => $authLogin['data']
            );

        else:

            $response = array(
                "code" => 200,
                "status" => "error",
                "message" => "User not found."
            );

        endif; 

        return $response;
    }

    public function auth_logout($data) {

        global $QueryTransformer;

        $logoutData = array(
            "model" => "auth",
            "action" => "update",
            "update" => array(
                "logged_out" => date("Y-m-d H:i:s")
            ),
            "condition" => array(
                "id" => $data['user_auth_id']
            )
        );

        $logoutQueryParams = $this->QueryTransformer->prepareQuery($logoutData);
        return $logout = $this->model->executeQuery('update', $logoutQueryParams['query'], $logoutQueryParams['params']);

    }
}