<?php

namespace Zeiterfassung\Controller;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Zeiterfassung\Writer\XlsxExporter;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Zeiterfassung\Entity\TimeEntry;
use ZipArchive;

final class TimeEntryBatchController extends AbstractController
{
    private $data_headers = ['Datum', 'Eintrag', 'Austrag'];

    private function formatReportData(array|Paginator $rawData): array
    {
        $date_format = datefmt_create('de-DE');
        $date_format->setPattern("EEEE dd.MM.y");

        $month_format = datefmt_create('de-DE');
        $month_format->setPattern("MMMM y");

        $data = [];

        // sort entries into [Costumer][Month][entiry]
        foreach ($rawData as $timeEntry) {
            if(!$timeEntry instanceof TimeEntry) continue;
            $month = datefmt_format($month_format, $timeEntry->getCheckinTime());
            $data[$timeEntry->getUser()->getFullname()][$month][] = [
                'Datum'=>   datefmt_format($date_format, $timeEntry->getCheckinTime()), 
                'Eintrag' => $timeEntry->getCheckinTime()->format('H:i'), 
                'Austrag' => $timeEntry->getCheckoutTime()? $timeEntry->getCheckoutTime()->format('H:i'):''];
        }

        return $data;
    }
    
    public function batchGetReportAction(ProxyQueryInterface $query, AdminInterface $admin): Response//BinaryFileResponse|RedirectResponse
    {
        $admin->checkAccess('list');
        $selectedEntries = $query->execute();
        $data = $this->formatReportData($selectedEntries);
        $dompdf = new Dompdf();
        $dompdf->setPaper('A4', 'portrait');

        $html = $this->renderView('@Zeiterfassung/monthly_report_collection.html.twig', [
            'data' => $data, 
            'headers' => $this->data_headers
        ]);
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream();

        // anyything after $dompdf->stream() doesnt matter
        $this->addFlash('sonata_flash_success', 'successfully exported');
        return new Response($html);
    }
}
