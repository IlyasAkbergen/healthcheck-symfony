<?php

declare(strict_types=1);

namespace Esb\HealthCheckSymfony\Checks;

use Esb\HealthCheck\HealthCheck;
use Esb\HealthCheck\Status;
use Esb\HealthCheckSymfony\Settings\KafkaSettings;
use RuntimeException;

class KafkaCheck extends HealthCheck
{
    const NAME = 'kafka';

    private KafkaSettings $kafkaSettings;
    private \RdKafka\KafkaConsumer $consumer;

    public function __construct(KafkaSettings $kafkaSettings)
    {
        $this->kafkaSettings = $kafkaSettings;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function handle(): Status
    {
        try {
            $this->init();

            $this->consumer->subscribe($this->getTopics());

            while (true) {
                $message = $this->consumer->consume(5 * 1000);
                if (is_null($message)) {
                    continue;
                }
                switch ($message->err) {
                    case RD_KAFKA_RESP_ERR_NO_ERROR:
                        if (!empty($message->payload)) {
                            return $this->okay();
                        }
                        break;
                    case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                        throw new RuntimeException('No more messages; will wait for more', $message->err);
                    case RD_KAFKA_RESP_ERR__TIMED_OUT:
                        throw new RuntimeException('Timed out', $message->err);
                    default:
                        throw new RuntimeException($message->errstr(), $message->err);
                }
            }
        } catch (\Throwable $e) {
            return $this->problem('Consuming messages failed');
        }
    }

    private function init()
    {
        $conf = new \RdKafka\Conf();

        $conf->setRebalanceCb(
            function (\RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
                switch ($err) {
                    case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                        $kafka->assign($partitions);
                        break;
                    case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                        $kafka->assign(null);
                        break;
                    default:
                        throw new RuntimeException($err);
                }
            }
        );

        // Configure the group.id. All consumer with the same group.id will consume
        // different partitions.
        $conf->set('group.id', $this->kafkaSettings->getGroup());
        $conf->set('enable.auto.commit', 'false');
        // Initial list of Kafka brokers
        $conf->set('metadata.broker.list', $this->kafkaSettings->getBrokerList());

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'earliest': start from the beginning
        $conf->set('auto.offset.reset', 'earliest');

        $conf->set('security.protocol', $this->kafkaSettings->getSecurityProtocol());
        $conf->set('sasl.mechanism', $this->kafkaSettings->getSaslMechanism());
        $conf->set('sasl.username', $this->kafkaSettings->getSaslUsername());
        $conf->set('sasl.password', $this->kafkaSettings->getSaslPassword());
        $conf->set('partition.assignment.strategy', "roundrobin");

        $this->consumer = new \RdKafka\KafkaConsumer($conf);
    }

    /**
     * @return string[]
     */
    private function getTopics(): array
    {
        $topics = [];
        foreach ($this->kafkaSettings->getTopics() as $topic) {
            $topics[] = $this->kafkaSettings->getEnv() . '.' . $topic;
        }
        return $topics;
    }
}
