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
            "model" => "accounts",
            "action" => "retrieve",
            "retrieve" => "*",
            "condition" => array(
                "email" => $data['fields']['email']
            )
        );

        $loginQueryParams = $this->QueryTransformer->prepareQuery($userData);
        $authLogin = $this->model->auth_login($loginQueryParams['query'], $loginQueryParams['params']);

        if ($authLogin['code'] == 200 && !empty($authLogin['data'])) :

            $authPassword = $this->model->auth_password($data['fields']['password'], $authLogin['data'][0]['password']); 

            if ($authPassword) :

                $csrfToken = bin2hex(random_bytes(32));

                //check if user has an existing auth data that has not been logged out
                $checkAuthData = array(
                    "model" => "auth",
                    "action" => "retrieve",
                    "retrieve" => "*",
                    "condition" => array(
                        "uid" => $authLogin['data'][0]['uid'],
                        "logged_out" => "0000-00-00",
                    ),
                    "additionalClauses" => "ORDER BY id DESC LIMIT 1"
                );

                $checkAuthQueryParams = $this->QueryTransformer->prepareQuery($checkAuthData);
                $checkAuth = $this->model->executeQuery('retrieve', $checkAuthQueryParams['query'], $checkAuthQueryParams['params']);

                if ($checkAuth['code'] == 200 && !empty($checkAuth['data'])) :

                    //regenrate a new one and update login datetime
                    $updateAuthData = array(
                        "model" => "auth",
                        "action" => "update",
                        "update" => array(
                            "csrf_token" => $csrfToken,
                            "logged_in" => date("Y-m-d H:i:s")
                        ),
                        "condition" => array(
                            "id" => $checkAuth['data'][0]['id']
                        )
                    );

                    $updateAuthDataQueryParams = $this->QueryTransformer->prepareQuery($updateAuthData);
                    $updateAuth = $this->model->executeQuery('update', $updateAuthDataQueryParams['query'], $updateAuthDataQueryParams['params']);

                    $user_auth_id = $checkAuth['data'][0]['id'];

                else:

                    //insert into auth table for tracking of login, logout time and date
                    $authData = array(
                        "model" => "auth",
                        "action" => "insert",
                        "insert" => array(
                            "uid" => $authLogin['data'][0]['uid'],
                            "csrf_token" => $csrfToken,
                            "logged_out" => "0000-00-00"
                        )
                    );

                    $authQuery = $this->QueryTransformer->prepareQuery($authData);
                    $auth = $this->model->executeQuery('insert', $authQuery['query'], $authQuery['params']);

                    $user_auth_id = $auth['id'];

                endif;


                //return records to client side

                $userModel = ($authLogin['data'][0]['user_type'] == 99) ? "admin" : "user";

                $userDetailsData = array(
                    "model" => $userModel,
                    "action" => "retrieve",
                    "retrieve" => "*",
                    "condition" => array(
                        "id" => $authLogin['data'][0]['uid']
                    )
                );

                $userDetailsDataQueryParams = $this->QueryTransformer->prepareQuery($userDetailsData);
                $userDetails = $this->model->executeQuery('retrieve', $userDetailsDataQueryParams['query'], $userDetailsDataQueryParams['params']);

                $response_data = $this->QueryTransformer->transformRecords($userDetails['data']);

                //add user_level and user_auth_id into response['data']
                foreach ($response_data as &$user) :
                    $user['user_level'] = $authLogin['data'][0]['user_type'];
                    $user['user_auth_id'] = $user_auth_id;
                endforeach;

                $response = array(
                    "code" => 200,
                    "status" => "success",
                    "message" => "Logging in... Please wait...",
                    "data" => $response_data,
                    "csrf_token" => $csrfToken,
                    "acl" => $this->model->auth_access_control($authLogin['data'][0]['user_type'])
                );

            else :
                $response = array('code'=> 200, 'status' => 'error', 'message' => 'Incorrect password.'); 
            endif;

        else:
        
            $response = array('code'=>200, 'status' => 'error', 'message' => 'No account found in this email. Please check and try again.');
        
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