<?php
/*
  Plugin Name: VouchSafe
  Plugin URI: http://api.vouchsafe.com/
  Description: The VouchSafe plugin adds the easy, effective VouchSafe spam protection system to your WordPress comments and registration systems.
  Version: 1.3
  Author: ShareThink Ltd.
  Author URI: http://www.vouchsafe.com
  License: GPL2
 */

/*  Copyright 2011   VouchSafe

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/*  VouchSafe Library */
require_once(dirname(__FILE__) . '/vouchsafe-lib.php');

/**
 * Adding session if it's not valid
 */
function vouchsafe_plugin_init() {
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'vouchsafe_plugin_init');
/**
 * Adding the CSS into the page whenever VouchSafe plugin is used
 */
function vouchsafe_css()
{
    if (!defined('WP_CONTENT_URL'))
        define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');

    $path = WP_CONTENT_URL . '/plugins/vouchsafe/vouchsafe.css';

    echo '<link rel="stylesheet" type="text/css" href="' . $path . '" />';
}
add_action('admin_head', 'vouchsafe_css'); // style option page


/* GLOBALS AND OPTIONS AND ADMIN PANEL */
$vouchsafe_saved_error = '';

$option_defaults = array(
    'pubkey' => '', // the public key for VouchSafe
    'privkey' => '', // the private key for VouchSafe
    'vouchsafe_comments' => '1', // whether or not to show the VouchSafe widget on the comment post
    'vouchsafe_registration' => '1', // whether or not to show the VouchSafe widget on the registration page
    'error_blank' => '<strong>ERROR</strong>: Please complete the VouchSafe challenge.', // message to display if the user forget to answer the VouchSafe challenge
    'error_incorrect' => '<strong>ERROR</strong>: Your response to the VouchSafe challenge was incorrect.', // message to display when the user's response to the VouchSafe challenge is wrong
);
add_option('vouchsafe', $option_defaults); // First time use, add the default options.  Will do nothing afterward
$vouchsafe_opt = get_option('vouchsafe'); // get the options from the database

/**
 * Check if the user has the appropriate credentials and add the plugin menu to the admin page
 */
function vouchsafe_wp_add_options_to_admin()
{
    if (is_super_admin ())
    {
        add_submenu_page('wpmu-admin.php', 'VouchSafe', 'VouchSafe', 'manage_options', __FILE__, 'vouchsafe_wp_options_subpanel');
        add_options_page('VouchSafe', 'VouchSafe', 'manage_options', __FILE__, 'vouchsafe_wp_options_subpanel');
    }
}
add_action('admin_menu', 'vouchsafe_wp_add_options_to_admin');

/**
 * Display the option menu
 * @global array $vouchsafe_opt
 */
