<?php
    $phar = new CPhar('signal.phar', 'signal');
    $phar->create();
    
    class CPhar {
        private $__name, $__composer;
        
        public function __construct($name, $composer) {
            $this->__name = $name;
            $this->__composer = $composer;
        }

        public function create() {
            if(file_exists($this->__name)) {
                unlink($this->__name);
            }

            $phar = new Phar($this->__name, 0, $this->__name);
            $phar->buildFromDirectory(__DIR__ . '/' . $this->__composer);
            $phar->setDefaultStub('index.php', 'index.php');
            $phar->compressFiles(Phar::GZ);
        }

        public function release() {
            if(file_exists($this->__name)) {
                $phar = new Phar($this->__name);
                $phar->convertToData(Phar::ZIP);
            }
        }
    }
