<?php

namespace Proximum\Vimeet\Tests\Domain\Template;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Template\TaggedInfoGuesser;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class ParticipantInfoGuesserTest extends TestCase
{
    /** @var TaggedInfoGuesser */
    private $taggedInfoGuesser;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var User */
    private $user;

    /** @var Event */
    private $event;

    /** @var Type */
    private $type;

    /** @var RegistrationTemplate */
    private $registrationTemplate;

    /** @var Sheet */
    private $sheet;

    /** @var Participant */
    private $participant;

    /** @var TemplateData */
    private $templateData;

    /** @var ObjectProphecy */
    private $locale;

    public function setUp()
    {
        $this->taggedInfoGuesser    = $this->prophesize(TaggedInfoGuesser::class);
        $this->templateDataFactory  = $this->prophesize(TemplateDataFactory::class);
        $this->user                 = UserFactory::create();
        $this->event                = EventFactory::createEvent();
        $this->type                 = new Type($this->event);
        $this->registrationTemplate = new RegistrationTemplate(
            'Registration template',
            [],
            ['fr'],
            'fr',
            new \DateTime('2017-12-01')
        );
        $this->sheet                = SheetFactory::create($this->event, $this->user, new \DateTime(), $this->type);
        $this->participant          = new Participant($this->sheet, $this->user, [], true, new \DateTime());
        $this->templateData         = new TemplateData($this->type, [], 'fr', 'fr');
        $this->locale               = 'fr';
    }

    public function testGuessParticipantLastName()
    {
        $this->type->setRegistrationTemplate($this->registrationTemplate);

        $this->taggedInfoGuesser
            ->guessFirst(
                $this->registrationTemplate,
                $this->participant->getData(),
                Tag::PARTICIPANT_LASTNAME,
                $this->locale
            )
            ->shouldBeCalled()
            ->willReturn('DUPOND');

        $guesser = new ParticipantInfoGuesser($this->taggedInfoGuesser->reveal(), $this->templateDataFactory->reveal());

        $this->assertEquals('DUPOND', $guesser->guessParticipantLastName($this->participant, $this->locale));
    }

    public function testGuessParticipantFirstName()
    {
        $this->type->setRegistrationTemplate($this->registrationTemplate);

        $this->taggedInfoGuesser
            ->guessFirst(
                $this->registrationTemplate,
                $this->participant->getData(),
                Tag::PARTICIPANT_FIRSTNAME,
                $this->locale
            )
            ->shouldBeCalled()
            ->willReturn('john');

        $guesser = new ParticipantInfoGuesser($this->taggedInfoGuesser->reveal(), $this->templateDataFactory->reveal());

        $this->assertEquals('John', $guesser->guessParticipantFirstName($this->participant, $this->locale));
    }

