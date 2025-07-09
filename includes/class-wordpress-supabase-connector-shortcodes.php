<?php

/**
 * La classe responsable de la gestion des shortcodes
 *
 * @link       https://github.com/leyscore/wordpress-supabase-connector
 * @since      1.0.0
 *
 * @package    Wordpress_Supabase_Connector
 * @subpackage Wordpress_Supabase_Connector/includes
 */

/**
 * La classe responsable de la gestion des shortcodes
 *
 * Cette classe définit tous les shortcodes utilisés par le plugin
 * pour afficher les données de Supabase dans WordPress.
 *
 * @package    Wordpress_Supabase_Connector
 * @subpackage Wordpress_Supabase_Connector/includes
 * @author     Etienne Baurice <bauriceetienne@gmail.com>
 */
class Wordpress_Supabase_Connector_Shortcodes {

	/**
	 * L'instance de la classe API
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Wordpress_Supabase_Connector_API    $api    L'instance de la classe API
	 */
	private $api;

	/**
	 * Initialise la classe et définit ses propriétés.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->api = new Wordpress_Supabase_Connector_API();
	}

	/**
	 * Enregistre tous les shortcodes
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'supabase_data', array( $this, 'supabase_data_shortcode' ) );
		add_shortcode( 'supabase_profile', array( $this, 'supabase_profile_shortcode' ) );
	}

	/**
	 * Shortcode pour afficher des données d'une table Supabase
	 *
	 * @since    1.0.0
	 * @param    array     $atts    Les attributs du shortcode
	 * @return   string             Le contenu HTML généré
	 */
	public function supabase_data_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'table'    => '',
				'select'   => '*',
				'limit'    => 10,
				'order'    => '',
				'template' => 'table',
				'class'    => 'supabase-data',
			),
			$atts,
			'supabase_data'
		);

		if ( empty( $atts['table'] ) ) {
			return '<div class="supabase-error">' . __( 'Erreur: Le paramètre "table" est requis.', 'wordpress-supabase-connector' ) . '</div>';
		}

		if ( ! $this->api->is_configured() ) {
			return '<div class="supabase-error">' . __( 'Erreur: L\'API Supabase n\'est pas configurée.', 'wordpress-supabase-connector' ) . '</div>';
		}

		$options = array(
			'select' => $atts['select'],
			'filter' => array(
				'limit' => intval( $atts['limit'] ),
			),
		);

		if ( ! empty( $atts['order'] ) ) {
			$options['filter']['order'] = $atts['order'];
		}

		$data = $this->api->get_data( $atts['table'], $options );

		if ( is_wp_error( $data ) ) {
			return '<div class="supabase-error">' . $data->get_error_message() . '</div>';
		}

		if ( empty( $data ) ) {
			return '<div class="supabase-notice">' . __( 'Aucune donnée trouvée.', 'wordpress-supabase-connector' ) . '</div>';
		}

		// Sélectionner le template approprié
		switch ( $atts['template'] ) {
			case 'list':
				return $this->render_list_template( $data, $atts );
			case 'cards':
				return $this->render_cards_template( $data, $atts );
			case 'table':
			default:
				return $this->render_table_template( $data, $atts );
		}
	}

	/**
	 * Shortcode pour afficher le profil d'un utilisateur Supabase
	 *
	 * @since    1.0.0
	 * @param    array     $atts    Les attributs du shortcode
	 * @return   string             Le contenu HTML généré
	 */
	public function supabase_profile_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'user_id'  => '',
				'template' => 'card',
				'class'    => 'supabase-profile',
			),
			$atts,
			'supabase_profile'
		);

		if ( empty( $atts['user_id'] ) ) {
			return '<div class="supabase-error">' . __( 'Erreur: Le paramètre "user_id" est requis.', 'wordpress-supabase-connector' ) . '</div>';
		}

		if ( ! $this->api->is_configured() ) {
			return '<div class="supabase-error">' . __( 'Erreur: L\'API Supabase n\'est pas configurée.', 'wordpress-supabase-connector' ) . '</div>';
		}

		$options = array(
			'select' => '*',
			'filter' => array(
				'id' => 'eq.' . $atts['user_id'],
				'limit' => 1,
			),
		);

		$data = $this->api->get_data( 'profiles', $options );

		if ( is_wp_error( $data ) ) {
			return '<div class="supabase-error">' . $data->get_error_message() . '</div>';
		}

		if ( empty( $data ) ) {
			return '<div class="supabase-notice">' . __( 'Profil non trouvé.', 'wordpress-supabase-connector' ) . '</div>';
		}

		$profile = $data[0];

		// Sélectionner le template approprié
		switch ( $atts['template'] ) {
			case 'detail':
				return $this->render_profile_detail_template( $profile, $atts );
			case 'card':
			default:
				return $this->render_profile_card_template( $profile, $atts );
		}
	}

	/**
	 * Génère le HTML pour le template de table
	 *
	 * @since    1.0.0
	 * @param    array     $data    Les données à afficher
	 * @param    array     $atts    Les attributs du shortcode
	 * @return   string             Le contenu HTML généré
	 */
	private function render_table_template( $data, $atts ) {
		if ( empty( $data ) || ! is_array( $data ) ) {
			return '';
		}

		$html = '<div class="supabase-data-container">';
		$html .= '<table class="' . esc_attr( $atts['class'] ) . '">';

		// En-têtes de table
		$html .= '<thead><tr>';
		foreach ( array_keys( $data[0] ) as $key ) {
			$html .= '<th>' . esc_html( $key ) . '</th>';
		}
		$html .= '</tr></thead>';

		// Corps de la table
		$html .= '<tbody>';
		foreach ( $data as $row ) {
			$html .= '<tr>';
			foreach ( $row as $value ) {
				$html .= '<td>' . $this->format_value( $value ) . '</td>';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody>';

		$html .= '</table>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Génère le HTML pour le template de liste
	 *
	 * @since    1.0.0
	 * @param    array     $data    Les données à afficher
	 * @param    array     $atts    Les attributs du shortcode
	 * @return   string             Le contenu HTML généré
	 */
	private function render_list_template( $data, $atts ) {
		if ( empty( $data ) || ! is_array( $data ) ) {
			return '';
		}

		$html = '<ul class="supabase-data-list ' . esc_attr( $atts['class'] ) . '">';

		foreach ( $data as $item ) {
			$html .= '<li class="supabase-data-item">';
			foreach ( $item as $key => $value ) {
				$html .= '<div class="supabase-data-field">';
				$html .= '<span class="supabase-data-label">' . esc_html( $key ) . ':</span> ';
				$html .= '<span class="supabase-data-value">' . $this->format_value( $value ) . '</span>';
				$html .= '</div>';
			}
			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Génère le HTML pour le template de cartes
	 *
	 * @since    1.0.0
	 * @param    array     $data    Les données à afficher
	 * @param    array     $atts    Les attributs du shortcode
	 * @return   string             Le contenu HTML généré
	 */
	private function render_cards_template( $data, $atts ) {
		if ( empty( $data ) || ! is_array( $data ) ) {
			return '';
		}

		$html = '<div class="supabase-data-grid ' . esc_attr( $atts['class'] ) . '">';

		foreach ( $data as $item ) {
			$html .= '<div class="supabase-data-card">';
			foreach ( $item as $key => $value ) {
				$html .= '<div class="supabase-data-field">';
				$html .= '<div class="supabase-data-label">' . esc_html( $key ) . '</div>';
				$html .= '<div class="supabase-data-value">' . $this->format_value( $value ) . '</div>';
				$html .= '</div>';
			}
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Génère le HTML pour le template de carte de profil
	 *
	 * @since    1.0.0
	 * @param    array     $profile    Les données du profil
	 * @param    array     $atts       Les attributs du shortcode
	 * @return   string                Le contenu HTML généré
	 */
	private function render_profile_card_template( $profile, $atts ) {
		if ( empty( $profile ) || ! is_array( $profile ) ) {
			return '';
		}

		$html = '<div class="supabase-profile-container ' . esc_attr( $atts['class'] ) . '">';
		$html .= '<div class="supabase-profile-card">';

		// Avatar
		$avatar_url = ! empty( $profile['avatar_url'] ) ? $profile['avatar_url'] : 'https://www.gravatar.com/avatar/?d=mp&f=y';
		$html .= '<div class="supabase-profile-avatar">';
		$html .= '<img src="' . esc_url( $avatar_url ) . '" alt="' . esc_attr( $profile['username'] ?? __( 'Utilisateur', 'wordpress-supabase-connector' ) ) . '" />';
		$html .= '</div>';

		// Informations
		$html .= '<div class="supabase-profile-info">';
		
		// Nom d'utilisateur
		if ( ! empty( $profile['username'] ) ) {
			$html .= '<h3 class="supabase-profile-username">' . esc_html( $profile['username'] ) . '</h3>';
		}

		// Nom complet
		if ( ! empty( $profile['full_name'] ) ) {
			$html .= '<div class="supabase-profile-fullname">' . esc_html( $profile['full_name'] ) . '</div>';
		}

		// Site web
		if ( ! empty( $profile['website'] ) ) {
			$html .= '<div class="supabase-profile-website">';
			$html .= '<a href="' . esc_url( $profile['website'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $profile['website'] ) . '</a>';
			$html .= '</div>';
		}

		$html .= '</div>'; // .supabase-profile-info
		$html .= '</div>'; // .supabase-profile-card
		$html .= '</div>'; // .supabase-profile-container

		return $html;
	}

	/**
	 * Génère le HTML pour le template de profil détaillé
	 *
	 * @since    1.0.0
	 * @param    array     $profile    Les données du profil
	 * @param    array     $atts       Les attributs du shortcode
	 * @return   string                Le contenu HTML généré
	 */
	private function render_profile_detail_template( $profile, $atts ) {
		if ( empty( $profile ) || ! is_array( $profile ) ) {
			return '';
		}

		$html = '<div class="supabase-profile-container ' . esc_attr( $atts['class'] ) . '">';
		$html .= '<div class="supabase-profile-detail">';

		// Avatar
		$avatar_url = ! empty( $profile['avatar_url'] ) ? $profile['avatar_url'] : 'https://www.gravatar.com/avatar/?d=mp&f=y';
		$html .= '<div class="supabase-profile-avatar">';
		$html .= '<img src="' . esc_url( $avatar_url ) . '" alt="' . esc_attr( $profile['username'] ?? __( 'Utilisateur', 'wordpress-supabase-connector' ) ) . '" />';
		$html .= '</div>';

		// Informations détaillées
		foreach ( $profile as $key => $value ) {
			// Ignorer l'URL de l'avatar car elle est déjà affichée
			if ( $key === 'avatar_url' ) {
				continue;
			}

			$html .= '<div class="supabase-profile-field">';
			$html .= '<span class="supabase-profile-label">' . esc_html( $key ) . ':</span> ';
			
			if ( $key === 'website' && ! empty( $value ) ) {
				$html .= '<a href="' . esc_url( $value ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $value ) . '</a>';
			} else {
				$html .= $this->format_value( $value );
			}
			
			$html .= '</div>';
		}

		$html .= '</div>'; // .supabase-profile-detail
		$html .= '</div>'; // .supabase-profile-container

		return $html;
	}

	/**
	 * Formate une valeur pour l'affichage
	 *
	 * @since    1.0.0
	 * @param    mixed     $value    La valeur à formater
	 * @return   string              La valeur formatée
	 */
	private function format_value( $value ) {
		if ( is_null( $value ) ) {
			return '<em>' . __( 'Null', 'wordpress-supabase-connector' ) . '</em>';
		} elseif ( is_array( $value ) || is_object( $value ) ) {
			return '<pre>' . esc_html( json_encode( $value, JSON_PRETTY_PRINT ) ) . '</pre>';
		} elseif ( is_bool( $value ) ) {
			return $value ? __( 'Vrai', 'wordpress-supabase-connector' ) : __( 'Faux', 'wordpress-supabase-connector' );
		} else {
			return esc_html( $value );
		}
	}

}
