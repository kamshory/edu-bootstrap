<?php

require_once __DIR__."/xlsxwriter/xlsxwriter.class.php";
$writer = new XLSXWriter();
$filename = "Tambah Siswa";
$filename = filter_sanitize_file_name($filename);
$filename .= ".xlsx";

header('Content-disposition: attachment; filename="'.$filename.'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');

$sqlc = "SELECT `edu_class`.`class_id`, `edu_class`.`name`, `edu_class`.`school_program_id` 
FROM `edu_class` 
LEFT JOIN (`edu_school_program`) ON (`edu_school_program`.`school_program_id` = `edu_class`.`school_program_id`)
WHERE `edu_class`.`active` = true AND `edu_class`.`school_id` = '$school_id' AND `edu_class`.`name` != '' 
ORDER BY `edu_class`.`grade_id` ASC, `edu_school_program`.`sort_order` ASC , `edu_class`.`sort_order` ASC 
";
$arrc = $database->fetchAssocAll($sqlc, array());

$header = array(
    "NISN"=>'string', 
    "NIS"=>'string', 
    "NAMA"=>'string', 
    "JENIS_KELAMIN"=>'string', 
    "TEMPAT_LAHIR"=>'string',
    "TANGGAL_LAHIR"=>'date',
    "TELEPON"=>'string', 
    "EMAIL"=>'string', 
    "ALAMAT"=>'string'
);

foreach($arrc as $class)
{   
    $className = $class['name'];
    $writer->writeSheetHeader($className, $header );
    $rows = array(
        array(
            "NISN"=>'<NISN>', 
            "NIS"=>'<NIS>', 
            "NAMA"=>'<NAMA SISWA>', 
            "JENIS_KELAMIN"=>'L', 
            "TEMPAT_LAHIR"=>'<TEMPAT LAHIR SISWA>',
            "TANGGAL_LAHIR"=>date('Y-m-d', strtotime('-15 years')),
            "TELEPON"=>'<TELEPON SISWA>', 
            "EMAIL"=>'<EMAIL SISWA>', 
            "ALAMAT"=>'<ALAMAT SISWA>'
        ),
        array(
            "NISN"=>'<NISN>', 
            "NIS"=>'<NIS>', 
            "NAMA"=>'<NAMA SISWI>', 
            "JENIS_KELAMIN"=>'P', 
            "TEMPAT_LAHIR"=>'<TEMPAT LAHIR SISWA>',
            "TANGGAL_LAHIR"=>date('Y-m-d', strtotime('-15 years')),
            "TELEPON"=>'<TELEPON SISWA>', 
            "EMAIL"=>'<EMAIL SISWA>', 
            "ALAMAT"=>'<ALAMAT SISWA>'
        )
    );
    foreach($rows as $row)
    {
        $writer->writeSheetRow($className, array_values($row));
    }
}

$writer->writeToStdOut();