    public function testGuessParticipantInfos()
    {
        $this->type->setRegistrationTemplate($this->registrationTemplate);
        $tags = Tag::getParticipantTags();
        $this->templateDataFactory
            ->createRegistrationFromParticipant($this->participant, $this->locale)
            ->shouldBeCalled()
            ->willReturn($this->templateData);

        foreach ($tags as $tag) {
            $this->taggedInfoGuesser
                ->guessFirstFromTemplateData(
                    $this->templateData,
                    $tag
                )
                ->shouldBeCalled()
                ->willReturn('foobar_' . $tag);
        }

        $guesser = new ParticipantInfoGuesser($this->taggedInfoGuesser->reveal(), $this->templateDataFactory->reveal());

        $expected = [
            'participant_firstname' => 'foobar_participant_firstname',
            'participant_lastname'  => 'foobar_participant_lastname',
            'participant_phone'     => 'foobar_participant_phone',
            'participant_mobile'    => 'foobar_participant_mobile',
            'participant_position'  => 'foobar_participant_position',
            'participant_avatar'    => 'foobar_participant_avatar',
            'participant_address'   => 'foobar_participant_address',
            'participant_zipcode'   => 'foobar_participant_zipcode',
            'participant_city'      => 'foobar_participant_city',
            'participant_country'   => 'foobar_participant_country',
            'participant_website'   => 'foobar_participant_website',
            'participant_gender'    => 'foobar_participant_gender',
            'participant_generic_tag_1' => 'foobar_participant_generic_tag_1',
            'participant_generic_tag_2' => 'foobar_participant_generic_tag_2',
            'participant_generic_tag_3' => 'foobar_participant_generic_tag_3',
            'participant_generic_tag_4' => 'foobar_participant_generic_tag_4',
            'participant_generic_tag_5' => 'foobar_participant_generic_tag_5',
            'participant_generic_tag_6' => 'foobar_participant_generic_tag_6',
            'participant_generic_tag_7' => 'foobar_participant_generic_tag_7',
            'participant_generic_tag_8' => 'foobar_participant_generic_tag_8',
            'participant_generic_tag_9' => 'foobar_participant_generic_tag_9',
            'participant_generic_tag_10' => 'foobar_participant_generic_tag_10',
            'participant_generic_tag_11' => 'foobar_participant_generic_tag_11',
            'participant_generic_tag_12' => 'foobar_participant_generic_tag_12',
            'participant_generic_tag_13' => 'foobar_participant_generic_tag_13',
            'participant_generic_tag_14' => 'foobar_participant_generic_tag_14',
            'participant_generic_tag_15' => 'foobar_participant_generic_tag_15',
            'participant_generic_tag_16' => 'foobar_participant_generic_tag_16',
            'participant_generic_tag_17' => 'foobar_participant_generic_tag_17',
            'participant_generic_tag_18' => 'foobar_participant_generic_tag_18',
            'participant_generic_tag_19' => 'foobar_participant_generic_tag_19',
            'participant_generic_tag_20' => 'foobar_participant_generic_tag_20',
            'participant_generic_tag_21' => 'foobar_participant_generic_tag_21',
            'participant_generic_tag_22' => 'foobar_participant_generic_tag_22',
            'participant_generic_tag_23' => 'foobar_participant_generic_tag_23',
            'participant_generic_tag_24' => 'foobar_participant_generic_tag_24',
            'participant_generic_tag_25' => 'foobar_participant_generic_tag_25',
            'participant_generic_tag_26' => 'foobar_participant_generic_tag_26',
            'participant_generic_tag_27' => 'foobar_participant_generic_tag_27',
            'participant_generic_tag_28' => 'foobar_participant_generic_tag_28',
            'participant_generic_tag_29' => 'foobar_participant_generic_tag_29',
            'participant_generic_tag_30' => 'foobar_participant_generic_tag_30',
            'participant_generic_tag_31' => 'foobar_participant_generic_tag_31',
            'participant_generic_tag_32' => 'foobar_participant_generic_tag_32',
            'participant_generic_tag_33' => 'foobar_participant_generic_tag_33',
            'participant_generic_tag_34' => 'foobar_participant_generic_tag_34',
            'participant_generic_tag_35' => 'foobar_participant_generic_tag_35',
            'participant_generic_tag_36' => 'foobar_participant_generic_tag_36',
            'participant_generic_tag_37' => 'foobar_participant_generic_tag_37',
            'participant_generic_tag_38' => 'foobar_participant_generic_tag_38',
            'participant_generic_tag_39' => 'foobar_participant_generic_tag_39',
            'participant_generic_tag_40' => 'foobar_participant_generic_tag_40',
            'participant_generic_tag_41' => 'foobar_participant_generic_tag_41',
            'participant_generic_tag_42' => 'foobar_participant_generic_tag_42',
            'participant_generic_tag_43' => 'foobar_participant_generic_tag_43',
            'participant_generic_tag_44' => 'foobar_participant_generic_tag_44',
            'participant_generic_tag_45' => 'foobar_participant_generic_tag_45',
            'participant_generic_tag_46' => 'foobar_participant_generic_tag_46',
            'participant_generic_tag_47' => 'foobar_participant_generic_tag_47',
            'participant_generic_tag_48' => 'foobar_participant_generic_tag_48',
            'participant_generic_tag_49' => 'foobar_participant_generic_tag_49',
            'participant_generic_tag_50' => 'foobar_participant_generic_tag_50',
            'participant_generic_tag_51' => 'foobar_participant_generic_tag_51',
            'participant_generic_tag_52' => 'foobar_participant_generic_tag_52',
            'participant_generic_tag_53' => 'foobar_participant_generic_tag_53',
            'participant_generic_tag_54' => 'foobar_participant_generic_tag_54',
            'participant_generic_tag_55' => 'foobar_participant_generic_tag_55',
            'participant_generic_tag_56' => 'foobar_participant_generic_tag_56',
            'participant_generic_tag_57' => 'foobar_participant_generic_tag_57',
            'participant_generic_tag_58' => 'foobar_participant_generic_tag_58',
            'participant_generic_tag_59' => 'foobar_participant_generic_tag_59',
            'participant_generic_tag_60' => 'foobar_participant_generic_tag_60',
            'participant_generic_tag_61' => 'foobar_participant_generic_tag_61',
            'participant_generic_tag_62' => 'foobar_participant_generic_tag_62',
            'participant_generic_tag_63' => 'foobar_participant_generic_tag_63',
            'participant_generic_tag_64' => 'foobar_participant_generic_tag_64',
            'participant_generic_tag_65' => 'foobar_participant_generic_tag_65',
            'participant_generic_tag_66' => 'foobar_participant_generic_tag_66',
            'participant_generic_tag_67' => 'foobar_participant_generic_tag_67',
            'participant_generic_tag_68' => 'foobar_participant_generic_tag_68',
            'participant_generic_tag_69' => 'foobar_participant_generic_tag_69',
            'participant_generic_tag_70' => 'foobar_participant_generic_tag_70',
            'participant_generic_tag_71' => 'foobar_participant_generic_tag_71',
            'participant_generic_tag_72' => 'foobar_participant_generic_tag_72',
            'participant_generic_tag_73' => 'foobar_participant_generic_tag_73',
            'participant_generic_tag_74' => 'foobar_participant_generic_tag_74',
            'participant_generic_tag_75' => 'foobar_participant_generic_tag_75',
            'participant_generic_tag_76' => 'foobar_participant_generic_tag_76',
            'participant_generic_tag_77' => 'foobar_participant_generic_tag_77',
            'participant_generic_tag_78' => 'foobar_participant_generic_tag_78',
            'participant_generic_tag_79' => 'foobar_participant_generic_tag_79',
            'participant_generic_tag_80' => 'foobar_participant_generic_tag_80',
            'participant_generic_tag_81' => 'foobar_participant_generic_tag_81',
            'participant_generic_tag_82' => 'foobar_participant_generic_tag_82',
            'participant_generic_tag_83' => 'foobar_participant_generic_tag_83',
            'participant_generic_tag_84' => 'foobar_participant_generic_tag_84',
            'participant_generic_tag_85' => 'foobar_participant_generic_tag_85',
            'participant_generic_tag_86' => 'foobar_participant_generic_tag_86',
            'participant_generic_tag_87' => 'foobar_participant_generic_tag_87',
            'participant_generic_tag_88' => 'foobar_participant_generic_tag_88',
            'participant_generic_tag_89' => 'foobar_participant_generic_tag_89',
            'participant_generic_tag_90' => 'foobar_participant_generic_tag_90',
            'participant_generic_tag_91' => 'foobar_participant_generic_tag_91',
            'participant_generic_tag_92' => 'foobar_participant_generic_tag_92',
            'participant_generic_tag_93' => 'foobar_participant_generic_tag_93',
            'participant_generic_tag_94' => 'foobar_participant_generic_tag_94',
            'participant_generic_tag_95' => 'foobar_participant_generic_tag_95',
            'participant_generic_tag_96' => 'foobar_participant_generic_tag_96',
            'participant_generic_tag_97' => 'foobar_participant_generic_tag_97',
            'participant_generic_tag_98' => 'foobar_participant_generic_tag_98',
            'participant_generic_tag_99' => 'foobar_participant_generic_tag_99',
            'participant_arrival_date'   => 'foobar_participant_arrival_date',
            'participant_departure_date' => 'foobar_participant_departure_date',
        ];

        $this->assertEquals($expected, $guesser->guessParticipantInfos($this->participant, $this->locale));
    }

