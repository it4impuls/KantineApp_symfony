<?php

namespace App\Controller;

use App\Entity\Costumer;
use App\Repository\CostumerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Persisters\Exception\UnrecognizedField;
use JMS\Serializer\Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatableMessage;

final class CostumerController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator
    ) {}

    private function flashCostumerAddError(Costumer $costumer, string $error)
    {
        $this->addFlash('error', new TranslatableMessage(
            "%firstname% %lastname% in %dep% could not me added: %cause%",
            [
                '%cause%' => $error,
                '%firstname%' => $costumer->getFirstname(),
                '%lastname%' => $costumer->getLastname(),
                '%dep%' => $costumer->getDepartment() ?? _("NO DEPARTMENT SET"),
            ]
        ));
    }

    #[IsGranted('ROLE_ADMIN_COSTUMER_VIEW')]
    #[Route('/api/costumers/{id}', name: 'get_costumer')]
    public function getCostumer($id): Response | JsonResponse
    {
        // get Costumer in JSON compatible format
        $costumer = $this->entityManager->getRepository(Costumer::class)->findByCode($id)->getArrayResult();
        if (!$costumer) throw $this->createNotFoundException(
            'No product found for id ' . $id
        );
        return new JsonResponse($costumer);
    }

    #[IsGranted('ROLE_ADMIN_COSTUMER_VIEW')]
    #[Route('/api/costumers/', name: 'get_costumers',)]
    public function getCostumerFiltered(Request $request, SerializerInterface $serializer): Response | JsonResponse
    {
        // get Costumer in JSON compatible format
        $filter = $request->query->all();
        $costumers = $this->entityManager->getRepository(Costumer::class)->filterBy($filter)->getArrayResult();
        if (!$costumers) throw $this->createNotFoundException(
            'No costumer found with those attributes'
        );

        // $serialized = $serializer->serialize($costumers, 'json');

        return new JsonResponse($costumers);
    }

    #[Route('/api/allowed_departments', name: 'get_allowed_departments')]
    public function getAllowedDepartments(): JsonResponse
    {
        return new JsonResponse(Costumer::DEPARTMENTS);
    }

    #[IsGranted('ROLE_ADMIN_COSTUMER_CREATE')]
    #[Route('/add_users', name: 'upload_users')]
    public function uploadUsers(Request $request): Response
    {

        $form = $this->createFormBuilder()
            ->add('file', FileType::class)
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileField = $form["file"]->getData();

            if ($fileField->getMimeType() != "text/csv" && $fileField->getMimeType() != "text/plain") {
                $this->addFlash('error', message: _("File must be csv"));
                return $this->render('components/Form.html.twig', [
                    'form' => $form,
                ]);
            }

            //rows
            $lines = str_getcsv($fileField->getContent(), "\n", '"', "\\");
            $deliminator = str_contains($lines[0], ";") ? ";" : ",";
            foreach ($lines as $line) {
                if (!str_contains($line, $deliminator)) break;
                $data = str_getcsv($line, $deliminator, '"', "\\");
                if (sizeof($data) < 2) break;
                try {
                    $costumer = new Costumer();
                    $costumer
                        ->setActive(true)
                        ->setFirstname($data[0])
                        ->setLastname($data[1])
                        ->setDepartment(Department: count($data) >= 3 ? $data[2] : null);

                    $errors = $this->validator->validate($costumer);
                    if ($errors->count() > 0) {
                        foreach ($errors as $key => $error) {
                            // update Dep
                            if ($error->getConstraint() instanceof UniqueEntity && $costumer->getDepartment()) {
                                $cause = $error->getCause();
                                if (count($cause) != 1) {
                                    $this->flashCostumerAddError($costumer, $error->getMessage());
                                    break;
                                }
                                // save existing costumer with new department
                                $cause[0]->setDepartment($costumer->getDepartment());
                                $err_new = $this->validator->validate($cause[0]);
                                if ($err_new->count() > 0) {
                                    $this->flashCostumerAddError($costumer, $err_new[0]->getMessage());
                                    break;
                                }
                                $this->entityManager->persist($cause[0]);
                                $this->entityManager->flush();
                                $this->addFlash('notice', new TranslatableMessage(
                                    'updated department %dep% for existing costumer: %firstname% %lastname% &emsp; <img src="/%barcode%"> ',
                                    [
                                        '%firstname%' => $cause[0]->getFirstname(),
                                        '%lastname%' => $cause[0]->getLastname(),
                                        '%dep%' => $cause[0]->getDepartment() ?? _("NO DEPARTMENT SET"),
                                        '%barcode%' => $cause[0]->getBarcode()
                                    ]
                                ));
                                break;
                            } else {
                                $this->flashCostumerAddError($costumer, $error->getMessage());
                                break;
                            }
                        }
                    } else {
                        // actually executes the queries (i.e. the INSERT query)
                        $this->entityManager->persist($costumer);
                        $this->entityManager->flush();
                        $this->addFlash('notice', new TranslatableMessage(
                            'sucessfully added: %firstname% %lastname% in %dep% &emsp; <img src="/%barcode%"> ',
                            [
                                '%firstname%' => $costumer->getFirstname(),
                                '%lastname%' => $costumer->getLastname(),
                                '%dep%' => $costumer->getDepartment() ?? _("NO DEPARTMENT SET"),
                                '%barcode%' => $costumer->getBarcode()
                            ]
                        ));
                    }
                } catch (\Throwable $th) {
                    $this->flashCostumerAddError($costumer, (string)$th);
                }
            }
        }

        return $this->render('components/Form.html.twig', [
            'form' => $form,
        ]);
    }

    /* 
        For testing.
        data from: 
            https://nachnamen.net/deutschland
            https://opendata.jena.de/dataset/vornamen
    */
    #[When(env: 'dev')]
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

    #[IsGranted('ROLE_ADMIN_COSTUMER_DELETE')]
    #[Route('/cron/delete_old_costumers', name: 'del_costumers')]
    public function deleteOldInactiveCostumers(Request $request): Response
    {
        $repository = $this->entityManager->getRepository(Costumer::class);
        $count = $repository->deleteOldInactive();
        return new Response($count ? sprintf('Deleted %d old Costumer(s).', $count) : 'No Costumers to delete');
    }
}
