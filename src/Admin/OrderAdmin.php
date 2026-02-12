<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Costumer;
use DateTime;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\DataTransformer\MoneyToLocalizedStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

final class OrderAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('Costumer')
            ->add('order_dateTime', DateRangeFilter::class, [
                    'field_type'=> DateRangePickerType::class,
                    'field_options' => [
                    'field_options' => [
                        'format' => 'dd.MM.yyyy'
                     ],
                ]])
            ->add('ordered_item')
            ->add('tax')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('Costumer', EntityType::class, [
                'class' => Costumer::class,
                'choice_label' => 'id',
            ])
            ->add('order_dateTime')
            ->add('orderFormatted')
            ->add('tax')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    // public function getDataSourceIterator()
    // {
    //     $datasourceit = parent::getDataSourceIterator();
    //     $datasourceit->setDateTimeFormat('d/m/Y'); //change this to suit your needs
    //     return $datasourceit;
    // }

    protected function configureExportFields(): array
    {
        return ['Costumer.id', 'order_dateTime', 'OrderNum', 'tax'];
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('Costumer', EntityType::class, [
                'class' => Costumer::class,
                'choice_label' => 'id',
            ])
            // ->add('order_dateTime')
            ->add('ordered_item', MoneyType::class, [])
            ->add('tax')
        ;
    }

    /**
     * @phpstan-return T
     */
    protected function createNewInstance(): object
    {
        $instance = parent::createNewInstance();
        $instance->setOrderDateTime(new DateTime());

        return $instance;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('Costumer', EntityType::class, [
                'class' => Costumer::class,
                'choice_label' => 'id',
            ])
            ->add('order_dateTime')
            ->add('ordered_item', FieldDescriptionInterface::TYPE_CURRENCY, ['currency' => 'EUR', 'data_transformer' => new MoneyToLocalizedStringTransformer(locale:'deDE'),])
            ->add('tax')
        ;
    }
}
