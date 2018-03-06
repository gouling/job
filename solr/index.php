<?php
    require('CZookeeper.php');
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

    class CSession {
        private $__node, $__zookeeper;
        public function __construct($zookeeper) {
            $this->__node = '/session';
            $this->__zookeeper = $zookeeper;
        }
        
        public function open() {
            $this->__zookeeper->set($this->__node, 'php session share');
        }
        
        public function close() {
        }

        public function read($id) {
            return $this->__zookeeper->get("{$this->__node}/{$id}");
        }
        
        public function write($id, $data) {
            return $this->__zookeeper->set( "{$this->__node}/{$id}", $data);
        }
        
        public function destroy($id) {
            return $this->__zookeeper->delete("{$this->__node}/{$id}");
        }
        
        public function gc() {
        }
    }
    
    $session = new CSession($zookeeper);
  session_set_save_handler(
        array($session, 'open'),
        array($session, 'close'),
        array($session, 'read'),
        array($session, 'write'),
        array($session, 'destroy'),
        array($session, 'gc')
    );