    public function testGuessParticipantInfosWithTemplateData()
    {
        $this->type->setRegistrationTemplate($this->registrationTemplate);
        $tags = Tag::getParticipantTags();

        foreach ($tags as $tag) {
            $this->taggedInfoGuesser
                ->guessFirstFromTemplateData(
                    $this->templateData,
                    $tag
                )
                ->shouldBeCalled()
                ->willReturn('foobar_' . $tag);
        }

        $guesser = new ParticipantInfoGuesser($this->taggedInfoGuesser->reveal(), $this->templateDataFactory->reveal());

        $expected = [
            'participant_firstname' => 'foobar_participant_firstname',
            'participant_lastname'  => 'foobar_participant_lastname',
            'participant_phone'     => 'foobar_participant_phone',
            'participant_mobile'    => 'foobar_participant_mobile',
            'participant_position'  => 'foobar_participant_position',
            'participant_avatar'    => 'foobar_participant_avatar',
            'participant_address'   => 'foobar_participant_address',
            'participant_zipcode'   => 'foobar_participant_zipcode',
            'participant_city'      => 'foobar_participant_city',
            'participant_country'   => 'foobar_participant_country',
            'participant_website'   => 'foobar_participant_website',
            'participant_gender'    => 'foobar_participant_gender',
            'participant_generic_tag_1' => 'foobar_participant_generic_tag_1',
            'participant_generic_tag_2' => 'foobar_participant_generic_tag_2',
            'participant_generic_tag_3' => 'foobar_participant_generic_tag_3',
            'participant_generic_tag_4' => 'foobar_participant_generic_tag_4',
            'participant_generic_tag_5' => 'foobar_participant_generic_tag_5',
            'participant_generic_tag_6' => 'foobar_participant_generic_tag_6',
            'participant_generic_tag_7' => 'foobar_participant_generic_tag_7',
            'participant_generic_tag_8' => 'foobar_participant_generic_tag_8',
            'participant_generic_tag_9' => 'foobar_participant_generic_tag_9',
            'participant_generic_tag_10' => 'foobar_participant_generic_tag_10',
            'participant_generic_tag_11' => 'foobar_participant_generic_tag_11',
            'participant_generic_tag_12' => 'foobar_participant_generic_tag_12',
            'participant_generic_tag_13' => 'foobar_participant_generic_tag_13',
            'participant_generic_tag_14' => 'foobar_participant_generic_tag_14',
            'participant_generic_tag_15' => 'foobar_participant_generic_tag_15',
            'participant_generic_tag_16' => 'foobar_participant_generic_tag_16',
            'participant_generic_tag_17' => 'foobar_participant_generic_tag_17',
            'participant_generic_tag_18' => 'foobar_participant_generic_tag_18',
            'participant_generic_tag_19' => 'foobar_participant_generic_tag_19',
            'participant_generic_tag_20' => 'foobar_participant_generic_tag_20',
            'participant_generic_tag_21' => 'foobar_participant_generic_tag_21',
            'participant_generic_tag_22' => 'foobar_participant_generic_tag_22',
            'participant_generic_tag_23' => 'foobar_participant_generic_tag_23',
            'participant_generic_tag_24' => 'foobar_participant_generic_tag_24',
            'participant_generic_tag_25' => 'foobar_participant_generic_tag_25',
            'participant_generic_tag_26' => 'foobar_participant_generic_tag_26',
            'participant_generic_tag_27' => 'foobar_participant_generic_tag_27',
            'participant_generic_tag_28' => 'foobar_participant_generic_tag_28',
            'participant_generic_tag_29' => 'foobar_participant_generic_tag_29',
            'participant_generic_tag_30' => 'foobar_participant_generic_tag_30',
            'participant_generic_tag_31' => 'foobar_participant_generic_tag_31',
            'participant_generic_tag_32' => 'foobar_participant_generic_tag_32',
            'participant_generic_tag_33' => 'foobar_participant_generic_tag_33',
            'participant_generic_tag_34' => 'foobar_participant_generic_tag_34',
            'participant_generic_tag_35' => 'foobar_participant_generic_tag_35',
            'participant_generic_tag_36' => 'foobar_participant_generic_tag_36',
            'participant_generic_tag_37' => 'foobar_participant_generic_tag_37',
            'participant_generic_tag_38' => 'foobar_participant_generic_tag_38',
            'participant_generic_tag_39' => 'foobar_participant_generic_tag_39',
            'participant_generic_tag_40' => 'foobar_participant_generic_tag_40',
            'participant_generic_tag_41' => 'foobar_participant_generic_tag_41',
            'participant_generic_tag_42' => 'foobar_participant_generic_tag_42',
            'participant_generic_tag_43' => 'foobar_participant_generic_tag_43',
            'participant_generic_tag_44' => 'foobar_participant_generic_tag_44',
            'participant_generic_tag_45' => 'foobar_participant_generic_tag_45',
            'participant_generic_tag_46' => 'foobar_participant_generic_tag_46',
            'participant_generic_tag_47' => 'foobar_participant_generic_tag_47',
            'participant_generic_tag_48' => 'foobar_participant_generic_tag_48',
            'participant_generic_tag_49' => 'foobar_participant_generic_tag_49',
            'participant_generic_tag_50' => 'foobar_participant_generic_tag_50',
            'participant_generic_tag_51' => 'foobar_participant_generic_tag_51',
            'participant_generic_tag_52' => 'foobar_participant_generic_tag_52',
            'participant_generic_tag_53' => 'foobar_participant_generic_tag_53',
            'participant_generic_tag_54' => 'foobar_participant_generic_tag_54',
            'participant_generic_tag_55' => 'foobar_participant_generic_tag_55',
            'participant_generic_tag_56' => 'foobar_participant_generic_tag_56',
            'participant_generic_tag_57' => 'foobar_participant_generic_tag_57',
            'participant_generic_tag_58' => 'foobar_participant_generic_tag_58',
            'participant_generic_tag_59' => 'foobar_participant_generic_tag_59',
            'participant_generic_tag_60' => 'foobar_participant_generic_tag_60',
            'participant_generic_tag_61' => 'foobar_participant_generic_tag_61',
            'participant_generic_tag_62' => 'foobar_participant_generic_tag_62',
            'participant_generic_tag_63' => 'foobar_participant_generic_tag_63',
            'participant_generic_tag_64' => 'foobar_participant_generic_tag_64',
            'participant_generic_tag_65' => 'foobar_participant_generic_tag_65',
            'participant_generic_tag_66' => 'foobar_participant_generic_tag_66',
            'participant_generic_tag_67' => 'foobar_participant_generic_tag_67',
            'participant_generic_tag_68' => 'foobar_participant_generic_tag_68',
            'participant_generic_tag_69' => 'foobar_participant_generic_tag_69',
            'participant_generic_tag_70' => 'foobar_participant_generic_tag_70',
            'participant_generic_tag_71' => 'foobar_participant_generic_tag_71',
            'participant_generic_tag_72' => 'foobar_participant_generic_tag_72',
            'participant_generic_tag_73' => 'foobar_participant_generic_tag_73',
            'participant_generic_tag_74' => 'foobar_participant_generic_tag_74',
            'participant_generic_tag_75' => 'foobar_participant_generic_tag_75',
            'participant_generic_tag_76' => 'foobar_participant_generic_tag_76',
            'participant_generic_tag_77' => 'foobar_participant_generic_tag_77',
            'participant_generic_tag_78' => 'foobar_participant_generic_tag_78',
            'participant_generic_tag_79' => 'foobar_participant_generic_tag_79',
            'participant_generic_tag_80' => 'foobar_participant_generic_tag_80',
            'participant_generic_tag_81' => 'foobar_participant_generic_tag_81',
            'participant_generic_tag_82' => 'foobar_participant_generic_tag_82',
            'participant_generic_tag_83' => 'foobar_participant_generic_tag_83',
            'participant_generic_tag_84' => 'foobar_participant_generic_tag_84',
            'participant_generic_tag_85' => 'foobar_participant_generic_tag_85',
            'participant_generic_tag_86' => 'foobar_participant_generic_tag_86',
            'participant_generic_tag_87' => 'foobar_participant_generic_tag_87',
            'participant_generic_tag_88' => 'foobar_participant_generic_tag_88',
            'participant_generic_tag_89' => 'foobar_participant_generic_tag_89',
            'participant_generic_tag_90' => 'foobar_participant_generic_tag_90',
            'participant_generic_tag_91' => 'foobar_participant_generic_tag_91',
            'participant_generic_tag_92' => 'foobar_participant_generic_tag_92',
            'participant_generic_tag_93' => 'foobar_participant_generic_tag_93',
            'participant_generic_tag_94' => 'foobar_participant_generic_tag_94',
            'participant_generic_tag_95' => 'foobar_participant_generic_tag_95',
            'participant_generic_tag_96' => 'foobar_participant_generic_tag_96',
            'participant_generic_tag_97' => 'foobar_participant_generic_tag_97',
            'participant_generic_tag_98' => 'foobar_participant_generic_tag_98',
            'participant_generic_tag_99' => 'foobar_participant_generic_tag_99',
            'participant_arrival_date'   => 'foobar_participant_arrival_date',
            'participant_departure_date' => 'foobar_participant_departure_date',
        ];

        $this->assertEquals($expected, $guesser->guessParticipantInfosWithTemplateData($this->templateData));
    }

