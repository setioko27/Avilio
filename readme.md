# Avilio

Avilio is a helper class designed to simplify the workload of WordPress developers. Born out of frustration with the repetitive process of copying and pasting code from previous projects at the start of every new WordPress development, this plugin aims to streamline the workflow, providing an easier and more efficient setup. 

Initially, Avilio was created to meet the specific needs of its author, who frequently relies on the Advanced Custom Fields (ACF Pro) plugin in WordPress projects. As such, Avilio integrates seamlessly with ACF Pro, offering support for developers who utilize it as a core component of their projects.

While the primary goal of Avilio is to serve its author's workflow, it may also prove useful for other developers encountering similar challenges in their own projects. If you find yourself frequently duplicating code from one WordPress project to the next, Avilio could help alleviate that burden.

## Installation

To install the plugin via Composer, run the following command:

```bash
composer require tio27/avilio
```

Make sure your WordPress environment is set up to handle Composer-based plugins.

## Usage

After installing Avilio, you can integrate it into your workflow. Since Avilio is built with ACF Pro in mind, it is recommended that ACF Pro be installed and activated in your WordPress setup to take full advantage of the helper class.




Avilio is designed to simplify and streamline common WordPress development tasks. Below are a few examples of how Avilio can be used to improve your workflow:

### 1. Simplifying `functions.php` for Cleaner, OOP-based Structure

Instead of cluttering your `functions.php` with long procedural code, Avilio enables you to structure your WordPress project using Object-Oriented Programming (OOP) principles. This leads to more maintainable and organized code. For example:

Before Avilio:

```php
// functions.php
add_action('init', 'register_custom_post_type');
function register_custom_post_type() {
    register_post_type('custom', array(
        'labels' => array(
            'name' => __('Custom Post'),
        ),
        'public' => true,
    ));
}

function wpdocs_theme_name_scripts() {
    wp_enqueue_script( 'script1-name', get_template_directory_uri() . '/js/example1.js', array( 'script_one_js' ), '1.0.0', false );
    wp_enqueue_script( 'script2-name', get_template_directory_uri() . '/js/example2.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'wpdocs_theme_name_scripts' );

function wpdocs_theme_name_style() {
    wp_enqueue_style( 'style-name', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'wpdocs_theme_name_style' );

```
With Avilio:
```php
// functions.php
use Avilio\Theme;

$myTheme = new Theme;
$myTheme->addAction('init',function(){
    register_post_type('custom', array(
        'labels' => array(
            'name' => __('Custom Post'),
        ),
        'public' => true,
    ));
})

$myTheme->addScripts([
    [
        'handle'=>'script1-name',
        'src'=>get_template_directory_uri() . '/js/example1.js',
        'deps'=> ['script_one_js'],
    ],
    [
        'handle'=>'script2-name',
        'src'=>get_template_directory_uri() . '/js/example2.js',
    ]
])

$myTheme->addStyles([
    [
        'handle' => 'style-name',
        'src' => get_stylesheet_uri()

    ]
])

//other commonly used examples
$myTheme->addImageSizes([
	['name' => 'thumbnail-180', 'width' => 180, 'height' => 227, 'crop' => true],
	['name' => 'thumbnail-240', 'width' => 240, 'height' => 240, 'crop' => true]
]);

$myTheme->addNavMenus([
	'header-menu' => "Header Menu",
	"footer-menu-1" => "Footer Menu",
	'sidebar-menu' => "Sidebar Menu"
]);
```
### 2. Simplifying ACF Pro Templating
Working with ACF Pro fields often involves repetitive template code. Avilio helps to simplify ACF templating by abstracting away much of the boilerplate code, allowing you to focus on the logic of your template.

