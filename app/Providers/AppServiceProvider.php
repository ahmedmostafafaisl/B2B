<?php

namespace App\Providers;

use App\Channels\FcmChannel;
use App\Repositories\ActivityLog\ActivityLogRepository;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use App\Repositories\Address\AddressRepository;
use App\Repositories\Banner\BannerRepository;
use App\Repositories\Blog\BlogRepository;
use App\Repositories\Contact\ContactRepository;
use App\Repositories\Faq\FaqRepository;
use App\Repositories\GlobalSearch\GlobalSearchRepository;
use App\Repositories\Interfaces\AddressRepositoryInterface;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use App\Repositories\Interfaces\BlogRepositoryInterface;
use App\Repositories\Interfaces\ContactRepositoryInterface;
use App\Repositories\Interfaces\FaqRepositoryInterface;
use App\Repositories\Interfaces\GlobalSearchRepositoryInterface;
use App\Repositories\Interfaces\KeyRepositoryInterface;
use App\Repositories\Interfaces\MenuItemRepositoryInterface;
use App\Repositories\Interfaces\MenuModuleRepositoryInterface;
use App\Repositories\Interfaces\PartnerRepositoryInterface;
use App\Repositories\Interfaces\PartRepositoryInterface;
use App\Repositories\Interfaces\RolePermissionInterface;
use App\Repositories\Interfaces\ServiceImageRepositoryInterface;
use App\Repositories\Interfaces\ServiceRepositoryInterface;
use App\Repositories\Interfaces\ServiceTypeRepositoryInterface;
use App\Repositories\Interfaces\ServiceTypeSpecificationRepositoryInterface;
use App\Repositories\Interfaces\SidebarRepositoryInterface;
use App\Repositories\Interfaces\SolutionImageRepositoryInterface;
use App\Repositories\Interfaces\SolutionRepositoryInterface;
use App\Repositories\Interfaces\SubjectRepositoryInterface;
use App\Repositories\Interfaces\SubpartApplicationRepositoryInterface;
use App\Repositories\Interfaces\SubpartDocRepositoryInterface;
use App\Repositories\Interfaces\SubpartFeatureRepositoryInterface;
use App\Repositories\Interfaces\SubpartModelRepositoryInterface;
use App\Repositories\Interfaces\SubPartRepositoryInterface;
use App\Repositories\Interfaces\SubpartReviewRepositoryInterface;
use App\Repositories\Interfaces\SubpartSpecificationRepositoryInterface;
use App\Repositories\Interfaces\SubServiceApplicationRepositoryInterface;
use App\Repositories\Interfaces\SubServiceFeatureRepositoryInterface;
use App\Repositories\Interfaces\SubServiceImageRepositoryInterface;
use App\Repositories\Interfaces\SubServiceModelRepositoryInterface;
use App\Repositories\Interfaces\SubServiceRepositoryInterface;
use App\Repositories\Interfaces\SubservienceDocRepositoryInterface;
use App\Repositories\Interfaces\SubservienceReviewRepositoryInterface;
use App\Repositories\Interfaces\SubservienceSpecificationRepositoryInterface;
use App\Repositories\Interfaces\TechRepositoryInterface;
use App\Repositories\Interfaces\UserLogRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Key\KeyRepository;
use App\Repositories\Part\PartRepository;
use App\Repositories\Partner\PartnerRepository;
use App\Repositories\Roles\RolePermissionRepository;
use App\Repositories\Service\ServiceImageRepository;
use App\Repositories\Service\ServiceRepository;
use App\Repositories\Service\ServiceTypeRepository;
use App\Repositories\ServiceTypeSpecification\ServiceTypeSpecificationRepository;
use App\Repositories\SideBar\MenuItemRepository;
use App\Repositories\SideBar\MenuModuleRepository;
use App\Repositories\SideBar\SidebarRepository;
use App\Repositories\Solution\SolutionImageRepository;
use App\Repositories\Solution\SolutionRepository;
use App\Repositories\Subject\SubjectRepository;
use App\Repositories\SubPart\SubPartRepository;
use App\Repositories\SubpartApplication\SubpartApplicationRepository;
use App\Repositories\SubpartDoc\SubpartDocRepository;
use App\Repositories\SubpartFeature\SubpartFeatureRepository;
use App\Repositories\SubpartModel\SubpartModelRepository;
use App\Repositories\SubpartReview\SubpartReviewRepository;
use App\Repositories\SubpartSpecification\SubpartSpecificationRepository;
use App\Repositories\SubService\SubServiceImageRepository;
use App\Repositories\SubService\SubServiceRepository;
use App\Repositories\SubServiceApplication\SubServiceApplicationRepository;
use App\Repositories\SubServiceFeature\SubServiceFeatureRepository;
use App\Repositories\SubServiceModel\SubServiceModelRepository;
use App\Repositories\SubservienceDoc\SubservienceDocRepository;
use App\Repositories\SubservienceReview\SubservienceReviewRepository;
use App\Repositories\SubservienceSpecification\SubservienceSpecificationRepository;
use App\Repositories\Task\TaskCommentRepository;
use App\Repositories\Task\TaskRepository;
use App\Repositories\Interfaces\TaskCommentRepositoryInterface;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Repositories\Tech\TechRepository;
use App\Repositories\User\UserLogRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(TechRepositoryInterface::class, TechRepository::class);
        $this->app->bind(AddressRepositoryInterface::class, AddressRepository::class);
        $this->app->bind(UserLogRepositoryInterface::class, UserLogRepository::class);
        $this->app->bind(RolePermissionInterface::class, RolePermissionRepository::class);
        $this->app->bind(ServiceRepositoryInterface::class, ServiceRepository::class);
        $this->app->bind(ServiceImageRepositoryInterface::class, ServiceImageRepository::class);
        $this->app->bind(SolutionRepositoryInterface::class, SolutionRepository::class);
        $this->app->bind(SolutionImageRepositoryInterface::class, SolutionImageRepository::class);
        $this->app->bind(PartRepositoryInterface::class, PartRepository::class);
        $this->app->bind(SubjectRepositoryInterface::class, SubjectRepository::class);
        $this->app->bind(KeyRepositoryInterface::class, KeyRepository::class);
        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);
        $this->app->bind(SubServiceImageRepositoryInterface::class, SubServiceImageRepository::class);
        $this->app->bind(SubServiceRepositoryInterface::class, SubServiceRepository::class);
        $this->app->bind(FaqRepositoryInterface::class, FaqRepository::class);
        $this->app->bind(MenuModuleRepositoryInterface::class, MenuModuleRepository::class);
        $this->app->bind(MenuItemRepositoryInterface::class, MenuItemRepository::class);
        $this->app->bind(SidebarRepositoryInterface::class, SidebarRepository::class);
        $this->app->bind(SubservienceSpecificationRepositoryInterface::class, SubservienceSpecificationRepository::class);
        $this->app->bind(SubservienceReviewRepositoryInterface::class, SubservienceReviewRepository::class);
        $this->app->bind(SubservienceDocRepositoryInterface::class, SubservienceDocRepository::class);
        $this->app->bind(SubServiceFeatureRepositoryInterface::class, SubServiceFeatureRepository::class);
        $this->app->bind(SubServiceApplicationRepositoryInterface::class, SubServiceApplicationRepository::class);
        $this->app->bind(SubServiceModelRepositoryInterface::class, SubServiceModelRepository::class);
        $this->app->bind(SubPartRepositoryInterface::class, SubPartRepository::class);
        $this->app->bind(SubpartSpecificationRepositoryInterface::class, SubpartSpecificationRepository::class);
        $this->app->bind(SubpartReviewRepositoryInterface::class, SubpartReviewRepository::class);
        $this->app->bind(SubpartDocRepositoryInterface::class, SubpartDocRepository::class);
        $this->app->bind(SubpartFeatureRepositoryInterface::class, SubpartFeatureRepository::class);
        $this->app->bind(SubpartApplicationRepositoryInterface::class, SubpartApplicationRepository::class);
        $this->app->bind(SubpartModelRepositoryInterface::class, SubpartModelRepository::class);
        $this->app->bind(ServiceTypeRepositoryInterface::class, ServiceTypeRepository::class);
        $this->app->bind(BannerRepositoryInterface::class, BannerRepository::class);
        $this->app->bind(BlogRepositoryInterface::class, BlogRepository::class);
        $this->app->bind(PartnerRepositoryInterface::class, PartnerRepository::class);
        $this->app->bind(ServiceTypeSpecificationRepositoryInterface::class, ServiceTypeSpecificationRepository::class);
        $this->app->bind(GlobalSearchRepositoryInterface::class, GlobalSearchRepository::class);
        $this->app->bind(ActivityLogRepositoryInterface::class, ActivityLogRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(TaskCommentRepositoryInterface::class, TaskCommentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->make(ChannelManager::class)->extend('fcm', function ($app) {
            return new FcmChannel;
        });
    }
}
