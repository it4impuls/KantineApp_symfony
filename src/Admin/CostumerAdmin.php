<?php

declare(strict_types=1);

namespace Shared\Admin;

use Shared\Entity\Costumer;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


final class CostumerAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('firstname')
            ->add('lastname')
            ->add('active', null, [
                'editable' => true,
                'inverse'  => true,
            ])
            ->add('enddate')
            ->add('Department', ChoiceFilter::class, [
            'field_type' => ChoiceType::class,
            'field_options' => [
                'choices' => Costumer::DEPARTMENTS
            ]
        ]);
            // ->add('Barcode', FieldDescriptionInterface::TYPE_HTML/*, ["required" => false, ['help' => '<img src="' . $this->getSubject()->getBarcode() . '" />']]*/)
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, ['read'])
            ->add('firstname')
            ->add('lastname')
            ->add('active', null, [
                'editable' => true
            ])
            ->add('Department', null, [
                'choices' => Costumer::DEPARTMENTS,
            ])
            ->add('enddate')
            ->add('Barcode', 'barcode')                         // custom types defined in config/packages/sonata_doctrine_orm_admin.yaml
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureExportFields(): array
    {
        return ['id', 'firstname', 'lastname', 'Department', 'active'];
    }


    protected function configureBatchActions(array $actions): array
    {
        if (
            $this->hasRoute('edit') && $this->hasAccess('list')
        ) {
            $actions['barcodes'] = [
                'ask_confirmation' => false,
                'controller' => 'Shared\Controller\CostumerCRUDController::batchActionBarcodes',
            ];
        }

        return $actions;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            // ->add('id')
            ->add('firstname')
            ->add('lastname')
            ->add('active', null, ['data' => true])
            ->add('enddate')
            ->add('Department', ChoiceType::class, [
                'choices' => Costumer::DEPARTMENTS,
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('firstname')
            ->add('lastname')
            ->add('active')
            ->add('enddate')
            ->add('Barcode', 'barcode')             // custom types defined in config/packages/sonata_doctrine_orm_admin.yaml
            ->add('Department')
        ;
    }
}
