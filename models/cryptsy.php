<?php

class CryptsyModel implements MarketInterfaceModel
{
    private $config;
    private $data;

    public function __construct()
    {
        $this->config = bsFactory::get('config');
        $f = file_get_contents($this->config->cryptsy_data_url);
        if ($f) {
            $this->data = json_decode($f, true);
        }
    }

    public function read($market_ref)
    {
        if ($this->data && $this->data['success']) {
            foreach ($this->data['return']['markets'] as $k => $v) {
                if ($k == $market_ref) {
                    return array(
                        'price' => $v['lasttradeprice'],
                        'volume' => $v['volume']
                    );
                }
            }
        }

        return null;
    }
}

