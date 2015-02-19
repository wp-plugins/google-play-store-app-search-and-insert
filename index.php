<?php
/*
  Plugin Name: Google Play Store App Search And Insert
  Plugin URI: https://wordpress.org/plugins/google-play-store-app-search-and-insert/
  Description: This plugin help you search and insert android app info from Google Play Store to content very quickly.
  Version: 1.0.1
  Author: maylamkeobong
  Author URI: http://anybuy.vn/may-lam-keo-bong-gon.htm
 */

add_action('media_buttons_context', 'gpssi_add_button');
function gpssi_add_button($context) {
    $context = '<a href="#gpssi_popup" id="gpssi-btn" class="button add_media" title="Google Image"><span class="wp-media-buttons-icon"></span>Insert Android App Info</a><input type="hidden" id="gpssi_featured_url" name="gpssi_featured_url" value="" />';
    return $context;
}

add_action('admin_enqueue_scripts', 'gpssi_enqueue');
function gpssi_enqueue($hook) {
    if (('edit.php' != $hook) && ('post-new.php' != $hook) && ('post.php' != $hook))
        return;
    wp_enqueue_script('colorbox', plugin_dir_url(__FILE__) . '/js/jquery.colorbox.js', array('jquery'));
    wp_enqueue_style('colorbox', plugins_url('css/colorbox.css', __FILE__));
}

function gpssi_get_remote_html( $url ) {
	$response = wp_remote_get( $url );
	if ( is_wp_error( $response ) ) {
		return ( '' );
	}
	$data = wp_remote_retrieve_body( $response );
	if ( is_wp_error( $data ) ) {
		return ( '' );
	}
	return $data;
}
add_action('wp_ajax_gpssi_search_action', 'gpssi_search');
function gpssi_search(){
	$retsult = '';
	$html = gpssi_get_remote_html('https://play.google.com/store/search?q='.$_POST['keyword'].'&c=apps');
	if($html == ''){ 
		$retsult = 'No result! Please try again!';
	}
	else {				
		$start = strpos($html, '<div class="cover-image-container">');
		while ( $start ) {
			$end = strpos($html, '<div class="reason-set-star-rating">', $start);
			$item = substr($html, $start , $end - $start);

			$app_id_start = strpos($item, 'href="/store/apps/details?id=') + 29;
			$app_id_end = strpos($item, '"', $app_id_start);
			$app_id = substr($item, $app_id_start , $app_id_end - $app_id_start);
					
			$app_name_start = strpos($item, '<img alt="') + 10;
			$app_name_end = strpos($item, '"', $app_name_start);
			$app_name = substr($item, $app_name_start , $app_name_end - $app_name_start);

			$app_image_start = strpos($item, 'data-cover-small="') + 18;
			$app_image_end = strpos($item, '"', $app_image_start);
			$app_image = substr($item, $app_image_start , $app_image_end - $app_image_start);
					
			$app_image_start = strpos($item, 'data-cover-large="') + 18;
			$app_image_end = strpos($item, '"', $app_image_start);
			$app_image2 = substr($item, $app_image_start , $app_image_end - $app_image_start);

			$app_des_start = strpos($item, '<div class="description">') + 25;
			$app_des_end = strpos($item, '<span class="paragraph-end">', $app_des_start);
			$app_des = substr($item, $app_des_start , $app_des_end - $app_des_start);	

			$app_price_start = strpos($item, '<span class="display-price">') + 28;
			$app_price_end = strpos($item, '</span>', $app_price_start);
			$app_price = substr($item, $app_price_start , $app_price_end - $app_price_start);													
					
			$retsult .= '<div class="gpssi-item"><div class="gpssi-item-link"><a href="https://play.google.com/store/apps/details?id='. $app_id .'" target="_blank" title="View this image in new windows">View</a><a class="gpssi-item-use" gpssiurl="https://play.google.com/store/apps/details?id='.$app_id.'" gpssiimage="'.$app_image.'" gpssiname="'.$app_name.'" gpssides="'.wp_html_excerpt( $app_des, 500 ).'" gpssiimage450="'. $app_image2.'" gpssiprice="'.$app_price.'" href="#">Use this image</a></div><div class="gpssi-item-overlay"></div><img src="'. $app_image.'"><span>100 x 100</span></div>';
			$start = strpos($html, '<div class="cover-image-container">', $end );
		}//endwhile
	}//endif
	echo $retsult;
	die();
}

