<?php
//header('Content-Type: text/plain; charset=utf-8');
require_once 'Excel/reader.php';
?>
<style>
.textfiled {height: 15.1px;     margin-bottom: 6px; ; }
</style>
<?php
try {
    
    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (
        !isset($_FILES['file']['error']) ||
        is_array($_FILES['file']['error'])
    ) {
        throw new RuntimeException('Invalid parameters.');
    }

    // Check $_FILES['file']['error'] value.
    switch ($_FILES['file']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    // You should also check filesize here. 
    if ($_FILES['file']['size'] > 1000000) {
        throw new RuntimeException('Exceeded filesize limit.');
    }

    // DO NOT TRUST $_FILES['file']['mime'] VALUE !!
    // Check MIME Type by yourself.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($_FILES['file']['tmp_name']),
        /*array(
            'xls' => 'application/excel',
            'xlxs' => 'application/excel',
        ),*/
		array('application/excel', 'application/vnd.ms-excel', 'application/octet-stream'),
	//	array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        true
    )) {
        throw new RuntimeException('Invalid file format.');
    }

    // You should name it uniquely.
    // DO NOT USE $_FILES['file']['name'] WITHOUT ANY VALIDATION !!
    // On this example, obtain safe unique name from its binary data.
    if (!move_uploaded_file(
        $_FILES['file']['tmp_name'],
        sprintf('./uploads/%s.%s',
            sha1_file($_FILES['file']['tmp_name']),
            $ext
        )
    )) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

$data = new Spreadsheet_Excel_Reader();
$data->setOutputEncoding('CP1251');
$data->read($_FILES['file']['name']);

//$columnCount = $data->sheets[0]['numCols'];
$conn = mysql_connect("localhost","root","");
mysql_select_db("test",$conn);

$sql = "DROP TABLE IF EXISTS temp_col";
$sql1 = "CREATE TABLE IF NOT EXISTS `temp_col` 
(
  `PK` int(11) NOT NULL AUTO_INCREMENT,
  `colTableOrder` varchar(100) NOT NULL,
  `colSheetOrder` varchar(100) NOT NULL,
  `finalColumnList` int(11) NOT NULL,
  PRIMARY KEY (`PK`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";

$result = mysql_query($sql);
$result1 = mysql_query($sql1);

			if (!$result || !$result1) 
					{
					echo ('Database Error:' . mysql_error());
					}

$col = "";
$col1 = "";
$values = "";
for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
	    for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
		    if (  $i == 1 ) {
				if ( $j == $data->sheets[0]['numCols'] ) {
						$col .= $data->sheets[0]['cells'][$i][$j]."<br />\n";
						$col1 .=$j ." - ". $data->sheets[0]['cells'][$i][$j]."<br />\n";
						$getColumnFromSheet = $data->sheets[0]['cells'][$i][$j];
						$query = "INSERT INTO `temp_col` (`colSheetOrder`) VALUES ('$getColumnFromSheet')";
						$result_query = mysql_query($query);
						if (!$result_query) {
							die('Query failed: ' . mysql_error());
						}

						continue;
					}
				//$col .= "\"".$data->sheets[0]['cells'][$i][$j]."\",";
				//$col .= $data->sheets[0]['cells'][$i][$j].",";
				$col .= $data->sheets[0]['cells'][$i][$j]."<br />\n";
				$col1 .=$j ." - ". $data->sheets[0]['cells'][$i][$j]."<br />\n";
				$getColumnFromSheet = $data->sheets[0]['cells'][$i][$j];
				$query = "INSERT INTO `temp_col` (`colSheetOrder`) VALUES ('$getColumnFromSheet')";
				$result_query = mysql_query($query);
				if (!$result_query) {
					die('Query failed: ' . mysql_error());
				}

					
				continue;
			}
			//echo "columnNames :\n$columnNames\n";
			//$values .= "Value = \"".$data->sheets[0]['cells'][$i][$j]."\",";
			if ( $j == $data->sheets[0]['numCols'] ) {
				$values .= "'".$data->sheets[0]['cells'][$i][$j]."'";
			}
			else {
				$values .= "'".$data->sheets[0]['cells'][$i][$j]."',";
			}
	        //echo "\"".$data->sheets[0]['cells'][$i][$j]."\",";
	    }
			$columnNames = $col1;
			//$values = "\"".$data->sheets[0]['cells'][$i][$j]."\",";
			
		$result = mysql_query('select * from upload');
		if (!$result) {
		die('Query failed: ' . mysql_error());
		}
		
$i = 0;
$columnData = "";
$getFinalList = "";
while ($i < mysql_num_fields($result)) {
    //echo "Information for column $i:\n";
    $meta = mysql_fetch_field($result, $i);
    if (!$meta) {
        echo "No information available.<br />\n";
    }
$columnData .= $i+1 ." - " . $meta->name."<br />\n";
$getFinalList .= $meta->name."<br />\n";
$query = "UPDATE temp_col set colTableOrder = '$meta->name' Where PK = $i+1";
$result_query = mysql_query($query);
	if (!$result_query) {
		die('Query failed: ' . mysql_error());
	}
//echo "$meta->name\n";
    $i++;
}
mysql_free_result($result);

/*			echo "columnNames From Sheet :-<br />\n$columnNames<br />\n";
			echo "columnNames From Table :-<br />\n$columnData<br />\n";
*/			
			$list = "";
			for ($k = 0; $k < $i; $k++) {
			//$columnOrder = ;
			$list .= "<input class='textfiled' type=\"text\" name=\"finalColumnList[]\" maxlength=\"4\" size=\"4\"\" /><br >\n ";
			}

			//echo "$values";

echo '<form name="input" action="uploadeFileOrder.php" method="POST">';
echo "<table style=\"width:600px\" width=\"80%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
echo "<h1> Columns Mapping! </h1>";
echo '<table border="1">';
echo "<tr>";
echo '  <th align="center">columnNames From Sheet</th>';
echo '  <th align="center">columnNames From Table</th> ';
echo '  <th align="center">Choose column</th>';
echo "</tr>";
echo "<tr valign=\"top\">";
echo "  <td >$columnNames<br />\n</td>";
echo "  <td >$columnData<br />\n</td> ";
echo "  <td maxlength=\"4\" size=\"4\">$list</td><br />\n";
echo "</tr>";
echo "</table>";
			echo "<br />\n";
			//echo "<input type=\"hidden\" name=\"colTableOrder[]\" value=\"$columnData\">";
			echo "<input type=\"hidden\" name=\"colTableOrder[]\" value=\"$getFinalList\">";
			echo "<input type=\"hidden\" name=\"colOrder[]\" value=\"$col\">";
			echo "<input type='submit' name='submit' value='Submit'/><br >\n";
			echo "<br />\n";
}

//if ( $_GET['Submit'] == 'Submit' ) {

//$getOrder = $_GET['finalColumnList'];
//echo "getOrder : $getOrder";

//}

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

} catch (RuntimeException $e) {

    echo $e->getMessage();

}

//if ( $_GET['Submit'] == 'Submit' ) {

//$getOrder = $_GET['finalColumnList'];
//echo "getOrder : $getOrder";

//}

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
