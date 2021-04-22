<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Event\Find\Find;
use Proximum\Vimeet\Application\Command\Event\Find\FindResult;
use Proximum\Vimeet\Application\Exception\Invoice\InvalidNumeroInvoiceException;
use Proximum\Vimeet\Application\Exception\Invoice\InvoiceNotFoundException;
use Proximum\Vimeet\Application\Exception\Order\InvalidNumeroOrderException;
use Proximum\Vimeet\Application\Exception\Order\OrderNotFoundException;
use Proximum\Vimeet\Application\Query\Event\Find\MultipleSheetFoundViewQuery;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Order\FindType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class OrderInvoiceFinderController extends AbstractController
{
    private TranslatorInterface $translator;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        TranslatorInterface $translator,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->translator = $translator;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    public function findAction(Request $request, UserInterface $admin): Response
    {
        $this->isGranted('ROLE_ALLOWED_TO_OPERATE');

        $multipleSheetsView = null;
        $find = new Find($admin);
        $findForm = $this->createForm(FindType::class, $find);

        if ($findForm->handleRequest($request)->isSubmitted() && $findForm->isValid()) {
            try {
                /** @var FindResult $result */
                $result = $this->commandBus->handle($find);

                if ($result->hasOnlyOneSheet()) {
                    return $this->redirectToRoute('admin_sheet_details', [
                        'event'     => $result->sheet->getEvent()->getId(),
                        'sheet'     => $result->sheet->getId(),
                        '_fragment' => 'sheetOrders',
                    ]);
                } else {
                    // Warn the user that there is multiple sheet with an invoice with this numero
                    $multipleSheetsView = $this->queryBus->handle(
                        new MultipleSheetFoundViewQuery($find->numero, $result->getSheets())
                    );
                }
            } catch (OrderNotFoundException $exception) {
                $this->addError($findForm->get('numero'), 'validators.order.orderNotFound');
            } catch (InvalidNumeroOrderException $exception) {
                $this->addError($findForm->get('numero'), 'validators.order.numeroNotValid');
            } catch (InvalidNumeroInvoiceException $exception) {
                $this->addError($findForm->get('numero'), 'validators.invoice.numeroNotValid');
            } catch (InvoiceNotFoundException $exception) {
                $this->addError($findForm->get('numero'), 'validators.invoice.invoiceNotFound');
            }
        }

        return $this->render('AdminBundle:Event/OrderInvoice:find.html.twig', [
            'multipleSheetsView' => $multipleSheetsView,
            'form'               => $findForm->createView(),
        ]);
    }

    private function addError(FormInterface $formElement, string $message): void
    {
        $formElement->addError(new FormError($this->translator->trans($message, [], 'validators')));
    }
}
