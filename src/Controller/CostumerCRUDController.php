<?php

namespace App\Controller;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
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
}
