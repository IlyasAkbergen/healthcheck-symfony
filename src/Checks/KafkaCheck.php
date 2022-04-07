<?php

namespace Esb\HealthCheckSymfony\Checks;

use App\Handler\Kafka\HandlerInterface;
use Esb\HealthCheck\HealthCheck;
use Esb\HealthCheck\Status;
use Esb\HealthCheckSymfony\Settings\KafkaSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RdKafka\Conf;
use RdKafka\TopicConf;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use RuntimeException;

class KafkaCheck extends HealthCheck
{
    private KafkaSettings $kafkaSettings;
    private KafkaConsumer $consumer;

    public function __construct(KafkaSettings $kafkaSettings)
    {
        $this->kafkaSettings = $kafkaSettings;
    }

    public function name(): string
    {
        return 'kafka';
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
            return $this->problem('Consuming messages failed', $this->exceptionContext($e));
        }
    }

    private function init()
    {
        $conf = new Conf();

        $conf->setRebalanceCb(
            function (KafkaConsumer $kafka, $err, array $partitions = null) {
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

        $this->consumer = new KafkaConsumer($conf);
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
