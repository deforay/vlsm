<?php

namespace App\Services;

use MysqliDb;
use App\Services\UsersService;
use App\Services\CommonService;
use App\Registries\ContainerRegistry;

class AppMenuService
{
    protected ?MysqliDb $db = null;
    protected string $table = 's_app_menu';
    protected CommonService $commonService;
    protected UsersService $usersService;

    public function __construct(?MysqliDb $db, CommonService $commonService, UsersService $usersService)
    {
        $this->db = $db ?? ContainerRegistry::get('db');
        $this->commonService = $commonService;
        $this->usersService = $usersService;
    }

    public function getAllActiveMenus($parentId = 0, $menuId = 0)
    {
        $this->db->where('status', 'active');
        if (!empty($menuId) && $menuId > 0) {
            $this->db->where('id', $menuId);
        }
        $this->db->where('parent_id', $parentId);
        $this->db->orderBy("display_order", "asc");
        $menuData = $this->db->get($this->table);
        $response = [];
        foreach ($menuData as $key => $menu) {
            $menu['access'] = true;
            if ($menu['link']  != "" && !empty($menu['link'])) {
                $menu['access'] = $this->usersService->isAllowed($menu['link']);
            }

            $menu['children'] = $menu['has_children'] == 'yes' ? $this->getAllActiveMenus($menu['id']) : [];
            $response[$key] = $menu['access'] ? $menu : null;
        }

        return array_filter($response);
    }
}
