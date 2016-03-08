<?php

namespace Gcl\GclUsers\Models;

use Baum\Node;
use Gcl\GclUsers\Models\RoleUser;
use Gcl\GclUsers\Models\PermissionRole;

/**
* NodePermission
*/
class NodePermission extends Node {

    public static $_model = null;

    /**
    * Table name.
    *
    * @var string
    */
    protected $table = 'permissions';

    //////////////////////////////////////////////////////////////////////////////


    // Below come the default values for Baum's own Nested Set implementation
    // column names.

    // You may uncomment and modify the following fields at your own will, provided
    // they match *exactly* those provided in the migration.

    // If you don't plan on modifying any of these you can safely remove them.


    /**
    * Column name which stores reference to parent's node.
    *
    * @var string
    */
    protected $parentColumn = 'parent_id';

    /**
    * Column name for the left index.
    *
    * @var string
    */
    protected $leftColumn = 'lft';

    /**
    * Column name for the right index.
    *
    * @var string
    */
    protected $rightColumn = 'rgt';

    /**
    * Column name for the depth field.
    *
    * @var string
    */
    protected $depthColumn = 'depth';

    /**
    * Column to perform the default sorting
    *
    * @var string
    */
    protected $orderColumn = null;

    /**
    * With Baum, all NestedSet-related fields are guarded from mass-assignment
    * by default.
    *
    * @var array
    */
    protected $guarded = array('id', 'parent_id', 'lft', 'rgt', 'depth');

    // This is to support "scoping" which may allow to have multiple nested
    // set trees in the same database table.

    // You should provide here the column names which should restrict Nested
    // Set queries. f.ex: company_id, etc.


    /**
    * Columns which restrict what we consider our Nested Set list
    *
    * @var array
    */
    protected $scoped = array();

    // public static function create(array $attributes = [])
    // {
    // }

    /**
     * Add a node permission
     *
     * @param array $attributes
     * @return json
     */
    public function add(array $attributes = [])
    {
        if (isset($attributes['parent_id']) && !empty($attributes['parent_id'])) {
            $parent = parent::where('id', '=', $attributes['parent_id'])->first();

            if (!$parent) {
                return false;
            }

            $child = parent::create($attributes);
            $child->makeChildOf($parent);
        } else {
            $root = $this->getRootNode();
            $child = parent::create($attributes);

            $child->makeChildOf($root);
        }

        return $this->getRootChildren();
    }

    /**
     * Update node tree permission
     *
     * @param json $attributes
     * @return json
     */
    public function tree($attributes = null)
    {
        $tree = json_decode($attributes, true);

        if (!empty($tree)) {
            $root = $this->getRootNode();
            $root->makeTree($tree);
        }

        return $this->getRootChildren();
    }

    /**
     * Get Root node
     *
     * @return root model
     */
    public function getRootNode()
    {
        return parent::find(1);
    }

    /**
     * Get children of root permission
     *
     * @return json
     */
    public function getRootChildren()
    {
        $root = $this->getRootNode();

        if ($root) {
            return $this->allNode($this->getRootNode()->id);
        }

        return '';
    }

    /**
     * List all node of one root
     *
     * @return json
     */
    public function allNode($root_id)
    {
        $tree = parent::where('id', '=', $root_id)->first()->getDescendants/*AndSelf*/()->toHierarchy();

        $result = json_encode($this->hierarchyToArr($tree->toArray()));

        return $result;
    }

    /**
     * Convert node hierarchy to array
     *
     * @param array $hierarchy
     * @return array
     */
    public function hierarchyToArr($hierarchy)
    {
        if (empty($hierarchy)) {
            return;
        }

        $result = [];

        foreach ($hierarchy as $key => $item) {
            $result[$key]['id'] = $item['id'];
            $result[$key]['name'] = $item['name'];
            $result[$key]['display_name'] = $item['display_name'];

            if (!empty($item['children'])) {
                $result[$key]['children'] = $this->hierarchyToArr($item['children']);
            }
        }

        return array_values($result);
    }

    /**
     * List permission of role
     *
     * @param user id
     * @return json
     */
    public function rolePerm($roleId)
    {
        $role = new PermissionRole;

        $tree = parent::where('id', '=', $this->getRootNode()->id)->first()->getDescendants()->toHierarchy();

        // Get list permission with status
        $permissions = $role->getRolePermission($roleId, $tree->toArray());

        return $permissions;
    }

    ////////////////////////////////////////////////////////////////////////////


    // Baum makes available two model events to application developers:

    // 1. `moving`: fired *before* the a node movement operation is performed.

    // 2. `moved`: fired *after* a node movement operation has been performed.

    // In the same way as Eloquent's model events, returning false from the
    // `moving` event handler will halt the operation.

    // Please refer the Laravel documentation for further instructions on how
    // to hook your own callbacks/observers into this events:
    // http://laravel.com/docs/5.0/eloquent#model-events

    /**
     * Retrieve model object
     */
    public static function model()
    {
        if (self::$_model === null) {
            self::$_model = new self;
        }

        return self::$_model;
    }

}
