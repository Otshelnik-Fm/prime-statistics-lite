<?php

/*

  ╔═╗╔╦╗╔═╗╔╦╗
  ║ ║ ║ ╠╣ ║║║ https://otshelnik-fm.ru
  ╚═╝ ╩ ╚  ╩ ╩

 */

if (!defined('ABSPATH')) exit;


function pstat_lite_settings($content){

    $my_adv = '';
    if(!rcl_exist_addon('prime-statistics')){
        $my_adv = '<div id="pstl_info">'
                    . '<i class="rcli fa-info" style="color:#ffae19;font-size:20px;vertical-align:text-bottom;margin:0 5px;" aria-hidden="true"></i>'
                    . ' Я выпустил похожее, премиум дополнение, с большими возможностями: '
                    . '<a href="https://codeseller.ru/products/prime-statistics/" title="Перейти к описанию" target="_blank">"Prime Statistics"</a>'
                    . ' - Шорткодом выводит расширенную статистику, и статистику PrimeForum в его подвале.<br/>'
                    . 'Предлагаю ознакомиться с его функционалом.<br/>'
                    . '- Там красивые чарты и графики'
                . '</div>';
    }

    $opt = new Rcl_Options(__FILE__);

    $content .= $opt->options('Настройки Prime Statistics Lite', array(
            $opt->options_box('Основные настройки',
                array(
                    array(
                        'type' => 'radio',
                        'title'=>'Показывать администрацию форума?',
                        'slug'=>'pspf_adm',
                        'values' => array('yes' => 'Да','no' => 'Нет'),
                        'default' => 'yes',
                        'notice'=> 'По умолчанию: Да<br/><hr>',
                        'help'=> 'Перейдя на форум, в его подвале будет отображен список администрации: админы и модераторы форума'
                        . '<br><br><strong>Внимание!</strong> Обратите внимание что используется реколл кеш на час - поэтому вы можете столкнуться с ситуацией словно опция не работает. Просто выждите час.'
                        . '<br>Реколл кеш вы можете также сбрасывать принудительно дополнением <a title="Перейти" href="https://codeseller.ru/products/recall-cache-control/">Recall Cache Control</a>'
                    )
                )
            ),
            $my_adv,
        )
    );

    return $content;
}
add_filter('admin_options_wprecall','pstat_lite_settings');