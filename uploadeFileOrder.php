<?php
//print_r($_POST); exit;

//if ( isset($_POST['Submit'] == "Submit" )) {

$conn = mysql_connect("localhost","root","");
mysql_select_db("test",$conn);

$colOrder = $_POST['colOrder'];
$getOrder = $_POST['finalColumnList'];
$colTableOrder = $_POST['colTableOrder'];
$sheetValues = $_POST['sheetValues'];

//foreach($colOrder as $KEY) {
 // print "$KEY <br />\n";
//}

$i = 1;
foreach($getOrder as $value) {

	if ( $value == "" ) {
		continue;
	}
//  print "$value<br />\n";
  //$getOrder .= $value;
  	$query = "UPDATE temp_col set finalColumnList = $value Where PK = $i";
	$result_query = mysql_query($query);
	if (!$result_query) {
		die('Query failed: ' . mysql_error());
	}
  $i++;
}

//foreach($colTableOrder as $KEY1) {
  //print "$KEY1<br />\n";
//}

$sql1 = "DROP TABLE IF EXISTS `temp_col1` ";
$sql2 = "CREATE TABLE `temp_col1` AS SELECT * FROM `temp_col` ";
$sql = "update temp_col tmp, temp_col1 tmp1 set tmp.colTableOrder = tmp1.colSheetOrder where tmp.PK = tmp1.PK and tmp.PK != tmp.finalColumnList ";
$sql3 = "DROP TABLE IF EXISTS `temp_col` ";
$sql4 = "DROP TABLE IF EXISTS `temp_col1` ";

$result1 = mysql_query($sql1);
$result2 = mysql_query($sql2);
$result_1 = mysql_query($sql);
//$result3 = mysql_query($sql3);
//$result4 = mysql_query($sql4);

	if (!$result1 || !$result2 || !$result_1) { 
			echo ('Database Error:' . mysql_error());
	}

	$getColumnListToInsert = mysql_query('SELECT colTableOrder FROM Temp_Col');
	if (!$getColumnListToInsert) {
		die('Query failed: ' . mysql_error());
	}
	$getLastColumnValue = mysql_query('SELECT * FROM Temp_Col ORDER BY PK DESC LIMIT 1');
	if (!$getLastColumnValue) {
		die('Query failed: ' . mysql_error());
	}
//	echo $getColumnListToInsert
$row1 = mysql_fetch_assoc($getLastColumnValue);
$max = $row1['colTableOrder'];
$insertOrder = "";
while (($row = mysql_fetch_assoc($getColumnListToInsert))) {
  echo $row['colTableOrder']."<br />\n";
  if ( $row['colTableOrder'] === $max ) {
	//echo "in if";
	$insertOrder .= $row['colTableOrder'];
  }
  else {
	$insertOrder .= $row['colTableOrder'].",";
	//echo "in else";
  }
}

foreach($sheetValues as $values) {
//  print "$values <br />\n";
  	$finalsql = "INSERT INTO upload ($insertOrder) 
	VALUES ($values)";
	echo $finalsql;
	
	$result = mysql_query($finalsql);
		if (!$result) { 
			echo ('Database Error:' . mysql_error());
		}
}

/*			$sql = "INSERT INTO upload ($columnNames) 
			VALUES ($values)";
		    echo $sql."\n";
			$result = mysql_query($sql);
			if (!$result) 
					{ 
					echo ('Database Error:' . mysql_error());
					}
*/

//    echo 'File is uploaded successfully.';

?>