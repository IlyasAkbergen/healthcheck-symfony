<?php

declare(strict_types=1);

namespace Esb\HealthCheckSymfony\Settings;

class KafkaSettings
{
    private string $group;
    private string $brokerList;
    private string $saslUsername;
    private string $saslPassword;
    private string $securityProtocol;
    private string $saslMechanism;
    private array $topics;

    public function setGroup(string $group): self
    {
        $this->group = $group;
        return $this;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setBrokerList(string $brokerList): self
    {
        $this->brokerList = $brokerList;
        return $this;
    }

    public function getBrokerList(): string
    {
        return $this->brokerList;
    }

    public function setSecurityProtocol(string $securityProtocol): self
    {
        $this->securityProtocol = $securityProtocol;
        return $this;
    }

    public function getSecurityProtocol(): string
    {
        return $this->securityProtocol;
    }

    public function setSaslMechanism(string $saslMechanism): self
    {
        $this->saslMechanism = $saslMechanism;
        return $this;
    }

    public function getSaslMechanism(): string
    {
        return $this->saslMechanism;
    }

    public function setSaslUsername(string $saslUsername): self
    {
        $this->saslUsername = $saslUsername;
        return $this;
    }

    public function getSaslUsername(): string
    {
        return $this->saslUsername;
    }

    public function setSaslPassword(string $saslPassword): self
    {
        $this->saslPassword = $saslPassword;
        return $this;
    }

    public function getSaslPassword(): string
    {
        return $this->saslPassword;
    }

    public function setTopics(array $topics): self
    {
        $this->topics = $topics;
        return $this;
    }

    public function getTopics(): array
    {
        return $this->topics;
    }
}
