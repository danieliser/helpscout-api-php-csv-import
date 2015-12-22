<?php

require_once 'init.php';

if ( ! $_FILES || empty ( $_FILES['backlog_file'] ) ) { ?>
	<form action="" method="post" enctype="multipart/form-data">
		<label>
			Select CSV File
			<input type="file" name="backlog_file" />
		</label>
		<button type="submit">Upload</button>
	</form><?php
} else {
	$backlog = csv_to_array( $_FILES['backlog_file']['tmp_name'] );
	save_array_to_file( $backlog, 'uploads/backlog-current.php' );
}
