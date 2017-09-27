<?php
	$report=new CReportClient(array(
		'100001'=>'http://localhost/index.php',
		'100002'=>'http://localhost/index.php',
		'100003'=>'http://localhost/index.php',
		'100004'=>'http://localhost/index.php'
	), __DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR, array(
		'key'=>100003,
		'name'=>'芶凌'
	));
	var_dump($report->download());

	class CReportClient {
		private $__cache=null, $__config=null, $__zip=null;
		private $__multi=null, $__curl=array();
		private $__file=array(), $__name=array(), $__status=array(), $__identity=0;

		public function download() {
			do {
				curl_multi_exec($this->__multi, $running);
			} while ($running>0);

			foreach ($this->__curl as $key=>$val) {
				fclose($this->__file[$key]);
				curl_multi_remove_handle($this->__multi, $this->__curl[$key]);
				
				$status=curl_getinfo($val)['http_code'];
				$code=explode('.', $key)[6];
				
				$this->__log('-------------------------');
				if($this->__status[$code]=$this->__extractTo($status, $key, $this->__name[$key])) {
					$this->__log("{$code} {$status} true");
					$this->__loadData($key, $code);
				} else {
					$this->__log("{$code} {$status} false");
				}
				$this->__log('');
			}
		
			return $this->__status;
		}

		private function __loadData($key, $code) {
			$list=opendir($this->__cache.$key);
			while (($file=readdir($list))!=false) {
				if($file=='.' || $file=='..') {
					continue;
				}

				$info=explode('.', $file);
				$file=$this->__cache.$key.DIRECTORY_SEPARATOR.$file;
				$data=array(
					'id'=>$code,
					'name'=>$info[0],
					'page'=>$info[1],
					'db'=>json_decode(file_get_contents($file))
				);
				if($refer=$this->__callAction($data)) {
					unlink($file);
					$refer='true';
				} else {
					$refer='false';
				}

				$this->__log("{$data['id']} {$data['name']} {$data['page']} {$refer}");
			}
			closedir($list);
		}

		private function __callAction($data) {
			return true;
		}

		private function __extractTo($status, $key, $file) {
			if ($status==200 && $this->__zip->open($file)==true) {
				$status=$this->__zip->extractTo($this->__cache.$key);
				$this->__zip->close();
			} else {
				$status=false;
			}

			return $status;
		}

		public function __construct($config, $cache, $data=array(), $time=0) {
			ini_set('default_socket_timeout', 0);
			ini_set('display_errors', 0);

			$this->__identity=date('Y.m.d H.i.s', time()).sprintf('.%u.', rand(1000,9999));
			$this->__cache=$cache;
			$this->__config=$config;

			$this->__zip=new ZipArchive();
			$this->__multi=curl_multi_init();

			foreach($this->__config as $key=>$val) {
				$key=$this->__identity.$key;

				$this->__name[$key]=$this->__cache.$key.'.zip';
				$this->__file[$key]=fopen($this->__name[$key], 'wb');
				$this->__curl[$key]=curl_init($val);
				curl_setopt($this->__curl[$key], CURLOPT_FILE, $this->__file[$key]);
				curl_setopt($this->__curl[$key], CURLOPT_HEADER, 0);
				curl_setopt($this->__curl[$key], CURLOPT_TIMEOUT, $time);
				curl_setopt($this->__curl[$key], CURLOPT_POSTFIELDS, http_build_query($data));

				curl_multi_add_handle($this->__multi, $this->__curl[$key]);
			}
		}

		public function __destruct() {
			curl_multi_close($this->__multi);

			foreach($this->__name as $key=>$val) {
				unlink($val);
				rmdir($this->__cache.$key);
			}
		}

		private function __log($msg) {
			file_put_contents("{$this->__cache}{$this->__identity}log", $msg.PHP_EOL, FILE_APPEND);
		}
	}
?>