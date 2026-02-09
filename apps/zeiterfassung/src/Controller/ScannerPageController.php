<?php

namespace Zeiterfassung\Controller;

use Zeiterfassung\Entity\TimeEntry;
use Shared\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Shared\Entity\Costumer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


#[Route(path: '/zeiterfassung', name: 'zeiterfassung')]
class ScannerPageController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {}

    #[Route('/api', name: 'scanner_api', methods: ['POST'])]
    public function scannerApi(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'No data provided'], 400);
        }

        // ------------- 2) Barcode scanning (legacy) -------------
        if (!isset($data['barcode'])) {
            return new JsonResponse(['error' => 'No barcode provided'], 400);
        }

        $userEntity = $this->em->getRepository(Costumer::class)->find($data['barcode']);
        if (!$userEntity) {
            return new JsonResponse(['error' => 'Could not create local user'], 500);
        }

        
        $lastEntry = $this->em->getRepository(TimeEntry::class)->getTimeEntryForUser($userEntity);
        $now = new \DateTime();
        $todayStart = (clone $now)->setTime(0, 0, 0);
        $todayEnd = (clone $now)->setTime(23, 59, 59);
        if ($lastEntry) {
            // 1) If last entry has no checkout, update it (normal checkout)
            if ($lastEntry->getCheckoutTime() === null) {
                $cooldownEnd = (clone $lastEntry->getCheckinTime())->modify('+15 minutes');
                if ($now < $cooldownEnd) {
                    $remaining = $cooldownEnd->getTimestamp() - $now->getTimestamp();
                    return new JsonResponse([
                        'error' => 'Cooldown active',
                        'minutes_remaining' => ceil($remaining / 60)
                    ], 429);
                }

                $lastEntry->setCheckoutTime($now);
                $this->em->flush();

                return new JsonResponse([
                    'status' => 'checkout_update',
                    'user_ID' => $userEntity->getId(),
                    'time' => $now->format('H:i:s')
                ], 200);
            }

            // 2) Last entry already has checkin + checkout → just update checkout
            $lastEntry->setCheckoutTime($now);
            $this->em->flush();

            return new JsonResponse([
                'status' => 'checkout_update_existing',
                'user_ID' => $userEntity->getId(),
                'time' => $now->format('H:i:s')
            ], 200);
        }

        // 3) No entry today → create new checkin
        $newEntry = new TimeEntry();
        $newEntry->setUser($userEntity);
        $newEntry->setCheckinTime($now);
        $newEntry->setCheckoutTime(null);
        $this->em->persist($newEntry);
        $this->em->flush();

        return new JsonResponse([
            'status' => 'checkin',
            'user' => $userEntity->getFirstname(). " ". $userEntity->getLastname(),
            'time' => $now->format('H:i:s'),
            'cooldown_until' => (clone $now)->modify('+15 minutes')->format('H:i:s')
        ], 201);

        
    }

    #[Route('/TimeEntries/{id}', name: 'gettimeentrie', methods: ['GET'])]
    private function getTimeEntry(Request $request, int $id): JsonResponse{
        return new JsonResponse($this->em->getRepository(TimeEntry::class)->find($id));
    }


    private function setCheckIn(Costumer $user, array $data): JsonResponse
    {
        $todayStart = (new \DateTime())->setTime(0, 0, 0);
        $todayEnd = (new \DateTime())->setTime(23, 59, 59);

        $existing = $this->em->getRepository(TimeEntry::class)->createQueryBuilder('t')
            ->where('t.user = :user')
            ->andWhere('t.checkinTime BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('start', $todayStart)
            ->setParameter('end', $todayEnd)
            ->getQuery()
            ->getResult();

        if (count($existing) > 0) {
            // Overwrite today's first entry's check-in
            $entry = $existing[0];
            $entry->setCheckinTime(new \DateTime($data['checkin']));
            $this->em->flush();

            return new JsonResponse(['status' => 'checkin_overwritten']);
        }

        $entry = new TimeEntry();
        $entry->setUser($user);
        $entry->setCheckinTime(new \DateTime($data['checkin']));
        $entry->setCheckoutTime(null);
        $this->em->persist($entry);
        $this->em->flush();

        return new JsonResponse(['status' => 'checkin_set']);
    }

    private function setCheckout(Costumer $user, array $data): JsonResponse
    {
        $todayStart = (new \DateTime())->setTime(0, 0, 0);
        $todayEnd = (new \DateTime())->setTime(23, 59, 59);

        $entries = $this->em->getRepository(TimeEntry::class)->createQueryBuilder('t')
            ->where('t.user = :user')
            ->andWhere('t.checkinTime BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('start', $todayStart)
            ->setParameter('end', $todayEnd)
            ->orderBy('t.checkinTime', 'DESC')
            ->getQuery()
            ->getResult();

        if (count($entries) === 0) {
            // No entry exists → create new with only checkout time
            $entry = new TimeEntry();
            $entry->setUser($user);
            $entry->setCheckinTime($todayStart); // or minimal placeholder
            $entry->setCheckoutTime(new \DateTime($data['checkout']));
            $this->em->persist($entry);
            $this->em->flush();

            return new JsonResponse(['status' => 'checkout_created']);
        }

        // Overwrite the checkout of the first entry
        $entry = $entries[0];
        $entry->setCheckoutTime(new \DateTime($data['checkout']));
        $this->em->flush();

        return new JsonResponse(['status' => 'checkout_overwritten']);
    }

}