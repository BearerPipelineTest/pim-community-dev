<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Apps\InternalApi;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeFeatureFlag;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetAllConnectedAppScopeMessagesActionEndToEnd extends WebTestCase
{
    private FakeFeatureFlag $featureFlagMarketplaceActivate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureFlagMarketplaceActivate = $this->get('akeneo_connectivity.connection.marketplace_activate.feature');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_throws_not_found_exception_with_feature_flag_disabled(): void
    {
        $this->featureFlagMarketplaceActivate->disable();
        $this->authenticateAsAdmin();

        $this->client->request(
            'GET',
            '/rest/apps/connected-apps/connectionCodeA/scope-messages'
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_it_redirects_on_missing_xmlhttprequest_header(): void
    {
        $this->featureFlagMarketplaceActivate->enable();
        $this->authenticateAsAdmin();

        $this->client->request(
            'GET',
            '/rest/apps/connected-apps/connectionCodeA/scope-messages'
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        assert($response instanceof RedirectResponse);
        Assert::assertEquals('/', $response->getTargetUrl());
    }

    public function test_it_throws_access_denied_exception_with_missing_acl(): void
    {
        $this->featureFlagMarketplaceActivate->enable();
        $this->authenticateAsAdmin();
        $this->removeAclFromRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');

        $this->client->request(
            'GET',
            '/rest/apps/connected-apps/connectionCodeA/scope-messages',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function test_it_throws_not_found_exception_with_wrong_connection_code(): void
    {
        $this->featureFlagMarketplaceActivate->enable();
        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');

        $this->client->request(
            'GET',
            '/rest/apps/connected-apps/connectionCodeA/scope-messages',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_it_gets_all_connected_app_scope_messages(): void
    {
        $this->featureFlagMarketplaceActivate->enable();
        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');

        $this->getConnectionLoader()->createConnection('connectionCodeA', 'Connector A', FlowType::DATA_DESTINATION, false);
        $this->getConnectedAppLoader()->createConnectedApp(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'App A',
            ['write_association_types'],
            'connectionCodeA',
            'http://www.example.com/path/to/logo/a',
            'author A',
            ['category A1', 'category A2'],
            false,
            'partner A'
        );

        $expectedResult = [
            [
                'icon' => 'association_types',
                'type' => 'edit',
                'entities' => 'association_types',
            ]
        ];

        $this->client->request(
            'GET',
            '/rest/apps/connected-apps/connectionCodeA/scope-messages',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();
        $result = \json_decode($response->getContent(), true);

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    private function getConnectionLoader(): ConnectionLoader
    {
        return $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
    }

    private function getConnectedAppLoader(): ConnectedAppLoader
    {
        return $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
    }
}
