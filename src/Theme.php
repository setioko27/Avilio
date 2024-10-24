<?php
namespace Avilio;

class Theme
{

    public function addAction($hook, $function, $priority = 10, $accepted_args = 1)
    {
        add_action($hook, function (...$args) use ($function) {
            return call_user_func_array($function, $args);
        }, $priority, $accepted_args);
    }

    public function addFilter($hook, $function, $priority = 10, $accepted_args = 1)
    {
        add_filter($hook, function (...$args) use ($function) {
            return call_user_func_array($function, $args);
        }, $priority, $accepted_args);
    }
    private function actionAfterSetup($function)
    {
        $this->addAction("after_setup_theme", $function);
    }

    private function actionEnqueueScripts($function)
    {
        $this->addAction("wp_enqueue_scripts", $function);
    }

    public function __construct()
    {
        $this->addSupport('title-tag')
            ->addSupport('custom-logo', [
                'height' => 250,
                'width' => 250,
                'flex-width' => true,
                'flex-height' => true,
                'unlink-homepage-logo' => true,
            ])
            ->addSupport('post-thumbnails')
            ->addSupport('html5', [
                'search-form',
                'comment-form',
                'comment-list',
                'gallery',
                'caption'
            ])
            ->addStyles([
                'handle' => "theme-style",
                "src" => get_stylesheet_uri()
            ]);

        
    }

    public function addSupport($feature, $options = null)
    {
        $this->actionAfterSetup(function () use ($feature, $options) {
            if ($options) {
                add_theme_support($feature, $options);
            } else {
                add_theme_support($feature);
            }
        });
        return $this;
    }

    public function removeSupport($feature)
    {
        $this->actionAfterSetup(function () use ($feature) {
            remove_theme_support($feature);
        });
        return $this;
    }

    public function loadTextDomain($domain, $path = false)
    {
        $this->actionAfterSetup(function () use ($domain, $path) {
            load_theme_textdomain($domain, $path);
        });
        return $this;
    }




    public function addImageSizes($sizes = [])
    {
        $this->actionAfterSetup(function () use ($sizes) {
            foreach ($sizes as $size) {
                $name = $size['name'] ?? '';
                $width = $size['width'] ?? 0;
                $height = $size['height'] ?? 0;
                $crop = $size['crop'] ?? false;

                if (!empty($name)) {
                    add_image_size($name, $width, $height, $crop);
                }
            }
        });
        return $this;
    }

    public function removeImageSize($name)
    {
        $this->actionAfterSetup(function () use ($name) {
            remove_image_size($name);
        });
        return $this;
    }

    public function addStyles($styles = [])
    {
        $this->actionEnqueueScripts(function () use ($styles) {
            foreach ($styles as $style) {
                $handle = $style['handle'] ?? '';
                $src = $style['src'] ?? '';
                $deps = $style['deps'] ?? [];
                $ver = $style['ver'] ?? false;
                $media = $style['media'] ?? 'all';

                if (!empty($handle) && !empty($src)) {
                    wp_enqueue_style($handle, $src, $deps, $ver, $media);
                }
            }
        });
        return $this;
    }

    public function addAdminStyle($handle = '', $src = '', $deps = false, $ver = '1.0.0')
    {
        $this->addAction('admin_enqueue_scripts', function () use ($handle, $src, $deps, $ver) {
            wp_enqueue_style($handle, $src, $deps, $ver);
        });
        return false;
    }

    public function addScripts($scripts = [])
    {
        $this->actionEnqueueScripts(function () use ($scripts) {
            foreach ($scripts as $script) {
                $handle = $script['handle'] ?? '';
                $src = $script['src'] ?? '';
                $deps = $script['deps'] ?? [];
                $ver = $script['ver'] ?? false;
                $inFooter = $script["in_footer"] ?? true;

                if (!empty($handle) && !empty($src)) {
                    wp_enqueue_script($handle, $src, $deps, $ver, $inFooter);
                }
            }
        });
        return $this;
    }



    public function removeStyle($handle)
    {
        $this->actionEnqueueScripts(function () use ($handle) {
            wp_dequeue_style($handle);
            wp_deregister_style($handle);
        });
        return $this;
    }

    public function removeScript($handle)
    {
        $this->actionEnqueueScripts(function () use ($handle) {
            wp_dequeue_script($handle);
            wp_deregister_script($handle);
        });
        return $this;
    }

    public function addNavMenus($locations = [])
    {
        $this->actionAfterSetup(function () use ($locations) {
            register_nav_menus($locations);
        });
        return $this;
    }

    public function addNavMenu($location, $description)
    {
        $this->actionAfterSetup(function () use ($location, $description) {
            register_nav_menu($location, $description);
        });
        return $this;
    }

    public function removeNavMenu($location)
    {
        $this->actionAfterSetup(function () use ($location) {
            unregister_nav_menu($location);
        });
        return $this;
    }

    
}
