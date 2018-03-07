<?php
	require('CZookeeper.php');
	require('CSession.php');
	require('CSolr.php');

	$zookeeper = new CZookeeper('127.0.0.1:2181,127.0.0.1:2182,127.0.0.1:2183');
	$zookeeper->create(array(
		'/solr' => 'solr tender database',
		'/solr/host' => 'http://127.0.0.1:8983/solr/',
		'/solr/timeout' => 5
	));

	$config = $zookeeper->get('/solr');

	$solr = new CSolr($config['host'], $config['timeout']);
	$query = $solr->query('SELECT * FROM tender WHERE borrow_name LIKE \'%女士%\'');

	$session = new CSession($zookeeper);
	session_set_save_handler(
		array($session, 'open'),
		array($session, 'close'),
		array($session, 'read'),
		array($session, 'write'),
		array($session, 'destroy'),
		array($session, 'gc')
	);
	session_start();
	$id = session_id();

	$_SESSION['id']  = 1;
	$_SESSION['user'] = array(
	'name'=>'gouling',
	'tel'=>'17612800917',
	);
	
	$info = $session->getLastUpdateTime($id);
	$datetime = date('Y-m-d H:i:s', $info[0]['mtime']/1000);
	var_dump("session_id: {$id}, lastUpdateTime: {$datetime}");
	var_dump($_SESSION);
