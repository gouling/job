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

        /**
         * AND OR = > < >= <= !=
         *
         * binary 等于
         * binaryprefix 前缀
         * regexstring 正则
         * substring 包含

         * RowFilter(=,'binary:1') 记录(指定行键)
         * PrefixFilter('1') 记录(指定行键前缀)
         * FamilyFilter(=, 'binary:login') 记录(查找列族等于字符的字段)
         * ValueFilter(=, 'binary:芶凌') 记录(查找行值等于字符的字段)
         * QualifierFilter(=, 'binary:username') 记录(字段)
         * ColumnPrefixFilter('telephone') 记录(字段前缀)
         * ColumnRangeFilter('address', true, 'telephone', true) 记录(指定字段范围), bool值是否包含等于
         * MultipleColumnPrefixFilter('sex', 'telephone') 记录(多个字段)
         * SingleColumnValueFilter('info', 'telephone', =, 'binary:17612800917') 记录(列族指定列查找)
         *
         * @param $table
         * @param array $filter [filterString, startRow, stopRow]
         * @param int $nbRows
         * @return array
         */
        public function search($table, $filter = [], $nbRows = 10) {
            $scan = new TScan($filter);

            $list = [];
            $id = $this->__client->scannerOpenWithScan($table, $scan, []);

            if ($data = $this->__client->scannerGetList($id, $nbRows)) {
                foreach ($data as $v) {
                    foreach ($v->columns as $column => $value) {
                        $list[$v->row][$column] = $value->value;
                    }
                }
            }

            return $list;
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
         * @param $startRow
         * @param $nbRows
         * @return array
         */
        public function getRows($table, $startRow = 1, $nbRows = 1000) {
            $list = [];
            $id = $this->__client->scannerOpen($table, $startRow, [], []);
            if ($data = $this->__client->scannerGetList($id, $nbRows)) {
                foreach ($data as $v) {
                    foreach ($v->columns as $column => $value) {
                        $list[$v->row][$column] = $value->value;
                    }
                }
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

        /**
         * 创建表
         * @param $tableName
         * @param $columns
         */
        public function createTable($tableName, $columns) {
            $this->__client->createTable($tableName, $columns);
        }

        public function __destruct() {
            $this->__transport->close();
        }
    }