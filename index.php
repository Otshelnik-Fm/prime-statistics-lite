<?php

/*

╔═╗╔╦╗╔═╗╔╦╗
║ ║ ║ ╠╣ ║║║ https://otshelnik-fm.ru
╚═╝ ╩ ╚  ╩ ╩

*/

if (!defined('ABSPATH')) exit;


require_once 'admin/settings.php';


// подключаем стили только если мы на страницах форума
function pstat_lite_style(){
    if( is_prime_forum() ){
        rcl_enqueue_style('pstat_lite_style', rcl_addon_url('style.css', __FILE__));
    }
}
if(!is_admin()){
    add_action('rcl_enqueue_scripts', 'pstat_lite_style', 10);
}

// хук срабатывает - инициализация форума
//add_action('pfm_init','',10);


// подключим перевод
function pstat_lite_textdomain(){
    load_textdomain( 'pstat-lite', rcl_addon_path(__FILE__) . '/languages/pstat-lite-'. get_locale() . '.mo' );
}
add_action('plugins_loaded', 'pstat_lite_textdomain', 10);



// сформируем блоки
function pstat_lite_box(){
    $result = pstat_lite_counts();

    $out = '<div class="pstatl_box">';
        $out .= '<div class="pstatl_title">'.__('Forum Stats','pstat-lite').'</div>';//Статистика форума
        $out .= '<div class="pstatl_item pstatl_group"><span class="pstatl_num">'.$result[0]['groups'].'</span><span>'.__('Groups of forums','pstat-lite').'</span></div>';//Групп форумов
        $out .= '<div class="pstatl_item pstatl_forums"><span class="pstatl_num">'.$result[0]['forums'].'</span><span>'.__('Forums','pstat-lite').'</span></div>';//Форумов
        $out .= '<div class="pstatl_item pstatl_topics"><span class="pstatl_num">'.$result[0]['topics'].'</span><span>'.__('Topics','pstat-lite').'</span></div>';//Тем
        $out .= '<div class="pstatl_item pstatl_posts"><span class="pstatl_num">'.$result[0]['posts'].'</span><span>'.__('Posts','pstat-lite').'</span></div>';//Сообщений
        $out .= '<div class="pstatl_item pstatl_members"><span class="pstatl_num">'.$result[0]['visits'].'</span><span>'.__('Participants','pstat-lite').'</span></div>';//Участников
    $out .= '</div>';

    if( rcl_get_option('pspf_adm', 'yes') == 'yes' ){
        $out .= '<div class="pstatl_boss_box">';
            $out .= '<div class="pstatl_title">'.__('The moderating team','pstat-lite').'</div>';//Администрация форума
            $out .= pstat_lite_boss_names();
        $out .= '</div>';
    }

    return $out;
}


// загоним все в кеш и выведем в подвале форума
function pstat_lite_cache(){
    $rcl_cache = new Rcl_Cache(3600);        //кеш на час

    $string = 'pstat-cache';                 //уникальный ключ
    $file = $rcl_cache->get_file($string);   //получаем данные кеш-файла по указанному ключу

    if( !$file->need_update ){                 //если кеш не просрочен
        echo $rcl_cache->get_cache();        //выведем содержимое кеш-файла
    } else {
        $content = pstat_lite_box();
        $rcl_cache->update_cache($content);  //создаем или обновляем кеш-файл с сформированным контентом

        echo $content;                       //выведем контент
    }
}
add_action('pfm_footer', 'pstat_lite_cache'); //срабатывает в подвале форума


