<?php

declare(strict_types=1);

namespace Shared\Admin;

use Doctrine\DBAL\Types\StringType;
use Shared\Entity\Costumer;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

final class CostumerAdmin extends AbstractAdmin
{
    

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('name', CallbackFilter::class, [
                'callback' => static function(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): bool {
                    if (!$data->hasValue()) {
                        return false;
                    }
                    assert($query instanceof ProxyQuery);

                    // search each space seperated key individually
                    foreach(explode(" ", $data->getValue()) as $key){
                        $query->andWhere( 
                            $query->expr()->orX(
                                $query->expr()->like($alias.'.id', $query->expr()->literal('%' . $key . '%')),
                                $query->expr()->like($alias.'.firstname', $query->expr()->literal('%' . $key . '%')),
                                $query->expr()->like($alias.'.lastname', $query->expr()->literal('%' . $key . '%'))
                        ));
                    }
                    
                    return true;
                },
            ])
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
            ->add('enddate', null, [
                'widget' => 'single_text',
                'html5' => false,
                'help' => '(Format: dd.MM.yyyy)',
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
            ->add('active')
            ->add('enddate', DatePickerType::class, [
                'widget' => 'single_text',
                'html5' => false,])
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
            ->add('enddate', null, [
                'format' => 'd.M.y',
            ])
            ->add('Barcode', 'barcode')             // custom types defined in config/packages/sonata_doctrine_orm_admin.yaml
            ->add('Department')
        ;
    }
}
