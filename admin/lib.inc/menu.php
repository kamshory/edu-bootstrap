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
                'caption'=>'Daftar Kelas',
                'link'=>'kelas-daftar.php'                       
            ),
            array(
                'caption'=>'Daftar Jurusan',
                'link'=>'kelas-deskripsi.php'                       
            ),
            array(
                'caption'=>'Perubahan Kelas Siswa',
                'link'=>'kelas-siswa-ubah.php'                       
            )
        )
    )

);

class MainMenu
{
    public function __construct()
    {
        // Do nothing
    }
    public function show($strcuture, $selectedMenu)
    {
$html = 
'       <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="sidebar-sticky pt-3">
                <div class="accordion" id="accordionEx" role="tablist" aria-multiselectable="true">
';
        if(is_array($strcuture))
        {
            foreach($strcuture as $index => $menu)
            {
$html .= 
'          <div class="card">' . "\r\n";
                $captionMenu = $menu['caption'];
                $expanded = $this->containLink($menu, $selectedMenu);
                $html .= $this->createMenu($index, $captionMenu, $expanded);
                $html .= $this->createSubmenu($index, $menu, $expanded, $selectedMenu);
$html .= 
'           </div>
';
            }
        }
$html .= 
'      </nav>' . "\r\n";
        return $html;
    }

    public function containLink($menu, $selectedMenu)
    {
        if(!isset($menu['submenu']))
        {
            return false;
        }
        foreach($menu['submenu'] as $submenu)
        {
            if($submenu['link'] == $selectedMenu)
            {
                return true;
            }
        }
        return false;
    }

    public function createSubmenu($index, $menu, $expanded, $selectedMenu)
    {
        $cls1 = $expanded ? ' show' : '';
        if(!isset($menu['submenu']))
        {
            return '';
        }
        $html = '';
        $id = 'collapseMainMenu' . $index;

        $html .= 
'        <div id="'.$id.'" class="collapse'.$cls1.'" role="tabpanel" aria-labelledby="headingOne" data-parent="#accordionEx" style="">
            <div class="menu-child">
';



        foreach($menu['submenu'] as $submenu)
        {
            $captionSubmenu = $submenu['caption'];
            $linkSubmenu = $submenu['link'];
            
$html .= 
'               <a href="'.$linkSubmenu.'" class="list-group-item list-group-item-action py-2 ripple" aria-current="true"><i class="fas fa-tachometer-alt fa-fw me-3"></i><span>'.$captionSubmenu.'</span></a>
';
        }
$html .= 
'           </div>
        </div>
';

        
        return $html;
    }

    public function createMenu($index, $caption, $expanded)
    {
        $id = 'collapseMainMenu' . $index;
        $attrExpanded = $expanded ? ' aria-expanded="true"' : '';
        return '   <div class="card-header" role="tab" id="headingOne"'.$attrExpanded.'>
        <a data-toggle="collapse" href="#'.$id.'" aria-controls="'.$id.'" class="collapsed">
          <div class="mb-0">
            '.$caption.' <i class="fa fa-angle-down rotate-icon float-right"></i>
          </div>
        </a>
      </div>
      ';
    }
}

$maniMenu = new MainMenu();
echo $maniMenu->show($structure, basename($_SERVER['PHP_SELF']));