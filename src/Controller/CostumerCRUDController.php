<?php

namespace Shared\Controller;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Sonata\Exporter\Source\ArraySourceIterator;
use Sonata\Exporter\Writer\XlsxWriter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sonata\Exporter\Handler;
use ZipArchive;


final class CostumerCRUDController extends CRUDController
{
    public function batchActionBarcodes(ProxyQueryInterface $query, AdminInterface $admin): Response
    {

        $admin->checkAccess('list');
        $selectedModels = $query->execute();

        $zip = new ZipArchive();
        $zipName = tempnam(sys_get_temp_dir(), 'zip');
        if ($zip->open($zipName, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException(_('Cannot open ' . $zipName));
        }

        foreach ($selectedModels as $key => $model) {
            $filename = $model->getLastname() . $model->getFirstname() . $model->getId() . ".svg";
            $zip->addFile($model->getBarcode(), $filename);
        }
        $zip->close();

        $response = new BinaryFileResponse($zipName);
        $response->headers->set('Content-Type', 'application/zip');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'barcodes.zip');
        $this->addFlash('sonata_flash_success', _('successfully exported'));
        return $response;
    }

    public function batchActionExportNames(ProxyQueryInterface $selectedModelQuery, AdminInterface $admin): Response
    {
        $admin->checkAccess('list');

        $selectedUsers = $selectedModelQuery->execute();
        $data = [];

        foreach ($selectedUsers as $user) {
            $data[] = [
                'Vorname' => $user->getFirstname(),
                'Nachname' => $user->getLastname(),
            ];
        }

        $source = new ArraySourceIterator($data);
        $writer = new XlsxWriter('php://output');
        $fileName = 'Teilnehmer_' . date('d.m.Y') . '.xlsx';

        return new StreamedResponse(function () use ($source, $writer) {
            Handler::create($source, $writer)->export();
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
