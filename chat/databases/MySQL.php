<?php
    namespace Databases;
    
    class MySQL extends \PDO {
        public function __construct($config) {
            parent::__construct($config['dsn'], $config['username'], $config['password'], $config['options']);
        }
        
        public function query($statement, $params = []) {
            $ds = $this->prepare($statement);
            return $ds->execute($params) ? $ds->fetchAll(self::FETCH_ASSOC) : [];
        }

        public function execute($statement, $params = []) {
            $ds = $this->prepare($statement);
            $ds->execute($params);
            return [
                'affected_rows' => $ds->rowCount(),
                'last_insert_id' => (int)$this->lastInsertId()
            ];
        }
        
        public function tree($items, $id, $parentId) {
            $tree = [];
            $items = array_combine(array_column($items, $id), $items);
            
            foreach ($items as $item) {
                if (isset($items[$item[$parentId]])) {
                    $items[$item[$parentId]]['children'][$item[$id]] = &$items[$item[$id]];
                } else {
                    $tree[$item[$id]] = &$items[$item[$id]];
                }
            }
            
            return $tree;
        }
    }
