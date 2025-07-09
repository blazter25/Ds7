<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Verificar autenticación
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('No autorizado');
}

$userId = $_SESSION['user_id'];
$format = $_GET['format'] ?? 'json';
$type = $_GET['type'] ?? 'activities'; // activities, statistics, challenges

// Obtener datos según el tipo solicitado
$data = [];
$pdo = getConnection();

switch ($type) {
    case 'activities':
        $stmt = $pdo->prepare("
            SELECT a.*, c.name as challenge_name
            FROM activities a
            JOIN user_challenges uc ON a.user_challenge_id = uc.id
            JOIN challenges c ON uc.challenge_id = c.id
            WHERE uc.user_id = ?
            ORDER BY a.activity_date DESC
        ");
        $stmt->execute([$userId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
        
    case 'statistics':
        // Estadísticas generales
        $stats = getUserStatistics($userId);
        
        // Actividades por tipo
        $stmt = $pdo->prepare("
            SELECT activity_type, COUNT(*) as count, 
                   SUM(duration) as total_duration,
                   SUM(calories_burned) as total_calories
            FROM activities a
            JOIN user_challenges uc ON a.user_challenge_id = uc.id
            WHERE uc.user_id = ?
            GROUP BY activity_type
        ");
        $stmt->execute([$userId]);
        $activityTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Progreso mensual
        $stmt = $pdo->prepare("
            SELECT DATE_FORMAT(activity_date, '%Y-%m') as month,
                   COUNT(*) as activities,
                   SUM(calories_burned) as calories,
                   SUM(duration) as total_duration
            FROM activities a
            JOIN user_challenges uc ON a.user_challenge_id = uc.id
            WHERE uc.user_id = ?
            GROUP BY DATE_FORMAT(activity_date, '%Y-%m')
            ORDER BY month DESC
        ");
        $stmt->execute([$userId]);
        $monthlyProgress = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [
            'general' => $stats,
            'by_activity_type' => $activityTypes,
            'monthly_progress' => $monthlyProgress
        ];
        break;
        
    case 'challenges':
        $stmt = $pdo->prepare("
            SELECT c.*, uc.status, uc.progress, uc.start_date, uc.end_date,
                   COUNT(DISTINCT a.id) as activity_count,
                   SUM(a.calories_burned) as total_calories,
                   SUM(a.duration) as total_duration
            FROM challenges c
            JOIN user_challenges uc ON c.id = uc.challenge_id
            LEFT JOIN activities a ON a.user_challenge_id = uc.id
            WHERE uc.user_id = ?
            GROUP BY c.id, uc.id
        ");
        $stmt->execute([$userId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
}

// Exportar según el formato solicitado
switch ($format) {
    case 'json':
        exportJSON($data, $type);
        break;
        
    case 'xml':
        exportXML($data, $type);
        break;
        
    case 'excel':
    case 'csv':
        exportExcel($data, $type);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Formato no soportado']);
}

/**
 * Exportar a JSON
 */
function exportJSON($data, $type) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="fitness_' . $type . '_' . date('Y-m-d') . '.json"');
    
    echo json_encode([
        'export_date' => date('Y-m-d H:i:s'),
        'type' => $type,
        'data' => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

/**
 * Exportar a XML
 */
function exportXML($data, $type) {
    header('Content-Type: application/xml');
    header('Content-Disposition: attachment; filename="fitness_' . $type . '_' . date('Y-m-d') . '.xml"');
    
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><fitness_export></fitness_export>');
    $xml->addChild('export_date', date('Y-m-d H:i:s'));
    $xml->addChild('type', $type);
    
    $dataNode = $xml->addChild('data');
    arrayToXml($data, $dataNode);
    
    echo $xml->asXML();
}

/**
 * Convertir array a XML recursivamente
 */
function arrayToXml($data, &$xml) {
    foreach ($data as $key => $value) {
        if (is_numeric($key)) {
            $key = 'item_' . $key;
        }
        
        if (is_array($value)) {
            $subnode = $xml->addChild($key);
            arrayToXml($value, $subnode);
        } else {
            $xml->addChild($key, htmlspecialchars($value ?? ''));
        }
    }
}

/**
 * Exportar a Excel/CSV
 */
function exportExcel($data, $type) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="fitness_' . $type . '_' . date('Y-m-d') . '.csv"');
    
    // BOM para UTF-8
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    
    switch ($type) {
        case 'activities':
            // Encabezados
            fputcsv($output, [
                'Fecha',
                'Desafío',
                'Tipo de Actividad',
                'Duración (min)',
                'Calorías',
                'Notas',
                'Registrado'
            ]);
            
            // Datos
            foreach ($data as $row) {
                fputcsv($output, [
                    $row['activity_date'],
                    $row['challenge_name'],
                    $row['activity_type'],
                    $row['duration'],
                    $row['calories_burned'],
                    $row['notes'],
                    $row['created_at']
                ]);
            }
            break;
            
        case 'statistics':
            // Estadísticas generales
            fputcsv($output, ['ESTADÍSTICAS GENERALES']);
            fputcsv($output, ['Métrica', 'Valor']);
            fputcsv($output, ['Desafíos Totales', $data['general']['total_challenges']]);
            fputcsv($output, ['Desafíos Completados', $data['general']['completed_challenges']]);
            fputcsv($output, ['Calorías Totales', $data['general']['total_calories_burned']]);
            fputcsv($output, ['Tiempo Total (min)', $data['general']['total_workout_time']]);
            
            fputcsv($output, []); // Línea vacía
            
            // Por tipo de actividad
            fputcsv($output, ['POR TIPO DE ACTIVIDAD']);
            fputcsv($output, ['Tipo', 'Sesiones', 'Duración Total (min)', 'Calorías Totales']);
            foreach ($data['by_activity_type'] as $activity) {
                fputcsv($output, [
                    $activity['activity_type'],
                    $activity['count'],
                    $activity['total_duration'],
                    $activity['total_calories']
                ]);
            }
            
            fputcsv($output, []); // Línea vacía
            
            // Progreso mensual
            fputcsv($output, ['PROGRESO MENSUAL']);
            fputcsv($output, ['Mes', 'Actividades', 'Calorías', 'Duración Total (min)']);
            foreach ($data['monthly_progress'] as $month) {
                fputcsv($output, [
                    $month['month'],
                    $month['activities'],
                    $month['calories'],
                    $month['total_duration']
                ]);
            }
            break;
            
        case 'challenges':
            // Encabezados
            fputcsv($output, [
                'Desafío',
                'Duración (días)',
                'Estado',
                'Progreso (%)',
                'Fecha Inicio',
                'Fecha Fin',
                'Actividades',
                'Calorías Totales',
                'Tiempo Total (min)'
            ]);
            
            // Datos
            foreach ($data as $challenge) {
                fputcsv($output, [
                    $challenge['name'],
                    $challenge['duration'],
                    $challenge['status'],
                    $challenge['progress'],
                    $challenge['start_date'],
                    $challenge['end_date'] ?? 'En progreso',
                    $challenge['activity_count'],
                    $challenge['total_calories'] ?? 0,
                    $challenge['total_duration'] ?? 0
                ]);
            }
            break;
    }
    
    fclose($output);
}
?>