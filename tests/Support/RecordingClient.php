<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Support;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class RecordingClient implements ClientInterface
{
    public ?RequestInterface $lastRequest = null;

    private int $status = 200;

    private string $body = '{}';

    private ?\Throwable $throwOnSend = null;

    public function willReturn(int $status, string $body): void
    {
        $this->status = $status;
        $this->body = $body;
    }

    public function willThrow(\Throwable $throwable): void
    {
        $this->throwOnSend = $throwable;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->lastRequest = $request;

        if (null !== $this->throwOnSend) {
            throw $this->throwOnSend;
        }

        return (new Psr17Factory())->createResponse($this->status)
            ->withBody((new Psr17Factory())->createStream($this->body));
    }
}
