<?php

// Don't allow remote access
if (!$_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR']) {
    $this->output->set_status_header(400, 'No remote access allowed.');
}
// Only respond to a post method
if ($_SERVER['REQUEST_METHOD'] == 'POST' && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['queryMode']) && $_POST['queryMode']=='SQL') {
        // Connect, and start the database
        if (!isset($dbcon))
            $dbcon = pg_connect("host=localhost dbname=ra user=apache password=siddarth9123")
                or die('Could not connect '.pg_last_error());
        // Performing sql query
        $query = $_POST['code'];
        $result = pg_query($query) or die('Query failed '.pg_last_error().'<br/><br/><strong>You are in \'PostgreSQL\' mode. Please change the mode if you are executing a Relational Algebra query.</strong>');

        // Printing the result in html
        echo "<table class='table table-striped table-bordered'>\n";
        $i = 0;
        echo "\t<thead>\n\t<tr>\n";
        while ($i < pg_num_fields($result)) {
	    $fieldName = pg_field_name($result, $i);
            echo "\t\t<th style='width:250px;'>$fieldName</th>\n";
	    $i = $i + 1;
        }
        echo "\t<tr>\n\t</thead>\n\t<tbody>\n";
        $i = 0;
        while ($row = pg_fetch_row($result)) {
	    echo "\t<tr>\n";
	    $count = count($row);
	    $y = 0;
	    while ($y < $count)
	    {
		$c_row = current($row);
		echo "\t\t<td>$c_row</td>\n";
		next($row);
		$y = $y + 1;
	    }
	    echo "\t</tr>\n";
	    $i = $i + 1;
        }
        echo "\t</tbody>\n</table>";

        // Free result set
        pg_free_result($result);
    } else if (isset($_POST['queryMode']) && $_POST['queryMode'] == 'Relational Algebra') {
        $query = $_POST['code'];
        $queryId = $_POST['queryID'];
        $fh = fopen("RA/in", "w") or die("Server Error: Can't execute query [Permission Denied]");
        $query = $query."\n";
        fwrite($fh, $query);
        fclose($fh);
        $queryOutput = "RA/sample/".$queryId.'_ra_test.out';
        $correctOutput = "RA/sample/".$queryId.'_ra.out';
        exec('/usr/bin/java -jar RA/ra.jar RA/ra.properties -i RA/in -o '.$queryOutput, $output, $ret);
        for ($i = 5; $i < count($output, 0)-3; $i++) {
            echo $output[$i]."<br>";
        }
        exec('python tester.py '.$queryOutput.' '.$correctOutput, $isCorrect);
        if (count($isCorrect) == 0)
            echo "<br><strong>Congrats! Your output matches the solution query.</strong>";
        else {
            echo "<br>Oops! Your query did not match the solution query. The rows with mismatch are presented below -<br>";
            echo "<pre>".implode($isCorrect,"\n")."</pre>";
        }
    }
} else {
    phpinfo();
}
?>
