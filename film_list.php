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
    #searchtable td { font-size: 0.8em; font-family: verdana; }
    #searchtable tr:hover { background-color:#f1f1f1; }
    #searchtable a { font-weight: bold; text-decoration: none; color: #000000 }
    #searchtable a:hover { font-weight: bold; text-decoration: underline; color: #000000 }
    #stattable td { font-size: 0.8em; font-family: verdana; }
    #counttable td { font-size: 0.8em; font-family: verdana; }
    #counttable tr:hover { background-color:#f1f1f1; }
    #formtable td, input, p { vertical-align: top; font-size: 0.8em; font-family: verdana; }
    #formtable a { font-weight: bold; text-decoration: none; color: #000000 }
    #formtable a:hover { font-weight: bold; text-decoration: underline; color: #000000 }
  </style>
</head>

<body>

<?php
$db_server = 'localhost';
$db_user = '';
$db_passwort = '';
$link = mysql_connect($db_server, $db_user, $db_passwort) or die ('Keine Verbindung'.mysql_error());
mysql_select_db("") or die('Auswahl der Datenbank fehlgeschlagen.');
if (isset($_POST["date"])) $date = $_POST["date"];
if (isset($_GET["date"])) $date = $_GET["date"];

$parsing = FALSE;
if (isset($_GET["parse"]) || isset($_POST["parselink"])) {
  $parsing = TRUE;
  //get IMDB title page
  if (isset($_GET["parse"])) {
    $titleid = $_GET["titleid"];
    $parseurl = 'http://www.imdb.com/title/'.$titleid.'/';
  }
  if (isset($_POST["parselink"])) {
    $parseurl = $_POST["imdblink"];
  }
  $imdbcontent = get_data($parseurl);
  //parse movie details
  $parsename = trim(strip_tags(get_match('/<title>(.*) \(\d{4}\).*?<\/.*?>/isU',$imdbcontent)));
  $parseyear = get_match('/<title>.*?\((\d{4})\).*?<\/title>/is',$imdbcontent);
  $parsedirector = trim(strip_tags(get_match('/<h4.*Director:.*<\/h4>.*<a.*>(.*)<\/a>/isU',$imdbcontent))); 
  $parseruntime = get_match('/Runtime:<\/h4>.*?\n.*?(\d*) min/is',$imdbcontent);
  //get IMDB extended credits
  $parseurlext = $parseurl.'fullcredits';
  $imdbcontent = get_data($parseurlext);
  $parsecinemat = trim(strip_tags(get_match('/Cinematography by<\/a><\/h5>(.*)<\/a>/isU',$imdbcontent)));
}

if (isset($_GET["sort"]) && isset($_GET["dir"])) {
  if ($_GET["dir"] == "ascending") {
    $sortdir = "ASC";
    $newsortdir = "descending";
  } else {
    $sortdir = "DESC";
    $newsortdir = "ascending";
  }
  $sortcol = $_GET["sort"];
} else {
  $newsortdir = "ascending";
}

?>
<form action="film_list.php" method="post">
<div id="formtable">
<table>
  <tr>
    <td width="80px">Datum</td>
    <td width="150px">Titel</td>
    <td width="50px">Jahr</td>
    <td width="140px">Regisseur</td>
    <td width="140px">Cinematograph</td>
    <td width="50px">Dauer</td>
    <td width="300px">IMDB <? if (isset($parseurl)) echo '<a href="'.$parseurl.'" target="_blank">Link</a>'; else echo 'Link'; ?></td>
  </tr>
  <tr>
    <td><input name="date" type="text" size="8" maxlength="40" value="<? if(isset($date)) echo $date; else echo date("Y")."-".date("m")."-".date(d); ?>"></td>
    <td><input name="title" type="text" size="20" maxlength="40" value="<? if(isset($_POST["search"])) echo $_POST["title"]; if(parsing) echo $parsename;?>"></td>
    <td><input name="year" type="text" size="4" maxlength="10" value="<? if(parsing) echo $parseyear; ?>"></td>
    <td><input name="director" type="text" size="20" maxlength="40" value="<? if(parsing) echo $parsedirector; ?>"></td>
    <td><input name="cinematograph" type="text" size="20" maxlength="40" value="<? if(parsing) echo $parsecinemat; ?>"></td>
    <td><input name="length" type="text" size="3" maxlength="5" value="<? if(parsing) echo $parseruntime; ?>"></td>
    <td><input name="imdblink" type="text" size="40" maxlength="100" value="<? if(parsing) echo $parseurl; ?>"></td>
  </tr>
