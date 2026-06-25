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

  // CF7：メール（inputmode=email はAndroid Gboard で英数固定になるため text に変更）
  jQuery('.wpcf7-form input[type="email"]').attr('inputmode', 'text');
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

/* ペイジェント決済：顧客名の自動クレンジング（アルファベット→カタカナ変換）
----------------------------------------------*/
add_filter( 'usces_filter_paygent_customer_family_name', 'junon_sanitize_name_for_paygent' );
add_filter( 'usces_filter_paygent_customer_name',        'junon_sanitize_name_for_paygent' );

/**
 * ペイジェントに送信する顧客名を全角カタカナ等に変換する。
 * ひらがな→カタカナ、ローマ字→カタカナ変換を行い、
 * 残ったASCII文字（数字・記号等）を除去する。
 */
function junon_sanitize_name_for_paygent( $name ) {
	if ( '' === $name ) {
		return $name;
	}

	// 全角英数字→半角、ひらがな→全角カタカナ、半角スペース→全角スペース を一括変換
	$name = mb_convert_kana( $name, 'rnCS', 'UTF-8' );

	// 英字シーケンスをローマ字→カタカナ変換（孤立した文字は字名変換）
	$name = preg_replace_callback(
		'/[a-zA-Z]+/',
		function ( $m ) {
			return junon_romaji_or_letter_to_katakana( strtolower( $m[0] ) );
		},
		$name
	);

	// 数字・記号等のASCII文字を除去し、漢字・カタカナ・スペース・長音等のみ残す
	$name = preg_replace( '/[^\p{Han}\p{Katakana}\p{Hiragana}　 \-・ー]/u', '', $name );

	// 連続スペースを全角スペース1つに統一（/u フラグで多バイト文字を正しく扱う）
	$name = preg_replace( '/[ \x{3000}]+/u', "\xe3\x80\x80", $name );
	$name = trim( $name );

	// Paygent の12バイト上限（Shift_JIS換算）に収める
	if ( '' !== $name ) {
		$sjis = mb_convert_encoding( $name, 'SJIS-win', 'UTF-8' );
		if ( strlen( $sjis ) > 12 ) {
			$sjis = mb_strcut( $sjis, 0, 12, 'SJIS-win' );
			$name = mb_convert_encoding( $sjis, 'UTF-8', 'SJIS-win' );
		}
	}

	return '' !== $name ? $name : 'ナシ';
}

/**
 * 小文字ASCII英字シーケンスをカタカナに変換する。
 * ヘボン式ローマ字テーブルで最長一致変換し、
 * 変換できなかった孤立した子音は英字字名（ビー、シー…）に変換する。
 */
