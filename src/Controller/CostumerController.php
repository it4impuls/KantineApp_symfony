<?php

namespace App\Controller;

use App\Entity\Costumer;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ZipArchive;

final class CostumerController extends CRUDController
{
    private $entityManager;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    #[Route('/costumer', name: 'app_costumer')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/CostumerController.php',
        ]);
    }


    /* 
        For testing.
        data from: 
            https://nachnamen.net/deutschland
            https://opendata.jena.de/dataset/vornamen
    */
    #[Route('/generate/costumer/{num}', name: 'gen_costumers')]
    public function genUsers(Request $request, $num): Response
    {
        $dir = 'uploads';
        // look in public/barcodes/${id}.svg
        if (!is_dir($dir)) {
            mkdir($dir, 0755);
        }

        $names = [];
        foreach (["vornamen2024_opendata_datenschutz.csv", "nachnamen.csv"] as $key => $value) {
            $sur_loc = join(DIRECTORY_SEPARATOR, [$dir, $value]);
            $file = fopen($sur_loc, "r");
            $content = trim(fread($file, filesize($sur_loc)));
            // [0=>[Vornamen], 1=>[Nachnamen]]
            $names[$key] = explode("\n", $content);
            fclose($file);
        }

        $generated = [];
        for ($i = 0; $i < $num; $i++) {
            $costumer = new Costumer();
            $costumer->setFirstname($names[0][array_rand($names[0])])
                ->setLastname($names[1][array_rand($names[1])])
                ->setActive(true);
            $errors = $this->validator->validate($costumer);
            if ($errors->count() > 0) {
                return new Response((string)$errors);
            }
            $this->entityManager->persist($costumer);
            $this->entityManager->flush();
            $generated[$i] = join(" ", [$costumer->getId(), $costumer->getFirstname(), $costumer->getLastname()]) . "<br>";
        }

        return new Response(implode($generated));
    }

    // #[Route('/order/{id}/barcode', name: 'get_barcode')]
    public function batchActionBarcodes(ProxyQueryInterface $query, AdminInterface $admin): Response
    {
        $admin->checkAccess('list');
        $modelManager = $admin->getModelManager();
        $selectedModels = $query->execute();

        $msg = "";
        $imgs = [];

        $zip = new ZipArchive();
        $zipName = tempnam(sys_get_temp_dir(), 'zip');
        if ($zip->open($zipName, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Cannot open ' . $zipName);
        }



        foreach ($selectedModels as $key => $model) {
            $imgs[$key] = "<img src=/{$model->getBarcode()}>";
            $filename = $model->getLastname() . $model->getFirstname() . $model->getId() . ".svg";
            // $filename = ((string)$model->getFirstname()) . ".svg";
            $zip->addFile($model->getBarcode(), $filename);
        }
        $zip->close();

        $response = new BinaryFileResponse($zipName);
        $response->headers->set('Content-Type', 'application/zip');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'barcodes.zip');
        $this->addFlash('sonata_flash_success', _('sucess'));
        return $response;


        // return new RedirectResponse(
        //     $admin->generateUrl('list', [
        //         'filter' => $admin->getFilterParameters()
        //     ])
        // );
    }
}
