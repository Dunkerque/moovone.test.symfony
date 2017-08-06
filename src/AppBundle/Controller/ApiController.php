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
     * @Rest\View(serializerGroups={"movie"}, statusCode=200)
     */
    public function getAction(Request $request, $order, $dir, $page)
    {

        $qb = $this->getDoctrine()->getRepository("AppBundle:Movies")->getMovies($order, $dir);

        $routeName = "show_movies";
        $paginator = $this->get('pagination_factory')->createCollectionPagination(
            $qb,
            $request,
            $routeName,
            ["order" => $order, "dir" => $dir],
            $page
        );
        return $paginator;
    }

    /**
     * Add entity movies if error caught error return it
     *
     * @Rest\Post(
     *     path="v1/movies",
     *     name="create_movies"
     * )
     *
     * @Rest\View(statusCode=201)
     */
    public function postAction(Request $request)
    {

        $data = $this->get('jms_serializer')->deserialize($request->getContent(), 'array', 'json');
        $handler = $this->getMoviesManager()->saveMovies($data);
        if ($handler !== true) {
            return $this->view(
                $handler,
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Get service movies_manager
     *
     * @return \AppBundle\MoviesManager\MoviesManager|object
     */
    private function getMoviesManager()
    {

        return $this->get("movies_manager");
    }


    /**
     * Delete entity movies
     *
     * @Rest\Delete(
     *     path = "/v1/movies/{id}",
     *     name = "delete_movies",
     *     requirements={"id" = "\w+"}
     * )
     * @Rest\View(statusCode=204)
     */
    public function deleteAction(Request $request)
    {
        $hashid = $this->get('hashids')->decode($request->get('id'));
        $handler = $this->getMoviesManager()->deleteMovie($hashid);
        if ($handler == false) {
            return $this->view(
                sprintf("Movies with id : %d doesn't exist", $request->get('id')),
                Response::HTTP_NOT_FOUND
            );
        }

    }

}
