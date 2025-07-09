<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/leyscore/wordpress-supabase-connector
 * @since      1.0.0
 *
 * @package    Wordpress_Supabase_Connector
 * @subpackage Wordpress_Supabase_Connector/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <div class="card">
        <h2><?php _e( 'À propos de Supabase', 'wordpress-supabase-connector' ); ?></h2>
        <p>
            <?php _e( 'Supabase est une alternative open source à Firebase. Il fournit tous les services backend dont vous avez besoin pour créer un produit, notamment une base de données PostgreSQL, une authentification, des API instantanées, une gestion de stockage en temps réel et des fonctions.', 'wordpress-supabase-connector' ); ?>
        </p>
        <p>
            <a href="https://supabase.com" target="_blank" class="button"><?php _e( 'Visiter Supabase', 'wordpress-supabase-connector' ); ?></a>
        </p>
    </div>

    <div class="card">
        <h2><?php _e( 'Paramètres de l\'API', 'wordpress-supabase-connector' ); ?></h2>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'wordpress_supabase_connector_settings' );
            do_settings_sections( 'wordpress-supabase-connector' );
            submit_button();
            ?>
        </form>
    </div>

    <div class="card">
        <h2><?php _e( 'Statut de connexion', 'wordpress-supabase-connector' ); ?></h2>
        <?php
        // Vérifier si les paramètres sont configurés
        $api_url = get_option( 'wordpress_supabase_connector_api_url' );
        $api_key = get_option( 'wordpress_supabase_connector_api_key' );

        if ( empty( $api_url ) || empty( $api_key ) ) {
            echo '<div class="notice notice-warning inline"><p>' . __( 'Veuillez configurer l\'URL de l\'API et la clé API pour tester la connexion.', 'wordpress-supabase-connector' ) . '</p></div>';
        } else {
            // Tester la connexion
            $api = new Wordpress_Supabase_Connector_API();
            $connection_test = $api->test_connection();

            if ( is_wp_error( $connection_test ) ) {
                echo '<div class="notice notice-error inline"><p>' . __( 'Erreur de connexion: ', 'wordpress-supabase-connector' ) . esc_html( $connection_test->get_error_message() ) . '</p></div>';
            } else {
                echo '<div class="notice notice-success inline"><p>' . __( 'Connexion réussie à Supabase!', 'wordpress-supabase-connector' ) . '</p></div>';
                
                // Afficher des informations sur le projet
                if ( isset( $connection_test['project'] ) ) {
                    echo '<p><strong>' . __( 'Projet: ', 'wordpress-supabase-connector' ) . '</strong>' . esc_html( $connection_test['project']['name'] ) . '</p>';
                }
            }
        }
        ?>
    </div>

    <div class="card">
        <h2><?php _e( 'Documentation', 'wordpress-supabase-connector' ); ?></h2>
        <p>
            <?php _e( 'Pour plus d\'informations sur l\'utilisation de ce plugin, veuillez consulter la documentation.', 'wordpress-supabase-connector' ); ?>
        </p>
        <p>
            <a href="https://github.com/leyscore/wordpress-supabase-connector" target="_blank" class="button"><?php _e( 'Voir la documentation', 'wordpress-supabase-connector' ); ?></a>
        </p>
    </div>
</div>
