<?php

namespace App\Form;

use App\Entity\Costumer;
use App\Entity\Order;
use App\Form\OrderFormDTO\OrderFormDTO;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\index;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
// use Symfony\Bridge\Doctrine\Form\Type\;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    private EntityManagerInterface $em;
    function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    private $items = [
        "3,00€" => 3.00,
        "3,50€" => 3.50,
        "4,50€" => 4.50,
        "6,00€" => 6.00,
        "6,50€" => 6.50,
        "6,90€" => 6.90,
        "7,00€" => 7.00,
        "7,50€" => 7.50,
        "7,90€" => 7.90,
        "8,50€" => 8.50,
    ];
    private $taxes = [
        "7%" => 7,
        "19%" => 19
    ];
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // $this->$items
        // foreach ($this->$items as $key => $value) {
        //     $builder->('ordered_item')
        // }
        $builder
            // ->add('Costumer', EntityType::class, [
            //     // looks for choices from this entity
            //     'class' => Costumer::class,
            //     'choice_label' => 'id',
            //     'expanded' => true
            // ])
            ->add('Costumer')
            ->add('ordered_item', ChoiceType::class, ["choices" => $this->items, "expanded" => true, "required" => true])
            ->add('tax', ChoiceType::class, ["choices" => $this->taxes, "expanded" => true,  "required" => true])
            ->add('save', SubmitType::class)
        ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            // $c_id = (int)$request->request->parameters["order"]["Costumer_id"];
            // $request->request->parameters["order"]["ordered_item"] = $request->request->parameters["ordered_item"];
            // $request->request->parameters["order"]["ordered_item"] = $request->request->parameters["ordered_item"];
            // $request->request->parameters["order"]["Costumer"] = $this->get_Costumer($c_id);
            $data = $event->getData();
            $form = $event->getForm();
            // $costumer_id = (int)$data->Costumer_id;
            // $data->customer = $this->em->getRepository(Costumer::class)->find($costumer_id);
            // $event->setData($data);
            // throw new Exception(serialize($data->getCostumer()));
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderFormDTO::class,
        ]);
    }
}
