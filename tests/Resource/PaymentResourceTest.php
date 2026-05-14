<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Dto\Payment\Payment;
use Matchable\Whop\Dto\Payment\RefundResponse;
use Matchable\Whop\Dto\Payment\VoidResponse;
use Matchable\Whop\Exception\MissingArgumentsException;
use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\PaymentResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class PaymentResourceTest extends TestCase
{
    private RecordingClient $http;
    private PaymentResource $resource;

    protected function setUp(): void
    {
        $this->http = new RecordingClient();
        $factory = new Psr17Factory();
        $transport = new HttpTransport(
            httpClient: $this->http,
            apiKey: 'apik_test',
            baseUrl: 'https://api.whop.com/api/v1',
            requestFactory: $factory,
            streamFactory: $factory,
        );
        $this->resource = PaymentResource::initiate(transport: $transport);
    }

    public function testCreatePostsToPayments(): void
    {
        $this->http->willReturn(200, '{"id":"pay_1","status":"pending"}');

        $result = $this->resource->create(['amount' => 1000, 'currency' => 'usd']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/payments', (string) $req->getUri());
        self::assertSame('{"amount":1000,"currency":"usd"}', (string) $req->getBody());
        self::assertSame(['id' => 'pay_1', 'status' => 'pending'], $result);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{"data":[]}');

        $result = $this->resource->list(['page' => 1]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/payments?page=1', (string) $req->getUri());
        self::assertSame(['data' => []], $result);
    }

    public function testGetReturnsPaymentDto(): void
    {
        $this->http->willReturn(200, '{"id":"pay_1","status":"paid","amount":2500,"currency":"USD"}');

        $payment = $this->resource->get('pay_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/payments/pay_1', (string) $req->getUri());
        self::assertInstanceOf(Payment::class, $payment);
        self::assertSame('pay_1', $payment->id);
    }

    public function testRefundWithoutAmountSendsEmptyBody(): void
    {
        $this->http->willReturn(200, '{"refund_id":"ref_1"}');

        $refund = $this->resource->refund('pay_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/payments/pay_1/refund', (string) $req->getUri());
        self::assertSame('', (string) $req->getBody());
        self::assertInstanceOf(RefundResponse::class, $refund);
    }

    public function testRefundWithAmountSendsPartialAmount(): void
    {
        $this->http->willReturn(200, '{"refund_id":"ref_1"}');

        $this->resource->refund('pay_1', 500);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('{"partial_amount":500}', (string) $req->getBody());
    }

    public function testVoidReturnsVoidDto(): void
    {
        $this->http->willReturn(200, '{"id":"pay_1","status":"void"}');

        $void = $this->resource->void('pay_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/payments/pay_1/void', (string) $req->getUri());
        self::assertInstanceOf(VoidResponse::class, $void);
    }

    public function testGetFeesReturnsArray(): void
    {
        $this->http->willReturn(200, '{"fee":10}');

        $fees = $this->resource->getFees('pay_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/payments/pay_1/fees', (string) $req->getUri());
        self::assertSame(['fee' => 10], $fees);
    }

    public function testRetryPostsToRetryEndpoint(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->retry('pay_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/payments/pay_1/retry', (string) $req->getUri());
    }

    public function testGetWithMissingIdFieldThrowsMissingArgumentsException(): void
    {
        // Response is missing required "id" — DTO factory throws MissingArgumentsException directly.
        $this->http->willReturn(200, '{"status":"paid"}');

        $this->expectException(MissingArgumentsException::class);
        $this->resource->get('pay_1');
    }

    public function testRefundWithMissingRefundIdThrowsMissingArgumentsException(): void
    {
        // Response is missing required "refund_id"/"id" — DTO factory throws MissingArgumentsException directly.
        $this->http->willReturn(200, '{"status":"refunded"}');

        $this->expectException(MissingArgumentsException::class);
        $this->resource->refund('pay_1');
    }

    public function testVoidWithMissingIdThrowsMissingArgumentsException(): void
    {
        // Response is missing required "id" — DTO factory throws MissingArgumentsException directly.
        $this->http->willReturn(200, '{"status":"void"}');

        $this->expectException(MissingArgumentsException::class);
        $this->resource->void('pay_1');
    }
}