function vouchsafe_wp_options_subpanel()
{
    global $vouchsafe_opt;

    /* Check form submission and update options if no error occurred */
    if (isset($_POST['submit']))
    {
        $optionarray_update = array(
            'pubkey' => trim($_POST['vouchsafe_opt_pubkey']),
            'privkey' => trim($_POST['vouchsafe_opt_privkey']),
            'vouchsafe_comments' => $_POST['vouchsafe_comments'],
            'vouchsafe_registration' => $_POST['vouchsafe_registration'],
            'error_blank' => $_POST['error_blank'],
            'error_incorrect' => $_POST['error_incorrect'],
        );
        // save updated options
        update_option('vouchsafe', $optionarray_update);
    }

    // Refresh the options
    $vouchsafe_opt = get_option('vouchsafe'); // get the options from the database
?>
    <!------------- ADMIN DOM -------------->
    <div class="wrap" id="vouchsafe-admin">
        <h2>VouchSafe Options</h2>
        <h3>About VouchSafe</h3>
        <p>VouchSafe is a free, highly secure replacement for CAPTCHAs that helps you filter spam and undesired comments from your blog.</p>

        <p>VouchSafe ensures the validity of the users submitting comments by asking them to complete a simple task that would be impossible for a machine. For more details, visit the <a href="http://www.vouchsafe.com/">VouchSafe web site</a>.</p>

        <form name="form1" method="post" action="<?php echo $_SERVER['REDIRECT_SCRIPT_URI'] . '?page=' . plugin_basename(__FILE__); ?>&updated=true">
            <div class="submit">
                <input type="submit" name="submit" value="<?php _e('Update Options') ?> &raquo;" />
            </div>

            <!-- ****************** Operands ****************** -->
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">VouchSafe Keys</th>
                    <td>
                        <em>VouchSafe requires that you provide a KeySet</em> composed of a Public Key and a Private Key.  You can get your KeySet for free by registering with the <a href="http://console.vouchsafe.com/">VouchSafe Administration Console</a>. For more information about setting up your VouchSafe plugin visit the <a href="http://www.vouchsafe.com/get-vouchsafe/">Get VouchSafe</a> page on our website.
                        <br />
                        If you don't configure your plugin with a KeySet, the plugin will still continue to work, but you won't be able to customize the way it looks on your website.
                        <br />
                        <p class="vouchsafe-keys">
                            <!-- VouchSafe public key -->
                            <label class="vouchsafe-key" for="vouchsafe_opt_pubkey">Public Key:</label>
                            <input name="vouchsafe_opt_pubkey" id="vouchsafe_opt_pubkey" size="40" value="<?php echo $vouchsafe_opt['pubkey']; ?>" />
                        <br />
                        <!-- VouchSafe private key -->
                        <label class="vouchsafe-key" for="vouchsafe_opt_privkey">Private Key:</label>
                        <input name="vouchsafe_opt_privkey" id="vouchsafe_opt_privkey" size="40" value="<?php echo $vouchsafe_opt['privkey']; ?>" />
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Comment Options</th>
                <td>
                    <!-- Show VouchSafe on the comment post -->
                    <big>
                        <input type="checkbox" name="vouchsafe_comments" id="vouchsafe_comments" value="1" <?php if ($vouchsafe_opt['vouchsafe_comments'] == true) { echo 'checked="checked"'; } ?> />
                        <label for="vouchsafe_comments">Validate comments submission with VouchSafe.</label>
                    </big>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Registration Options</th>
                <td>
                    <!-- Show VouchSafe on the registration page -->
                    <big>
                        <input type="checkbox" name="vouchsafe_registration" id="vouchsafe_registration" value="1" <?php if ($vouchsafe_opt['vouchsafe_registration'] == true) { echo 'checked="checked"'; }?> />
                        <label for="vouchsafe_registration">Validate user registration with VouchSafe.</label>
                    </big>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Error Messages</th>
                <td>
                    <p>You can customize the messages displayed to the user when they skip the VouchSafe validation or fail to answer a challenge correctly.</p>
                    <!-- Error Messages -->
                    <p class="vouchsafe-errors">
                        <!-- Blank -->
                        <label class="vouchsafe-error" for="error_blank">User did not use the VouchSafe validation:</label><br/>
                        <input name="error_blank" id="error_blank" size="80" value="<?php echo $vouchsafe_opt['error_blank']; ?>" />
                        <br />
                        <br />
                        <!-- Incorrect -->
                        <label class="vouchsafe-error" for="error_incorrect">User`s response to the challenge is incorrect:</label><br/>
                        <input name="error_incorrect" id="error_incorrect" size="80" value="<?php echo $vouchsafe_opt['error_incorrect']; ?>" />
                    </p>
                </td>
        </table>
        <div class="submit">
            <input type="submit" name="submit" value="<?php _e('Update Options') ?> &raquo;" />
        </div>
    </form>
    <p class="copyright">&copy; Copyright 2010&nbsp;&nbsp;<a href="http://www.sharethink.com">ShareThink Ltd.</a></p>
</div>
<!------------- END ADMIN DOM -------------->
<?php 
}

/* =============================================================================
   VouchSafe on Registration Form
   ============================================================================= */
   
// Display the VouchSafe Button on the registration form
function display_vouchsafe($errors) {
global $vouchsafe_opt;

    if ($vouchsafe_opt['vouchsafe_registration'])
    {
        echo vouchsafe_get_html($vouchsafe_opt['pubkey']);
    }
}
add_action('register_form', 'display_vouchsafe');


// Validate the vouchsafe test
function check_vouchsafe($errors) {
    global $vouchsafe_opt;
    global $vouchsafe_saved_error;

    $challengeID = $_POST['vouchsafe-challenge-id'];
    $response = $_POST['vouchsafe-challenge-response'];
    $serverToken = isset($_POST["vouchsafe-server-token"])?$_POST["vouchsafe-server-token"]:null;
	
    $vouchsafe_response = vouchsafe_check_answer($vouchsafe_opt['privkey'], $challengeID, $response, $serverToken);
    if (!$vouchsafe_response->is_valid)
    {
        if($vouchsafe_response->error == 'incorrect-input-parameters')
            $errors->add('vouchsafe_validation_failed', $vouchsafe_opt['error_blank']);
        else
            $errors->add('vouchsafe_validation_failed', $vouchsafe_opt['error_incorrect']);
    }

    return $errors;
}

if ($vouchsafe_opt['vouchsafe_registration']) {
    add_filter('registration_errors', 'check_vouchsafe');
}
/* =============================================================================
   End VouchSafe on Registration Form
   ============================================================================= */

/* =============================================================================
  VouchSafe - The VouchSafe comment spam protection section
  ============================================================================= */
/**
 *  Embeds the vouchsafe widget into the comment form.
 *
 */
function vouchsafe_comment_form()
{
    global $vouchsafe_opt;

    if ($vouchsafe_opt['vouchsafe_comments'])
    {
        $widgetString =  vouchsafe_get_html($vouchsafe_opt['pubkey']);

        // Challenge was failed, display the error message
        if ($_GET['herror'] != null)
        {
            if($_GET['herror'] == 'incorrect-input-parameters')
                echo '<p class="vouchsafe-error" style="color: #990000">' . $vouchsafe_opt['error_blank'] . "</p>";
            else
                echo '<p class="vouchsafe-error" style="color: #990000">' . $vouchsafe_opt['error_incorrect'] . "</p>";
        }

        $comment_string = <<<COMMENT_FORM
<div style="padding: 20px 0">%1\$s</div>

<div id="vouchsafe_below"></div>
<script type='text/javascript'>                
var sub = document.getElementById('submit');
var parent = sub.parentNode;
parent.removeChild(sub);
document.getElementById('vouchsafe_below').appendChild(sub);
if ( typeof _vouchsafe_wordpress_savedcomment != 'undefined') {
document.getElementById('comment').value = _vouchsafe_wordpress_savedcomment;
}
if (typeof _vouchsafe_wordpress_author != 'undefined')
{
    document.getElementById('author').value = _vouchsafe_wordpress_author;
    delete _vouchsafe_wordpress_author;
}
if (typeof _vouchsafe_wordpress_email != 'undefined')
{
    document.getElementById('email').value = _vouchsafe_wordpress_email;
    delete _vouchsafe_wordpress_email;
}
if (typeof _vouchsafe_wordpress_url != 'undefined')
{
    document.getElementById('url').value = _vouchsafe_wordpress_url;
    delete _vouchsafe_wordpress_url;
}
</script>
COMMENT_FORM;

        printf($comment_string, $widgetString);
    }
}
add_action('comment_form', 'vouchsafe_comment_form'); // Adds the VouchSafe widget to the form


/*
 * If the vouchsafe guess was incorrect from vouchsafe_wp_check_comment, retrieve the saved
 * comment so we can reinsert it in the form.
 * @param boolean $approved
 * @return boolean $approved
 */
function vouchsafe_wp_saved_comment()
{
    if (!is_single() && !is_page())
        return;

    if (isset($_GET['herror']) && ($_GET["herror"]!= "") && (isset($_SESSION["vouchsafe_saved_comment"])))
    {
        $comment = $_SESSION["vouchsafe_saved_comment"];
        unset($_SESSION["vouchsafe_saved_comment"]);
        $commentContent = preg_replace('/([\\/\(\)\+\;\'\"])/e', '\'%\'.dechex(ord(\'$1\'))', $comment["comment_content"]);
        $commentContent = preg_replace('/\\r\\n/m', '\\\n', $commentContent);
        $commentAuthor = htmlentities($comment["comment_author"]);
        $commentAuthorEmail = htmlentities($comment["comment_author_email"]);
        $commentAuthorUrl = htmlentities($comment["comment_author_url"]);
        echo "<script type='text/javascript'>
                var _vouchsafe_wordpress_savedcomment =  '" . $commentContent . "';
                _vouchsafe_wordpress_savedcomment = unescape(_vouchsafe_wordpress_savedcomment);
                var _vouchsafe_wordpress_author = '$commentAuthor';
                var _vouchsafe_wordpress_email = '$commentAuthorEmail';
                var _vouchsafe_wordpress_url = '$commentAuthorUrl';
            </script>";
    }
}
add_filter('wp_head', 'vouchsafe_wp_saved_comment', 0);

/**
 * Checks if the VouchSafe Path is valid and sets an error session variable if not
 * @param array $comment_data
 * @return array $comment_data
 */
function vouchsafe_wp_check_comment($comment_data)
{
    global $vouchsafe_opt;
    global $vouchsafe_saved_error;
    
    $challengeID = $_POST['vouchsafe-challenge-id'];
    $response = $_POST['vouchsafe-challenge-response'];
    $serverToken = isset($_POST["vouchsafe-server-token"])?$_POST["vouchsafe-server-token"]:null;
	
    $vouchsafe_response = vouchsafe_check_answer($vouchsafe_opt['privkey'], $challengeID, $response, $serverToken);
    if (!$vouchsafe_response->is_valid)
    {
        $vouchsafe_saved_error = $vouchsafe_response->error;
        $_SESSION["vouchsafe_saved_comment"] = $comment_data;
        wp_redirect(vouchsafe_get_redirect_link($comment_data));
        die();
    }

    return $comment_data;
}
add_filter('preprocess_comment', 'vouchsafe_wp_check_comment', 0); // applied to the comment data prior to any other processing, when saving a new comment in the database

/**
 * Get the redirect link when a challange is failed
 * @global string $vouchsafe_saved_error
 * @param array $comment
 * @return string redirect link
 */
function vouchsafe_get_redirect_link($comment)
{
    global $vouchsafe_saved_error;
    $location = get_permalink( $comment["comment_post_ID"]) . "?herror=$vouchsafe_saved_error#commentform";
    return $location;
}
/*
 * If the VouchSafe challenge from vouchsafe_wp_check_comment was failed, then redirect back to the comment form
 * @param string $location
 * @param OBJECT $comment
 * @return string $location
 */
function vouchsafe_wp_relative_redirect($location, $comment)
{
    global $vouchsafe_saved_error;
    if ($vouchsafe_saved_error != '')
    {
        //replace the '#comment-' chars on the end of $location with '#commentform'.
        $location = substr($location, 0, strrpos($location, '#')) .
                ((strrpos($location, "?") === false) ? "?" : "&") .
                'hcommentid=' . $comment->comment_ID .
                '&herror=' . $vouchsafe_saved_error .
                '&hchash=' . vouchsafe_wp_hash($comment->comment_ID) .
                '#commentform';
    }
    return $location;
}
add_filter('comment_post_redirect', 'vouchsafe_wp_relative_redirect', 0, 2); // applied to the redirect location after someone adds a comment.

/**
 * If the user has not entered their keys yet, warn them that the VouchSafe plugin is not active
 */
function vouchsafe_warning()
{
    global $vouchsafe_opt;
    if ((!$vouchsafe_opt['pubkey'] || !$vouchsafe_opt['privkey']) && !isset($_POST['submit']))
    {
        $path = plugin_basename(__FILE__);
        $top = 0;
        if ($wp_version <= 2.5)
            $top = 12.7;
        else
            $top = 7;

        echo "<div id='vouchsafe-warning' class='updated fade-ff0000'><p><strong>VouchSafe is not active</strong> You must <a href='options-general.php?page=" . $path . "'>enter your VouchSafe keys</a> for it to work</p></div>
                <style type='text/css'>
                #adminmenu { margin-bottom: 5em; }
                </style>";
    }
}
add_action('admin_head', 'vouchsafe_warning');

/* UTILITY METHODS */
/**
 * Creates a hash for the given string using either WP default or MD5 with the user private key
 * @global string $privateKey
 * @param <type> $data String to be hashed
 * @return <type> Hased string
 */
function vouchsafe_wp_hash($data)
{
    global $privateKey;
    if (function_exists('wp_hash'))
        return wp_hash($data);
    else
        return md5($privateKey . $data);
}
?>