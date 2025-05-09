<?php
/**
 * Plugin Name:  io Code Highlight
 * Plugin URI:   https://www.iotheme.cn/store/io-code-highlight.html
 * Description:  一个代码语法高亮插件
 * Version:      2.0.1
 * Author:       一为
 * Author URI:   https://www.iowen.cn
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  i_theme
 */

define('IOTHEME_BLOCK_VERSION', '2.0.1');
define('IOTHEME_BLOCK_URL', plugins_url('', __FILE__));
define('IOTHEME_BLOCK_PATH', plugin_dir_path( __FILE__ ) );

define('IOTHEME_DEFAULT_LANG', 'php');
define('IOTHEME_DEFAULT_NUMB', true);

require IOTHEME_BLOCK_PATH . '/tinymce.php';
require IOTHEME_BLOCK_PATH . '/block.php';
require IOTHEME_BLOCK_PATH . '/code-languages.php';

/**
 * 加载enlighter js
 */
function io_code_add_enlighter_assets() {
    // 请检查是否需要加载高亮代码
    if ( ! is_admin() ) {
        global $posts;
        $force_load = apply_filters( 'io_code_assets_force_loading', false );
        // 判断是否存在代码块
        // 如果未找到块，则跳过加载资源
        if ( !$force_load ) {
            if (isset($posts) && is_array($posts)) {
                $found_block = array_reduce( $posts, function($found, $post) { 
                    return $found || has_block( 'code', $post ) || preg_match('/(crayon-|<\/pre>)/i', $post->post_content, $matches);
                }, false );
                if ( !$found_block ) {
                    return;
                }
            } else {
                return;
            }
        }
    }
    
    $_min = WP_DEBUG === true?'':'.min';

    wp_register_style( 'enlighterjs', IOTHEME_BLOCK_URL . '/assets/css/enlighterjs'.$_min.'.css', array(), IOTHEME_BLOCK_VERSION ); 
    wp_register_script( 'enlighterjs',    IOTHEME_BLOCK_URL . '/assets/js/enlighterjs'.$_min.'.js', array('jquery'), IOTHEME_BLOCK_VERSION, true );

    if(is_single()){
        wp_enqueue_style('enlighterjs');
        wp_enqueue_script('enlighterjs');

        wp_localize_script('enlighterjs', 'io_code_settings', array(
            'pre_c'        => '© '.get_bloginfo('name'),
        ));
    }
}
add_action('wp_enqueue_scripts',  'io_code_add_enlighter_assets' );

/**
 * 为编辑器添加全局变量
 * @return void 
 */
function io_code_plugin_mce_config(){ 
	echo '<script type="text/javascript">/* <![CDATA[ */
	const io_code_languages = ' . json_encode( io_code_languages() ) . '; 
	const io_code_default_lang = "' . IOTHEME_DEFAULT_LANG . '";
	const io_code_default_numb = ' . (IOTHEME_DEFAULT_NUMB?'true':'false') . ';
 /* ]]> */</script>';
}
add_action('admin_print_scripts', 'io_code_plugin_mce_config');//wp_enqueue_editor | wp_head
