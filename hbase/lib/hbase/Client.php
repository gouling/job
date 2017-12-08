<?php

    namespace HBase;

    require 'HBase.php';
    require 'Types.php';

    class Client {
        private $__sock, $__transport, $__protocol, $__client;

        public function __construct($address = 'localhost', $port = 9090, $timeout = 5000) {
            $this->__sock = new \Thrift\Transport\TSocket('localhost', 9090);
            $this->__sock->setRecvTimeout($timeout);
            $this->__sock->setRecvTimeout($timeout);
            $this->__transport = new \Thrift\Transport\TBufferedTransport($this->__sock);
            $this->__protocol = new \Thrift\Protocol\TBinaryProtocol($this->__transport);
            $this->__client = new \Hbase\HbaseClient($this->__protocol);
            $this->__transport->open();
        }

        public function search($table, $startRow, $stopRow, $column = [], $nbRows = 10) {
            $id = $this->__client->scannerOpenWithStop($table, $startRow, $stopRow, $column, []);
            return $this->__client->scannerGet($id);
        }

        /**
         * 编辑行记录
         * @param $table
         * @param $key
         * @param $data
         */
        public function setRow($table, $key, $data) {
            $this->addRow($table, $key, $data);
        }

        /**
         * 编辑行记录
         * @param $data
         */
        public function setRows($data) {
            foreach ($data as $v) {
                $this->addRow($v['table'], $v['key'], $v['data']);
            }
        }

        /**
         * 添加行记录
         * @param $table
         * @param $key
         * @param $data
         */
        public function addRow($table, $key, $data) {
            $mutations = array();

            foreach ($data as $column => $value) {
                $mutations[] = new Mutation([
                    'column' => $column,
                    'value' => $value
                ]);
            }

            $this->__client->mutateRow($table, $key, $mutations, []);
        }

        /**
         * 添加行记录
         * @param $data
         */
        public function addRows($data) {
            foreach ($data as $v) {
                $this->addRow($v['table'], $v['key'], $v['data']);
            }
        }

        /**
         * 查询行记录
         * @param $table
         * @param $key
         * @return array
         */
        public function getRow($table, $key) {
            $list = [];

            if ($v = $this->__client->getRow($table, $key, [])) {
                $v = array_pop($v);

                foreach ($v->columns as $column => $value) {
                    $list[$column] = $value->value;
                }
            }

            return $list;
        }

        /**
         * 查询行记录
         * @param $table
         * @return array
         */
        public function getRows($table) {
            $list = [];
            $k = 1;
            while ($v = $this->getRow($table, $k, [])) {
                $list[$k++] = $v;
            }

            return $list;
        }

        /**
         * 删除行记录
         * @param $table
         * @param $key
         */
        public function delRow($table, $key) {
            $this->__client->deleteAllRow($table, $key, []);
        }

        /**
         * 查询表信息
         * @param null $name
         * @return array|mixed
         */
        public function getTableNames($name = null) {
            $desc = [];
            $tables = $this->__client->getTableNames();

            foreach ($tables as $table) {
                $desc[$table] = $this->__client->getColumnDescriptors($table);
            }

            return !is_null($name) && isset($desc[$name]) ? $desc[$name] : $desc;
        }

        public function __destruct() {
            $this->__transport->close();
        }
    }