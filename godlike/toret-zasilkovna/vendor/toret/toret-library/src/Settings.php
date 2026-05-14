<?php

namespace ToretZasilkovna\Toret\Library;

class Settings extends LibraryManager
{
    use SettingsFieldsTrait;

    protected bool $isDev;

    protected array $manifest;

    public function __construct()
    {
        $this->isDev = file_exists(__DIR__ . '/../vite.dev');
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts_and_styles'));
    }

    private function getManifest(): array
    {
        if (!isset($this->manifest)) {
            $manifestPath = __DIR__ . '/dist/manifest.json';
            if (file_exists($manifestPath)) {
                $this->manifest = json_decode(file_get_contents($manifestPath), true);
            } else {
                $this->manifest = [];
            }
        }
        return $this->manifest;
    }

    function enqueue_admin_scripts_and_styles($hook): void
    {
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';
        $post_type = $screen ? $screen->post_type : '';

        $allowedSreens = ['toret-plugins_page_toret-eu-vat', 'toret-plugins_page_toret-ceskaposta', 'toret-plugins_page_toret-thepay', 'toret-plugins_page_toret-ybox24'];
        $isAllowed = false;

        if (in_array($screen_id, $allowedSreens)) {
            $isAllowed = true;
        }

        if ($hook === 'term.php' || $hook === 'edit-tags.php') {
            $isAllowed = true;
        }

        if ($post_type === 'product') {
            $isAllowed = true;
        }

        if (!$isAllowed) {
            return;
        }

        $manifest = $this->getManifest();
        $entry = 'src/assets/js/toret-settings.js';

        if (isset($manifest[$entry])) {
            $distUrl = plugins_url('/dist/', __FILE__);
            $jsFile = $manifest[$entry]['file'];
            wp_enqueue_script(self::TORET_LIBRARY_PREFIX . '-settings-js', $distUrl . $jsFile, [], time(), true);

            if (isset($manifest[$entry]['css'])) {
                foreach ($manifest[$entry]['css'] as $cssFile) {
                    wp_enqueue_style(self::TORET_LIBRARY_PREFIX . '-settings-css', $distUrl . $cssFile, [], time());
                }
            }
        }

        wp_localize_script(self::TORET_LIBRARY_PREFIX . '-settings-js', 'ToretZasilkovnaSettingsAjax', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);

        add_filter('script_loader_tag', function ($tag, $handle) {
            if (in_array($handle, ['vite-client', self::TORET_LIBRARY_PREFIX . '-settings-js'])) {
                return str_replace(' src=', ' type="module" defer src=', $tag);
            }
            return $tag;
        }, 10, 2);
    }

    public static function drawAdminMenuPage(
            array   $menu_items,
            array   $fields,
            string  $title,
            string  $subtitle,
            string  $slug,
            array   $settingsButtons,
            bool    $save_per_section = false,
            ?string $back_link = null,
            string  $layout = 'horizontal',
            array   $wide_tabs = [],
            bool    $auto_hide_single_submenu = true
    ): void {
        $first_tab_key = !empty($menu_items) ? array_key_first($menu_items) : 'general';

        $grouped_fields = [];
        foreach ($fields as $field) {
            $section = $field['section'] ?? 'general';
            $sub_section = $field['sub_section'] ?? 'zzz_default';
            $grouped_fields[$section][$sub_section][] = $field;
        }

        $requested_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : '';

        $js_config = [
                'requestedTab' => $requested_tab,
                'validTabs'    => array_keys($menu_items),
                'wideTabs'     => $wide_tabs,
                'firstTab'     => $first_tab_key
        ];

        $js_config_attr = htmlspecialchars(json_encode($js_config), ENT_QUOTES, 'UTF-8');
        ?>

        <div class="toret-plugin-settings">
            <div class="wrap settings-plugin-wrapper mx-auto transition-all duration-300 ease-in-out px-3 md:px-0"
                 x-data="toretSettings(<?php echo $js_config_attr; ?>)"
                 :class="wideTabs.includes(activeTab) ? 'max-w-[98%] px-6' : 'max-w-7xl'"
                 x-cloak>

                <div class="flex justify-between items-center mt-2 md:mt-4 mb-4 md:mb-8 pb-3 border-b">
                    <div>
                        <h1 class="text-3xl font-bold text-toret-800"><?php echo esc_html($title); ?></h1>
                        <p class="text-gray-500 mt-1"><?php echo $subtitle; ?></p>
                    </div>

                    <?php if ($back_link && $layout === 'horizontal'): ?>
                        <div class="mb-1">
                            <a href="<?php echo esc_url($back_link); ?>"
                               class="w-full mb-4 <?php echo self::get_button_classes('secondary'); ?>">
                                <span class="dashicons dashicons-arrow-left-alt mr-2"></span>
                                <?php echo $settingsButtons['back']; ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($menu_items)) : ?>

