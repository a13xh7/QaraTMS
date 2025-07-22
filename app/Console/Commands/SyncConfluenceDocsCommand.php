<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncConfluenceDocsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'confluence:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync documents from Confluence';

    private string|null $username;
    private string|null $token;
    private string $baseUrl = 'https://admin.atlassian.net/wiki/rest/api';

    public function __construct()
    {
        parent::__construct();
        $this->username = env('CONFLUENCE_USERNAME');
        $this->token = env('CONFLUENCE_API_TOKEN');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Starting Confluence sync...');
        $this->info('Starting Confluence sync...');

        try {
            // Get space information
            $space = $this->getSpaceInfo();
            $pages = $this->getPages($space);
            
            // Download each page content
            $this->info('Count pages: ' . count($pages));
            foreach ($pages as $page) {
                $this->downloadPage($page['id']);
            }

            $this->info('Sync completed successfully!');
        } catch (\Exception $e) {
            $this->error('Sync faaa: ' . $e->getMessage());
        }
    }

    private function getSpaceInfo()
    {
        $response = Http::withBasicAuth($this->username, $this->token)
            ->accept('application/json')
            ->get("{$this->baseUrl}/space", [
                'limit' => 1000
            ]);

        $data = $response->json();
        if (isset($data['results'])) {
            $spaces = $data['results'];
        } else {
            Log::error('No results key in response: ' . json_encode($data));
            $spaces = [];
        }
        $space = collect($spaces)->firstWhere('name', 'Test Engineer');
        if ($space) {
            $this->info('Sync space id: ' . $space['key']);
        } else {
            $this->error('Space "Test Engineer" not found.');
        }
        return $space['key'];
    }

    private function getPages($spaceKey)
    {
        $response = Http::withBasicAuth($this->username, $this->token)
            ->accept('application/json')
            ->get("{$this->baseUrl}/content", [
                'spaceKey' => $spaceKey,
                'type' => 'page',
                'limit' => 500
            ]);

        $this->info('Sync pages: ' . json_encode($response->json()));
        return $response->json()['results'];
    }

    private function downloadPage($pageId)
    {
        $response = Http::withBasicAuth($this->username, $this->token)
            ->accept('application/json')
            ->get("{$this->baseUrl}/content/{$pageId}", [
                'expand' => 'body.export_view,version,space'
            ]);

        $page = $response->json();

        // Clean the Confluence-specific tags
        // $cleanedValue = $this->removeConfluenceTags($page['body']['storage']['value']);

        $html = $page['body']['export_view']['value'];
        $this->info('Rendered HTML: ' . $html);
        $this->info('Page Version: ' . json_encode($page['version']['number']));
        $this->info('Page Title: ' . json_encode($page['title']));

        // Optionally, update the page array with the cleaned value
        // $page['body']['storage']['value'] = $cleanedValue;

        // Store the page content
        // Storage::put(storage_path('app/public/confluence/{$page['space']['key']}/{$pageId}.json'),
        //     json_encode($page, JSON_PRETTY_PRINT)
        // );
    }

    /**
     * Remove Confluence-specific tags and their contents from HTML.
     */
    private function removeConfluenceTags($html)
    {
        // List of tags to remove (add more as needed)
        $tags = [
            'ac:structured-macro',
            'ac:image',
            'ac:rich-text-body',
            'ac:plain-text-body',
            'ac:parameter',
            'ac:caption',
            'ac:inline-comment-marker',
            'ac:layout',
            'ac:layout-section',
            'ac:layout-cell',
            'ri:page',
            'ri:user',
            'ri:url',
        ];

        foreach ($tags as $tag) {
            // Remove opening/closing tag and content in between (non-greedy)
            $pattern = sprintf('/<%1$s\b[^>]*>.*?<\/%1$s>/s', preg_quote($tag, '/'));
            $html = preg_replace($pattern, '', $html);

            // Remove self-closing tags
            $patternSelfClosing = sprintf('/<%1$s\b[^>]*\/>/s', preg_quote($tag, '/'));
            $html = preg_replace($patternSelfClosing, '', $html);
        }

        return $html;
    }
}
