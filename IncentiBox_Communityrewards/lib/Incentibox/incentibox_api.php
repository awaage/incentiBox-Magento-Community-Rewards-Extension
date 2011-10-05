<?php
/*
$Id: admin/includes/classes/incentibox_api.php,v 1.0.0 2011/07/10 awaage Exp $

CRE Loaded, Open Source E-Commerce Solutions
http://www.creloaded.com

Copyright (c) 2008 CRE Loaded
Copyright (c) 2003 osCommerce

Released under the GNU General Public License
*/
class IncentiboxApi {
const DEBUG = false;
const INCENTIBOX_API_URL = 'http://api.incentibox.com';
const INCENTIBOX_API_PORT = 80;
const API_VERSION = '1';

private $password;
private $time_out = 60;
private $user_agent;
private $username;

// class methods
public function __construct($username = null, $password = null) {
if($username !== null) $this->set_username($username);
if($password !== null) $this->set_password($password);
}

private function perform_call($url, $params = array(), $authenticate = false, $use_post = true) {
// redefine
$url = (string) $url;
$aParameters = (array) $params;
$authenticate = (bool) $authenticate;
$use_post = (bool) $use_post;

// build url
$url = self::INCENTIBOX_API_URL .'/v'. self::API_VERSION . '/' . $url;

// validate needed authentication
if($authenticate && ($this->get_username() == '' || $this->get_password() == '')) {
throw new IncentiboxException('No username or password was set.');
}

// build GET URL if not using post
if(!empty($params) && !$use_post){
$url .= '?'. http_build_query( $params );
}

// set options
$options[CURLOPT_URL] = $url;
$options[CURLOPT_PORT] = self::INCENTIBOX_API_PORT;
$options[CURLOPT_USERAGENT] = $this->get_useragent();
$options[CURLOPT_FOLLOWLOCATION] = true;
$options[CURLOPT_RETURNTRANSFER] = true;
$options[CURLOPT_TIMEOUT] = (int) $this->get_time_out();

// HTTP basic auth
if($authenticate) {
$options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
$options[CURLOPT_USERPWD] = $this->get_username() .':'. $this->get_password();
}

// build post params if $use_post
if(!empty($params) && $use_post) {
$options[CURLOPT_POST] = true;
$options[CURLOPT_POSTFIELDS] = http_build_query( $params );
}

// curl init
$curl = curl_init();
// set options
curl_setopt_array($curl, $options);
// execute
$response = curl_exec($curl);
$headers = curl_getinfo($curl);
// fetch errors and status code
$errorNumber = curl_errno($curl);
$errorMessage = curl_error($curl);
$http_status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ($errorNumber != 0) {
$response = 'cURL ERROR: [' . $errorNumber . "] " . $errorMessage;
}
// close
curl_close($curl);
return array('response_code' => $http_status_code,
'response' => $response);
}


// Getters
private function get_password(){
return (string) $this->password;
}
public function get_time_out(){
return (int) $this->time_out;
}
public function get_useragent(){
return (string) 'PHP IncentiBox API Client/'. self::API_VERSION .' '. $this->user_agent;
}
private function get_username(){
return (string) $this->username;
}

// Setters
private function set_username($username){
$this->username = (string) $username;
}
private function set_password($password){
$this->password = (string) $password;
}
public function set_time_out($seconds){
$this->time_out = (int) $seconds;
}
public function set_user_agent($user_agent){
$this->user_agent = (string) $user_agent;
}

/**
* Returns all redeemed_rewards for the given program_id.
* If param @last_redeemed_reward_id is given, returns all redeemed_rewards where id > @last_redeemed_reward_id
*
* @return array
* @param $program_id
* @param $last_redeemed_reward_id (optional)
*/
public function get_redeemed_rewards($program_id, $last_redeemed_reward_id = null) {
// build url
$url = 'programs/'.urlencode($program_id).'/redeemed_rewards';
$url = ($last_redeemed_reward_id != null) ? $url . '/' . urlencode($last_redeemed_reward_id) : $url;

$response = $this->perform_call($url, array(), true, false);

$response_code = $response['response_code'];
$response = $response['response'];

// decode the returned json
if ($response_code == 200){
return json_decode($response,true);
} else {
throw new IncentiboxException($response_code . ' - ' . $response);
}
}
}


// IncentiboxException
class IncentiboxException extends Exception { }

?>