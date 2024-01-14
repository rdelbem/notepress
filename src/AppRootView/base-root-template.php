<?php 

// Prevent direct file access
if (!defined('ABSPATH') || !is_user_logged_in()) {
    exit;
}

/* Template Name: base root template */ 
?>

<script>
    window.nonce = <?php echo '"' . wp_create_nonce('rest') . '"' ?>
</script>