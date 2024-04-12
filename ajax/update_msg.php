<?php

if(is_array($_REQUEST['update_array']) && $_REQUEST['id'] !== "")
{
	// подключение к бд
	require_once '../lib/PDOConnect.php';
	$message_status = DBConnect::init()->updateMessage($table = 'messages', $data = $_REQUEST['update_array'], $id = $_REQUEST['id']);
	if($message_status !== false)
	{
		$response['status'] = true;
	}
	else
	{
		$response['status'] = false;
		$response['data']['errorMsg'] = "Ошибка добавления в бд";
	}
	echo json_encode($response);
}