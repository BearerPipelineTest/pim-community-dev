<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory;

use Akeneo\Channel\Locale\API\Query\IsLocaleEditable;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryIsLocaleEditable implements IsLocaleEditable
{
    /** @var array<string, string[]> */
    private array $editableLocalesPerUserGroupName = [];

    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function forUserId(string $localeCode, int $userId): bool
    {
        /** @var UserInterface $user */
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        if (null === $user) {
            return false;
        }

        foreach ($user->getGroupNames() as $groupName) {
            if (\in_array($localeCode, $this->editableLocalesPerUserGroupName[$groupName] ?? [])) {
                return true;
            }
        }

        return false;
    }

    public function addEditableLocaleCodeForGroup(string $groupName, string $localeCode): void
    {
        $this->editableLocalesPerUserGroupName[$groupName][] = $localeCode;
    }
}
