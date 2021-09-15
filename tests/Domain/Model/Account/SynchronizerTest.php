<?php

namespace Proximum\Vimeet\Tests\Domain\Model\Account;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class SynchronizerTest extends TestCase
{
    public function testGet()
    {
        $locale  = 'fr';
        $user    = new User('email@email.com', '__password__', '__salt__', $locale);
        $account = new User\Account();
        $account->setFirstName('jean');
        $account->setLastName('Dupond');
        $account->setPhone('Foo');
        $account->setMobile('Bar');
        $user->setAccount($account);

        $templateData = new TemplateData('root', [], 'fr', 'fr');
        $block = new Block('12', [], 'fr', 'fr');
        $text  = new TemplateObject\Text('69b3cde1', 'text', [], $locale, $locale);
        $editableText1 = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_firstname'],
        ], $locale, $locale);
        $editableText2 = new TemplateObject\EditableText('69b3cde2', 'editable-text', [
            'tags' => ['participant_lastname'],
        ], $locale, $locale);
        $telephone1    = new TemplateObject\Telephone('69b3cde1', 'telephone', [
            'tags' => ['participant_phone'],
        ], $locale, $locale);
        $telephone2    = new TemplateObject\Telephone('69b3cde2', 'telephone', [
            'tags' => ['participant_mobile'],
        ], $locale, $locale);

        $block->addChild(1, 'dded0597', $text);
        $block->addChild(1, '541f84d4', $editableText1);
        $block->addChild(1, '838197c7', $editableText2);
        $block->addChild(1, '1efb9cbb', $telephone1);
        $block->addChild(1, '3b759fbb', $telephone2);
        $templateData->addChild(0, '811f6edf', $block);

        // Mock
        $userRepository = $this->prophesize(UserRepositoryInterface::class);

        // Expected
        $expectedTemplateData = new TemplateData('root', [], 'fr', 'fr');
        $expectedBlock = new Block('12', [], 'fr', 'fr');
        $expectedText  = new TemplateObject\Text('69b3cde1', 'text', [], $locale, $locale);
        $expectedEditableText1 = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_firstname'],
        ], $locale, $locale);
        $expectedEditableText1->setContentValue('Jean');
        $expectedEditableText2 = new TemplateObject\EditableText('69b3cde2', 'editable-text', [
            'tags' => ['participant_lastname'],
        ], $locale, $locale);
        $expectedEditableText2->setContentValue('DUPOND');
        $expectedTelephone1    = new TemplateObject\Telephone('69b3cde1', 'telephone', [
            'tags' => ['participant_phone'],
        ], $locale, $locale);
        $expectedTelephone1->setContentValue('Foo');
        $expectedTelephone2    = new TemplateObject\Telephone('69b3cde2', 'telephone', [
            'tags' => ['participant_mobile'],
        ], $locale, $locale);
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
        $locale = 'fr';

        $user = new User('email@email.com', '__password__', '__salt__', $locale);
        $account = new User\Account();
        $user->setAccount($account);

        $templateData = new TemplateData('root', [], 'fr', 'fr');
        $block = new Block('12', [], 'fr', 'fr');
        $text  = new TemplateObject\Text('69b3cde1', 'text', [], $locale, $locale);
        $editableText1 = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_firstname'],
        ], $locale, $locale);
        $editableText1->setContentValue('Test');
        $editableText2 = new TemplateObject\EditableText('69b3cde2', 'editable-text', [
            'tags' => ['participant_lastname'],
        ], $locale, $locale);
        $editableText2->setContentValue('Truc');
        $telephone1    = new TemplateObject\Telephone('69b3cde1', 'telephone', [
            'tags' => ['participant_phone'],
        ], $locale, $locale);
        $telephone1->setContentValue('Foo');
        $telephone2    = new TemplateObject\Telephone('69b3cde2', 'telephone', [
            'tags' => ['participant_mobile'],
        ], $locale, $locale);
        $telephone2->setContentValue('Bar');

        $block->addChild(1, 'dded0597', $text);
        $block->addChild(1, '541f84d4', $editableText1);
        $block->addChild(1, '838197c7', $editableText2);
        $block->addChild(1, '1efb9cbb', $telephone1);
        $block->addChild(1, '3b759fbb', $telephone2);
        $templateData->addChild(0, '811f6edf', $block);

        // Expected
        $expectedUser = new User('email@email.com', '__password__', '__salt__', $locale);
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
