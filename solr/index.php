<?php
    require('CZookeeper.php');
    require('CSolr.php');

    $zookeeper = new CZookeeper('127.0.0.1:2181');
    $zookeeper->create(array(
        '/solr' => 'solr tender database',
        '/solr/host' => 'http://127.0.0.1:8983/solr/',
        '/solr/timeout' => 5
    ));
    $config = $zookeeper->get('solr');

    $solr = new CSolr($config['host'], $config['timeout']);
    $query = $solr->query('SELECT * FROM tender');

    print_r($query);