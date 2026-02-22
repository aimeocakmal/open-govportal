<?php

namespace Tests\Feature;

use App\Models\Broadcast;
use App\Models\SearchOverride;
use App\Models\StaffDirectory;
use App\Services\SearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchServiceTest extends TestCase
{
    use RefreshDatabase;

    private SearchService $searchService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchService = new SearchService;
    }

    public function test_search_returns_matching_broadcasts(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Pengumuman Penting Kerajaan',
            'title_en' => 'Important Government Announcement',
            'status' => 'published',
            'slug' => 'pengumuman-penting',
        ]);
        $broadcast->syncSearchContent();

        $results = $this->searchService->search('Pengumuman', 'ms');

        $this->assertNotEmpty($results);
        $this->assertStringContainsString('Pengumuman', $results->first()->title);
    }

    public function test_search_returns_empty_for_no_matches(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Siaran Akhbar',
            'status' => 'published',
            'slug' => 'siaran-akhbar',
        ]);
        $broadcast->syncSearchContent();

        $results = $this->searchService->search('xyznonexistent', 'ms');

        $this->assertEmpty($results);
    }

    public function test_search_override_takes_priority(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Digital Malaysia',
            'status' => 'published',
            'slug' => 'digital-malaysia',
        ]);
        $broadcast->syncSearchContent();

        SearchOverride::factory()->create([
            'query' => 'digital',
            'title_ms' => 'Override Result',
            'title_en' => 'Override Result EN',
            'url' => '/special',
            'is_active' => true,
            'priority' => 100,
        ]);

        $results = $this->searchService->search('digital', 'ms');

        $this->assertNotEmpty($results);
        $this->assertEquals('Override Result', $results->first()->title);
        $this->assertEquals('override', $results->first()->type);
    }

    public function test_draft_content_not_indexed_by_observer(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Draft Siaran',
            'status' => 'draft',
            'slug' => 'draft-siaran',
        ]);

        // Manually trigger observer
        $observer = new \App\Observers\SearchContentObserver;
        $observer->saved($broadcast);

        $this->assertDatabaseMissing('searchable_content', [
            'searchable_type' => Broadcast::class,
            'searchable_id' => $broadcast->id,
        ]);
    }

    public function test_published_content_indexed_by_observer(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Published Siaran',
            'status' => 'published',
            'slug' => 'published-siaran',
        ]);

        $observer = new \App\Observers\SearchContentObserver;
        $observer->saved($broadcast);

        $this->assertDatabaseHas('searchable_content', [
            'searchable_type' => Broadcast::class,
            'searchable_id' => $broadcast->id,
        ]);
    }

    public function test_deleted_content_removed_from_index(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'To Delete',
            'status' => 'published',
            'slug' => 'to-delete',
        ]);
        $broadcast->syncSearchContent();

        $this->assertDatabaseHas('searchable_content', [
            'searchable_id' => $broadcast->id,
        ]);

        $observer = new \App\Observers\SearchContentObserver;
        $observer->deleted($broadcast);

        $this->assertDatabaseMissing('searchable_content', [
            'searchable_id' => $broadcast->id,
        ]);
    }

    public function test_staff_directory_is_indexed(): void
    {
        $staff = StaffDirectory::factory()->create([
            'name' => 'Ahmad bin Ali',
            'position_ms' => 'Pengarah',
            'department_ms' => 'Jabatan IT',
            'is_active' => true,
        ]);
        $staff->syncSearchContent();

        $this->assertDatabaseHas('searchable_content', [
            'searchable_type' => StaffDirectory::class,
            'searchable_id' => $staff->id,
            'title_ms' => 'Ahmad bin Ali',
        ]);

        $results = $this->searchService->search('Ahmad', 'ms');
        $this->assertNotEmpty($results);
    }

    public function test_search_respects_locale(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Tajuk Bahasa Melayu',
            'title_en' => 'English Language Title',
            'status' => 'published',
            'slug' => 'test-locale',
        ]);
        $broadcast->syncSearchContent();

        $msResults = $this->searchService->search('Tajuk', 'ms');
        $this->assertNotEmpty($msResults);

        $enResults = $this->searchService->search('English', 'en');
        $this->assertNotEmpty($enResults);
    }

    public function test_empty_query_returns_empty(): void
    {
        $results = $this->searchService->search('', 'ms');
        $this->assertEmpty($results);

        $results = $this->searchService->search('   ', 'ms');
        $this->assertEmpty($results);
    }
}
