<?php

namespace Enqueue\Bundle\Profiler;

use Enqueue\Client\MessagePriority;
use Enqueue\Client\ProducerInterface;
use Enqueue\Client\TraceableProducer;
use Enqueue\Util\JSON;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class MessageQueueCollector extends DataCollector
{
    /**
     * @var ProducerInterface
     */
    private $producer;

    /**
     * @param ProducerInterface $producer
     */
    public function __construct(ProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'sent_messages' => [],
        ];

        if ($this->producer instanceof TraceableProducer) {
            $this->data['sent_messages'] = $this->producer->getTraces();
        }
    }

    /**
     * @return array
     */
    public function getSentMessages()
    {
        return $this->data['sent_messages'];
    }

    /**
     * @param string $priority
     *
     * @return string
     */
    public function prettyPrintPriority($priority)
    {
        $map = [
            MessagePriority::VERY_LOW => 'very low',
            MessagePriority::LOW => 'low',
            MessagePriority::NORMAL => 'normal',
            MessagePriority::HIGH => 'high',
            MessagePriority::VERY_HIGH => 'very high',
        ];

        return isset($map[$priority]) ? $map[$priority] : $priority;
    }

    /**
     * @param mixed $body
     *
     * @return string
     */
    public function ensureString($body)
    {
        return is_string($body) ? $body : JSON::encode($body);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'enqueue.message_queue';
    }
}
