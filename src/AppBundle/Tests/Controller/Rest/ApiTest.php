<?php
/**
 * Created by PhpStorm.
 * User: danny
 * Date: 04/08/2017
 * Time: 00:38
 */

use AppBundle\Tests\Controller\WebTestCase;

class ApiTest extends WebTestCase
{

    const IDMOVIE = "9WwGlnjNbjeVqzv";


    /**
     * Test method get
     */
    public function testGET()
    {

        $this->client->request('GET', $this->hostname, [], [], []);
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals("application/json", $this->client->getResponse()->headers->get("content-type"));
        $this->assertArrayHasKey("count", $data);
        $this->assertArrayHasKey("total", $data);
        $this->assertArrayHasKey("data", $data);
        $this->assertEquals($this->container->getParameter("pagination")["maxPerPage"], $data["count"]);
    }

    /**
     * Test method get with param order
     */
    public function testGETWithParamOrderASC()
    {

        $this->client->request('GET', $this->hostname, ["order" => "asc"], [], []);
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals("application/json", $this->client->getResponse()->headers->get("content-type"));
        $this->assertArrayHasKey("count", $data);
        $this->assertEquals("12 Angry Men", $data["data"][0][0]["name"]);
        $this->assertEquals("Crocodile Dundee", $data["data"][0][29]["name"]);

        $this->client->request('GET', $this->hostname, ["order" => "desc"], [], []);
        $data = json_decode($this->client->getResponse()->getContent(), true);


        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals("application/json", $this->client->getResponse()->headers->get("content-type"));
        $this->assertArrayHasKey("count", $data);
        $this->assertEquals("Whiplash", $data["data"][0][0]["name"]);
        $this->assertEquals("The Hobbit: An Unexpected Journey", $data["data"][0][29]["name"]);
    }


    /**
     * Test method get with param dir
     */
    public function testGETWithParamDir()
    {

        $this->client->request('GET', $this->hostname, ["dir" => "asc"], [], []);
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals("application/json", $this->client->getResponse()->headers->get("content-type"));
        $this->assertArrayHasKey("count", $data);

        $this->assertEquals("Pulp Fiction", $data["data"][0][29]["name"]);

        $this->client->request('GET', $this->hostname, ["dir" => "desc"], [], []);
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals("application/json", $this->client->getResponse()->headers->get("content-type"));
        $this->assertArrayHasKey("count", $data);
        $this->assertEquals("The Big Short", $data["data"][0][0]["name"]);
        $this->assertEquals("Big Nothing", $data["data"][0][29]["name"]);


    }

    /**
     * Test method get with pagiantion
     */
    public function testGETWithPagination()
    {

        $this->client->request('GET', $this->hostname, ["page" => "2"], [], []);
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals("application/json", $this->client->getResponse()->headers->get("content-type"));
        $this->assertArrayHasKey("actualPage", $data["links"]);
        $this->assertEquals(2, $data["links"]["actualPage"]);

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
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        $item = $this->em->getRepository("AppBundle:Movies")->findOneBy(["name" => "Spawn"]);
        $this->em->remove($item);
        $this->em->flush();

        $this->client->request('GET', $this->hostname, [], [], []);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(146, $data["total"]);

    }


    /**
     * Test method delete
     */
    public function testDELETE()
    {

        $this->client->request('DELETE', $this->hostname . "/" . self::IDMOVIE, [], [], []);
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());

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

        $this->assertInternalType("array", $error);
        $this->assertEquals("400", $this->client->getResponse()->getStatusCode());
        $this->assertEquals("name", $error["Fields"]);
        $this->assertEquals("the field cannot be empty", $error["Cause"]);

    }

    /**
     * Test method delete with id not exist
     */
    public function testDELETEWithError()
    {
        $this->client->request('DELETE', $this->hostname . "/" . "testidnotexist", [], [], []);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
        $this->assertInternalType("string", $this->client->getResponse()->getContent());
    }

}