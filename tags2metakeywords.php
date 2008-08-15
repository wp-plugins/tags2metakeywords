<?php
/*
Plugin Name: Tags2MetaKeywords
Plugin URI: http://www.thinkagain.cn/archives/827.html
Description: Add keyword based on tags, author, description and copyright info to meta area in html header. Developed for Wordpress 2.3.
Author: ThinkAgain
Version: 0.31
Author URI:http://www.thinkagain.cn/archives/827.html
*/ 
/*
Installation:
1. Upload tags2metakeywords folder to \wp-content\plugins\.
2. Activate plugin at the 'Plugin' option page in wordpress.
Upgrade:
1. Delete the previous tags2metakeywords.php under \wp-content\plugins\.
2. Upload tags2metakeywords folder to \wp-content\plugins\.
3. Activate plugin at the 'Plugin' option page in wordpress.
Uninstallation:
deactivate plugin at the 'Plugin' option page in wordpress.
*/
	load_plugin_textdomain('tags2metakeywords',"/wp-content/plugins/tags2metakeywords/" );
	add_filter('wp_head', 'tags2metakeywords');
	add_action('admin_menu', 'tags2metakeywords_add_option');
	function tags2metakeywords(){
		global $post,$wpdb;
		$options = get_option('tags2metakeywords');
		$author = $options['author'];
		$index_keywords = $options['index_keywords'];
		$index_description = $options['index_description'];
		$metakeywords = $index_keywords;
		$description = $index_description;
		$copyright = $options['copyright'];
		if (is_category()) {
			$categorykeywords = $options['category_keywords'];
			$catname = single_cat_title('', false);
			$cat_id = get_cat_ID($catname);
			$categorydescription = tags2metakeywords_getcategory_description($cat_id);
			$category_description = $options['category_description'];
			if ( empty( $categorykeywords )){
				$metakeywords .= ',' . $catname ;
			}else{
				$metakeywords .= ',' . $categorykeywords . ',' . $catname;
			}
			if (!empty($categorydescription)){
				$description = $categorydescription;
			} elseif (!empty($category_description)){
				$description = $category_description;
			}
		}elseif(is_single()){
			$gettag = get_the_tags($post->ID);
			$getcat = get_the_category($post->ID);
			foreach ($getcat as $category) {
				$catname[] = $category->name;
			}
			if ( empty( $gettag ) ){
				$metakeywords .= ',' . implode( ',', $catname );	
			}else{
				foreach ($gettag as $tag) {
				$posttag[] = $tag->name;
				}
				$metakeywords .= ',' . implode( ',', $posttag ) .',' . implode( ',', $catname );
			}
		}elseif(is_page()){
			$page_keywords = $options['page_keywords'];
			$pagename = $post->post_title;
			if (empty($page_keywords)){
				$metakeywords .= ',' . $pagename;
			}else{
				$metakeywords .= ',' . $page_keywords. ',' . $pagename;
			}
		}elseif(is_tag()) {
			$tag_keywords = $options['tag_keywords'];
			$tagname = single_tag_title('', false);
			if (empty($tag_keywords)){
				$metakeywords .= ',' . $tagname;
			}else{
				$metakeywords .= ',' . $tag_keywords . ',' . $tagname;
			}
		}elseif(is_archive()){
			$archive_keywords = $options['archive_keywords'];
			$archive_date = get_the_time('Ym');
			if (empty($archive_keywords)){
				$metakeywords .= ',' . $archive_date;
			}else{
				$metakeywords .= ',' . $archive_keywords . ',' . $archive_date;
			}
		}elseif(is_search()){
			$search_keywords = $options['search_keywords'];
			$search_query = get_query_var('s');
			if (empty($search_keywords)){
				$metakeywords .= ',' . $search_query;
			}else{
				$metakeywords .= ',' . $search_keywords . ',' . $search_query;
			}
		}elseif(is_404()){
			$keywords_404 = $options['404_keywords'];
			if (!empty($keywords_404)){
				$metakeywords .= ',' . $keywords_404;
			}	
		}
		echo "\n". '<!-- Start of meta info created by tags2metakeywords Plugin by http://www.thinkagain.cn/ -->'."\n";
		if (empty($author)){
			$author = tags2metakeywords_getauthor_name();
		}
		echo '<meta name="author" content="'.$author.'"/>'."\n" ;
		if (empty($copyright)){
			$copyright = 'Copyright &copy; ' .date("Y") .' '. get_bloginfo('url') .' All Rights Reserved.';
		}
		echo '<meta name="copyright" content="'.$copyright.'"/>'."\n" ;
		if (empty($description)){
			$description = get_option('blogdescription');
		}
		echo '<meta name="description" content="'.$description.'"/>'."\n" ;
		if (empty($metakeywords)){
			echo '<!-- end of meta info created by tags2metakeywords. Plugin by http://www.thinkagain.cn/ -->' . "\n";
		}else{
			if (empty($index_keywords)){
				$metakeywords = ltrim($metakeywords,',');
			}
			echo '<meta name="keywords" content="'.$metakeywords.'"/>' ;
			echo "\n". '<!-- end of meta info created by tags2metakeywords. Plugin by http://www.thinkagain.cn/ -->' . "\n";
		}
	}
	function tags2metakeywords_getauthor_name(){
		global $wpdb;
		$name = $wpdb->get_var("SELECT display_name FROM $wpdb->users WHERE user_nicename = 'admin'");
		return $name;
	}
	function tags2metakeywords_getcategory_description($cat_id){
		global $wpdb;
		$description = $wpdb->get_var("SELECT description FROM $wpdb->term_taxonomy WHERE term_id = '$cat_id'");
		return $description;
	}
	function tags2metakeywords_add_option() {
		if (function_exists('tags2metakeywords_option')) {
			add_options_page('tags2metakeywords', 'tags2metakeywords',8, 'tags2metakeywords', 'tags2metakeywords_option');
		}
	}
	function tags2metakeywords_option(){
		$options = get_option('tags2metakeywords');
		if (isset($_POST['update_setting'])) {
			$options['author'] = $_POST['author'];
			$options['copyright'] = $_POST['copyright'];
			$options['index_keywords'] = $_POST['index_keywords'];
			$options['index_description'] = $_POST['index_description'];
			$options['category_keywords'] = $_POST['category_keywords'];
			$options['category_description'] = $_POST['category_description'];
			$options['page_keywords'] = $_POST['page_keywords'];
			$options['archive_keywords'] = $_POST['archive_keywords'];
			$options['tag_keywords'] = $_POST['tag_keywords'];
			$options['search_keywords'] = $_POST['search_keywords'];
			$options['404_keywords'] = $_POST['404_keywords'];
			update_option('tags2metakeywords', $options);
			echo '<div id="message" class="updated fade"><p>';
			echo _e("setting updated",'tags2metakeywords');
			echo '</p></div>';
		}
		else if (isset($_POST['set_default'])) {
			$options['author'] = tags2metakeywords_getauthor_name();
			$options['copyright'] = '';
			$options['index_keywords'] = '';
			$options['index_description'] = get_option('blogdescription');
			$options['category_keywords'] = '';
			$options['category_description'] = '';
			$options['page_keywords'] = '';
			$options['archive_keywords'] = '';
			$options['tag_keywords'] = '';
			$options['search_keywords'] = '';
			$options['404_keywords'] = '';
			update_option('tags2metakeywords', $options);
			echo '<div id="message" class="updated fade"><p>';
			echo _e("default loaded",'tags2metakeywords');
			echo '</p></div>';		    
		}
		?>
<div class="wrap">
<h2><?php _e("tags2metakeywords options",'tags2metakeywords');?></h2>
<p style="font-size:1.5em;"><?php _e("caution",'tags2metakeywords');?></p>
<form method="post">
	<fieldset name="set_author">
		<p><?php _e("blog author",'tags2metakeywords');?></p>
		<p style="text-decoration:blink"><?php if (empty($options['author'])) _e("No author",'tags2metakeywords');?></p>
		<textarea style="width:50%;height:25px;" name="author"><?php echo $options['author'];?></textarea>
	</fieldset>
	<fieldset name="set_copyright">
		<p><?php _e("copyright",'tags2metakeywords');?></p>
		<textarea style="width:50%;height:25px;" name="copyright"><?php echo $options['copyright'];?></textarea>
	</fieldset>
	<fieldset name="set_index_keywords">
		<p><?php _e("index keywords",'tags2metakeywords');?></p>
		<p style="text-decoration:blink"><?php if (empty($options['index_keywords'])) _e("No index keywords",'tags2metakeywords');?></p>
		<textarea style="width:50%;height:25px;" name="index_keywords"><?php echo $options['index_keywords'];?></textarea>
	</fieldset>
	<fieldset name="set_index_description">
		<p><?php _e("index description",'tags2metakeywords');?></p>
		<textarea style="width:50%;height:50px;" name="index_description"><?php echo $options['index_description'];?></textarea>
	</fieldset>
	<fieldset name="set_category_keywords">
		<p><?php _e("category keywords",'tags2metakeywords');?></p>
		<textarea style="width:50%;height:25px;" name="category_keywords"><?php echo $options['category_keywords'];?></textarea>
	</fieldset>
	<fieldset name="set_category_description">
		<p><?php _e("category description",'tags2metakeywords');?></p>
		<textarea style="width:50%;height:50px;" name="category_description"><?php echo $options['category_description'];?></textarea>
	</fieldset>
	<fieldset name="set_page_keywords">
		<p><?php _e("page keywords",'tags2metakeywords');?></p>
		<textarea style="width:50%;height:25px;" name="page_keywords"><?php echo $options['page_keywords'];?></textarea>
	</fieldset>
	<fieldset name="set_archive_keywords">
		<p><?php _e("archive keywords",'tags2metakeywords');?></p>
		<textarea style="width:50%;height:25px;" name="archive_keywords"><?php echo $options['archive_keywords'];?></textarea>
	</fieldset>
	<fieldset name="set_tag_keywords">
		<p><?php _e("tag keywords",'tags2metakeywords');?></p>
		<textarea style="width:50%;height:25px;" name="tag_keywords"><?php echo $options['tag_keywords'];?></textarea>
	</fieldset>
	<fieldset name="set_search_keywords">
		<p><?php _e("search keywords",'tags2metakeywords');?></p>
		<textarea style="width:50%;height:25px;" name="search_keywords"><?php echo $options['search_keywords'];?></textarea>
	</fieldset>
	<fieldset name="set_404_keywords">
		<p><?php _e("404 keywords",'tags2metakeywords');?></p>
		<textarea style="width:50%;height:25px;" name="404_keywords"><?php echo $options['404_keywords'];?></textarea>
	</fieldset>
	<div class="submit">
		<input type="submit" name="set_default" value="<?php _e("load default",'tags2metakeywords');?>" />
		<input type="submit" name="update_setting" value="<?php _e("update setting",'tags2metakeywords');?>" />
	</div>
</form>
</div>
<?php
	}
?>