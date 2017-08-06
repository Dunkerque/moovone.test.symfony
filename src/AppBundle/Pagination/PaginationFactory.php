<?php
/**
 * Created by PhpStorm.
 * User: danny
 * Date: 06/08/2017
 * Time: 15:50
 */

namespace AppBundle\Pagination;


use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouterInterface;

class PaginationFactory
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var array
     */
    private $maxPerpage;

    public function __construct(RouterInterface $router, array $maxPerPage)
    {

        $this->router = $router;
        $this->maxPerpage = $maxPerPage["maxPerPage"];
    }

    /**
     * Create array with result of movies formatted like  => [
     * "data" =>  {
     *     "id": "VvmazxjQLyGMQRd",
     *     "name": "The Cotton Club"
     *  },
     * "total" => 100,
     * "count" => 500,
     * "link": {
     *      "self": "http://localhost:8080/v1/movies?order=&dir=&page=1",
     *      "first": "http://localhost:8080/v1/movies?order=&dir=&page=1",
     *      "last": "http://localhost:8080/v1/movies?order=&dir=&page=5",
     *      "next": "http://localhost:8080/v1/movies?order=&dir=&page=2"
     *   }
     * ]
     *
     * @param QueryBuilder $qb
     * @param Request $request
     * @param $route
     * @param array $routeParams
     * @return array
     */
    public function createCollectionPagination(QueryBuilder $qb, Request $request, $route, array $routeParams = array(), $page)
    {

        $adapter = new DoctrineORMAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($this->maxPerpage);
        $pagerfanta->setCurrentPage($page);

        $paginatedCollection = new PaginationCollection($pagerfanta->getCurrentPageResults(), $pagerfanta->getNbResults());

        $createLinkUrl = function ($targetPage) use ($route, $routeParams) {
            return $this->router->generate($route, array_merge(
                $routeParams,
                array('page' => $targetPage)
            ), UrlGenerator::ABSOLUTE_URL);
        };
        $paginatedCollection->addLink('self', $createLinkUrl($page));
        $paginatedCollection->addLink('first', $createLinkUrl(1));
        $paginatedCollection->addLink('last', $createLinkUrl($pagerfanta->getNbPages()));
        if ($pagerfanta->hasNextPage()) {
            $paginatedCollection->addLink('next', $createLinkUrl($pagerfanta->getNextPage()));
        }
        if ($pagerfanta->hasPreviousPage()) {
            $paginatedCollection->addLink('prev', $createLinkUrl($pagerfanta->getPreviousPage()));
        }
        return get_object_vars($paginatedCollection);
    }
}