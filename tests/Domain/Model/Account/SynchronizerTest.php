<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Model\Account;

use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\Object;
use Proximum\Vimeet\Domain\Template\TemplateData;

class SynchronizerTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $user = new User('email@email.com', '__password__', '__salt__', 'fr');
        $account = new User\Account();
        $account->setFirstName('Test');
        $account->setLastName('Truc');
        $account->setPhone('Foo');
        $account->setMobile('Bar');
        $user->setAccount($account);

        $templateData = new TemplateData('root', []);
        $block = new Block('12', []);
        $text  = new Object\Text('text', [], 'fr', 'fr');
        $editableText1 = new Object\EditableText('editable-text', [
            'tags' => ['participant_firstname'],
        ], 'fr', 'fr');
        $editableText2 = new Object\EditableText('editable-text', [
            'tags' => ['participant_lastname'],
        ], 'fr', 'fr');
        $telephone1    = new Object\Telephone('telephone', [
            'tags' => ['participant_phone'],
        ], 'fr', 'fr');
        $telephone2    = new Object\Telephone('telephone', [
            'tags' => ['participant_mobile'],
        ], 'fr', 'fr');

        $block->addChild(1, 'dded0597', $text);
        $block->addChild(1, '541f84d4', $editableText1);
        $block->addChild(1, '838197c7', $editableText2);
        $block->addChild(1, '1efb9cbb', $telephone1);
        $block->addChild(1, '3b759fbb', $telephone2);
        $templateData->addChild(0, '811f6edf', $block);

        // Mock
        $userRepository = $this->prophesize(UserRepositoryInterface::class);

        // Expected
        $expectedTemplateData = new TemplateData('root', []);
        $expectedBlock = new Block('12', []);
        $expectedText  = new Object\Text('text', [], 'fr', 'fr');
        $expectedEditableText1 = new Object\EditableText('editable-text', [
            'tags' => ['participant_firstname'],
        ], 'fr', 'fr');
        $expectedEditableText1->setContentValue('Test');
        $expectedEditableText2 = new Object\EditableText('editable-text', [
            'tags' => ['participant_lastname'],
        ], 'fr', 'fr');
        $expectedEditableText2->setContentValue('Truc');
        $expectedTelephone1    = new Object\Telephone('telephone', [
            'tags' => ['participant_phone'],
        ], 'fr', 'fr');
        $expectedTelephone1->setContentValue('Foo');
        $expectedTelephone2    = new Object\Telephone('telephone', [
            'tags' => ['participant_mobile'],
        ], 'fr', 'fr');
        $expectedTelephone2->setContentValue('Bar');

        $expectedBlock->addChild(1, 'dded0597', $expectedText);
        $expectedBlock->addChild(1, '541f84d4', $expectedEditableText1);
        $expectedBlock->addChild(1, '838197c7', $expectedEditableText2);
        $expectedBlock->addChild(1, '1efb9cbb', $expectedTelephone1);
        $expectedBlock->addChild(1, '3b759fbb', $expectedTelephone2);
        $expectedTemplateData->addChild(0, '811f6edf', $expectedBlock);

        // Test Synchronizer
        $accountSynchronizer = new Synchronizer($userRepository->reveal());
        $accountSynchronizer->get($templateData, $user);

        $this->assertEquals($expectedTemplateData, $templateData);
    }

    public function testSet()
    {
        $user = new User('email@email.com', '__password__', '__salt__', 'fr');
        $account = new User\Account();
        $user->setAccount($account);

        $templateData = new TemplateData('root', []);
        $block = new Block('12', []);
        $text  = new Object\Text('text', [], 'fr', 'fr');
        $editableText1 = new Object\EditableText('editable-text', [
            'tags' => ['participant_firstname'],
        ], 'fr', 'fr');
        $editableText1->setContentValue('Test');
        $editableText2 = new Object\EditableText('editable-text', [
            'tags' => ['participant_lastname'],
        ], 'fr', 'fr');
        $editableText2->setContentValue('Truc');
        $telephone1    = new Object\Telephone('telephone', [
            'tags' => ['participant_phone'],
        ], 'fr', 'fr');
        $telephone1->setContentValue('Foo');
        $telephone2    = new Object\Telephone('telephone', [
            'tags' => ['participant_mobile'],
        ], 'fr', 'fr');
        $telephone2->setContentValue('Bar');

        $block->addChild(1, 'dded0597', $text);
        $block->addChild(1, '541f84d4', $editableText1);
        $block->addChild(1, '838197c7', $editableText2);
        $block->addChild(1, '1efb9cbb', $telephone1);
        $block->addChild(1, '3b759fbb', $telephone2);
        $templateData->addChild(0, '811f6edf', $block);

        // Expected
        $expectedUser = new User('email@email.com', '__password__', '__salt__', 'fr');
        $account2 = new User\Account();
        $account2->setFirstName('Test');
        $account2->setLastName('Truc');
        $account2->setPhone('Foo');
        $account2->setMobile('Bar');
        $expectedUser->setAccount($account2);

        // Mock
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->set($expectedUser)->shouldBeCalled();

        // Test Synchronizer
        $accountSynchronizer = new Synchronizer($userRepository->reveal());
        $accountSynchronizer->set($templateData, $user);

    }
}
