<?php

/**
 * Follow this format for every controllers to be created.
 * Always include the MainController for CSRF token verification before submitting or performing action of every request
 * 
 * **/

class UserController {

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

    public function test($data) {

        // Verify CSRF token using the headers stored in the constructor
        if (!$this->mainController->verifyCsrfToken($this->headers)) {
            return array('error' => 'Invalid CSRF token');
        }

        $response = array(
            'code' => 200,
            'message' => 'csrf is valid and accepted',
        );

        return $response;
    }

    /**
     * 
     * parameter in client side example:
     * let parameter = {
     *      model: 'user', //name of model-controller
     *      method: 'add_user', //name of function inside the controller
     *      action: 'insert', //this is for the QueryTransformer Helper. Define if action is insert, update, retrieve or delete action
     *      insert: { //if method is insert
     *          id: 1,
     *          name: 'asd',
     *          etc...
     *      }
     * }
     * 
     * **/

    public function add_user($data) {

        // Verify CSRF token using the headers stored in the constructor
        if (!$this->mainController->verifyCsrfToken($this->headers)) {
            return array('error' => 'Invalid CSRF token');
        }

        //insert into users table
        $addUserParams = $this->QueryTransformer->prepareQuery($data);
        $addUser = $this->model->executeQuery('insert', $addUserParams['query'], $addUserParams['params']);

        if ($addUser['code'] == 200 && isset($addUser['id'])) :

            //create account
            $accountData = array(
                "model" => "accounts",
                "action" => "insert",
                "insert" => array(
                    "uid" => $addUser['id'],
                    "email" => $data['insert']['email'],
                    "password" => password_hash("password123", PASSWORD_DEFAULT), //create default password "password123"; user can change this when they login and access their account
                    "user_type" => 5
                )
            );

            $accountQuery = $this->QueryTransformer->prepareQuery($accountData);
            $account = $this->model->executeQuery('insert', $accountQuery['query'], $accountQuery['params']);

            $response = $addUser;
        else:
            $response = array(
                "code" => 200,
                "status" => "error",
                "message" => "Oops! An error occured while adding user. Please try again."
            );

        endif;

        return $response;

    }

    public function insert($data) {

        // Verify CSRF token using the headers stored in the constructor
        if (!$this->mainController->verifyCsrfToken($this->headers)) {
            return array('error' => 'Invalid CSRF token');
        }

        if (isset($data['method'])) {
            $data['action'] = $data['method'];
            unset($data['method']);
        }

        $updateQueryParams = $this->QueryTransformer->prepareQuery($data);
        return $update = $this->model->executeQuery('insert', $updateQueryParams['query'], $updateQueryParams['params']);

    }

    public function update($data) {

        // Verify CSRF token using the headers stored in the constructor
        if (!$this->mainController->verifyCsrfToken($this->headers)) {
            return array('error' => 'Invalid CSRF token');
        }

        if (isset($data['method'])) {
            $data['action'] = $data['method'];
            unset($data['method']);
        }

        $updateQueryParams = $this->QueryTransformer->prepareQuery($data);
        return $update = $this->model->executeQuery('update', $updateQueryParams['query'], $updateQueryParams['params']);

    }

    public function retrieve($data) {

        // Verify CSRF token using the headers stored in the constructor
        if (!$this->mainController->verifyCsrfToken($this->headers)) {
            return array('error' => 'Invalid CSRF token');
        }

        if (isset($data['method'])) {
            $data['action'] = $data['method'];
            unset($data['method']);
        }

        $retrieveQueryParams = $this->QueryTransformer->prepareQuery($data);
        return $retrieve = $this->model->executeQuery('retrieve', $retrieveQueryParams['query'], $retrieveQueryParams['params']);

    }

    public function delete($data) {

        // Verify CSRF token using the headers stored in the constructor
        if (!$this->mainController->verifyCsrfToken($this->headers)) {
            return array('error' => 'Invalid CSRF token');
        }

        if (isset($data['method'])) {
            $data['action'] = $data['method'];
            unset($data['method']);
        }

        $retrieveQueryParams = $this->QueryTransformer->prepareQuery($data);
        return $retrieve = $this->model->executeQuery('delete', $retrieveQueryParams['query'], $retrieveQueryParams['params']);

    }
}