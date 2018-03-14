<?php
declare(strict_types=1);

namespace predaddy\domain;

use PHPUnit\Framework\TestCase;
use precore\util\UUID;
use predaddy\fixture\article\EventSourcedArticleId;
use predaddy\fixture\article\EventSourcedArticleId2;

/**
 * @package src\predaddy\domain
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class UUIDAggregateIdTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCreatedFromValue()
    {
        $value = UUID::randomUUID()->toString();
        $articleId = EventSourcedArticleId::from($value);
        self::assertInstanceOf(EventSourcedArticleId::className(), $articleId);
        self::assertEquals($value, $articleId->value());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldFailIfBuiltFromInvalidString()
    {
        EventSourcedArticleId::from('invalid');
    }

    /**
     * @test
     */
    public function shouldNotBeEqualTwoDifferentTypeOfAggregateId()
    {
        $value = UUID::randomUUID()->toString();
        $id1 = EventSourcedArticleId::from($value);
        $id2 = EventSourcedArticleId2::from($value);
        self::assertFalse($id1->equals($id2));
        self::assertFalse($id2->equals($id1));
    }

    /**
     * @test
     */
    public function shouldEqualsBeSymmetric()
    {
        $id1 = EventSourcedArticleId::create();
        $id2 = new GenericAggregateId($id1->value(), $id1->aggregateClass());
        self::assertTrue($id2->equals($id1));
        self::assertTrue($id1->equals($id2));
    }
}
