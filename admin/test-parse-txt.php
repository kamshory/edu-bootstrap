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
img:1e8c02863854cb30c7c9f5ef3e3161d1.svg#middle##latex%7C%5Cint_%7Bx%3D0%7D%5E%7B23%7D%20%5Csin(2x)%20dx 
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
A. Kontak\\
| No | Nama | Alamat | Telepon | Email |\\
| -- | -- | -- | -- | -- |\\
| 1 | Test | Bandung | 088888 | wryw@ueuwfh |\\
| 2 | Tos | Jakarta | 375823 | sdgs@krgh |
B. Pelanggan
C. Calon Pelanggan
D. Mita
Jalaban: A

Berikut ini adalah sebuah tabel
img:37f0d72eb5c45dcd47768ffd60b4467d.svg#middle##latex%7Cf(x)%3Da_0%2B%5Csum_(n%3D1)%5E%E2%88%9E%20(a_n%20%20%5Ccos%20(n%CF%80x%2FL)%2Bb_n%20%20%5Csin%20(n%CF%80x%2FL)%20)%20 
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






';
echo "<ol>\r\n";
$clear_data = parseRawQuestion($raw_txt_data);
foreach ($clear_data as $question_no => $question) {
    $object = parseQuestion($question);
    print_r($object);
    echo "<li>".$object['question'];
    echo "<ol>\r\n";
    foreach($object['option'] as $opt)
    {
        echo "<li>".$opt['text']."<br>".$opt['value'];
    }
    echo "</ol>\r\n";
    echo "</li>";
}
echo "</ol>\r\n";

