<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Movies;
use AppBundle\Form\MoviesType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ApiController extends FOSRestController
{
    /**
     *
     * Display movies with param (order, dir, page)
     *
     * @Rest\Get(
     *     path = "v1/movies",
     *     name = "show_movies",
     * )
     *
     * @Rest\QueryParam(
     *     name="order",
     *     default=null,
     *     description="Sort data by order name, asc or desc"
     * )
     *
     * @Rest\QueryParam(
     *     name="dir",
     *     default=null,
     *     description="Sort data by order id, asc or desc"
     * )
     * @Rest\QueryParam(
     *     name="page",
     *     requirements="\d+",
     *     default="1",
     *     description="Page of result"
     * )
     *
     * @Rest\View(serializerGroups={"movie"})
     */
    public function getAction($order, $dir, $page)
    {

        $movies = $this->getDoctrine()->getRepository("AppBundle:Movies")->listMoviesWithOrder($order, $dir);

        $adapter = new \Pagerfanta\Adapter\ArrayAdapter($movies);
        $pager = new \Pagerfanta\Pagerfanta($adapter);
        $pager->setMaxPerPage($this->getParameter("pagination")["maxPerPage"]);

        if ($page) {
            try {
                $pager->setCurrentPage($page);
            } catch (NotValidCurrentPageException $e) {
                throw new NotFoundHttpException();
            }
        }

        $result = $pager->getCurrentPageResults();
        $prevPage = ($page == 1) ? 1 : $pager->getPreviousPage();

        return [
            "total" => $pager->getNbResults(),
            "count" => count($result),
            "data" => [
                $result
            ],
            "links" => [
                "next" => $this->generateUrl('show_movies', ['page' => $pager->getNextPage()], UrlGeneratorInterface::ABSOLUTE_URL),
                "prev" => $this->generateUrl('show_movies', ['page' => $prevPage], UrlGeneratorInterface::ABSOLUTE_URL),
                "numberPage" => $pager->getNbPages(),
                "actualPage" => $pager->getCurrentPage()
            ]
        ];
    }

    /**
     * Add entity movies if error caught error return it
     *
     * @Rest\Post(
     *     path="v1/movies",
     *     name="create_movies"
     * )
     *
     * @Rest\View(statusCode=201, serializerGroups={"movie"})
     */
    public function postAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $data = $this->get('jms_serializer')->deserialize($request->getContent(), 'array', 'json');
        $movies = new Movies();
        $form = $this->get('form.factory')->create(MoviesType::class, $movies);

        $form->submit($data);
        if (!$form->isValid()) {
            $errors = $this->getErrorsFromValidator($movies);

            return $this->view(
                $errors,
                Response::HTTP_BAD_REQUEST
            );
        }
        $em->persist($movies);
        $em->flush();
    }

    /**
     * Get error from validator
     *
     * @param $entity Movies
     * @return array List error caught from the validator
     */
    private function getErrorsFromValidator($entity)
    {
        $dataErrors = array();
        $errors = $this->get('validator')->validate($entity);
        foreach ($errors as $error) {
            $dataErrors["Fields"] = $error->getPropertyPath();
            $dataErrors["Cause"] = $error->getMessage();
        }
        return $dataErrors;
    }


    /**
     * Delete entity movies
     *
     * @Rest\Delete(
     *     path = "/v1/movies/{id}",
     *     name = "delete_movies",
     *     requirements={"id" = "\w+"}
     * )
     * @Rest\View(statusCode=204, serializerGroups={"movie"})
     */
    public function deleteAction(Request $request)
    {

        $em = $this->getDoctrine();
        $hashid = $this->get('hashids')->decode($request->get('id'));

        if (empty($hashid)) {
            return $this->view(
                "This is doesn't exist",
                Response::HTTP_NOT_FOUND
            );
        }
        $movie = $em->getRepository("AppBundle:Movies")->find($hashid[0]);

        if (!$movie) {
            return $this->view(
                sprintf("Movies with id : %d doesn't exist", $request->get('id')),
                Response::HTTP_NOT_FOUND
            );
        }
        $em->getManager()->remove($movie);
        $em->getManager()->flush();
    }

}
