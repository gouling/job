<?php
    class CLog {
        const CONSOLE = 'CONSOLE';
        const FILE = 'FILE';
        
        private $__dir, $__handle;
        
        public function __construct($handle = self::CONSOLE) {
            $this->__dir = __DIR__ . DIRECTORY_SEPARATOR;
            $this->__handle = $handle;
        }
        
        public function info($message) {
            $this->__logHandle(sprintf('%s INFO %s%s', time(), $message, PHP_EOL));
        }
        
        private function __logHandle($message) {
            switch($this->__handle) {
                case self::CONSOLE:
                    printf($message);
                    break;
                case self::FILE:
                    file_put_contents($this->__dir . date('Y-m-d') . '.txt', $message, FILE_APPEND);
                    break;
            }
        }
    }