</table>
<table>
  <tr>
    <td width="40px">Wert</td>
    <td width="400px">Notizen</td>
  <tr>
    <td><input name="rating" type="text" size="3" maxlength="2"></td>
    <td><textarea name="notes" type="text" cols="50" rows="3"></textarea></td>
  </tr>
</table><br>
</div>
<table>
  <tr><td width="300px">
    <input type="submit" name="search" value="IMDB Suche">
    <input type="submit" name="parselink" value="IMDB Link">
  </td><td width="300px">
    <input type="submit" name="submit" value="Absenden">
    <input type="reset" value="L&ouml;schen">
  </td></tr></table>
</form><br>

<div id="searchtable">
<?php

if (isset($_POST["submit"])) {
  $date = $_POST["date"];
  $year=(int)substr($date,0,4);
  $month=(int)substr($date,5,2);
  $day=(int)substr($date,8,2);
  $title = $_POST["title"];
  $filmyear = $_POST["year"];
  $imdblink = $_POST["imdblink"];
  preg_match('/(tt\d{7})/isU',$imdblink,$matches);
  $imdbid = $matches[1];
  $director = $_POST["director"];
  $cinema = $_POST["cinematograph"];
  $rating = $_POST["rating"];
  $length = $_POST["length"];
  $notes = $_POST["notes"];
  if (!checkdate($month,$day,$year)) {
    echo "<p>Datum nicht korrekt!</p>";
  } else {
    if (empty($_POST["date"])) {
      echo "<p>Datum fehlt!</p>";
    } else {
      if (empty($_POST["title"])) {
        echo "<p>Titel fehlt!</p>";
      } else {
        $sql = "INSERT INTO film_list (number,date,imdbid,title,year,director,cinematographer,rating,length,notes) VALUES (NULL,'".$date."','".$imdbid."','".$title."','".$filmyear."','".$director."','".$cinema."','".$rating."','".$length."','".$notes."')";
        $result = mysql_query($sql);
        if (!$result) {
          die('<p>Einf&uuml;gen der Daten fehlgeschlagen! '.mysql_error()).'</p>';
        } else {
          echo "<p>Neuer Datensatz hinzugef&uuml;gt.</p>";
        }
      }
    }
  }
  
}

