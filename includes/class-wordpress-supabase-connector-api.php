<?php

/**
 * La classe responsable de la connexion à l'API Supabase
 *
 * @link       https://github.com/leyscore/wordpress-supabase-connector
 * @since      1.0.0
 *
 * @package    Wordpress_Supabase_Connector
 * @subpackage Wordpress_Supabase_Connector/includes
 */

/**
 * La classe responsable de la connexion à l'API Supabase
 *
 * Cette classe gère toutes les interactions avec l'API Supabase,
 * y compris l'authentification, les requêtes à la base de données et le stockage.
 *
 * @package    Wordpress_Supabase_Connector
 * @subpackage Wordpress_Supabase_Connector/includes
 * @author     Etienne Baurice <bauriceetienne@gmail.com>
 */
class Wordpress_Supabase_Connector_API {

	/**
	 * L'URL de l'API Supabase
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $api_url    L'URL de l'API Supabase
	 */
	private $api_url;

	/**
	 * La clé API Supabase
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $api_key    La clé API Supabase
	 */
	private $api_key;

	/**
	 * Initialise la classe et définit ses propriétés.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->api_url = get_option( 'wordpress_supabase_connector_api_url' );
		$this->api_key = get_option( 'wordpress_supabase_connector_api_key' );
	}

	/**
	 * Vérifie si l'API est configurée
	 *
	 * @since    1.0.0
	 * @return   boolean    True si l'API est configurée, false sinon
	 */
	public function is_configured() {
		return ! empty( $this->api_url ) && ! empty( $this->api_key );
	}

	/**
	 * Effectue une requête à l'API Supabase
	 *
	 * @since    1.0.0
	 * @param    string    $endpoint    Le point de terminaison de l'API
	 * @param    string    $method      La méthode HTTP (GET, POST, PATCH, DELETE)
	 * @param    array     $data        Les données à envoyer (pour POST, PATCH)
	 * @param    array     $headers     Les en-têtes supplémentaires
	 * @return   array|WP_Error         La réponse de l'API ou une erreur
	 */
	private function request( $endpoint, $method = 'GET', $data = array(), $headers = array() ) {
		if ( ! $this->is_configured() ) {
			return new WP_Error( 'api_not_configured', __( 'L\'API Supabase n\'est pas configurée', 'wordpress-supabase-connector' ) );
		}

		$url = trailingslashit( $this->api_url ) . $endpoint;

		$default_headers = array(
			'apikey'         => $this->api_key,
			'Authorization'  => 'Bearer ' . $this->api_key,
			'Content-Type'   => 'application/json',
			'Prefer'         => 'return=representation',
		);

		$headers = array_merge( $default_headers, $headers );

		$args = array(
			'method'    => $method,
			'headers'   => $headers,
			'timeout'   => 30,
		);

		if ( ! empty( $data ) && in_array( $method, array( 'POST', 'PATCH' ) ) ) {
			$args['body'] = json_encode( $data );
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $response_code < 200 || $response_code >= 300 ) {
			return new WP_Error(
				'api_error',
				sprinf( __( 'Erreur API Supabase: %s', 'wordpress-supabase-connector' ), $response_body['message'] ?? $response_code ),
				$response_body
			);
		}

		return $response_body;
	}

	/**
	 * Récupère des données d'une table Supabase
	 *
	 * @since    1.0.0
	 * @param    string    $table       Le nom de la table
	 * @param    array     $options     Les options de requête (select, filter)
	 * @return   array|WP_Error         Les données ou une erreur
	 */
	public function get_data( $table, $options = array() ) {
		$endpoint = 'rest/v1/' . $table;
		$query_params = array();

		// Colonnes à sélectionner
		if ( ! empty( $options['select'] ) ) {
			$query_params['select'] = $options['select'];
		}

		// Filtres supplémentaires
		if ( ! empty( $options['filter'] ) ) {
			foreach ( $options['filter'] as $key => $value ) {
				$query_params[ $key ] = $value;
			}
		}

		// Construction de l'URL avec les paramètres
		if ( ! empty( $query_params ) ) {
			$endpoint .= '?' . http_build_query( $query_params );
		}

		return $this->request( $endpoint, 'GET' );
	}

	/**
	 * Insère des données dans une table Supabase
	 *
	 * @since    1.0.0
	 * @param    string    $table    Le nom de la table
	 * @param    array     $data     Les données à insérer
	 * @return   array|WP_Error      La réponse ou une erreur
	 */
	public function insert_data( $table, $data ) {
		$endpoint = 'rest/v1/' . $table;
		return $this->request( $endpoint, 'POST', $data );
	}

	/**
	 * Met à jour des données dans une table Supabase
	 *
	 * @since    1.0.0
	 * @param    string    $table       Le nom de la table
	 * @param    array     $data        Les données à mettre à jour
	 * @param    array     $conditions  Les conditions pour la mise à jour
	 * @return   array|WP_Error         La réponse ou une erreur
	 */
	public function update_data( $table, $data, $conditions ) {
		$endpoint = 'rest/v1/' . $table;
		$query_params = array();

		// Construction des conditions
		foreach ( $conditions as $key => $value ) {
			$query_params[ $key ] = $value;
		}

		// Construction de l'URL avec les paramètres
		if ( ! empty( $query_params ) ) {
			$endpoint .= '?' . http_build_query( $query_params );
		}

		return $this->request( $endpoint, 'PATCH', $data );
	}

	/**
	 * Supprime des données d'une table Supabase
	 *
	 * @since    1.0.0
	 * @param    string    $table       Le nom de la table
	 * @param    array     $conditions  Les conditions pour la suppression
	 * @return   array|WP_Error         La réponse ou une erreur
	 */
	public function delete_data( $table, $conditions ) {
		$endpoint = 'rest/v1/' . $table;
		$query_params = array();

		// Construction des conditions
		foreach ( $conditions as $key => $value ) {
			$query_params[ $key ] = $value;
		}

		// Construction de l'URL avec les paramètres
		if ( ! empty( $query_params ) ) {
			$endpoint .= '?' . http_build_query( $query_params );
		}

		return $this->request( $endpoint, 'DELETE' );
	}

}
