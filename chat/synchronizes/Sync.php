<?php
    namespace Synchronizes;
    
    class Sync extends Chat {
        public function auth() {
            $this->update();
        }
        
        public function cancel() {
            $trans = [
                "DELETE FROM chat_ent WHERE id='{$this->opts['id']}'",
                "DELETE FROM level_user USING level_user,chat_level WHERE chat_level.ent_id='{$this->opts['id']}' AND level_user.level_id=chat_level.level_id",
                "DELETE FROM level_user USING level_user,chat_user WHERE chat_user.ent_id='{$this->opts['id']}' AND level_user.user_id=chat_user.user_id",
                "DELETE FROM level USING level,chat_level WHERE chat_level.ent_id='{$this->opts['id']}' AND level.id=chat_level.level_id",
                "DELETE FROM chat_level WHERE ent_id='{$this->opts['id']}'",
                "DELETE FROM user USING user,chat_user WHERE chat_user.ent_id='{$this->opts['id']}' AND user.id=chat_user.user_id",
                "DELETE FROM chat_user WHERE chat_user.ent_id='{$this->opts['id']}'",
                "ALTER TABLE level AUTO_INCREMENT=1",
                "ALTER TABLE user AUTO_INCREMENT=1",
            ];
            
            return $this->database->multi($trans);
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

            $this->database->beginTransaction();
            try {
                // 企业微信与系统层级信息
                foreach($departs as $depart) {
                    $this->updateLevel($depart);
                }
                // 更新系统层级信息与关系信息
                $this->updateParentId();
                $this->updateRel();
                
                $this->database->commit();
            } catch (\Exception $e) {
                $this->database->rollback();
                throw $e;
            }

            // 获取企业微信层级包含与系统层级的关系信息，为更新用户部门做准备
            $root_level = $this->getChatRootLevel();
            $chat_level = $this->database->query("SELECT * FROM chat_level WHERE ent_id='{$this->opts['id']}' ORDER BY id ASC");
            $chat_level = array_combine(array_column($chat_level, 'id'), $chat_level);
  
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
                    throw $e;
                }
            }
        }
        
        public function updateEnt($auth) {
            $this->database->beginTransaction();
            try {
                if(is_null($ent = $this->getEntById())) {
                    $this->database->create('chat_ent', $auth);
                } else {
                    $this->database->update('chat_ent', $auth);
                    // 移除不可见层信息
                    if($deleted = array_diff(explode(',', $ent['departs']), explode(',', $auth['departs']))) {
                        $departs = array_column($this->database->query("SELECT level_id FROM chat_level WHERE ent_id='{$this->opts['id']}' AND id IN (" . implode(',', $deleted) . ")"), 'level_id');
                        $this->deleteLevel($departs);
                    }
                }
                $this->database->commit();
            } catch (\Exception $e) {
                $this->database->rollback();
                throw $e;
            }
        }

        public function deleteLevel($departs) {
            $deleted = implode(',', $departs);
            $trans = [];
            
            /**
             * 成员仅在此部门，部门完全等于
             * 成员在多个部门，此部门在中间
             * 成员在多个部门，此部门在最前
             * 成员在多个部门，此部门在最后
             * 成员无部门信息，移除微信接点
             * 成员无部门信息，移除系统成员信息
             * 移除企业微信层级信息
             * 移除系统层级信息
             */
            foreach($departs as $id) {
                $trans = array_merge($trans, [
                    "UPDATE user SET level='' WHERE level='{$id}'",
                    "UPDATE user SET level=REPLACE(level, ',{$id},', ',') WHERE level LIKE '%,{$id},%'",
                    "UPDATE user SET level=REPLACE(level, '{$id},', '') WHERE level LIKE '{$id},%'",
                    "UPDATE user SET level=REPLACE(level, ',{$id}', '') WHERE level LIKE '%,{$id}'",
                ]);
            }
            $trans = array_merge($trans, [
                "DELETE FROM chat_user USING user,chat_user WHERE chat_user.ent_id='{$this->opts['id']}' AND chat_user.user_id=user.id AND user.level=''",
                "DELETE FROM user WHERE level=''",
                "DELETE FROM level USING level,chat_level WHERE chat_level.ent_id='{$this->opts['id']}' AND level.id=chat_level.level_id AND chat_level.level_id IN ({$deleted})",
                "DELETE FROM chat_level WHERE ent_id='{$this->opts['id']}' AND level_id IN ({$deleted})"
            ]);

            $this->database->multi($trans);
        }
        
        public function updateLevel($depart) {
            if($chat = $this->database->find('SELECT * FROM chat_level WHERE ent_id=:ent_id AND id=:id', [
                'ent_id' => $this->opts['id'],
                'id' => $depart['id'],
            ])) {
                $this->database->execute('UPDATE chat_level SET parent_id=:parent_id WHERE ent_id=:ent_id AND id=:id', [
                    'ent_id' => $this->opts['id'],
                    'id' => $depart['id'],
                    'parent_id' =>  $depart['parentid'],
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
            sort($user['level']);
            
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
                $chat = $this->getChatById($user['userid']);
            } else {
                $this->database->execute('UPDATE chat_user SET chat_id=:chat_id WHERE ent_id=:ent_id AND chat_id=:chat_id', [
                    'ent_id' => $this->opts['id'],
                    'chat_id' => $user['userid'],
                ]);
                
                $this->database->update('user', $values + [
                    'id' => $chat['user_id'],
                ]);
            }
            
            foreach($user['level'] as $level_id) {
                $this->updateLevelUser($level_id, $chat['user_id']);
            }
        }
        
        public function updateLevelUser($level_id, $user_id) {
            $data = compact('level_id', 'user_id');
            
            $rel = $this->database->find('SELECT * FROM level_user WHERE level_id=:level_id AND user_id=:user_id', $data);
            if(is_null($rel)) {
                $this->database->create('level_user', $data);
            }
        }

        public function getEntById() {
            return $this->database->find('SELECT * FROM chat_ent WHERE id=:id', [
                'id' => $this->opts['id'],
            ]);
        }
        
        public function getChatById($id) {
            return $this->database->find('SELECT * FROM chat_user WHERE ent_id=:ent_id AND chat_id=:chat_id', [
                'ent_id' => $this->opts['id'],
                'chat_id' => $id,
            ]);
        }
        
        public function getUserById($id) {
            return $this->database->find('SELECT * FROM user WHERE id=:id', [
                'id' => $id,
            ]);
        }
        
        public function setDatabase(&$database) {
            $this->database = $database;
        }
        
        public function updateRel() {
            $sys = $this->database->query("SELECT level.* FROM level,chat_level WHERE level.id=chat_level.level_id AND chat_level.ent_id='{$this->opts['id']}' ORDER BY level.id ASC");
            
            $tree = array_combine(array_column($sys, 'id'), $sys);
            $this->database->level($tree, 'id', 'parent_id');

            foreach($tree as $v) {
                $this->database->execute('UPDATE level SET rel=:rel WHERE id=:id', [
                    'rel' => $v['rel'],
                    'id' => $v['id']
                ]);
            }
        }

        public function updateParentId() {
            $root = $this->getChatRootLevel();
            $chat = $this->database->query("SELECT * FROM chat_level WHERE ent_id='{$this->opts['id']}' ORDER BY id ASC");
            $sys = $this->database->query("SELECT level.* FROM level,chat_level WHERE level.id=chat_level.level_id AND chat_level.ent_id='{$this->opts['id']}' ORDER BY level.id ASC");

            $chat = array_combine(array_column($chat, 'id'), $chat);
            $sys = array_combine(array_column($sys, 'id'), $sys);
            
            foreach($chat as $v) {
                if($v['parent_id'] != 0) {
                    $parent_id = isset($chat[$v['parent_id']]) ? $chat[$v['parent_id']]['level_id'] : $root['level_id'];
                    
                    $this->database->execute('UPDATE level SET parent_id=:parent_id WHERE id=:id', [
                        'parent_id' => $parent_id,
                        'id' => $v['level_id']
                    ]);
                }
            }
        }
        
        protected function getChatRootLevel() {
            if($root = $this->database->find("SELECT * FROM chat_level WHERE ent_id='{$this->opts['id']}' AND id=1 AND parent_id=0")) {
                return $root;
            }
            
            throw new \Exception("服务器在验证企业微信顶层组织时，没能满足其中的一个。", 412);
        }
        
        protected function getSysRootLevel() {
            $chat = $this->getChatRootLevel();
            
            if($root = $this->database->find("SELECT * FROM level WHERE id={$chat['level_id']} AND parent_id=0")) {
                return $root;
            }
            
            throw new \Exception("服务器在验证系统顶层组织时，没能满足其中的一个。", 412);
        }
    }
