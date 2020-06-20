<?php

namespace mhs\tools;

/**
 * Class Categories
 * @package mhs\tools
 */
class Categories
{
    /**
     * @var Tree
     */
    protected $tree = null;

    /**
     * @param array $data
     * @param array $options
     * @return Tree
     */
    public function tree($data = [], $options = [])
    {
        if (!$this->tree) {
            $this->tree = new Tree($data, $options);
        } else {
            $data && $this->tree->setData($data);
            $options && $this->tree->setOptions($options);
        }

        return $this->tree;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->tree(), $name], $arguments);
    }
}