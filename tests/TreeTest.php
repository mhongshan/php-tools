<?php

namespace mhs\tools\tests;

use mhs\tools\Tree;
use PHPUnit\Framework\TestCase;

class TreeTest extends TestCase
{
    protected $data = [
        ['cid'=>1, 'pid'=>0, 'name'=>'user center'],
        ['cid'=>2, 'pid'=>1, 'name'=>'profile'],
        ['cid'=>3, 'pid'=>1, 'name'=>'secret'],
        ['cid'=>4, 'pid'=>0, 'name'=>'setting'],
        ['cid'=>5, 'pid'=>4, 'name'=>'web setting'],
        ['cid'=>6, 'pid'=>4, 'name'=>'upload setting'],
        ['cid'=>7, 'pid'=>3, 'name'=>'change password'],
        ['cid'=>8, 'pid'=>3, 'name'=>'change phone number'],
    ];
    protected $options = [
        'pk' => 'cid',
    ];

    public function testSubTree()
    {
        $tree = new Tree($this->data, $this->options+['level'=>'level','pad'=>'-', 'pad_name'=>'name']);
        $this->assertIsObject($tree);
        $treeData = $tree->subTree();
        $this->assertIsArray($treeData);
        $this->assertNotEmpty($treeData);
        $current = current($treeData);
        $this->assertArrayHasKey('children', $current);
        $this->assertEquals(2, $current['children'][0]['cid']);
        $this->assertEquals('-profile', $current['children'][0]['pad_name']);
    }

    public function testSubTreeWithPid()
    {
        $pid = 1;
        $tree = new Tree($this->data, $this->options);
        $this->assertIsObject($tree);
        $treeData = $tree->subTree($pid);
        $this->assertIsArray($treeData);
        $this->assertNotEmpty($treeData);
        $current = current($treeData);
        $this->assertArrayHasKey('children', $current);
        $this->assertFalse($current['hasChildren']);
    }

    public function testSubTreeWithMaxLevel()
    {
        $tree = new Tree($this->data, $this->options);
        $tree->setMaxLevel(1);
        $this->assertIsObject($tree);
        $treeData = $tree->subTree();
        $this->assertIsArray($treeData);
        $this->assertNotEmpty($treeData);
        $current = current($treeData);
        $this->assertFalse($current['hasChildren']);
    }

    public function testParentTree()
    {
        $result = [
            'cid'=>1,
            'pid'=>0,
            'name'=>'user center',
            'children' => [
                'cid'=>3,
                'pid'=>1,
                'name'=>'secret',
                'children' => [
                    'cid'=>7, 'pid'=>3, 'name'=>'change password', 'children'=>[],
                ],
            ],
        ];
        $tree = new Tree($this->data, $this->options+['has'=>'']);
        $treeData = $tree->parentTree(7);
        $this->assertEquals($result, $treeData);
    }

    public function testFlatTree()
    {
        $tree = new Tree($this->data, $this->options);
        $treeData = $tree->subTree();
        $flat = $tree->flatTree($treeData);
        $this->assertNotEmpty($flat);
        $cid7 = $flat[3];
        $this->assertEquals(7, $cid7['cid']);
        $result = [
            'cid'=>1,
            'pid'=>0,
            'name'=>'user center',
            'children' => [
                'cid'=>3,
                'pid'=>1,
                'name'=>'secret',
                'children' => [
                    'cid'=>7, 'pid'=>3, 'name'=>'change password', 'children'=>[],
                ],
            ],
        ];
        $tree->setOptions(['pad'=>'-', 'pad_name'=>'name']);
        $flat = $tree->flatTree($result);
        $this->assertEquals(3, count($flat));
    }

    public function testTreeFilter()
    {
        $data = [
            ['id'=>1, 'pid'=>0, 'name'=>'a'], 
            ['id'=>2, 'pid'=>0, 'name'=>'b'],
            ['id'=>3, 'pid'=>1, 'name'=>'c'],
            ['id'=>4, 'pid'=>3, 'name'=>'d'],
        ];
        $tree = new Tree($data, ['has'=>'']);
        $treeData = $tree->subTree();
        $treeData = $tree->filterTree($treeData, function($item){
            if (empty($item['children'])) {
                unset($item['children']);
            }
            return $item;
        });
        $result = [
            ['id'=>1, 'pid'=>0, 'name'=>'a', 'children'=>[
                ['id'=>3, 'pid'=>1, 'name'=>'c', 'children'=>[
                    ['id'=>4, 'pid'=>3, 'name'=>'d']
                ]]
            ]],
            ['id'=>2, 'pid'=>0, 'name'=>'b']
        ];
        $this->assertEquals($result, $treeData);
    }
}