<?php

$structure = array(
    array(
        'caption'=>'Depan',
        'link'=>'index.php',
        'submenu'=>array(
            array(
                'caption'=>'Halaman Depan',
                'link'=>'index.php'                       
            )
        )
    ),
    array(
        'caption'=>'Sekolah',
        'link'=>'sekolah.php',
        'submenu'=>array(
            array(
                'caption'=>'Sekolah',
                'link'=>'sekolah.php'                       
            ),
            array(
                'caption'=>'Ganti Sekolah',
                'link'=>'ganti-sekolah.php'                       
            )
        )
    ),
    array(
        'caption'=>'Kelas',
        'link'=>'kelas.php',
        'submenu'=>array(
            array(
                'caption'=>'Kelas',
                'link'=>'kelas.php'                       
            ),
            array(
                'caption'=>'Jurusan',
                'link'=>'jurusan.php'                       
            )
        )
        ),
        array(
            'caption'=>'Pengguna',
            'link'=>'siswa.php',
            'submenu'=>array(
                array(
                    'caption'=>'Siswa',
                    'link'=>'siswa.php'                       
                ),
                array(
                    'caption'=>'Guru',
                    'link'=>'guru.php',
                )
            )
        ),
        array(
            'caption'=>'Ujian',
            'link'=>'ujian.php',
            'submenu'=>array(
                array(
                    'caption'=>'Ujian',
                    'link'=>'ujian.php'                       
                ),
                array(
                    'caption'=>'Riwayat',
                    'link'=>'ujian-riwayat.php'                       
                ),
                array(
                    'caption'=>'Laporan Hasil',
                    'link'=>'ujian-laporan.php'                       
                )
            )
        ),
        array(
            'caption'=>'Artikel',
            'link'=>'artikel.php',
            'submenu'=>array(
                array(
                    'caption'=>'Artikel',
                    'link'=>'artikel.php'                       
                ),
                array(
                    'caption'=>'Informasi',
                    'link'=>'informasi.php'                       
                )
            )
        ),
        array(
            'caption'=>'Keluar',
            'link'=>'logout.php',
            'submenu'=>array(
                array(
                    'caption'=>'Keluar',
                    'link'=>'logout.php'                       
                )
            )
        )

);

require_once dirname(dirname(dirname(__FILE__))) . "/lib.inc/classes/MainMenu.php";



$maniMenu = new MainMenu();
echo $maniMenu->show($structure, basename($_SERVER['PHP_SELF']));