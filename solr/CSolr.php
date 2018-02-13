<?php
    class CSolr {
        private $__host, $__timeout;
        private $__query, $__fields;

        public function query($query) {
            $data = array();
            $query = str_ireplace(array(
                '\'',
                '\"'
            ), '', $query);

            foreach ($this->__query as $k => $pattern) {
                if (preg_match($pattern, $query, $data) == 1) {
                    $fields = explode(',', $k);
                    $host = $this->__parseQuery(array_combine($fields, $data));
                    return array_merge(
                        array(
                            'recordHost' => $host,
                            'recordQuery' => $query,
                        ),
                        $this->__requestData($host)
                    );
                }
            }

            return array();
        }

        private function __requestData($host) {
            $ref = array(
                'recordState' => 0,
                'recordTotal' => 0,
                'recordSet' => array(),
            );
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $host,
                CURLOPT_CONNECTTIMEOUT => $this->__timeout,
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1,
            ));

            $data = json_decode(curl_exec($curl), true);
            $ref['recordState'] = curl_errno($curl);
            if ($ref['recordState'] == 0 && is_array($data) && isset($data['response'])) {
                $ref = array_merge($ref, array(
                    'recordTotal' => $data['response']['numFound'],
                    'recordSet' => $data['response']['docs'],
                ));
            }
            curl_close($curl);

            return $ref;
        }

        private function __parseQuery($data) {
            $server = "{$this->__host}{$data['core']}/select?";
            $query = array(
                'wt=json',
                'indent=off'
            );

            unset($data['query'], $data['core']);

            if (isset($data['q'])) {
                foreach ($this->__fields as $k => $rule) {
                    preg_match_all($rule['regular'], $data['q'], $ref);
                    foreach ($ref[0] as $v) {
                        list($field, $val) = explode($k, $v);
                        $rule['target'] = str_ireplace('variable', $val, $rule['target']);
                        if ($k == 'LIKE') {
                            $field = str_ireplace(' ', '', $field) . ':';
                            $rule['target'] = str_ireplace(array(
                                ' ',
                                $rule['source']
                            ), array(
                                '',
                                $rule['target']
                            ),
                                $val);
                        }
                        if ($k == '<>') {
                            $field = "-{$field}:";
                            $rule['target'] = str_ireplace($rule['source'], $rule['target'], $val);
                        }
                        $data['q'] = str_ireplace($v, str_ireplace($v, $field . $rule['target'], $v), $data['q']);
                    }
                }
            } else {
                $data['q'] = '*:*';
            }

            foreach ($data as $k => $v) {
                $v = rawurlencode($v);
                $query[] = "{$k}={$v}";
            }

            return $server . str_ireplace(' ', '%20', implode('&', $query));
        }

        public function __construct($host, $timeout = 5) {
            $this->__host = $host;
            $this->__timeout = $timeout;

            $this->__query = array(
                'query,fl,core,q,sort,start,rows' => '/SELECT\ ([\s\S]*)\ FROM\ ([\s\S]*)\ WHERE\ ([\s\S]*)\ ORDER\ BY\ ([\s\S]*)\ LIMIT\ (\d*)\,(\d*)/i',
                'query,fl,core,q,sort' => '/SELECT\ ([\s\S]*)\ FROM\ ([\s\S]*)\ WHERE\ ([\s\S]*)\ ORDER\ BY\ ([\s\S]*)/i',
                'query,fl,core,q,start,rows' => '/SELECT\ ([\s\S]*)\ FROM\ ([\s\S]*)\ WHERE\ ([\s\S]*)\ LIMIT\ (\d*)\,(\d*)/i',
                'query,fl,core,q' => '/SELECT\ ([\s\S]*)\ FROM\ ([\s\S]*)\ WHERE\ ([\s\S]*)/i',
                'query,fl,core,sort,start,rows' => '/SELECT\ ([\s\S]*)\ FROM\ ([\s\S]*)\ ORDER\ BY\ ([\s\S]*)\ LIMIT\ (\d*)\,(\d*)/i',
                'query,fl,core,start,rows' => '/SELECT\ ([\s\S]*)\ FROM\ ([\s\S]*)\ LIMIT\ (\d*)\,(\d*)/i',
                'query,fl,core' => '/SELECT\ ([\s\S]*)\ FROM\ ([\s\S]*)/i',
            );
            $this->__fields = array(
                '<>' => array(
                    'regular' => '/\S*<>\S*/i',
                    'source' => '<>',
                    'target' => ':variable'
                ),
                '>=' => array(
                    'regular' => '/\S*>=\S*/i',
                    'source' => '>=',
                    'target' => ':[variable TO *]'
                ),
                '<=' => array(
                    'regular' => '/\S*<=\S*/i',
                    'source' => '<=',
                    'target' => ':[* TO variable]'
                ),
                '>' => array(
                    'regular' => '/\S*>\S*/i',
                    'source' => '>',
                    'target' => ':{variable TO *]'
                ),
                '<' => array(
                    'regular' => '/\S*<\S*/i',
                    'source' => '<',
                    'target' => ':[* TO variable}'
                ),
                '=' => array(
                    'regular' => '/\S*=\S*/i',
                    'source' => '=',
                    'target' => ':variable'
                ),
                'LIKE' => array(
                    'regular' => '/\S*\ LIKE \S*/i',
                    'source' => '%',
                    'target' => '*'
                ),
            );
        }
    }