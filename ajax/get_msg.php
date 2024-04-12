<?php
if($_REQUEST['action'] == "getList")
{
	// подключение к бд
	require_once '../lib/PDOConnect.php';

	// выясняем текущую страницу и лимиты
	$page = ($_REQUEST['page'] != "") ? $_REQUEST['page'] : 1;
	$page_limit = 5;

	// сравниваем количество элементов, чтобы не делать лишние запросы в бд
	$currentElemsCount = $_REQUEST['elemsCount'];
	$elements = DBConnect::init()->countElements($table = 'messages');
	if($currentElemsCount != $elements || $_REQUEST['forceChange'] === "Y")
	{
		// сам запрос на обновление
		$messages = DBConnect::init()->getAllMessages($table = 'messages', $filter = null, $sort = ["field" => "insertDate", "sort_type" => "desc"], $page, $page_limit);
		if($messages !== false && is_array($messages))
		{
			// выясняем количество страниц
			$pages_count = ceil($elements / $page_limit);

			if($pages_count > 0)
				$response['pageData'] = $pages_count;

			if($elements > 0)
				$response['newElemsCount'] = $elements;

			$response['data'] = $messages;
			$response['status'] = true;
		}
		else
		{
			$response['status'] = false;
			$response['data']['errorMsg'] = "Ошибка выборки из бд";
		}
	}
	else
	{
		$response['status'] = false;
		$response['data']['errorMsg'] = "Обновление не требуется";
	}
	echo json_encode($response);
}