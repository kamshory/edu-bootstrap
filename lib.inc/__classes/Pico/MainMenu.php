<?php
namespace Pico;

class MainMenu
{
    public function __construct()
    {
        // Do nothing
    }

    /**
     * Show menu
     * @param array $strcuture Menu structure
     * @param string $selectedMenu Selected menu link
     * @return string HTML contain menu structure
     */
    public function show($strcuture, $selectedMenu)
    {
        $html =
            '       <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="sidebar-sticky pt-3">
                <div class="accordion" id="accordionEx" role="tablist" aria-multiselectable="true">
';
        if (is_array($strcuture)) {
            foreach ($strcuture as $index => $menu) {
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
            '               </div>
            </div>
        </nav>' . "\r\n";
        return $html;
    }

    /**
     * Check if menu is contain selected menu link
     * @param array $menu Menu object
     * @param string $selectedMenu Selected menu link
     * @return bool true if menu is contain selected menu link and false if menu is not contain selected menu link
     */
    private function containLink($menu, $selectedMenu)
    {
        if (!isset($menu['submenu'])) {
            return false;
        }
        foreach ($menu['submenu'] as $submenu) {
            if ($submenu['link'] == $selectedMenu) {
                return true;
            }
        }
        return false;
    }

    /**
     * Create submenu
     * @param int $index Menu index
     * @param array $menu Menu object
     * @param bool $expanded Flag if menu is expanded or not
     * @param string $selectedMenu Selected menu link
     * @return string HTML contain menu structure
     */
    private function createSubmenu($index, $menu, $expanded, $selectedMenu) //NOSONAR
    {
        $cls1 = $expanded ? ' show' : '';
        if (!isset($menu['submenu'])) {
            return '';
        }
        $html = '';
        $id = 'collapseMainMenu' . $index;

        $html .= '        <div id="' . $id . '" class="collapse' . $cls1 . '" role="tabpanel" aria-labelledby="headingOne" data-parent="#accordionEx">
            <div class="menu-child">
';


        foreach ($menu['submenu'] as $submenu) {
            $captionSubmenu = $submenu['caption'];
            $linkSubmenu = $submenu['link'];
            $icon = ' ' . trim($submenu['icon']);
            $active = $selectedMenu == $linkSubmenu ? ' active' : '';


            $html .=
                '               <a href="' . $linkSubmenu . '" class="list-group-item list-group-item-action py-2 ripple' . $active . '" aria-current="true"><i class="fas' . $icon . ' fa-fw me-3"></i><span>' . $captionSubmenu . '</span></a>
';
        }
        $html .=
            '           </div>
        </div>
';


        return $html;
    }

    /**
     * Create menu
     * @param int $index Menu index
     * @param string $caption Menu caption
     * @param bool $expanded Flag if menu is expanded or not
     * @return string HTML contain menu structure
     */
    public function createMenu($index, $caption, $expanded)
    {
        $id = 'collapseMainMenu' . $index;
        $attrExpanded = $expanded ? ' aria-expanded="true"' : '';
        return '   <div class="card-header" role="tab" id="headingOne"' . $attrExpanded . '>
        <a data-toggle="collapse" href="#' . $id . '" aria-controls="' . $id . '" class="collapsed">
          <div class="mb-0">
            ' . $caption . ' <i class="fa fa-angle-down rotate-icon float-right"></i>
          </div>
        </a>
      </div>
      ';
    }
}
