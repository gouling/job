<?php
    class Sync extends Chat {
        public function auth() {
            $departs = $this->getDeparts();
            foreach($departs as $depart) {
                $users = $this->getUsers($depart['id']);
                
                try {
                    $this->addDepart($depart);
                    foreach($users as $user) {
                        $this->addUser($user);
                    }
                } catch(\Exception $e) {
                    throw $e;
                }
            }
        }
        
        public function addDepart($depart) {
            print_r($depart);
        }
        
        public function addUser($user) {
            print_r($user);
        }
    }
