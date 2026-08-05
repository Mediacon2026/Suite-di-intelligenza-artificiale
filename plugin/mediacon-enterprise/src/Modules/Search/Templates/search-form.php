<?php
/**
 * Reusable accessible global search form.
 *
 * @package MediaconEnterprise
 *
 * @var array<string,mixed> $config Search configuration.
 * @var string              $search_action Results URL.
 */

defined( 'ABSPATH' ) || exit;
$form_id = wp_unique_id( 'mce-search-' );
?>
<form class="mce-search-form" role="search" method="get" action="<?php echo esc_url( $search_action ); ?>" data-endpoint="<?php echo esc_url( rest_url( 'mediacon-enterprise/v1/search/suggest' ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>" data-min="<?php echo esc_attr( (string) ( $config['min_chars'] ?? 2 ) ); ?>">
	<label for="<?php echo esc_attr( $form_id ); ?>"><?php esc_html_e( 'Cerca nel sito', 'mediacon-enterprise' ); ?></label>
	<div class="mce-search-form__control">
		<input id="<?php echo esc_attr( $form_id ); ?>" type="search" name="q" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( (string) ( $_GET['q'] ?? '' ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public shareable search query. ?>" minlength="<?php echo esc_attr( (string) ( $config['min_chars'] ?? 2 ) ); ?>" maxlength="<?php echo esc_attr( (string) ( $config['max_chars'] ?? 80 ) ); ?>" autocomplete="off" aria-autocomplete="list" aria-expanded="false" aria-controls="<?php echo esc_attr( $form_id . '-list' ); ?>">
		<button type="submit"><?php esc_html_e( 'Cerca', 'mediacon-enterprise' ); ?></button>
	</div>
	<div id="<?php echo esc_attr( $form_id . '-status' ); ?>" class="mce-search-form__status" aria-live="polite"></div>
	<ul id="<?php echo esc_attr( $form_id . '-list' ); ?>" class="mce-search-form__suggestions" role="listbox" hidden></ul>
</form>
