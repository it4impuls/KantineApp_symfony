<?php

namespace App\Controller\Admin;

use App\Entity\Costumer;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CostumerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Costumer::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('id')->set,
            // fieldsets usually display only a title
            FormField::addFieldset('User Details'),
            TextField::new('firstName'),
            TextField::new('lastName'),

            // fieldsets without titles only display a separation between fields
            FormField::addFieldset(),
            DateTimeField::new('enddate')->onlyOnDetail(),

            // fieldsets can also define their icon, CSS class and help message
            BooleanField::new('active'),

            ImageField::new('Barcode')->onlyOnDetail()
        ];
    }

    // public function configureActions(Actions $actions): Actions
    // {
    //     $viewAction = Action::new('viewView', 'View')->linkToUrl(function (Foo $entity) {
    //         return $entity->getUrl();
    //     });

    //     // These are displayed in reverse order!
    //     return $actions
    //         ->add(Crud::PAGE_INDEX, $secondAction)
    //         ->add(Crud::PAGE_INDEX, $viewAction)
    //         ->add(Crud::PAGE_EDIT, $viewAction)
    //     ;
    // }
}
