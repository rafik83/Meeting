<?php

namespace Proximum\Vimeet\Tests\Application\Command\Type\PaymentConditions;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Type\PaymentConditions\Update;
use Proximum\Vimeet\Application\Command\Type\PaymentConditions\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Repository\Type\PaymentConditionsRepositoryInterface;

class UpdateHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $paymentConditionsRepository;

    /** @var ObjectProphecy */
    private $type;

    /** @var ObjectProphecy */
    private $event;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->type = $this->prophesize(Type::class);
        $this->type->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->paymentConditionsRepository = $this->prophesize(PaymentConditionsRepositoryInterface::class);
    }

    public function testHandleNoSpecificConditions()
    {
        $this->type->getPaymentConditions()->willReturn(null);

        $this->event->getLocales()->shouldBeCalled()->willReturn(['fr']);
        $this->event->getBankInfo('fr')->shouldBeCalled()->willReturn('info bancaire');
        $this->event->getBillingAddress('fr')->shouldBeCalled()->willReturn('adresse de facturation');
        $this->event->getPaymentCondition('fr')->shouldBeCalled()->willReturn('condition de paiement');
        $this->event->getPaymentFooter('fr')->shouldBeCalled()->willReturn('pied de page pour le paiement');

        $this->paymentConditionsRepository->remove(Argument::any())->shouldNotBeCalled();
        $this->paymentConditionsRepository->add(Argument::any())->shouldNotBeCalled();
        $this->paymentConditionsRepository->set(Argument::any())->shouldNotBeCalled();

        $command = new Update($this->type->reveal());
        $command->specificPaymentConditions = false;
        $handler = new UpdateHandler($this->paymentConditionsRepository->reveal());
        $handler->handle($command);
    }

    public function testHandleRemovePaymentConditions()
    {
        $paymentConditions = $this->prophesize(Type\PaymentConditions::class);
        $paymentConditions->isAllowDeposit()->willReturn(false);
        $paymentConditions->getDepositUntil()->willReturn(null);
        $paymentConditions->getMinimumForDeposit()->willReturn(null);
        $paymentConditions->getDeposit()->willReturn(null);
        $paymentConditions->getPaymentModes()->willReturn([]);
        $paymentConditions->getTranslations()->willReturn([]);
        $this->type->getPaymentConditions()->willReturn($paymentConditions->reveal());
        $this->event->getLocales()->shouldBeCalled()->willReturn(['fr']);
        $paymentConditions->getBankInfo('fr')->shouldBeCalled()->willReturn('info bancaire');
        $paymentConditions->getBillingAddress('fr')->shouldBeCalled()->willReturn('adresse de facturation');
        $paymentConditions->getPaymentCondition('fr')->shouldBeCalled()->willReturn('condition de paiement');
        $paymentConditions->getPaymentFooter('fr')->shouldBeCalled()->willReturn('pied de page pour le paiement');

        $this->paymentConditionsRepository->remove($paymentConditions->reveal())->shouldBeCalled();
        $this->paymentConditionsRepository->set(Argument::any())->shouldNotBeCalled();
        $this->paymentConditionsRepository->add(Argument::any())->shouldNotBeCalled();

        $command = new Update($this->type->reveal());
        $command->specificPaymentConditions = false;
        $handler = new UpdateHandler($this->paymentConditionsRepository->reveal());
        $handler->handle($command);
    }

    public function testHandleUpdatePaymentConditions()
    {
        $translations = [
            'fr' => [
                'billingAddress' => 'billing address',
                'paymentCondition' => 'payment condition',
                'paymentFooter' => 'payment footer',
                'bankInfo' => 'bank info',
            ]
        ];

        $dateTime = new \DateTime();
        $paymentConditions = $this->prophesize(Type\PaymentConditions::class);
        $paymentConditions->isAllowDeposit()->willReturn(false);
        $paymentConditions->getDepositUntil()->willReturn(null);
        $paymentConditions->getMinimumForDeposit()->willReturn(null);
        $paymentConditions->getDeposit()->willReturn(null);
        $paymentConditions->getPaymentModes()->willReturn([]);
        $paymentConditions->getTranslations()->willReturn([]);
        $paymentConditions
            ->updateTranslations($translations)
            ->shouldBeCalled();
        $this->type->getPaymentConditions()->willReturn($paymentConditions->reveal());
        $this->event->getLocales()->shouldBeCalled()->willReturn(['fr']);
        $paymentConditions->getBankInfo('fr')->shouldBeCalled()->willReturn('info bancaire');
        $paymentConditions->getBillingAddress('fr')->shouldBeCalled()->willReturn('adresse de facturation');
        $paymentConditions->getPaymentCondition('fr')->shouldBeCalled()->willReturn('condition de paiement');
        $paymentConditions->getPaymentFooter('fr')->shouldBeCalled()->willReturn('pied de page pour le paiement');

        $paymentConditions->update(
            [Mode::PAYMENT_PAYPAL],
            true,
            $dateTime,
            600,
            50
        )->shouldBeCalled();
        $this->paymentConditionsRepository->set($paymentConditions->reveal())->shouldBeCalled();
        $this->paymentConditionsRepository->add(Argument::any())->shouldNotBeCalled();
        $this->paymentConditionsRepository->remove(Argument::any())->shouldNotBeCalled();

        $command = new Update($this->type->reveal());
        $command->specificPaymentConditions = true;
        $command->allowDeposit = true;
        $command->depositUntil = $dateTime;
        $command->deposit = 50;
        $command->minimumForDeposit = 600;
        $command->paymentModes = [Mode::PAYMENT_PAYPAL];
        $command->translations = $translations;
        $handler = new UpdateHandler($this->paymentConditionsRepository->reveal());
        $handler->handle($command);
    }

    public function testHandleCreatePaymentConditions()
    {
        $translations = [
            'fr' => [
                'billingAddress' => 'billing address',
                'paymentCondition' => 'payment condition',
                'paymentFooter' => 'payment footer',
                'bankInfo' => 'bank info',
            ]
        ];

        $this->event->getLocales()->shouldBeCalled()->willReturn(['fr']);
        $this->event->getBankInfo('fr')->shouldBeCalled()->willReturn('info bancaire');
        $this->event->getBillingAddress('fr')->shouldBeCalled()->willReturn('adresse de facturation');
        $this->event->getPaymentCondition('fr')->shouldBeCalled()->willReturn('condition de paiement');
        $this->event->getPaymentFooter('fr')->shouldBeCalled()->willReturn('pied de page pour le paiement');

        $dateTime = new \DateTime();
        $this->type->getPaymentConditions()->willReturn(null);

        $expected = new Type\PaymentConditions(
            $this->type->reveal(),
            [Mode::PAYMENT_PAYPAL],
            true,
            $dateTime,
            600,
            50
        );
        $expected->updateTranslations($translations);
        $this->paymentConditionsRepository->add($expected)->shouldBeCalled();
        $this->paymentConditionsRepository->set(Argument::any())->shouldNotBeCalled();
        $this->paymentConditionsRepository->remove(Argument::any())->shouldNotBeCalled();

        $command = new Update($this->type->reveal());
        $command->specificPaymentConditions = true;
        $command->allowDeposit = true;
        $command->depositUntil = $dateTime;
        $command->deposit = 50;
        $command->minimumForDeposit = 600;
        $command->paymentModes = [Mode::PAYMENT_PAYPAL];
        $command->translations = $translations;
        $handler = new UpdateHandler($this->paymentConditionsRepository->reveal());
        $handler->handle($command);
    }
}
