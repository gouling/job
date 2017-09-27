<?php
    set_time_limit(0);
    include_once('CExcel.php');
    $excel = new CExcel(array(
        'cache' => __DIR__ . DIRECTORY_SEPARATOR . 'cache',
        'template' => __DIR__ . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'default.data',
        'column' => array('编号', '名称'),
    ));
    
    $db = mysqli_connect('localhost', 'root', 'root', 'framework');
    $query = mysqli_query($db, 'SELECT count(*) as RecordCount FROM tbl_menu');
    $data = mysqli_fetch_assoc($query);
    $recordCount = $data['RecordCount'];

    $pageSize = 3;
    $pageCount = ceil($recordCount / $pageSize) + 1;
    for ($page = 1, $identity = 0; $page < $pageCount; $page++) {
        $identity = ($page - 1) * $pageSize;
        $query = mysqli_query($db, "SELECT id,name FROM tbl_menu LIMIT {$identity}, {$pageSize}");
        $data = array();
        while ($row = mysqli_fetch_assoc($query)) {
            $data[]=$row;
        }
        $excel->load($data, $page);
    }
    $excel->create()->download();
?>