if (isset($_POST["search"])) {
  //get IMDB search results
  $search_title = $_POST["title"];
  $url = 'http://www.imdb.com/find?s=all&q=';
  $url = $url.urlencode($search_title);
  $imdb_search = get_data($url);

  //display popular titles
  $num_results = get_match('/Popular Titles<\/b> \(Displaying (\d*) Result\D?\)<table>/is',$imdb_search);
  if ($num_results==0) {
    echo '<p>Keine popul&auml;ren Titel gefunden!</p>';
  } else {
    if ($num_results==1) {
      echo '<p>'.$num_results.' popul&auml;ren Titel gefunden<br></p>';
    } else {
      echo '<p>'.$num_results.' popul&auml;re Titel gefunden<br></p>';
    }
    echo "<p>\n<table>\n";
    for ($i=1;$i<=$num_results;$i++) {
      echo '<tr><td width="25px" align="center">'.$i.".</td>\n";
      $ret = preg_match_all('/['.$i.']\.<\/td>.*?<a href="\/title\/(tt\d{7}).*?">(.*?)<\/a> \((\d{4})\)/is',$imdb_search,$matches,PREG_SET_ORDER);
      $titleid = $matches[0][1];
      $title = $matches[0][2];
      $year = $matches[0][3];
      echo '<td width="350px"><a href="http://www.imdb.com/title/'.$titleid.'" target="_blank">'.$title.' ('.$year.")</a></td>\n";
      echo '<td width="70px" align="center"><a href="film_list.php?parse=1&date='.$date.'&titleid='.$titleid."\">w&auml;hlen</a></td></tr>\n";
    }
    echo "</table>\n</p>\n";
  }

  //display exact matches
  $num_results = get_match('/Titles \(Exact Matches\)<\/b> \(Displaying (\d*) Result\D?\)<table>/is',$imdb_search);
  if ($num_results==0) {
    echo '<p>Keine exakte &Uuml;bereinstimmung gefunden!</p>';
  } else {
    if ($num_results==1) {
      echo '<p>'.$num_results.' exakten Titel gefunden<br></p>';
    } else {
      echo '<p>'.$num_results.' exakte Titel gefunden<br></p>';
    }
    echo "<p>\n<table>\n";
    for ($i=1;$i<=$num_results;$i++) {
      echo '<tr><td width="25px" align="center">'.$i.".</td>\n";
      $ret = preg_match_all('/['.$i.']\.<\/td>.*?<a href="\/title\/(tt\d{7}).*?">(.*?)<\/a> \((\d{4})\)/is',$imdb_search,$matches,PREG_SET_ORDER);
      $titleid = $matches[0][1];
      $title = $matches[0][2];
      $year = $matches[0][3];
      echo '<td width="350px"><a href="http://www.imdb.com/title/'.$titleid.'" target="_blank">'.$title.' ('.$year.")</a></td>\n";
      echo '<td width="70px" align="center"><a href="film_list.php?parse=1&date='.$date.'&titleid='.$titleid."\">w&auml;hlen</a></td></tr>\n";
    }
    echo "</table>\n</p>\n";
  }

  //display partial matches
  $num_results = get_match('/Titles \(Partial Matches\)<\/b> \(Displaying (\d*) Result\D?\)<table>/is',$imdb_search);
  if ($num_results==0) {
    echo '<p>Keine ungef&auml;hre &Uuml;bereinstimmung gefunden!</p>';
  } else {
    if ($num_results==1) {
      echo '<p>'.$num_results.' ungef&auml;hren Titel gefunden<br></p>';
    } else {
      echo '<p>'.$num_results.' ungef&auml;hre Titel gefunden<br></p>';
    }
    echo "<p>\n<table>\n";
    for ($i=1;$i<=$num_results;$i++) {
      echo '<tr><td width="25px" align="center">'.$i.".</td>\n";
      $ret = preg_match_all('/['.$i.']\.<\/td>.*?<a href="\/title\/(tt\d{7}).*?">(.*?)<\/a> \((\d{4})\)/is',$imdb_search,$matches,PREG_SET_ORDER);
      $titleid = $matches[0][1];
      $title = $matches[0][2];
      $year = $matches[0][3];
      echo '<td width="350px"><a href="http://www.imdb.com/title/'.$titleid.'" target="_blank">'.$title.' ('.$year.")</a></td>\n";
      echo '<td width="70px" align="center"><a href="film_list.php?parse=1&date='.$date.'&titleid='.$titleid."\">w&auml;hlen</a></td></tr>\n";
    }
    echo "</table>\n</p>\n";
  }

  echo '<p><a href="'.$url.'" target="_blank">IMDB-Suchseite &ouml;ffnen</a></p>';
}

//gets the match content
function get_match($regex,$content)
{
	preg_match($regex,$content,$matches);
	return $matches[1];
}

//gets the data from an URL
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
</p>
</div>

<br><hr><br>

<div id="stattable">
<table>
  <tr>
    <td width="440px"><b>Filmdauer</b></td>
    <td width="440px"><b>Produktionsjahr</b></td>
  </tr>
  <tr>
    <td><img src="film_diagram.php?type=duration"></td>
    <td><img src="film_diagram.php?type=year"></td>
  </tr>
  <tr>
    <td width="440px"><b>Bewertung</b></td>
    <td width="440px"><b>Zeitverlauf</b></td>
  </tr>
  <tr>
    <td><img src="film_diagram.php?type=rating"></td>
    <td valign="top"><img src="film_diagram.php?type=monthplot"></td>
  </tr>
  <tr>
    <td width="440px"><b>Bewertung vs. Produktionsjahr</b></td>
    <td></td>
  </tr>
  <tr>
    <td><img src="film_diagram.php?type=correlation"></td>
    <td></td>
  </td>
</table>
</div>

<br><hr><br>

<div id="counttable">
<table align="left">
  <tr>
    <td width="180px" align="center"><b>Regisseur</b></td>
    <td width="80px" align="center"><b>Anzahl</b></td>
    <td width="100px" align="center"><b>Bewertung</b></td>
  </tr>
