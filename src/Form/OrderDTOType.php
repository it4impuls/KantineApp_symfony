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
use Symfony\Component\Form\Extension\Core\Type\index;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
// use Symfony\Bridge\Doctrine\Form\Type\;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderDTOType extends AbstractType
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
        $builder
            ->add('Costumer', null, ["required" => true])
            ->add('ordered_item', ChoiceType::class, ["choices" => $this->items, "expanded" => true, "required" => true])
            ->add('tax', ChoiceType::class, ["choices" => $this->taxes, "expanded" => true,  "required" => true])
            ->add('save', SubmitType::class)
            ->add('update', SubmitType::class, ['attr' => ['class' => 'btn button']])
            ->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn button']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderFormDTO::class,
        ]);
    }
}
