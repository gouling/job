<?php
    class CSession {
        private $__node, $__zookeeper;
        public function __construct($zookeeper) {
            $this->__node = '/session';
            $this->__zookeeper = $zookeeper;
        }
        
        public function getLastUpdateTime($id) {
            return $this->__zookeeper->getNodeInfo("{$this->__node}/{$id}");
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
        }
    }
