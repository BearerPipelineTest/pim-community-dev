<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps;

interface ScopeMapperRegistryInterface
{
    /**
     * @return array<string>
     */
    public function getAllScopes(): array;

    /**
     * Provides all information needed to display scopes.
     * Filters the scopes by keeping the higher hierarchy scope.
     *
     * @param array<string> $scopeList
     *
     * @throw \LogicArgumentException if the given scope does not exist
     *
     * @return array<
     *     array{
     *         icon: string,
     *         type: string,
     *         entities: string,
     *     }
     * >
     */
    public function getMessages(array $scopeList): array;

    /**
     * Provides acls that correspond to the given scopes from the bottom of the hierarchy.
     *
     * @param array<string> $scopeList
     *
     * @throw \LogicArgumentException if the given scope does not exist
     *
     * @return array<string>
     */
    public function getAcls(array $scopeList): array;
}
