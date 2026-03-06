<?php

declare(strict_types=1);

namespace Zeiterfassung\Admin;

use Shared\Entity\Costumer;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\UserBundle\Admin\Model\UserAdmin;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


// custom admin to add Department
final class FaUserAdmin extends UserAdmin
{

    protected function configureListFields(ListMapper $list): void
    {
        parent::configureListFields($list);
        $list->add('department');

    }

    protected function configureFormFields(FormMapper $form): void
    {
        parent::configureFormFields($form);
        $form
            ->with('general', ['class' => 'col-md-4'])
                ->add('department', ChoiceType::class, [
                    'choices' => Costumer::DEPARTMENTS,
                    'required' => true,
                    'data' => ''
                ])
            ->end()
            ->remove('roles')
            ->remove('realRoles');

    }
}