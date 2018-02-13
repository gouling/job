<?php
    class CZookeeper {
        private $__zookeeper;

        public function get($name) {
            $data = array();

            if ($this->__zookeeper->exists("/{$name}")) {
                $data['doc'] = $this->__zookeeper->get("/{$name}");
                $child = $this->__zookeeper->getchildren("/{$name}");
                foreach ($child as $node) {
                    $data[$node] = $this->__zookeeper->get("/{$name}/{$node}");
                }
            }

            return $data;
        }

        public function create($configList) {
            foreach ($configList  as $k => $v) {
                if($this->__zookeeper->exists($k)) {
                    $this->__zookeeper->set($k, $v);
                } else {
                    $this->__zookeeper->create($k, $v, array(
                        array(
                            'perms' => Zookeeper::PERM_ALL,
                            'scheme' => 'world',
                            'id' => 'anyone',
                        )
                    ));
                }
            }
        }

        public function __construct($zkConnInfo) {
            $this->__zookeeper = new \Zookeeper($zkConnInfo);
        }
    }