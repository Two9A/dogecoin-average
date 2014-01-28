<?php

class VircurexModel implements MarketInterfaceModel
{
    private $config;

    public function __construct()
    {
        $this->config = bsFactory::get('config');
    }

    public function read($market_ref)
    {
        list($base, $alt) = explode('/', $market_ref);
        $f = file_get_contents(sprintf($this->config->vircurex_price_url, $base, $alt));
        if ($f) {
            $data = json_decode($f, true);
            if ($data && $data['value']) {
                $price = $data['value'];
            }
        }

        $f = file_get_contents(sprintf($this->config->vircurex_volume_url, $base, $alt));
        if ($f) {
            $data = json_decode($f, true);
            if ($data && $data['value']) {
                $volume = $data['value'];
            }
        }

        if (isset($price, $volume)) {
            return array(
                'price'  => $price,
                'volume' => $volume
            );
        }

        return null;
    }
}

