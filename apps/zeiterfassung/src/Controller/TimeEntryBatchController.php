<?php

namespace Zeiterfassung\Controller;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Zeiterfassung\Entity\TimeEntry;
use Mpdf\Mpdf;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

final class TimeEntryBatchController extends AbstractController
{
    private $data_headers = ['Datum', 'Eintrag', 'Austrag'];

    // requires sorted by customer + timeentry date
    private function monthlyIterable(iterable $rawData)
    {
        $date_format = datefmt_create('de-DE');
        $date_format->setPattern("EEEE dd.MM.y");

        $month_format = datefmt_create('de-DE');
        $month_format->setPattern("MMMM y");

        $last_costumer = null;
        $last_month = null;

        $data = [];

        foreach ($rawData as $timeEntry) {
            assert ($timeEntry instanceof TimeEntry);
            $month = datefmt_format($month_format, $timeEntry->getCheckinTime());

            // not same month or costumer, yield current and reset new page
            if (($timeEntry->getUser()->getId() != $last_costumer || $month != $last_month) )
            {
                if (!empty($data))
                    yield $data;
                $data = [];
            }
            $data['data'][] = [
                'Datum'=>   datefmt_format($date_format, $timeEntry->getCheckinTime()), 
                'Eintrag' => $timeEntry->getCheckinTime()->format('H:i'), 
                'Austrag' => $timeEntry->getCheckoutTime()? $timeEntry->getCheckoutTime()->format('H:i'):''
            ];
            $data['name'] = $timeEntry->getUser()->getFullName();
            $data['month'] = $month;
            $last_costumer = $timeEntry->getUser()->getId() ;
            $last_month = $month;
        }

        yield $data;
    }
    
    public function batchGetReportAction(ProxyQueryInterface $query, AdminInterface $admin, LoggerInterface $logger): Response//BinaryFileResponse|RedirectResponse
    {
        assert($query instanceof ProxyQuery);
        $admin->checkAccess('list');
        $selectedEntries = $query->getQueryBuilder()
            ->addOrderBy('o.user')
            ->addOrderBy('o.checkinTime')
            ->getQuery()
            ->toIterable();
        $data = $this->monthlyIterable($selectedEntries);
        $first=true;
        try {
            $pdf = new Mpdf();
            $pdf->setLogger($logger);
            // write each page individually
            foreach ($data as $page) {
                $html = $this->renderView('@Zeiterfassung/monthly_report.html.twig', [
                            'entries' => $page['data'],
                            'customer' => $page['name'],
                            'headers' => $this->data_headers,
                            'first' => $first,
                            'month' => $page['month']
                        ]);
                $pdf->WriteHTML($html, init:$first, close:false);
                $first=false;
            }

            // output pdf to browser. Function should exit from OutputHttpInline
            $pdf->WriteHTML('', init:false, close:true);
            $pdf->OutputHttpDownload('file.pdf');

            // TODO: does not get shown until next refresh...
            $this->addFlash(
                'sonata_flash_success',
                'Success'
            );

        } catch (\Throwable $th) {
            $this->addFlash(
                'sonata_flash_error',
                $th->getMessage()
            );
            // throw $th;
        } finally {
            // only returns when something went wrong
            return new RedirectResponse(
                    $admin->generateUrl('list', [
                        'filter' => $admin->getFilterParameters()
                    ])
                );
        }

    }
}