function junon_romaji_or_letter_to_katakana( $ascii ) {
	static $romaji = array(
		// 3文字（拗音・特殊）
		'sha' => 'シャ', 'shi' => 'シ', 'shu' => 'シュ', 'she' => 'シェ', 'sho' => 'ショ',
		'chi' => 'チ',   'cha' => 'チャ', 'chu' => 'チュ', 'che' => 'チェ', 'cho' => 'チョ',
		'tsu' => 'ツ',
		'kya' => 'キャ', 'kyu' => 'キュ', 'kyo' => 'キョ',
		'sya' => 'シャ', 'syu' => 'シュ', 'syo' => 'ショ',
		'tya' => 'チャ', 'tyu' => 'チュ', 'tyo' => 'チョ',
		'nya' => 'ニャ', 'nyu' => 'ニュ', 'nyo' => 'ニョ',
		'hya' => 'ヒャ', 'hyu' => 'ヒュ', 'hyo' => 'ヒョ',
		'mya' => 'ミャ', 'myu' => 'ミュ', 'myo' => 'ミョ',
		'rya' => 'リャ', 'ryu' => 'リュ', 'ryo' => 'リョ',
		'gya' => 'ギャ', 'gyu' => 'ギュ', 'gyo' => 'ギョ',
		'zya' => 'ジャ', 'zyu' => 'ジュ', 'zyo' => 'ジョ',
		'bya' => 'ビャ', 'byu' => 'ビュ', 'byo' => 'ビョ',
		'pya' => 'ピャ', 'pyu' => 'ピュ', 'pyo' => 'ピョ',
		// 2文字
		'ka' => 'カ', 'ki' => 'キ', 'ku' => 'ク', 'ke' => 'ケ', 'ko' => 'コ',
		'sa' => 'サ', 'si' => 'シ', 'su' => 'ス', 'se' => 'セ', 'so' => 'ソ',
		'ta' => 'タ', 'ti' => 'チ', 'tu' => 'ツ', 'te' => 'テ', 'to' => 'ト',
		'na' => 'ナ', 'ni' => 'ニ', 'nu' => 'ヌ', 'ne' => 'ネ', 'no' => 'ノ',
		'ha' => 'ハ', 'hi' => 'ヒ', 'hu' => 'フ', 'he' => 'ヘ', 'ho' => 'ホ',
		'fu' => 'フ',  'fa' => 'ファ', 'fi' => 'フィ', 'fe' => 'フェ', 'fo' => 'フォ',
		'ma' => 'マ', 'mi' => 'ミ', 'mu' => 'ム', 'me' => 'メ', 'mo' => 'モ',
		'ya' => 'ヤ', 'yu' => 'ユ', 'yo' => 'ヨ',
		'ra' => 'ラ', 'ri' => 'リ', 'ru' => 'ル', 're' => 'レ', 'ro' => 'ロ',
		'wa' => 'ワ', 'wi' => 'ウィ', 'we' => 'ウェ', 'wo' => 'ヲ',
		'ga' => 'ガ', 'gi' => 'ギ', 'gu' => 'グ', 'ge' => 'ゲ', 'go' => 'ゴ',
		'za' => 'ザ', 'zi' => 'ジ', 'zu' => 'ズ', 'ze' => 'ゼ', 'zo' => 'ゾ',
		'ja' => 'ジャ', 'ji' => 'ジ', 'ju' => 'ジュ', 'je' => 'ジェ', 'jo' => 'ジョ',
		'da' => 'ダ', 'di' => 'ヂ', 'du' => 'ヅ', 'de' => 'デ', 'do' => 'ド',
		'ba' => 'バ', 'bi' => 'ビ', 'bu' => 'ブ', 'be' => 'ベ', 'bo' => 'ボ',
		'pa' => 'パ', 'pi' => 'ピ', 'pu' => 'プ', 'pe' => 'ペ', 'po' => 'ポ',
		'va' => 'ヴァ', 'vi' => 'ヴィ', 'vu' => 'ヴ', 've' => 'ヴェ', 'vo' => 'ヴォ',
		// 1文字（母音・ン）
		'a' => 'ア', 'i' => 'イ', 'u' => 'ウ', 'e' => 'エ', 'o' => 'オ',
		'n' => 'ン',
	);

	// ローマ字変換でマッチしない孤立した子音の字名（英語読み）
	static $letter_names = array(
		'b' => 'ビー',      'c' => 'シー',      'd' => 'ディー',    'f' => 'エフ',
		'g' => 'ジー',      'h' => 'エイチ',    'j' => 'ジェイ',    'k' => 'ケイ',
		'l' => 'エル',      'm' => 'エム',      'p' => 'ピー',      'q' => 'キュー',
		'r' => 'アール',    's' => 'エス',      't' => 'ティー',    'v' => 'ブイ',
		'w' => 'ダブリュー', 'x' => 'エックス', 'y' => 'ワイ',      'z' => 'ゼット',
	);

	$result = '';
	$len    = strlen( $ascii );
	$i      = 0;

	while ( $i < $len ) {
		$matched = false;

		// 3文字→2文字→1文字の順で最長一致
		for ( $l = min( 3, $len - $i ); $l >= 1; $l-- ) {
			$seg = substr( $ascii, $i, $l );
			if ( ! isset( $romaji[ $seg ] ) ) {
				continue;
			}
			// 語中の 'n' が次に母音・y・n が続く場合は1文字ではなく次の2〜3文字パターンを優先
			// （例：「na」は2文字でマッチするため、ここには来ない。末尾・子音前の'n'のみ到達）
			$result .= $romaji[ $seg ];
			$i      += $l;
			$matched = true;
			break;
		}

		if ( ! $matched ) {
			// ローマ字テーブルに無い孤立文字（子音等）→英字字名に変換
			$c = $ascii[ $i ];
			if ( isset( $letter_names[ $c ] ) ) {
				$result .= $letter_names[ $c ];
			}
			$i++;
		}
	}

	return $result;
}

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

