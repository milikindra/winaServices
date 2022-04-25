<?php

namespace App\Tree;

class ModuleNode
{
    // public $data;
    public $children;
    public $module_id;
    public $module_name;
    public $path;
    public $icon_class;
    public $route;
    public $end;

    public $id;
    public $level;
    public $parent;

    public function __construct($id, $dataset, $level)
    {
        if (isset($dataset[$id])) {
            $this->module_id = $dataset[$id]->module_id;
            $this->module_name = $dataset[$id]->module_name;
            $this->path = $dataset[$id]->module_name;
            $this->icon_class = $dataset[$id]->icon_class;
            $this->route = $dataset[$id]->module_routes;
            $this->notes = $dataset[$id]->notes;
            // $this->data = $dataset[$id];
        }
        $this->level = $level;
        $this->id = $id;
        $this->end = false;
        $this->children = [];
    }
    public function addChildren($dataset, $parent, &$list)
    {
        $this->parent = $parent;
        // if($depth < $depthMax)
        // {
        // $depth++;
        if (isset($dataset[$this->id])) {
            // $this->parent[$this->data['member_id']]['id'] = $this->data['member_id'];
            // $this->parent[$this->data['member_id']]['text'] = $this->data['member_id']." - ".$this->data['member_name'];
            // $this->parent[$this->data['member_id']]['level'] = $this->level;
            if ($dataset[$this->id]->module_child != null) {
                $loop = explode(',', $dataset[$this->id]->module_child);
                foreach ($loop as $children) {
                    $this->level++;
                    $temp = new ModuleNode($children, $dataset, $this->level);
                    $temp->addChildren($dataset, $this->parent, $list);
                    array_push($this->children, $temp);
                }
            } else {
                $this->children = $this->level;
                $this->end = true;
            }
        }
        // }
    }
}
