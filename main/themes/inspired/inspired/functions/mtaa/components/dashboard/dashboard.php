<?php
if (mtaa::$tf) return;

function Mtaa_Dashboard() {
?>
<div class="table table_news">
    <p class="sub">From our Blog</p>
    <div class="rss-widget">
        <?php
        /**
         * Get RSS Feed(s)
         */

        $items = get_transient('mtaa_dashboard_widget_news');

        if (!(is_array($items) && count($items))) {
            include_once(ABSPATH . RES . '/class-simplepie.php');
            $rss = new SimplePie();
            $rss->set_timeout(5);
            $rss->set_feed_url('http://www.mtaandao.co.ke/feed/');
            $rss->strip_htmltags(array_merge($rss->strip_htmltags, array('h1', 'a', 'img')));
            $rss->enable_cache(false);
            $rss->init();

            $items = $rss->get_items(0, 3);

            $cached = array();
            foreach ($items as $item) {
                $cached[] = array(
                    'url' => $item->get_permalink(),
                    'title' => $item->get_title(),
                    'date' => $item->get_date("d M Y"),
                    'content' => substr(strip_tags($item->get_content()), 0, 128) . "..."
                );
            }

            $items = $cached;
            set_transient('mtaa_dashboard_widget_news', $cached, 60 * 60 * 24);
        }
        ?>

        <ul class="news">
            <?php if (empty($items)) {
                echo '<li>No items</li>';
            } else {
                foreach ($items as $item) {
            ?>

                <li class="post">
                    <a href="<?php echo $item['url']; ?>" class="rsswidget"><?php echo $item['title']; ?></a>
                    <span class="rss-date"><?php echo $item['date']; ?></span>
                    <div class="rssSummary"><?php echo $item['content']; ?></div>
                </li>

            <?php
                }
            }
            ?>
        </ul><!-- end of .news -->
    </div>
</div>

<div class="clear">&nbsp;</div>
<?php
}

function mtaa_dashboard_widgets() {
    mn_add_dashboard_widget('dashboard_mtaa', 'Mtaa News', 'Mtaa_Dashboard');

    mn_enqueue_style('dashboard_mtaa_stylesheet', Mtaa::$assetsPath . '/css/dashboard.css');

    // Globalize the metaboxes array, this holds all the widgets for admin
    global $mn_meta_boxes;

    // Get the regular dashboard widgets array
    // (which has our new widget already but at the end)

    $normal_dashboard = $mn_meta_boxes['dashboard']['normal']['core'];

    // Backup and delete our new dashbaord widget from the end of the array
    $mtaa_widget_backup = array('dashboard_mtaa' => $normal_dashboard['dashboard_mtaa']);
    unset($normal_dashboard['dashboard_mtaa']);

    // Merge the two arrays together so our widget is at the beginning
    $sorted_dashboard = array_merge($mtaa_widget_backup, $normal_dashboard);

    // Save the sorted array back into the original metaboxes
    $mn_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
}

add_action('mn_dashboard_setup', 'mtaa_dashboard_widgets');
