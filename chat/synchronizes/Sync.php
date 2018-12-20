<?php
    namespace Synchronizes;
    
    class Sync extends Chat {
        protected $time = 0;
        public function auth() {
            $this->time = time();
            
            $this->update();
            $this->updateParentId();
            $this->updateRel();
        }
        
        public function update() {
            $departs = $this->getDeparts();
            $auth = $this->getAuthInfo();

            // 企业微信顶层不可见，设置一个顶层，并按层级编号自然排序
            $root = 1;
            if(!isset($departs[$root])) {
                $departs[$root] = [
                    'id' => $root,
                    'name' => $auth['name'],
                    'parentid' => 0,
                ];
            }
            ksort($departs);
            
            // 使用企业微信所有可见层覆盖可见范围中的不完整的可见层信息
            $auth = array_merge($auth, [
                'departs' => implode(',', array_keys($departs))
            ]);
            
            // 企业微信接入信息
            $this->updateEnt($auth);

            foreach($departs as $depart) {
                try {
                    $users = $this->getUsers($depart['id']);
                } catch (\Exception $e) {
                    $users = [];
                }

                $this->database->beginTransaction();
                try {
                    $this->updateLevel($depart);
                    foreach($users as $user) {
                        $this->updateUser($user);
                    }
                    $this->database->commit();
                } catch (\Exception $e) {
                    $this->database->rollback();
                }
            }
        }
        
        public function updateEnt($auth) {
            $auth += [
                'updated' => $this->time,
            ];
            if(is_null($this->getEntById($auth['id']))) {
                $ent = $this->database->create('chat_ent', $auth);
            } else {
                $ent = $this->database->update('chat_ent', $auth);
            }
            
            if($ent['affected_rows'] == 0) {
                throw new \Exception("更新企业微信企业信息时服务器遇到了一个未曾预料的状况，导致了它无法完成对请求的处理。", 500);
            }
        }
        
        public function updateLevel($depart) {
            if($chat = $this->database->query('SELECT * FROM chat_level WHERE ent_id=:ent_id AND id=:id', [
                ':ent_id' => $this->opts['id'],
                ':id' => $depart['id'],
            ])) {
                $chat = array_shift($chat);
                $updated_chat = $this->database->execute('UPDATE chat_level SET parent_id=:parent_id,updated=:updated WHERE ent_id=:ent_id AND id=:id', [
                    ':ent_id' => $this->opts['id'],
                    ':id' => $depart['id'],
                    ':parent_id' =>  $depart['parentid'],
                    ':updated' => $this->time,
                ]);
                if($updated_chat['affected_rows'] == 0) {
                    throw new \Exception("更新企业微信组织层级关系时服务器遇到了一个未曾预料的状况，导致了它无法完成对请求的处理。", 500);
                }
                
                $updated_sys = $this->database->update('sys_level', [
                    'id' => $chat['level_id'],
                    'name' => $depart['name'],
                    'updated' => $this->time,
                ]);
                if($updated_sys['affected_rows'] == 0) {
                    throw new \Exception("更新系统组织层级关系时服务器遇到了一个未曾预料的状况，导致了它无法完成对请求的处理。", 500);
                }
            } else {
                $created_sys = $this->database->create('sys_level', [
                    'name' => $depart['name'],
                    'parent_id' => 0,
                    'updated' => $this->time,
                ]);
                if($created_sys['affected_rows'] == 0) {
                    throw new \Exception("创建系统组织层级关系时服务器遇到了一个未曾预料的状况，导致了它无法完成对请求的处理。", 500);
                }
                
                $created_chat = $this->database->create('chat_level', [
                    'id' => $depart['id'],
                    'parent_id' => $depart['parentid'],
                    'level_id' => $created_sys['last_insert_id'],
                    'ent_id' => $this->opts['id'],
                    'updated' => $this->time,
                ]);
                if($created_chat['affected_rows'] == 0) {
                    throw new \Exception("创建企业微信组织层级关系时服务器遇到了一个未曾预料的状况，导致了它无法完成对请求的处理。", 500);
                }
                
                return $created_sys['last_insert_id'];
            }
        }
        
        public function updateUser($user) {
            $chat = $this->getChatById($user['userid']);
            if(is_null($chat)) {
                $created_sys = $this->database->create('sys_user', [
                    'name' => $user['name'],
                    'gender' => $user['gender'],
                    'position' => $user['position'] ?? '',
                    'mobile' => $user['mobile'] ?? '',
                    'email' => $user['email'] ?? '',
                    'avatar' => $user['avatar'],
                    'enable' => $user['enable'] ?? 1,
                    'status' => $user['status'],
                    'level' => implode(',', $user['department']),
                    'updated' => $this->time,
                ]);
                if($created_sys['affected_rows'] == 0) {
                    throw new \Exception("创建系统用户时服务器遇到了一个未曾预料的状况，导致了它无法完成对请求的处理。", 500);
                }
                
                $created_chat = $this->database->create('chat_user', [
                    'ent_id' => $this->opts['id'],
                    'chat_id' => $user['userid'],
                    'user_id' => $created_sys['last_insert_id'],
                    'updated' => $this->time,
                ]);
                if($created_chat['affected_rows'] == 0) {
                    throw new \Exception("创建企业微信用户时服务器遇到了一个未曾预料的状况，导致了它无法完成对请求的处理。", 500);
                }
            } else {
                $updated_chat = $this->database->execute('UPDATE chat_user SET chat_id=:chat_id,updated=:updated WHERE ent_id=:ent_id AND chat_id=:chat_id', [
                    ':ent_id' => $this->opts['id'],
                    ':chat_id' => $user['userid'],
                    ':updated' => $this->time,
                ]);
                if($updated_chat['affected_rows'] == 0 && isset($updated_chat['exception']['code'])) {
                    throw new \Exception("更新企业微信用户时服务器遇到了一个未曾预料的状况，导致了它无法完成对请求的处理({$updated_chat['exception']['message']})。", $updated_chat['exception']['code']);
                }
                
                $updated_sys_columns = ['name', 'gender', 'position', 'mobile', 'email', 'avatar', 'enable', 'status', 'level'];
                $updated_sys_values = [
                    'level' => implode(',', $user['department']),
                    'id' => $chat['user_id'],
                    'updated' => $this->time,
                ];
                
                foreach($updated_sys_columns as $k) {
                    if(isset($user[$k])) {
                        $updated_sys_values[$k] = $user[$k];
                    }
                }
                
                $updated_sys = $this->database->update('sys_user', $updated_sys_values);
                if($updated_sys['affected_rows'] == 0 && isset($updated_sys['exception']['code'])) {
                    throw new \Exception("更新系统用户时服务器遇到了一个未曾预料的状况，导致了它无法完成对请求的处理({$updated_sys['exception']['message']})。", $updated_sys['exception']['code']);
                }
            }
        }

        public function getEntById($id) {
            if($ent = $this->database->query('SELECT * FROM chat_ent WHERE id=:id', [
                ':id' => $id,
            ])) {
                return array_shift($ent);
            }
        }
        
        public function getChatById($id) {
            if($chat = $this->database->query('SELECT * FROM chat_user WHERE ent_id=:ent_id AND chat_id=:chat_id', [
                ':ent_id' => $this->opts['id'],
                ':chat_id' => $id,
            ])) {
                return array_shift($chat);
            }
        }
        
        public function getUserById($id) {
            if($user = $this->database->execute('SELECT * FROM sys_user WHERE id=:id', [
                ':id' => $id,
            ])) {
                return array_shift($user);
            }
        }
        
        public function setDatabase(&$database) {
            $this->database = $database;
        }
        
        public function updateRel() {
            $state = true;
            
            $sys = $this->database->query('SELECT * FROM sys_level ORDER BY id ASC');
            $sys = array_combine(array_column($sys, 'id'), $sys);
            $root = $this->getSysRootLevel();

            $this->database->beginTransaction();
            try {
                foreach($sys as $id => $v) {
                    if($v['parent_id'] == 0) {
                        $rel = $id;
                    } else if(!isset($sys[$v['parent_id']])) {
                        $rel = $root['rel'] . ',' . $id;
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
                $this->updateRel();
            }
        }
        
        public function updateParentId() {
            $sys = $this->database->query('SELECT * FROM sys_level ORDER BY id ASC');
            $chat = $this->database->query('SELECT * FROM chat_level ORDER BY id ASC');
            
            $sys = array_combine(array_column($sys, 'id'), $sys);
            $chat = array_combine(array_column($chat, 'id'), $chat);
            $root = $this->getChatRootLevel();

            $this->database->beginTransaction();
            try {
                foreach($chat as $v) {
                    if($v['parent_id'] != 0) {
                        $parent_id = isset($chat[$v['parent_id']]) ? $chat[$v['parent_id']]['level_id'] : $root['level_id'];
                        
                        $this->database->execute('UPDATE sys_level SET parent_id=:parent_id WHERE id=:id', [
                            ':parent_id' => $parent_id,
                            ':id' => $v['level_id']
                        ]);
                    }
                }
                $this->database->commit();
            } catch (\Exception $e) {
                $this->database->rollback();
            }
        }
        
        protected function getChatRootLevel() {
            if($root = $this->database->query('SELECT * FROM chat_level WHERE id=1 AND parent_id=0')) {
                return array_shift($root);
            }
            
            throw new \Exception("服务器在验证企业微信顶层组织时，没能满足其中的一个。", 412);
        }
        
        protected function getSysRootLevel() {
            if($root = $this->database->query('SELECT * FROM sys_level WHERE id=1 AND parent_id=0')) {
                return array_shift($root);
            }
            
            throw new \Exception("服务器在验证系统顶层组织时，没能满足其中的一个。", 412);
        }
    }
