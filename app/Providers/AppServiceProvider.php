<?php

namespace App\Providers;

use App\Listeners\LogSuccessfulLogin;
use App\Models\Achievement;
use App\Models\Address;
use App\Models\Broadcast;
use App\Models\Celebration;
use App\Models\FooterSetting;
use App\Models\HeroBanner;
use App\Models\MinisterProfile;
use App\Models\PageCategory;
use App\Models\Policy;
use App\Models\QuickLink;
use App\Models\StaffDirectory;
use App\Models\StaticPage;
use App\Observers\ContentRevisionObserver;
use App\Observers\EmbeddingObserver;
use App\Observers\PageCacheObserver;
use App\Observers\SearchContentObserver;
use App\Policies\ActivityLogPolicy;
use App\Policies\RolePolicy;
use App\Services\PublicNavigationService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Activity::class, ActivityLogPolicy::class);

        Event::listen(Login::class, LogSuccessfulLogin::class);

        // Register search content observer for FTS indexing
        $searchableModels = [Broadcast::class, Achievement::class, StaffDirectory::class, Policy::class];
        foreach ($searchableModels as $model) {
            $model::observe(SearchContentObserver::class);
        }

        // Register embedding observer for AI vector indexing
        $embeddableModels = [Broadcast::class, Achievement::class, Policy::class, StaffDirectory::class];
        foreach ($embeddableModels as $model) {
            $model::observe(EmbeddingObserver::class);
        }

        // Register content revision observer for versioning
        $revisionableModels = [Broadcast::class, Achievement::class, Celebration::class, Policy::class, StaticPage::class];
        foreach ($revisionableModels as $model) {
            $model::observe(ContentRevisionObserver::class);
        }

        // Register page cache observer for cache invalidation on content changes
        $cacheableModels = [
            HeroBanner::class, QuickLink::class, Broadcast::class,
            Achievement::class, Policy::class, MinisterProfile::class,
            Address::class, StaticPage::class, PageCategory::class,
            FooterSetting::class,
        ];
        foreach ($cacheableModels as $model) {
            $model::observe(PageCacheObserver::class);
        }

        RichEditor::configureUsing(function (RichEditor $editor): void {
            $editor->fileAttachmentsDirectory('rich-editor-attachments');
        });

        FileUpload::configureUsing(function (FileUpload $upload): void {
            $upload->visibility('public');
        });

        // Share navigation and footer data from DB (cached in PublicNavigationService)
        View::composer('*', function ($view) {
            $service = app(PublicNavigationService::class);
            $view->with('navItems', $service->getHeaderItems());
            $view->with('footerMenuItems', $service->getFooterMenuItems());
            $view->with('footerBranding', $service->getFooterBranding());
            $view->with('footerSocialLinks', $service->getFooterSocialLinks());
            $view->with('footerData', $service->getSocialUrls());
        });
    }
}
