<?php
/**
 * Created by PhpStorm.
 * User: danny
 * Date: 06/08/2017
 * Time: 17:46
 */

namespace AppBundle\MoviesManager;


use AppBundle\Entity\Movies;
use AppBundle\Form\MoviesType;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\View\View;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MoviesManager
{

    /**
     * @var FormFactory
     */
    private $form;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var ValidatorInterface
     */
    private $validator;


    public function __construct(FormFactory $form, ManagerRegistry $doctrine, ValidatorInterface $validator)
    {

        $this->form = $form;
        $this->doctrine = $doctrine;
        $this->validator = $validator;
    }

    /**
     * Register movies or caught error and return it
     *
     * @param string $data name of movies
     *
     * @return bool|array true if register was succedeed if not return array of errors
     */
    public function saveMovies($data)
    {

        $em = $this->doctrine->getManager();
        $movies = new Movies();
        $form = $this->form->create(MoviesType::class, $movies);
        $form->submit($data);

        if (!$form->isValid()) {
            $errors = $this->getErrorsFromValidator($movies);
            return $errors;
        }

        $em->persist($movies);
        $em->flush();
        return true;
    }

    /**
     * Get error from validator
     *
     * @param Movies $entity
     * @return array List error caught from the validator
     */
    private function getErrorsFromValidator($entity)
    {
        $dataErrors = [];
        $errors = $this->validator->validate($entity);
        foreach ($errors as $error) {
            $dataErrors["Fields"] = $error->getPropertyPath();
            $dataErrors["Cause"] = $error->getMessage();
        }
        return $dataErrors;
    }

    /**
     * Soft delete movie
     *
     * @param array $data id of entity
     *
     * @return bool true if delete was succedeed otherwise false
     */
    public function deleteMovie($data)
    {

        if (empty($data))
            return false;

        $movie = $this->doctrine->getRepository("AppBundle:Movies")->find($data[0]);
        $em = $this->doctrine->getManager();

        if (!$movie)
            return false;

        $em->remove($movie);
        $em->flush();
        return true;
    }
}