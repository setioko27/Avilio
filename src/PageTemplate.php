<?php
namespace Avilio;

class PageTemplate
{
    private string $templateDir;
    private string $baseDir;
    private array $data = [];
    protected array $context = [];

    private const CONTEXT_MAPPINGS = [
        'single' => 'getSingleContext',
        'archive' => 'getArchiveContext',
        'page' => 'getPageContext',
        'tax' => 'getTaxonomyContext'
    ];

    public function __construct(string $page = '', string $templateDir = '')
    {
        $this->baseDir = $page;
        $this->templateDir = $templateDir ?: get_template_directory() . '/template-parts';
    }

    public function context(array $additionalContext = []): void
    {
        $pageType = $this->determinePageType();
        if (isset(self::CONTEXT_MAPPINGS[$pageType])) {
            $method = self::CONTEXT_MAPPINGS[$pageType];
            $this->context = array_merge(
                $this->context,
                $this->{$method}()
            );
        }

        $this->context = array_merge($this->context, $additionalContext);
    }

    private function determinePageType(): string
    {
        switch (true) {
            case is_single():
                return 'single';
            case is_archive() || is_home():
                return 'archive';
            case is_page():
                return 'page';
            case is_tax():
                return 'tax';
            default:
                return '';
        }
    }

    private function getSingleContext(): array
    {
        global $post;
        return [
            'title' => get_the_title($post),
            'content' => apply_filters('the_content', $post->post_content),
            'excerpt' => get_the_excerpt($post),
            'author' => get_the_author(),
            'date' => get_the_date('', $post),
            'featured_image' => get_the_post_thumbnail_url($post, 'full'),
            'category' => get_the_category()
        ];
    }

    private function getArchiveContext(): array
    {
        return ['title' => get_the_archive_title()];
    }

    private function getPageContext(): array
    {
        global $post;
        return [
            'title' => get_the_title($post),
            'content' => apply_filters('the_content', $post->post_content),
            'featured_image' => get_the_post_thumbnail_url($post, 'full'),
            'url' => get_the_permalink($post)
        ];
    }

    private function getTaxonomyContext(): array
    {
        $term = get_queried_object();
        return [
            'title' => $term->name,
            'description' => $term->description,
            'featured_image' => get_field('featured_image', 'term_' . $term->term_id),
            'posts' => $this->get_posts([
                'tax_query' => [[
                    'taxonomy' => $term->taxonomy,
                    'field' => 'term_id',
                    'terms' => $term->term_id,
                ]]
            ])
        ];
    }

    public function get_context(): array
    {
        $this->context();
        return $this->context;
    }

    public function part(string $template, array $data = []): void
    {
        $templateFile = $this->resolveTemplatePath($template);
        
        if (!$this->validateTemplate($templateFile)) {
            return;
        }

        $this->renderTemplate($templateFile, $data);
    }

    private function resolveTemplatePath(string $template): string
    {
        return $this->templateDir . '/' . $template . '.php';
    }

    private function validateTemplate(string $templateFile): bool
    {
        if (!file_exists($templateFile)) {
            error_log("Template File not found: {$templateFile}");
            echo "Template File is : {$templateFile}";
            return false;
        }
        return true;
    }

    private function renderTemplate(string $templateFile, array $data): void
    {
        $this->data = array_merge($this->data, ['post' => $this->context], $data);
        extract($this->data);
        include $templateFile;
    }

    public function render(array $data = []): void
    {
        foreach ($data as $path => $sectionData) {
            if (isset($sectionData['part'])) {
                get_template_part($sectionData['part']);
                continue;
            }

            $path = $this->resolvePath($path);
            $this->part($path, $sectionData);
        }
    }

    private function resolvePath(string $path): string
    {
        return strpos($path, "~") !== 0 
            ? "{$this->baseDir}/{$path}" 
            : substr($path, 1);
    }

    public function __get(string $key)
    {
        return $this->data[$key] ?? null;
    }
}