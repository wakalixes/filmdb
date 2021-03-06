<?= '<?xml version="1.0" encoding="iso-8859-1"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
  <title>meine private Filmliste</title>
  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1" />
  <style type="text/css">
    #listtable td { font-size: 0.8em; font-family: verdana; }
    #listtable tr:hover { background-color:#f1f1f1; }
    #listtable a { font-weight: bold; text-decoration: none; color: #000000 }
    #listtable a:hover { font-weight: bold; text-decoration: underline; color: #000000 }
    #formtable td, input, p { vertical-align: top; font-size: 0.8em; font-family: verdana; }
  </style>
</head>

<body>
<?php

if (isset($_GET["search"])) {
	$search_title = $_GET["title"];
	$url = 'http://www.imdb.com/find?s=all&q=';
	$url = $url.urlencode($search_title);
	
	$imdb_search = get_data($url);
	echo $url.'<br>';
	$last_match_pos = 0;
	do {
		$ret = preg_match('/result_text.*?<a href="\/title\/(\S{9}).*?" >(.*?)<\/a> \((.*?)\) .*?<\/td>/',$imdb_search,$matches,PREG_OFFSET_CAPTURE,$last_match_pos);
		$found_match = $ret;
		if ($found_match) {
  		$titleid = $matches[1][0];
	  	$title = $matches[2][0];
  		$year = $matches[3][0];
  		$last_match_pos = $matches[3][1];
  		echo '<a href="imdb-parser.php?parse=1&titleid='.$titleid.'">'.$title.' ('.$year.')</a><br>';
  	  echo $matches[1][1].' ';
  	  echo $matches[2][1].' ';
  	  echo $matches[3][1].'<br>';
  	}
  } while ($found_match);
}

if (isset($_GET["parse"])) {
	//url
	$titleid = $_GET["titleid"];
	$url = 'http://www.imdb.com/title/'.$titleid.'/';
	$imdb_content = get_data($url);
	//parse movie details
	$name = get_match('/<title>(.*) \(\d{4}\).*?<\/.*?>/isU',$imdb_content);
	$year = get_match('/<title>.*?\((\d{4})\).*?<\/title>/is',$imdb_content);
	$director = strip_tags(get_match('/<h4.*Director:.*<\/h4>.*<a.*>(.*)<\/a>/isU',$imdb_content));
	$run_time = get_match('/Runtime:<\/h4>.*?\n.*?(\d*) min/is',$imdb_content);
	//load extended credits
	$creditsurl = $url.'fullcredits';
	$imdb_credit_content = get_data($creditsurl);
	$cinemat = strip_tags(get_match('/<h4.*Cinematography by.*<\/h4>.*<a.*>(.*)<\/a>/isU',$imdb_credit_content));
  //echo $cinemat;
	echo 'parsed successfully';

	//build content

	echo '<br>';
	echo $name.'<br>';
	echo $year.'<br>';
	echo $director.'<br>';
	echo $cinemat.'<br>';
	echo $run_time.'<br>';
}

//gets the match content
function get_match($regex,$content)
{
	preg_match($regex,$content,$matches);
	return $matches[1];
}

//gets the data from a URL
function get_data($url)
{
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}
?>

</body>
</html>
