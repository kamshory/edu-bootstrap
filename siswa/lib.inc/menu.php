<?php
if(!isset($cfg))
{
  exit();
}


$structure = array(
    array(
        'caption'=>'Depan',
        'link'=>'index.php',
        'submenu'=>array(
            array(
                'caption'=>'Halaman Depan',
                'icon'=>'fa-home',
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
                'icon'=>'fa-school',
                'link'=>'sekolah.php'                       
            ),
            array(
                'caption'=>'Ganti Sekolah',
                'icon'=>'fa-right-left',
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
                'icon'=>'fa-users',
                'link'=>'kelas.php'                       
            ),
            array(
                'caption'=>'Jurusan',
                'icon'=>'fa-users',
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
                    'icon'=>'fa-user',
                    'link'=>'siswa.php'                       
                ),
                array(
                    'caption'=>'Guru',
                    'icon'=>'fa-user',
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
                    'icon'=>'fa-pencil',
                    'link'=>'ujian.php'                       
                ),
                array(
                    'caption'=>'Riwayat',
                    'icon'=>'fa-calendar-days',
                    'link'=>'ujian-riwayat.php'                       
                ),
                array(
                    'caption'=>'Laporan Hasil',
                    'icon'=>'fa-table',
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
                    'icon'=>'fa-file',
                    'link'=>'artikel.php'                       
                ),
                array(
                    'caption'=>'Informasi',
                    'icon'=>'fa-file',
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
                    'icon'=>'fa-right-to-bracket',
                    'link'=>'logout.php'                       
                )
            )
        )

);

require_once dirname(dirname(dirname(__FILE__))) . "/lib.inc/classes/MainMenu.php";



$maniMenu = new MainMenu();
echo $maniMenu->show($structure, basename($_SERVER['PHP_SELF']));