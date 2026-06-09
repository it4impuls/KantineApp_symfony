<?php

declare(strict_types=1);

namespace Shared\Admin;

use Shared\Entity\Costumer;
use Shared\Entity\Tags;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Translation\TranslatableMessage;



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
            'choices_as_values' => true,
            'field_options' => [
                'choices' => Costumer::DEPARTMENTS,
                'choice_label'=>function (mixed $value): TranslatableMessage|string|null {
                    return $value;
                },
                // "expanded" => true,
                "multiple" => true,
            ],
        ])
        ->add(
            'tags',
            ModelFilter::class,
            [
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => 'name',
                    'minimum_input_length' => 1,
                ]
            ]
        );
    }

    protected function configureListFields(ListMapper $list): void
    {
        // ModelManagerInterface;

        $list
            ->add('id', null, ['read', ])
            ->add('firstname')
            ->add('lastname')
            ->add('active', null, [
                'editable' => true
            ])
            ->add('Department', 
            FieldDescriptionInterface::TYPE_CHOICE, 
            [
                'choices' => Costumer::DEPARTMENTS,
                'choice_translation_domain' => 'messages', 
                'multiple' => true,
                'editable' => true,
            ])
            ->add('tags', 
            FieldDescriptionInterface::TYPE_MANY_TO_MANY, 
            [
                'multiple' => true,
                'class' => Tags::class,
                'associated_property' => 'name',
            ])
            ->add('enddate', null, [
                'widget' => 'single_text',
                'html5' => false,
                'help' => '(Format: dd.mm.yyyy)',
                'format' => 'd.M.Y'])
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

            $actions['export_names'] = [
                'label' => 'Teilnehmer (XLSX)',
                'ask_confirmation' => false,
                'controller' => 'Shared\Controller\CostumerCRUDController::batchActionExportNames',
            ];
        }

        return $actions;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('firstname')
            ->add('lastname')
            ->add('active', null, ['data' => true])
            ->add('enddate', DatePickerType::class, [
                'widget' => 'single_text',
                'html5' => false,])
            ->add('Department', ChoiceType::class, [
                'choices' => Costumer::DEPARTMENTS,
            ])
            // TODO: editing tags in costumer doesnt persist for some reason and I dont have the patience to try to debug.

            //  ->add('tags', 
            //     ModelType::class, [
            //     // ModelAutocompleteType::class, [
            //     // 'minimum_input_length' => 1,
            //     // 'expanded' => true, 
            //     'label' => 'Tags',
            //     'required' => true,
            //     'multiple' => true,
            //     'placeholder' => 'Select tags',
            //     'btn_add'=>'+',
            //     'property' => 'name',
            // ])
            
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('firstname')
            ->add('lastname')
            ->add('active')
            ->add('enddate', null, [
                'format' => 'd.M.y',
            ])
            ->add('Barcode', 'barcode')             // custom types defined in config/packages/sonata_doctrine_orm_admin.yaml
            ->add('Department')
        ;
    }
}
