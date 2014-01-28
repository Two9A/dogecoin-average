<?php

class CoinExModel implements MarketInterfaceModel
{
    private $config;
    private $data;

    public function __construct()
    {
        $this->config = bsFactory::get('config');
        $f = file_get_contents($this->config->coinex_data_url);
        if ($f) {
            $this->data = json_decode($f, true);
        }
    }

    public function read($market_ref)
    {
        if ($this->data && $this->data['trade_pairs']) {
            foreach ($this->data['trade_pairs'] as $pair) {
                if ($pair['url_slug'] == $market_ref) {
                    return array(
                        'price'  => $pair['last_price'] / 100000000,
                        'volume' => $pair['currency_volume'] / 100000000
                    );
                }
            }
        }

        return null;
    }
}
