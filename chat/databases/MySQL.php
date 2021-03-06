<?php
    namespace Databases;
    
    class MySQL extends \PDO {
        public function __construct($config) {
            parent::__construct($config['dsn'], $config['username'], $config['password'], $config['options']);
        }

        public function query($statement, $params = []) {
            $ds = $this->prepare($statement);
            if($ds->execute($this->getParams($params)) == true) {
                return $ds->fetchAll(self::FETCH_ASSOC);
            } else {
                throw new \Exception(implode('->', $ds->errorInfo()), 500);
            }
        }

        public function execute($statement, $params = []) {
            $ds = $this->prepare($statement);
            
            if($ds->execute($this->getParams($params)) == true) {
                return [
                    'affected_rows' => $ds->rowCount(),
                    'last_insert_id' => (int)$this->lastInsertId()
                ];
            } else {
                throw new \Exception(implode('->', $ds->errorInfo()), 500);
            }
        }
        
        public function multi($statement) {
            $query = implode(';', $statement);
            
            return $this->execute($query);
        }
        
        public function tree($items, $id, $parent_id) {
            $tree = [];
            $items = array_combine(array_column($items, $id), $items);
            
            foreach ($items as $item) {
                if (isset($items[$item[$parent_id]])) {
                    $items[$item[$parent_id]]['children'][$item[$id]] = &$items[$item[$id]];
                } else {
                    $tree[$item[$id]] = &$items[$item[$id]];
                }
            }
            
            return $tree;
        }
        
        public function level(&$items, $id, $parent_id) {
            $state = false;
            foreach($items as &$v) {
                if($v[$parent_id] == 0) {
                    $v['rel'] = $v[$id];
                } else if(isset($items[$v[$parent_id]])) {
                    if(!empty($items[$v[$parent_id]]['rel'])) {
                        $v['rel'] = $items[$v[$parent_id]]['rel'] . ',' . $v[$id];
                    } else {
                        $state = true;
                    }
                }
            }
            
            if($state == true) {
                $this->level($items, $id, $parent_id);
            }
        }
        
                
        public function find($statement, $params = []) {
            if($data = $this->query($statement, $params)) {
                return array_shift($data);
            }
        }
        
        public function create($table, $params) {
            $columns = implode(',', array_keys($params));
            foreach($params as $k=>$v) {
                $values[] = ':' . $k;
            }
            
            $values = implode(',', $values);
            $query = "INSERT INTO {$table}({$columns}) VALUES ({$values})";
            
            return $this->execute($query, $params);
        }
        
        public function update($table, $params) {
            $values = [];
            foreach($params as $k=>$v) {
                $values[$k] = $k . '=:' . $k;
            }
            $values = implode(',', $values);
            $query = "UPDATE {$table} SET {$values} WHERE id=:id";

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
