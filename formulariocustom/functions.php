<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_VERSION', '3.1.1' );

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

if ( ! function_exists( 'hello_elementor_setup' ) ) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_elementor_setup() {
		if ( is_admin() ) {
			hello_maybe_update_theme_version_in_db();
		}

		if ( apply_filters( 'hello_elementor_register_menus', true ) ) {
			register_nav_menus( [ 'menu-1' => esc_html__( 'Header', 'hello-elementor' ) ] );
			register_nav_menus( [ 'menu-2' => esc_html__( 'Footer', 'hello-elementor' ) ] );
		}

		if ( apply_filters( 'hello_elementor_post_type_support', true ) ) {
			add_post_type_support( 'page', 'excerpt' );
		}

		if ( apply_filters( 'hello_elementor_add_theme_support', true ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);

			add_editor_style( 'classic-editor.css' );
			add_theme_support( 'align-wide' );

			if ( apply_filters( 'hello_elementor_add_woocommerce_support', true ) ) {
				add_theme_support( 'woocommerce' );
				add_theme_support( 'wc-product-gallery-zoom' );
				add_theme_support( 'wc-product-gallery-lightbox' );
				add_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
add_action( 'after_setup_theme', 'hello_elementor_setup' );

function hello_maybe_update_theme_version_in_db() {
    $theme_version_option_name = 'hello_theme_version';
    $hello_theme_db_version = get_option( $theme_version_option_name );

    if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
        update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
    }
}

if ( ! function_exists( 'hello_elementor_display_header_footer' ) ) {
	function hello_elementor_display_header_footer() {
        return apply_filters( 'hello_elementor_header_footer', true );
    }
}

if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
	function hello_elementor_scripts_styles() {
        wp_enqueue_script('jquery'); // Asegúrate de que jQuery esté encolado

        $min_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        if ( apply_filters( 'hello_elementor_enqueue_style', true ) ) {
            wp_enqueue_style(
                'hello-elementor',
                get_template_directory_uri() . '/style' . $min_suffix . '.css',
                [],
                HELLO_ELEMENTOR_VERSION
            );
        }

        if ( apply_filters( 'hello_elementor_enqueue_theme_style', true ) ) {
            wp_enqueue_style(
                'hello-elementor-theme-style',
                get_template_directory_uri() . '/theme' . $min_suffix . '.css',
                [],
                HELLO_ELEMENTOR_VERSION
            );
        }

        if ( hello_elementor_display_header_footer() ) {
            wp_enqueue_style(
                'hello-elementor-header-footer',
                get_template_directory_uri() . '/header-footer' . $min_suffix . '.css',
                [],
                HELLO_ELEMENTOR_VERSION
            );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_scripts_styles' );

if ( ! function_exists( 'hello_elementor_register_elementor_locations' ) ) {
	function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
        if ( apply_filters( 'hello_elementor_register_elementor_locations', true ) ) {
            $elementor_theme_manager->register_all_core_location();
        }
    }
}
add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

if ( ! function_exists( 'hello_elementor_content_width' ) ) {
	function hello_elementor_content_width() {
        $GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
    }
}
add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

if ( ! function_exists( 'hello_elementor_add_description_meta_tag' ) ) {
	function hello_elementor_add_description_meta_tag() {
        if ( ! apply_filters( 'hello_elementor_description_meta_tag', true ) || ! is_singular() ) {
            return;
        }

        $post = get_queried_object();
        if ( empty( $post->post_excerpt ) ) {
            return;
        }

        echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $post->post_excerpt ) ) . '">' . "\n";
    }
}
add_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );

// Admin notice
if ( is_admin() ) {
	require get_template_directory() . '/includes/admin-functions.php';
}

// Settings page
require get_template_directory() . '/includes/settings-functions.php';

// Header & footer styling option, inside Elementor
require get_template_directory() . '/includes/elementor-functions.php';

if ( ! function_exists( 'hello_elementor_customizer' ) ) {
	function hello_elementor_customizer() {
        if ( ! is_customize_preview() && hello_elementor_display_header_footer() ) {
            require get_template_directory() . '/includes/customizer-functions.php';
        }
    }
}
add_action( 'init', 'hello_elementor_customizer' );

