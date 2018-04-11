<?php
    class CZookeeper {
        private $__zookeeper;

        public function get($node) {
            $data = array();

            if ($this->exists($node)) {
                $data['node'] = $node;
                $data['doc'] = $this->__zookeeper->get($node);
                $child = $this->__zookeeper->getchildren($node);
                foreach ($child as $child_node) {
                    $data[$child_node] = $this->__zookeeper->get("{$node}/{$child_node}");
                }
            }

            return $data;
        }
        
        public function getNodeInfo($node) {
            if($this->exists($node)) {
                return $this->__zookeeper->getAcl($node);
            }
            
            return array();
        }
        
        public function set($node, $data) {
            if($this->exists($node)) {
                $this->__zookeeper->set($node, $data);
            } else {
                $this->__zookeeper->create($node, $data, array(
                    array(
                        'perms' => Zookeeper::PERM_ALL,
                        'scheme' => 'world',
                        'id' => 'anyone',
                    )
                ));
            }
        }
        
        public function delete($node) {
            if ($this->exists($node)) {
                $child = $this->__zookeeper->getchildren($node);
                foreach ($child as $child_node) {
                    $this->__zookeeper->delete("{$node}/{$child_node}");
                }

                $this->__zookeeper->delete($node);
            }
        }
        
        public function create($configList) {
            foreach ($configList  as $node => $v) {
                $this->set($node, $v);
            }
        }
        
        public function exists($node) {
            return $this->__zookeeper->exists($node);
        }

        public function __construct($zkConnInfo) {
            $this->__zookeeper = new \Zookeeper($zkConnInfo);
        }
    }
