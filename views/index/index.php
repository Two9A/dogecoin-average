<p class="blurb">The global volume-weighted average price of one Dogecoin is:</p>
<p class="price">
 <span id="curr_price"><?=$this->data['vwap']?></span>
 <span class="curr_currency"><acronym title="<?=$this->data['currency_name']?>"><?=$this->data['currency_code']?></acronym></span>
 <br>
 <?php if (isset($this->data['btc_vwap'])): ?>
 <span class="btc_display">
  <span id="doge_price"><?=$this->data['doge_vwap']?></span> BTC per DOGE,
  <span id="btc_price"><?=$this->data['btc_vwap']?></span>
  <span class="curr_currency"><acronym title="<?=$this->data['currency_name']?>"><?=$this->data['currency_code']?></acronym></span>
  per BTC
 </span>
 <br>
 <?php endif; ?>
 <span id="curr_date"><?=$this->data['date']?></span>
</p>
<ul class="currencies">
 <?php $i=0; $currencies=array('BTC','LTC','USD','CAD','GBP','EUR','CNY','AUD'); foreach ($currencies as $c): $i++; ?>
 <li class="<?=$this->data['currency_code']==$c?'curr':''?>"><a href="/<?=$c?>"><?=$c?></a></li>
 <?php endforeach; ?>
</ul>
<div class="api_link">
 <a href="/<?=$this->data['currency_code']?>.json">API data for this currency</a>
</div>
<div class="charts">
 <h2>24h price movement</h2>
 <div class="value_24h" style="width:700px;height:250px"></div>
</div>
<p class="blurb clear">This price has been calculated based on the following exchanges:</p>
<table class="volumes">
 <thead><tr><th>Exchange</th><th>Price</th><th>Volume</th></tr></thead>
 <tbody>
  <?php foreach ($this->data['markets'] as $market): ?>
  <tr>
   <td>
    <a href="<?=$market['market_url']?>"><?=$market['exchange_name']?></a>
    <br>(<?=floor(($this->data['ts']-$market['ts'])/60)?>m ago)
   </td>
   <td><?=$market['price']?></td>
   <td><?=$market['volume']?></td>
  </tr>
  <?php endforeach; ?>
 </tbody>
</table>
<div class="volume_chart" style="width:325px;height:300px"></div>
<div class="clear">&nbsp;</div>
<div class="initialdata"><?=json_encode($this->data)?></div>
