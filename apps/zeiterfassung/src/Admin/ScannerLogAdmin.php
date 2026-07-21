<?php

declare(strict_types=1);

namespace Zeiterfassung\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\DateFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\DatePickerType;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ScannerLogAdmin extends AbstractAdmin
{
    public function __construct( private TranslatorInterface $translator){}

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('level')
            ->add('message')
            ->add('scanner', ModelFilter::class, [
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => 'uname',
                    'minimum_input_length' => 1,
                ]
            ])
            ->add('scanner.id')
            ->add('timeStamp', DateFilter::class, [
                'field_type' => DatePickerType::class,
                // 'label' => $this->translator->trans('date'),
                // 'field_name' => 'timeStamp'
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id')
            ->add('scanner')
            ->add('level')
            ->add('message')
            ->add('timeStamp')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('id')
            ->add('level')
            ->add('message')
            ->add('timeStamp')
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('level')
            ->add('message')
            ->add('timeStamp')
        ;
    }
}
