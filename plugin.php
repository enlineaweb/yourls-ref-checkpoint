<?php
/*
Plugin Name: Referral Link Checkpoint
Description: Inject a landing page checkpoint to let visitors know when they are visiting a referral link.
Version: 1.0
Author: telepathics
Author URI: https://telepathics.xyz
*/

if( !defined( 'YOURLS_ABSPATH' ) ) die();

/*
 * Pass to referral link page
 */
yourls_add_action( 'pre_redirect', 'path_refck_link' );
function path_refck_link( $args ) {
  $url = $args[0];
  $search_string = yourls_get_option( 'path_refck_search_string' );
  $disclosure = yourls_get_option( 'path_refck_disclosure' );

  if( strpos($_SERVER["REQUEST_URI"], $search_string) === 1 ) {
    include 'custom_page.php';

    echo "<div class='container'><div class='row'>";
    if( $disclosure ) {
      echo $disclosure;
    } else {
      echo "Advertising Disclosure: This is a referral link, which means I may be compensated in exchange your clicks or purchases on the following website: $url";
    }
    echo "</div></div>";
    echo "<div class='container'><div class='row'><a href='$url' class='btn btn-success' role='button'>OK, Continue</a></div></div>";

    // Now die so the normal flow of event is interrupted
    die();
  }
}

/*
 * Register admin page
 */
yourls_add_action( 'plugins_loaded', 'path_refck_admin_page_add');
function path_refck_admin_page_add() {
  yourls_register_plugin_page( 'ref_checkpoint', 'Referral Link Checkpoint', 'path_refck_admin_page_do' );
}
function path_refck_admin_page_do() {
  if( isset( $_POST['path_refck_search_string'] ) || isset( $_POST['path_refck_disclosure'] ) ) {
    yourls_verify_nonce( 'path_refck' );
    path_refck_admin_page_update();
  }

  $saved_search_string = yourls_get_option( 'path_refck_search_string' );
  $saved_disclosure = yourls_get_option( 'path_refck_disclosure' );
  $nonce = yourls_create_nonce( 'path_refck' );

  echo '<h2>Referral Link Checkpoint</h2>';
  echo '<form method="post">';
  echo '<input type="hidden" name="nonce" value="' . $nonce . '" />';

  echo '<label for="path_refck_search_string"><b>Referral Keyword:</b> </label>';
  echo '<input id="path_refck_search_string" name="path_refck_search_string" value="' . $saved_search_string . '" placeholder="e.g. spon" />';
  echo '<p>Please define the CASE-SENSITIVE short URL keyword you PREPEND to your short links to indicate a referral link destination.</p>';

  echo '<label for="path_refck_disclosure"><b>General Disclosure:</b><br /></label>';
  echo '<textarea id="path_refck_disclosure" name="path_refck_disclosure" cols="50" rows="3" value="' . $saved_disclosure . '" placeholder="Advertising Disclosure: This is a referral link, which means I may be compensated in exchange your clicks or purchases on the following website..."></textarea>';
  echo '<p>Please let your visitors know what your referral links entail.  You can also create and modify a custom_page.php file.</p>';

  echo '<p><input type="submit" value="Update" /></p>';
  echo '</form>';
}
function path_refck_admin_page_update() {
  $search_string = $_POST['path_refck_search_string'];
  $disclosure = $_POST['path_refck_disclosure'];

  if( $search_string ) {
    if( yourls_get_option( 'path_refck_search_string' ) !== false ) {
      yourls_update_option( 'path_refck_search_string', $search_string );
      echo '<b>Keyword successfully updated.</b>';
    } else {
      yourls_add_option( 'path_refck_search_string', $search_string );
      echo '<b>Keyword successfully stored.</b>';
    }
  }
  if( $disclosure ) {
    if( yourls_get_option( 'path_refck_disclosure' ) !== false ) {
      yourls_update_option('path_refck_disclosure', $disclosure) ;
      echo '<b>Disclosure successfully updated.</b>';
    } else {
      yourls_add_option( 'path_refck_disclosure', $disclosure );
      echo '<b>Disclosure successfully stored.</b>';
    }
  }
}
