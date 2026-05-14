<?php

declare(strict_types=1);

namespace Matchable\Whop\Resource;

use Matchable\Whop\Exception\WhopApiException;
use Matchable\Whop\Http\HttpMethod;
use Matchable\Whop\Http\HttpTransport;

final readonly class CourseLessonResource extends BaseResource
{
    public static function initiate(HttpTransport $transport): self
    {
        return new self(transport: $transport);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function create(array $data): array
    {
        return $this->request(method: HttpMethod::POST, url: 'course-lessons', body: $data);
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function list(array $query = []): array
    {
        return $this->request(method: HttpMethod::GET, url: 'course-lessons', query: $query);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function get(string $id): array
    {
        return $this->request(method: HttpMethod::GET, url: sprintf('course-lessons/%s', $id));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function update(string $id, array $data): array
    {
        return $this->request(method: HttpMethod::PATCH, url: sprintf('course-lessons/%s', $id), body: $data);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function start(string $id): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('course-lessons/%s/start', $id));
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function markAsCompleted(string $id): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('course-lessons/%s/mark-as-completed', $id));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function submitAssessment(string $id, array $data): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('course-lessons/%s/submit-assessment', $id), body: $data);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function delete(string $id): array
    {
        return $this->request(method: HttpMethod::DELETE, url: sprintf('course-lessons/%s', $id));
    }
}
