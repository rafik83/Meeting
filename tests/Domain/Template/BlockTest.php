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
use Proximum\Vimeet\Domain\Template\Object\EditableText;

class BlockTest extends \PHPUnit_Framework_TestCase
{
    public function testGetBlocks()
    {
        $block = new Block('12', []);

        $blockA = new Block('block-a', []);
        $blockA->addChild(0, 'block-aa', new Block('block-aa', []));
        $blockB = new Block('block-b', []);
        $blockB->addChild(0, 'block-ba', new Block('block-ba', []));
        $blockB->addChild(0, 'block-bb', new Block('block-bb', []));
        $blockB->addChild(1, 'block-bc', new Block('block-bc', []));
        $blockC = new Block('block-c', []);

        $block->addChild(0, 'block-a', $blockA);
        $block->addChild(0, 'object-1', new EditableText('editable-text', [], 'fr', 'fr'));
        $block->addChild(0, 'object-2', new EditableText('editable-text', [], 'fr', 'fr'));
        $block->addChild(0, 'block-b', $blockB);
        $block->addChild(1, 'block-c', $blockC);

        $this->assertEquals([$blockA, $blockB, $blockC], $block->getBlocks());
    }

    public function testGetBlock()
    {
        $block = new Block('12', []);

        $blockA = new Block('block-a', []);
        $blockA->addChild(0, 'block-aa', new Block('block-aa', []));
        $blockB = new Block('block-b', []);
        $blockB->addChild(0, 'block-ba', new Block('block-ba', []));
        $blockB->addChild(0, 'block-bb', new Block('block-bb', []));
        $blockB->addChild(1, 'block-bc', new Block('block-bc', []));
        $blockC = new Block('block-c', []);

        $block->addChild(0, 'block-a', $blockA);
        $block->addChild(0, 'object-1', new EditableText('editable-text', [], 'fr', 'fr'));
        $block->addChild(0, 'object-2', new EditableText('editable-text', [], 'fr', 'fr'));
        $block->addChild(0, 'block-b', $blockB);
        $block->addChild(1, 'block-c', $blockC);

        $this->assertEquals($blockA, $block->getBlock(1));
        $this->assertEquals($blockB, $block->getBlock(2));
        $this->assertEquals($blockC, $block->getBlock(3));
        $this->assertEquals(null, $block->getBlock(4));
    }

    public function testGetBlocksCount()
    {
        $block = new Block('12', []);

        $blockA = new Block('block-a', []);
        $blockA->addChild(0, 'block-aa', new Block('block-aa', []));
        $blockA->addChild(0, 'object-1', new EditableText('editable-text', [], 'fr', 'fr'));
        $blockA->addChild(0, 'object-2', new EditableText('editable-text', [], 'fr', 'fr'));

        $blockB = new Block('block-b', []);
        $blockB->addChild(0, 'block-ba', (new Block('block-ba', []))->addChild(0, 'object-3', new EditableText('editable-text', [], 'fr', 'fr')));
        $blockB->addChild(0, 'block-bb', (new Block('block-bb', []))->addChild(0, 'object-4', new EditableText('editable-text', [], 'fr', 'fr')));
        $blockB->addChild(1, 'block-bc', (new Block('block-bc', []))->addChild(0, 'object-5', new EditableText('editable-text', [], 'fr', 'fr')));

        $blockC = new Block('block-c', []);

        $block->addChild(0, 'block-a', $blockA);
        $block->addChild(0, 'object-6', new EditableText('editable-text', [], 'fr', 'fr'));
        $block->addChild(0, 'object-7', new EditableText('editable-text', [], 'fr', 'fr'));
        $block->addChild(0, 'block-b', $blockB);
        $block->addChild(1, 'block-c', $blockC);

        $this->assertEquals(2, $block->getBlocksCount());
    }

    public function testGetNextBlockPosition()
    {
        $block = new Block('12', []);

        $blockA = new Block('block-a', []);
        $blockA->addChild(0, 'block-aa', new Block('block-aa', []));
        $blockA->addChild(0, 'object-1', new EditableText('editable-text', [], 'fr', 'fr'));
        $blockA->addChild(0, 'object-2', new EditableText('editable-text', [], 'fr', 'fr'));

        $blockB = new Block('block-b', []);
        $blockB->addChild(0, 'block-ba', (new Block('block-ba', []))->addChild(0, 'object-3', new EditableText('editable-text', [], 'fr', 'fr')));
        $blockB->addChild(0, 'block-bb', (new Block('block-bb', []))->addChild(0, 'object-4', new EditableText('editable-text', [], 'fr', 'fr')));
        $blockB->addChild(1, 'block-bc', (new Block('block-bc', []))->addChild(0, 'object-5', new EditableText('editable-text', [], 'fr', 'fr')));

        $blockC = new Block('block-c', []);

        $block->addChild(0, 'block-a', $blockA);
        $block->addChild(0, 'object-6', new EditableText('editable-text', [], 'fr', 'fr'));
        $block->addChild(0, 'object-7', new EditableText('editable-text', [], 'fr', 'fr'));
        $block->addChild(0, 'block-b', $blockB);
        $block->addChild(1, 'block-c', $blockC);

        $this->assertEquals(2, $block->getNextBlockPosition(1));
        $this->assertEquals(null, $block->getNextBlockPosition(2));
    }
}
