<?php
    namespace Synchronizes;
    
    class Sync extends Chat {
        public function auth() {
            $this->update();
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
            
            // 移除不可见层信息
            $this->deleteLevel($auth);
            
            // 企业微信接入信息
            $this->updateEnt($auth);

            $this->database->beginTransaction();
            try {
                // 企业微信与系统层级信息
                foreach($departs as $depart) {
                    $this->updateLevel($depart);
                }
                $this->database->commit();
            } catch (\Exception $e) {
                $this->database->rollback();
                exit('企业信息写入失败。');
            }
            
            // 更新系统层级信息与关系信息
            $this->updateParentId();
            $this->updateRel();
            
            // 获取企业微信层级包含与系统层级的关系信息，为更新用户部门做准备
            $chat_level = $this->database->query('SELECT * FROM chat_level ORDER BY id ASC');
            $chat_level = array_combine(array_column($chat_level, 'id'), $chat_level);
            $root_level = $this->getChatRootLevel();
            
            // 可见层级用户信息
            foreach($departs as $id=>$depart) {
                try {
                    $users = $this->getUsers($id);
                } catch (\Exception $e) {
                    $users = [];
                }

                $this->database->beginTransaction();
                try {
                    foreach($users as $user) {
                        $user['level'] = [];
                        foreach($user['department'] as $v) {
                            if(isset($chat_level[$v])) {
                                $user['level'][] = $chat_level[$v]['level_id'];
                            }
                        }
                        if(empty($user['level'])) {
                            $user['level'] = $root_level['level_id'];
                        }
                        
                        $this->updateUser($user);
                    }
                    $this->database->commit();
                } catch (\Exception $e) {
                    $this->database->rollback();
                }
            }
        }
        
        public function updateEnt($auth) {
            if(is_null($this->getEntById())) {
                $ent = $this->database->create('chat_ent', $auth);
            } else {
                $ent = $this->database->update('chat_ent', $auth);
            }
        }
        
        public function updateLevel($depart) {
            if($chat = $this->database->query('SELECT * FROM chat_level WHERE ent_id=:ent_id AND id=:id', [
                ':ent_id' => $this->opts['id'],
                ':id' => $depart['id'],
            ])) {
                $chat = array_shift($chat);
                $this->database->execute('UPDATE chat_level SET parent_id=:parent_id WHERE ent_id=:ent_id AND id=:id', [
                    ':ent_id' => $this->opts['id'],
                    ':id' => $depart['id'],
                    ':parent_id' =>  $depart['parentid'],
                ]);
                
                $this->database->update('level', [
                    'id' => $chat['level_id'],
                    'name' => $depart['name'],
                ]);
            } else {
                $created = $this->database->create('level', [
                    'name' => $depart['name'],
                    'parent_id' => 0,
                ]);
 
                $this->database->create('chat_level', [
                    'id' => $depart['id'],
                    'parent_id' => $depart['parentid'],
                    'level_id' => $created['last_insert_id'],
                    'ent_id' => $this->opts['id'],
                ]);
                
                return $created['last_insert_id'];
            }
        }
        
        public function updateUser($user) {
            $columns = ['name', 'gender', 'position', 'status'];
            $values = [
                'level' => implode(',', $user['level'])
            ];
            
            foreach($columns as $k) {
                if(isset($user[$k])) {
                    $values[$k] = $user[$k];
                }
            }
            
            $chat = $this->getChatById($user['userid']);
            if(is_null($chat)) {
                $created = $this->database->create('user', $values);
                
                $this->database->create('chat_user', [
                    'ent_id' => $this->opts['id'],
                    'chat_id' => $user['userid'],
                    'user_id' => $created['last_insert_id'],
                ]);
            } else {
                $this->database->execute('UPDATE chat_user SET chat_id=:chat_id WHERE ent_id=:ent_id AND chat_id=:chat_id', [
                    ':ent_id' => $this->opts['id'],
                    ':chat_id' => $user['userid'],
                ]);
                
                $this->database->update('user', $values + [
                    'id' => $chat['user_id'],
                ]);
            }
        }

        public function deleteLevel($auth) {
        }

        public function getEntById() {
            if($ent = $this->database->query('SELECT * FROM chat_ent WHERE id=:id', [
                ':id' => $this->opts['id'],
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
            if($user = $this->database->execute('SELECT * FROM user WHERE id=:id', [
                ':id' => $id,
            ])) {
                return array_shift($user);
            }
        }
        
        public function setDatabase(&$database) {
            $this->database = $database;
        }
        
        public function updateRel() {
            $sys = $this->database->query('SELECT * FROM level ORDER BY parent_id ASC');
            $tree = array_combine(array_column($sys, 'id'), $sys);
            $this->database->level($tree, 'id', 'parent_id');
            
            $this->database->beginTransaction();
            try {
                foreach($tree as $v) {
                    $this->database->execute('UPDATE level SET rel=:rel WHERE id=:id', [
                        ':rel' => $v['rel'],
                        ':id' => $v['id']
                    ]);
                }
                $this->database->commit();
            } catch (\Exception $e) {
                $this->database->rollback();
            }
        }

        public function updateParentId() {
            $sys = $this->database->query('SELECT * FROM level ORDER BY id ASC');
            $chat = $this->database->query('SELECT * FROM chat_level ORDER BY id ASC');
            
            $sys = array_combine(array_column($sys, 'id'), $sys);
            $chat = array_combine(array_column($chat, 'id'), $chat);
            $root = $this->getChatRootLevel();

            $this->database->beginTransaction();
            try {
                foreach($chat as $v) {
                    if($v['parent_id'] != 0) {
                        $parent_id = isset($chat[$v['parent_id']]) ? $chat[$v['parent_id']]['level_id'] : $root['level_id'];
                        
                        $this->database->execute('UPDATE level SET parent_id=:parent_id WHERE id=:id', [
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
            if($root = $this->database->query('SELECT * FROM level WHERE id=1 AND parent_id=0')) {
                return array_shift($root);
            }
            
            throw new \Exception("服务器在验证系统顶层组织时，没能满足其中的一个。", 412);
        }
    }
