<?php
// Cliente SOAP de ejemplo para probar el servicio
session_start();

// Para pruebas, simular un usuario logueado
$_SESSION['user_id'] = 1; // Cambiar según el ID del usuario a probar

try {
    // Configurar el cliente SOAP
    $options = [
        'location' => 'http://localhost/fitness-challenges/api/soap/server.php',
        'uri' => 'http://localhost/fitness-challenges/api/soap/',
        'trace' => 1,
        'exceptions' => true
    ];
    
    $client = new SoapClient(null, $options);
    
    echo "<h1>Pruebas del Servicio SOAP - Fitness Challenge</h1>";
    
    // Ejemplo 1: Obtener información de ejercicios
    echo "<h2>1. Información de Ejercicios de Cardio</h2>";
    $exercises = $client->getExerciseInfo('cardio');
    echo "<pre>";
    print_r($exercises);
    echo "</pre>";
    
    // Ejemplo 2: Obtener desafíos desde la base de datos
    echo "<h2>2. Desafíos Disponibles</h2>";
    $challenges = $client->getChallengesFromDB();
    echo "<pre>";
    print_r($challenges);
    echo "</pre>";
    
    // Ejemplo 3: Obtener desafíos del usuario
    echo "<h2>3. Mis Desafíos</h2>";
    $myChallenges = $client->getChallengesFromDB($_SESSION['user_id']);
    echo "<pre>";
    print_r($myChallenges);
    echo "</pre>";
    
    // Ejemplo 4: Obtener estadísticas del usuario
    echo "<h2>4. Mis Estadísticas</h2>";
    $stats = $client->getUserStats($_SESSION['user_id']);
    echo "<pre>";
    print_r($stats);
    echo "</pre>";
    
    // Ejemplo 5: Obtener actividades del usuario
    echo "<h2>5. Mis Actividades Recientes</h2>";
    $activities = $client->getUserActivities($_SESSION['user_id']);
    echo "<pre>";
    print_r($activities);
    echo "</pre>";
    
    // Ejemplo 6: Calcular calorías
    echo "<h2>6. Cálculo de Calorías</h2>";
    $calories = $client->calculateCalories('Correr', 30, 75);
    echo "<pre>";
    print_r($calories);
    echo "</pre>";
    
    // Ejemplo 7: Obtener rutina de entrenamiento
    echo "<h2>7. Rutina Recomendada</h2>";
    $routine = $client->getWorkoutRoutines('weight_loss', 'beginner');
    echo "<pre>";
    print_r($routine);
    echo "</pre>";
    
    // Ejemplo 8: Recomendaciones nutricionales
    echo "<h2>8. Recomendaciones Nutricionales</h2>";
    $nutrition = $client->getNutritionRecommendations('weight_loss', 75, 'moderate');
    echo "<pre>";
    print_r($nutrition);
    echo "</pre>";
    
    // Ejemplo 9: Registrar una actividad (comentado para evitar insertar datos de prueba)
    /*
    echo "<h2>9. Registrar Nueva Actividad</h2>";
    $newActivity = $client->registerActivity(
        $_SESSION['user_id'],  // userId
        1,                     // challengeId
        'Correr',              // activityType
        30,                    // duration
        300,                   // calories
        date('Y-m-d'),         // activityDate
        'Entrenamiento matutino' // notes
    );
    echo "<pre>";
    print_r($newActivity);
    echo "</pre>";
    */
    
    echo "<hr>";
    echo "<h2>Enlaces de Prueba para Exportación</h2>";
    echo "<p>Prueba las funciones de exportación con estos enlaces:</p>";
    echo "<ul>";
    echo "<li><a href='../export.php?format=json&type=activities'>Exportar Actividades en JSON</a></li>";
    echo "<li><a href='../export.php?format=xml&type=activities'>Exportar Actividades en XML</a></li>";
    echo "<li><a href='../export.php?format=excel&type=activities'>Exportar Actividades en Excel</a></li>";
    echo "<li><a href='../export.php?format=json&type=statistics'>Exportar Estadísticas en JSON</a></li>";
    echo "<li><a href='../export.php?format=excel&type=statistics'>Exportar Estadísticas en Excel</a></li>";
    echo "<li><a href='../export.php?format=json&type=challenges'>Exportar Desafíos en JSON</a></li>";
    echo "</ul>";
    
} catch (SoapFault $e) {
    echo "<h2>Error SOAP</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>";
    echo "Código: " . $e->getCode() . "\n";
    echo "Detalles: " . $e->faultstring . "\n";
    echo "</pre>";
} catch (Exception $e) {
    echo "<h2>Error General</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Mostrar información de depuración
if (isset($client)) {
    echo "<hr>";
    echo "<h2>Información de Depuración</h2>";
    echo "<h3>Última Petición SOAP:</h3>";
    echo "<pre>" . htmlspecialchars($client->__getLastRequest()) . "</pre>";
    echo "<h3>Última Respuesta SOAP:</h3>";
    echo "<pre>" . htmlspecialchars($client->__getLastResponse()) . "</pre>";
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        background-color: #f5f5f5;
    }
    h1 {
        color: #FF385C;
        border-bottom: 2px solid #FF385C;
        padding-bottom: 10px;
    }
    h2 {
        color: #333;
        margin-top: 30px;
    }
    h3 {
        color: #666;
    }
    pre {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 15px;
        overflow-x: auto;
    }
    a {
        color: #FF385C;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
    ul {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 20px 40px;
    }
    li {
        margin: 10px 0;
    }
</style>