<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Dto;

use Matchable\Whop\Dto\Company\Company;
use Matchable\Whop\Exception\MissingArgumentsException;
use PHPUnit\Framework\TestCase;

final class CompanyTest extends TestCase
{
    public function testCreateMapsAllFields(): void
    {
        $company = Company::create([
            'id' => 'biz_1',
            'title' => 'Acme',
            'email' => 'a@b.c',
            'country' => 'NL',
            'onboarding_url' => 'https://x',
            'verified' => true,
        ]);

        self::assertSame('biz_1', $company->id);
        self::assertSame('Acme', $company->name);
        self::assertSame('a@b.c', $company->email);
        self::assertSame('NL', $company->country);
        self::assertSame('https://x', $company->onboardingUrl);
        self::assertTrue($company->verified);
    }

    public function testCreateFallsBackToNameKeyAndDefaults(): void
    {
        $company = Company::create(['id' => 'biz_1', 'name' => 'Acme']);

        self::assertSame('Acme', $company->name);
        self::assertNull($company->email);
        self::assertNull($company->country);
        self::assertNull($company->onboardingUrl);
        self::assertFalse($company->verified);
    }

    public function testCreateThrowsWhenIdMissing(): void
    {
        $this->expectException(MissingArgumentsException::class);
        Company::create(['title' => 'Acme']);
    }

    public function testCreateThrowsWhenIdEmpty(): void
    {
        $this->expectException(MissingArgumentsException::class);
        Company::create(['id' => '', 'title' => 'Acme']);
    }

    public function testCreateThrowsWhenNameMissing(): void
    {
        $this->expectException(MissingArgumentsException::class);
        Company::create(['id' => 'biz_1']);
    }

    public function testCreateThrowsWhenNameEmpty(): void
    {
        $this->expectException(MissingArgumentsException::class);
        Company::create(['id' => 'biz_1', 'title' => '']);
    }

    public function testTitleKeyTakesPrecedenceOverNameKey(): void
    {
        $company = Company::create([
            'id' => 'biz_2',
            'title' => 'Title Name',
            'name' => 'Other Name',
        ]);

        self::assertSame('Title Name', $company->name);
    }
}
