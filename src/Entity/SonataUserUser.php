<?php

namespace Shared\Entity;

use Shared\Repository\SonataUserUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Sonata\UserBundle\Entity\BaseUser;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints\Choice;

#[ORM\Entity(repositoryClass: SonataUserUserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[ORM\Table(name: 'user__user')]
class SonataUserUser extends BaseUser implements UserInterface, PasswordAuthenticatedUserInterface
{
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