// ===========================================================
// 確認画面UI修正 kasio-web 20260522
// ===========================================================

/**
 * 【修正3】PC・SP共通：送付先情報の法人名・部署名を表示
 *
 * 配送先カスタムフィールド（company-name / dept-name）が空のとき（＝同じ配送先を
 * 選択した場合など）、お客様情報のカスタムフィールド（company / dept）の値で補完する。
 *
 * ※ 2026-05-22 更新: メール生成時のHTMLメッセージフィルターで対応
 */

/**
 * メール送信時：配送先情報をお客様情報で補完（HTML・テキスト両対応）
 *
 * Welcart の usces_get_thanksmail_htmlbody / usces_get_thanksmail_textbody で生成された
 * メッセージ内の配送先セクションから空の法人名・部署名を検出し、顧客情報で補完する。
 *
 * $data['ID'] または $data['order_id'] から注文IDを取得し、
 * 顧客のカスタムフィールド値を直接メタデータから取得する。
 */
add_filter( 'usces_filter_send_order_mail_shipping', 'my_complement_delivery_in_shipping', 10, 3 );
function my_complement_delivery_in_shipping( $msg_shipping, $data, $entry ) {
	global $usces;

	// $data から order_id を取得（複数のキー名に対応）
	$order_id = ! empty( $data['ID'] ) ? $data['ID'] : ( ! empty( $data['order_id'] ) ? $data['order_id'] : 0 );

	// order_id が取得できない場合、別の方法を試す
	if ( 0 === $order_id && ! empty( $data ) && is_array( $data ) ) {
		$keys = array_keys( $data );
		foreach ( $keys as $key ) {
			if ( preg_match( '/^id$/i', $key ) || preg_match( '/order.*id/i', $key ) ) {
				$order_id = (int) $data[ $key ];
				break;
			}
		}
	}

	// order_id が判明した場合のみ補完を試みる
	if ( $order_id > 0 ) {
		$customer_company = $usces->get_order_meta_value( 'cscs_company', $order_id );
		$customer_dept    = $usces->get_order_meta_value( 'cscs_dept', $order_id );
		$delivery_company = $usces->get_order_meta_value( 'csde_company-name', $order_id );  // ✅ ハイフン付きに修正
		$delivery_dept    = $usces->get_order_meta_value( 'csde_dept-name', $order_id );      // ✅ ハイフン付きに修正

		// 配送先が空の場合、顧客情報で補完
		if ( empty( $delivery_company ) ) {
			$delivery_company = $customer_company;
		}
		if ( empty( $delivery_dept ) ) {
			$delivery_dept = $customer_dept;
		}

		if ( usces_is_html_mail() ) {
			// HTML メール：<th><td> タグを対象
			if ( ! empty( $delivery_company ) ) {
				$msg_shipping = preg_replace(
					'/(<th>\s*法人名\s*<\/th>\s*<td[^>]*>)\s*(<\/td>)/s',
					'$1' . esc_html( $delivery_company ) . '$2',
					$msg_shipping
				);
			}
			if ( ! empty( $delivery_dept ) ) {
				$msg_shipping = preg_replace(
					'/(<th>\s*部署名\s*<\/th>\s*<td[^>]*>)\s*(<\/td>)/s',
					'$1' . esc_html( $delivery_dept ) . '$2',
					$msg_shipping
				);
			}
		} else {
			// テキストメール：法人名の補完（改行を削除して同一行に表示）
			if ( ! empty( $delivery_company ) ) {
				$msg_shipping = preg_replace(
					'/^(\s*法人名\s*:\s*)\r?\n\s*(.+)/m',
					'$1' . esc_html( $delivery_company ),
					$msg_shipping,
					1
				);
			}
			// テキストメール：部署名の補完（改行を削除して同一行に表示）
			if ( ! empty( $delivery_dept ) ) {
				$msg_shipping = preg_replace(
					'/^(\s*部署名\s*:\s*)\r?\n\s*(.+)/m',
					'$1' . esc_html( $delivery_dept ),
					$msg_shipping,
					1
				);
			}
		}
	}

	// FAX番号行を削除（PC テーマの削除と同様）
	$msg_shipping = preg_replace(
		'/^(\s*FAX番号\s*:.*?)(\r?\n|$)/m',
		'',
		$msg_shipping
	);

	return $msg_shipping;
}

