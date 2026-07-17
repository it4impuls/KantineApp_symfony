<?php

namespace Zeiterfassung\Controller;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

use Zeiterfassung\Entity\ScannerClient;
use Zeiterfassung\Entity\ScannerLogEntry;
use Zeiterfassung\DTO\LogDTO;

#[Route(path: '/api/scanner/{scanner_name}', name: 'app_scanner', methods:["GET"])]
final class ScannerClientController extends AbstractController
{
    #[Route('/', name: 'scanner_client_ping')]
    public function ping(string $scanner_name, EntityManagerInterface $entityManager): Response
    {
        $repo = $entityManager->getRepository(ScannerClient::class);
        $scanner_entity = $repo->getOrCreateByName($scanner_name);

        try {
            $scanner_entity->setLastOnline(new DateTime());
            $entityManager->persist($scanner_entity);
            $entityManager->flush();
        } catch (\Throwable $th) {
            return new Response($th->getMessage(), $th->getCode());
        }
        return new Response("OK");
    }

    #[Route('/log', name: 'scanner_client_log', methods:["POST"])]
    public function log(string $scanner_name, #[MapRequestPayload] LogDTO $logDto, EntityManagerInterface $entityManager, ObjectMapperInterface $objectMapper, LoggerInterface $logger): Response
    {
        $repo = $entityManager->getRepository(ScannerClient::class);
        $scanner_entity = $repo->findOneBy(["uname" => $scanner_name]);
        try {
            $log = $objectMapper->map($logDto, ScannerLogEntry::class);
            assert($log instanceof ScannerLogEntry);

            $log->setScanner($scanner_entity);
            $entityManager->persist($log);

            $scanner_entity->setLastOnline(new DateTime());
            $entityManager->persist($scanner_entity);

            $entityManager->flush();
        } catch (\Throwable $th) {
            return new Response($th->getMessage(),  400);
        }
        return new Response("OK");
    }
}
