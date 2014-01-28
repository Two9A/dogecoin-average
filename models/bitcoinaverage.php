<?php

class BitcoinAverageModel
{
    private $config;

    public function __construct()
    {
        $this->config = bsFactory::get('config');
    }

    public function read()
    {
        $ret = array();
        $f = file_get_contents($this->config->btcavg_url);
        if ($f) {
            $data = json_decode(trim($f), true);
            foreach ($data as $curr => $prices) {
                $ret[$curr] = $prices['last'];
            }
        }

        return $ret;
    }
}

