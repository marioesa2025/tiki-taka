<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sorteo Épico: Carrera de Caballos</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom, #1a6e1a, #0e4a0e);
            margin: 0;
            padding: 20px;
            color: white;
            text-align: center;
        }
        
        h1 {
            text-shadow: 2px 2px 4px #000;
            font-size: 2.5em;
        }
        
        .pista {
            width: 90%;
            max-width: 800px;
            height: 400px;
            background: url('https://img.freepik.com/free-vector/green-racing-stripes-pattern_53876-90052.jpg');
            border: 8px solid #5d4037;
            border-radius: 10px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.6);
        }
        
        .caballo {
            width: 80px;
            height: 60px;
            position: absolute;
            left: 20px;
            font-size: 40px;
            text-shadow: 2px 2px 4px #000;
            filter: drop-shadow(0 0 5px rgba(255, 255, 255, 0.7));
            transition: left 0.1s linear;
            transform: scaleX(-1); /* Para que miren hacia la derecha */
        }
        
        .meta {
            position: absolute;
            right: 20px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: repeating-linear-gradient(
                to bottom,
                #fff,
                #fff 10px,
                #f00 10px,
                #f00 20px
            );
            z-index: 10;
        }
        
        #iniciar {
            background: #e91e63;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.3s;
        }
        
        #iniciar:hover {
            background: #c2185b;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
        }
        
        #iniciar:disabled {
            background: #9e9e9e;
        }
        
        #resultado {
            font-size: 28px;
            margin: 20px;
            color: #FFD700;
            text-shadow: 0 0 10px #FFD700, 0 0 20px #FFD700;
            opacity: 0;
            transition: opacity 1s;
        }
        
        #tabla-posiciones {
            background: rgba(0, 0, 0, 0.7);
            width: 250px;
            margin: 20px auto;
            padding: 10px;
            border-radius: 10px;
            text-align: left;
        }
        
        #contador {
            font-size: 60px;
            color: #FFD700;
            margin: 20px;
            text-shadow: 0 0 10px #000;
        }
        
        /* Animación de galope */
        @keyframes galope {
            0% { transform: scaleX(-1) translateY(0px); }
            50% { transform: scaleX(-1) translateY(-5px); }
            100% { transform: scaleX(-1) translateY(0px); }
        }
        
        .galopando {
            animation: galope 0.3s infinite;
        }
        
        /* Confeti */
        .confeti {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: #f00;
            opacity: 0;
            z-index: 100;
        }
    </style>
