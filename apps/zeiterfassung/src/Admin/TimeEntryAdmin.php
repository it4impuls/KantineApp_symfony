<?php

namespace Zeiterfassung\Admin;

use Zeiterfassung\Entity\FaUser;
use Shared\Entity\Costumer;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage;

use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateTimePickerType;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;


final class TimeEntryAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'admin_time_entry';
    protected $baseRoutePattern = 'attendance';
    public function __construct(private UsageTrackingTokenStorage $ts)
    {
    }

    // -------------------------------------------------------------------
    // Helper to avoid duplicate user join in filters
    // -------------------------------------------------------------------

    private function costumerToStr(Costumer $user): string
    {
        if (!$user instanceof Costumer) {
            return (string)$user;
        }
        $dept = $user->getDepartment() ?? 'No Dept';
        return sprintf('[%s] %s', $dept, $user->getFullName());
    }

    private function ensureUserJoin(QueryBuilder $qb, string $alias): void
    {
        $joins = $qb->getDQLPart('join');
        if (isset($joins[$alias])) {
            foreach ($joins[$alias] as $join) {
                if ($join->getAlias() === 'u') {
                    return;
                }
            }
        }
        $qb->leftJoin("$alias.user", "u");
    }

    // -------------------------------------------------------------------
    // BATCH ACTIONS
    // -------------------------------------------------------------------

    protected function configureBatchActions(array $actions): array
    {
        if (isset($actions['delete'])) {
            unset($actions['delete']);
        }

        return $actions;
    }

    // -------------------------------------------------------------------
    // TEMPLATES
    // -------------------------------------------------------------------

    protected function configureTemplates(): array
    {
        return [
            'list' => 'admin/_auto_refresh_list.html.twig',
        ];
    }

    // -------------------------------------------------------------------
    // FORM
    // -------------------------------------------------------------------

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('user', ModelAutocompleteType::class, [
                'label' => 'User',
                'btn_add' => false,
                'required' => true,
                'placeholder' => 'Select user',
                'property' => ['firstname', 'lastname'],
                'minimum_input_length' => 1,
                'to_string_callback' => fn($user, $property) =>  $this->costumerToStr($user),
                'constraints' => [
                    new NotNull([
                        'message' => 'Please select a user.',
                    ]),
                ],
            ])
            ->add('checkinTime', DateTimePickerType::class, [
                'label' => 'Check-in',
                'widget' => 'single_text',
                'html5' => false,
                'help' => '(Format: dd.mm.yyyy hh:mm)',
                'format' => 'dd.MM.yyyy HH:mm',
                'required' => true,
                'datepicker_options' => [
                    'allowInputToggle' => true,

                ],
            ])
            ->add('checkoutTime', DateTimePickerType::class, [
                'label' => 'Check-out',
                'widget' => 'single_text',
                'html5' => false,
                'help' => '(Format: dd.mm.yyyy hh:mm)',
                'format' => 'dd.MM.yyyy HH:mm',
                'required' => false,
                'datepicker_options' => [
                    'allowInputToggle' => true,

                ],
            ]);
    }

    // -------------------------------------------------------------------
    // FILTERS
    // -------------------------------------------------------------------
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        // user
        $filter->add(
            'user',
            ModelFilter::class,
            [
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => ['firstname', 'lastname'],
                    'minimum_input_length' => 1,
                    'to_string_callback' => function ($user, $property) {
                        return $this->costumerToStr($user);
                    },
                ]
            ]
        );

        $filter->add('user.Department', ChoiceFilter::class, [
            'field_type' => ChoiceType::class,
            'field_options' => [
                'choices' => Costumer::DEPARTMENTS
            ]
        ]);

        $filter->add('missingCheckinCheckout', CallbackFilter::class, [
            'label' => 'Missing Check-in/Out',
            'field_type' => CheckboxType::class,
            'callback' => function ($qb, $alias, $field, $value) {
                if (!$value || !$value->hasValue() || $value->getValue() !== true) return false;
                $qb->andWhere("$alias.checkinTime IS NULL OR $alias.checkoutTime IS NULL");
                return true;
            },
        ]);

        $filter->add('today', CallbackFilter::class, [
            'label' => 'Today only',
            'field_type' => CheckboxType::class,
            'callback' => function ($qb, $alias, $field, $value) {
                if (!$value || !$value->hasValue() || $value->getValue() !== true) return false;
                $todayStart = new \DateTime('today');
                $todayEnd   = new \DateTime('tomorrow');
                $qb->andWhere("$alias.checkinTime BETWEEN :ts AND :te")
                    ->setParameter('ts', $todayStart)
                    ->setParameter('te', $todayEnd);
                return true;
            },
        ]);

        $filter->add('fromDate', CallbackFilter::class, [
            'label' => 'From Date',
            'field_type' => DatePickerType::class,
            'field_options' => [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'html5' => false,
                'attr' => ['class' => 'custom-datepicker'],
                'datepicker_options' => [
                    'allowInputToggle' => true,

                ],
            ],
            'callback' => function ($qb, $alias, $field, $value) {
                if (!$value || !$value->hasValue()) return false;

                $from = $value->getValue();
                $qb->andWhere("$alias.checkinTime >= :from")
                    ->setParameter('from', $from);

                return true;
            }
        ]);

        $filter->add('toDate', CallbackFilter::class, [
            'label' => 'To Date',
            'field_type' => DatePickerType::class,
            'field_options' => [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'html5' => false,
                'attr' => ['class' => 'custom-datepicker'],
                'datepicker_options' => [
                    'allowInputToggle' => true,

                ],
            ],
            'callback' => function ($qb, $alias, $field, $value) {
                if (!$value || !$value->hasValue()) return false;

                $to = $value->getValue();
                $qb->andWhere("$alias.checkinTime <= :to")
                    ->setParameter('to', $to);

                return true;
            }
        ]);
    }

    

    // -------------------------------------------------------------------
    // LIST VIEW
    // -------------------------------------------------------------------
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('user', null, [
                'label' => 'Name',
                'associated_property' => 'fullName',
            ])
            ->add('user.department', null, ['label' => 'Department'])
            ->add('checkinTime', null, ['label' => 'Check-in', 'format' => 'd.m.Y - H:i:s'])
            ->add('checkoutTime', null, ['label' => 'Check-out', 'format' => 'd.m.Y - H:i:s'])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'label' => 'Actions',
                'actions' => ['edit' => [], 'delete' => []],
            ]);
    }

    // -------------------------------------------------------------------
    // SHOW VIEW
    // -------------------------------------------------------------------
    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('user.fullName', null, ['label' => 'Name'])
            ->add('user.department', null, ['label' => 'Department'])
            ->add('checkinTime', null, ['label' => 'Check-in'])
            ->add('checkoutTime', null, ['label' => 'Check-out']);
    }

    // -------------------------------------------------------------------
    // DEFAULT SORTING + TODAY FILTER
    // -------------------------------------------------------------------
    // protected $datagridValues = [
    //     '_sort_order' => 'DESC',
    //     '_sort_by'    => 'checkinTime',
    //     'today' => ['type' => null, 'value' => true],
    // ];

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::SORT_BY] = 'id';
    }

    //super annoying, dont do this, cant apply any other filters
    // protected function configureDefaultFilterValues(array &$filterValues): void
    // {
    //     $user = $this->ts->getToken()->getUser();
    //     if(!$user instanceof FaUser) return;
    //     $filterValues['user__Department'] = [
    //         'value' => $user->getDepartment(),
    //     ];
    //     $filterValues['today'] = [
    //         'value' => 1,
    //     ];
    // }
}
