<?php

$db = mysqli_connect("localhost", "f71614_dbuser", "x%A&2BLb9Y*5h@fn", "f71614_db");
$db->set_charset("utf8mb4");
$arr = json_decode(file_get_contents("php://input"), true);

switch ($arr["method"]) {
    case 'addUser':
        $arr = $arr["data"];
        $name = $arr["name"];
        $surname = $arr["surname"];
        $age = $arr["age"];
        $sex = $arr["sex"];
        $email = $arr["email"];
        $password = $arr["password"];
        $city = $arr["city"];
        $token = bin2hex(random_bytes(5));
        $result = $db->query("INSERT INTO tinder_users(email, password, name, sex, city, age, token, surname) VALUES ('{$email}', '{$password}', '{$name}', '{$sex}', '{$city}', {$age}, '{$token}', '{$surname}')");
        
        $arr = [
            'result' => 'success',
            'response' => [
                'token' => $token,
                'id' => $db->insert_id,
            ]
        ];
        echo json_encode($arr);
    break;
    case 'login':
        $arr = $arr["data"];
        $email = $arr["email"];
        $password = $arr["password"];
        $token_request = $arr["token"];

        $result = $db->query("SELECT id, token FROM tinder_users WHERE email = '{$email}' AND password = '{$password}'");
        $id;
        $token;
        while ($row = $result->fetch_row()) {
            $id = $row[0];
            $token = $row[1];
            if ($token_request != $token) {
                $token = bin2hex(random_bytes(5));
                $db->query("UPDATE tinder_users SET token = '{$token}' WHERE email = '{$email}' AND password = '{$password}'");
            }
        }
        if (isset($id) && isset($token)) {
            echo json_encode([
                'result' => 'success',
                'response' => [
                    'token' => $token,
                    'id' => $id
                ]
            ]);
        } else {
            echo json_encode([
                'result' => 'error',
                'message' => 'Authorization wrong',
            ]);
        }
    break;
    case 'addAnimal':
        $arr = $arr["data"];

        $sex = $arr["sex"];
        $name = $arr["name"];
        $ownerId = $arr["ownerId"];
        $age = $arr["age"];

        $result = $db->query("INSERT INTO tinder_animals (sex, name, age, ownerId) VALUES ('{$sex}', '{$name}', {$age}, {$ownerId})");
        if ($result) {
            echo json_encode([
                'result' => 'success',
                'message' => "OK",
            ]);
        } else {
            header("HTTP/1.1 500 ERROR");
            echo json_encode([
                'result' => 'error',
                'message' => 'No such owner',
            ]);
        }
    break;
    case 'getAnimals':
        $arr = $arr["data"];

        $result = $db->query("SELECT * FROM tinder_animals");

        $arr = [];
        while ($row = $result->fetch_assoc()) {
            $arr[] = [
                'sex' => $row["sex"],
                'name' => $row["name"],
                'ownerId' => $row["ownerId"],
                'age' => $row["age"],
                'photo' => $row["photo"],
            ];
        }
        echo json_encode([
            'result' => 'success',
            'response' => $arr,
        ]);
        break;
    case 'getAnimalByOwnerId':
        $arr = $arr["data"];
        $ownerId = $arr["id"];

        $result = $db->query("SELECT * FROM tinder_animals WHERE ownerId = {$ownerId}");
        while ($row = $result->fetch_assoc()) {
            echo json_encode([
                'result' => 'success',
                'response' => [
                    'sex' => $row["sex"],
                    'name' => $row["name"],
                    'ownerId' => $row["ownerId"],
                    'age' => $row["age"],
                    'photo' => $row["photo"],
                ]
            ]);
            break;
        }
        break;

    case 'uploadAnimalPhoto':
        $arr = $arr['data'];

        $url = $arr['url'];
        $ownerId = $arr['id'];

        if ($db->query("UPDATE tinder_animals SET photo = '{$url}' WHERE ownerId = {$ownerId}")) {
            echo json_encode([
                'result' => 'success',
                'response' => 'OK',
            ]);
        } else {
            echo json_encode([
                'result' => 'error',
                'response' => 'Something went wrong',
            ]);
        }
        break;
    case 'getProfile':
        $arr = $arr["data"];

        $email = $arr["email"];
        $password = $arr["password"];

        $result = $db->query("SELECT * FROM tinder_users WHERE email = '{$email}' AND password = '{$password}'");
        while ($row = $result->fetch_assoc()) {
            echo json_encode([
                'result' => 'success',
                'response' => [
                    'name' => $row["name"],
                    'sex' => $row["sex"],
                    'city' => $row["city"],
                    'age' => $row["age"],
                    'token' => $row["token"],
                    'surname' => $row["surname"],
                    'photo' => $row['photo'],
                ]
            ]);
            break;
        }
    break;
    case 'editProfile':
        $arr = $arr["data"];

        $name = $arr["name"];
        $sex = $arr["sex"];
        $city = $arr["city"];
        $age = $arr["age"];
        $surname = $arr["surname"];
        $email = $arr["email"];
        $token = $arr["token"];
        $password = $arr["password"];
        
    if ($db->query("UPDATE tinder_users SET name = '{$name}', sex = '{$sex}', city = '{$city}', age = {$age}, surname = '{$surname}', email = '{$email}', password = '{$password}' WHERE token = '{$token}'")) {
        echo json_encode([
            'result' => 'success',
            'response' => 'OK',
        ]);
    } else {
        echo json_encode([
            'result' => 'error',
            'response' => 'Something went wrong',
        ]);
    }
    break;
    case 'getProfileById':
        $arr = $arr["data"];

        $id = $arr["id"];

        $result = $db->query("SELECT * FROM tinder_users WHERE id = {$id}");
        while ($row = $result->fetch_assoc()) {
            echo json_encode([
                'result' => 'success',
                'response' => [
                    'name' => $row["name"],
                    'sex' => $row["sex"],
                    'city' => $row["city"],
                    'age' => $row["age"],
                    'surname' => $row["surname"],
                    'photo' => $row['photo'],
                    'email' => $row['email'],
                ]
            ]);
            break;
        }
    break;
    case 'getProfileByGender':
        $arr = $arr['data'];

        $user_id = $arr["id"];
        $user_sex;
        $result = $db->query("SELECT sex FROM tinder_users WHERE id = {$user_id}");
        while ($row = $result->fetch_assoc()) {
            $user_sex = $row['sex'];
        }
        $gender = $user_sex == 'male' ? 'female' : 'male';
        $result = $db->query("SELECT * FROM tinder_users WHERE sex = '{$gender}' AND id NOT IN(SELECT toUser FROM tinder_matching WHERE fromUser = $user_id) AND id NOT IN(SELECT fromUser FROM tinder_matching WHERE toUser = $user_id AND isMatched = 1)");
        $arr = [];
        while ($row = $result->fetch_assoc()) {
            $arr[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'sex' => $row['sex'],
                'city' => $row['city'],
                'age' => $row['age'],
                'surname' => $row['surname'],
                'photo' => $row['photo'],
            ];
        }
        echo json_encode([
            'result' => 'success',
            'response' => $arr,
        ]);
    break;
    case 'uploadPhoto':
        $arr = $arr['data'];

        $url = $arr['url'];
        $id = $arr['id'];

        if ($db->query("UPDATE tinder_users SET photo = '{$url}' WHERE id = {$id}")) {
            echo json_encode([
                'result' => 'success',
                'response' => 'OK',
            ]);
        } else {
            echo json_encode([
                'result' => 'error',
                'response' => 'Something went wrong',
            ]);
        }
    break;
	case 'addLike':
	$arr = $arr["data"];

        $fromId = $arr["fromId"];
        $toId = $arr["toId"];

        $id = -1;
		$result = $db->query("SELECT id FROM tinder_matching WHERE fromUser = {$toId} AND toUser = {$fromId}");
            while ($row = $result->fetch_assoc()) {
                $id = $row["id"];
            }

        if($id == -1){
            $resultInsert = $db->query("INSERT INTO tinder_matching (fromUser, toUser, isMatched) VALUES ('{$fromId}', '{$toId}', 0)");
            if ($resultInsert) {
                echo json_encode([
                    'result' => 'success',
                    'message' => "ok insert",
                ]);
            } else {
                header("HTTP/1.1 500 ERROR");
                echo json_encode([
                    'result' => 'error',
                    'message' => 'error in insert',
                ]);
            }
        } else {
            $resultUpdate = $db->query("UPDATE tinder_matching SET isMatched = 1 WHERE id = {$id}");

            if ($resultUpdate) {
                echo json_encode([
                    'result' => 'success',
                    'message' => "ok update",
                ]);
            } else {
                header("HTTP/1.1 500 ERROR");
                echo json_encode([
                    'result' => 'error',
                    'message' => 'error in update',
                ]);
            }
        }
	break;
	case 'getLikes':
		 $result = $db->query("SELECT * FROM tinder_matching");
        $arr = [];
        while ($row = $result->fetch_assoc()) {
            $arr[] = [
                'id' => $row['id'],
                'fromUser' => $row['fromUser'],
                'toUser' => $row['toUser'],
                'isMatched' => $row['isMatched'],
            ];
        }
        echo json_encode([
            'result' => 'success',
            'response' => $arr,
        ]);
	break;
    case 'getLikesById':
        $arr = $arr["data"];

        $id = $arr["id"];
        $result = $db->query("SELECT a.id as id, a.fromUser as fromUser, a.toUser as toUser, a.isMatched as isMatched, b.name as name, ". 
            "b.photo as photo, b.surname as surname FROM tinder_matching as a INNER JOIN tinder_users as b ON (b.id = a.toUser) WHERE a.isMatched = 1 AND (a.fromUser = {$id} OR a.toUser = {$id})");
        $arr = [];
        while ($row = $result->fetch_assoc()) {
            $arr[] = [
                'id' => $row['id'],
                'fromUser' => $row['fromUser'],
                'toUser' => $row['toUser'],
                'isMatched' => $row['isMatched'],
                'photo' => $row['photo'],
                'name' => $row['name'],
                'surname' => $row['surname'],
            ];
        }
        echo json_encode([
            'result' => 'success',
            'response' => $arr,
        ]);
        break;
    case 'getChats':
        $arr = $arr['data'];

        $user_id = isset($arr['user_id']) ? $arr['user_id'] : 0;
        $user2_id = isset($arr['user2_id']) ? $arr['user2_id'] : 0;

        $result = $db->query("SELECT * FROM chat WHERE user_id = {$user_id} OR user2_id = {$user_id}");
        $arr = [];
        while ($row = $result->fetch_assoc()) {
            $arr[] = [
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'chat_name' => $row['chat_name'],
                'user2_id' => $row['user2_id']
            ];
        }

        echo json_encode([
            'result' => 'success',
            'response' => $arr
        ]);
        break;
    case 'createChat':
        $arr = $arr['data'];
        $user_id = $arr['user_id'];
        $user2_id = $arr['user2_id'];
        $chat_name = $arr['chat_name'];

        if ($db->query("INSERT INTO chat (user_id, user2_id, chat_name) VALUES ({$user_id}, {$user2_id}, '{$chat_name}')")) {
            echo json_encode([
                'result' => 'success',
                'response' => 'OK'
            ]);
        } else {
            echo json_encode([
                'result' => 'error',
                'response' => 'Error'
            ]);
        }
        
        break;
    case 'sendMessage':
        $arr = $arr['data'];

        $message = $arr['message'];
        $chatid = $arr['chatid'];
        $userid = $arr['userid'];
        $to_user_id = $arr['to_user_id'];

        if ($db->query("INSERT INTO tinder_messages (message, chatid, userid, to_user_id, sent_time) VALUES ('{$message}', {$chatid}, {$userid}, {$to_user_id}, NOW())")) {
            echo json_encode([
                'result' => 'success',
                'response' => 'OK'
            ]);
        } else {
            echo json_encode([
                'result' => 'error',
                'response' => 'Error'
            ]);
        }
        break;
    case 'getChatMessages':
        $arr = $arr['data'];

        $chatid = $arr['chatid'];

        $result = $db->query("SELECT * FROM tinder_messages WHERE chatid = {$chatid}");
        $arr = [];
        while ($row = $result->fetch_assoc()) {
            $arr[] = [
                'message' => $row['message'],
                'chatid' => $row['chatid'],
                'userid' => $row['userid'],
                'to_user_id' => $row['to_user_id'],
                'sent_time' => $row['sent_time'],
            ];
        }

        echo json_encode([
            'result' => 'success',
            'response' => $arr
        ]);
    break;
    
}