Before Avilio:
```php
// get value from acf field
$display = get_field('field_name');
$display_option = get_field('field_option_name','option');

// repeater field
if( have_rows('parent_repeater') ):
    while( have_rows('parent_repeater') ) : the_row();

        $title = get_sub_field('sub_title');
        $desc = get_sub_field('sub_description');
        $image = get_sub_field('sub_image');
    endwhile;
endif;
```
With Avilio:
```php
use Avilio\ACF;

// get value from acf field
$display = ACF::field('field_name');
$display_option = ACF::option('field_option_name');

//repeater field
$repeater_field = ACF::field('parent_repeater',[
    'title' => 'sub_title',
    'desc' => 'sub_description',
    'image' => 'sub_image'
])
// The output from the repeater field above will be in the form of an array that you can use in your template.
```

### 3. Modular and Reusable Page Templates
Avilio promotes a modular approach to building WordPress page templates, encouraging the separation of template logic into smaller, reusable components. This helps maintain a cleaner and more maintainable structure for your page templates.

Before Avilio:
```php
// page.php
<?php 
get_header();
?>
<div class="page">
    <section class="section1">
        <h2><?php echo get_field('section1_title') ?></h2>
        <h4><?php echo get_field('section1_subtitle') ?></h4>
        <img src="<?php echo get_field('section1_image') ?>" alt="image">
        <div class="section1__desc">
            <?php echo get_field('section1_desc') ?>
        </div>
    </section>
    <section class="section2">
        <h2><?php echo get_field('section2_title') ?></h2>
        <h4><?php echo get_field('section2_subtitle') ?></h4>
        <?php if( have_rows('slides') ): ?>
            <ul class="slides">
                <?php while( have_rows('section2_slides') ): the_row(); 
                    $image = get_sub_field('section2_image');
                ?>
                    <li>
                        <?php echo wp_get_attachment_image( $image, 'full' ); ?>
                        <p><?php echo acf_esc_html( get_sub_field('section2_caption') ); ?></p>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>


<?php get_footer(); ?>
```

With Avilio:
```php
// page.php
<?php 
use Avilio/PageTemplate;
get_header();

$template = new PageTemplate();
$data = [
    'section1' => [
        'path' => 'content/section1',
        'title' => $template::field('section1_title'),
        'subtitle' => $template::field('subtitle'),
        'image' => $template::field('image'),
        'desc' => $template::field('desc'),
    ],
    'section2' => [
        'path' => 'content/section2',
        'title' => $template::field('section2_title'),
        'subtitle' => $template::field('section2_subtitle'),
        'lists' => $template::field('slides',[
            'image' => 'section2_image',
            'caption' => 'section2_caption'
        ]),
    ],
]

?>

<div class="page">
    <?php $template->render($data) ?>
</div>

<?php get_footer(); ?>
```

```php 
//template-parts/content/section1.php
<section class="section1">
    <h2><?php echo $title ?></h2>
    <h4><?php echo $subtitle ?></h4>
    <img src="<?php echo $image ?>" alt="image">
    <div class="section1__desc">
        <?php echo $desc ?>
    </div>
</section>
```

```php
//template-parts/content/section2.php
<section class="section2">
    <h2><?php echo $title ?></h2>
    <h4><?php echo $subtitle ?></h4>
    <?php if( !empty($lists) ): ?>
        <ul class="slides">
            <?php foreach($lists as $list) ?>
                <li>
                    <?php echo $image ?> 
                    // If the field image returns an ID, Avilio will automatically generate it into a URL.
                    <p><?php echo $caption ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
```

## Basic Setup
Install the plugin via Composer.
Add the necessary configurations to your theme or plugin, following Avilio's available methods.
Start leveraging the predefined functions and helpers to avoid repetitive tasks and boilerplate code.
Requirements
WordPress 5.0 or higher
PHP 7.4 or higher
ACF Pro plugin (optional but recommended)
Contributing
If you encounter any issues or would like to contribute improvements, feel free to open a pull request or issue on the GitHub repository. We welcome feedback and contributions from the community.

## License
This plugin is open-sourced software licensed under the MIT license.


