<?php
header('Content-Type: text/plain; charset=utf-8');
require_once 'Excel/reader.php';

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
//	if (
//	!isset($_FILES['file']['name'])
//	)
//	{
  //      throw new RuntimeException('File Not selected.');
    //}

$columnCount = $data->sheets[0]['numCols'];
//echo "columnCount : $columnCount";
$conn = mysql_connect("localhost","root","");
mysql_select_db("test",$conn);

$col = "";
//$values = array();
for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
		$values = "";
	    for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
		    if (  $i == 1 ) {
				if ( $j == $data->sheets[0]['numCols'] ) {
						$col .= $data->sheets[0]['cells'][$i][$j];
						continue;
					}
				//$col .= "\"".$data->sheets[0]['cells'][$i][$j]."\",";
				$col .= $data->sheets[0]['cells'][$i][$j].",";
					
				continue;
			}
			//$values .= "Value = \"".$data->sheets[0]['cells'][$i][$j]."\",";
			if ( $j == $data->sheets[0]['numCols'] ) {
				$values .= "'".$data->sheets[0]['cells'][$i][$j]."'";
			}
			else {
				$values .= "'".$data->sheets[0]['cells'][$i][$j]."',";
			}
	        //echo "\"".$data->sheets[0]['cells'][$i][$j]."\",";
	    }
			$columnNames = $col;
			//$values = "\"".$data->sheets[0]['cells'][$i][$j]."\",";

			echo "columnNames = $columnNames\n";
			echo "$values";
			
			echo "\n";

			$sql = "INSERT INTO upload ($columnNames) 
			VALUES ($values)";
		    echo $sql."\n";
			$result = mysql_query($sql);
			if (!$result) 
					{ 
					echo ('Database Error:' . mysql_error());
					}
}

    echo 'File is uploaded successfully.';

} catch (RuntimeException $e) {

    echo $e->getMessage();

}

?>
