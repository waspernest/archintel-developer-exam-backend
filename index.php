<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-CSRF-Token, X-User-ID, Content-Type');

require_once 'config/config.php';

if (isset($_REQUEST['model'])) :

    $modelClass = (!empty($_REQUEST['model'])) ? ucfirst($_REQUEST['model']) : null;
    $method = trim($_REQUEST['method']) ?? null;
    $response = [];

    if (!is_null($modelClass)) :

        if (file_exists('controllers/'.$modelClass.'Controller.php')) :
            include 'controllers/'.$modelClass.'Controller.php';
            include 'models/QueryTransformer.php'; // Include QueryTransformer first

            $controller = "{$modelClass}Controller"; 
            $methodClass = new $controller($modelClass); // Pass model class to constructor
            $QueryTransformer = new QueryTransformer;

            switch ($modelClass) :
                case 'Auth':
                    $response = $methodClass->$method($_REQUEST);
                    break;
                case 'User':
                    $response = $methodClass->$method($_REQUEST);
                    break;
                case 'Access_control':
                    $response = $methodClass->$method($_REQUEST);
                    break;
                case 'Assoc':
                    $response = $methodClass->$method($_REQUEST);
                    break;
                case 'Location':
                    $response = $methodClass->$method($_REQUEST);
                    break;
                case 'Staff':
                    $response = $methodClass->$method($_REQUEST);
                    break;
                default:
                    $response = $methodClass->$method($_REQUEST);
                    break;
            endswitch;
        endif;

    endif;

    echo json_encode($response);

endif; 