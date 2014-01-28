<?php

class CurrencyModel
{
    private $dbc;
    
    public function __construct()
    {
        $this->dbc = bsFactory::get('pdo')->get();
    }

    public function get_id($code)
    {
        $st = $this->dbc->prepare('SELECT currency_id FROM currencies WHERE currency_code = :curr');
        $st->bindValue(':curr', $code);
        $st->execute();
        
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows)) {
            return $rows[0]['currency_id'];
        }

        return 0;
    }
}

