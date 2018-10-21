<?php
namespace  tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    /**
     * Tests if we can access users
     * @dataProvider urisGetProvider
     * @param array $params
     */
    public function testGetUsers(array $params)
    {
        $client = static::createClient();
        $client->request($params['method'], $params['uri']);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Provides Get uris
     * @return array
     */
    public function urisGetProvider()
    {
        return [
            [["uri" => "/api/users", "method" => "GET"]],
            [["uri" => "/api/users/1", "method" => "GET"]]
        ];
    }
}
