<?php

namespace App\Service;

use longlang\phpkafka\Producer\Producer;
use longlang\phpkafka\Producer\ProducerConfig;

class KafkaProducer
{
    private ?Producer $producer = null;
    private string $brokers;
    private bool $enabled;

    public function __construct()
    {
        $this->brokers = $_ENV['KAFKA_BROKERS'] ?? 'kafka:9092';
        $this->enabled = ($_ENV['KAFKA_ENABLED'] ?? 'true') === 'true';
    }

    /**
     * Publish an event to a Kafka topic.
     *
     * @param string $topic   Kafka topic name
     * @param array  $payload Event payload (will be JSON-encoded)
     * @param string $key     Optional partition key (e.g. user_id for ordering)
     */
    public function publish(string $topic, array $payload, string $key = ''): void
    {
        if (!$this->enabled) {
            return;
        }

        try {
            $this->getProducer()->send(
                $topic,
                json_encode($payload),
                $key !== '' ? $key : null
            );
        } catch (\Throwable $e) {
            // Kafka failures must never break the main request flow.
            // Log and continue — the app works without Kafka.
            error_log("[KafkaProducer] Failed to publish to {$topic}: " . $e->getMessage());
        }
    }

    private function getProducer(): Producer
    {
        if ($this->producer === null) {
            $config = new ProducerConfig();
            $config->setBrokers($this->brokers);
            $config->setAcks(1); // leader ack only — fast and reliable enough
            $config->setAutoCreateTopic(true);
            $this->producer = new Producer($config);
        }

        return $this->producer;
    }
}
