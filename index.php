<?php

require_once 'vendor/autoload.php';
// подключение к бд
require_once 'lib/PDOConnect.php';



// подключение Twig
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem('templates/');
$twig = new Twig_Environment($loader);

// выбор самого шаблона
try
{
	$template = $twig->loadTemplate('index.html');
	// данные передаваемые в шаблон
	$data_array['title'] = "Страница | Таблица";
	$data_array['h2_title'] = "Таблица";

	// страница и лимиты
	$page = 1;
	$page_limit = 5;

	// выясняем количество страниц легким запросом
	$elements = DBConnect::init()->countElements($table = 'messages');
	if($elements > 0)
	{
		$data_array['pages_count'] = ceil($elements / $page_limit);
		$data_array['element_count'] = $elements;
	}
	else
		$data_array['pages_count'] = 0;
		$data_array['element_count'] = 0;


	// запрос всех данных на момент загрузки страницы
	$messages = DBConnect::init()->getAllMessages($table = 'messages', $filter = null, $sort = ["field" => "insertDate", "sort_type" => "desc"], $page, $page_limit);
	if($messages !== false && is_array($messages))
		$data_array['messages'] = $messages;
}
catch(Twig_Error_Loader $e) // отлов события, если не найден шаблон, и вывод соответствующей ошибки
{
	$template = $twig->loadTemplate('error404.html');
	$data_array['title'] = "Ошибка 404 | страница не найдена";
	$data_array['h1_title'] = "Ошибка 404.";
	$data_array['error_msg'] = "Шаблон не найден.";
}

echo $template->render($data_array);