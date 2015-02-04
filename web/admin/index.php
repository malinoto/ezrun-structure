<?php
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'logic_top.php');

header('Content-Type: text/html; charset=utf-8');

$main_obj = new Main;
$main_obj->setAdminLevel(1);

$tpl = new Template(true);
$tpl->loadTemplate('index');

$act = isset($_GET['act']) ? $_GET['act'] : 'start';

switch ($act)
{
	case 'start':
	$body = parse_admin_module('start/start', array('language_obj' => $language_obj));
	break;
	
	default:
	$body = parse_admin_module('start/start', array('language_obj' => $language_obj));
	break;
}

//messages
printMessages($tpl);

$tpl->setVariable('body', $body);

include(PUBLIC_PATH . 'logic_bottom.php');

echo $tpl->get();
?>