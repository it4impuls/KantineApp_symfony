<?php

namespace Zeiterfassung\Controller;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\Exporter\Handler;
use Sonata\Exporter\Source\ArraySourceIterator;
use Sonata\Exporter\Writer\XlsxExporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Zeiterfassung\Entity\TimeEntry;
use ZipArchive;

final class TimeEntryBatchController extends AbstractController
{
    private function formatReportData(array $rawData): array
    {
        $format = datefmt_create('de-DE');
        $format->setPattern("EEEE dd.MM.y");

        $data = [];

        // sort entries into [Costumer][Month][entiry]
        foreach ($rawData as $timeEntry) {
            if(!$timeEntry instanceof TimeEntry) continue;
            $month = $timeEntry->getCheckinTime()->format('m.y');
            $data[$timeEntry->getUser()->getFullname()][$month][] = [
                'Datum'=>   datefmt_format($format, $timeEntry->getCheckinTime()), 
                'Eintrag' => $timeEntry->getCheckinTime()->format('H:i'), 
                'Austrag' => $timeEntry->getCheckoutTime()? $timeEntry->getCheckoutTime()->format('H:i'):''];
        }

        return $data;
    }
    
    public function batchGetReportAction(ProxyQueryInterface $query, AdminInterface $admin): BinaryFileResponse|RedirectResponse
    {
        $admin->checkAccess('list');
        $selectedEntries = $query->execute();

        
        $exporter = new XlsxExporter();
        $zipName = $exporter->writeAsTimeEntryReport($selectedEntries); 

        $response = new BinaryFileResponse($zipName);
        $response->headers->set('Content-Type', 'application/zip');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Teilnehmer_Zeiteinträge' . '.zip');
        $this->addFlash('sonata_flash_success', 'successfully exported');
        return $response;
    }
}
