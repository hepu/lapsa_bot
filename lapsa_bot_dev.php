<?php

define('BOT_TOKEN', '248896479:AAE7NqGo4JzQybBW9Q3XTxnQ7sBSRCHfEX4');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

function apiRequestWebhook($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  $parameters["method"] = $method;

  header("Content-Type: application/json");
  echo json_encode($parameters);
  return true;
}

function exec_curl_request($handle) {
  $response = curl_exec($handle);

  if ($response === false) {
    $errno = curl_errno($handle);
    $error = curl_error($handle);
    error_log("Curl returned error $errno: $error\n");
    curl_close($handle);
    return false;
  }

  $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
  curl_close($handle);

  if ($http_code >= 500) {
    // do not wat to DDOS server if something goes wrong
    sleep(10);
    return false;
  } else if ($http_code != 200) {
    $response = json_decode($response, true);
    error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
    if ($http_code == 401) {
      throw new Exception('Invalid access token provided');
    }
    return false;
  } else {
    $response = json_decode($response, true);
    if (isset($response['description'])) {
      error_log("Request was successfull: {$response['description']}\n");
    }
    $response = $response['result'];
  }

  return $response;
}

function apiRequest($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  foreach ($parameters as $key => &$val) {
    // encoding to JSON array parameters, for example reply_markup
    if (!is_numeric($val) && !is_string($val)) {
      $val = json_encode($val);
    }
  }
  $url = API_URL.$method.'?'.http_build_query($parameters);

  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);

  return exec_curl_request($handle);
}

function apiRequestJson($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  $parameters["method"] = $method;

  $handle = curl_init(API_URL);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);
  curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
  curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

  return exec_curl_request($handle);
}