</head>
<body>
    <h1>🏇 SORTEO CARRERA ÉPICA 🏇</h1>
    
    <div id="contador"></div>
    
    <div class="pista">
        <div class="meta"></div>
        <?php
        $participantes = [
            ['emoji' => '🐎', 'nombre' => 'Relámpago', 'color' => '#FF5722'],
            ['emoji' => '🦄', 'nombre' => 'Unicornio', 'color' => '#E91E63'],
            ['emoji' => '🐴', 'nombre' => 'Tornado', 'color' => '#3F51B5'],
            ['emoji' => '🦓', 'nombre' => 'Rayas', 'color' => '#000000'],
            ['emoji' => '🐖', 'nombre' => 'Trufa', 'color' => '#FFC107']
        ];
        
        foreach ($participantes as $i => $caballo) {
            echo '<div class="caballo" id="caballo'.$i.'" style="top: '.($i*70 + 30).'px; color: '.$caballo['color'].';">'.$caballo['emoji'].' '.$caballo['nombre'].'</div>';
        }
        ?>
    </div>
    
    <div id="tabla-posiciones">
        <h3>🏆 Posiciones:</h3>
        <ol id="lista-posiciones"></ol>
    </div>
    
    <button id="iniciar">INICIAR CARRERA</button>
    <div id="resultado"></div>

    <!-- Sonidos (usando Audio API) -->
    <audio id="sonido-carrera" src="https://www.soundjay.com/horse/sounds/horse-gallop-01.mp3" loop></audio>
    <audio id="sonido-publico" src="https://www.soundjay.com/human/sounds/crowd-cheer-01.mp3"></audio>
    <audio id="sonido-campana" src="https://www.soundjay.com/misc/sounds/bell-ring-01.mp3"></audio>
    
    <script>
        // Elementos DOM
        const btnIniciar = document.getElementById('iniciar');
        const resultado = document.getElementById('resultado');
        const contador = document.getElementById('contador');
        const listaPosiciones = document.getElementById('lista-posiciones');
        
        // Sonidos
        const sonidoCarrera = document.getElementById('sonido-carrera');
        const sonidoPublico = document.getElementById('sonido-publico');
        const sonidoCampana = document.getElementById('sonido-campana');
        
        // Configuración
        const participantes = <?php echo json_encode($participantes); ?>;
        const meta = document.querySelector('.pista').offsetWidth - 100;
        let carreraEnCurso = false;
        let posiciones = [];
        
        // Iniciar carrera
        btnIniciar.addEventListener('click', function() {
            if (carreraEnCurso) return;
            
            btnIniciar.disabled = true;
            resultado.style.opacity = '0';
            listaPosiciones.innerHTML = '';
            posiciones = [];
            
            // Cuenta regresiva
            let cuenta = 3;
            contador.textContent = cuenta;
            sonidoCampana.play();
            
            const intervaloCuenta = setInterval(() => {
                cuenta--;
                contador.textContent = cuenta > 0 ? cuenta : '¡YA!';
                
                if (cuenta <= 0) {
                    clearInterval(intervaloCuenta);
                    setTimeout(() => {
                        contador.textContent = '';
                        iniciarCarrera();
                    }, 500);
                }
            }, 1000);
        });
        
        function iniciarCarrera() {
            carreraEnCurso = true;
            sonidoCarrera.play();
            
            // Mover cada caballo
            participantes.forEach((participante, index) => {
                const caballo = document.getElementById(`caballo${index}`);
                caballo.classList.add('galopando');
                
                // Velocidad base + aleatoriedad
                let velocidadBase = Math.random() * 2 + 1;
                let posicion = 20;
                let avance = 0;
                
                const intervalo = setInterval(() => {
                    // Añadir variabilidad para hacerlo más emocionante
                    const impulso = Math.random() * 3;
                    posicion += velocidadBase + impulso;
                    avance = (posicion / meta) * 100;
                    
                    caballo.style.left = `${posicion}px`;
                    
                    // Verificar si llegó a la meta
                    if (posicion >= meta) {
                        clearInterval(intervalo);
                        caballo.classList.remove('galopando');
                        
                        // Registrar posición
                        if (!posiciones.includes(index)) {
                            posiciones.push(index);
                            actualizarTablaPosiciones();
                            
                            // Si es el primero, mostrar como ganador
                            if (posiciones.length === 1) {
                                finalizarCarrera(participante);
                            }
                        }
                    }
                }, 50);
            });
        }
        
        function actualizarTablaPosiciones() {
            listaPosiciones.innerHTML = posiciones
                .map((idx, i) => `<li>${i+1}. ${participantes[idx].emoji} ${participantes[idx].nombre}</li>`)
                .join('');
        }
        
        function finalizarCarrera(ganador) {
            carreraEnCurso = false;
            sonidoCarrera.pause();
            sonidoPublico.play();
            
            resultado.textContent = `🎉 ¡${ganador.nombre} GANA EL SORTEO! 🎉`;
            resultado.style.opacity = '1';
            
            // Crear confeti
            crearConfeti();
            
            // Enviar el ganador al servidor (opcional)
            fetch(`guardar_ganador.php?ganador=${encodeURIComponent(ganador.nombre)}`);
        }
        
        function crearConfeti() {
            const colores = ['#f00', '#0f0', '#00f', '#ff0', '#f0f', '#0ff'];
            const pista = document.querySelector('.pista');
            
            for (let i = 0; i < 100; i++) {
                const confeti = document.createElement('div');
                confeti.className = 'confeti';
                confeti.style.left = Math.random() * 100 + '%';
                confeti.style.top = Math.random() * 100 + '%';
                confeti.style.backgroundColor = colores[Math.floor(Math.random() * colores.length)];
                confeti.style.opacity = '1';
                confeti.style.transform = `rotate(${Math.random() * 360}deg)`;
                confeti.style.width = `${Math.random() * 10 + 5}px`;
                confeti.style.height = `${Math.random() * 10 + 5}px`;
                
                pista.appendChild(confeti);
                
                // Animación
                setTimeout(() => {
                    confeti.style.transition = 'all 3s';
                    confeti.style.opacity = '0';
                    confeti.style.transform = `translateY(${Math.random() * 200}px) rotate(${Math.random() * 360}deg)`;
                    
                    // Eliminar después de animación
                    setTimeout(() => confeti.remove(), 3000);
                }, Math.random() * 1000);
            }
        }
    </script>
</body>
</html>