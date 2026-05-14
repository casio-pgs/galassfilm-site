<?php
/*
Plugin Name: Junonet-plugin
Plugin URI: http://junonet.biz/
Description: Junonet Plugin For Wordpress.
Version: 1.0.0
Author: taizo fujita
Author URI: http://junonet.biz/
License: GPL2
*/

/* ↓↓ ここからコード ↓↓
------------------------------------------------------------------------*/
// スマホ用, HomeLeft.	// Area 2, HomeRight.
register_sidebar(array(	
'name' => __( 'スマホ用 Home Left', 'uscestheme' ),	
'id' => 'sp-homeleft-widget-area',	
'description' => __( 'sp home left sidebar widget area', 'uscestheme' ),	
'before_widget' => '<li id="%1$s" class="widget %2$s">',	
'after_widget' => '</li>',	
'before_title' => '<div class="widget_title">',	
'after_title' => '</div>',	
));


remove_filter('the_content','wpautop');

//カテゴリー説明文でHTMLタグを使う
remove_filter( 'pre_term_description', 'wp_filter_kses' );

// 絵文字用のjavascriptとcssを呼ばない
   remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
   remove_action( 'wp_print_styles', 'print_emoji_styles' );

/* 記事内でテンプレートフォルダのパスを表示
----------------------------------------------*/
function shortcode_templateurl() {
    return get_stylesheet_directory_uri();
}
add_shortcode('template_url', 'shortcode_templateurl');

/* ホームのURLを取得する
----------------------------------------------*/
function get_url(){
  return home_url();
}
add_shortcode('home_url', 'get_url');




/* ヘッダーに jQueryコード(JavaScript) を挿入
----------------------------------------------*/
function nendebcom_header_insert_js_code() {
?>
<?php
}
add_action( 'wp_head', 'nendebcom_header_insert_js_code', 10 );

/* フッターに jQueryコード(JavaScript) を挿入
----------------------------------------------*/
function junon_footer_insert_js_code() {
?>
<script>
jQuery(document).ready(function(){
  jQuery(".submenu").css("display","none");
  jQuery(".trigger").click(function(){
    if(jQuery("+.submenu",this).css("display")=="none"){
         jQuery(this).addClass("active-submenu");
         jQuery(this).removeClass("none-submenu");
         jQuery("+.submenu",this).slideDown("normal");
  }else{
    jQuery(this).removeClass("active-submenu");
    jQuery(this).addClass("none-submenu");
    jQuery("+.submenu",this).slideUp("normal");
  }
  });

  // ── inputmode 一括設定（フォームキーボード制御）──────────────────

  // CF7：数字キーボード（ガラスサイズ・郵便番号）
  jQuery('input[name^="text-tate"], input[name^="text-yoko"], input[name^="text-su"], input[name^="your-zip"]')
    .attr('inputmode', 'numeric').attr('pattern', '\\d*');

  // CF7：英数キーボード（メール・電話）
  jQuery('.wpcf7-form input[type="email"]').attr('inputmode', 'email');
  jQuery('.wpcf7-form input[type="tel"]').attr('inputmode', 'tel');

  // CF7：日本語キーボード（その他テキスト・Android Gboard対策）
  jQuery('.wpcf7-form input[type="text"]:not([name^="text-tate"]):not([name^="text-yoko"]):not([name^="text-su"]):not([name^="your-zip"]), .wpcf7-form textarea')
    .attr('inputmode', 'text');

  // Welcart：数字キーボード（郵便番号・電話・FAX）
  jQuery('#zipcode, #tel, #fax').attr('inputmode', 'numeric').attr('pattern', '\\d*');

  // Welcart：日本語キーボード（氏名・住所）
  jQuery('#name1, #name2, #name3, #name4, #address1, #address2, #address3')
    .attr('inputmode', 'text').attr('autocapitalize', 'off').attr('autocorrect', 'off');
});
</script>

<?php
}
add_filter( 'wp_footer', 'junon_footer_insert_js_code', 20 );

/* ↑↑ ここまでコード ↑↑
------------------------------------------------------------------------*/

if ( ! function_exists( 'junon_plugin_last_load' ) ) :
/*
 * サンプル
 * my-plugin を最後に読み込むようにする。
 * Note : http://nendeb.com/%e3%83%9e%e3%82%a4%e3%83%bb%e3%83%97%e3%83%a9%e3%82%b0%e3%82%a4%e3%83%b3%e3%82%92%e6%9c%80%e5%be%8c%e3%81%ab%e8%aa%ad%e3%81%bf%e8%be%bc%e3%82%80%e3%82%88%e3%81%86%e3%81%ab%e3%81%99%e3%82%8b/
*/
function junon_plugin_last_load() {

    $this_activeplugin  = '';
    $this_plugin        = 'my-plugin/my-plugin.php';    //最後に読み込みたいプラグイン
    $active_plugins     = get_option( 'active_plugins' );
    $new_active_plugins = array();

    foreach ( $active_plugins as $plugins ) {
        if( $plugins != $this_plugin ){
            $new_active_plugins[] = $plugins;
        }else{
            $this_activeplugin = $this_plugin;
        }
    }

    if( $this_activeplugin ){
        $new_active_plugins[] = $this_activeplugin;
    }

    if( ! empty( $new_active_plugins ) ){
        update_option( 'active_plugins' ,  $new_active_plugins );
    }
}
endif; // junon_plugin_last_load
add_action( "activated_plugin", "junon_plugin_last_load" );

?>