if ( ! function_exists( 'hello_elementor_check_hide_title' ) ) {
	function hello_elementor_check_hide_title( $val ) {
        if ( defined( 'ELEMENTOR_VERSION' ) && Elementor\Plugin::instance()->documents->get(get_the_ID())->get_settings('hide_title') === "yes" ){
            $val = false;
        }
        return $val;
    }
}
add_filter('hello_elementor_page_title', 'hello_elementor_check_hide_title');

/**
 * BC:
 * In v2.7.0 the theme removed the `hello_elementor_body_open()` from `header.php` replacing it with `wp_body_open()`.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if ( ! function_exists('hello_elementor_body_open') ){
	function hello_elementor_body_open(){
    	wp_body_open();
    }
}

// Agregar CSS personalizado para ocultar el div de WhatsApp
function custom_hide_whatsapp_div() {
    ?>
    <style type="text/css">
        .qlwapp.qlwapp-free.qlwapp-bubble.qlwapp-bottom-right.qlwapp-all.qlwapp-rounded.qlwapp-js-ready.desktop { 
            display: none !important; /* Asegúrate de que se oculte */
        }
    </style>
    <?php
}
add_action('wp_head', 'custom_hide_whatsapp_div');

// Crear el endpoint
add_action('rest_api_init', function () {
    register_rest_route('agendarcita/v1', '/enviar', array(
        'methods' => 'POST',
        'callback' => 'procesar_cita_agenda',
        'permission_callback' => '__return_true'
    ));
});

function procesar_cita_agenda($request) {
    $params = $request->get_params();

    try {
        // Crear el formato que Flamingo espera
        $flamingo_args = array(
            'subject' => 'Nueva cita de vacunación - ' . $params['nombre'],
            'from' => $params['email'],
            'from_name' => $params['nombre'] . ' ' . $params['apellido'],
            'from_email' => $params['email'],
            'fields' => array(
                'nombre' => $params['nombre'],
                'apellido' => $params['apellido'],
                'email' => $params['email'],
                'telefono' => $params['telefono'],
                'servicios' => $params['servicios'],
                'sucursales' => $params['sucursales'],
                'vacunas' => $params['vacunas'],
                'fecha' => $params['fecha'],
                'hora' => $params['hora'],
                'observacion' => $params['observacion'] ?? ''
            )
        );

        // Guardar en Flamingo
        $contact = Flamingo_Contact::add($flamingo_args);
        $inbound = Flamingo_Inbound_Message::add($flamingo_args);

        // Enviar email
        $to = 'COLOCAR EL CORREO';
        $subject = 'Nueva cita de vacunación - ' . $params['nombre'];
        $mensaje = "Se ha recibido una nueva solicitud de cita:\n\n";
        $mensaje .= "Información del paciente:\n";
        $mensaje .= "- Nombre: " . $params['nombre'] . "\n";
        $mensaje .= "- Apellido: " . $params['apellido'] . "\n";
        $mensaje .= "- Email: " . $params['email'] . "\n";
        $mensaje .= "- Teléfono: " . $params['telefono'] . "\n\n";
        $mensaje .= "Detalles de la cita:\n";
        $mensaje .= "- Servicio: " . $params['servicios'] . "\n";
        $mensaje .= "- Sucursal: " . $params['sucursales'] . "\n";
        $mensaje .= "- Vacuna: " . $params['vacunas'] . "\n";
        $mensaje .= "- Fecha: " . $params['fecha'] . "\n";
        $mensaje .= "- Hora: " . $params['hora'] . "\n\n";
        $mensaje .= "Observaciones:\n" . ($params['observacion'] ?? '');

        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: Amadita Vacunas <COLOCAR EL CORREO>'
        );

        $enviado = wp_mail($to, $subject, $mensaje, $headers);

        if ($inbound && $enviado) {
            return new WP_REST_Response(array(
                'status' => 'success',
                'message' => 'Cita agendada exitosamente'
            ), 200);
        } else {
            throw new Exception('Error al procesar la solicitud');
        }
    } catch (Exception $e) {
        return new WP_REST_Response(array(
            'status' => 'error',
            'message' => $e->getMessage()
        ), 500);
    }
}

?>