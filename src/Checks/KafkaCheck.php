<?php

namespace Esb\HealthCheckSymfony\Checks;

use App\Handler\Kafka\HandlerInterface;
use Esb\HealthCheck\HealthCheck;
use Esb\HealthCheck\Status;
use Esb\HealthCheckSymfony\Settings\KafkaSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use RuntimeException;

class KafkaCheck extends HealthCheck
{
    private ContainerInterface $container;
    private KafkaSettings $kafkaSettings;
    private HandlerInterface $topicHandler;
    private KafkaConsumer $consumer;

    public function __construct(
        ContainerInterface $container,
        KafkaSettings $kafkaSettings,
        HandlerInterface $topicHandler
    ) {
        $this->container = $container;
        $this->kafkaSettings = $kafkaSettings;
        $this->topicHandler = $topicHandler;
    }

    public function name(): string
    {
        return 'kafka';
    }

    public function handle(): Status
    {
        $this->init();

        $this->consumer->subscribe($this->getTopics());

        $info = [];

        try {
            $firstMessage = $this->consumer->consume(RD_KAFKA_OFFSET_BEGINNING);
            $firstMessage = $this->consumer->consume(rd_kafka_offste_tail(1));
        } catch (\Throwable $e) {
            return $this->problem('consuming messages failed', $this->exceptionContext($e));
        }
        return $this->okay($info);
    }

    private function init()
    {
        $conf = new Conf();

        $conf->setRebalanceCb(
            function (KafkaConsumer $kafka, $err, array $partitions = null) {
                switch ($err) {
                    case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                        echo "Assign: ";
                        print_r($partitions);
                        $kafka->assign($partitions);
                        break;
                    case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                        echo "Revoke: ";
                        print_r($partitions);
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

        $consumer = new KafkaConsumer($conf);
        $this->consumer = $consumer;
    }

    private function getTopics(): array
    {
        $topics = [];
        foreach ($this->kafkaSettings->getTopics() as $topic) {
            $topics[] = $this->kafkaSettings->getEnv() . '.' . $topic;
        }
        return $topics;
    }
}
