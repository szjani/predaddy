<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use DateTime;
use precore\lang\BaseObject;
use precore\lang\NullPointerException;
use precore\lang\ObjectInterface;
use precore\util\Objects;
use precore\util\Preconditions;
use precore\util\ToStringHelper;
use precore\util\UUID;

/**
 * Base class for all types of messages. Contains the message identifier and timestamp.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class AbstractMessage extends BaseObject implements Message
{
    protected $id;
    protected $created;

    public function __construct()
    {
        $this->created = new DateTime();
        $this->id = UUID::randomUUID()->toString();
    }

    /**
     * @return string
     * @throws NullPointerException if ID is not initialized
     */
    public function identifier() : string
    {
        return Preconditions::checkNotNull($this->id, 'ID is not initialized');
    }

    /**
     * @return DateTime
     * @throws NullPointerException if created field is not initialized
     */
    public function created() : DateTime
    {
        return clone Preconditions::checkNotNull($this->created, 'created field is not initialized');
    }

    final public function equals(ObjectInterface $object = null) : bool
    {
        if ($object === $this) {
            return true;
        }
        /* @var $object AbstractMessage */
        return $object !== null
            && $this->getClassName() === $object->getClassName()
            && $this->id === $object->id;
    }

    /**
     * @return ToStringHelper
     */
    protected function toStringHelper() : ToStringHelper
    {
        return Objects::toStringHelper($this)
            ->add('id', $this->identifier())
            ->add('created', $this->created());
    }

    public function toString() : string
    {
        return $this->toStringHelper()->toString();
    }
}
