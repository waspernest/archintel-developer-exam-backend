<?php

/**
 * Follow this format for every controllers to be created.
 * Always include the MainController for CSRF token verification before submitting or performing action of every request
 * 
 * **/

class AssocController {

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

    public function insert($data) {

        // Verify CSRF token using the headers stored in the constructor
        if (!$this->mainController->verifyCsrfToken($this->headers)) {
            return array('error' => 'Invalid CSRF token');
        }

        if (isset($data['table'])) {
            $data['model'] = $data['table'];
            unset($data['table']);
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

        if (isset($data['table'])) {
            $data['model'] = $data['table'];
            unset($data['table']);
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

        if (isset($data['table'])) {
            $data['model'] = $data['table'];
            unset($data['table']);
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

        if (isset($data['table'])) {
            $data['model'] = $data['table'];
            unset($data['table']);
        }

        if (isset($data['method'])) {
            $data['action'] = $data['method'];
            unset($data['method']);
        }

        $retrieveQueryParams = $this->QueryTransformer->prepareQuery($data);
        return $retrieve = $this->model->executeQuery('delete', $retrieveQueryParams['query'], $retrieveQueryParams['params']);

    }

    /**
     * Since there is no QueryBuilder or Helper yet for custom queries like JOINS, we create it manually for now/
     * **/

    public function custom_join_query($data) {

        $query = "";
        $params = [];

        switch($data['table']) :

            case 'get_user_locations': //get all the locations from location_assoc table under the user id and get the location details from location table

                $query = "SELECT 
                            l.*
                        FROM location_assoc as la
                        INNER JOIN location as l ON la.loc_id = l.id
                        WHERE la.uid = ?";

                $params[] = $data['uid'];   

                break;

            case 'get_owners_staffs': // get all staffs information from staff table then get the location name from location table

                $query = "SELECT
                            s.*,
                            l.location_name as location_name,
                            sa.user_level as user_level
                        FROM staff_assoc as sa
                        INNER JOIN staff as s ON sa.staff_id = s.id
                        INNER JOIN location as l on sa.loc_id = l.id
                        WHERE sa.owner_id = ?";

                $params[] = $data['uid'];

                break;

            case 'get_staff_detail': // get the staff detail together with location detail

                $query = "SELECT
                            s.*,
                            l.location_name as location_name,
                            sa.user_level as user_level
                        FROM staff_assoc as sa
                        INNER JOIN staff as s ON sa.staff_id = s.id
                        INNER JOIN location as l on sa.loc_id = l.id
                        WHERE sa.staff_id = ?";

                $params[] = $data['uid'];

                break;

            default:
                break;

        endswitch;

        return $return = $this->model->executeQuery('retrieve', $query, $params);

    }
}