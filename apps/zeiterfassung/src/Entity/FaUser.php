<?php

namespace Zeiterfassung\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Choice;
use Shared\Entity\Costumer;
use Shared\Entity\SonataUserUser;

#[ORM\Entity]
class FaUser extends SonataUserUser
{
    public function __construct()
    {
        $this->addRole('ROLE_FA');
    }

    #[Choice(choices: Costumer::DEPARTMENTS, message: '{{ value }} not a valid department. Possible departments: {{ choices }}')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $department = null;

    public function getDepartment():  ?string{
        return $this->department;
    }

    public function setDepartment(string $department):  void{
        $this->department = $department;
    }
}
