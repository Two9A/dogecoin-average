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

        $currency = $this->currency->get_id(strtoupper($method));
        if ($currency) {
            $this->view->data = array_merge(
                $this->reader->get_latest($currency),
                array('history' => $this->reader->get_history($currency))
            );
        } else {
            $data = $this->reader->get_latest(1);
            $history = $this->reader->get_history(1);
            $mul = $this->reader->get_btc_price($data['ts'], strtoupper($method));

            $data['currency_name'] = $mul['currency_name'];
            $data['currency_code'] = strtoupper($method);
            $data['doge_vwap'] = $data['vwap'];
            $data['btc_vwap'] = $mul['price'];
            $data['vwap'] = sprintf('%.8f', $data['vwap'] * $mul['price']);
            foreach ($history as &$h) {
                $h['vwap'] *= $mul['price'];
            }
            $data['history'] = $history;
            $this->view->data = $data;
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

