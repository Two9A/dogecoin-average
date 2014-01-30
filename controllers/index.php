<?php

class indexController extends bsControllerBase
{
    private $reader;
    private $currency;

    private $bot_ua;

    public function __construct()
    {
        parent::__construct();
        $this->reader = new MarketReaderModel();
        $this->currency = new CurrencyModel();

        $this->bot_ua = array(
            'dogec0in.com',
            'mIRC32'
        );
    }

    public function indexAction()
    {
        $this->catchall('BTC');
    }

    public function catchall($method)
    {
        $this->view->add_asset('js', 'update.js');
        $this->view->add_asset('js', 'flot/jquery.flot.min.js');
        $this->view->add_asset('js', 'flot/jquery.flot.pie.min.js');
        $this->view->add_asset('js', 'flot/jquery.flot.time.min.js');

        $code = strtoupper($method);
        $raw_data = array();

        $currency = $this->currency->get_id($code);
        if ($currency) {
            $raw_data = array_merge(
                $this->reader->get_latest($currency),
                array('history' => $this->reader->get_history($currency))
            );
        }

        if (!$currency || $this->currency->is_fiat($code)) {
            $btc_data = $this->reader->get_latest(1);
            $history = $this->reader->get_history(1);
            $mul = $this->reader->get_btc_price($btc_data['ts'], $code);
    
            $btc_data['currency_name'] = $mul['currency_name'];
            $btc_data['currency_code'] = $code;
            $btc_data['doge_vwap'] = $btc_data['vwap'];
            $btc_data['btc_vwap'] = $mul['price'];
            $btc_data['vwap'] = sprintf('%.8f', $btc_data['vwap'] * $mul['price']);
            foreach ($btc_data['markets'] as $mk => $mv) {
                $btc_data['markets'][$mk]['price'] = sprintf('%.8f', $mv['price'] * $mul['price']);
            }
            foreach ($history as &$h) {
                $h['vwap'] *= $mul['price'];
            }
            $btc_data['history'] = $history;
        }

        if (count($raw_data) && count($raw_data['markets'])) {
            if ($this->currency->is_fiat($code)) {
                foreach ($raw_data['markets'] as $m) {
                    $btc_data['markets'][] = $m;
                }
                usort($btc_data['markets'], function($a, $b) {
                    $va = $a['volume'];
                    $vb = $b['volume'];
                    return $va==$vb ? 0 : (($va > $vb) ? -1 : 1);
                });

                $total = 0;
                $volume = 0;
                foreach ($btc_data['markets'] as $m) {
                    $volume += $m['volume'];
                    $total += ($m['volume'] * $m['price']);
                    if ($m['ts'] > $btc_data['ts']) {
                        $btc_data['ts'] = $m['ts'];
                    }
                }

                $btc_data['date'] = date('jS F Y, H:i (T)', $btc_data['ts']);
                $btc_data['vwap'] = sprintf('%.8f', ($volume ? ($total / $volume) : 0));

                foreach ($btc_data['history'] as $hk => $hx) {
                    $total   = $hx['vwap'] * $hx['volume'];
                    $volume  = $hx['volume'];
                    $total  += $raw_data['history'][$hk]['vwap'] * $raw_data['history'][$hk]['volume'];
                    $volume += $raw_data['history'][$hk]['volume'];

                    $btc_data['history'][$hk]['vwap'] = sprintf('%.8f', $total / $volume);
                    $btc_data['history'][$hk]['volume'] = $volume;
                }

                $this->view->data = $btc_data;
            } else {
                $this->view->data = $raw_data;
            }
        } else {
            $this->view->data = $btc_data;
        }

        foreach ($this->bot_ua as $ua) {
            if (strpos($_SERVER['HTTP_USER_AGENT'], $ua) !== false) {
                $this->view->prepend_title(sprintf('%.8f %s | ', $this->view->data['vwap'], $this->view->data['currency_code']));
                break;
            }
        }
        
        return 'index';
    }

    public function readAction()
    {
        $this->reader->read();
        $this->view->set_formatter('json');
    }
}

