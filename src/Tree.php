<?php

namespace mhs\tools;

class Tree
{
    /**
     * @var array $data
     */
    protected $data = [];
    /**
     * @var array $options
     */
    protected $options = [
        'pk' => 'id', // pk field
        'parent' => 'pid', // parent field
        'level' => '', // level field
        'children' => 'children', // children field
        'has' => 'hasChildren', // hasChildren
        'pad' => '', // level pad
        'pad_name' => '', // pad name field
    ];
    /**
     * @var int
     */
    protected $maxLevel = 0;

    public function __construct($data = [], $options = [])
    {
        $this->setData($data);
        $this->setOptions($options);
    }

    /**
     * get sub tree
     * @param int $pid
     * @return array
     */
    public function subTree($pid = 0)
    {
        return $this->buildSubTree($this->data, $pid, 0);
    }

    /**
     * @param int $id
     * @return array|mixed
     */
    public function parentTree($id = 0)
    {
        return $this->buildParentTree($this->data, $id);
    }

    /**
     * @param $treeData
     * @param int $level
     * @return array
     */
    public function flatTree($treeData, $level = 1)
    {
        $result = [];
        if (!$treeData) {
            return $result;
        }
        $keys = array_keys($treeData);
        if (array_keys($keys) !== $keys) {
            $treeData = [$treeData];
        }
        foreach($treeData as $value) {
            $children = $value[$this->options['children']] ?? [];
            unset($value[$this->options['children']], $this->options['has']);
            $value = $this->pad($value, $level);
            $result[] = $value;
            if ($children) {
                $tree = new Tree([], $this->options);
                $res = $tree->flatTree($children, $level+1);
                $result = array_merge($result, $res);
            }
        }
        unset($tree, $res);

        return $result;
    }

    /**
     * @param array $data
     * @param mixed $pid
     * @param int $level
     * @return array
     */
    protected function buildSubTree($data, $pid, $level = 0)
    {
        $result = [];
        if($this->maxLevel && $level>=$this->maxLevel) {
            return $result;
        }
        foreach($data as $datum) {
            if ($datum[$this->options['parent']] == $pid) {
                $children = $this->buildSubTree($data, $datum[$this->options['pk']], $level+1);
                $result[] = $this->item($datum, $children, $level+1);
            }
        }

        return $result;
    }

    /**
     * @param $data
     * @param $id
     * @return array|mixed
     */
    protected function buildParentTree($data, $id)
    {
        $result = [];
        $data = array_column($data, null, $this->options['pk']);
        if (!isset($data[$id])) {
            return $result;
        }
        $this->options['level'] = '';
        $item = $this->item($data[$id], []);
        $pid = $item[$this->options['parent']];
        $level = 0;
        while(isset($data[$pid]) && (!$this->maxLevel || ($this->maxLevel && $level <= $this->maxLevel))) {
            $parent = $data[$pid];
            $pid = $parent[$this->options['parent']];
            $item = $this->item($parent, $item);
            $level++;
        }

        return $item;
    }

    /**
     * @param $item
     * @param array $children
     * @param int $level
     * @return mixed
     */
    protected function item($item, $children = [], $level = 0)
    {
        $item[$this->options['children']] = $children;
        $this->options['has'] && $item[$this->options['has']] = !empty($children);
        $this->options['level'] && $item[$this->options['level']] = $level;
        $item = $this->pad($item, $level);

        return $item;
    }

    /**
     * @param $item
     * @param $level
     * @return mixed
     */
    protected function pad($item, $level)
    {
        if ($this->options['pad'] && $this->options['pad_name']) {
            $level = $level > 0 ? ($level-1) : 0;
            $item['pad_name'] = str_repeat($this->options['pad'], $level).$item[$this->options['pad_name']];
        }

        return $item;
    }
    /**
     * @param array $data
     * @return self
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions(array $options): self
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * @param int $maxLevel
     * @return self
     */
    public function setMaxLevel(int $maxLevel): self
    {
        $this->maxLevel = $maxLevel;

        return $this;
    }
}