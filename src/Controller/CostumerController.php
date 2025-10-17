<?php

namespace App\Controller;

use App\Entity\Costumer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatableMessage;

final class CostumerController extends AbstractController
{
    private $entityManager;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
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
            $lines = str_getcsv($fileField->getContent(), "\n");
            foreach ($lines as $line) {
                $deliminator = str_contains($line, ";") ? ";" : ",";
                $data = str_getcsv($line, $deliminator);
                try {
                    $costumer = new Costumer();
                    $costumer
                        ->setActive(true)
                        ->setFirstname($data[0])
                        ->setLastname($data[1])
                        ->setDepartment(Department: count($data) >= 3 ? $data[2]  : null);

                    $errors = $this->validator->validate($costumer);
                    if ($errors->count() > 0) {
                        foreach ($errors as $key => $error) {
                            if ($error->getConstraint() instanceof UniqueEntity) {
                                $this->addFlash('error', new TranslatableMessage(
                                    'Costumer %firstname% %lastname% already exists',
                                    [
                                        '%firstname%' => $costumer->getFirstname(),
                                        '%lastname%' => $costumer->getLastname()
                                    ]
                                ));
                            } else {
                                $this->addFlash('error', $error->getMessage());
                            }
                        }
                    } else {
                        $this->entityManager->persist($costumer);

                        // actually executes the queries (i.e. the INSERT query)
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
                    $this->addFlash('error', (string)$th);
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
}
