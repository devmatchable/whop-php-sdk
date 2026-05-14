<?php

declare(strict_types=1);

namespace Matchable\Whop\Resource;

use Matchable\Whop\Exception\WhopApiException;
use Matchable\Whop\Http\HttpMethod;
use Matchable\Whop\Http\HttpTransport;

final readonly class FileResource extends BaseResource
{
    public static function initiate(HttpTransport $transport): self
    {
        return new self(transport: $transport);
    }

    /**
     * Create a file record and get a presigned upload URL.
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function create(string $filename): array
    {
        return $this->request(method: HttpMethod::POST, url: 'files', body: ['filename' => $filename]);
    }
}
