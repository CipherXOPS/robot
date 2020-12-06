<?php
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
		$boolean = file_get_contents('booleans.txt');
		$booleans= explode("\n",$boolean);
		$admin = 1116598525;
		$message_id = $message['message_id'];
		$rpto = $message['reply_to_message']['forward_from']['id'];
		$chat_id = $message['chat']['id'];
		$txxxtt = file_get_contents('msgs.txt');
		$pmembersiddd= explode("-!-@-#-$",$txxxtt);
		$status = file_get_contents('setting.txt');
		$statuspm= explode("\n",$status);
		
		if (isset($message['photo'])) {
			
			if ( $chat_id != $admin) {
				
				if ($statuspm[1]=="✅") {
					$txt = file_get_contents('banlist.txt');
					$membersid= explode("\n",$txt);					
					$substr = substr($text, 0, 28);
					
					if (!in_array($chat_id,$membersid)) {
						apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));	
						apiRequest("forwardMessage", array('chat_id' => $admin,  "from_chat_id"=> $chat_id ,"message_id" => $message_id));
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $pmembersiddd[1] ,"parse_mode" =>"HTML"));	
					}
					else{
						
						apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));	
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "متاسفانه شما بلاک شده اید و نمی توانید پیام ارسال کنید" ,"parse_mode" =>"HTML"));	
						
					}
				}
				else{
					apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));	
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "متاسفانه امکان ارسال تصویر وجود ندارد" ,"parse_mode" =>"HTML"));	
				}
			}
			else if($rpto !="" && $chat_id==$admin){
				$photo = $message['photo'];
				$photoid = json_encode($photo, JSON_PRETTY_PRINT);
				$photoidd = json_encode($photoid, JSON_PRETTY_PRINT); 
				$photoidd = str_replace('"[\n    {\n        \"file_id\": \"','',$photoidd);
				$pos = strpos($photoidd, '",\n');
				$pos = $pos -1;
				$substtr = substr($photoidd, 0, $pos);
				$caption = $message['caption'];
				if($caption != "")
				{
					apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "upload_photo"));	
					apiRequest("sendphoto", array('chat_id' => $rpto, "photo" => $substtr,"caption" =>$caption));
				}
				else{
					apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "upload_photo"));	
					apiRequest("sendphoto", array('chat_id' => $rpto, "photo" => $substtr));
				}
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پيام شما ارسال شد." ,"parse_mode" =>"HTML"));
				
			}  
			else if ($chat_id == $admin && $booleans[0] == "true") {
				
				$photo = $message['photo'];
				$photoid = json_encode($photo, JSON_PRETTY_PRINT);
				$photoidd = json_encode($photoid, JSON_PRETTY_PRINT); 
				$photoidd = str_replace('"[\n    {\n        \"file_id\": \"','',$photoidd);
				$pos = strpos($photoidd, '",\n');
				$pos = $pos -1;
				$substtr = substr($photoidd, 0, $pos);
				$caption = $message['caption'];
				
				
				$ttxtt = file_get_contents('pmembers.txt');
				$membersidd= explode("\n",$ttxtt);
				for($y=0;$y<count($membersidd);$y++){
					if($caption != "")
					{
						apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "upload_photo"));	
						apiRequest("sendphoto", array('chat_id' => $membersidd[$y], "photo" => $substtr,"caption" =>$caption));
					}
					else{
						apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "upload_photo"));	
						apiRequest("sendphoto", array('chat_id' => $membersidd[$y], "photo" => $substtr));
					}
					
				}
				$memcout = count($membersidd)-1;
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پيام شما به  ".$memcout." مخاطب ارسال شد","parse_mode" =>"HTML",'reply_markup' => array(
				'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
				'selective' => true,
				'resize_keyboard' => true)));
				$addd = "false";
				file_put_contents('booleans.txt',$addd); 
			}
		}
		if (isset($message['video'])) {
			
			if ( $chat_id != $admin) {
				
				if ($statuspm[2]=="✅") {
					$txt = file_get_contents('banlist.txt');
					$membersid= explode("\n",$txt);
					
					$substr = substr($text, 0, 28);
					if (!in_array($chat_id,$membersid)) {
						apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
						apiRequest("forwardMessage", array('chat_id' => $admin,  "from_chat_id"=> $chat_id ,"message_id" => $message_id));
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $pmembersiddd[1],"parse_mode" =>"HTML"));	
						}else{
						apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "متاسفانه شما بلاک شده اید و نمی توانید پیام ارسال کنید" ,"parse_mode" =>"HTML"));	
						
					}
				}
				else{
					apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));	
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "متاسفانه امکان ارسال ویدئو وجود ندارد" ,"parse_mode" =>"HTML"));	
				}
			}
			else if($rpto !="" && $chat_id==$admin){
				$video = $message['video']['file_id'];
				$caption = $message['caption'];
				if($caption != "")
				{
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"upload_video"));
					apiRequest("sendvideo", array('chat_id' => $rpto, "video" => $video,"caption" =>$caption));
				}
				else{
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"upload_video"));
					apiRequest("sendvideo", array('chat_id' => $rpto, "video" => $video));
				}
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" =>"پيام شما ارسال شد. ","parse_mode" =>"HTML"));
				
			}
			else if ($chat_id == $admin && $booleans[0] == "true") {
				$video = $message['video']['file_id'];
				$caption = $message['caption'];
				$ttxtt = file_get_contents('pmembers.txt');
				$membersidd= explode("\n",$ttxtt);
				for($y=0;$y<count($membersidd);$y++){
					if($caption != "")
					{
						apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"upload_video"));
						apiRequest("sendvideo", array('chat_id' => $membersidd[$y], "video" => $video,"caption" =>$caption));
					}
					else{
						apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"upload_video"));
						apiRequest("sendvideo", array('chat_id' => $membersidd[$y], "video" => $video));
					}
				}
				$memcout = count($membersidd)-1;
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پيام شما به  ".$memcout." مخاطب ارسال شد","parse_mode" =>"HTML",'reply_markup' => array(
				'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
				'selective' => true,
				'resize_keyboard' => true)));
				$addd = "false";
				file_put_contents('booleans.txt',$addd); 
			}
		}
		if (isset($message['sticker'])) {
			
			if ( $chat_id != $admin) {
				if ($statuspm[0]=="✅") {
					$txt = file_get_contents('banlist.txt');
					$membersid= explode("\n",$txt);
					
					$substr = substr($text, 0, 28);
					if (!in_array($chat_id,$membersid)) {
						apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
						apiRequest("forwardMessage", array('chat_id' => $admin,  "from_chat_id"=> $chat_id ,"message_id" => $message_id));
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $pmembersiddd[1] ,"parse_mode" =>"HTML"));	
					}
					else{
						
						apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "متاسفانه شما بلاک شده اید و نمی توانید پیام ارسال کنید" ,"parse_mode" =>"HTML"));	
						
					}
				}
				else{
					apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));	
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "متاسفانه امکان ارسال استیکر وجود ندارد" ,"parse_mode" =>"HTML"));	
				}
			}
			else if($rpto !="" && $chat_id==$admin){
				$sticker = $message['sticker']['file_id'];
				
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequest("sendsticker", array('chat_id' => $rpto, "sticker" => $sticker));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" =>"پيام شما ارسال شد. " ,"parse_mode" =>"HTML"));
				
			}
			
			else if ($chat_id == $admin && $booleans[0] == "true") {
				$sticker = $message['sticker']['file_id'];
				$ttxtt = file_get_contents('pmembers.txt');
				$membersidd= explode("\n",$ttxtt);
				for($y=0;$y<count($membersidd);$y++){
					
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
					apiRequest("sendsticker", array('chat_id' => $membersidd[$y], "sticker" => $sticker));
					
					
					
				}
				$memcout = count($membersidd)-1;
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پيام شما به  ".$memcout." مخاطب ارسال شد","parse_mode" =>"HTML",'reply_markup' => array(
				'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
				'selective' => true,
				'resize_keyboard' => true)));
				$addd = "false";
				file_put_contents('booleans.txt',$addd); 
			}
		}		
		if (isset($message['document'])) {
			
			if ( $chat_id != $admin) {
				if ($statuspm[5]=="✅") {
					$txt = file_get_contents('banlist.txt');
					$membersid= explode("\n",$txt);
					
					$substr = substr($text, 0, 28);
					if (!in_array($chat_id,$membersid)) {
						apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
						apiRequest("forwardMessage", array('chat_id' => $admin,  "from_chat_id"=> $chat_id ,"message_id" => $message_id));
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $pmembersiddd[1],"parse_mode" =>"HTML"));	
						}else{
						
						apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "متاسفانه شما بلاک شده اید و نمی توانید پیام ارسال کنید" ,"parse_mode" =>"HTML"));	
						
					}
				}
				else{
					apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));	
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "متاسفانه امکان ارسال فایل وجود ندارد" ,"parse_mode" =>"HTML"));	
				}
			}
			else if($rpto !="" && $chat_id==$admin){
				$video = $message['document']['file_id'];
				$caption = $message['caption'];
				if($caption != "")
				{
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"upload_document"));
					apiRequest("sendDocument", array('chat_id' => $rpto, "document" => $video,"caption" =>$caption));
				}
				else{
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"upload_document"));
					apiRequest("sendDocument", array('chat_id' => $rpto, "document" => $video));
				}
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پيام شما ارسال شد. " ,"parse_mode" =>"HTML"));
				
			}
			else if ($chat_id == $admin && $booleans[0] == "true") {
				$video = $message['document']['file_id'];
				$ttxtt = file_get_contents('pmembers.txt');
				$membersidd= explode("\n",$ttxtt);
				for($y=0;$y<count($membersidd);$y++){
					
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"upload_document"));
					apiRequest("sendDocument", array('chat_id' => $membersidd[$y], "document" => $video));
					
				}
				$memcout = count($membersidd)-1;
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پيام شما به  ".$memcout." مخاطب ارسال شد","parse_mode" =>"HTML",'reply_markup' => array(
				'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
				'selective' => true,
				'resize_keyboard' => true)));
				$addd = "false";
				file_put_contents('booleans.txt',$addd); 
			}
		}
		if (isset($message['voice'])) {
			
			if ( $chat_id != $admin) {
				if ($statuspm[3]=="✅") {
					$txt = file_get_contents('banlist.txt');
					$membersid= explode("\n",$txt);
					
					$substr = substr($text, 0, 28);
					if (!in_array($chat_id,$membersid)) {
						apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
						apiRequest("forwardMessage", array('chat_id' => $admin,  "from_chat_id"=> $chat_id ,"message_id" => $message_id));
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $pmembersiddd[1] ,"parse_mode" =>"HTML"));	
						}else{
						
						apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "متاسفانه شما بلاک شده اید و نمی توانید پیام ارسال کنید" ,"parse_mode" =>"HTML"));	
						
					}}
					else{
						apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));	
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "متاسفانه امکان ارسال ویس وجود ندارد" ,"parse_mode" =>"HTML"));	
					}
			}
			else if($rpto !="" && $chat_id==$admin){
				$video = $message['voice']['file_id'];
				$caption = $message['caption'];
				if($caption != "")
				{
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"upload_audio"));
					apiRequest("sendVoice", array('chat_id' => $rpto, "voice" => $video,"caption" =>$caption));
				}
				else{
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"upload_audio"));
					apiRequest("sendVoice", array('chat_id' => $rpto, "voice" => $video));
				}
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" =>"پيام شما ارسال شد. ","parse_mode" =>"HTML"));
				
			}
			else if ($chat_id == $admin && $booleans[0] == "true") {
				$video = $message['voice']['file_id'];
				$ttxtt = file_get_contents('pmembers.txt');
				$membersidd= explode("\n",$ttxtt);
				for($y=0;$y<count($membersidd);$y++){
					
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"upload_audio"));
					apiRequest("sendVoice", array('chat_id' => $membersidd[$y], "voice" => $video));
				}
				$memcout = count($membersidd)-1;
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => " پيام شما به  ".$memcout." مخاطب ارسال شد","parse_mode" =>"HTML",'reply_markup' => array(
				'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
				'selective' => true,
				'resize_keyboard' => true)));
				$addd = "false";
				file_put_contents('booleans.txt',$addd); 
			}
		}
		if (isset($message['audio'])) {
			
			if ( $chat_id != $admin) {
				if ($statuspm[4]=="✅") {
					$txt = file_get_contents('banlist.txt');
					$membersid= explode("\n",$txt);
					
					$substr = substr($text, 0, 28);
					if (!in_array($chat_id,$membersid)) {
						apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
						apiRequest("forwardMessage", array('chat_id' => $admin,  "from_chat_id"=> $chat_id ,"message_id" => $message_id));
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $pmembersiddd[1] ,"parse_mode" =>"HTML"));	
						}else{
						
						apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "متاسفانه شما بلاک شده اید و نمی توانید پیام ارسال کنید" ,"parse_mode" =>"HTML"));	
						
					}
				}
				else{
					apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));	
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "متاسفانه امکان ارسال موزیک وجود ندارد" ,"parse_mode" =>"HTML"));	
				}
			}
			else if($rpto !="" && $chat_id==$admin){
				$video = $message['audio']['file_id'];
				$caption = $message['caption'];
				if($caption != "")
				{
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"upload_audio"));
					apiRequest("sendaudio", array('chat_id' => $rpto, "audio" => $video,"caption" =>$caption));
				}
				else{
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"upload_audio"));
					apiRequest("sendaudio", array('chat_id' => $rpto, "audio" => $video));
				}
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پيام شما ارسال شد. " ,"parse_mode" =>"HTML"));
				
			}
			else if ($chat_id == $admin && $booleans[0] == "true") {
				$video = $message['audio']['file_id'];
				$ttxtt = file_get_contents('pmembers.txt');
				$membersidd= explode("\n",$ttxtt);
				for($y=0;$y<count($membersidd);$y++){
					
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"upload_audio"));
					apiRequest("sendaudio", array('chat_id' => $membersidd[$y], "audio" => $video));
					
				}
				$memcout = count($membersidd)-1;
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پيام شما به  ".$memcout." مخاطب ارسال شد","parse_mode" =>"HTML",'reply_markup' => array(
				'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
				'selective' => true,
				'resize_keyboard' => true)));
				$addd = "false";
				file_put_contents('booleans.txt',$addd); 
			}
		}
		if (isset($message['contact'])) {
			
			if ( $chat_id != $admin) {
				
				$txt = file_get_contents('banlist.txt');
				$membersid= explode("\n",$txt);
				
				$substr = substr($text, 0, 28);
				if (!in_array($chat_id,$membersid)) {
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
					apiRequest("forwardMessage", array('chat_id' => $admin,  "from_chat_id"=> $chat_id ,"message_id" => $message_id));
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $pmembersiddd[1] ,"parse_mode" =>"HTML"));	
					}else{
					
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "متاسفانه شما بلاک شده اید و نمی توانید پیام ارسال کنید" ,"parse_mode" =>"HTML"));	
					
				}
			}
			else if($rpto !="" && $chat_id==$admin){
				$phone = $message['contact']['phone_number'];
				$first = $message['contact']['first_name'];
				
				$last = $message['contact']['last_name'];
				
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));		
				apiRequest("sendcontact", array('chat_id' => $rpto, "phone_number" => $phone,"Last_name" =>$last,"first_name"=> $first));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" =>"پيام شما ارسال شد. ","parse_mode" =>"HTML"));
				
			}
			else if ($chat_id == $admin && $booleans[0] == "true") {
				$phone = $message['contact']['phone_number'];
				$first = $message['contact']['first_name'];
				
				$last = $message['contact']['last_name'];
				$ttxtt = file_get_contents('pmembers.txt');
				$membersidd= explode("\n",$ttxtt);
				for($y=0;$y<count($membersidd);$y++){
					
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
					apiRequest("sendcontact", array('chat_id' => $membersidd[$y], "phone_number" => $phone,"Last_name" =>$last,"first_name"=> $first));
					
				}
				$memcout = count($membersidd)-1;
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پيام شما به  ".$memcout." مخاطب ارسال شد","parse_mode" =>"HTML",'reply_markup' => array(
				'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
				'selective' => true,
				'resize_keyboard' => true)));
				$addd = "false";
				file_put_contents('booleans.txt',$addd); 
			}
		}	
		if (isset($message['text'])) {
			// incoming text message
			$text = $message['text'];
			$matches = explode(" ", $text); 
			
			if ($text=="/start") {
				
				if($chat_id!=$admin){
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
					apiRequest("sendMessage", array('chat_id' => $chat_id,"text"=>$pmembersiddd[0] ,"parse_mode"=>"HTML"));
					
					$txxt = file_get_contents('pmembers.txt');
					$pmembersid= explode("\n",$txxt);
					if (!in_array($chat_id,$pmembersid)) {
						$aaddd = file_get_contents('pmembers.txt');
						$aaddd .= $chat_id."\n";
						file_put_contents('pmembers.txt',$aaddd);
					}
					
				}
				if($chat_id==$admin){
					apiRequestJson("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
					apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'براي پاسخ روي پيام مورد نظر ريپلاي کنيد و متن خود را بنويسيد',"parse_mode"=>"MARKDOWN", 'reply_markup' => array(
					'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
					'selective' => true,
					'resize_keyboard' => true)));				
					$addd = "false";
					file_put_contents('booleans.txt',$addd); 
				}
				
			}
			else if ($text =="📣 ویرایش پیام شروع"  && $chat_id == $admin && $booleans[0]=="false") {
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پیام شروع فعلی شما :
				
				".str_replace("\n","",$pmembersiddd[0])."
				
				لطفا پیام شروع جدید را ارسال کنید یا برای بازگشت ⏹ لغو را بزنید.","parse_mode" =>"HTML",'reply_markup' => array(
				'keyboard' => array(array('⏹ لغو')),
				'selective' => true,
				'resize_keyboard' => true)));
				
				$boolean = file_get_contents('booleans.txt');
				$booleans= explode("\n",$boolean);
				$addd = file_get_contents('banlist.txt');
				$addd = "matenestart";
				file_put_contents('booleans.txt',$addd);
				
			}
			else if ($chat_id == $admin && $booleans[0] == "matenestart") {
				
				if ($text =="⏹ لغو"  && $chat_id == $admin) {
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "ویرایش پیام شروع لغو شد","parse_mode" =>"HTML",'reply_markup' => array(
					'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
					'selective' => true,
					'resize_keyboard' => true)));
					$addd = "false";
					file_put_contents('booleans.txt',$addd); 
				}
				else{
					$starttext = str_replace("/setstart","",$text);
					
					file_put_contents('msgs.txt',$starttext."
					
					-!-@-#-$"."
					".$pmembersiddd[1]);
					apiRequestJson("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پیام شروع شما به \"".$starttext."\" تغییر پیدا کرد.","parse_mode","parse_mode" =>"HTML",'reply_markup' => array(
					'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
					'selective' => true,
					'resize_keyboard' => true)));
					$addd = "false";
					file_put_contents('booleans.txt',$addd); 
				}
			}
			else if ($text =="📣 ویرایش پیام پیشفرض"  && $chat_id == $admin && $booleans[0]=="false") {
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پیام پیشفرض فعلی شما :
				
				".str_replace("\n","",$pmembersiddd[1])."
				
				لطفا پیام پیشفرض جدید را ارسال کنید یا برای بازگشت ⏹ لغو را بزنید.","parse_mode" =>"HTML",'reply_markup' => array(
				'keyboard' => array(array('⏹ لغو')),
				'selective' => true,
				'resize_keyboard' => true)));
				
				$boolean = file_get_contents('booleans.txt');
				$booleans= explode("\n",$boolean);
				$addd = file_get_contents('banlist.txt');
				$addd = "mateneok";
				file_put_contents('booleans.txt',$addd);
				
			}
			else if ($chat_id == $admin && $booleans[0] == "mateneok") {
				
				if ($text =="⏹ لغو"  && $chat_id == $admin) {
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "ویرایش پیام پیشفرض لغو شد","parse_mode" =>"HTML",'reply_markup' => array(
					'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
					'selective' => true,
					'resize_keyboard' => true)));
					$addd = "false";
					file_put_contents('booleans.txt',$addd); 
				}
				else{
					$starttext = str_replace("/setstart","",$text);
					
					file_put_contents('msgs.txt',$pmembersiddd[0]."
					
					-!-@-#-$"."
					".$starttext);
					apiRequestJson("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پيام پيش فرض شما به \"".$starttext."\" تغییر پیدا کرد.","parse_mode" =>"HTML",'reply_markup' => array(
					'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
					'selective' => true,
					'resize_keyboard' => true)));
					$addd = "false";
					file_put_contents('booleans.txt',$addd); 
				}
			}
			else if ($text != "" && $chat_id != $admin) {
				
				$txt = file_get_contents('banlist.txt');
				$membersid= explode("\n",$txt);
				
				$substr = substr($text, 0, 28);
				if (!in_array($chat_id,$membersid)) {
					apiRequest("forwardMessage", array('chat_id' => $admin,  "from_chat_id"=> $chat_id ,"message_id" => $message_id));
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" =>$pmembersiddd[1] ,"parse_mode" =>"HTML"));	
					
				}
				else{
					if($substr !="thisisnarimanfrombeatbotteam"){
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "متاسفانه شما بلاک شده اید و نمی توانید پیام ارسال کنید" ,"parse_mode" =>"HTML"));	
					}
					else{
						$textfa =str_replace("thisisnarimanfrombeatbotteam","??",$text);
						apiRequest("sendMessage", array('chat_id' => $admin, "text" =>  $textfa,"parse_mode" =>"HTML"));	
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $pmembersiddd[1] ,"parse_mode" =>"HTML"));		
						
					}
				}
				
				
			}
			else if ($text == "⚙ تنظیمات" && $chat_id==$admin) {
				$status = file_get_contents('setting.txt');
				$statuspm= explode("\n",$status);
				$keyboard = array(
				'inline_keyboard' => array(
				[
				['text'=>'تنظیمات دریافت پیام','callback_data'=>'setting']
				],
				[
				['text'=>$statuspm[0],'callback_data'=>'stickerstatus'],
				['text'=>'دسترسی استیکر','callback_data'=>'stickername']
				],
				[
				['text'=>$statuspm[1],'callback_data'=>'imagestatus'],
				['text'=>'دسترسی عکس','callback_data'=>'imagename']
				],
				[
				['text'=>$statuspm[2],'callback_data'=>'videostatus'],
				['text'=>'دسترسی ویدئو','callback_data'=>'videoname']
				],
				[
				['text'=>$statuspm[3],'callback_data'=>'voicestatus'],
				['text'=>'دسترسی ویس','callback_data'=>'voicename']
				],
				[
				['text'=>$statuspm[4],'callback_data'=>'musicstatus'],
				['text'=>'دسترسی موزیک','callback_data'=>'musicname']
				],
				[
				['text'=>$statuspm[5],'callback_data'=>'documentstatus'],
				['text'=>'دسترسی فایل','callback_data'=>'documentname']
				],
				[
				['text'=>'سایر تنظیمات','callback_data'=>'advsetting']
				],
				[
				['text'=>'🗑','callback_data'=>'memberdelete'],
				['text'=>'پاک کردن اعضا','callback_data'=>'membername']
				],
				[
				['text'=>'🗑','callback_data'=>'banlistdelete'],
				['text'=>'پاک کردن لیست سیاه','callback_data'=>'banlistname']
				]
				)
				);
				apiRequest("sendMessage", array('chat_id' => $admin, "text" => "با استفاده از این بخش می توانید ربات خود را شخصی سازی #Devilfser کنید و بر پیام های دریافتی از سمت کاربران نظارت کنید\n🚫 = قفل شده\t\t✅ = آزاد","parse_mode" =>"MARKDOWN",'reply_markup' =>json_encode($keyboard) ));
				
				//apiRequestJson("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				//apiRequestJson("sendMessage", array('chat_id' => $chat_id,"parse_mode"=>"HTML", "text" => 'يکي از گزينه ها را انتخاب کنيد', //'reply_markup' => array(
				//'keyboard' => array(array('❌ پاک کردن اعضا','❌ پاک کردن لیست سیاه'),array('🔙 بازگشت')),
				//'selective' => true,
				//'resize_keyboard' => true)));
				
				
				
			}
			else if ($text == "⚓️ راهنما" && $chat_id==$admin) {
				$keyboard = array(
				'inline_keyboard' => array(
				[
				['text'=>'🔰 دستورات','callback_data'=>'command'],
				['text'=>'🔰 دکمه ها','callback_data'=>'button']
				]
				)
				);
				apiRequest("sendMessage", array('chat_id' => $admin, "text" => "- این ربات جهت راحتی شما و پشتیبانی از ربات،کانال،گروه یا حتی وبسایت شما ساخته شده است\nبرای مشاهده ی دستورات از دکمه های زیر استفاده کنید 👇\nCopy Right 2020 ©\nتیم اوج","parse_mode" =>"MARKDOWN",'reply_markup' =>json_encode($keyboard) ));
			}
			else if ($text == "🔙 بازگشت" && $chat_id==$admin) {
				apiRequestJson("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'براي پاسخ روي پيام مورد نظر ريپلاي کنيد و متن خود را بنويسيد', 'reply_markup' => array(
				'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
				'selective' => true,
				'resize_keyboard' => true))); 
			}
			else if ($text =="🗣 پیام همگانی"  && $chat_id == $admin && $booleans[0]=="false") {
				apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پیام خود را ارسال کنید . . . @Devilfser
				
				⚠️پیام شما میتواند متن , عکس , ویدیو , فایل(گیف یا هر فایل دیگر) , وویس , آهنگ  و کانتکت باشد
				
				برای بازگشت ⏹ لغو را بزنید.","parse_mode" =>"HTML",'reply_markup' => array(
				'keyboard' => array(array('⏹ لغو')),
				'selective' => true,
				'resize_keyboard' => true)));
				
				$boolean = file_get_contents('booleans.txt');
				$booleans= explode("\n",$boolean);
				$addd = file_get_contents('banlist.txt');
				$addd = "true";
				file_put_contents('booleans.txt',$addd);
				
			}
			else if ($chat_id == $admin && $booleans[0] == "true") {
				
				if ($text =="⏹ لغو"  && $chat_id == $admin) {
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پیام همگانی لغو شد","parse_mode" =>"HTML",'reply_markup' => array(
					'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
					'selective' => true,
					'resize_keyboard' => true)));
					$addd = "false";
					file_put_contents('booleans.txt',$addd); 
				}
				else{
					$texttoall =$text;
					$ttxtt = file_get_contents('pmembers.txt');
					$membersidd= explode("\n",$ttxtt);
					for($y=0;$y<count($membersidd);$y++){
						apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
						apiRequest("sendMessage", array('chat_id' => $membersidd[$y], "text" => $texttoall,"parse_mode" =>"HTML"));
					}
					$memcout = count($membersidd)-1;
					apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پيام شما به  ".$memcout." مخاطب ارسال شد","parse_mode" =>"HTML",'reply_markup' => array(
					'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
					'selective' => true,
					'resize_keyboard' => true)));
					$addd = "false";
					file_put_contents('booleans.txt',$addd); 
				}
			}
			else if($text == "👥 تعداد اعضا" && $chat_id == $admin ){
				$txtt = file_get_contents('pmembers.txt');
				$membersidd= explode("\n",$txtt);
				$mmemcount = count($membersidd) -1;
				apiRequestJson("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequestJson("sendMessage", array('chat_id' => $chat_id,"parse_mode" =>"HTML", "text" => "👥 تعداد کل کاربران : ".$mmemcount." نفر",'reply_markup' => array(
				'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
				'selective' => true,
				'resize_keyboard' => true)));
				
				
			}
			else if($text == "❌ لیست سیاه" && $chat_id == $admin ){
				$txtt = file_get_contents('banlist.txt');
				$membersidd= explode("\n",$txtt);
				$mmemcount = count($membersidd) -1;
				apiRequestJson("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
				apiRequestJson("sendMessage", array('chat_id' => $chat_id,"parse_mode" =>"HTML", "text" => "👥 تعداد کل کاربران بلاک شده : ".$mmemcount." نفر",'reply_markup' => array(
				'keyboard' => array(array('⚓️ راهنما','👥 تعداد اعضا','❌ لیست سیاه'),array('⚙ تنظیمات','🗣 پیام همگانی'),array('📣 ویرایش پیام پیشفرض','📣 ویرایش پیام شروع')),
				'selective' => true,
				'resize_keyboard' => true)));
				
				
			}
			else if($rpto != "" && $chat_id == $admin){
				if($text != "/ban" && $text != "/unban"){
					apiRequest("sendChatAction",array('chat_id'=>$rpto,"action"=>"typing"));
					apiRequest("sendMessage", array('chat_id' => $rpto, "text" => $text ,"parse_mode" =>"HTML"));
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "پيام شما ارسال شد" ,"parse_mode" =>"HTML"));
				}
				else{
					if($text == "/ban"){
						$txtt = file_get_contents('banlist.txt');
						$banid= explode("\n",$txtt);
						if (!in_array($rpto,$banid)) {
							$addd = file_get_contents('banlist.txt');
							$addd = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "", $addd);
							$addd .= $rpto."
							";
							
							file_put_contents('banlist.txt',$addd);
							apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
							apiRequest("sendMessage", array('chat_id' => $rpto, "text" => "
							شما در ليست سياه قرار گرفتيد" ,"parse_mode" =>"HTML"));
						}
						apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
						به ليست سياه افزوده شد" ,"parse_mode" =>"HTML"));
					}
					if($text == "/unban"){
						$txttt = file_get_contents('banlist.txt');
						$banidd= explode("\n",$txttt);
						if (in_array($rpto,$banidd)) {
							$adddd = file_get_contents('banlist.txt');
							$adddd = str_replace($rpto,"",$adddd);
							$adddd = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "", $adddd);
							$adddd .="
							";
							
							
							$banid= explode("\n",$adddd);
							if($banid[1]=="")
							$adddd = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "", $adddd);
							
							file_put_contents('banlist.txt',$adddd);
						}
						apiRequest("sendChatAction",array('chat_id'=>$chat_id,"action"=>"typing"));
						apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "از ليست سياه پاک شد" ,"parse_mode" =>"HTML"));
						apiRequest("sendMessage", array('chat_id' => $rpto, "text" => "شما از ليست سياه پاک شديد و می توانید پیام خود را ارسال کنید" ,"parse_mode" =>"HTML"));
					}
				}
			}
		} else {}
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
	///////////////////////////////////////////////////////////
	ob_start();
	
	function makeHTTPRequest($method,$datas=[]){
		$url = "https://api.telegram.org/bot".BOT_TOKEN."/".$method;
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($datas));
		$res = curl_exec($ch);
		if(curl_error($ch)){
			var_dump(curl_error($ch));
			}else{
			return json_decode($res);
		}
	}
	
	$callback_id   = $update['callback_query']['id'];
	$callback_data = $update['callback_query']['data'];
	$chatid        = $update['callback_query']['message']['chat']['id'];
	$messageid     = $update['callback_query']['message']['message_id'];
	$status = file_get_contents('setting.txt');
	$statuspm= explode("\n",$status);
	if($callback_data=="command" && $chat_id==$admin){
		var_dump(
		makeHTTPRequest('editMessageText',[
		'chat_id'=>$chatid,
		'message_id'=>$messageid,
		'text'=>"⚠ برای پاسخ به پیام های کاربران روی آن ها ریپلای کنید و پیام خود را ارسال کنید.\n🔰 لیست دستورات:\n\n1⃣ برای اضافه کردن کاربر به لیست سیاه\n/ban :\nروی پیام ریپلای کنید و  ban/ را ارسال کنید\n\n2⃣ برای پاک کردن کاربر از لیست سیاه\n/unban :\nروی پیام ریپلای کنید و  unban/ را ارسال کنید",
		'reply_markup'=>json_encode([
		'inline_keyboard'=>[
		[
		['text'=>"🔙 بازگشت",'callback_data'=>"back"],
		['text'=>'🔰 دکمه ها','callback_data'=>'button']
		]
		]
		])
		])
		);
		
	}
	else if($callback_data=="button" && $chat_id==$admin){
		var_dump(
		makeHTTPRequest('editMessageText',[
		'chat_id'=>$chatid,
		'message_id'=>$messageid,
		'text'=>"🔰 لیست دکمه ها\n\n🗣 پیام همگانی :\nارسال پیام به اعضا و گروه ها.\n\n⚙ تنظیمات :\nتنظیمات ربات.\n\n📣 ویرایش پیام شروع :\nویرایش پیام استارت ربات شما.\n\n📣 ویرایش پیام پیشفرض :\nویرایش پیام پیشفرض ربات شما.\n\n👥 تعداد اعضا :\nمشاهده ی تعداد اعضا و گروه ها.\n\n❌ لیست سیاه :\nمشاهده ی لیست سیاه.",
		'reply_markup'=>json_encode([
		'inline_keyboard'=>[
		[
		['text'=>"🔙 بازگشت",'callback_data'=>"back"],
		['text'=>'🔰 دستورات','callback_data'=>'command']
		]
		]
		])
		])
		);
	}
	else if($callback_data=="back" && $chat_id==$admin){
		var_dump(
		makeHTTPRequest('editMessageText',[
		'chat_id'=>$chatid,
		'message_id'=>$messageid,
		'text'=>"- این ربات جهت راحتی شما و پشتیبانی از ربات،کانال،گروه یا حتی وبسایت شما ساخته شده است\nبرای مشاهده ی دستورات از دکمه های زیر استفاده کنید 👇\nCopy Right 2020 ©\nUnix Team",
		'reply_markup'=>json_encode([
		'inline_keyboard'=>[
		[
		['text'=>'🔰 دستورات','callback_data'=>'command'],
		['text'=>'🔰 دکمه ها','callback_data'=>'button']
		]
		]
		])
		])
		);
	}
	else if($callback_data=="stickername" || $callback_data=="imagename" || $callback_data=="videoname" || $callback_data=="voicename" || $callback_data=="musicname" || $callback_data=="documentname" || $callback_data=="setting" || $callback_data=="advsetting" || $callback_data=="banlistname" || $callback_data=="membername" && $chat_id==$admin){
		$alert=false;
		if($callback_data=="stickername"){
			$callbackMessage = 'در صورت فعال بودن کاربر اجازه ارسال استیکر را برای شما خواهد داشت';
		}
		else if($callback_data=="imagename"){
			$callbackMessage = 'در صورت فعال بودن کاربر اجازه ارسال عکس را برای شما خواهد داشت';
		}
		else if($callback_data=="videoname"){
			$callbackMessage = 'در صورت فعال بودن کاربر اجازه ارسال ویدئو را برای شما خواهد داشت';
		}
		else if($callback_data=="voicename"){
			$callbackMessage = 'در صورت فعال بودن کاربر اجازه ارسال ویس را برای شما خواهد داشت';
		}
		else if($callback_data=="musicname"){
			$callbackMessage = 'در صورت فعال بودن کاربر اجازه ارسال موزیک را برای شما خواهد داشت';
		}
		else if($callback_data=="documentname"){
			$callbackMessage = 'در صورت فعال بودن کاربر اجازه ارسال فایل را برای شما خواهد داشت';
		}
		else if($callback_data=="setting"){
			$callbackMessage = 'در این قسمت می توانید نوع پیام های دریافتی توسط کاربر را مشخص کنید';
			$alert=true;
		}
		else if($callback_data=="advsetting"){
			$callbackMessage = 'در این قسمت می توانید تمامی اطلاعات را پاک کرده و به حالت اولیه بازگردانید';
			$alert=true;
		}
		else if($callback_data=="banlistname"){
			$callbackMessage = "برای پاک کردن تنها یک کاربر از این لیست از بخش راهنما استفاده کنید\n⚠ توجه نمایید که در صورت پاک کردن لیست کاربران سیاه قابلیت بازگردانی وجود ندارد!";
			$alert=true;
		}
		else if($callback_data=="membername"){
			$callbackMessage = "با استفاده از این بخش می توانید تمامی کاربران ربات خود را پاک نمایید\n⚠ توجه نمایید که در صورت پاک کردن لیست کاربران شما قابلیت بازگردانی وجود ندارد!";
			$alert=true;
		}
		
		var_dump(makeHTTPRequest('answerCallbackQuery',[
		'callback_query_id'=>$callback_id,
		'show_alert'=>$alert,
		'text'=>$callbackMessage
		]));
		
	}	
	else if($callback_data=="stickerstatus" || $callback_data=="imagestatus" || $callback_data=="videostatus" || $callback_data=="voicestatus" || $callback_data=="musicstatus" || $callback_data=="documentstatus" && $chat_id==$admin){
	
		if(($statuspm[0]=="🚫" && $callback_data=="stickerstatus") || ($statuspm[1]=="🚫" && $callback_data=="imagestatus") || ($statuspm[2]=="🚫" && $callback_data=="videostatus") || ($statuspm[3]=="🚫" && $callback_data=="voicestatus") || ($statuspm[4]=="🚫" && $callback_data=="musicstatus") || ($statuspm[5]=="🚫" && $callback_data=="documentstatus")){
			$newstatus="✅";
		}
		else if(($statuspm[0]=="✅" && $callback_data=="stickerstatus") || ($statuspm[1]=="✅" && $callback_data=="imagestatus") || ($statuspm[2]=="✅" && $callback_data=="videostatus") || ($statuspm[3]=="✅" && $callback_data=="voicestatus") || ($statuspm[4]=="✅" && $callback_data=="musicstatus") || ($statuspm[5]=="✅" && $callback_data=="documentstatus")){
			$newstatus="🚫";
		}
		else{
			$newstatus="🚫";
		} 
		
		if($callback_data=="stickerstatus"){
			file_put_contents('setting.txt',$newstatus."\n".$statuspm[1]."\n".$statuspm[2]."\n".$statuspm[3]."\n".$statuspm[4]."\n".$statuspm[5]);
		}
		else if($callback_data=="imagestatus"){
			file_put_contents('setting.txt',$statuspm[0]."\n".$newstatus."\n".$statuspm[2]."\n".$statuspm[3]."\n".$statuspm[4]."\n".$statuspm[5]);
		}
		else if($callback_data=="videostatus"){
			file_put_contents('setting.txt',$statuspm[0]."\n".$statuspm[1]."\n".$newstatus."\n".$statuspm[3]."\n".$statuspm[4]."\n".$statuspm[5]);
		}
		else if($callback_data=="voicestatus"){
			file_put_contents('setting.txt',$statuspm[0]."\n".$statuspm[1]."\n".$statuspm[2]."\n".$newstatus."\n".$statuspm[4]."\n".$statuspm[5]);
		}
		else if($callback_data=="musicstatus"){
			file_put_contents('setting.txt',$statuspm[0]."\n".$statuspm[1]."\n".$statuspm[2]."\n".$statuspm[3]."\n".$newstatus."\n".$statuspm[5]);
		}
		else if($callback_data=="documentstatus"){
			file_put_contents('setting.txt',$statuspm[0]."\n".$statuspm[1]."\n".$statuspm[2]."\n".$statuspm[3]."\n".$statuspm[4]."\n".$newstatus);
		}
		
		$status = file_get_contents('setting.txt');
		$statuspm= explode("\n",$status);
		var_dump(
		makeHTTPRequest('editMessageReplyMarkup',[
		'chat_id'=>$chatid,
		'message_id'=>$messageid,
		'reply_markup'=>json_encode([
		'inline_keyboard'=>[
		[
		['text'=>"تنظیمات دریافت پیام",'callback_data'=>"setting"]
		],
		[
		['text'=>$statuspm[0],'callback_data'=>"stickerstatus"],
		['text'=>'دسترسی استیکر','callback_data'=>'stickername']
		],
		[
		['text'=>$statuspm[1],'callback_data'=>'imagestatus'],
		['text'=>'دسترسی عکس','callback_data'=>'imagename']
		],
		[
		['text'=>$statuspm[2],'callback_data'=>'videostatus'],
		['text'=>'دسترسی ویدئو','callback_data'=>'videoname']
		],
		[
		['text'=>$statuspm[3],'callback_data'=>'voicestatus'],
		['text'=>'دسترسی ویس','callback_data'=>'voicename']
		],
		[
		['text'=>$statuspm[4],'callback_data'=>'musicstatus'],
		['text'=>'دسترسی موزیک','callback_data'=>'musicname']
		],
		[
		['text'=>$statuspm[5],'callback_data'=>'documentstatus'],
		['text'=>'دسترسی فایل','callback_data'=>'documentname']
		],
		[
		['text'=>'سایر تنظیمات','callback_data'=>'advsetting']
		],
		[
		['text'=>'🗑','callback_data'=>'memberdelete'],
		['text'=>'پاک کردن اعضا','callback_data'=>'membername']
		],
		[
		['text'=>'🗑','callback_data'=>'banlistdelete'],
		['text'=>'پاک کردن لیست سیاه','callback_data'=>'banlistname']
		]
		]
		])
		])
		);
		var_dump(makeHTTPRequest('answerCallbackQuery',[
		'callback_query_id'=>$callback_id,
		'show_alert'=>false,
		'text'=>"ویرایش با موفقیت انجام شد"
		]));
	}
	else if($callback_data=="memberdelete" && $chat_id==$admin){
		$txxt = file_get_contents('pmembers.txt');
		$pmembersid= explode("\n",$txxt);
		file_put_contents('pmembers.txt',"");		
		var_dump(
		makeHTTPRequest('sendMessage',[
		'chat_id'=>$chatid,
		'text'=>"✔️ لیست مخاطبین پاک شد",
		])
		);
	}
	else if($callback_data=="banlistdelete" && $chat_id==$admin){
		$txxt = file_get_contents('banlist.txt');
		$pmembersid= explode("\n",$txxt);
		file_put_contents('banlist.txt',"");	
		var_dump(
		makeHTTPRequest('sendMessage',[
		'chat_id'=>$chatid,
		'text'=>"✔️ لیست سیاه پاک شد",
		])
		);
	}
?>					
