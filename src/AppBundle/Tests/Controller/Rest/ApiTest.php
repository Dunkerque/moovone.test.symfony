<?php
/**
 * Created by PhpStorm.
 * User: danny
 * Date: 04/08/2017
 * Time: 00:38
 */

namespace AppBundle\Tests\Controller\WebTestCase;

use AppBundle\Tests\Controller\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiTest extends WebTestCase
{

    const IDMOVIE = "9WwGlnjNbjeVqzv";

    /**
     * Test method get
     */
    public function testGET()
    {

        $this->client->request('GET', $this->hostname);
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJsonGETResponse($data);
    }

    /**
     * Test method get with param order
     */
    public function testGETWithParamOrderASC()
    {

        $this->client->request('GET', $this->hostname, ["order" => "name"]);
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJsonGETResponse($data);
        $this->assertEquals("12 Angry Men", $data["data"][0]["name"]);
        $this->assertEquals("Crocodile Dundee", $data["data"][29]["name"]);

        $this->client->request('GET', $this->hostname, ["order" => "name"]);
        $data = json_decode($this->client->getResponse()->getContent(), true);


        $this->assertJsonGETResponse($data);
        $this->assertEquals("12 Angry Men", $data["data"][0]["name"]);
        $this->assertEquals("Crocodile Dundee", $data["data"][29]["name"]);
    }


    /**
     * Test method get with param dir
     */
    public function testGETWithParamDir()
    {

        $this->client->request('GET', $this->hostname, ["dir" => "asc"]);
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJsonGETResponse($data);

        $this->assertEquals("Pulp Fiction", $data["data"][29]["name"]);

        $this->client->request('GET', $this->hostname, ["dir" => "desc"]);
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJsonGETResponse($data);
        $this->assertEquals("Beetlejuice", $data["data"][0]["name"]);
        $this->assertEquals("Pulp Fiction", $data["data"][29]["name"]);
    }

    /**
     * Test method get with pagiantion
     */
    public function testGETWithPagination()
    {

        $this->client->request('GET', $this->hostname, ["page" => "2"]);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $currentPage = (int)explode("&page=", $data['link']['self'])[1];

        $this->asserJsonPaginationResponse($data, $this->client->getResponse());
        $this->assertEquals(2, $currentPage);
    }

    /**
     * Test method post
     */
    public function testPOST()
    {

        $movies = [
            "name" => "Spawn"
        ];
        $this->client->request('POST', $this->hostname, [], [], ["CONTENT_TYPE" => "application/json"], json_encode($movies));
        $this->assertJsonResponse($this->client->getResponse(), Response::HTTP_CREATED);

        $item = $this->em->getRepository("AppBundle:Movies")->findOneBy(["name" => "Spawn"]);
        $this->em->remove($item);
        $this->em->flush();

        $this->client->request('GET', $this->hostname);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(146, $data["total"]);

    }

    /**
     * Test method delete
     */
    public function testDELETE()
    {
        $this->client->request('DELETE', $this->hostname . "/" . self::IDMOVIE);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());

        $item = $this->em->getRepository("AppBundle:Movies")->findAll();
        $this->assertEquals(145, count($item));
    }

    /**
     * Test method post with no data it will caught error from validator
     */
    public function testPOSTWithEmptyData()
    {
        $movies = [
            "name" => ""
        ];
        $this->client->request('POST', $this->hostname, [], [], ["CONTENT_TYPE" => "application/json"], json_encode($movies));

        $error = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJsonPostWithoutData($error);

    }

    /**
     * Test method delete with id not exist
     */
    public function testDELETEWithError()
    {
        $this->client->request('DELETE', $this->hostname . "/" . "testidnotexist", [], [], []);
        $this->assertJsonDeleteWithError();
    }


}