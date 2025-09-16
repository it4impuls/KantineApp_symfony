<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderFormDTO\OrderFormDTO;
use App\Form\OrderType;
use App\Entity\Costumer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class OrderController extends AbstractController
{
    private $logger;
    private $entityManager;
    private $validatior;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, ValidatorInterface $validatior)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->validatior = $validatior;
    }

    #[Route('/success', name: 'task_success')]
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }

    public function get_Costumer(int $id): Costumer
    {
        // $id = $str_id.int
        $costumer = $this->entityManager->getRepository(Costumer::class)->find($id);

        if (!$costumer) {
            throw $this->createNotFoundException(
                'No product found for id ' . $id
            );
        }

        return $costumer;
    }

    public function makeOrderFromDTO(OrderFormDTO $orderDTO): Order
    {
        $order = new Order();
        $order->setOrderDate(new DateTime());
        $order->setCostumer($this->get_Costumer((int)$orderDTO->getCostumer()));
        $order->setOrderedItem($orderDTO->getOrderedItem());
        $order->setTax($orderDTO->getTax());
        return $order;
    }

    #[Route('/', name: 'app_order')]
    public function orderForm(Request $request): Response
    {
        // creates a task object and initializes some data for this example
        $order = new OrderFormDTO();

        $form = $this->createForm(OrderType::class, $order);
        $options = ['form' => $form, 'alert' => '', 'errors' => []];
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $order = $form->getData();

            // throw new Exception(serialize($request->getPayload()->all()));
            $this->logger->debug(serialize($form->getData()));
        }
        $options['errors'] = $form->getErrors();

        return $this->render('components/Index.html.twig', $options);
    }
}
