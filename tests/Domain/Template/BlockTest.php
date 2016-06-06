<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Template;

use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;
use Proximum\Vimeet\Domain\Template\Object\EditableText;

class BlockTest extends \PHPUnit_Framework_TestCase
{
    public function testGetObjects()
    {
        $object1 = new EditableText('editable-text', ['foobar' => 'foobar'], 'fr', 'fr');
        $object2 = new EditableText('editable-text', ['barfoo' => 'barfoo'], 'fr', 'fr');

        $block = new Block('12', []);
        $block->addChild(0, 'object-1', $object1);
        $block->addChild(0, 'object-2', $object2);

        $this->assertEquals(['object-1' => $object1, 'object-2' => $object2], $block->getObjects());
    }

    public function testGetObject()
    {
        $object1 = new EditableText('editable-text', ['foobar' => 'foobar'], 'fr', 'fr');
        $object2 = new EditableText('editable-text', ['barfoo' => 'barfoo'], 'fr', 'fr');

        $block = new Block('12', []);
        $block->addChild(0, 'object-1', $object1);
        $block->addChild(0, 'object-2', $object2);

        $this->assertEquals($object1, $block->getObject('object-1'));
        $this->assertEquals($object2, $block->getObject('object-2'));
    }

    public function testGetObjectThrowsObjectNotFoundException()
    {
        $this->expectException(ObjectNotFoundException::class);

        $object1 = new EditableText('editable-text', ['foobar' => 'foobar'], 'fr', 'fr');
        $object2 = new EditableText('editable-text', ['barfoo' => 'barfoo'], 'fr', 'fr');

        $block = new Block('12', []);
        $block->addChild(0, 'object-1', $object1);
        $block->addChild(0, 'object-2', $object2);

        $block->getObject('object-3');
    }
}
