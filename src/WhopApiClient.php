<?php

declare(strict_types=1);

namespace Matchable\Whop;

use Http\Discovery\Psr17FactoryDiscovery;
use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\AccessTokenResource;
use Matchable\Whop\Resource\AccountLinkResource;
use Matchable\Whop\Resource\AdCampaignResource;
use Matchable\Whop\Resource\AdGroupResource;
use Matchable\Whop\Resource\AdResource;
use Matchable\Whop\Resource\AffiliateResource;
use Matchable\Whop\Resource\AiChatResource;
use Matchable\Whop\Resource\AppBuildResource;
use Matchable\Whop\Resource\AppResource;
use Matchable\Whop\Resource\AuthorizedUserResource;
use Matchable\Whop\Resource\ChatChannelResource;
use Matchable\Whop\Resource\CheckoutResource;
use Matchable\Whop\Resource\CompanyResource;
use Matchable\Whop\Resource\CompanyTokenTransactionResource;
use Matchable\Whop\Resource\CourseChapterResource;
use Matchable\Whop\Resource\CourseLessonInteractionResource;
use Matchable\Whop\Resource\CourseLessonResource;
use Matchable\Whop\Resource\CourseResource;
use Matchable\Whop\Resource\CourseStudentResource;
use Matchable\Whop\Resource\DisputeAlertResource;
use Matchable\Whop\Resource\DisputeResource;
use Matchable\Whop\Resource\DmChannelResource;
use Matchable\Whop\Resource\DmMemberResource;
use Matchable\Whop\Resource\EntryResource;
use Matchable\Whop\Resource\ExperienceResource;
use Matchable\Whop\Resource\FeeMarkupResource;
use Matchable\Whop\Resource\FileResource;
use Matchable\Whop\Resource\ForumPostResource;
use Matchable\Whop\Resource\ForumResource;
use Matchable\Whop\Resource\InvoiceResource;
use Matchable\Whop\Resource\LeadResource;
use Matchable\Whop\Resource\LedgerAccountResource;
use Matchable\Whop\Resource\MemberResource;
use Matchable\Whop\Resource\MembershipResource;
use Matchable\Whop\Resource\MessageResource;
use Matchable\Whop\Resource\NotificationResource;
use Matchable\Whop\Resource\PaymentMethodResource;
use Matchable\Whop\Resource\PaymentResource;
use Matchable\Whop\Resource\PayoutAccountResource;
use Matchable\Whop\Resource\PayoutMethodResource;
use Matchable\Whop\Resource\PlanResource;
use Matchable\Whop\Resource\ProductResource;
use Matchable\Whop\Resource\PromoCodeResource;
use Matchable\Whop\Resource\ReactionResource;
use Matchable\Whop\Resource\RefundResource;
use Matchable\Whop\Resource\ResolutionCenterResource;
use Matchable\Whop\Resource\ReviewResource;
use Matchable\Whop\Resource\SetupIntentResource;
use Matchable\Whop\Resource\ShipmentResource;
use Matchable\Whop\Resource\StatsResource;
use Matchable\Whop\Resource\SupportChannelResource;
use Matchable\Whop\Resource\TopupResource;
use Matchable\Whop\Resource\TransferResource;
use Matchable\Whop\Resource\UserResource;
use Matchable\Whop\Resource\VerificationResource;
use Matchable\Whop\Resource\WebhookResource;
use Matchable\Whop\Resource\WithdrawalResource;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Main entry point for the Whop PHP SDK.
 *
 * Accepts a PSR-18 HTTP client and optional PSR-17 factories (auto-discovered
 * via php-http/discovery when not provided), constructs one {@see HttpTransport},
 * and wires all resource classes through it.
 */
