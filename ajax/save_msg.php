<?php
if($_REQUEST['name'] !== "")
{
	// подключение к бд
	require_once '../lib/PDOConnect.php';
	$message_status = DBConnect::init()->addMessage($table = 'messages', $data = $_REQUEST);
	if($message_status !== false)
	{
		$response['data']['name'] = $_REQUEST['name'];
		$response['data']['insertDate'] = date("Y-m-d H:i:s");
		$response['status'] = true;
	}
	else
	{
		$response['status'] = false;
		$response['data']['errorMsg'] = "Ошибка добавления в бд";
	}
	echo json_encode($response);
}