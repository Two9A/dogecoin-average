<?php

class MarketReaderModel
{
    private $dbc;
    private $config;

    public function __construct()
    {
        $this->dbc = bsFactory::get('pdo')->get();
        $this->config = bsFactory::get('config');
    }

    public function read()
    {
        $models = array();

        $ts = time();

        $wst = $this->dbc->prepare('INSERT INTO prices(ts, market_id, price, volume) VALUES(:ts, :market, :price, :volume)');
        $bst = $this->dbc->prepare('INSERT INTO btc_price(ts, currency_code, price) VALUES(:ts, :curr, :price)');
        $st = $this->dbc->query(
            'SELECT m.market_id, m.market_ref, e.exchange_name, e.exchange_model FROM markets m
            LEFT JOIN exchanges e ON m.exchange_id = e.exchange_id'
        );
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $market) {
            if (!isset($models[$market['exchange_model']])) {
                $modelname = $market['exchange_model'] . 'Model';
                $models[$market['exchange_model']] = new $modelname;
            }
            $data = $models[$market['exchange_model']]->read($market['market_ref']);

            if ($data) {
                $wst->bindValue(':ts',     $ts);
                $wst->bindValue(':market', $market['market_id']);
                $wst->bindValue(':price',  $data['price']);
                $wst->bindValue(':volume', $data['volume']);
                $wst->execute();
            }
        }

        $btcavg = new BitcoinAverageModel;
        foreach ($btcavg->read() as $curr => $price) {
            $bst->bindValue(':ts',    $ts);
            $bst->bindValue(':curr',  $curr);
            $bst->bindValue(':price', $price);
            $bst->execute();
        }
    }

    public function get_btc_price($ts, $currency)
    {
        $cst = $this->dbc->prepare(
            'SELECT p.price, c.currency_name FROM btc_price p
            LEFT JOIN btc_currency c ON p.currency_code=c.currency_code
            WHERE p.ts=:ts AND p.currency_code=:curr'
        );
        $cst->bindValue(':ts', $ts);
        $cst->bindValue(':curr', $currency);
        $cst->execute();

        $rows = $cst->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows)) {
            return $rows[0];
        } else {
            throw new Exception('Invalid currency code', 404);
        }
    }

    public function get_latest($currency)
    {
        $cst = $this->dbc->prepare('SELECT currency_name, currency_code FROM currencies WHERE currency_id=:curr');
        $cst->bindValue(':curr', $currency);
        $cst->execute();
        $ret = $cst->fetch(PDO::FETCH_ASSOC);
        $ts = 0;

        $st = $this->dbc->prepare(
            'SELECT p.price, p.volume, p.ts, m.market_url, e.exchange_name FROM prices p
            INNER JOIN (
                SELECT market_id, MAX(ts) AS max_ts FROM prices GROUP BY market_id
            ) q ON (p.ts=q.max_ts AND p.market_id=q.market_id)
            LEFT JOIN markets m ON p.market_id=m.market_id
            LEFT JOIN exchanges e ON m.exchange_id=e.exchange_id
            WHERE m.currency_id=:currency
            ORDER BY p.volume DESC'
        );
        $st->bindValue(':currency', $currency);
        $st->execute();
        $ret['markets'] = $st->fetchAll(PDO::FETCH_ASSOC);

        $total = 0;
        $volume = 0;
        foreach ($ret['markets'] as $market) {
            $volume += $market['volume'];
            $total += ($market['volume'] * $market['price']);
            if ($market['ts'] > $ts) {
                $ts = $market['ts'];
            }
        }

        $ret['ts']   = $ts;
        $ret['date'] = date('jS F Y, H:i (T)', $ts);
        $ret['vwap'] = sprintf('%.8f', $total / $volume);

        return $ret;
    }

    public function get_history($currency, $timescale = 86400, $step = 10)
    {
        $ts = time();
        $st = $this->dbc->prepare(
            'SELECT (SUM(p.price * p.volume) / SUM(p.volume)) AS vwap, p.ts FROM prices p
            LEFT JOIN markets m ON p.market_id=m.market_id
            LEFT JOIN exchanges e ON m.exchange_id=e.exchange_id
            WHERE m.currency_id=:currency AND p.ts BETWEEN :start AND :end
            GROUP BY p.ts
            ORDER BY p.ts ASC'
        );
        $st->bindValue(':currency', $currency);
        $st->bindValue(':start', $ts - $timescale);
        $st->bindValue(':end', $ts);
        $st->execute();

        $data = $st->fetchAll(PDO::FETCH_ASSOC);
        $i = 0;
        $ret = array();
        foreach ($data as &$r) {
            $r['time'] = date('H:i', $r['ts']);
            if (++$i == $step) {
                $ret[] = $r;
                $i = 0;
            }
        }

        return $ret;
    }
}
