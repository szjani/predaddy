<?php
declare(strict_types=1);

namespace predaddy\domain;

use PHPUnit\Framework\TestCase;

/**
 * @package predaddy\domain
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class UserIdTest extends TestCase
{
    public function testToString()
    {
        $userId = UserId::create();
        self::assertRegExp('#[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}.*User\}$#', (string) $userId);
    }
}