<?php
$sql = "SELECT director, COUNT(*) totcount, ROUND(AVG(rating),1) avgrating FROM (SELECT director, rating FROM `film_list` GROUP BY title) titlegrp GROUP BY director HAVING COUNT(*) > 2 ORDER BY COUNT(*) DESC, avgrating DESC";
$result = mysql_query($sql) or die('Anfrage fehlgeschlagen: '.mysql_error());
while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
  echo "  <tr>\n";
  foreach ($line as $colkey => $colvalue) {
    switch ($colkey) {
      case 'director':
      case 'totcount':
      case 'avgrating':
        echo '    <td align="center">'.nl2br($colvalue)."</td>\n";
        break;
      default:
        break;
    }
  }
}
echo "  </tr>\n";
?>
<table>
  <tr>
    <td width="220px" align="center"><b>Titel</b></td>
    <td width="80px" align="center"><b>Anzahl</b></td>
    <td width="100px" align="center"><b>Bewertung</b></td>
  </tr>
<?php
$sql = "SELECT title, COUNT(*) totcount, ROUND(AVG(rating),1) avgrating FROM `film_list` GROUP BY title HAVING COUNT(*) > 1 ORDER BY COUNT(*) DESC, avgrating DESC";
$result = mysql_query($sql) or die('Anfrage fehlgeschlagen: '.mysql_error());
while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
  echo "  <tr>\n";
  foreach ($line as $colkey => $colvalue) {
    switch ($colkey) {
      case 'title':
      case 'totcount':
      case 'avgrating':
        echo '    <td align="center">'.nl2br($colvalue)."</td>\n";
        break;
      default:
        break;
    }
  }
}
echo "  </tr>\n";
?>
</table>
</div>
<br><hr><br>

<div id="listtable">
<table width="100%">
  <tr>
    <td width="30px" align="center"><a href="film_list.php?sort=number&dir=<? echo $newsortdir; ?>">Nr.</a></td>
    <td width="90px"><a href="film_list.php?sort=date&dir=<? echo $newsortdir; ?>">Datum</a></td>
    <td width="150px"><a href="film_list.php?sort=title&dir=<? echo $newsortdir; ?>">Titel</a></td>
    <td width="40px" align="center"><a href="film_list.php?sort=year&dir=<? echo $newsortdir; ?>">Jahr</a></td>
    <td width="140px"><a href="film_list.php?sort=director&dir=<? echo $newsortdir; ?>">Regisseur</a></td>
    <td width="140px"><a href="film_list.php?sort=cinematographer&dir=<? echo $newsortdir; ?>">Cinematograph</a></td>
    <td width="50px" align="center"><a href="film_list.php?sort=length&dir=<? echo $newsortdir; ?>">Dauer</a></td>
    <td width="40px" align="center"><a href="film_list.php?sort=rating&dir=<? echo $newsortdir; ?>">Wert</a></td>
    <td><b>Notizen</b></td>
  </tr>
<?php
if (isset($_GET["sort"]) && isset($_GET["dir"])) {
  $sql = "SELECT number,date,imdbid,title,year,director,cinematographer,length,rating,notes FROM film_list ORDER BY film_list.".$sortcol." ".$sortdir;
} else {
  $sql = "SELECT number,date,imdbid,title,year,director,cinematographer,length,rating,notes FROM film_list ORDER BY film_list.number DESC";
}
$result = mysql_query($sql) or die('Anfrage fehlgeschlagen: '.mysql_error());

while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
  echo "  <tr>\n";
  foreach ($line as $colkey => $colvalue) {
    switch ($colkey) {
      case 'title':
        if ($imdbidlist<>'') {
          echo '    <td><a href="http://www.imdb.com/title/'.$imdbidlist.'" target="_blank">'.$colvalue."</a></td>\n";
        } else {
          echo '    <td>'.$colvalue."</td>\n";
        }
        break;
      case 'imdbid':
        $imdbidlist = $colvalue;
        break;
      case 'number':
      case 'year':
      case 'rating':
      case 'length':
        echo '    <td align="center">'.nl2br($colvalue)."</td>\n";
        break;
      default:
        echo '    <td>'.nl2br($colvalue)."</td>\n";
        break;
    }
  }
  echo "  </tr>\n";
}
echo "</table>\n";
echo "</div>\n";

mysql_free_result($result);
mysql_close($link);
?>

</body>
</html>
