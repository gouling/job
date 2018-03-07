<?php
    class CSession {
        private $__lifetime ,  $__time;
        private $__node, $__zookeeper;
        public function __construct($zookeeper) {
            $this->__lifetime = 5;
            $this->__time = time();
        
            $this->__node = '/session';
            $this->__zookeeper = $zookeeper;
            
    session_set_save_handler(
                array(&$this, 'open'),
                array(&$this, 'close'),
                array(&$this, 'read'),
                array(&$this, 'write'),
                array(&$this, 'destroy'),
                array(&$this, 'gc')
            );
    session_start();
        }
        
        public function getLastUpdateTime($id) {
            if($data = $this->__zookeeper->getNodeInfo("{$this->__node}/{$id}")) {
                if(isset($data[0]['mtime'])) {
                    return $data[0]['mtime']/1000;
                }
            }
            
            return 0;
        }
        
        public function open() {
            $this->__zookeeper->set($this->__node, 'php session share');
        }
        
        public function close() {
        }

        public function read($id) {
           if($data = $this->__zookeeper->get("{$this->__node}/{$id}")) {
                return $data['doc'];
           }
        }
        
        public function write($id, $data) {
            return $this->__zookeeper->set( "{$this->__node}/{$id}", $data);
        }
        
        public function destroy($id) {
            return $this->__zookeeper->delete("{$this->__node}/{$id}");
        }
        
        public function gc() {
            if($session = $this->__zookeeper->get($this->__node)) {
                if(isset($session['doc'])) {
                    unset($session['doc']);
                }
                foreach($session as $id=>$v) {
                    $time = $this->getLastUpdateTime($id);
                    if($time + $this->__lifetime < $this->__time) {
                        $this->__zookeeper->delete("{$this->__node}/{$id}");
                    }
                }
            }
        }
    }
