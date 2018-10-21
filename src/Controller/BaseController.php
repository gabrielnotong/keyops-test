<?php
namespace App\Controller;
use App\Exception\ResourceValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Class BaseController
 * @package App\Controller
 */
abstract class BaseController extends FOSRestController
{
    /**
     * Checks if users send a well formatted JSON
     * @param ConstraintViolationList $violations
     * @throws ResourceValidationException
     */
    protected function validateEntity(ConstraintViolationList $violations): void {
        if (count($violations)) {
            $message = "The JSON sent contains invalid data: ";
            foreach ($violations as $violation) {
                $message .= sprintf(" Field [%s]: %s", $violation->getPropertyPath(), $violation->getMessage());
            }
            throw new ResourceValidationException($message);
        }
    }

    /**
     * Checks if the entity is found or not
     * @param null $entity
     * @param string $message
     */
    protected function notFound($entity = null, string $message): void {
        if (empty($entity) || $entity == null) {
            throw $this->createNotFoundException($message);
        }
    }

    /**
     * Used to delete all entities
     * @param int $id
     * @param string $errorMsg
     * @param EntityRepository $repository
     * @param EntityManagerInterface $manager
     */
    protected function deleteEntity(int $id, string $errorMsg, EntityRepository $repository, EntityManagerInterface $manager): void {
        $entity = $repository->findOneBy(['id' => $id]);
        $this->notFound($entity, $errorMsg);
        $manager->remove($entity);
        $manager->flush();
    }
}
