// Registrar el endpoint REST API para el formulario
add_action('rest_api_init', function () {
    register_rest_route('lupaform/v1', '/enviar', array(
        'methods' => 'POST',
        'callback' => 'procesar_formulario_lupa',
        'permission_callback' => '__return_true'
    ));
});

function procesar_formulario_lupa($request) {
    $params = $request->get_params();

    try {
        // Validar los campos requeridos
        if (empty($params['nombre']) || empty($params['correo']) || empty($params['mensaje'])) {
            throw new Exception('Todos los campos son requeridos');
        }

        // Validar el formato del correo
        if (!is_email($params['correo'])) {
            throw new Exception('El correo electrónico no es válido');
        }

        // Crear el formato para Flamingo (si está instalado)
        $flamingo_args = array(
            'subject' => 'Nuevo mensaje de contacto - ' . $params['nombre'],
            'from' => $params['correo'],
            'from_name' => $params['nombre'],
            'from_email' => $params['correo'],
            'fields' => array(
                'nombre' => $params['nombre'],
                'correo' => $params['correo'],
                'mensaje' => $params['mensaje']
            )
        );

        // Guardar en Flamingo si está disponible
        if (class_exists('Flamingo_Contact')) {
            $contact = Flamingo_Contact::add($flamingo_args);
            $inbound = Flamingo_Inbound_Message::add($flamingo_args);
        }

        // Enviar email
        $to = get_option('admin_email'); // Correo del administrador de WordPress
        $subject = 'Nuevo mensaje de contacto - ' . $params['nombre'];
        $mensaje = "Se ha recibido un nuevo mensaje de contacto:\n\n";
        $mensaje .= "Nombre: " . $params['nombre'] . "\n";
        $mensaje .= "Correo: " . $params['correo'] . "\n";
        $mensaje .= "Mensaje:\n" . $params['mensaje'];

        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );

        $enviado = wp_mail($to, $subject, $mensaje, $headers);

        if ($enviado) {
            return new WP_REST_Response(array(
                'status' => 'success',
                'message' => 'Mensaje enviado exitosamente'
            ), 200);
        } else {
            throw new Exception('Error al enviar el mensaje');
        }
    } catch (Exception $e) {
        return new WP_REST_Response(array(
            'status' => 'error',
            'message' => $e->getMessage()
        ), 500);
    }
} 