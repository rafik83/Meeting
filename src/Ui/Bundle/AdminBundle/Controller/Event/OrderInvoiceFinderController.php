<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event;

use Proximum\Vimeet\Application\Command\Event\Find\Find;
use Proximum\Vimeet\Application\Command\Event\Find\FindResult;
use Proximum\Vimeet\Application\Exception\Invoice\InvalidNumeroInvoiceException;
use Proximum\Vimeet\Application\Exception\Invoice\InvoiceNotFoundException;
use Proximum\Vimeet\Application\Exception\Order\InvalidNumeroOrderException;
use Proximum\Vimeet\Application\Exception\Order\OrderNotFoundException;
use Proximum\Vimeet\Application\Query\Event\Find\MultipleSheetFoundViewQuery;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Order\FindType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class OrderInvoiceFinderController extends Controller
{
    /**
     * @param Request       $request
     * @param UserInterface $admin
     *
     * @return Response
     */
    public function findAction(Request $request, UserInterface $admin): Response
    {
        $this->isGranted('ROLE_ALLOWED_TO_OPERATE');

        $multipleSheetsView = null;
        $find = new Find($admin);
        $findForm = $this->createForm(FindType::class, $find);

        if ($findForm->handleRequest($request)->isSubmitted() && $findForm->isValid()) {
            try {
                /** @var FindResult $result */
                $result = $this->get('tactician.commandbus')->handle($find);

                if ($result->hasOnlyOneSheet()) {
                    return $this->redirectToRoute('admin_sheet_details', [
                        'event'     => $result->sheet->getEvent()->getId(),
                        'sheet'     => $result->sheet->getId(),
                        '_fragment' => 'sheetOrders',
                    ]);
                } else {
                    // Warn the user that there is multiple sheet with an invoice with this numero
                    $multipleSheetsView = $this->get('tactician.commandbus.query')->handle(
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

    /**
     * @param FormInterface $formElement
     * @param string        $message
     */
    private function addError(FormInterface $formElement, $message)
    {
        $formElement->addError(new FormError($this->get('translator')->trans($message, [], 'validators')));
    }
}
