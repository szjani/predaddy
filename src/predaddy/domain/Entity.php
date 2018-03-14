<?php
declare(strict_types=1);

namespace predaddy\domain;

use precore\lang\ObjectInterface;

/**
 * DDD entity. The following rules should be applied:
 *  - Each aggregate has a root entity.
 *  - Each entity has identity field.
 *  - Only the root is accessible from outside the boundary.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface Entity extends ObjectInterface
{
}