// считаем всё одним запросом
function pstat_lite_counts(){
    global $wpdb;

    $pstat_cnt = $wpdb->get_results("
                        SELECT
                        (SELECT COUNT(*) FROM ". RCL_PREF ."pforum_groups) AS groups,
                        (SELECT COUNT(*) FROM ". RCL_PREF ."pforums) AS forums,
                        (SELECT COUNT(*) FROM ". RCL_PREF ."pforum_topics) AS topics,
                        (SELECT COUNT(*) FROM ". RCL_PREF ."pforum_posts) AS posts,
                        (SELECT COUNT(*) FROM ".$wpdb->users.") AS visits
                    ", ARRAY_A);

    return $pstat_cnt;

/* Array(
    [0] => Array(
            [groups] => 3
            [forums] => 7
            [topics] => 15
            [posts] => 61
            [visits] => 4
        )
) */
}


// получим администрацию
function pstat_lite_forum_boss_data(){
    global $wpdb;

    $boss = $wpdb->get_results("
                        SELECT m.user_id, m.meta_value,u.user_nicename, u.display_name
                        FROM $wpdb->usermeta AS m
                        LEFT JOIN ".$wpdb->users." AS u
                        ON m.user_id = u.ID
                        WHERE m.meta_key = 'pfm_role'
                        AND m.meta_value IN ('administrator', 'moderator')
                        ORDER BY meta_value,m.user_id;
                    ", ARRAY_A);
    return $boss;

/*Array(
    [0] => Array(
            [user_id] => 2
            [meta_value] => administrator
            [user_nicename] => dfdfdsde
            [display_name] => Надежда Великолепная
        )
    [1] => Array(
            [user_id] => 108
            [meta_value] => moderator
            [user_nicename] => hgrrhbymum
            [display_name] => Андрей Плечёв
        )
) */
}

// выведем администрацию
function pstat_lite_boss_names(){
    $datas = pstat_lite_forum_boss_data();

    $admin_title = false;
    $admin = false;
    $moder_title = false;
    $moder = false;

    foreach($datas as $data){
        $url = get_author_posts_url($data['user_id'], $data['user_nicename']);

        if( $data['meta_value'] === 'administrator' ){
            if(!$admin_title) $admin_title = '<div class="pstatl_role">'.__('Administrators','pstat-lite').':</div>';//Администраторы
            $admin .= '<span><a href="'.$url.'">'.$data['display_name'].'</a></span>';
        }
        else if( $data['meta_value'] === 'moderator' ){
            if(!$moder_title) $moder_title = '<div class="pstatl_role">'.__('Moderators','pstat-lite').':</div>';//Модераторы
            $moder .= '<span><a href="'.$url.'">'.$data['display_name'].'</a></span>';
        }
    }

    return '<div class="pstatl_adm">'.$admin_title.$admin.'</div><div class="pstatl_mod">'.$moder_title.$moder.'</div>';
}


// цвет WP-Recall
function pstat_lite_color($styles, $rgb){
    if( !pfm_get_option('forum-colors') ) return $styles; // Цвета форума - По умолчанию

    list($r, $g, $b) = $rgb;
    $color = $r.','.$g.','.$b;

    $styles .= '
        #prime-forum .pstatl_box,
        #prime-forum .pstatl_boss_box {
            background-color: rgba('.$color.',0.02);
        }
        #prime-forum .pstatl_title {
            background-color: rgba('.$color.',0.1);
        }
    ';

    return $styles;
}
add_filter('rcl_inline_styles', 'pstat_lite_color', 10, 2);


function pstat_lite_add_settings(){
    $chr_page = get_current_screen();

    if($chr_page->base != 'wp-recall_page_rcl-options') return;
    if( isset($_COOKIE['otfmi_1']) && isset($_COOKIE['otfmi_2']) && isset($_COOKIE['otfmi_3']) )  return;

    require_once 'admin/for-settings.php';
}
add_action('admin_footer', 'pstat_lite_add_settings');


function pstat_lite_admin_styles(){
    $chr_page = get_current_screen();
    if( $chr_page->base != 'wp-recall_page_rcl-options' ) return;

$style = '
    #pstl_info {
        background-color: #dff5d4;
        border: 1px solid #c1eab7;
        margin: 5px 0;
        padding: 6px 12px 8px;
        font-size: 14px;
    }
';

    $style_min = pstat_lite_inline($style);

    echo "\r\n<style>".$style_min."</style>\r\n";
}
add_action('admin_footer', 'pstat_lite_admin_styles');


function pstat_lite_inline($src){
    $src_cleared =  preg_replace('/ {2,}/','',str_replace(array("\r\n", "\r", "\n", "\t"), '', $src));

    $src_non_space = str_replace(': ', ':', $src_cleared);

    $src_sanity = str_replace(' {', '{', $src_non_space);

    return $src_sanity;
}