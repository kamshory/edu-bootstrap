<?php

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
                'caption'=>'Profil Sekolah',
                'icon'=>'fa-school',
                'link'=>'sekolah-profil.php'                       
            ),
            array(
                'caption'=>'Deskripsi Sekolah',
                'link'=>'sekolah-deskripsi.php'                       
            ),
            array(
                'caption'=>'Ganti Sekolah',
                'icon'=>'fa-right-left',
                'link'=>'ganti-sekolah.php'                       
            ),
            array(
                'caption'=>'Impor Data',
                'link'=>'impor-data.php'                       
            ),
            array(
                'caption'=>'Update Aplikasi',
                'link'=>'update.php'                       
            ),
            array(
                'caption'=>'Peralatan',
                'link'=>'peralatan.php'                       
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
            ),
            array(
                'caption'=>'Perubahan Kelas Siswa',
                'icon'=>'fa-right-left',
                'link'=>'kelas-siswa-ubah.php'                       
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
                ),
                array(
                    'caption'=>'Admin',
                    'link'=>'admin.php'                       
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
                    'caption'=>'Soal',
                    'icon'=>'fa-list',
                    'link'=>'ujian-soal.php'                       
                ),
                array(
                    'caption'=>'Ekspor Soal',
                    'icon'=>'fa-file-export',
                    'link'=>'ujian-ekspor.php'                       
                ),
                array(
                    'caption'=>'Bank Soal',
                    'icon'=>'fa-box',
                    'link'=>'ujian-bank-soal.php'                       
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