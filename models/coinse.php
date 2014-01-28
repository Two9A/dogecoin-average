<?php

class CoinsEModel implements MarketInterfaceModel
{
    private $config;
    private $data;

    public function __construct()
    {
        $this->config = bsFactory::get('config');
        $f = file_get_contents($this->config->coinse_data_url);
        if ($f) {
            $this->data = json_decode($f, true);
        }
    }

    public function read($market_ref)
    {
        if ($this->data && $this->data['status']) {
            foreach ($this->data['markets'] as $k => $v) {
                if ($k == $market_ref) {
                    return array(
                        'price' => $v['marketstat']['ltp'],
                        'volume' => $v['marketstat']['24h']['volume']
                    );
                }
            }
        }

        return null;
    }
}

