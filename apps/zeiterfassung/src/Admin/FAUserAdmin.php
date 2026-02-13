<?php

declare(strict_types=1);

namespace Zeiterfassung\Admin;

use Shared\Entity\Costumer;
use Shared\Entity\SonataUserUser;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\UserBundle\Admin\Model\UserAdmin;
use Sonata\UserBundle\Form\Type\RolesMatrixType;
use Sonata\UserBundle\Form\Type\SecurityRolesType;
use Sonata\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;


// custom admin to add Department
final class FAUserAdmin extends UserAdmin
{

    protected function configureListFields(ListMapper $list): void
    {
        parent::configureListFields($list);
        $list->add('Department');

    }

    protected function configureFormFields(FormMapper $form): void
    {
        parent::configureFormFields($form);
        $form
            ->with('general', ['class' => 'col-md-4'])
                ->add('Department', ChoiceType::class, [
                    'choices' => Costumer::DEPARTMENTS,
                    'required' => false,
                    'data' => ''
                ])
            ->end()
            ->remove('roles')
            ->remove('realRoles');

    }
}