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
                'caption'=>'Profil Sekolah',
                'link'=>'sekolah-profil.php'                       
            ),
            array(
                'caption'=>'Deskripsi Sekolah',
                'link'=>'sekolah-deskripsi.php'                       
            ),
            array(
                'caption'=>'Ganti Sekolah',
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
                'link'=>'kelas.php'                       
            ),
            array(
                'caption'=>'Jurusan',
                'link'=>'jurusan.php'                       
            ),
            array(
                'caption'=>'Perubahan Kelas Siswa',
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
                    'link'=>'siswa.php'                       
                ),
                array(
                    'caption'=>'Guru',
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
                    'link'=>'ujian.php'                       
                ),
                array(
                    'caption'=>'Soal',
                    'link'=>'ujian-soal.php'                       
                ),
                array(
                    'caption'=>'Ekspor Soal',
                    'link'=>'ujian-ekspor.php'                       
                ),
                array(
                    'caption'=>'Bank Soal',
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