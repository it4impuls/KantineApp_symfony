<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

final class CostumerAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        // $options = array('required' => false);
        // if (($subject = $this->getSubject()) && $subject->getPhoto()) {
        //     $path = $subject->getPhotoWebPath();
        //     $options['help'] = '<img src="' . $path . '" />';
        // }
        $filter
            ->add('id')
            ->add('firstname')
            ->add('lastname')
            ->add('active', null, [
                'editable' => true,
                'inverse'  => true,
            ])
            ->add('enddate')
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
            ->add('enddate')
            ->add('Barcode', 'barcode')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureExportFields(): array
    {
        return ['id', 'firstname', 'lastname'];
    }


    protected function configureBatchActions(array $actions): array
    {
        if (
            $this->hasRoute('edit') && $this->hasAccess('list')
        ) {
            $actions['barcodes'] = [
                'ask_confirmation' => false,
                // 'controller' => 'App/Controller/CostumerController',
                // Or 'App/Controller/MergeController::batchMergeAction' base on how you declare your controller service.
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
            ->add('active')
            ->add('enddate')

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
            ->add('Barcode', 'barcode')
        ;
    }
}
