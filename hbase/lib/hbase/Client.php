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

        public function delRow($table, $key) {
            $this->__client->deleteAllRow($table, $key, []);
        }

        public function addRow($data) {
            foreach ($data['data'] as $column => $value) {
                $this->__client->mutateRow($data['table'], $data['key'], [
                    new Mutation([
                        'column' => $column,
                        'value' => $value
                    ])
                ], []);
            }
        }

        public function addRows($data) {
            foreach ($data as $v) {
                $this->addRow($v);
            }
        }

        public function getRows($table) {
            $list = [];
            $k = 1;
            while ($v = $this->__client->getRow($table, $k++, [])) {
                $v = array_pop($v);

                foreach ($v->columns as $column => $value) {
                    $list[$v->row][$column] = $value->value;
                }
            }

            return $list;
        }

        public function getTableNames() {
            $desc = [];
            $tables = $this->__client->getTableNames();
            foreach ($tables as $table) {
                $desc[$table] = $this->__client->getColumnDescriptors($table);
            }

            return $desc;
        }

        public function __destruct() {
            $this->__transport->close();
        }
    }