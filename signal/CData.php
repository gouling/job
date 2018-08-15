<?php
    class CData {
        /**
         * 获取需要处理的数据
         */
        public function get():array {
            return [
                'time' => time()
            ];
        }
        
        /**
         * 数据处理并返回结果
         * @return [code, message]
         */
        public function set($data = []):array {
            return [
                'code' => 200,
                'message' => '已完成'
            ];
        }
    }
