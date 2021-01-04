<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Planner;

use Proximum\Vimeet\Application\Command\Planner\ExportJobCreator;
use Proximum\Vimeet\Application\Exception\Planner\DayNotConfiguredException;
use Proximum\Vimeet\Application\Exception\Planner\NoSpotActiveException;
use Proximum\Vimeet\Application\Exception\Planner\SlotNotConfiguredException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\XmlFileResponse;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Planner\ExportType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ExportController extends Controller
{
    /**
     * @param Request       $request
     * @param UserInterface $admin
     * @param Event         $event
     * @param string        $mode
     *
     * @return Response
     */
    public function exportAction(Request $request, UserInterface $admin, Event $event, string $mode): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $isEventOpened = $this->get('domain.key_dates.checker.event_open_access_checker')->allowedToAccess($event);

        if (ExportJobCreator::MODE_AUTO === $mode && $isEventOpened) {
            throw $this->createAccessDeniedException('Planner is not authorized when event is opened');
        }

        if (!$admin instanceof Admin) {
            throw $this->createNotFoundException('Admin not found');
        }

        $exportJobCreator = new ExportJobCreator($event, $admin, $request->getLocale(), $mode);
        $form = $this->createForm(ExportType::class, $exportJobCreator, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($exportJobCreator);
                $this->addFlash(
                    'success',
                    $exportJobCreator->isModeAuto()
                        ? 'flash.admin.planner.run.success'
                        : 'flash.admin.planner.export.success'
                );

                return $this->redirectToRoute('admin_planner', ['event' => $event->getId()]);
            } catch (NoSpotActiveException $noSpotActiveException) {
                $this->exceptionToFormError($form, $noSpotActiveException);
            } catch (DayNotConfiguredException $dayNotConfiguredException) {
                $this->exceptionToFormError($form, $dayNotConfiguredException);
            } catch (SlotNotConfiguredException $slotNotConfiguredException) {
                $this->exceptionToFormError($form, $slotNotConfiguredException);
            }
        }

        return $this->render('AdminBundle:Planner/Export:form.html.twig', [
            'event'      => $event,
            'form'       => $form->createView(),
            'isModeAuto' => $exportJobCreator->isModeAuto(),
        ]);
    }

    /**
     * @param FormInterface $form
     * @param \Exception    $exception
     */
    private function exceptionToFormError(FormInterface $form, \Exception $exception): void
    {
        $form->addError(
            new FormError(
                $this->get('translator')->trans(
                    sprintf('flash.%s', $exception->getMessage()),
                    [],
                    'flashes'
                )
            )
        );
    }

    /**
     * @param Event  $event
     * @param string $hash
     * @param File   $file
     *
     * @return XmlFileResponse
     */
    public function exportFileAction(Event $event, $hash, File $file)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if ($file->getHash() !== $hash) {
            throw $this->createNotFoundException(
                sprintf('File %s has a different hash from the one given %s', $file->getId(), $hash)
            );
        }

        $path = sprintf('%s%s', $this->getParameter('infrastructure.export_planner_path'), $file->getPath());

        if (!$this->get('filesystem')->exists($path)) {
            throw $this->createNotFoundException(sprintf('File %s not found', $file->getId()));
        }

        return new XmlFileResponse(
            file_get_contents($path),
            sprintf('export_planner_%s_%s.xml', $event->getId(), date('Y_m_d_His'))
        );
    }
}
