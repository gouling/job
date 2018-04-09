<?php
    class CCache extends \Redis {
        private $__prefix;
        
        public function __construct($prefix) {
            $this->__prefix = $prefix;
            return parent::__construct();
        }
        
        public function setOptionById($id, $option) {
            return $this->hSet($this->__prefix['option'], $id, json_encode($option)) !== false;
        }
        
        public function getOptionById($id) {
            $data = array();
            if($val = $this->hGet($this->__prefix['option'], $id)) {
                $data = json_decode($val, true);
            }
            
            return $data;
        }
    }
