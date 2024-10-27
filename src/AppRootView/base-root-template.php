<?php 

namespace Olmec\OlmecNotepress\AppRootView;

use Olmec\OlmecNotepress\Types\User;

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

$userObject = wp_get_current_user();
$userAvatarUrl = get_avatar_url($userObject->ID) !== 0 ? get_avatar_url($userObject->ID) : null;
/* Template Name: base root template */
?>

<!DOCTYPE html>
<html <?php language_attributes() ?> style="margin: 0px">
<head>
    <meta charset="<?php bloginfo('charset') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notepress</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <?php wp_head() ?>
</head>
<body <?php body_class() ?>>

<div id="root"></div>

<script>
    window.nonce = "<?php echo esc_js(wp_create_nonce('wp_rest')); ?>";
    window.api_url = "<?php echo esc_url(OLMEC_NOTEPRESS_API_URL); ?>";
    window.user = <?php echo wp_json_encode(new User((int) $userObject->data->ID, $userObject->data->display_name ?? $userObject->data->first_name ?? 'Unknown user name', esc_url($userAvatarUrl))); ?>;
</script>

<?php wp_footer() ?>
</body>
</html>