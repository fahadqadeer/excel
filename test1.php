<?php

require_once 'Excel/reader.php';
$data = new Spreadsheet_Excel_Reader();
$data->setOutputEncoding('CP1251');
$data->read('test.xls');

$conn = mysql_connect("localhost","root","");
mysql_select_db("test",$conn);
echo "Pakistan!out loop\n";
for ( $x = 1; $x <= count($data->sheets[0]["cells"]); $x++ ) {
	echo "in loop\n";
    $name = $data->sheets[0]["cells"][$x][1];
    $lname = $data->sheets[0]["cells"][$x][2];
    $dept = $data->sheets[0]["cells"][$x][3];
	echo "Pakistan! in loop\n";
	echo "name : $name, lname : $lname, dept : $dept\n";
    $sql = "INSERT INTO test (name,lname,dept) 
        VALUES ('$name','$lname','$dept')";
    echo $sql."\n";
	$result = mysql_query($sql);
    if (!$result) 
            { 
            echo ('Database Error:' . mysql_error());
            }
}
?>