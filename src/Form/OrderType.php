<?php

namespace App\Form;

use App\Entity\Costumer;
use App\Entity\Order;
use App\Form\OrderFormDTO;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('id', IntegerType::class)
            ->add('Costumer', EntityType::class, [
                'class' => Costumer::class,
                'choice_label' => 'id',
                'attr' => ['display' => 'none'],
                "required" => true
            ])
            ->add('ordered_item', NumberType::class, ["required" => true])
            ->add('tax', IntegerType::class, ["required" => true])
            ->add('order_dateTime', DateTimeType::class, ["required" => true])
            ->add('cancel', SubmitType::class, ["required" => true])
            ->add('save', SubmitType::class, ['attr' => ['class' => 'btn']])
        ;
        // $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
        //     $data = $event->getData();
        //     $form = $event->getForm();
        //     // $data->setOrderDateTime($data->getDateTime());


        //     // $costumer_id = (int)$data->Costumer_id;
        //     // $data->customer = $this->em->getRepository(Costumer::class)->find($costumer_id);
        //     // $event->setData($data);
        //     // throw new Exception(serialize($data->getCostumer()));
        // });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'validation_groups' => ['Default', 'create'],
        ]);
    }
}
