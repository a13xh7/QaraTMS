<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuVisibility extends Model
{
    protected $fillable = [
        'menu_key',
        'menu_name',
        'is_visible',
        'parent_key',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    /**
     * Define parent-child relationships for menu items
     */
    private static $menuHierarchy = [
        'analytics_dashboard' => [
            'grafana_automation_report',
            'defect_analytics_dashboard',
            'testing_progress'
        ],
        'grafana_automation_report' => [
            'api_dashboard',
            'apps_dashboard'
        ],
        'defect_analytics_dashboard' => [
            'defect_analytics',
            'bug_budget'
        ]
    ];

    /**
     * Get all children menu keys for a given parent
     * 
     * @param string $parentKey
     * @return array
     */
    public static function getChildren(string $parentKey): array
    {
        return self::$menuHierarchy[$parentKey] ?? [];
    }

    /**
     * Get the parent key for a given menu item
     * 
     * @param string $menuKey
     * @return string|null
     */
    public static function getParent(string $menuKey): ?string
    {
        foreach (self::$menuHierarchy as $parent => $children) {
            if (in_array($menuKey, $children)) {
                return $parent;
            }
        }
        return null;
    }

    /**
     * Get all visible menu items
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getVisibleMenus()
    {
        return self::where('is_visible', true)->get();
    }

    /**
     * Get visibility status for a specific menu item considering parent/child relationships
     *
     * @param string $menuKey
     * @return bool
     */
    public static function isVisible(string $menuKey): bool
    {
        // Get the menu item
        $menu = self::where('menu_key', $menuKey)->first();
        
        // If menu item doesn't exist or is explicitly hidden, it's not visible
        if (!$menu || !$menu->is_visible) {
            return false;
        }
        
        // Check if this is a parent menu and if it has any visible children
        if (isset(self::$menuHierarchy[$menuKey])) {
            $children = self::$menuHierarchy[$menuKey];
            if (empty($children)) {
                return $menu->is_visible;
            }
            
            // Check if at least one child is visible
            $hasVisibleChild = false;
            foreach ($children as $childKey) {
                if (self::isVisible($childKey)) {
                    $hasVisibleChild = true;
                    break;
                }
            }
            
            // Parent is only visible if at least one child is visible
            return $hasVisibleChild;
        }
        
        // For items that aren't parents, check if their parent is visible
        $parentKey = self::getParent($menuKey);
        if ($parentKey) {
            $parentMenu = self::where('menu_key', $parentKey)->first();
            // If parent exists and is explicitly hidden, child should be hidden too
            if ($parentMenu && !$parentMenu->is_visible) {
                return false;
            }
        }
        
        // If no parent relationship or parent is visible, use the item's own visibility
        return $menu->is_visible;
    }

    /**
     * Update visibility for a menu item and its children
     *
     * @param string $menuKey
     * @param bool $isVisible
     * @return void
     */
    public static function updateVisibilityWithChildren(string $menuKey, bool $isVisible): void
    {
        // Update this menu item
        $menu = self::where('menu_key', $menuKey)->first();
        if ($menu) {
            $menu->is_visible = $isVisible;
            $menu->save();
        }
        
        // If hiding a parent, hide all its children
        if (!$isVisible && isset(self::$menuHierarchy[$menuKey])) {
            foreach (self::$menuHierarchy[$menuKey] as $childKey) {
                self::updateVisibilityWithChildren($childKey, false);
            }
        }
    }
}
