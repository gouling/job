<?php
    require('CZookeeper.php');
    require('CSolr.php');

    $zookeeper = new CZookeeper('127.0.0.1:2181,127.0.0.1:2182,127.0.0.1:2183');
    $zookeeper->create(array(
        '/solr' => 'solr tender database',
        '/solr/host' => 'http://127.0.0.1:8983/solr/',
        '/solr/timeout' => 5
    ));
    $config = $zookeeper->get('solr');

    $solr = new CSolr($config['host'], $config['timeout']);
    $query = $solr->query('SELECT * FROM tender WHERE borrow_name LIKE \'%女士%\'');

    print_r($query);