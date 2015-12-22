<?php

function format_api_date( $date = 'NOW' ) {
	return date( 'Y-m-d\TH:i:s\Z', strtotime( $date ) );
}

function filename_from_url( $url ) {
	return basename( parse_url( $url, PHP_URL_PATH ) );
}

function endswith($string, $test) {
	$strlen = strlen($string);
	$testlen = strlen($test);
	if ($testlen > $strlen) return false;
	return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
}

function csv_to_array($filename='', $delimiter=',')
{
	if(!file_exists($filename) || !is_readable($filename))
		return FALSE;

	$header = NULL;
	$data = array();
	if (($handle = fopen($filename, 'r')) !== FALSE)
	{
		while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
		{
			if(!$header)
				$header = $row;
			else
				$data[] = array_combine($header, $row);
		}
		fclose($handle);
	}
	return $data;
}

function save_array_to_file( $data, $file ) {
	file_put_contents( $file, '<?php return '.var_export( $data, true ).";\n" );
}

function readCSV($csvFile){

	$rows = array_map('str_getcsv', file($csvFile));
	$header = array_shift($rows);
	$csv = array();
	foreach ($rows as $row) {
		$csv[] = array_combine($header, $row);
	}

	return $csv;

	$file_handle = fopen( $csvFile, 'r' );
	while ( ! feof( $file_handle ) ) {
		$line_of_text[] = fgetcsv( $file_handle, 1024 );
	}
	fclose( $file_handle );
	return $line_of_text;
}

function dbg( $var ) {
	echo '<pre>'; print_r( $var ); echo '</pre>';
}
