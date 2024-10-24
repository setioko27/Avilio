<?php
namespace Avilio;
class PageTemplate
{
    private $templateDir;
    private $data = [];

    protected $context = [];

    public function __construct($templateDir = '')
    {
        $this->templateDir = $templateDir ?: get_template_directory() . '/template-parts';
    }

    public function context($additionalContext = [])
    {
        if (is_single()) {
            global $post;
            $this->context = array_merge($this->context, [
                'title' => get_the_title($post),
                'content' => apply_filters('the_content', $post->post_content),
                'excerpt' => get_the_excerpt($post),
                'author' => get_the_author(),
                'date' => get_the_date('', $post),
                'featured_image' => get_the_post_thumbnail_url($post, 'full'),
            ]);
        } elseif (is_archive() || is_home()) {
            $this->context['title'] = get_the_archive_title();
            $this->context['posts'] = $this->get_posts();
        } elseif (is_page()) {
            global $post;
            $this->context = array_merge($this->context, [
                'title' => get_the_title($post),
                'content' => apply_filters('the_content', $post->post_content),
                'featured_image' => get_the_post_thumbnail_url($post, 'full'),
                'url' => get_the_permalink($post)
            ]);


        } elseif (is_tax()) {
            $term = get_queried_object();
            $this->context = array_merge($this->context, [
                'title' => $term->name,
                'description' => $term->description,
                'featured_image' => get_field('featured_image', 'term_' . $term->term_id),
                'posts' => $this->get_posts([
                    'tax_query' => [
                        [
                            'taxonomy' => $term->taxonomy,
                            'field' => 'term_id',
                            'terms' => $term->term_id,
                        ]
                    ]
                ])
            ]);
        }

        $this->context = array_merge($this->context, $additionalContext);
    }

    public function part($template, $data = [])
    {
        $this->data = array_merge($this->data, ['post' => $this->context], $data);
        $templateFile = $this->templateDir . '/' . $template . '.php';

        if (!file_exists($templateFile)) {
            throw new Exception("Template file {$templateFile} not found.");
        }

        extract($this->data);
        include $templateFile;
    }

    public function render($data = [])
    {
        foreach ($data as $section_data) {
            if (isset($section_data['path'])) {
                $this->part($section_data['path'], $section_data);
            } elseif (isset($section_data['part'])) {
                get_template_part($section_data['part']);
            }

        }
    }

    public function __get($key)
    {
        return $this->data[$key] ?? null;
    }


}