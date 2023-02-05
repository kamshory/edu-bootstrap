<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
include_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";
$raw_txt_data = '
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
B. Pelanggan
C. Calon Pelanggan
D. Mita
Jalaban: A

';

$clear_data = parseRawQuestion($raw_txt_data);
foreach ($clear_data as $question_no => $question) {
    $object = parseQuestion($question);
    print_r($object);
}

$text = "Berikut ini adalah sebuah tabel<br />| No | Nama | Alamat | Telepon | Email |<br />| -- | -- | -- | -- | -- |<br />| 1 | Test | Bandung | 088888 | wryw@ueuwfh |<br />| 2 | Tos | Jakarta | 375823 | sdgs@krgh |<br />Data di atas adalah data..";

