<?php
    namespace Synchronizes;
    
    class Sync extends Chat {
        protected $database = null;
                
        public function create() {
            $departs = $this->getDeparts();
            $ent = $this->addEnt(array_column($departs, 'id'), []);
            
            foreach($departs as $depart) {
                $depart['ent_id'] = $ent['id'];
                
                try {
                    $users = $this->getUsers($depart['id']);
                } catch (\Exception $e) {
                    continue;
                }
                
                $this->database->beginTransaction();
                try {
                    $this->addLevel($depart);
                    foreach($users as $user) {
                        $this->addUser($user);
                    }
                    $this->database->commit();
                } catch (\Exception $e) {
                    $this->database->rollback();
                    printf($e->getMessage());
                }
            }
        }
        
        public function addEnt(array $departs, array $users) {
            $info = $this->getAuthInfo();
            
            $ent = $this->database->execute('INSERT INTO chat_ent VALUES(:id,:name,:departs,:users)', [
                ':id' => $info['id'],
                ':name' => $info['name'],
                ':departs' => implode(',', $departs),
                ':users' => implode(',', $users),
            ]);
            
            if($ent['affected_rows'] == 0) {
                throw new \Exception("创建企业微信企业信息时服务器遇到了一个未曾预料的状况，导致了它无法完成对请求的处理。", 500);
            }
            
            return $info;
        }
        
        public function addLevel($depart) {
            $sys = $this->database->execute('INSERT INTO sys_level(name,parent_id) VALUES(:name,:parent_id)', [
                ':name' => $depart['name'],
                ':parent_id' => 0,
            ]);
            if($sys['affected_rows'] == 0) {
                throw new \Exception("创建系统组织层级关系时服务器遇到了一个未曾预料的状况，导致了它无法完成对请求的处理。", 500);
            }
            
            $chat = $this->database->execute('INSERT INTO chat_level(id,parent_id,level_id) VALUES(:id,:parent_id,:level_id)', [
                ':id' => $depart['id'],
                ':parent_id' => $depart['parentid'],
                ':level_id' => $sys['last_insert_id'],
            ]);
            if($chat['affected_rows'] == 0) {
                throw new \Exception("创建企业微信组织层级关系时服务器遇到了一个未曾预料的状况，导致了它无法完成对请求的处理。", 500);
            }
        }
        
        public function addUser($user) {
        }
        
        public function setDatabase(&$database) {
            $this->database = $database;
        }
        
        public function updateLevel() {
            $state = true;
            
            $sys = $this->database->query('SELECT * FROM sys_level ORDER BY id ASC');
            $sys = array_combine(array_column($sys, 'id'), $sys);

            $this->database->beginTransaction();
            try {
                foreach($sys as $id => $v) {
                    if($v['parent_id'] == 0) {
                        $rel = $id;
                    } else if($sys[$v['parent_id']]['rel'] != '') {
                        $rel = $sys[$v['parent_id']]['rel'] . ',' . $id;
                    } else {
                        $state = false;
                        continue;
                    }

                    $this->database->execute('UPDATE sys_level SET rel=:rel WHERE id=:id', [
                        ':rel' => $rel,
                        ':id' => $id
                    ]);
                }
                $this->database->commit();
            } catch (\Exception $e) {
                $this->database->rollback();
                $state = false;
            }
            
            if($state == false) {
                $this->updateLevel();
            }
        }
        
        public function updateParentId() {
            $sys = $this->database->query('SELECT * FROM sys_level ORDER BY id ASC');
            $chat = $this->database->query('SELECT * FROM chat_level ORDER BY id ASC');
            
            $sys = array_combine(array_column($sys, 'id'), $sys);
            $chat = array_combine(array_column($chat, 'id'), $chat);

            $this->database->beginTransaction();
            try {
                foreach($chat as $v) {
                    if($v['parent_id'] != 0) {
                        $this->database->execute('UPDATE sys_level SET parent_id=:parent_id WHERE id=:id', [
                            ':parent_id' => $chat[$v['parent_id']]['level_id'],
                            ':id' => $v['level_id']
                        ]);
                    }
                }
                $this->database->commit();
            } catch (\Exception $e) {
                $this->database->rollback();
            }
        }
    }