    public function testGuessParticipantPhone()
    {
        $this->type->setRegistrationTemplate($this->registrationTemplate);

        $this->taggedInfoGuesser
            ->guessFirst(
                $this->registrationTemplate,
                $this->participant->getData(),
                Tag::PARTICIPANT_PHONE,
                $this->locale
            )
            ->shouldBeCalled()
            ->willReturn('0123456789');

        $guesser = new ParticipantInfoGuesser($this->taggedInfoGuesser->reveal(), $this->templateDataFactory->reveal());

        $this->assertEquals('0123456789', $guesser->guessParticipantPhone($this->participant, $this->locale));
    }

    public function testGuessParticipantInfoForMail()
    {
        $this->type->setRegistrationTemplate($this->registrationTemplate);

        $this->templateDataFactory
            ->createRegistrationFromParticipant($this->participant, $this->locale)
            ->shouldBeCalled()
            ->willReturn($this->templateData);

        $mailBuildedInfo = [];
        foreach ($this->templateData->getAllTaggedDatas() as $tag => $values) {
            $mailBuildedInfo[$tag] = (!empty($values)) ? reset($values) : '';
        }

        $guesser = new ParticipantInfoGuesser($this->taggedInfoGuesser->reveal(), $this->templateDataFactory->reveal());

        $this->assertEquals($mailBuildedInfo, $guesser->guessParticipantInfoForMail($this->participant, $this->locale));
    }

