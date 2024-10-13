<?php

/**
 * Follow this format for every controllers to be created.
 * Always include the MainController for CSRF token verification before submitting or performing action of every request
 * 
 * **/

class ArticleController {

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

    public function insert($data) {

        if (isset($data['method'])) {
            $data['action'] = $data['method'];
            unset($data['method']);
        }

        $updateQueryParams = $this->QueryTransformer->prepareQuery($data);
        return $update = $this->model->executeQuery('insert', $updateQueryParams['query'], $updateQueryParams['params']);

    }

    public function update($data) {

        if (isset($data['method'])) {
            $data['action'] = $data['method'];
            unset($data['method']);
        }

        $updateQueryParams = $this->QueryTransformer->prepareQuery($data);
        return $update = $this->model->executeQuery('update', $updateQueryParams['query'], $updateQueryParams['params']);

    }

    public function retrieve($data) {

        if (isset($data['method'])) {
            $data['action'] = $data['method'];
            unset($data['method']);
        }

        $retrieveQueryParams = $this->QueryTransformer->prepareQuery($data);
        return $retrieve = $this->model->executeQuery('retrieve', $retrieveQueryParams['query'], $retrieveQueryParams['params']);

    }

    public function delete($data) {

        if (isset($data['method'])) {
            $data['action'] = $data['method'];
            unset($data['method']);
        }

        $retrieveQueryParams = $this->QueryTransformer->prepareQuery($data);
        return $retrieve = $this->model->executeQuery('delete', $retrieveQueryParams['query'], $retrieveQueryParams['params']);

    }
}