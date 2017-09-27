<?php
	include('CReportServer.php');

	$db=mysqli_connect('192.168.3.160', 'root', '123456', 'tcenter');
	$report=new CReportServer(__DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR);

	$query=mysqli_query($db, 'SELECT count(*) as RecordCount FROM tcenter_report_complex_new');
	$data=mysqli_fetch_all($query, MYSQLI_ASSOC);

	$recordCount=$data[0]['RecordCount'];
	$pageSize=2000;
	$pageCount=ceil($recordCount/$pageSize)+1;
	$params=$report->getData();

	for($page=1, $identity=0; $page<$pageCount; $page++) {
		$identity=($page-1)*$pageSize;

		$query=mysqli_query($db, "SELECT * FROM tcenter_report_complex_new LIMIT {$identity}, {$pageSize}");
		$report->load(mysqli_fetch_all($query, MYSQLI_ASSOC), 'tcenter_report_complex_new');
	}

	$query=mysqli_query($db, 'SELECT count(*) as RecordCount FROM tcenter_report_up');
	$data=mysqli_fetch_all($query, MYSQLI_ASSOC);

	$recordCount=$data[0]['RecordCount'];
	$pageSize=2000;
	$pageCount=ceil($recordCount/$pageSize)+1;

	for($page=1, $identity=0; $page<$pageCount; $page++) {
		$identity=($page-1)*$pageSize;

		$query=mysqli_query($db, "SELECT * FROM tcenter_report_up LIMIT {$identity}, {$pageSize}");
		$report->load(mysqli_fetch_all($query, MYSQLI_ASSOC), 'tcenter_report_up');
	}

	$report->create();
	$report->download();
?>