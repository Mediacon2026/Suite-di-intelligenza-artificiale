<?php
/**
 * Preventivo wizard.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
$config        = is_array( $config ?? null ) ? $config : array();
$public_config = array(
	'expenses' => $config['expenses'] ?? array(),
	'texts'    => $config['texts'] ?? array(),
	'print'    => $config['print'] ?? array(),
);
?>
<main class="mce-preventivo" data-endpoint="<?php echo esc_url( rest_url( 'mediacon-enterprise/v1/preventivo/calculate' ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>" data-config="<?php echo esc_attr( wp_json_encode( $public_config ) ); ?>">
	<header class="mce-preventivo__hero"><p class="mce-preventivo__eyebrow"><?php esc_html_e( 'Mediacon', 'mediacon-enterprise' ); ?></p><h1><?php esc_html_e( 'Calcola il preventivo di mediazione', 'mediacon-enterprise' ); ?></h1><p><?php echo esc_html( (string) ( $config['texts']['explanation'] ?? '' ) ); ?></p></header>
	<div class="mce-preventivo__progress" aria-label="<?php esc_attr_e( 'Avanzamento', 'mediacon-enterprise' ); ?>"><span></span></div>
	<form class="mce-preventivo__wizard" novalidate>
		<section data-step="1"><h2>1. <?php esc_html_e( 'Tipo mediazione', 'mediacon-enterprise' ); ?></h2><label><input type="radio" name="type" value="mandatory" required> <?php esc_html_e( 'Obbligatoria', 'mediacon-enterprise' ); ?></label><label><input type="radio" name="type" value="court_ordered"> <?php esc_html_e( 'Demandata dal giudice', 'mediacon-enterprise' ); ?></label><label><input type="radio" name="type" value="voluntary"> <?php esc_html_e( 'Volontaria', 'mediacon-enterprise' ); ?></label></section>
		<section data-step="2" hidden><h2>2. <?php esc_html_e( 'Valore della controversia', 'mediacon-enterprise' ); ?></h2><label><?php esc_html_e( 'Valore in euro', 'mediacon-enterprise' ); ?><input type="number" name="value" min="0.01" step="0.01" required></label></section>
		<section data-step="3" hidden><h2>3. <?php esc_html_e( 'Parti istanti', 'mediacon-enterprise' ); ?></h2><div data-party-list="claimant"></div><button type="button" data-add-party="claimant"><?php esc_html_e( 'Aggiungi parte istante', 'mediacon-enterprise' ); ?></button></section>
		<section data-step="4" hidden><h2>4. <?php esc_html_e( 'Parti invitate', 'mediacon-enterprise' ); ?></h2><div data-party-list="invited"></div><button type="button" data-add-party="invited"><?php esc_html_e( 'Aggiungi parte invitata', 'mediacon-enterprise' ); ?></button></section>
		<section data-step="5" hidden><h2>5. <?php esc_html_e( 'Centri di interesse', 'mediacon-enterprise' ); ?></h2><p><?php esc_html_e( 'Indica il numero di centri per ciascuna parte.', 'mediacon-enterprise' ); ?></p><div data-centers></div></section>
		<section data-step="6" hidden><h2>6. <?php esc_html_e( 'Scenario', 'mediacon-enterprise' ); ?></h2><label><?php esc_html_e( 'Esito o fase', 'mediacon-enterprise' ); ?><select name="scenario" required><option value="absence">Assenza della parte invitata</option><option value="first_no_agreement">Mancato accordo al primo incontro</option><option value="first_agreement">Accordo al primo incontro</option><option value="continuation">Prosecuzione oltre il primo incontro</option><option value="multiple_no_agreement">Mancato accordo dopo più incontri</option><option value="multiple_agreement">Accordo dopo più incontri</option><option value="mediator_proposal">Proposta del Mediatore</option></select></label><label><?php esc_html_e( 'Eventuale aumento ulteriore (%)', 'mediacon-enterprise' ); ?><input type="number" name="increase" min="0" max="300" step="0.01" value="0"></label></section>
		<section data-step="7" hidden><h2>7. <?php esc_html_e( 'Spese vive per parte', 'mediacon-enterprise' ); ?></h2><p><?php esc_html_e( 'Raccomandate, firma digitale, copie ulteriori o altre spese documentate.', 'mediacon-enterprise' ); ?></p><div data-expenses></div></section>
		<section data-step="8" hidden><h2>8. <?php esc_html_e( 'Importi già versati', 'mediacon-enterprise' ); ?></h2><div data-paid></div></section>
		<section data-step="9" hidden><h2>9. <?php esc_html_e( 'Riepilogo dati', 'mediacon-enterprise' ); ?></h2><div data-review></div></section>
		<section data-step="10" hidden><h2>10. <?php esc_html_e( 'Generazione preventivo', 'mediacon-enterprise' ); ?></h2><p><?php esc_html_e( 'Ogni parte riceverà un calcolo autonomo. Il totale generale sarà mostrato solo come somma distinta.', 'mediacon-enterprise' ); ?></p><button type="submit" class="mce-preventivo__primary"><?php esc_html_e( 'Genera preventivo', 'mediacon-enterprise' ); ?></button></section>
		<p class="mce-preventivo__error" role="alert" hidden></p>
		<nav><button type="button" data-back disabled><?php esc_html_e( 'Indietro', 'mediacon-enterprise' ); ?></button><button type="button" data-next><?php esc_html_e( 'Avanti', 'mediacon-enterprise' ); ?></button><button type="button" data-reset><?php esc_html_e( 'Azzera', 'mediacon-enterprise' ); ?></button></nav>
	</form>
	<section class="mce-preventivo__result" aria-live="polite" hidden></section>
	<p class="mce-preventivo__disclaimer"><?php echo esc_html( (string) ( $config['texts']['disclaimer'] ?? '' ) ); ?></p>
</main>
