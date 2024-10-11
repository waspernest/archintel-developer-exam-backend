<?php
class Auth
{	

	public function generateRandomString($length = 12) {

		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	    $charactersLength = strlen($characters);
	    $randomString = '';

	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[random_int(0, $charactersLength - 1)];
	    }

	    return sha1($randomString.date("Ymd-His"));

	}

	/*
	*
	*	Actions: insert, update, delete, retrieve
	*
	*/

    public function executeQuery($action, $query, $params){

	    try {
	        global $dbh;

	        if (!$dbh) {
	            throw new Exception('Database connection is not initialized');
	        }

	        $stmt = $dbh->prepare($query);

	        foreach ($params as $index => $param) :
	            $stmt->bindValue($index + 1, $param);
	        endforeach;

	        $stmt->execute();

	        // define return type based on $action i.e insert, update, retrieve or delete
	        switch ($action):

	        	case 'insert':

	        		$array = array('code'=>200, 'message'=>'Successful', 'id'=> $dbh->lastInsertId());

	        		break;

	        	case 'update':

	        		$array = array('code'=>200, 'message'=>'Successful');

	        		break;

	        	default:

	        		// $records = [];

	        		// foreach ($stmt->fetchAll() as $data):
	        		// 	if (isset($data['password'])) unset($data['password']);
	        		// 	else $records[] = $data;
	        		// endforeach;

	        		// retrieve
	        		$array = array('code'=>200, 'message'=>'Successful', 'data' =>$stmt->fetchAll(PDO::FETCH_ASSOC));

	        		break;

	        endswitch;

	        return $array;

	    } catch (PDOException $e) {
	        return array('code'=>402, 'message'=>$e->getMessage());
	    } catch (Exception $e) {
	        return array('code'=>403, 'message'=>$e->getMessage());
	    }
	}

	public function auth_login($query, $params) {

		try {

			global $dbh;

			$stmt = $dbh->prepare($query);

			foreach ($params as $key => $param) :
				$stmt->bindValue($key + 1, $param);
			endforeach;

			$stmt->execute();

			$records = [];
			if ($stmt->rowCount() > 0) :	
				$rs = $stmt->fetchAll(PDO::FETCH_ASSOC);

				foreach ($rs as $key => $value):
					$records[] = $value;
				endforeach;

			endif;

	        $array = array('code'=>200, 'message'=>'Successful', 'data' =>$records);

	        return $array;


		} catch (PDOException $e) {
	        return array('code'=>402, 'message'=>$e->getMessage());
	    } catch (Exception $e) {
	        return array('code'=>403, 'message'=>$e->getMessage());
	    }

	}

	public function auth_password($input_password, $user_password) {

		if (password_verify($input_password, $user_password)) return true;

		return false;

	}

	public function auth_access_control($user_type) {

		#get the access control from DB

		try {

			global $dbh;

			$stmt = $dbh->prepare("SELECT access FROM access_control WHERE user_type=:user_type LIMIT 1");
			$stmt->execute(array(":user_type" => $user_type));

			if ($stmt->rowCount() > 0) :

				// Fetch the access control data
	            $result = $stmt->fetch(PDO::FETCH_ASSOC);
	            // Decode the JSON string
	            $access = json_decode($result['access'], true); // Use true to get an associative array

	            if (isset($access) && !empty($access)) :
	            	return $access;
	            endif;

			endif;

		} catch (PDOException $e) {
	        return array('code'=>402, 'message'=>$e->getMessage());
	    } catch (Exception $e) {
	        return array('code'=>403, 'message'=>$e->getMessage());
	    }

	}
	
}
