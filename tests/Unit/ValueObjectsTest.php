<?php

namespace Tests\Unit;

use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Order\ValueObjects\CustomerName;
use PHPUnit\Framework\TestCase;

class ValueObjectsTest extends TestCase
{
      public function test_order_id_value_object()
      {
            // Test creating from int
            $orderId = OrderId::fromInt(123);
            $this->assertEquals(123, $orderId->getValue());
            $this->assertFalse($orderId->isNull());
            $this->assertTrue($orderId->isNotNull());

            // Test creating null ID
            $nullOrderId = OrderId::fromInt(null);
            $this->assertNull($nullOrderId->getValue());
            $this->assertTrue($nullOrderId->isNull());
            $this->assertFalse($nullOrderId->isNotNull());

            // Test generating new ID
            $generatedId = OrderId::generate();
            $this->assertNull($generatedId->getValue());

            // Test equality
            $orderId1 = OrderId::fromInt(123);
            $orderId2 = OrderId::fromInt(123);
            $orderId3 = OrderId::fromInt(456);

            $this->assertTrue($orderId1->equals($orderId2));
            $this->assertFalse($orderId1->equals($orderId3));

            // Test string conversion
            $this->assertEquals('123', (string) $orderId1);
      }

      public function test_order_status_value_object()
      {
            // Test creating from string
            $status = OrderStatus::fromString('pending');
            $this->assertEquals('pending', $status->getValue());
            $this->assertTrue($status->isPending());

            // Test factory methods
            $confirmed = OrderStatus::confirmed();
            $this->assertTrue($confirmed->isConfirmed());

            $shipped = OrderStatus::shipped();
            $this->assertTrue($shipped->isShipped());

            // Test status transitions
            $pending = OrderStatus::pending();
            $confirmed = OrderStatus::confirmed();
            $shipped = OrderStatus::shipped();
            $delivered = OrderStatus::delivered();
            $cancelled = OrderStatus::cancelled();

            $this->assertTrue($pending->canTransitionTo($confirmed));
            $this->assertTrue($pending->canTransitionTo($cancelled));
            $this->assertFalse($pending->canTransitionTo($delivered));

            $this->assertTrue($confirmed->canTransitionTo($shipped));
            $this->assertTrue($confirmed->canTransitionTo($cancelled));
            $this->assertFalse($confirmed->canTransitionTo($pending));

            $this->assertTrue($shipped->canTransitionTo($delivered));
            $this->assertFalse($shipped->canTransitionTo($pending));

            // Test equality
            $status1 = OrderStatus::pending();
            $status2 = OrderStatus::fromString('pending');
            $status3 = OrderStatus::confirmed();

            $this->assertTrue($status1->equals($status2));
            $this->assertFalse($status1->equals($status3));

            // Test string conversion
            $this->assertEquals('pending', (string) $status1);
      }

      public function test_customer_name_value_object()
      {
            // Test creating from string
            $name = CustomerName::fromString('John Doe');
            $this->assertEquals('John Doe', $name->getValue());
            $this->assertEquals('John', $name->getFirstName());
            $this->assertEquals('Doe', $name->getLastName());
            $this->assertEquals('J.D.', $name->getInitials());

            // Test with single name
            $singleName = CustomerName::fromString('John');
            $this->assertEquals('John', $singleName->getFirstName());
            $this->assertEquals('', $singleName->getLastName());
            $this->assertEquals('J.', $singleName->getInitials());

            // Test with multiple names
            $fullName = CustomerName::fromString('John Michael Doe');
            $this->assertEquals('John', $fullName->getFirstName());
            $this->assertEquals('Michael Doe', $fullName->getLastName());
            $this->assertEquals('J.M.D.', $fullName->getInitials());

            // Test equality (case insensitive)
            $name1 = CustomerName::fromString('John Doe');
            $name2 = CustomerName::fromString('john doe');
            $name3 = CustomerName::fromString('Jane Doe');

            $this->assertTrue($name1->equals($name2));
            $this->assertFalse($name1->equals($name3));

            // Test string conversion
            $this->assertEquals('John Doe', (string) $name1);
      }

      public function test_order_id_validation()
      {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Order ID must be positive or null');

            OrderId::fromInt(0);
      }

      public function test_order_status_validation()
      {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Invalid order status: invalid');

            OrderStatus::fromString('invalid');
      }

      public function test_customer_name_validation()
      {
            // Test empty name
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Customer name cannot be empty');
            CustomerName::fromString('');

            // Test too short name
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Customer name must be at least 2 characters long');
            CustomerName::fromString('A');

            // Test too long name
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Customer name cannot exceed 100 characters');
            CustomerName::fromString(str_repeat('A', 101));

            // Test invalid characters
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Customer name can only contain letters, spaces, hyphens, and apostrophes');
            CustomerName::fromString('John123');
      }
}