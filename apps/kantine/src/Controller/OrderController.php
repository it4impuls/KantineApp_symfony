<?php

namespace Kantine\Controller;

use Kantine\Entity\Order;
use Kantine\Form\OrderFormDTO;
use Kantine\Form\OrderDTOType;
use Kantine\Repository\OrderRepository;
use Shared\Entity\Costumer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatableMessage;
use FOS\RestBundle\View\View;


final class OrderController extends AbstractFOSRestController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {
        
    }
    public function getOrderAction(Request $request): Response
    {
        $view = View::create();

        $data = $this->entityManager->getRepository(Order::class)->findAll();

        $view->setData($data);
        return $this->handleView($view);
    }

    private function get_Costumer(int $id): Costumer | null
    {
        return $this->entityManager->getRepository(Costumer::class)->find($id);
    }

    public function makeOrderFromDTO(OrderFormDTO $orderDTO): Order
    {
        $order = new Order();
        $now = new DateTime();
        $order->setOrderDateTime($now);
        // $order->setOrderDate($now);
        $order->setCostumer($this->get_Costumer((int)$orderDTO->getCostumer()));
        $order->setOrderedItem($orderDTO->getOrderedItem());
        $order->setTax($orderDTO->getTax());
        return $order;
    }


    #[Route('/', name: 'app_order')]
    public function orderForm(Request $request): Response
    {
        // creates a task object and initializes some data for this example
        $orderDTO = new OrderFormDTO();
        $orderDTO->setTax(7);
        $order = new Order();
        $form = $this->createForm(OrderDTOType::class, $orderDTO);

        $options = ['form' =>$form,  'override_form' => null];
        $form->handleRequest($request);
        
        // form is submitted (any submit button pressed)
        if ( $form->isSubmitted() && $form->isValid()) {
            $cancelButton =$form->get('cancel');
            assert($cancelButton instanceof SubmitButton);

            if ($cancelButton->isClicked()) {
                $orderDTO = new OrderFormDTO();
                $form = $this->createForm(OrderDTOType::class, $orderDTO);
                $options['form']=$form;
                return $this->render_site($options);
            }

            $orderDTO =$form->getData();
            $order = $this->makeOrderFromDTO($orderDTO);
            $existing = $this->already_ordered($order);


            // if the costumer is not found, bail early
            if ($order->getCostumer() === null) {
                $this->addFlash('alert', new TranslatableMessage("Costumer not found"));
                return $this->render_site($options);
            }

            $saveButton =$form->get('save');
            assert($saveButton instanceof SubmitButton);

            $updateButton =$form->get('update');
            assert($updateButton instanceof SubmitButton);

            // normal OK submit
            if ($saveButton->isClicked()) {
                // if already ordered show update dialog
                if ($existing) {
                    $options['override_form'] = true;
                } else {

                    //try saving, if error write in $options['alert']
                    $this->save_order($order, $options);
                }
            } elseif ($updateButton->isClicked()) {
                $existing->setOrderedItem($order->getOrderedItem());
                $existing->setTax($order->getTax());
                $existing->setOrderDateTime($order->getOrderDateTime());
                $this->save_order($existing, $options);
            }
        }
        return $this->render_site($options);
    }



    private function render_site(&$options)
    {
        return $this->render('@Kantine/components/Order_submit.html.twig', $options);
    }

    private function already_ordered(Order $order): ?Order
    {
        //check for already ordered
        $repository = $this->entityManager->getRepository(Order::class);
        if (!$repository instanceof OrderRepository) {
            throw new ConstraintDefinitionException(\sprintf('Class must use "%s".', OrderRepository::class));
        }
        return $repository->findCostumerOrderAtDate($order->getCostumer(), $order->getOrderDateTime());
    }

    private function save_order(Order $order, &$options = []): bool
    {
        $errors = $this->validator->validate($order);
        if ($errors->count() > 0) {
            $this->addFlash('alert', (string)$errors);
            return false;
        } else {
            $this->entityManager->persist($order);

            // actually executes the queries (i.e. the INSERT query)
            $this->entityManager->flush();
            $this->addFlash('alert', new TranslatableMessage("success"));
            return true;
        }
    }


    #[When(env: 'dev')]
    #[Route('/generate/order/{num}', name: 'gen_orders')]
    public function genOrders(Request $request, $num): Response
    {
        $generated = [];
        $costumers = $this->entityManager->getRepository(Costumer::class)->findAll();
        shuffle($costumers);
        foreach ($costumers as $key => $value) {
            if ($num <= $key) {
                break;
            }
            $order = new order();
            $order->setCostumer($value)
                ->setOrderedItem((float)rand(0, 20) / 2)
                ->setTax(rand(7, 14));

            $errors = $this->validator->validate($order);
            if ($errors->count() > 0) {
                $msg = (string)$errors . "<br><br>added:" . implode($generated);
                return new Response((string)$msg);
            }
            $this->entityManager->persist($order);
            $this->entityManager->flush();
            $generated[$key] = join(" ", [$order->getId(), $order->getOrderedItem(), $order->getTax() . "%"]) . "<br>";
        }

        return new Response(implode($generated));
    }
}
