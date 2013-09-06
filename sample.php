<?php

namespace Foo\Bar;

use DateTime;
use Goodby\EventSourcing\Event;
use Goodby\EventSourcing\EventNotifiable;
use Goodby\EventSourcing\EventSerializer\JSONEventSerializer;
use Goodby\EventSourcing\EventStore\MySQLEventStore;
use Goodby\EventSourcing\EventStreamId;
use PDO;

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/goodby-assertion/Goodby/Assertion/Assert.php';

class ItemPurchased implements Event
{
    private $eventVersion;
    private $occurredOn;
    private $itemId;
    private $price;

    public function __construct($itemId, $price)
    {
        $this->eventVersion = 1;
        $this->occurredOn = time();
        $this->itemId = $itemId;
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function eventVersion()
    {
        return $this->eventVersion;
    }

    /**
     * @return \DateTime
     */
    public function occurredOn()
    {
        return (new DateTime)->setTimestamp($this->occurredOn);
    }

    /**
     * Must return a primitive key-value set which is serializable.
     * @return mixed[]
     */
    public function toContractualData()
    {
        return [
            'eventVersion' => $this->eventVersion,
            'occurredOn'   => $this->occurredOn,
            'itemId'       => $this->itemId,
            'price'        => $this->price,
        ];
    }

    /**
     * @param mixed[] $data
     * @return Event
     */
    public static function fromContractualData(array $data)
    {
        $self = new self($data['itemId'], $data['price']);
        $self->eventVersion = $data['eventVersion'];
        $self->occurredOn = $data['occurredOn'];

        return $self;
    }
}

class MySQLEventNotifiable implements EventNotifiable
{
    /**
     * @return void
     */
    public function notifyEvents()
    {
        echo 'New events appended!!', PHP_EOL;
    }
}

$dsn = 'mysql:host=localhost;dbname=test;charset=utf8';
$username = 'root';
$password = 'root';
$connection = new PDO($dsn, $username, $password, [
    PDO::ATTR_ORACLE_NULLS       => PDO::NULL_NATURAL, // NULL is available
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_AUTOCOMMIT         => true,
]);
$serializer = new JSONEventSerializer();
$eventStore = new MySQLEventStore($connection, $serializer);
$eventStore->registerEventNotifiable(new MySQLEventNotifiable());

$connection->exec('TRUNCATE TABLE event_store');

$eventStreamId = new EventStreamId('foo');
$events = [
    new ItemPurchased('item1234', 1000),
    new ItemPurchased('item1234', 2000),
];
$eventStore->append($eventStreamId, $events);

$events = $eventStore->eventStreamSince($eventStreamId);

var_dump($events);
