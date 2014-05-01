<?php
/*
Plugin Name: Post-Mod-Dare
Plugin URI: 
Version: v1.0
Author: Mike Kasprzak
Description: Inlined Pending Post Moderation (Spammers have driven us to this). 

*/

require_once( ABSPATH . "wp-includes/pluggable.php" );

function show_publish_buttons(){
	Global $post;
	//only print fi admin
	if (current_user_can('edit_others_posts')){
		echo '
		<form action="" method="POST" name="front_end_publish" class="promoform">
			<input id="pid" type="hidden" name="pid" value="'.$post->ID.'" />
			<input id="FE_PUBLISH" type="hidden" name="FE_PUBLISH" value="FE_PUBLISH" />
			<input id="submit" type="submit" name="submit" value="Publish Post" class="promobutton" />
		</form>';

		echo ' | ';

		echo '
		<form action="" method="POST" name="front_end_trash" class="promoform">
			<input id="pid" type="hidden" name="pid" value="'.$post->ID.'" />
			<input id="FE_TRASH" type="hidden" name="FE_TRASH" value="FE_TRASH" />
			<input id="submit" type="submit" name="submit" value="Remove" class="promobutton" />
		</form>';
	}
}

function show_promote_buttons(){
	Global $post;
	//only print fi admin
	if (current_user_can('edit_others_posts')){
		echo '
		<form action="" method="POST" name="front_end_promote" class="promoform">
			<input id="pid" type="hidden" name="pid" value="'.$post->ID.'" />
			<input id="FE_USER_PROMOTE" type="hidden" name="FE_USER_PROMOTE" value="FE_USER_PROMOTE" />
			<input id="submit" type="submit" name="submit" value="Promote to Author" class="promobutton" />
		</form>';

		echo ' | ';
		
		echo '
		<form action="" method="POST" name="front_end_demote" class="promoform">
			<input id="pid" type="hidden" name="pid" value="'.$post->ID.'" />
			<input id="FE_USER_DEMOTE" type="hidden" name="FE_USER_DEMOTE" value="FE_USER_DEMOTE" />
			<input id="submit" type="submit" name="submit" value="QUARANTINE" class="promobutton" />
		</form>';
	}
}

	
// Update the post status
function change_post_status($post_id,$status){
	$current_post = get_post( $post_id, 'ARRAY_A' );
	$current_post['post_status'] = $status;
	wp_update_post($current_post);
}
// Update the user level //
function change_user_level($user_id,$status){
	$newuser = new WP_User( $user_id /*$user->ID*/ );
	$newuser->set_role( $status );
}

// Responses to Post Status Changes //	
if (isset($_POST['FE_PUBLISH']) && $_POST['FE_PUBLISH'] == 'FE_PUBLISH'){
	if (isset($_POST['pid']) && !empty($_POST['pid'])){
		change_post_status((int)$_POST['pid'],'publish');
	}
}
if (isset($_POST['FE_TRASH']) && $_POST['FE_TRASH'] == 'FE_TRASH'){
	if (isset($_POST['pid']) && !empty($_POST['pid'])){
		change_post_status((int)$_POST['pid'],'trash');
	}
}

// Responses to User Level Changes //
if (isset($_POST['FE_USER_PROMOTE']) && $_POST['FE_USER_PROMOTE'] == 'FE_USER_PROMOTE'){
	if (isset($_POST['pid']) && !empty($_POST['pid'])) {
		$current_post = get_post( (int)$_POST['pid'], 'ARRAY_A' );		
		change_user_level( $current_post['post_author'], 'author' );
	}
}
if (isset($_POST['FE_USER_DEMOTE']) && $_POST['FE_USER_DEMOTE'] == 'FE_USER_DEMOTE'){
	if (isset($_POST['pid']) && !empty($_POST['pid'])) {
		$current_post = get_post( (int)$_POST['pid'], 'ARRAY_A' );		
		change_user_level( $current_post['post_author'], 'subscriber' );
	}
}
if (isset($_POST['FE_USER_RESET']) && $_POST['FE_USER_RESET'] == 'FE_USER_RESET'){
	if (isset($_POST['pid']) && !empty($_POST['pid'])) {
		$current_post = get_post( (int)$_POST['pid'], 'ARRAY_A' );		
		change_user_level( $current_post['post_author'], 'contributor' );
	}
}


/* http://wordpress.stackexchange.com/questions/103938/how-to-display-pending-posts-on-the-homepage-only-for-editors */
// If the user is of high enough level, modify the query to return both pending and published posts // 
function allow_pending_posts_wpse_103938($qry) {
  if (!is_admin() && current_user_can('edit_others_posts')) {
    $qry->set('post_status', array('publish','pending'));
  }
}
add_action('pre_get_posts','allow_pending_posts_wpse_103938');

?>
