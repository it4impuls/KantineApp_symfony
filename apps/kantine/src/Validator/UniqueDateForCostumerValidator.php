<?php

namespace Kantine\Validator;

use Kantine\Repository\OrderRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class UniqueDateForCostumerValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ManagerRegistry $registry,
    ) {}


    // adapted from Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator::validate
    #[\Override]
    public function validate(mixed $entity, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueDateForCostumer) {
            throw new UnexpectedTypeException($constraint, UniqueDateForCostumer::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $entity || '' === $entity) {
            return;
        }

        $em = $this->registry->getManagerForClass($entity::class);

        if (!$em) {
            throw new ConstraintDefinitionException(\sprintf('Unable to find the object manager associated with an entity of class "%s".', get_debug_type($entity)));
        }
        $class = $em->getClassMetadata($entity::class);

        if (!$class->hasField($constraint->datefield) && !$class->hasAssociation($constraint->datefield)) {
            throw new ConstraintDefinitionException(\sprintf('Class does not have field "%s".', $constraint->datefield));
        }

        if (!$class->hasField($constraint->costumerfield) && !$class->hasAssociation($constraint->costumerfield)) {
            throw new ConstraintDefinitionException(\sprintf('Class does not have field "%s".', $constraint->costumerfield));
        }

        $repository = $em->getRepository($entity::class);

        if (!$repository instanceof OrderRepository) {
            throw new ConstraintDefinitionException(\sprintf('Class must use "%s".', OrderRepository::class));
        }

        if (property_exists($class, 'propertyAccessors')) {
            $date = $class->propertyAccessors[$constraint->datefield]->getValue($entity);
        } else {
            $date = $class->reflFields[$constraint->datefield]->getValue($entity);
        }

        if (property_exists($class, 'propertyAccessors')) {
            $costumer = $class->propertyAccessors[$constraint->costumerfield]->getValue($entity);
        } else {
            $costumer = $class->reflFields[$constraint->costumerfield]->getValue($entity);
        }


        $result = $repository->findCostumerOrderAtDate($costumer, $date);

        // if there are no Orders at that date or the order at that date is the same as one validating
        if (!$result || $result->getId() == $entity->getId()) {
            return;
        }
        // $result = $repository->findOneBy([$constraint->costumerfield=>$entity->costmumer])

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ Costumer }}', $costumer->getId())
            ->setParameter('{{ date }}', $date->format('d.m.Y h:m:s'))
            ->addViolation();
    }
}
