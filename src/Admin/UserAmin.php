<?php

declare(strict_types=1);

namespace Shared\Admin;

use Shared\Entity\Costumer;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\UserBundle\Admin\Model\UserAdmin as ModelUserAdmin;
use Sonata\UserBundle\Form\Type\RolesMatrixType;
use Sonata\UserBundle\Form\Type\SecurityRolesType;
use Sonata\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;


// custom admin to add Department
final class UserAmin extends ModelUserAdmin
{

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('username')
            ->add('email')
            ->add('enabled', null, ['editable' => true])
            ->add('Department')
            ->add('createdAt');

        if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $list
                ->add('impersonating', FieldDescriptionInterface::TYPE_STRING, [
                    'virtual_field' => true,
                    'template' => '@SonataUser/Admin/Field/impersonating.html.twig',
                ]);
        }

        $list->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, [
            'translation_domain' => 'SonataAdminBundle',
            'actions' => [
                'edit' => [],
            ],
        ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('general', ['class' => 'col-md-4'])
                ->add('username')
                ->add('email')
                ->add('Department', ChoiceType::class, [
                    'choices' => Costumer::DEPARTMENTS,
                ])
                ->add('plainPassword', PasswordType::class, [
                    'required' => (!$this->hasSubject() || null === $this->getSubject()->getId()),
                ])
                ->add('enabled', null)
            ->end()
            ->with('roles', ['class' => 'col-md-8'])
                ->add('realRoles', RolesMatrixType::class, [
                    'label' => false,
                    'multiple' => true,
                    'required' => false,
                ])
            ->end();

    }
}