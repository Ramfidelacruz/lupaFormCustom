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
    
    // Log inicial para verificar los parámetros recibidos
    error_log('=== INICIO DEL PROCESO DE FORMULARIO ===');
    error_log('Parámetros recibidos: ' . print_r($params, true));

    try {
        // Validar los campos requeridos
        if (empty($params['nombre']) || empty($params['correo']) || empty($params['mensaje'])) {
            error_log('Error: Campos requeridos faltantes');
            throw new Exception('Todos los campos son requeridos');
        }

        // Validar el formato del correo
        if (!is_email($params['correo'])) {
            error_log('Error: Correo electrónico inválido - ' . $params['correo']);
            throw new Exception('El correo electrónico no es válido');
        }

        // Crear el formato para Flamingo
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

        error_log('Argumentos preparados para Flamingo: ' . print_r($flamingo_args, true));

        // Guardar en Flamingo si está disponible
        if (class_exists('Flamingo_Contact')) {
            try {
                error_log('Intentando guardar en Flamingo Contact...');
                $contact = Flamingo_Contact::add($flamingo_args);
                error_log('Flamingo Contact guardado exitosamente');
                
                error_log('Intentando guardar en Flamingo Inbound Message...');
                $inbound = Flamingo_Inbound_Message::add($flamingo_args);
                error_log('Flamingo Inbound Message guardado exitosamente');
            } catch (Exception $e) {
                error_log('Error en Flamingo: ' . $e->getMessage());
                // No lanzamos la excepción aquí para continuar con el proceso
            }
        } else {
            error_log('Flamingo no está disponible en el sistema');
        }

        // Preparar el correo
        $to = 'info@lupard.com';
        $subject = 'Nuevo mensaje de contacto - ' . $params['nombre'];
        $mensaje = "Se ha recibido un nuevo mensaje de contacto:\n\n";
        $mensaje .= "Nombre: " . $params['nombre'] . "\n";
        $mensaje .= "Correo: " . $params['correo'] . "\n";
        $mensaje .= "Mensaje:\n" . $params['mensaje'];

        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );

        error_log('Intentando enviar correo a: ' . $to);
        error_log('Headers del correo: ' . print_r($headers, true));

        // Intentar enviar el correo
        $enviado = wp_mail($to, $subject, $mensaje, $headers);
        error_log('Resultado del envío de correo: ' . ($enviado ? 'Exitoso' : 'Fallido'));

        if ($enviado) {
            error_log('=== PROCESO COMPLETADO EXITOSAMENTE ===');
            return new WP_REST_Response(array(
                'status' => 'success',
                'message' => 'Mensaje enviado exitosamente'
            ), 200);
        } else {
            error_log('Error: Falló el envío del correo');
            throw new Exception('Error al enviar el mensaje');
        }
    } catch (Exception $e) {
        error_log('Error en procesar_formulario_lupa: ' . $e->getMessage());
        error_log('=== PROCESO TERMINADO CON ERROR ===');
        return new WP_REST_Response(array(
            'status' => 'error',
            'message' => $e->getMessage()
        ), 500);
    }
} 