add_action('admin_footer', 'gpssi_add_inline_popup_content');
function gpssi_add_inline_popup_content() {
    ?>
    <style>
        .gpssi-container{
			border: 1px #999999 solid;
            width: 640px;
			height: 320px;
            display: inline-block;
            margin-top: 10px;
			overflow-y: scroll;
        }
        .gpssi-item{
            position: relative;
            display: inline-block;
            width: 140px;
            height: 140px;
            text-align: center;
            border: 1px solid #ddd;
            float: left;
            margin-right: 3px;
            margin-bottom: 3px;
            padding: 2px;
            background: #fff;
        }
        .gpssi-item img{
            max-width: 140px;
            max-height: 140px;
        }
        .gpssi-use-image{
			border: 1px #999999 solid;
            width: 100%;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px solid #dedede;
			height: 150px;
			overflow-y: scroll;
        }
        .gpssi-item span{
            position: absolute;
            bottom: 2px;
            right: 2px;
            background: #000;
            padding: 0 4px;
            color: #fff;
            font-size: 10px;
        }
        .gpssi-page{
            text-align: center;
        }
        .gpssi-item-overlay{width: 140px;height: 140px;background: #000; position: absolute; top: 2px; left: 2px; z-index: 997; opacity:0.7; filter:alpha(opacity=70); display: none}
        .gpssi-item-link{display: none; position: absolute; top: 50px; width: 100%; text-align: center; z-index: 998}
        .gpssi-item-link a{
            display: inline-block;
            background: #fff;
            padding: 0 10px;
            height: 24px;
            line-height: 24px;
            margin-bottom: 5px;
            text-decoration: none;
            width: 90px;
            font-size: 12px;
        }
        .gpssi-item:hover > .gpssi-item-overlay{display: block}
        .gpssi-item:hover > .gpssi-item-link{display: block}
        .gpssi-loading{display: inline-block; height: 20px; line-height: 20px; min-width:20px; padding-left: 25px; background: url("<?php echo plugin_dir_url(__FILE__) . '/images/spinner.gif'; ?>") no-repeat;}
    </style>
    <div style='display:none'>
        <div id="gpssi_popup" style="width: 640px; height: 600px; padding: 10px; overflow: hidden">
            <select name="gpssitype" id="gpssitype" style="float:left">
                <option value="info">app info</option>
                <option value="image">app image</option>
            </select>
            <select name="gpssiborder" id="gpssiborder" style="float:left">
            	<option value="none">none</option>
                <option value="border">border = 1px dotted #ddd</option>
                <option value="quote">use [quote]</option>
                <option value="code">use [code]</option>
                <option value="hr">use [hr] end</option>
            </select>
            <div style="width:98%; display: inline-block; margin-top: 5px; height:28px; line-height: 28px;"><span style="float:left; margin-right: 10px;">Insert keyword here:</span> <input type="text" id="gpssiinput" name="gpssiinput" value="" size="30"/> <input type="button" id="gpssisearch" class="button" value="Search"/> <span id="gpssispinner" style="display:none" class="gpssi-loading"> </span></div>
            <div id="gpssi-container" class="gpssi-container"></div>
            <div id="gpssi-page" class="gpssi-page"></div>
            <div id="gpssi-use-image" class="gpssi-use-image"></div>
            <div style="text-align:right; padding-top: 5px;"><input type="button" id="gpssiinsert" class="button button-primary" value="Insert into post"></div>
        </div>
    </div>
    <script>
        function insertAtCaret(areaId, text) {
            var txtarea = document.getElementById(areaId);
            var scrollPos = txtarea.scrollTop;
            var strPos = 0;
            var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
                "ff" : (document.selection ? "ie" : false));
            if (br == "ie") {
                txtarea.focus();
                var range = document.selection.createRange();
                range.moveStart('character', -txtarea.value.length);
                strPos = range.text.length;
            }
            else if (br == "ff")
                strPos = txtarea.selectionStart;

            var front = (txtarea.value).substring(0, strPos);
            var back = (txtarea.value).substring(strPos, txtarea.value.length);
            txtarea.value = front + text + back;
            strPos = strPos + text.length;
            if (br == "ie") {
                txtarea.focus();
                var range = document.selection.createRange();
                range.moveStart('character', -txtarea.value.length);
                range.moveStart('character', strPos);
                range.moveEnd('character', 0);
                range.select();
            }
            else if (br == "ff") {
                txtarea.selectionStart = strPos;
                txtarea.selectionEnd = strPos;
                txtarea.focus();
            }
            txtarea.scrollTop = scrollPos;
        }
        jQuery("#gpssisearch").click(function() {
            vShowImages(0);
        });
        jQuery("#gpssi-btn").colorbox({inline: true, width: "670px"});
        jQuery("#gpssi-page a").live("click", function() {
            vShowImages(jQuery(this).attr("rel") - 1);
        });
        jQuery("#gpssiinsert").live("click", function() {
            //if(jQuery(".gpssi-item-use").html() != '') {
                vinsert = jQuery("#gpssi-use-image").html();
                if (!tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
                    insertAtCaret('content', vinsert);
                } else {
                    tinyMCE.activeEditor.execCommand('mceInsertContent', 0, vinsert);
                }
                jQuery.colorbox.close();
            //} else {
                //alert('Have an error! Please try again!');
            //}
        });
        jQuery("#gpssifeatured").live("click", function() {
            vffurl = jQuery('#gpssi-url').val();
            jQuery('#gpssi_featured_url').val(vffurl);
            jQuery('#postimagediv div.inside img').remove();
            jQuery('#postimagediv div.inside').prepend('<img src="'+vffurl+'" width="270"/>');
            jQuery.colorbox.close();
        });
        jQuery("#remove-post-thumbnail").live("click", function() {
            jQuery('#gpssi_featured_url').val('');
        });
        jQuery(".gpssi-item-use").live("click", function() {
            jQuery("#gpssi-use-image").show();
			if(jQuery("#gpssitype").val() == 'info'){//if type
			html = '<div class="product-entry" style="clear: both; height: 110px; padding: 3px; overflow: hidden;';
			if (jQuery("#gpssiborder").val() == 'border'){
				html += 'border: 1px dotted #ddd;';
			}
			html += '"><a title="' + jQuery(this).attr('gpssiname') + '" href="' + jQuery(this).attr('gpssiurl') + '"><img style="float: left; display: inline; margin: 0 15px 10px 0; border: 4px solid #eee; padding: 1px;" src="' + jQuery(this).attr('gpssiimage') + '" alt="' + jQuery(this).attr('gpssiname') + '" width="100" height="100" /></a><strong><a title="' + jQuery(this).attr('gpssiname') + '" href="' + jQuery(this).attr('gpssiurl') + '">' + jQuery(this).attr('gpssiname') + '</a></strong><br /><strong style="color:#f00">' + jQuery(this).attr('gpssiprice') + '</strong><br />' + jQuery(this).attr('gpssides') + '</div>';
			switch(jQuery("#gpssiborder").val()) {
				case 'hr':
					html += '<hr />';
					break;
				case 'quote':
					html = '<blockquote>' + html + '</blockquote>';
					break;
				case 'code':
					html = '<code>' + html + '</code>';
					break;					
				default:
					html = html;
			}
			}else{// if type
			html = '<div style="width: 460px" class="wp-caption aligncenter"><a href="' + jQuery(this).attr('gpssiurl') + '"><img class="size-full" src="' + jQuery(this).attr('gpssiimage450') + '" alt="' + jQuery(this).attr('gpssiname') + '" width="450" height="450"></a><p class="wp-caption-text">' + jQuery(this).attr('gpssiname') + '</p></div>';
			}// end if type
			jQuery("#gpssi-use-image").html(html);
        });
        function vShowImages(page) {
            if(jQuery("#gpssiinput").val() == '') {
                alert('Please enter keyword to search!');
            } else {
                jQuery('#gpssispinner').show();
                jQuery('#gpssi-container').html('');
                vstart = page * 8;
				var data = {
					action: 'gpssi_search_action',
					keyword: jQuery("#gpssiinput").val()
				};
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(response) {
					jQuery('#gpssi-container').html(response);
					jQuery('#gpssispinner').hide();	
				});
            }
        }
    </script>
    <?php
}
?>