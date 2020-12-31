<?php

namespace Proximum\Vimeet\Tests\Domain\Account;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class SynchronizerTest extends TestCase
{
    public function testGet()
    {
        // Context
        $templateData = $this->prophesize(TemplateData::class);
        $user = $this->prophesize(User::class);
        $account = $this->prophesize(User\Account::class);
        $user->getAccount()->willReturn($account->reveal());
        $account->getGender()->willReturn('Man');
        $account->getFirstName()->willReturn('First Name');
        $account->getCompany()->willReturn('Sheet title');
        $account->getPhone()->willReturn('Participant Phone');
        $account->getMobile()->willReturn('Participant Mobile');
        $account->getPosition()->willReturn('Participant position');

        $object1 = $this->prophesize(EditableText::class);
        $object2 = $this->prophesize(EditableText::class);
        $object3 = $this->prophesize(EditableText::class);
        $object4 = $this->prophesize(EditableText::class);
        $object5 = $this->prophesize(EditableText::class);
        $object6 = $this->prophesize(Nomenclature::class);
        $objects = [
            $object1->reveal(),
            $object2->reveal(),
            $object3->reveal(),
            $object4->reveal(),
            $object5->reveal(),
            $object6->reveal(),
        ];
        $templateData->getEditableObjects()->willReturn($objects);
        $object1->getContentValue()->willReturn('abc');
        $object2->getContentValue()->willReturn('');
        $object3->getContentValue()->willReturn('');
        $object4->getContentValue()->willReturn('');
        $object5->getContentValue()->willReturn('');
        $object6->getContentValue()->willReturn('');
        $templateData->getLocale()->willReturn('fr');

        // Expected
        $object1->getTags()->shouldNotBeCalled();
        $object2->getTags()->shouldBeCalled()->willReturn([Tag::PARTICIPANT_GENDER]);
        $object3->getTags()->shouldBeCalled()->willReturn([Tag::PARTICIPANT_FIRSTNAME]);
        $object4->getTags()->shouldBeCalled()->willReturn([Tag::SHEET_TITLE]);
        $object5->getTags()->shouldBeCalled()->willReturn([Tag::PARTICIPANT_PHONE, Tag::PARTICIPANT_MOBILE]);
        $object6->getTags()->shouldBeCalled()->willReturn([Tag::PARTICIPANT_POSITION]);
        $object2->setContentValue('Man')->shouldBeCalled();
        $object3->setContentValue('First Name')->shouldBeCalled();
        $object4->setContentValue('Sheet title')->shouldBeCalled();
        $object5->setContentValue('Participant Phone')->shouldBeCalled();
        $object5->setContentValue('Participant Mobile')->shouldBeCalled();
        $object6->getKeyForLabel('Participant position', 'fr')->shouldBeCalled()->willReturn('key1');
        $object6->setContentValue('key1')->shouldBeCalled();

        // Mock
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->set($user->reveal())->shouldNotBeCalled();

        // Synchronizer
        $synchronizer = new Synchronizer($userRepository->reveal());
        $result = $synchronizer->get($templateData->reveal(), $user->reveal());

        $this->assertInstanceOf(TemplateData::class, $result);
    }

    public function testSet()
    {
        // Context
        $templateData = $this->prophesize(TemplateData::class);
        $user = $this->prophesize(User::class);
        $account = $this->prophesize(User\Account::class);
        $user->getAccount()->willReturn($account->reveal());

        $object  = $this->prophesize(EditableText::class);
        $object1 = $this->prophesize(EditableText::class);
        $object2 = $this->prophesize(EditableText::class);
        $object3 = $this->prophesize(EditableText::class);
        $object4 = $this->prophesize(EditableText::class);
        $object5 = $this->prophesize(EditableText::class);
        $object6 = $this->prophesize(Nomenclature::class);
        $objects = [
            $object->reveal(),
            $object1->reveal(),
            $object2->reveal(),
            $object3->reveal(),
            $object4->reveal(),
            $object5->reveal(),
            $object6->reveal(),
        ];
        $templateData->getEditableObjects()->willReturn($objects);
        $object->getContentValueLocalize()->willReturn('');
        $object1->getContentValueLocalize()->willReturn('Last Name');
        $object2->getContentValueLocalize()->willReturn('Woman');
        $object3->getContentValueLocalize()->willReturn('First Name');
        $object4->getContentValueLocalize()->willReturn('Sheet title');
        $object5->getContentValueLocalize()->willReturn('Participant Phone');
        $object6->getContentValueLocalize()->willReturn('key1');
        $templateData->getLocale()->willReturn('fr');

        //Expected
        $object->getTags()->shouldNotBeCalled();
        $object1->getTags()->shouldBeCalled()->willReturn([Tag::PARTICIPANT_LASTNAME]);
        $object2->getTags()->shouldBeCalled()->willReturn([Tag::PARTICIPANT_GENDER]);
        $object3->getTags()->shouldBeCalled()->willReturn([Tag::PARTICIPANT_FIRSTNAME]);
        $object4->getTags()->shouldBeCalled()->willReturn([Tag::SHEET_TITLE]);
        $object5->getTags()->shouldBeCalled()->willReturn([Tag::PARTICIPANT_PHONE, Tag::PARTICIPANT_MOBILE]);
        $object6->getTags()->shouldBeCalled()->willReturn([Tag::PARTICIPANT_POSITION]);
        $account->setGender('Woman')->shouldBeCalled();
        $account->setFirstName('First Name')->shouldBeCalled();
        $account->setLastName('Last Name')->shouldBeCalled();
        $account->setCompany('Sheet title')->shouldBeCalled();
        $account->setPhone('Participant Phone')->shouldBeCalled();
        $account->setMobile('Participant Phone')->shouldBeCalled();
        $account->setPosition('Participant position')->shouldBeCalled();
        $object6->getLabelForKey('key1', 'fr')->shouldBeCalled()->willReturn('Participant position');

        // Mock
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->set($user->reveal())->shouldBeCalled();

        // Synchronizer
        $synchronizer = new Synchronizer($userRepository->reveal());
        $synchronizer->set($templateData->reveal(), $user->reveal());
    }
}
