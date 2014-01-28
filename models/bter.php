<?php

class BterModel implements MarketInterfaceModel
{
    private $config;

    public function __construct()
    {
        $this->config = bsFactory::get('config');
    }

    public function read($market_ref)
    {
        $f = file_get_contents(sprintf($this->config->bter_data_url, $market_ref));
        if ($f) {
            $data = json_decode($f, true);
            if ($data && $data['result'] == 'true') {
                return array(
                    'price'  => $data['last'],
                    'volume' => $data['vol_doge']
                );
            }
        }

        return null;
    }
}

