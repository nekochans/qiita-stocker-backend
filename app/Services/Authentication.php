<?php
/**
 * Authentication
 */

namespace App\Services;

use App\Models\Domain\AccountEntity;

use App\Models\Domain\AccountRepository;
use App\Models\Domain\LoginSessionEntity;
use App\Models\Domain\LoginSessionRepository;
use App\Models\Domain\Exceptions\UnauthorizedException;
use App\Models\Domain\Exceptions\LoginSessionExpiredException;

/**
 * Trait Authentication
 * @package App\Services
 */
trait Authentication
{
    /**
     * AccountEntity を取得する
     *
     * @param array $params
     * @param LoginSessionRepository $loginSessionRepository
     * @param AccountRepository $accountRepository
     * @return AccountEntity
     * @throws LoginSessionExpiredException
     * @throws UnauthorizedException
     */
    public function findAccountEntity(
        array $params,
        LoginSessionRepository $loginSessionRepository,
        AccountRepository $accountRepository
    ): AccountEntity {
        if ($params['sessionId'] === null) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        }

        $loginSessionEntity = $loginSessionRepository->find($params['sessionId']);

        if ($loginSessionEntity->isExpired()) {
            throw new LoginSessionExpiredException($loginSessionEntity->loginSessionExpiredMessage());
        }

        return $loginSessionEntity->findHasAccountEntity($accountRepository);
    }
}
