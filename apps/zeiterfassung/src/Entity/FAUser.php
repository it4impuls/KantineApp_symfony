<?php

namespace Zeiterfassung\Entity;

use Shared\Repository\SonataUserUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Sonata\UserBundle\Entity\BaseUser;
use Doctrine\DBAL\Types\Types;
use Shared\Entity\Costumer;
use Symfony\Component\Validator\Constraints\Choice;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[ORM\Table(name: 'user__fa')]
class FAUser extends BaseUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct()
    {
        $this->addRole('ROLE_FA');
    }


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    #[Choice(choices: Costumer::DEPARTMENTS, message: '{{ value }} not a valid department. Possible departments: {{ choices }}')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Department = null;

    public function getDepartment():  ?string{
        return $this->Department;
    }

    public function setDepartment(string $Department):  void{
        $this->Department = $Department;
    }
}
