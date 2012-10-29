<?php

// Don't allow remote access
if (!$_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR']) {
    $this->output->set_status_header(400, 'No remote access allowed.');
}
// Only respond to a post method
if ($_SERVER['REQUEST_METHOD'] == 'POST' && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['queryMode']) && $_POST['queryMode']=='PostgreSQL') {
        // Connect, and start the database
        if (!isset($dbcon))
            $dbcon = pg_connect("host=localhost dbname=ra user=wwwdata password=yourock9")
                or die('Could not connect '.pg_last_error());
        // Performing sql query
        $query = $_POST['code'];
        $result = pg_query($query) or die('Query failed '.pg_last_error());

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
        $fh = fopen("RA/in", "w") or die("Server Error: Can't execute query [Permission Denied]");
        fwrite($fh, $query);
        fclose($fh);
        //exec('echo "'.$query.'" > RA/in');
        exec('java -jar RA/ra.jar RA/res/ra/ra.properties -i RA/in', &$output);
        for ($i = 5; $i < count($output, 0)-3; $i++) {
            echo $output[$i]."<br>";
        }
    }
} else {
    phpinfo();
}
?>