/**
 * メール本文全体から FAX番号行を削除（顧客情報セクションも対象）
 */
add_filter( 'usces_filter_send_order_mail_bodyall', 'my_remove_fax_from_entire_mail_body', 10, 2 );
function my_remove_fax_from_entire_mail_body( $mail_body, $data ) {
	// テキストメール全体：FAX番号行を削除（顧客情報・配送先両セクション対応）
	$mail_body = preg_replace(
		'/^(\s*FAX番号\s*:.*?)(\r?\n|$)/m',
		'',
		$mail_body
	);
	return $mail_body;
}

/**
 * フィルター対応：確認画面でも補完（HTML/テキスト両対応）
 */
add_filter( 'usces_filter_custom_field_info', 'my_delivery_company_field_fix', 10, 4 );
function my_delivery_company_field_fix( $html, $data, $custom_field, $position ) {
	// 送付先の name_after カスタムフィールドのみ対象
	if ( 'delivery' !== $custom_field || 'name_after' !== $position ) {
		return $html;
	}

	// 配送先の法人名が空なら、お客様情報の法人名で補完
	$company = ! empty( $data['custom_delivery']['company-name'] )
		? $data['custom_delivery']['company-name']
		: ( ! empty( $data['custom_customer']['company'] ) ? $data['custom_customer']['company'] : '' );

	// 配送先の部署名が空なら、お客様情報の部署名で補完
	$dept = ! empty( $data['custom_delivery']['dept-name'] )
		? $data['custom_delivery']['dept-name']
		: ( ! empty( $data['custom_customer']['dept'] ) ? $data['custom_customer']['dept'] : '' );

	// HTML メール対応（<td> を含む）
	if ( ! empty( $company ) ) {
		$html = preg_replace(
			'/(<th>\s*法人名\s*<\/th>\s*<td[^>]*>)\s*(<\/td>)/s',
			'$1' . esc_html( $company ) . '$2',
			$html
		);
	}
	if ( ! empty( $dept ) ) {
		$html = preg_replace(
			'/(<th>\s*部署名\s*<\/th>\s*<td[^>]*>)\s*(<\/td>)/s',
			'$1' . esc_html( $dept ) . '$2',
			$html
		);
	}

	// テキスト メール対応（: の形式）
	if ( ! empty( $company ) ) {
		$html = preg_replace(
			'/^(\s*法人名\s*:\s*)(\r?\n|$)/m',
			'$1' . esc_html( $company ) . '$2',
			$html
		);
	}
	if ( ! empty( $dept ) ) {
		$html = preg_replace(
			'/^(\s*部署名\s*:\s*)(\r?\n|$)/m',
			'$1' . esc_html( $dept ) . '$2',
			$html
		);
	}

	return $html;
}

// ===========================================================
// 注文ありがとうメール：内消費税ラベルの外側括弧を中括弧に kasio-web 20260526
// テーマ非依存でPC(welcart_default_child)/スマホ(welcart_sp_custom)両方に適用するためプラグイン側に設置
// 例: ( 内消費税(10%) : ¥41 ) → { 内消費税(10%) : ¥41 }（内側の(10%)は維持）
// ===========================================================
add_filter( 'usces_filter_mail_tax_label', 'my_mail_tax_label_brace' );
function my_mail_tax_label_brace( $tax_label ) {
	if ( preg_match( '/^\(\s(.*)\s\)$/u', $tax_label, $m ) ) {
		$tax_label = '{ ' . $m[1] . ' }';
	}
	return $tax_label;
}

?>