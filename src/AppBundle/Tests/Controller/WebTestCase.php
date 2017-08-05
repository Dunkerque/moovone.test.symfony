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

    protected function tearDown()
    {
        parent::tearDown();
    }

}