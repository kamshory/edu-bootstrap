<?php
require_once dirname(__DIR__)."/lib.inc/auth-admin.php";
require_once dirname(__DIR__)."/lib.inc/lib.test.php";
$raw_txt_data = '

Berikut ini adalah sebuah tabel
| No | Nama | Alamat | Telepon | Email |
| -- | -- | -- | -- | -- |
| 1 | Tio | Bandung | 088888 | wryw@ueuwfh |
| 2 | Dio | Jakarta | 375823 | sdgs@krgh |
| 3 | Rio | Makassar | 356735 | djfsdjf@khsk |
img:0538dce591b915131d25f588845f73fb.svg#middle##latex%7C%5Cint_%7Bx%3D0%7D%5E%7B23%7D%20%5Csin(2x)%20dx 
Data di atas adalah data..
A. Kontak
B. Pelanggan
| No | Nama | Alamat | Telepon | Email |
| -- | -- | -- | -- | -- |
| 1 | Budi | Bandung | 088888 | wryw@ueuwfh |
| 2 | Didi | Jakarta | 375823 | sdgs@krgh |
C. Calon Pelanggan
D. Mita
Jalaban: A

Berikut ini adalah sebuah tabel
| No | Nama | Alamat | Telepon | Email |
| -- | -- | -- | -- | -- |
| 1 | Test | Bandung | 088888 | wryw@ueuwfh |
| 2 | Tos | Jakarta | 375823 | sdgs@krgh |
Data di atas adalah data..
A. Kontak
B. Pelanggan
C. Calon Pelanggan
D. Mita
Jalaban: A

Berikut ini adalah sebuah tabel
| No | Nama | Alamat | Telepon | Email |
| -- | -- | -- | -- | -- |
| 1 | Test | Bandung | 088888 | wryw@ueuwfh |
| 2 | Tos | Jakarta | 375823 | sdgs@krgh |
Data di atas adalah data..
A. Kontak
| No | Nama | Alamat | Telepon | Email |
| -- | -- | -- | -- | -- |
| 1 | Test | Bandung | 088888 | wryw@ueuwfh |
| 2 | Tos | Jakarta | 375823 | sdgs@krgh |
B. Pelanggan
C. Calon Pelanggan
| No | Nama | Alamat | Telepon | Email |
| -- | -- | -- | -- | -- |
| 1 | Test | Bandung | 088888 | wryw@ueuwfh |
| 2 | Tos | Jakarta | 375823 | sdgs@krgh |
D. Mita
Jalaban: A

Berikut ini adalah sebuah tabel
img:2329cf7a0b2a8ad11e07ef5d3c85c996.png#middle##latex%7Cf(x)%3Da_0%2B%5Csum_(n%3D1)%5E%E2%88%9E%20(a_n%20%20%5Ccos%20(n%CF%80x%2FL)%2Bb_n%20%20%5Csin%20(n%CF%80x%2FL)%20)%20 
| No | Nama | Alamat | Telepon | Email |
| -- | -- | -- | -- | -- |
| 1 | Test | Bandung | 088888 | wryw@ueuwfh |
| 2 | Tos | Jakarta | 375823 | sdgs@krgh |
| 3 | Didi | Surabaya | 3532587 | sdfhs@hguf |
Data di atas merupakan data...
A. Kontak
B. Pelanggan
C. Calon Pelanggan
D. Mita
Jalaban: A

Berikut ini adalah sebuah tabel
| No | Nama | Alamat | Telepon | Email |
| -- | -- | -- | -- | -- |
| 1 | Test | Bandung | 088888 | wryw@ueuwfh |
| 2 | Tos | Jakarta | 375823 | sdgs@krgh |
| 3 | Didi | Surabaya | 3532587 | sdfhs@hguf |
Data di atas merupakan data...
A. img:2329cf7a0b2a8ad11e07ef5d3c85c996.png#middle##latex%7Cf(x)%3Da_0%2B%5Csum_(n%3D1)%5E%E2%88%9E%20(a_n%20%20%5Ccos%20(n%CF%80x%2FL)%2Bb_n%20%20%5Csin%20(n%CF%80x%2FL)%20)%20  Kontak 
B. Pelanggan
C. Calon Pelanggan
D. Mita
Jalaban: A









';

$picoTest = new \Pico\PicoTestCreator();
$test_dir = "";
$base_src = "";

echo "<ol>\r\n";
$clear_data = $picoTest->parseRawQuestion($raw_txt_data);
foreach ($clear_data as $question_no => $question) {
    $object = $picoTest->parseQuestion($question);

    $content = $picoTest->fixTable(nl2br(utf8ToEntities(\Pico\PicoDOM::filterHtml(\Pico\PicoDOM::addImages(@$object['question'], $test_dir, $base_src)))));
    $content = $picoEdu->brToNewLineEncoded($content);

    echo "<li>".$content;
    echo "<ol>\r\n";
    foreach($object['option'] as $opt)
    {
        $content = $picoTest->fixTable(nl2br(utf8ToEntities(\Pico\PicoDOM::filterHtml(\Pico\PicoDOM::addImages(@$opt['text'], $test_dir, $base_src)))));
        $content = $picoEdu->brToNewLineEncoded($content);
    
        echo "<li>".$content."<br>".$opt['value'];
    }
    echo "</ol>\r\n";
    echo "</li>";
}
echo "</ol>\r\n";

