<?
$type = $_GET["type"];
$db_server = 'localhost';
$db_user = '';
$db_passwort = '';
$link = mysql_connect($db_server, $db_user, $db_passwort) or die ('Keine Verbindung'.mysql_error());
mysql_select_db("") or die('Auswahl der Datenbank fehlgeschlagen.');
if ($type == "monthplot") {
  $rand = 10;
  $balkenbreite = 7;
  $jahr_abstand = 3;
  $num_jahre = 6;
  $breite = 2*$rand + $num_jahre*12*$balkenbreite + ($num_jahre-1)*$jahr_abstand;
  $hoehe = 200;
  $schrift = 3;
  $schriftypos = $hoehe - 16;
  $schriftxpos = $rand + 2;
  $zeile = 16 - $rand + 2;
  $val_abstand_y = 14;
  $val_abstand_x = 2;
  $scale_y = 11;
  $bild = imagecreatetruecolor($breite, $hoehe);
  $farbe_hintergrund = imagecolorexact($bild, 245, 245, 245);
  $farbe_text = imagecolorexact($bild, 0, 0, 0);
  $farbe_zwischen = imagecolorexact($bild, 220, 220, 220);
  $farbe_rot = imagecolorexact($bild, 255, 0, 0);
  $farbe_gruen = imagecolorexact($bild, 0, 255, 0);
  $farbe_schwarz = imagecolorexact($bild, 0, 0, 0);
  $farbe_gelb = imagecolorexact($bild, 255, 255, 0);
  $farbe_lila = imagecolorexact($bild, 255, 0, 255);
  imagefill($bild, 0, 0, $farbe_hintergrund);
  $colcount = 0;
  $groupcount = 0;
  for($i=2009;$i<=date("Y");$i++) {
    $groupcount++;
    $maxcountmonth = 12;
    if($i==date("Y")) $maxcountmonth = date("n");
    imagestring($bild, $schrift, $schriftxpos+($groupcount-1)*(12*$balkenbreite+$jahr_abstand), $schriftypos, $i, $farbe_text);
    imagefilledrectangle($bild, ($groupcount-1)*(12*$balkenbreite+$jahr_abstand)+$rand, $hoehe-$rand-$zeile, $groupcount*(12*$balkenbreite+$jahr_abstand)-$jahr_abstand+$rand, $rand, $farbe_zwischen);
    $maxval = 0;
    $maxmonth = 0;
    for($j=1;$j<=$maxcountmonth;$j++) {
      $sql = "SELECT COUNT(*) as anzahl FROM `film_list` WHERE MONTH(date) = ".$j." AND YEAR(date) = ".$i;
      $result = mysql_query($sql) or die(mysql_error());
      $anzahl_result = mysql_fetch_array($result);
      $anzahl_sum = $anzahl_result["anzahl"];
      if($anzahl_sum>$maxval) {
        $maxval = $anzahl_sum;
        $maxmonth = $j;
      }
      $colcount++;
      imagefilledrectangle($bild, ($colcount-1)*$balkenbreite+($groupcount-1)*$jahr_abstand+$rand, $hoehe-$rand-$zeile, $colcount*$balkenbreite+($groupcount-1)*$jahr_abstand+$rand, $hoehe-$anzahl_sum*$scale_y-$rand-$zeile, $farbe_pixel);
    }
    imagestring($bild, $schrift, ($maxmonth-1)*$balkenbreite+($groupcount-1)*(12*$balkenbreite+$jahr_abstand)+$val_abstand_x+$rand, $hoehe-$rand-$zeile-$maxval*$scale_y-$val_abstand_y, $maxval, $farbe_text);
  }
  
} elseif ($type == "correlation") {
  $pixelsize = 18;
  $rx = 10;
  $ry = 10;
  $breite = 2*$rx+$pixelsize*11;
  $hoehe = 2*$ry+$pixelsize*11;
  $bild = imagecreatetruecolor($breite, $hoehe);
  $farbe_hintergrund = imagecolorexact($bild, 245, 245, 245);
  $farbe_text = imagecolorexact($bild, 0, 0, 0);
  $farbe_zwischen = imagecolorexact($bild, 220, 220, 220);
  $farbe_rot = imagecolorexact($bild, 255, 0, 0);
  $farbe_gruen = imagecolorexact($bild, 0, 255, 0);
  $farbe_schwarz = imagecolorexact($bild, 0, 0, 0);
  $farbe_gelb = imagecolorexact($bild, 255, 255, 0);
  $farbe_lila = imagecolorexact($bild, 255, 0, 255);
  imagefill($bild, 0, 0, $farbe_hintergrund);
  $colcount = 0;
  for($i=1910;$i<2020;$i+=10) {
    $colcount++;
    $minyear = $i;
    $maxyear = $minyear + 10;
    $anzahl_rating = array();
    for($j=0;$j<11;$j++) {
      $rating = $j;
      $sql = "SELECT COUNT(*) as anzahl FROM `film_list` WHERE `rating` = ".$rating." AND `year` BETWEEN ".$minyear." AND ".$maxyear;
      $result = mysql_query($sql) or die(mysql_error());
      $anzahl_result = mysql_fetch_array($result);
      $anzahl_sum = $anzahl_result["anzahl"];
      $anzahl_rating[$rating] = $anzahl_sum;
    }
    $anzahl_max = max($anzahl_rating);
    for($j=0;$j<11;$j++) {
      $greyval = 255-(($anzahl_rating[$j]*255)/$anzahl_max);
      $farbe_pixel = imagecolorexact($bild, $greyval, $greyval, $greyval);
      imagefilledrectangle($bild, ($colcount-1)*$pixelsize+$rx, (10-$j)*$pixelsize+$ry, $colcount*$pixelsize+$rx, (10-$j+1)*$pixelsize+$ry, $farbe_pixel);
    }
  }
} else {
  if ($type == "duration") {
    $daten = "";
    for($i=10;$i<230;$i+=20) {
      $minlength = $i;
      $maxlength = $minlength + 20;
      $sql = "SELECT COUNT(*) as anzahl FROM `film_list` WHERE `length` BETWEEN ".$minlength." AND ".$maxlength;
      $result = mysql_query($sql) or die(mysql_error());
      $anzahl_result = mysql_fetch_array($result);
      $anzahl_sum = $anzahl_result["anzahl"];
      $daten = $daten.$minlength."-".$maxlength.":".$anzahl_sum.":schwarz, ";
      $breite = 400;
      $hoehe = 227;
      $einheit = "";
      $abstand = 8;
      $schrift = 3;
      $legende_abstand = 10;
      $scale_y = 2;
    }
    $daten = substr($daten,0,strlen($daten)-2);
    //echo $daten;
  } elseif ($type == "year") {
    $daten = "";
    for($i=1910;$i<2020;$i+=10) {
      $minyear = $i;
      $maxyear = $minyear + 10;
      $sql = "SELECT COUNT(*) as anzahl FROM `film_list` WHERE `year` BETWEEN ".$minyear." AND ".$maxyear;
      $result = mysql_query($sql) or die(mysql_error());
      $anzahl_result = mysql_fetch_array($result);
      $anzahl_sum = $anzahl_result["anzahl"];
      $daten = $daten.$minyear."-".$maxyear.":".$anzahl_sum.":schwarz, ";
      $breite = 400;
      $hoehe = 227;
      $einheit = "";
      $abstand = 8;
      $schrift = 3;
      $legende_abstand = 10;
      $scale_y = 1.5;
    }
    $daten = substr($daten,0,strlen($daten)-2);
    //echo $daten;
  } elseif ($type == "rating") {
    $daten = "";
    for($i=0;$i<11;$i++) {
      $rating = $i;
      $sql = "SELECT COUNT(*) as anzahl FROM `film_list` WHERE `rating` = ".$rating;
      $result = mysql_query($sql) or die(mysql_error());
      $anzahl_result = mysql_fetch_array($result);
      $anzahl_sum = $anzahl_result["anzahl"];
      $daten = $daten.$rating.":".$anzahl_sum.":schwarz, ";
      $breite = 350;
      $hoehe = 227;
      $einheit = "";
      $abstand = 8;
      $schrift = 3;
      $legende_abstand = 10;
      $scale_y = 3;
    }
    $daten = substr($daten,0,strlen($daten)-2);
  }
  
  $daten = explode(", ", $daten);
  $werte = array();
  $bezeichnungen = array();
  $farben = array();
  for($i=0; $i<sizeof($daten); $i++) {
    $temp = explode(":", $daten[$i]);
    array_push($bezeichnungen, $temp[0]);
    array_push($werte, $temp[1]);
    array_push($farben, $temp[2]);
    if($abstand_text < imagefontwidth($schrift)*strlen($temp[0])) $abstand_text = imagefontwidth($schrift)*strlen($temp[0]);
  }
  
  $abstand_text_h = imagefontheight($schrift);
  $bild = imagecreatetruecolor($breite, $hoehe);
  $farbe_hintergrund = imagecolorexact($bild, 245, 245, 245);
  $farbe_text = imagecolorexact($bild, 0, 0, 0);
  $farbe_zwischen = imagecolorexact($bild, 220, 220, 220);
  $farbe_rot = imagecolorexact($bild, 255, 0, 0);
  $farbe_gruen = imagecolorexact($bild, 0, 255, 0);
  $farbe_schwarz = imagecolorexact($bild, 0, 0, 0);
  $farbe_gelb = imagecolorexact($bild, 255, 255, 0);
  $farbe_lila = imagecolorexact($bild, 255, 0, 255);
  imagefill($bild, 0, 0, $farbe_hintergrund);
  
  $balken_x = $abstand;
  $balken_y = $hoehe - $abstand;
  $balken_b = 2 * $abstand;
  $diagramm_h = $hoehe - 2 * $abstand;
  $balken_versatz = 0;
  
  $legende_x = $balken_x + sizeof($werte) *
  $balken_b + (sizeof($werte) - 1) * $abstand + 2 * $abstand;
  $legende_y = $hoehe - $abstand -
  $legende_abstand;
  $legende_b = $legende_x + $legende_abstand;
  $legende_h = $legende_y + $legende_abstand;
  $legende_versatz = 0;
  
  for($i=0; $i<sizeof($werte); $i++) {
    $prozent = 100 / array_sum($werte) * $werte[$i];
    $balken_h = $diagramm_h / 100 * $prozent * $scale_y;
    $wert = $werte[$i]." ".$einheit;
    $farbe = "farbe_".$farben[$i];
  
    imagefilledrectangle($bild, $balken_x + $balken_versatz, $abstand, $balken_x + $balken_versatz + $balken_b, $hoehe - $abstand, $farbe_zwischen);
    imagefilledrectangle($bild, $balken_x + $balken_versatz, $balken_y - $balken_h, $balken_x + $balken_versatz + $balken_b, $balken_y, ${$farbe});
    imagestring($bild, $schrift, $balken_x + $balken_versatz + 2, $balken_y - $balken_h - $abstand_text_h, $werte[$i], $farbe_text);
  
    imagefilledrectangle($bild, $legende_x, $legende_y - $legende_versatz, $legende_b, $legende_h - $legende_versatz, ${$farbe});
    imagestring($bild, $schrift, $legende_x + 2 * $legende_abstand, $legende_y - $legende_versatz, $bezeichnungen[$i], $farbe_text);
    imagestring($bild, $schrift, $legende_x + 3 * $legende_abstand + $abstand_text, $legende_y - $legende_versatz, $wert, $farbe_text);
  
    $balken_versatz = $balken_versatz + 3 * $abstand;
    $legende_versatz = $legende_versatz + 2 * $legende_abstand;
  }
}
header("Content-type: image/png");
imagepng($bild);
?>