                    <div>
                        <?php if ($layout === 'vertical'): ?>

                            <div class="md:grid md:grid-cols-12 md:gap-8">
                                <aside class="md:col-span-3 mb-4 md:mb-0 mt-4 md:mt-16 md:sticky md:top-12 self-start">
                                    <?php if ($back_link): ?>
                                        <a href="<?php echo esc_url($back_link); ?>"
                                           class="w-full mb-4 <?php echo self::get_button_classes('secondary'); ?>">
                                            <span class="dashicons dashicons-arrow-left-alt mr-2"></span>
                                            <?php echo $settingsButtons['back']; ?>
                                        </a>
                                    <?php endif; ?>

                                    <nav class="space-y-1 bg-white shadow-sm rounded-lg p-2 border  max-h-[85vh] overflow-y-auto">
                                        <?php foreach ($menu_items as $key => $menu_title) : ?>

                                            <a href="#"
                                               @click.prevent="activeTab = '<?= esc_attr($key) ?>'; scrollToTop()"
                                               :class="{
                                        'bg-toret-50 text-toret-800 border-toret-colborder font-semibold shadow-sm': activeTab === '<?= esc_attr($key) ?>',
                                        'text-toret-coltext hover:bg-toret-50 hover:text-toret-800 border-transparent': activeTab !== '<?= esc_attr($key) ?>'
                                        }"
                                               class="group flex items-center px-3 py-2.5 text-sm font-medium border rounded-md transition-all duration-150 ease-in-out">
                                        <span :class="activeTab === '<?= esc_attr($key) ?>' ? 'bg-toret-600' : 'bg-transparent group-hover:bg-toret-200'"
                                              class="w-1.5 h-1.5 mr-3 rounded-full transition-colors duration-150"></span>
                                                <?= esc_html($menu_title) ?>
                                            </a>

                                            <?php
                                            $valid_subsections = [];
                                            if (isset($grouped_fields[$key])) {
                                                foreach ($grouped_fields[$key] as $sub_title => $sub_fields) {
                                                    if ($sub_title !== 'zzz_default') {
                                                        $valid_subsections[$sub_title] = $sub_fields;
                                                    }
                                                }
                                            }

                                            $should_show_submenu = count($valid_subsections) > 0;
                                            if ($auto_hide_single_submenu && count($valid_subsections) <= 1) {
                                                $should_show_submenu = false;
                                            }
                                            ?>

                                            <?php if ($should_show_submenu): ?>
                                                <div x-show="activeTab === '<?= esc_attr($key) ?>'"
                                                     x-collapse
                                                     class="hidden md:block pl-7 space-y-1 pb-2">
                                                    <?php foreach ($valid_subsections as $sub_title => $sub_fields): ?>
                                                        <?php
                                                        $sub_id = sanitize_title($key . '-' . $sub_title);
                                                        ?>
                                                        <a href="#<?= $sub_id ?>"
                                                           @click.prevent="scrollToSub('<?= $sub_id ?>')"
                                                           class="block px-2 py-1 text-xs text-gray-500 hover:text-toret-600 hover:bg-toret-50 rounded transition-colors cursor-pointer">
                                                            <?= esc_html($sub_title) ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                        <?php endforeach; ?>
                                    </nav>
                                </aside>

                                <main class="md:col-span-9">
                                    <?php self::renderTabContent($menu_items, $grouped_fields, $settingsButtons, $isVertical = true, $save_per_section, $slug); ?>
                                </main>
                            </div>

                        <?php else: ?>
                            <div class="border-b mb-6 overflow-x-auto">
                                <nav class="flex -mb-px space-x-6">
                                    <?php foreach ($menu_items as $key => $menu_title) : ?>
                                        <a href="#"
                                           @click.prevent="activeTab = '<?= esc_attr($key) ?>'"
                                           :class="{
                                    'border-toret-600 text-toret-800': activeTab === '<?= esc_attr($key) ?>',
                                    'border-transparent text-gray-500 hover:text-toret-800 hover:border-toret-300': activeTab !== '<?= esc_attr($key) ?>'
                                    }"
                                           class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-colors focus:outline-none">
                                            <?= esc_html($menu_title) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </nav>
                            </div>
                            <div>
                                <?php self::renderTabContent($menu_items, $grouped_fields, $settingsButtons, $isVertical = false, $save_per_section, $slug); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <form method="post">
                        <div class="bg-white shadow-md rounded-xl border  p-6">
                            <?php
                            if (isset($grouped_fields)) {
                                foreach ($grouped_fields as $section => $subsections) {
                                    foreach ($subsections as $sub_section_title => $fields_in_subsection) {
                                        if ($sub_section_title !== 'zzz_default') echo "<h3 class='text-base font-bold text-toret-800 mb-2 mt-8 first:mt-0'>" . esc_html($sub_section_title) . "</h3>";
                                        echo "<div class='bg-toret-50 border  rounded-lg p-4 space-y-4'>";
                                        foreach ($fields_in_subsection as $field) self::renderField($field);
                                        echo "</div>";
                                    }
                                }
                            }
                            ?>
                        </div>
                        <?php if (!$save_per_section): ?>
                            <div class="mt-4">
                                <button type="submit" name="save_settings" value="<?= esc_attr($slug) ?>"
                                        class="button button-primary px-5 py-2.5 text-sm font-medium rounded-lg"><?= esc_html($settingsButtons['submit']) ?></button>
                            </div>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    private static function renderTabContent($menu_items, $grouped_fields, $settingsButtons, $isVertical, $save_per_section, $slug)
    {
        foreach ($menu_items as $section_key => $section_title) : ?>
            <div x-show="activeTab === '<?= esc_attr($section_key) ?>'"
                    <?php if ($isVertical): ?> x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" <?php endif; ?>
                 class="space-y-6">

                <?php if ($isVertical): ?>
                    <div class="pb-1 md:pb-2">
                        <h2 class="text-2xl font-bold text-toret-800 !mb-0 !mt-0"><?= esc_html($section_title) ?></h2>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">

                    <?php
                    if (isset($grouped_fields[$section_key])) {
                        foreach ($grouped_fields[$section_key] as $sub_section_title => $fields_in_subsection) {

                            $should_show_save = true;
                            foreach ($fields_in_subsection as $f) {
                                if (isset($f['hide_save_button']) && $f['hide_save_button'] === true) {
                                    $should_show_save = false;
                                    break;
                                }
                            }

                            $anchor_id = ($sub_section_title !== 'zzz_default') ? sanitize_title($section_key . '-' . $sub_section_title) : '';
                            ?>

                            <div class="bg-white shadow-md rounded-xl border p-6 mb-3">

                                <?php if ($sub_section_title !== 'zzz_default'): ?>
                                    <div class="border-b pb-4 mb-6">
                                        <h3 id="<?= esc_attr($anchor_id) ?>" class="text-lg font-bold text-toret-800 scroll-mt-24">
                                            <?= esc_html($sub_section_title) ?>
                                        </h3>
                                    </div>
                                <?php endif; ?>

                                <?php
                                $top_fields = [];
                                $normal_fields = [];
                                foreach ($fields_in_subsection as $f) {
                                    if (isset($f['position']) && $f['position'] === 'top') $top_fields[] = $f;
                                    else $normal_fields[] = $f;
                                }

                                if (!empty($top_fields)) {
                                    echo '<div class="mb-4">';
                                    foreach ($top_fields as $field) {
                                        echo $field['message'] ?? $field['html'] ?? '';
                                    }
                                    echo '</div>';
                                }

                                if (!empty($normal_fields)) {
                                    echo "<div class='space-y-4'>";
                                    foreach ($normal_fields as $field) {
                                        self::renderField($field);
                                    }
                                    echo "</div>";
                                }
                                ?>

                                <?php if ($should_show_save): ?>
                                    <div class="mt-8 pt-5 border-t flex justify-end">
                                        <button type="submit"
                                                name="save_settings_<?php echo $slug; ?>"
                                                value="<?php echo $save_per_section ? esc_attr($section_key) : esc_attr($slug); ?>"
                                                <?php if ($anchor_id): ?>
                                                    @click="saveScrollTarget('<?= esc_attr($anchor_id) ?>')"
                                                <?php endif; ?>
                                                class="<?php echo self::get_button_classes('primary'); ?>">
                                            <?php echo esc_html($settingsButtons['submit']); ?>
                                        </button>
                                    </div>
                                <?php endif; ?>

                            </div>

                            <?php
                        }
                    }
                    ?>

                </form>

            </div>
        <?php endforeach;
    }
}