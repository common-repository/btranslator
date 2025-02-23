<?php
/*
Plugin Name: BTranslator
Plugin URI: http://edo.webmaster.am/btranslator?xyz=998
Description: Get translations with a single click between 35 languages (90% of internet users) on your website! For support visit <a href="http://edo.webmaster.am/forum/btranslator/">BTranslator Forum</a>.
Version: 1.0.23
Author: Edvard Ananyan
Author URI: http://edo.webmaster.am

*/

/*  Copyright 2011 Edvard Ananyan  (email : edo888@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('widgets_init', array('BTranslator', 'register'));
register_activation_hook(__FILE__, array('BTranslator', 'activate'));
register_deactivation_hook(__FILE__, array('BTranslator', 'deactivate'));
add_action('admin_menu', array('BTranslator', 'admin_menu'));
add_action('init', array('BTranslator', 'enqueue_scripts'));
add_shortcode('BTranslator', array('BTranslator', 'get_widget_code'));
add_shortcode('btranslator', array('BTranslator', 'get_widget_code'));

class BTranslator extends WP_Widget {
    function activate() {
        $data = array(
            'btranslator_title' => 'Translate',
        );
        $data = get_option('BTranslator');
        BTranslator::load_defaults(& $data);

        add_option('BTranslator', $data);
    }

    function deactivate() {
        // delete_option('BTranslator');
    }

    function control() {
        $data = get_option('BTranslator');
        ?>
        <p><label>Title: <input name="btranslator_title" type="text" class="widefat" value="<?php echo $data['btranslator_title']; ?>"/></label></p>
        <p>Please go to Settings -> BTranslator for configuration.</p>
        <?php
        if (isset($_POST['btranslator_title'])){
            $data['btranslator_title'] = attribute_escape($_POST['btranslator_title']);
            update_option('BTranslator', $data);
        }
    }

    function enqueue_scripts() {
        $data = get_option('BTranslator');
        BTranslator::load_defaults(& $data);
        $wp_plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );

        if($data['translation_method'] == 'on_fly' and !is_admin()) {
            if($data['load_jquery'])
                wp_enqueue_script('jquery-translate', $wp_plugin_url.'/jquery-translate.js', array('jquery'));
            else
                wp_enqueue_script('jquery-translate', $wp_plugin_url.'/jquery-translate.js');
        }

        wp_enqueue_style('btranslator-style', $wp_plugin_url.'/btranslator-style'.$data['flag_size'].'.css');
    }

    function widget($args) {
        $data = get_option('BTranslator');
        BTranslator::load_defaults(& $data);

        echo $args['before_widget'];
        echo $args['before_title'] . '<a href="http://edo.webmaster.am/btranslator" rel="follow" target="_blank">' . $data['btranslator_title'] . '</a>' . $args['after_title'];
        if(empty($data['widget_code']))
            echo 'Configure it from WP-Admin -> Settings -> BTranslator to see it in action.';
        else
            echo $data['widget_code'];
        echo $args['after_widget'];
        //echo '<img src="http://cmshippo.com/gstats.png" width="0" height="0" alt="g-stats" class="gstats" />';
        echo '<noscript>Javascript is required to use this <a href="http://edo.webmaster.am/btranslator">website translator</a>, <a href="http://edo.webmaster.am/btranslator">site translator</a>, <a href="http://edo.webmaster.am/btranslator">automatic translation</a>, <a href="http://edo.webmaster.am/btranslator">machine translation</a></noscript>';
    }

    function get_widget_code($atts) {
        $data = get_option('BTranslator');
        BTranslator::load_defaults(& $data);

        if(empty($data['widget_code']))
            return 'Configure it from WP-Admin -> Settings -> BTranslator to see it in action.';
        else
            return $data['widget_code'].'<noscript>Javascript is required to use this <a href="http://edo.webmaster.am/btranslator">website translator</a>, <a href="http://edo.webmaster.am/btranslator">site translator</a>, <a href="http://edo.webmaster.am/btranslator">automatic translation</a>, <a href="http://edo.webmaster.am/btranslator">machine translation</a></noscript>';
            //.'<img src="http://cmshippo.com/gstats.png" width="0" height="0" alt="g-stats" class="gstats" />';
    }

    function register() {
        wp_register_sidebar_widget('btranslator', 'BTranslator', array('BTranslator', 'widget'), array('description' => __('Bing Automatic Translator')));
        wp_register_widget_control('btranslator', 'BTranslator', array('BTranslator', 'control'));
    }

    function admin_menu() {
        add_options_page('BTranslator Options', 'BTranslator', 'administrator', 'btranslator_options', array('BTranslator', 'options'));
    }

    function options() {
        ?>
        <div class="wrap">
        <div id="icon-options-general" class="icon32"><br/></div>
        <h2>BTranslator</h2>
        <?php
        if($_POST['save'])
            BTranslator::control_options();
        $data = get_option('BTranslator');
        BTranslator::load_defaults(& $data);

        $site_url = get_option('siteurl');

        extract($data);

        #unset($data['widget_code']);
        #echo '<pre>', print_r($data, true), '</pre>';

$script = <<<EOT

var languages = ['Arabic','Bulgarian','Chinese (Simplified)','Chinese (Traditional)','Czech','Danish','Dutch','English','Estonian','Finnish','French','German','Greek','Haitian Creole','Hebrew','Hungarian','Indonesian','Italian','Japanese','Korean','Latvian','Lithuanian','Norwegian','Polish','Portuguese','Romanian','Russian','Slovak','Slovenian','Spanish','Swedish','Thai','Turkish','Ukrainian','Vietnamese']
var language_codes = ['ar','bg','zh-CN','zh-TW','cs','da','nl','en','et','fi','fr','de','el','ht','iw','hu','id','it','ja','ko','lv','lt','no','pl','pt','ro','ru','sk','sl','es','sv','th','tr','uk','vi'];
var languages_map = {en_x: 0, en_y: 0, ar_x: 100, ar_y: 0, bg_x: 200, bg_y: 0, zhCN_x: 300, zhCN_y: 0, zhTW_x: 400, zhTW_y: 0, hr_x: 500, hr_y: 0, cs_x: 600, cs_y: 0, da_x: 700, da_y: 0, nl_x: 0, nl_y: 100, fi_x: 100, fi_y: 100, fr_x: 200, fr_y: 100, de_x: 300, de_y: 100, el_x: 400, el_y: 100, hi_x: 500, hi_y: 100, it_x: 600, it_y: 100, ja_x: 700, ja_y: 100, ko_x: 0, ko_y: 200, no_x: 100, no_y: 200, pl_x: 200, pl_y: 200, pt_x: 300, pt_y: 200, ro_x: 400, ro_y: 200, ru_x: 500, ru_y: 200, es_x: 600, es_y: 200, sv_x: 700, sv_y: 200, ca_x: 0, ca_y: 300, tl_x: 100, tl_y: 300, iw_x: 200, iw_y: 300, id_x: 300, id_y: 300, lv_x: 400, lv_y: 300, lt_x: 500, lt_y: 300, sr_x: 600, sr_y: 300, sk_x: 700, sk_y: 300, sl_x: 0, sl_y: 400, uk_x: 100, uk_y: 400, vi_x: 200, vi_y: 400, sq_x: 300, sq_y: 400, et_x: 400, et_y: 400, gl_x: 500, gl_y: 400, hu_x: 600, hu_y: 400, mt_x: 700, mt_y: 400, th_x: 0, th_y: 500, tr_x: 100, tr_y: 500, fa_x: 200, fa_y: 500, af_x: 300, af_y: 500, ms_x: 400, ms_y: 500, sw_x: 500, sw_y: 500, ga_x: 600, ga_y: 500, cy_x: 700, cy_y: 500, be_x: 0, be_y: 600, is_x: 100, is_y: 600, mk_x: 200, mk_y: 600, yi_x: 300, yi_y: 600, hy_x: 400, hy_y: 600, az_x: 500, az_y: 600, eu_x: 600, eu_y: 600, ka_x: 700, ka_y: 600, ht_x: 0, ht_y: 700, ur_x: 100, ur_y: 700};

function RefreshDoWidgetCode() {
    var new_line = "\\n";
    var widget_preview = '<!-- BTranslator: http://edo.webmaster.am/btranslator -->'+new_line;
    var widget_code = '';
    var translation_method = jQuery('#translation_method').val();
    var default_language = jQuery('#default_language').val();
    var flag_size = jQuery('#flag_size').val();
    var pro_version = jQuery('#pro_version:checked').length > 0 ? true : false;
    var new_window = jQuery('#new_window:checked').length > 0 ? true : false;
    var analytics = jQuery('#analytics:checked').length > 0 ? true : false;

    if(translation_method == 'bing_default') {
        included_languages = '';
        jQuery.each(languages, function(i, val) {
            lang = language_codes[i];
            if(jQuery('#incl_langs'+lang+':checked').length) {
                lang_name = val;
                included_languages += ','+lang;
            }
        });

        widget_preview += '<div id="MicrosoftTranslatorWidget" style="width:200px;min-height:83px;border-color:#3A5770;background-color:#78ADD0;"></div>'+new_line;
        widget_preview += '<script type="text/javascript">'+new_line;
        widget_preview += 'setTimeout('+new_line;
        widget_preview += 'function() {'+new_line;
        widget_preview += 'var s = document.createElement("script");'+new_line;
        widget_preview += 's.type = "text/javascript";'+new_line;
        widget_preview += 's.charset = "UTF-8";'+new_line;
        widget_preview += 's.src = ((location && location.href && location.href.indexOf("https") == 0) ? "https://ssl.microsofttranslator.com" : "http://www.microsofttranslator.com" ) + "/ajax/v2/widget.aspx?mode=auto&from='+default_language+'&layout=ts";'+new_line;
        widget_preview += 'var p = document.getElementsByTagName("head")[0] || document.documentElement;'+new_line;
        widget_preview += 'p.insertBefore(s, p.firstChild);'+new_line;
        widget_preview += '}, 0);'+new_line;
        widget_preview += '<\/script>';
    } else if(translation_method == 'on_fly' || translation_method == 'redirect') {
        // Adding flags
        if(jQuery('#show_flags:checked').length) {
            jQuery.each(languages, function(i, val) {
                lang = language_codes[i];
                if(jQuery('#fincl_langs'+lang+':checked').length) {
                    lang_name = val;
                    flag_x = languages_map[lang.replace('-', '')+'_x'];
                    flag_y = languages_map[lang.replace('-', '')+'_y'];
                    widget_preview += '<a href="javascript:doBTranslator(\''+default_language+'|'+lang+'\')" title="'+lang_name+'" class="gflag" style="background-position:-'+flag_x+'px -'+flag_y+'px;"><img src="{$site_url}/wp-content/plugins/btranslator/blank.png" height="'+flag_size+'" width="'+flag_size+'" alt="'+lang_name+'" /></a>';
                }
            });
        }

        // Adding dropdown
        if(jQuery('#show_dropdown:checked').length) {
            if(jQuery('#show_flags:checked').length && jQuery('#add_new_line:checked').length)
                widget_preview += '<br />';
            else
                widget_preview += ' ';
            widget_preview += '<select onchange="doBTranslator(this);">';
            widget_preview += '<option value="">Select Language</option>';
            jQuery.each(languages, function(i, val) {
                lang = language_codes[i];
                if(jQuery('#incl_langs'+lang+':checked').length) {
                    lang_name = val;
                    widget_preview += '<option value="'+default_language+'|'+lang+'">'+lang_name+'</option>';
                }
            });
            widget_preview += '</select>';
        }

        // Adding javascript
        widget_code += new_line+new_line;
        widget_code += '<script type="text/javascript">'+new_line;
        widget_code += '//<![CDATA['+new_line;
        if(pro_version && translation_method == 'redirect' && new_window) {
            widget_code += "function openTab(url) {var form=document.createElement('form');form.method='post';form.action=url;form.target='_blank';document.body.appendChild(form);form.submit();}"+new_line;
            if(analytics)
                widget_code += "function doBTranslator(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;var lang=lang_pair.split('|')[1];_gaq.push(['_trackEvent', 'BTranslator', lang, location.pathname+location.search]);var plang=location.pathname.split('/')[1];if(plang.length !=2 && plang != 'zh-CN' && plang != 'zh-TW')plang='"+default_language+"';if(lang == '"+default_language+"')openTab(location.protocol+'//'+location.host+location.pathname.replace('/'+plang+'/', '/')+location.search);else openTab(location.protocol+'//'+location.host+'/'+lang+location.pathname.replace('/'+plang+'/', '/')+location.search);}"+new_line;
            else
                widget_code += "function doBTranslator(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;var lang=lang_pair.split('|')[1];var plang=location.pathname.split('/')[1];if(plang.length !=2 && plang != 'zh-CN' && plang != 'zh-TW')plang='"+default_language+"';if(lang == '"+default_language+"')openTab(location.protocol+'//'+location.host+location.pathname.replace('/'+plang+'/', '/')+location.search);else openTab(location.protocol+'//'+location.host+'/'+lang+location.pathname.replace('/'+plang+'/', '/')+location.search);}"+new_line;
        } else if(pro_version && translation_method == 'redirect') {
            if(analytics)
                widget_code += "function doBTranslator(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;var lang=lang_pair.split('|')[1];_gaq.push(['_trackEvent', 'BTranslator', lang, location.pathname+location.search]);var plang=location.pathname.split('/')[1];if(plang.length !=2 && plang != 'zh-CN' && plang != 'zh-TW')plang='"+default_language+"';if(lang == '"+default_language+"')location.href=location.protocol+'//'+location.host+location.pathname.replace('/'+plang+'/', '/')+location.search;else location.href=location.protocol+'//'+location.host+'/'+lang+location.pathname.replace('/'+plang+'/', '/')+location.search;}"+new_line;
            else
                widget_code += "function doBTranslator(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;var lang=lang_pair.split('|')[1];var plang=location.pathname.split('/')[1];if(plang.length !=2 && plang != 'zh-CN' && plang != 'zh-TW')plang='"+default_language+"';if(lang == '"+default_language+"')location.href=location.protocol+'//'+location.host+location.pathname.replace('/'+plang+'/', '/')+location.search;else location.href=location.protocol+'//'+location.host+'/'+lang+location.pathname.replace('/'+plang+'/', '/')+location.search;}"+new_line;
        } else if(translation_method == 'redirect' && new_window) {
            widget_code += 'if(top.location!=self.location)top.location=self.location;'+new_line;
            widget_code += "window['_tipoff']=function(){};window['_tipon']=function(a){};"+new_line;
            if(analytics)
                widget_code += "function doBTranslator(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;if(location.hostname!='translate.googleusercontent.com' && lang_pair=='"+default_language+"|"+default_language+"')return;var lang=lang_pair.split('|')[1];_gaq.push(['_trackEvent', 'BTranslator', lang, location.pathname+location.search]);if(location.hostname=='translate.googleusercontent.com' && lang_pair=='"+default_language+"|"+default_language+"')openTab(unescape(gfg('u')));else if(location.hostname!='translate.googleusercontent.com' && lang_pair!='"+default_language+"|"+default_language+"')openTab('http://translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+escape(location.href));else openTab('http://translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+unescape(gfg('u')));}"+new_line;
            else
                widget_code += "function doBTranslator(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(location.hostname!='translate.googleusercontent.com' && lang_pair=='"+default_language+"|"+default_language+"')return;else if(location.hostname=='translate.googleusercontent.com' && lang_pair=='"+default_language+"|"+default_language+"')openTab(unescape(gfg('u')));else if(location.hostname!='translate.googleusercontent.com' && lang_pair!='"+default_language+"|"+default_language+"')openTab('http://translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+escape(location.href));else openTab('http://translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+unescape(gfg('u')));}"+new_line;
            widget_code += 'function gfg(name) {name=name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");var regexS="[\\?&]"+name+"=([^&#]*)";var regex=new RegExp(regexS);var results=regex.exec(location.href);if(results==null)return "";return results[1];}'+new_line;
            widget_code += "function openTab(url) {var form=document.createElement('form');form.method='post';form.action=url;form.target='_blank';document.body.appendChild(form);form.submit();}"+new_line;
        } else if(translation_method == 'redirect') {
            widget_code += 'if(top.location!=self.location)top.location=self.location;'+new_line;
            widget_code += "window['_tipoff']=function(){};window['_tipon']=function(a){};"+new_line;
            if(analytics)
                widget_code += "function doBTranslator(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;if(location.hostname!='translate.googleusercontent.com' && lang_pair=='"+default_language+"|"+default_language+"')return;var lang=lang_pair.split('|')[1];_gaq.push(['_trackEvent', 'BTranslator', lang, location.pathname+location.search]);if(location.hostname=='translate.googleusercontent.com' && lang_pair=='"+default_language+"|"+default_language+"')location.href=unescape(gfg('u'));else if(location.hostname!='translate.googleusercontent.com' && lang_pair!='"+default_language+"|"+default_language+"')location.href='http://translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+escape(location.href);else location.href='http://translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+unescape(gfg('u'));}"+new_line;
            else
                widget_code += "function doBTranslator(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(location.hostname!='translate.googleusercontent.com' && lang_pair=='"+default_language+"|"+default_language+"')return;else if(location.hostname=='translate.googleusercontent.com' && lang_pair=='"+default_language+"|"+default_language+"')location.href=unescape(gfg('u'));else if(location.hostname!='translate.googleusercontent.com' && lang_pair!='"+default_language+"|"+default_language+"')location.href='http://translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+escape(location.href);else location.href='http://translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+unescape(gfg('u'));}"+new_line;
            widget_code += 'function gfg(name) {name=name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");var regexS="[\\?&]"+name+"=([^&#]*)";var regex=new RegExp(regexS);var results=regex.exec(location.href);if(results==null)return "";return results[1];}'+new_line;
        } else if(translation_method == 'on_fly') {
            widget_code += "if(jQuery.cookie('glang') && jQuery.cookie('glang') != '"+default_language+"') jQuery(function(\$){\$('body').translate('"+default_language+"', \$.cookie('glang'), {toggle:true, not:'.notranslate'});});"+new_line;
            if(analytics)
                widget_code += "function doBTranslator(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;var lang=lang_pair.split('|')[1];_gaq.push(['_trackEvent', 'BTranslator', lang, location.pathname+location.search]);jQuery.cookie('glang', lang, {path: '/'});jQuery(function(\$){\$('body').translate('"+default_language+"', lang, {toggle:true, not:'.notranslate'});});}"+new_line;
            else
                widget_code += "function doBTranslator(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;var lang=lang_pair.split('|')[1];jQuery.cookie('glang', lang, {path: '/'});jQuery(function(\$){\$('body').translate('"+default_language+"', lang, {toggle:true, not:'.notranslate'});});}"+new_line;
        }

        widget_code += '//]]>'+new_line;
        widget_code += '<\/script>'+new_line;

    }

    widget_code = widget_preview + widget_code;

    jQuery('#widget_code').val(widget_code);

    ShowWidgetPreview(widget_preview);

}

function ShowWidgetPreview(widget_preview) {
    widget_preview = widget_preview.replace(/javascript:doBTranslator/g, 'javascript:void')
    widget_preview = widget_preview.replace('onchange="doBTranslator(this);"', '');
    widget_preview = widget_preview.replace('if(jQuery.cookie', 'if(false && jQuery.cookie');
    jQuery('#widget_preview').html(widget_preview);
}

jQuery('#pro_version').attr('checked', '$pro_version'.length > 0);
jQuery('#new_window').attr('checked', '$new_window'.length > 0);
jQuery('#analytics').attr('checked', '$analytics'.length > 0);
jQuery('#load_jquery').attr('checked', '$load_jquery'.length > 0);
jQuery('#add_new_line').attr('checked', '$add_new_line'.length > 0);
jQuery('#show_dropdown').attr('checked', '$show_dropdown'.length > 0);
jQuery('#show_flags').attr('checked', '$show_flags'.length > 0);

jQuery('#default_language').val('$default_language');
jQuery('#translation_method').val('$translation_method');
jQuery('#flag_size').val('$flag_size');

if(jQuery('#widget_code').val() == '')
    RefreshDoWidgetCode();
else
    ShowWidgetPreview(jQuery('#widget_code').val());
EOT;

// selected languages
if(count($incl_langs) > 0)
    $script .= "jQuery.each(languages, function(i, val) {jQuery('#incl_langs'+language_codes[i]).attr('checked', false);});\n";
if(count($fincl_langs) > 0)
    $script .= "jQuery.each(languages, function(i, val) {jQuery('#fincl_langs'+language_codes[i]).attr('checked', false);});\n";
foreach($incl_langs as $lang)
    $script .= "jQuery('#incl_langs$lang').attr('checked', true);\n";
foreach($fincl_langs as $lang)
    $script .= "jQuery('#fincl_langs$lang').attr('checked', true);\n";
?>
        <form id="btranslator" name="form1" method="post" action="<?php echo get_option('siteurl') . '/wp-admin/options-general.php?page=btranslator_options' ?>">
        <p>Use the configuration form below to customize the BTranslator widget.</p>
        <p>If you would like to edit translations manually and have SEF URLs (<?php echo $site_url; ?><b>/es/</b>, <?php echo $site_url; ?><b>/fr/</b>, <?php echo $site_url; ?><b>/it/</b>, etc.) for translated languages or you want your translated pages to be indexed in search engines you may consider <a href="http://edo.webmaster.am/btranslator?xyz=998" target="_blank">BTranslator Pro</a> version.</p>
        <div style="float:left;width:270px;">
            <h4>Widget options</h4>
            <table style="font-size:11px;">
            <tr>
                <td class="option_name">Translation method:</td>
                <td>
                    <select id="translation_method" name="translation_method" onChange="RefreshDoWidgetCode()">
                        <option value="bing_default">Bing Default</option>
                        <option value="on_fly" selected>On Fly (jQuery)</option>
                        <option value="redirect">Redirect</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="option_name">Default language:</td>
                <td>
                    <select id="default_language" name="default_language" onChange="RefreshDoWidgetCode()">
                        <option value="ar">Arabic</option>
                        <option value="bg">Bulgarian</option>
                        <option value="zh-CN">Chinese (Simplified)</option>
                        <option value="zh-TW">Chinese (Traditional)</option>
                        <option value="cs">Czech</option>
                        <option value="da">Danish</option>
                        <option value="nl">Dutch</option>
                        <option value="en" selected>English</option>
                        <option value="et">Estonian</option>
                        <option value="fi">Finnish</option>
                        <option value="fr">French</option>
                        <option value="de">German</option>
                        <option value="el">Greek</option>
                        <option value="ht">Haitian Creole</option>
                        <option value="iw">Hebrew</option>
                        <option value="hu">Hungarian</option>
                        <option value="id">Indonesian</option>
                        <option value="it">Italian</option>
                        <option value="ja">Japanese</option>
                        <option value="ko">Korean</option>
                        <option value="lv">Latvian</option>
                        <option value="lt">Lithuanian</option>
                        <option value="no">Norwegian</option>
                        <option value="pl">Polish</option>
                        <option value="pt">Portuguese</option>
                        <option value="ro">Romanian</option>
                        <option value="ru">Russian</option>
                        <option value="sk">Slovak</option>
                        <option value="sl">Slovenian</option>
                        <option value="es">Spanish</option>
                        <option value="sv">Swedish</option>
                        <option value="th">Thai</option>
                        <option value="tr">Turkish</option>
                        <option value="uk">Ukrainian</option>
                        <option value="vi">Vietnamese</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="option_name">Load jQuery library:</td>
                <td><input id="load_jquery" name="load_jquery" value="1" type="checkbox" checked="checked" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()"/></td>
            </tr>
            <tr>
                <td class="option_name">Open in new window:</td>
                <td><input id="new_window" name="new_window" value="1" type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()"/></td>
            </tr>
            <tr>
                <td class="option_name">Analytics:</td>
                <td><input id="analytics" name="analytics" value="1" type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()"/></td>
            </tr>
            <tr>
                <td class="option_name">Operate with Pro version:</td>
                <td><input id="pro_version" name="pro_version" value="1" type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()"/></td>
            </tr>
            <tr>
                <td class="option_name">Show flags:</td>
                <td><input id="show_flags" name="show_flags" value="1" type="checkbox" checked="checked" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()"/></td>
            </tr>
            <tr>
                <td class="option_name">Flag size:</td>
                <td>
                <select id="flag_size"  name="flag_size" onchange="RefreshDoWidgetCode()">
                    <option value="16" selected>16px</option>
                    <option value="24">24px</option>
                    <option value="32">32px</option>
                </select>
                </td>
            </tr>
            <tr>
                <td class="option_name">Flag languages:</td>
                <td>
                <div style="height:55px;overflow-y:scroll;">
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsar" name="fincl_langs[]" value="ar"><label for="fincl_langsar">Arabic</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsbg" name="fincl_langs[]" value="bg"><label for="fincl_langsbg">Bulgarian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langszh-CN" name="fincl_langs[]" value="zh-CN"><label for="fincl_langszh-CN">Chinese (Simplified)</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langszh-TW" name="fincl_langs[]" value="zh-TW"><label for="fincl_langszh-TW">Chinese (Traditional)</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langscs" name="fincl_langs[]" value="cs"><label for="fincl_langscs">Czech</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsda" name="fincl_langs[]" value="da"><label for="fincl_langsda">Danish</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsnl" name="fincl_langs[]" value="nl"><label for="fincl_langsnl">Dutch</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsen" name="fincl_langs[]" value="en" checked><label for="fincl_langsen">English</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langset" name="fincl_langs[]" value="et"><label for="fincl_langset">Estonian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsfi" name="fincl_langs[]" value="fi"><label for="fincl_langsfi">Finnish</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsfr" name="fincl_langs[]" value="fr" checked><label for="fincl_langsfr">French</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsde" name="fincl_langs[]" value="de" checked><label for="fincl_langsde">German</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsel" name="fincl_langs[]" value="el"><label for="fincl_langsel">Greek</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsht" name="fincl_langs[]" value="ht"><label for="fincl_langsht">Haitian Creole</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsiw" name="fincl_langs[]" value="iw"><label for="fincl_langsiw">Hebrew</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langshu" name="fincl_langs[]" value="hu"><label for="fincl_langshu">Hungarian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsid" name="fincl_langs[]" value="id"><label for="fincl_langsid">Indonesian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsit" name="fincl_langs[]" value="it" checked><label for="fincl_langsit">Italian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsja" name="fincl_langs[]" value="ja"><label for="fincl_langsja">Japanese</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsko" name="fincl_langs[]" value="ko"><label for="fincl_langsko">Korean</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langslv" name="fincl_langs[]" value="lv"><label for="fincl_langslv">Latvian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langslt" name="fincl_langs[]" value="lt"><label for="fincl_langslt">Lithuanian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsno" name="fincl_langs[]" value="no"><label for="fincl_langsno">Norwegian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langspl" name="fincl_langs[]" value="pl"><label for="fincl_langspl">Polish</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langspt" name="fincl_langs[]" value="pt" checked><label for="fincl_langspt">Portuguese</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsro" name="fincl_langs[]" value="ro"><label for="fincl_langsro">Romanian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsru" name="fincl_langs[]" value="ru" checked><label for="fincl_langsru">Russian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langssk" name="fincl_langs[]" value="sk"><label for="fincl_langssk">Slovak</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langssl" name="fincl_langs[]" value="sl"><label for="fincl_langssl">Slovenian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langses" name="fincl_langs[]" value="es" checked><label for="fincl_langses">Spanish</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langssv" name="fincl_langs[]" value="sv"><label for="fincl_langssv">Swedish</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsth" name="fincl_langs[]" value="th"><label for="fincl_langsth">Thai</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langstr" name="fincl_langs[]" value="tr"><label for="fincl_langstr">Turkish</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsuk" name="fincl_langs[]" value="uk"><label for="fincl_langsuk">Ukrainian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langsvi" name="fincl_langs[]" value="vi"><label for="fincl_langsvi">Vietnamese</label><br />
                </div>
                </td>
            </tr>
            <tr>
                <td class="option_name">Add new line:</td>
                <td><input id="add_new_line" name="add_new_line" value="1" type="checkbox" checked="checked" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()"/></td>
            </tr>
            <tr>
                <td class="option_name">Show dropdown:</td>
                <td><input id="show_dropdown" name="show_dropdown" value="1" type="checkbox" checked="checked" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()"/></td>
            </tr>
            <tr>
                <td class="option_name">Dropdown languages:</td>
                <td>
                <div style="height:55px;overflow-y:scroll;">
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsar" name="incl_langs[]" value="ar" checked><label for="incl_langsar">Arabic</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsbg" name="incl_langs[]" value="bg" checked><label for="incl_langsbg">Bulgarian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langszh-CN" name="incl_langs[]" value="zh-CN" checked><label for="incl_langszh-CN">Chinese (Simplified)</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langszh-TW" name="incl_langs[]" value="zh-TW" checked><label for="incl_langszh-TW">Chinese (Traditional)</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langscs" name="incl_langs[]" value="cs" checked><label for="incl_langscs">Czech</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsda" name="incl_langs[]" value="da" checked><label for="incl_langsda">Danish</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsnl" name="incl_langs[]" value="nl" checked><label for="incl_langsnl">Dutch</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsen" name="incl_langs[]" value="en" checked><label for="incl_langsen">English</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langset" name="incl_langs[]" value="et" checked><label for="incl_langset">Estonian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsfi" name="incl_langs[]" value="fi" checked><label for="incl_langsfi">Finnish</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsfr" name="incl_langs[]" value="fr" checked><label for="incl_langsfr">French</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsde" name="incl_langs[]" value="de" checked><label for="incl_langsde">German</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsel" name="incl_langs[]" value="el" checked><label for="incl_langsel">Greek</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsht" name="incl_langs[]" value="ht" checked><label for="incl_langsht">Haitian Creole</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsiw" name="incl_langs[]" value="iw" checked><label for="incl_langsiw">Hebrew</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langshu" name="incl_langs[]" value="hu" checked><label for="incl_langshu">Hungarian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsid" name="incl_langs[]" value="id" checked><label for="incl_langsid">Indonesian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsit" name="incl_langs[]" value="it" checked><label for="incl_langsit">Italian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsja" name="incl_langs[]" value="ja" checked><label for="incl_langsja">Japanese</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsko" name="incl_langs[]" value="ko" checked><label for="incl_langsko">Korean</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langslv" name="incl_langs[]" value="lv" checked><label for="incl_langslv">Latvian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langslt" name="incl_langs[]" value="lt" checked><label for="incl_langslt">Lithuanian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsno" name="incl_langs[]" value="no" checked><label for="incl_langsno">Norwegian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langspl" name="incl_langs[]" value="pl" checked><label for="incl_langspl">Polish</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langspt" name="incl_langs[]" value="pt" checked><label for="incl_langspt">Portuguese</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsro" name="incl_langs[]" value="ro" checked><label for="incl_langsro">Romanian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsru" name="incl_langs[]" value="ru" checked><label for="incl_langsru">Russian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langssk" name="incl_langs[]" value="sk" checked><label for="incl_langssk">Slovak</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langssl" name="incl_langs[]" value="sl" checked><label for="incl_langssl">Slovenian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langses" name="incl_langs[]" value="es" checked><label for="incl_langses">Spanish</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langssv" name="incl_langs[]" value="sv" checked><label for="incl_langssv">Swedish</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsth" name="incl_langs[]" value="th" checked><label for="incl_langsth">Thai</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langstr" name="incl_langs[]" value="tr" checked><label for="incl_langstr">Turkish</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsuk" name="incl_langs[]" value="uk" checked><label for="incl_langsuk">Ukrainian</label><br />
                <input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langsvi" name="incl_langs[]" value="vi" checked><label for="incl_langsvi">Vietnamese</label><br />
                </div>
                </td>
            </tr>
            </table>
        </div>

        <div style="float:left;width:232px;padding-left:50px;">
            <h4>Widget preview</h4>
            <div id="widget_preview"></div>
            <div style="margin-top:15px;"><small class="black">Save the changes to see it in action.</small></div>
            <div style="margin-top:15px;"><small class="black">Note: Analytics feature can be enabled if you have Google Analytics _gaq code in your site. To see the analytics data you need to login to your Google Analytics account -> Content -> Event Tracking. Will not work in Bing Default translation method.</small></div>
            <div style="margin-top:15px;"><small class="black">Note: You will need to use Redirect translation methotd with "Operate with Pro version" on if you have installed the Pro version.</small></div>
        </div>

        <div style="clear:both;"></div>

        <div style="margin-top:20px;">
            <h4>Widget code</h4>
            <span style="color:red;">DO NOT COPY THIS INTO YOUR POSTS OR PAGES! Put [BTranslator] inside the post/page <br />or add a BTranslator widget into your sidebar from Appearance -> Widgets instead.</span><br /><br />
            You can edit this if you wish:<br />
            <textarea id="widget_code" name="widget_code" onchange="ShowWidgetPreview(this.value)" style="font-family:Monospace;font-size:11px;height:150px;width:565px;"><?php echo $widget_code; ?></textarea>
            <?php wp_nonce_field('btranslator-save'); ?>
        </div>
            <p class="submit"><input type="submit" class="button-primary" name="save" value="<?php _e('Save Changes'); ?>" /></p>
        </form>
        </div>
        <script type="text/javascript"><?php echo $script; ?></script>
        <?php
    }

    function control_options() {
        check_admin_referer('btranslator-save');

        $data = get_option('BTranslator');

        $data['pro_version'] = isset($_POST['pro_version']) ? $_POST['pro_version'] : '';
        $data['new_window'] = isset($_POST['new_window']) ? $_POST['new_window'] : '';
        $data['analytics'] = isset($_POST['analytics']) ? $_POST['analytics'] : '';
        $data['load_jquery'] = isset($_POST['load_jquery']) ? $_POST['load_jquery'] : '';
        $data['add_new_line'] = isset($_POST['add_new_line']) ? $_POST['add_new_line'] : '';
        $data['show_dropdown'] = isset($_POST['show_dropdown']) ? $_POST['show_dropdown'] : '';
        $data['show_flags'] = isset($_POST['show_flags']) ? $_POST['show_flags'] : '';
        $data['default_language'] = $_POST['default_language'];
        $data['translation_method'] = $_POST['translation_method'];
        $data['flag_size'] = $_POST['flag_size'];
        $data['widget_code'] = stripslashes($_POST['widget_code']);
        $data['incl_langs'] = $_POST['incl_langs'];
        $data['fincl_langs'] = $_POST['fincl_langs'];

        echo '<p style="color:red;">Changes Saved</p>';
        update_option('BTranslator', $data);
    }

    function load_defaults(& $data) {
        $data['pro_version'] = isset($data['pro_version']) ? $data['pro_version'] : '';
        $data['new_window'] = isset($data['new_window']) ? $data['new_window'] : '';
        $data['analytics'] = isset($data['analytics']) ? $data['analytics'] : '';
        $data['load_jquery'] = isset($data['load_jquery']) ? $data['load_jquery'] : '1';
        $data['add_new_line'] = isset($data['add_new_line']) ? $data['add_new_line'] : '1';
        $data['show_dropdown'] = isset($data['show_dropdown']) ? $data['show_dropdown'] : '1';
        $data['show_flags'] = isset($data['show_flags']) ? $data['show_flags'] : '1';
        $data['default_language'] = isset($data['default_language']) ? $data['default_language'] : 'en';
        $data['translation_method'] = isset($data['translation_method']) ? $data['translation_method'] : 'on_fly';
        $data['flag_size'] = isset($data['flag_size']) ? $data['flag_size'] : '16';
        $data['widget_code'] = isset($data['widget_code']) ? $data['widget_code'] : '';
        $data['incl_langs'] = isset($data['incl_langs']) ? $data['incl_langs'] : array();
        $data['fincl_langs'] = isset($data['fincl_langs']) ? $data['fincl_langs'] : array();
    }
}