<?php

namespace Zeiterfassung\Controller;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Zeiterfassung\Writer\XlsxExporter;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Zeiterfassung\Entity\TimeEntry;
use ZipArchive;

final class TimeEntryBatchController extends AbstractController
{
    private function formatReportData(array|Paginator $rawData): array
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
        $data = $this->formatReportData($selectedEntries);

        $zipName = tempnam(sys_get_temp_dir(), 'zip_');
        $zip = new ZipArchive();
        if ($zip->open($zipName, ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException(_('Cannot open ' . $zipName));
        }

        // write into zip archive structured costumer/month.xlsx
        foreach ($data as $costumer => $months) {
            $zip->addEmptyDir($costumer);
            foreach ($months as $month => $entries) {
                $writer = new XlsxExporter();
                $file = $writer->writeAsTimeEntryReport($entries, $costumer);
                $zip->addFile($file, $costumer.DIRECTORY_SEPARATOR.$costumer.'_'.$month.'.xlsx');
            }
        }
        if (!$zip->close()) throw new \RuntimeException(_('Cannot close ' . $zipName));
        
        $response = new BinaryFileResponse($zipName);
        $response->headers->set('Content-Type', 'application/zip');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Teilnehmer_Zeiteinträge' . '.zip');
        $this->addFlash('sonata_flash_success', 'successfully exported');
        return $response;
    }
}
