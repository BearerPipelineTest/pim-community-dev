<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAccessTokenInterface;
use OAuth2\IOAuth2;
use OAuth2\IOAuth2GrantCode;
use OAuth2\Model\IOAuth2AuthCode;
use OAuth2\OAuth2;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateAccessToken implements CreateAccessTokenInterface
{
    private IOAuth2 $auth2;
    private IOAuth2GrantCode $storage;
    private ClientProviderInterface $clientProvider;

    public function __construct(
        IOAuth2 $auth2,
        IOAuth2GrantCode $storage,
        ClientProviderInterface $clientProvider
    ) {
        $this->auth2 = $auth2;
        $this->storage = $storage;
        $this->clientProvider = $clientProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $clientId, string $code): array
    {
        $client = $this->clientProvider->findClientByAppId($clientId);
        if (null === $client) {
            throw new \InvalidArgumentException('No client found with the given client id.');
        }

        /** @var IOAuth2AuthCode|null $authCode */
        $authCode = $this->storage->getAuthCode($code);
        if (null === $authCode) {
            throw new \InvalidArgumentException('Unknown authorization code.');
        }
        $this->auth2->setVariable(OAuth2::CONFIG_ACCESS_LIFETIME, null);

        return $this->auth2->createAccessToken(
            $client,
            $authCode->getData(),
            null,
            3600*4
        );
    }
}
