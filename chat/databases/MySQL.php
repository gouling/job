<?php
    namespace Databases;
    
    class MySQL extends \PDO {
        public function __construct($config) {
            parent::__construct($config['dsn'], $config['username'], $config['password'], $config['options']);
        }
        
        public function query($statement, $params = []) {
            $ds = $this->prepare($statement);
            if($ds->execute($params) == true) {
                return $ds->fetchAll(self::FETCH_ASSOC);
            } else {
                throw new \Exception(implode('->', $ds->errorInfo()), $ds->errorCode());
            }
        }

        public function execute($statement, $params = []) {
            $ds = $this->prepare($statement);
            $ex = [];
            
            if($ds->execute($params) == false) {
                $ex = [
                    'code' => $ds->errorCode(),
                    'message' => implode('->', $ds->errorInfo()),
                ];
            }
    
            return [
                'affected_rows' => $ds->rowCount(),
                'last_insert_id' => (int)$this->lastInsertId(),
                'exception' => $ex
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
        
        public function create($table, $params) {
            $columns = implode(',', array_keys($params));
            foreach($params as $k=>$v) {
                $values[$k] = ':' . $k;
                $params[$values[$k]] = $v;
                unset($params[$k]);
            }
            $values = implode(',', $values);
            $query = "INSERT INTO {$table}({$columns}) VALUES ({$values})";
            
            return $this->execute($query, $params);
        }
        
        public function update($table, $params) {
            foreach($params as $k=>$v) {
                $columns[$k] = $k . '=:' . $k;
                $params[':' . $k] = $v;
                unset($params[$k]);
            }
            $columns = implode(',', $columns);
            $query = "UPDATE {$table} SET {$columns} WHERE id=:id";
            
            return $this->execute($query, $params);
        }
        
        public function getParams($params) {
            $data = [];
            foreach($params as $k=>$v) {
                $data[":{$k}"] = $v;
            }
            
            return $data;
        }
    }
