<?php
declare(strict_types=1);

namespace predaddy\presentation;

use precore\lang\Enum;

/**
 * Direction enum.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Direction extends Enum
{
    public static $ASC;
    public static $DESC;
}
Direction::init();
