<?php
/**
 * AbstractTestCase
 */

namespace Tests\Feature;

use Tests\TestCase;
use App\Eloquents\Account;
use Tests\CreatesApplication;
use App\Eloquents\AccessToken;
use App\Eloquents\LoginSession;
use App\Eloquents\QiitaAccount;

/**
 * Class AbstractTestCase
 * @package Tests\Unit
 */
abstract class AbstractTestCase extends TestCase
{
    use CreatesApplication;

    public function setUp()
    {
        parent::setUp();

        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Account::truncate();
        LoginSession::truncate();
        AccessToken::truncate();
        QiitaAccount::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function tearDown()
    {
        $this->beforeApplicationDestroyed(function () {
            \DB::disconnect();
        });

        parent::tearDown();
    }
}
