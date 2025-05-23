
<script>

document.addEventListener('DOMContentLoaded', function() {
    const nextButton = document.getElementById('next-button');
    const prevButton = document.getElementById('prev-button');
    
    if(nextButton) {
        nextButton.addEventListener('click', function() {
            console.log('Botón siguiente clickeado');
            nextStep();
        });
    }
    
    if(prevButton) {
        prevButton.addEventListener('click', function() {
            console.log('Botón atrás clickeado');
            prevStep();
        });
    }

    // Evento para la fecha
    const fechaInput = document.getElementById('fecha');
    if (fechaInput) {
        // Establecer fecha mínima como hoy
        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const yyyy = today.getFullYear();
        fechaInput.min = yyyy + '-' + mm + '-' + dd;
        
        fechaInput.addEventListener('change', function() {
            updateHoras(this);
        });
    }

    // JavaScript form handling
    document.getElementById('multistep-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => data[key] = value);

        try {
            const response = await fetch('/wp-json/agendarcita/v1/enviar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if(result.status === 'success') {
                alert('Cita agendada exitosamente');
                // Esperamos 1.5 segundos antes de redirigir
                setTimeout(() => {
                    window.location.href = 'https://amaditavacunas.com/'; // Esto redirige a la página principal
                }, 1000);
            } else {
                alert('Error al procesar la solicitud: ' + result.message);
            }
        } catch(error) {
            console.error('Error:', error);
            alert('Ocurrió un error al procesar la solicitud. Por favor, intenta de nuevo más tarde.');
        }
    });
});

function nextStep() {
    console.log('Ejecutando nextStep');
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const circle1 = document.getElementById('step1-circle');
    const circle2 = document.getElementById('step2-circle');

    if (validateStep1()) {
        console.log('Validación exitosa, cambiando paso');
        step1.classList.remove('active');
        step2.classList.add('active');
        circle1.classList.remove('active');
        circle2.classList.add('active');
        step1.style.display = 'none';
        step2.style.display = 'block';
    }
}

function prevStep() {
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const circle1 = document.getElementById('step1-circle');
    const circle2 = document.getElementById('step2-circle');

    step2.classList.remove('active');
    step1.classList.add('active');
    circle2.classList.remove('active');
    circle1.classList.add('active');
    step2.style.display = 'none';
    step1.style.display = 'block';
}

function validateStep1() {
    console.log('Validando paso 1');
    const step1 = document.getElementById('step1');
    const inputs = step1.querySelectorAll('input[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value) {
            isValid = false;
            input.style.borderColor = 'red';
        } else {
            input.style.borderColor = '#E5E7EB';
        }
    });

    if (!isValid) {
        alert('Por favor, complete todos los campos requeridos');
    }

    return isValid;
}

function validateStep2() {
    const step2 = document.getElementById('step2');
    const requiredFields = step2.querySelectorAll('select[required], input[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value) {
            isValid = false;
            field.style.borderColor = 'red';
        } else {
            field.style.borderColor = '#E5E7EB';
        }
    });

    return isValid;
}

function updateHoras(fechaInput) {
    const [year, month, day] = fechaInput.value.split('-');
    const fecha = new Date(year, month - 1, day);
    const horaSelect = document.getElementById('hora-select');
    const esDomingo = fecha.getDay() === 0;
    const esSabado = fecha.getDay() === 6;
    
    if (esDomingo) {
        alert('Los domingos no están disponibles');
        fechaInput.value = ''; // Limpia el campo de fecha
        horaSelect.innerHTML = '<option value="">Seleccione hora</option>';
        return;
    }
    
    horaSelect.innerHTML = '<option value="">Seleccione hora</option>';
    
    const horas = esSabado ? [
        '7:00 AM a 8:00 AM',
        '8:00 AM a 9:00 AM',
        '9:00 AM a 10:00 AM',
        '10:00 AM a 11:00 AM',
        '11:00 AM a 12:00 PM',
        '12:00 PM a 1:00 PM',
        '1:00 PM a 2:00 PM',
        '2:00 PM a 3:00 PM'
    ] : [
        '7:00 AM a 8:00 AM',
        '8:00 AM a 9:00 AM',
        '9:00 AM a 10:00 AM',
        '10:00 AM a 11:00 AM',
        '11:00 AM a 12:00 PM',
        '12:00 PM a 1:00 PM',
        '1:00 PM a 2:00 PM',
        '2:00 PM a 3:00 PM',
        '3:00 PM a 4:00 PM',
        '4:00 PM a 5:00 PM',
        '5:00 PM a 6:00 PM',
        '6:00 PM a 7:00 PM'
    ];
    
    horas.forEach(hora => {
        const option = document.createElement('option');
        option.value = hora;
        option.textContent = hora;
        horaSelect.appendChild(option);
    });
}
</script>