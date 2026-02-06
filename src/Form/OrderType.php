<?php

namespace App\Form;

use App\Entity\Costumer;
use App\Entity\Order;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Costumer', EntityType::class, [
                'class' => Costumer::class,
                'choice_label' => 'id',
                'attr' => ['display' => 'none'],
                "required" => true
            ])
            ->add('ordered_item', NumberType::class, ["required" => true])
            ->add('tax', HiddenType::class, ["required" => true])
            ->add('order_dateTime', DateTimeType::class, ["required" => true])
            ->add('cancel', SubmitType::class, ["required" => true])
            ->add('save', SubmitType::class, ['attr' => ['class' => 'btn']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'validation_groups' => ['Default', 'create'],
        ]);
    }
}