    public function testGuessParticipantPosition()
    {
        $this->type->setRegistrationTemplate($this->registrationTemplate);

        $this->templateDataFactory
            ->createRegistrationFromParticipant($this->participant, $this->locale)
            ->shouldBeCalled()
            ->willReturn($this->templateData);

        $expected = $this->templateData->getTaggedContentValue(Tag::PARTICIPANT_POSITION);

        $guesser = new ParticipantInfoGuesser($this->taggedInfoGuesser->reveal(), $this->templateDataFactory->reveal());

        $this->assertEquals($expected, $guesser->guessParticipantPosition($this->participant, $this->locale));
    }

    public function testGuessParticipantCompleteName()
    {
        $this->type->setRegistrationTemplate($this->registrationTemplate);
        $tags = Tag::getParticipantTags();
        $this->templateDataFactory
            ->createRegistrationFromParticipant($this->participant, $this->locale)
            ->shouldBeCalled()
            ->willReturn($this->templateData);

        foreach ($tags as $tag) {
            $this->taggedInfoGuesser
                ->guessFirstFromTemplateData(
                    $this->templateData,
                    $tag
                )
                ->shouldBeCalled()
                ->willReturn('foobar_' . $tag);
        }

        $guesser = new ParticipantInfoGuesser($this->taggedInfoGuesser->reveal(), $this->templateDataFactory->reveal());

        $expected = 'foobar_participant_firstname foobar_participant_lastname';

        $this->assertEquals($expected, $guesser->guessParticipantCompleteName($this->participant, $this->locale));
    }
}
