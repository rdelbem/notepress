<?php 

// Prevent direct file access
if (!defined('ABSPATH') || !is_user_logged_in()) {
    exit;
}

/* Template Name: base root template */
?>

<!DOCTYPE html>
<html <?php language_attributes() ?>>
<head>
    <meta charset="<?php bloginfo('charset') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head() ?>
</head>
<body <?php body_class() ?>>

<div id="root"></div>

<script>
    window.nonce = <?php echo '"' . wp_create_nonce('rest') . '"' ?>
</script>

<?php wp_footer() ?>
</body>
</html>