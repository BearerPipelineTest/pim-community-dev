<?php
namespace Pim\Bundle\ConfigBundle\Tests\Functional\Controller;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ChannelControllerTest extends ControllerTest
{
    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testIndex($locale)
    {
        $uri = '/'. $locale .'/config/channel/index';

        // assert without authentication
        $client = static::createClient();
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        // assert with authentication
        $client = static::createAuthenticatedClient();
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testCreate($locale)
    {
        $uri = '/'. $locale .'/config/channel/create';

        // assert without authentication
        $client = static::createClient();
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        // assert with authentication
        $client = static::createAuthenticatedClient();
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testEdit($locale)
    {
        // initialize authentication to call container and get channel entity
        $client = static::createClient();
        $channel = $this->getRepository()->findOneBy(array());
        $uri = '/'. $locale .'/config/channel/edit/'. $channel->getId();

        // assert without authentication
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        // assert with authentication
        $client = static::createAuthenticatedClient();
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // assert with unknown channel id
        $uri = '/'. $locale .'/config/channel/edit/0';
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testRemove($locale)
    {
        // initialize authentication to call container and get channel entity
        $client = static::createClient();
        $channel = $this->getRepository()->findOneBy(array());
        $uri = '/'. $locale .'/config/channel/remove/'. $channel->getId();

        // assert without authentication
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        // assert with authentication
        $client = static::createAuthenticatedClient();
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        // assert with unknown channel id (last removed)
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * Get tested entity repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        return $this->getStorageManager()->getRepository('PimConfigBundle:Channel');
    }
}
