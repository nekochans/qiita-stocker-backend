<?php
/**
 * AbstractTestCase
 */

namespace Tests\Feature;

use Tests\TestCase;
use Tests\CreatesApplication;

/**
 * Class AbstractTestCase
 * @package Tests\Unit
 */
abstract class AbstractTestCase extends TestCase
{
    use CreatesApplication;

    public function tearDown()
    {
        $this->beforeApplicationDestroyed(function () {
            \DB::disconnect();
        });

        parent::tearDown();
    }
}
