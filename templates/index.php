<!doctype html>
<html>
 <head>
  <title><?=$this->__info['title']?></title>
  <link rel="shortcut icon" href="http://www.dogecoinaverage.com/favicon.ico?v=2">
  <link rel="stylesheet" type="text/css" href="/css/style.css">
  <?php foreach ($this->__assets['css'] as $css) { ?>
  <link rel="stylesheet" type="text/css" href="/css/<?=$css?>">
  <?php } ?>
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <?php foreach ($this->__assets['js'] as $js) { ?>
  <script type="text/javascript" src="/js/<?=$js?>"></script>
  <?php } ?>
 </head>
 <body>
  <div class="header">
   <h1><a href="http://www.dogecoinaverage.com/">DogecoinAverage</a></h1>
   <ul class="links">
   </ul>
  </div>
  <div class="container">
   <?=$view_output?>
  </div>
  <div class="footer">
   <div class="donate">DGDogexsRob7eQtQ4oR2vG39ZhCWXeoUvG</div>
   <div class="contact">admin@dogecoinaverage.com</div>
   <div class="clear"></div>
  </div>
 </body>
</html>