function processMessage($message) {
  // process incoming message
  $message_id = $message['message_id'];
  $chat_id = $message['chat']['id'];
  $usuario = "@".$message['from']['username'];
  if (isset($message['text'])) {
    // incoming text message
	
	$random_risa = "";
	$random_num_risa = rand(1,120);
	if($random_num_risa >= 1 && $random_num_risa <= 10){
		$random_risa = 'jaja';
	}else if($random_num_risa > 10 && $random_num_risa <= 20){
		$random_risa = 'jajaja';
	}else if($random_num_risa > 20 && $random_num_risa <= 30){
		$random_risa = 'jajajaja!';
	}else if($random_num_risa > 30 && $random_num_risa <= 37){
		$random_risa = 'lol';
	}else if($random_num_risa > 37 && $random_num_risa <= 43){
		$random_risa = 'xD';
	}else if($random_num_risa > 43 && $random_num_risa <= 48){
		$random_risa = 'lol xD';
	}else if($random_num_risa > 48 && $random_num_risa <= 53){
		$random_risa = 'lmao';
	}else if($random_num_risa > 53 && $random_num_risa <= 57){
		$random_risa = 'ROFL';
	}else if($random_num_risa > 57 && $random_num_risa <= 60){
		$random_risa = 'ROFLMAO';
	}else if($random_num_risa > 60 && $random_num_risa <= 62){
		$random_risa = 'jajajajaghagajaja';
	}else if($random_num_risa > 62 && $random_num_risa <= 67){
		$random_risa = 'Huehue';
	}else if($random_num_risa > 67 && $random_num_risa <= 71){
		$random_risa = 'Huehuehue';
	}
	
	$link = mysqli_connect('roadadventurerus.ipagemysql.com', 'lapsa_bot', 'lapsa123','lapsa_bot');
	if (!$link) {
		apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => 'Could not connect: ' . mysqli_error()));
	}
	
	$respuesta_query = "SELECT respuesta, 
	(select sum(valor) FROM lapsa_bot.palabras WHERE lower('".$message['text']."') like concat('%',lower(palabra),'%')) as total
	FROM lapsa_bot.palabras WHERE lower('".$message['text']."') like concat('%',lower(palabra),'%')
	order by -log(rand())/valor
	limit 1";
	
	$result = mysqli_query($link,$respuesta_query);
	$array = mysqli_fetch_assoc($result);
	$sw2 = 0;
	
	if($array['total'] < 1000){
		$random_action = rand(1,1000);
		if($array['total'] >= $random_action){
			$sw2 = 1;
			apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => 'Se mostrara rand: '.$random_action.' - respuesta: '.$array['respuesta'].' - Total: '.$array['total']));
		}
	}else{
		$random_action = rand(1,$array['total']+100);
		if($array['total'] >= $random_action){
			$sw2 = 1;
			apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => 'Se mostrara rand: '.$random_action.' - respuesta: '.$array['respuesta'].' - Total: '.$array['total']));
		}
	}
	
	
	if($sw2 == 0){
		$random_action = rand(1,1000);
		if($random_action >= 1 && $random_action <= 300){
			//Translajan
			$array_texto = explode(' ',$message['text']);
		
			$sw = 0;
			$palabra_translajan = "";
			for($i = 0; $i < sizeof($array_texto);$i++){
				if(substr($array_texto[$i], -2) == "ar" || substr($array_texto[$i], -2) == "er" || substr($array_texto[$i], -2) == "ir"){
					$array_texto[$i] = substr($array_texto[$i],0,strlen($array_texto[$i])-1)."jan";
					$sw = 1;
					$random_action_2 = rand(1,100);
					if($random_action_2 >= 1 && $random_action_2 <= 40){
						$palabra_translajan = $array_texto[$i];
					}
				}
			}
			$newtexto = implode(' ',$array_texto);
			
			//Solo si se hizo algun reemplazo, se mandara mensaje.
			if($sw == 1){
				if($palabra_translajan != ""){
					apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => $palabra_translajan." ".$random_risa));
				}else{
					apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => $newtexto." ".$random_risa));
				}
			}
		}else if($random_action > 301 && $random_action <= 335){
			//Transla iiiii
			$vowels_lower = array("a", "e", "o", "u", "á", "é", "ó", "ú");
			$vowels_upper = array("A", "E", "O", "U", "Á", "É", "Ó", "Ú");
			$newtexto = str_replace($vowels_lower, "i", $message['text']);
			$newtexto = str_replace($vowels_upper, "I", $newtexto);
			apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "text" => $newtexto));
		}else if($random_action > 301 && $random_action <= 400){
			$array_texto = explode(' ',$message['text']);
		
			$sw = 0;
			for($i = 0; $i < sizeof($array_texto);$i++){
				if(substr($array_texto[$i], 0, 2) == "8=" && substr($array_texto[$i], -2) == "=D"){
					$sw = 1;
				}
			}
			if($sw == 1){
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $usuario.' serás mkite'));
			}
		}else if($random_action > 501 && $random_action <= 520){
			apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Ichii'));
		}else if($random_action > 601 && $random_action <= 650){
			apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'Random encuesta: ¿Si estas encerrado en un closet con un gay que haces?', 'reply_markup' => array(
			'keyboard' => array(array('1. Te quedas con el gay.', '2. Te sales del closet.')),
			'one_time_keyboard' => true,
			'resize_keyboard' => true)));
		}
	}
	
	
    /*if (strpos($text, "/start") === 0) {
      apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'Hello', 'reply_markup' => array(
        'keyboard' => array(array('Hello', 'Hi')),
        'one_time_keyboard' => true,
        'resize_keyboard' => true)));
    } else if ($text === "Hello" || $text === "Hi") {
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Nice to meet you'));
    } else if (strpos($text, "/stop") === 0) {
      // stop now
    } else {
      apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => 'Cool'));
    }*/
  } else {
	//Es imagen o emoji
	$random_action = rand(1,320);
	if($random_action >= 1 && $random_action <= 20){
		apiRequest("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => '#miraTuEsaVaina'));
	}else if($random_action > 20 && $random_action <= 30){
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $usuario.' serás mkite'));
	}else if($random_action > 30 && $random_action <= 40){
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Como cosa rara, '.$usuario.' mandando vainas chirris'));
	}else if($random_action > 40 && $random_action <= 60){
		apiRequest("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => '#porVainasAsi'));
	}
    
  }
}


define('WEBHOOK_URL', 'https://my-site.example.com/secret-path-for-webhooks/');

if (php_sapi_name() == 'cli') {
  // if run from console, set or delete webhook
  apiRequest('setWebhook', array('url' => isset($argv[1]) && $argv[1] == 'delete' ? '' : WEBHOOK_URL));
  exit;
}


$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
  // receive wrong update, must not happen
  exit;
}

if (isset($update["message"])) {
		processMessage($update["message"]);
}

?>