final readonly class WhopApiClient
{
    private const string DEFAULT_BASE_URL = 'https://api.whop.com/api/v1';

    // Core
    public readonly CompanyResource $companies;
    public readonly AccountLinkResource $accountLinks;
    public readonly FileResource $files;
    public readonly AccessTokenResource $accessTokens;
    public readonly AuthorizedUserResource $authorizedUsers;
    public readonly UserResource $users;
    public readonly MemberResource $members;
    public readonly WebhookResource $webhooks;

    // Payments & Billing
    public readonly PaymentResource $payments;
    public readonly CheckoutResource $checkouts;
    public readonly PlanResource $plans;
    public readonly ProductResource $products;
    public readonly MembershipResource $memberships;
    public readonly RefundResource $refunds;
    public readonly InvoiceResource $invoices;
    public readonly PromoCodeResource $promoCodes;
    public readonly PaymentMethodResource $paymentMethods;
    public readonly SetupIntentResource $setupIntents;

    // Platform & Finance
    public readonly TransferResource $transfers;
    public readonly FeeMarkupResource $feeMarkups;
    public readonly TopupResource $topups;
    public readonly WithdrawalResource $withdrawals;
    public readonly LedgerAccountResource $ledgerAccounts;
    public readonly PayoutAccountResource $payoutAccounts;
    public readonly PayoutMethodResource $payoutMethods;

    // Disputes
    public readonly DisputeResource $disputes;
    public readonly DisputeAlertResource $disputeAlerts;
    public readonly ResolutionCenterResource $resolutionCenter;

    // Commerce
    public readonly ShipmentResource $shipments;
    public readonly LeadResource $leads;
    public readonly EntryResource $entries;
    public readonly ReviewResource $reviews;
    public readonly AffiliateResource $affiliates;
    public readonly StatsResource $stats;

    // Experiences & Courses
    public readonly ExperienceResource $experiences;
    public readonly CourseResource $courses;
    public readonly CourseChapterResource $courseChapters;
    public readonly CourseLessonResource $courseLessons;
    public readonly CourseLessonInteractionResource $courseLessonInteractions;
    public readonly CourseStudentResource $courseStudents;

    // Communication
    public readonly ChatChannelResource $chatChannels;
    public readonly DmChannelResource $dmChannels;
    public readonly DmMemberResource $dmMembers;
    public readonly MessageResource $messages;
    public readonly ReactionResource $reactions;
    public readonly ForumResource $forums;
    public readonly ForumPostResource $forumPosts;
    public readonly SupportChannelResource $supportChannels;
    public readonly NotificationResource $notifications;

    // Advertising
    public readonly AdCampaignResource $adCampaigns;
    public readonly AdGroupResource $adGroups;
    public readonly AdResource $ads;

    // Apps & AI
    public readonly AppResource $apps;
    public readonly AppBuildResource $appBuilds;
    public readonly AiChatResource $aiChats;
    public readonly CompanyTokenTransactionResource $tokenTransactions;

    // Verifications
    public readonly VerificationResource $verifications;

    /**
     * @param ClientInterface              $httpClient      PSR-18 HTTP client
     * @param string                       $apiKey          Whop API key (Bearer token)
     * @param string                       $baseUrl         API base URL (default: https://api.whop.com/api/v1)
     * @param RequestFactoryInterface|null $requestFactory  PSR-17 request factory; auto-discovered when null
     * @param StreamFactoryInterface|null  $streamFactory   PSR-17 stream factory; auto-discovered when null
     *
     * @throws \Http\Discovery\Exception\NotFoundException when $requestFactory or $streamFactory
     *                                                     is null and no PSR-17 implementation is installed
     */
    public function __construct(
        ClientInterface $httpClient,
        string $apiKey,
        string $baseUrl = self::DEFAULT_BASE_URL,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ) {
        $transport = new HttpTransport(
            httpClient: $httpClient,
            apiKey: $apiKey,
            baseUrl: $baseUrl,
            requestFactory: $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory(),
            streamFactory: $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory(),
        );

        // Core
        $this->companies = CompanyResource::initiate(transport: $transport);
        $this->accountLinks = AccountLinkResource::initiate(transport: $transport);
        $this->files = FileResource::initiate(transport: $transport);
        $this->accessTokens = AccessTokenResource::initiate(transport: $transport);
        $this->authorizedUsers = AuthorizedUserResource::initiate(transport: $transport);
        $this->users = UserResource::initiate(transport: $transport);
        $this->members = MemberResource::initiate(transport: $transport);
        $this->webhooks = WebhookResource::initiate(transport: $transport);

        // Payments & Billing
        $this->payments = PaymentResource::initiate(transport: $transport);
        $this->checkouts = CheckoutResource::initiate(transport: $transport);
        $this->plans = PlanResource::initiate(transport: $transport);
        $this->products = ProductResource::initiate(transport: $transport);
        $this->memberships = MembershipResource::initiate(transport: $transport);
        $this->refunds = RefundResource::initiate(transport: $transport);
        $this->invoices = InvoiceResource::initiate(transport: $transport);
        $this->promoCodes = PromoCodeResource::initiate(transport: $transport);
        $this->paymentMethods = PaymentMethodResource::initiate(transport: $transport);
        $this->setupIntents = SetupIntentResource::initiate(transport: $transport);

        // Platform & Finance
        $this->transfers = TransferResource::initiate(transport: $transport);
        $this->feeMarkups = FeeMarkupResource::initiate(transport: $transport);
        $this->topups = TopupResource::initiate(transport: $transport);
        $this->withdrawals = WithdrawalResource::initiate(transport: $transport);
        $this->ledgerAccounts = LedgerAccountResource::initiate(transport: $transport);
        $this->payoutAccounts = PayoutAccountResource::initiate(transport: $transport);
        $this->payoutMethods = PayoutMethodResource::initiate(transport: $transport);

        // Disputes
        $this->disputes = DisputeResource::initiate(transport: $transport);
        $this->disputeAlerts = DisputeAlertResource::initiate(transport: $transport);
        $this->resolutionCenter = ResolutionCenterResource::initiate(transport: $transport);

        // Commerce
        $this->shipments = ShipmentResource::initiate(transport: $transport);
        $this->leads = LeadResource::initiate(transport: $transport);
        $this->entries = EntryResource::initiate(transport: $transport);
        $this->reviews = ReviewResource::initiate(transport: $transport);
        $this->affiliates = AffiliateResource::initiate(transport: $transport);
        $this->stats = StatsResource::initiate(transport: $transport);

        // Experiences & Courses
        $this->experiences = ExperienceResource::initiate(transport: $transport);
        $this->courses = CourseResource::initiate(transport: $transport);
        $this->courseChapters = CourseChapterResource::initiate(transport: $transport);
        $this->courseLessons = CourseLessonResource::initiate(transport: $transport);
        $this->courseLessonInteractions = CourseLessonInteractionResource::initiate(transport: $transport);
        $this->courseStudents = CourseStudentResource::initiate(transport: $transport);

        // Communication
        $this->chatChannels = ChatChannelResource::initiate(transport: $transport);
        $this->dmChannels = DmChannelResource::initiate(transport: $transport);
        $this->dmMembers = DmMemberResource::initiate(transport: $transport);
        $this->messages = MessageResource::initiate(transport: $transport);
        $this->reactions = ReactionResource::initiate(transport: $transport);
        $this->forums = ForumResource::initiate(transport: $transport);
        $this->forumPosts = ForumPostResource::initiate(transport: $transport);
        $this->supportChannels = SupportChannelResource::initiate(transport: $transport);
        $this->notifications = NotificationResource::initiate(transport: $transport);

        // Advertising
        $this->adCampaigns = AdCampaignResource::initiate(transport: $transport);
        $this->adGroups = AdGroupResource::initiate(transport: $transport);
        $this->ads = AdResource::initiate(transport: $transport);

        // Apps & AI
        $this->apps = AppResource::initiate(transport: $transport);
        $this->appBuilds = AppBuildResource::initiate(transport: $transport);
        $this->aiChats = AiChatResource::initiate(transport: $transport);
        $this->tokenTransactions = CompanyTokenTransactionResource::initiate(transport: $transport);

        // Verifications
        $this->verifications = VerificationResource::initiate(transport: $transport);
    }
}
