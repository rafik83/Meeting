<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareActivateAccountMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareChangeNewMailAccountMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareChangeOldMailAccountMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareMeetingFollowUpMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareOrderConfirmedMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareParticipantAddedMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PreparePreRegisterMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareRegisterAccountMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareSheetChangeTypeMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareTransactionConfirmMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareUserCompleteProfileMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareVersionDiffChangedMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\PrepareHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareChangeNewMailAccountView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareChangeOldMailAccountView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserRegisteredMailView;
use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ChangeNewMailAddressMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ChangeOldMailAddressMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\RegisterAccountMail;

class PrepareHandlerTest extends TestCase
{
    private ObjectProphecy $prepareRegisterAccountMail,
        $prepareActivateAccountMail,
        $prepareParticipantAddedMail,
        $preparePreRegisterMail,
        $prepareUserCompleteProfileMail,
        $prepareTransactionTotalMail,
        $prepareOrderConfirmedMail,
        $prepareVersionDiffChangedMail,
        $prepareSheetChangeTypeMail,
        $user,
        $event,
        $prepareChangeOldMailAccountMail,
        $prepareChangeNewMailAccountMail,
        $changeMailToken,
        $prepareMeetingFollowUpMail
    ;

    private string $locale;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
        $this->locale = 'fr';
        $this->prepareRegisterAccountMail = $this->prophesize(PrepareRegisterAccountMail::class);
        $this->prepareActivateAccountMail = $this->prophesize(PrepareActivateAccountMail::class);
        $this->prepareParticipantAddedMail = $this->prophesize(PrepareParticipantAddedMail::class);
        $this->preparePreRegisterMail = $this->prophesize(PreparePreRegisterMail::class);
        $this->prepareUserCompleteProfileMail = $this->prophesize(PrepareUserCompleteProfileMail::class);
        $this->prepareTransactionTotalMail = $this->prophesize(PrepareTransactionConfirmMail::class);
        $this->prepareOrderConfirmedMail = $this->prophesize(PrepareOrderConfirmedMail::class);
        $this->prepareVersionDiffChangedMail = $this->prophesize(PrepareVersionDiffChangedMail::class);
        $this->prepareSheetChangeTypeMail = $this->prophesize(PrepareSheetChangeTypeMail::class);
        $this->prepareChangeOldMailAccountMail = $this->prophesize(PrepareChangeOldMailAccountMail::class);
        $this->prepareChangeNewMailAccountMail = $this->prophesize(PrepareChangeNewMailAccountMail::class);
        $this->prepareMeetingFollowUpMail = $this->prophesize(PrepareMeetingFollowUpMail::class);
        $this->changeMailToken = $this->prophesize(ChangeMailToken::class);
    }

    public function testPrepareRegisterAccountMail(): void
    {
        $mail = new PrepareUserRegisteredMailView(
            $this->event->reveal(),
            $this->user->reveal(),
            $this->locale
        );
        $preparedMail = $this->prophesize(RegisterAccountMail::class);

        $this->prepareRegisterAccountMail->prepare($mail)->shouldBeCalled()->willReturn($preparedMail->reveal());
        $this->prepareActivateAccountMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareParticipantAddedMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->preparePreRegisterMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareUserCompleteProfileMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareTransactionTotalMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareOrderConfirmedMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareVersionDiffChangedMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareSheetChangeTypeMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareChangeOldMailAccountMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareChangeNewMailAccountMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareMeetingFollowUpMail->prepare(Argument::any())->shouldNotBeCalled();

        $handler = new PrepareHandler(
            $this->prepareRegisterAccountMail->reveal(),
            $this->prepareActivateAccountMail->reveal(),
            $this->prepareParticipantAddedMail->reveal(),
            $this->preparePreRegisterMail->reveal(),
            $this->prepareUserCompleteProfileMail->reveal(),
            $this->prepareTransactionTotalMail->reveal(),
            $this->prepareOrderConfirmedMail->reveal(),
            $this->prepareVersionDiffChangedMail->reveal(),
            $this->prepareSheetChangeTypeMail->reveal(),
            $this->prepareChangeOldMailAccountMail->reveal(),
            $this->prepareChangeNewMailAccountMail->reveal(),
            $this->prepareMeetingFollowUpMail->reveal()
        );

        $result = $handler->handle($mail);

        $this->assertInstanceOf(RegisterAccountMail::class, $result);
    }

    public function testPrepareChangeOldAndNewMailAccountMail(): void
    {
        $oldMail = new PrepareChangeOldMailAccountView(
            $this->event->reveal(),
            $this->user->reveal(),
            $this->locale,
            $this->changeMailToken->reveal()
        );

        $newMail = new PrepareChangeNewMailAccountView(
            $this->event->reveal(),
            $this->user->reveal(),
            $this->locale,
            $this->changeMailToken->reveal()
        );

        $preparedOldMail = $this->prophesize(ChangeOldMailAddressMail::class);
        $preparedNewMail = $this->prophesize(ChangeNewMailAddressMail::class);

        $this->prepareRegisterAccountMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareActivateAccountMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareParticipantAddedMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->preparePreRegisterMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareUserCompleteProfileMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareTransactionTotalMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareOrderConfirmedMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareVersionDiffChangedMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareSheetChangeTypeMail->prepare(Argument::any())->shouldNotBeCalled();
        $this->prepareChangeOldMailAccountMail->prepare($oldMail)->shouldBeCalled()->willReturn($preparedOldMail->reveal());
        $this->prepareChangeNewMailAccountMail->prepare($newMail)->shouldBeCalled()->willReturn($preparedNewMail->reveal());
        $this->prepareMeetingFollowUpMail->prepare($newMail)->shouldNotBeCalled();

        $handler = new PrepareHandler(
            $this->prepareRegisterAccountMail->reveal(),
            $this->prepareActivateAccountMail->reveal(),
            $this->prepareParticipantAddedMail->reveal(),
            $this->preparePreRegisterMail->reveal(),
            $this->prepareUserCompleteProfileMail->reveal(),
            $this->prepareTransactionTotalMail->reveal(),
            $this->prepareOrderConfirmedMail->reveal(),
            $this->prepareVersionDiffChangedMail->reveal(),
            $this->prepareSheetChangeTypeMail->reveal(),
            $this->prepareChangeOldMailAccountMail->reveal(),
            $this->prepareChangeNewMailAccountMail->reveal(),
            $this->prepareMeetingFollowUpMail->reveal()
        );

        $oldResult = $handler->handle($oldMail);
        $newResult = $handler->handle($newMail);

        $this->assertInstanceOf(ChangeOldMailAddressMail::class, $oldResult);
        $this->assertInstanceOf(ChangeNewMailAddressMail::class, $newResult);
    }
}
