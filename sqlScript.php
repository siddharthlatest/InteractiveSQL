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
        $queryId = $_POST['queryID'];
        $result = pg_query($query) or die('Query failed '.pg_last_error().'<br/><br/><strong>You are in \'PostgreSQL\' mode. Please change the mode if you are executing a Relational Algebra query.</strong>');
        // Writing results to an output file
        $queryOutput = "RA/sample/".$queryId.'_sq_test.out';
        $fh = fopen($queryOutput, "w") or die("Server Error: Can't write to a file [Permission Denied]");
        $results = Array();
        while ($results[] = pg_fetch_row($result)) {
            fwrite($fh, implode("|", $results[count($results)-1])."\n");
        }
        fclose($fh);
        unset($results[count($results)-1]);
        // Printing the result in html
        echo "<table class='table table-striped table-bordered'>\n";
        $i = 0;
        echo "\t<thead>\n\t<tr>\n";
        while ($i < pg_num_fields($result)) {
	    $field_name = pg_field_name($result, $i);
            $field_type = pg_field_type($result, $i);
            echo "\t\t<th style='width:250px;'>$field_name"." (".$field_type.")</th>\n";
	    $i = $i + 1;
        }
        echo "\t</tr>\n\t</thead>\n\t<tbody>\n";
        $i = 0;
        while ($i < count($results)) {
	    echo "\t<tr>\n";
	    $y = 0;
	    while ($y < count($results[$i])) {
		echo "\t\t<td>".$results[$i][$y]."</td>\n";
		$y = $y + 1;
	    }
	    echo "\t</tr>\n";
	    $i = $i + 1;
        }
        echo "\t</tbody>\n</table>";
        // Compute and print feedback
        $correctOutput = "RA/sample/".$queryId.'_sq.out';
        exec('python tester.py '.$queryOutput.' '.$correctOutput, $isCorrect);
        if (count($isCorrect) == 0)
            echo "<br><strong>Congrats! Your output matches the solution query.</strong>";
        else {
            echo "<br>Oops! Your query did not match the solution query. Look at the feedback below -<br>";
            echo "<pre><div id='feedbackTop' style='font-weight:bold;'>Incorrect row(-), Missing row(*)\n</div>";
            echo implode($isCorrect,"\n")."</pre>";
        }
        // Free result set
        pg_free_result($result);
    } else if (isset($_POST['queryMode']) && $_POST['queryMode'] == 'Relational Algebra') {
        $query = $_POST['code'];
        $queryId = $_POST['queryID'];
        $fh = fopen("RA/in", "w") or die("Server Error: Can't execute query [Permission Denied]");
        $query = $query."\n";
        fwrite($fh, $query);
        fclose($fh);
        // Execute RA query
        $queryOutput = "RA/sample/".$queryId.'_ra_test.out';
        exec('/usr/bin/java -jar RA/ra.jar RA/ra.properties -i RA/in -o '.$queryOutput, $output, $ret);
        // Pretty print RA query
        echo "<table class='table table-striped table-bordered'>\n";
        echo "\t<thead>\n\t<tr>\n";
        if (strncmp($output[5], "Error", strlen("Error")))
            $output[5] = substr($output[5], 16, strlen($output[5])-17);
        $row = explode(",", $output[5]);
        for ($j = 0; $j < count($row); $j++)
            echo "<th style='width:250px;'>".$row[$j]."</th>";
        echo "\t</tr>\n\t</thead>\n\t<tbody>\n";
        for ($i = 7; $i < count($output, 0)-5; $i++) {
            $row = explode("|", $output[$i]);
	    echo "\t<tr>\n";
            for ($j = 0; $j < count($row); $j++)
                echo "<td>".$row[$j]."</td>";
	    echo "\t</tr>\n";
        }
        echo "\t</tbody>\n</table>";
        // Compute and print feedback
        $correctOutput = "RA/sample/".$queryId.'_ra.out';
        exec('python tester.py --RA '.$queryOutput.' '.$correctOutput, $isCorrect);
        if (count($isCorrect) == 0)
            echo "<br><strong>Congrats! Your output matches the solution query.</strong>";
        else {
            echo "<br>Oops! Your query did not match the solution query. Look at the feedback below -<br>";
            echo "<pre><div id='feedbackTop' style='font-weight:bold;'>Incorrect row(-), Missing row(*)\n</div>";
            echo implode($isCorrect,"\n")."</pre>";
        }
    }
} else {
    phpinfo();
}
?>
