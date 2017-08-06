<?php
/**
 * Created by PhpStorm.
 * User: danny
 * Date: 04/08/2017
 * Time: 22:32
 */

namespace AppBundle\Tests\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as baseWebTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Response;


abstract class WebTestCase extends baseWebTestCase
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Registry
     */
    protected $doctrine;


    public function setUp()
    {
        $this->client = static::createClient();
        $this->client->getKernel()->boot();
        $this->container = $this->client->getContainer();
        $this->hostname = $this->container->getParameter("hostname");
        $this->doctrine = $this->container->get('doctrine');
        $this->em = $this->doctrine->getManager();
    }

    /**
     * @param Response $response
     * @param string $status
     */
    public function assertJsonResponse($response, $status)
    {

        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals($status, $response->getStatusCode());
    }

    public function assertJsonGETResponse($data)
    {
        $this->assertArrayHasKey("count", $data);
        $this->assertArrayHasKey("total", $data);
        $this->assertArrayHasKey("data", $data);
        $this->assertJsonResponse($this->client->getResponse(), Response::HTTP_OK);
        $this->assertEquals($this->container->getParameter("pagination")["maxPerPage"], $data["count"]);
    }

    public function asserJsonPaginationResponse($data, $response)
    {
        $this->assertJsonGETResponse($data);
        $this->assertArrayHasKey("self", $data["link"]);
        $this->assertArrayHasKey("next", $data["link"]);
        $this->assertArrayHasKey("last", $data["link"]);
        $this->assertArrayHasKey("prev", $data["link"]);
    }

    public function assertJsonPostWithoutData($error)
    {
        $this->assertInternalType("array", $error);
        $this->assertJsonResponse($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
        $this->assertEquals("name", $error["Fields"]);
        $this->assertEquals("the field cannot be empty", $error["Cause"]);
    }

    public function assertJsonDeleteWithError()
    {

        $this->assertJsonResponse($this->client->getResponse(), Response::HTTP_NOT_FOUND);
        $this->assertInternalType("string", $this->client->getResponse()->getContent());
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

}