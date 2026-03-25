<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Enhanced Debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't echo errors to JSON output
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$debugFile = __DIR__ . '/debug_output.log';
file_put_contents($debugFile, "Request Start: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// Configuration
$PROJECT_ID = 'my-project-1542742202384';
$DATA_STORE_ID = 'tesis-cieepe-data_1771317842045';
$KEY_FILE_PATH = __DIR__ . '/../my-project-1542742202384-bdcc1ba8d583.json'; // Updated path logic

if (!file_exists($KEY_FILE_PATH)) {
    file_put_contents($debugFile, "Key File Missing: $KEY_FILE_PATH\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['error' => 'Configuration Error: Key file missing']);
    exit;
}

// 1. Get Input
$input = json_decode(file_get_contents('php://input'), true);
$query = isset($input['query']) ? trim($input['query']) : '';

if (empty($query)) {
    echo json_encode(['error' => 'Query is required']);
    exit;
}

try {
    // 2. Get Access Token
    $accessToken = getAccessToken($KEY_FILE_PATH);

    // 3. Call Vertex AI Search API (Reverting to Search for stability first)
    // We will mimic the chat response structure
    $results = searchVertexAI($PROJECT_ID, $DATA_STORE_ID, $accessToken, $query);

    // transform search result to chat format if needed by frontend
    // The frontend expects { reply: ..., searchResults: ... } or just raw results
    
    // For now, let's stick to the search response structure the previous frontend handled, 
    // but wrapper it to look like a chat reply.
    
    $chatResponse = [
        'reply' => [
            'reply' => isset($results['summary']['summaryText']) ? $results['summary']['summaryText'] : "Aquí están los resultados encontrados:"
        ],
        'searchResults' => isset($results['results']) ? $results['results'] : []
    ];

    echo json_encode($chatResponse);

} catch (Exception $e) {
    http_response_code(500);
    file_put_contents($debugFile, "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Generates a Google OAuth2 Access Token
 */
function getAccessToken($keyFile) {
    global $debugFile;
    if (!file_exists($keyFile)) {
        throw new Exception("Key file not found: $keyFile");
    }

    $keyData = json_decode(file_get_contents($keyFile), true);
    if (!$keyData) {
        throw new Exception("Invalid key file JSON");
    }

    $privateKey = $keyData['private_key'];
    $clientEmail = $keyData['client_email'];
    $tokenUri = $keyData['token_uri'];

    $header = ['alg' => 'RS256', 'typ' => 'JWT'];
    $headerEncoded = base64UrlEncode(json_encode($header));

    $now = time();
    $claimSet = [
        'iss' => $clientEmail,
        'scope' => 'https://www.googleapis.com/auth/cloud-platform',
        'aud' => $tokenUri,
        'exp' => $now + 3600,
        'iat' => $now
    ];
    $claimSetEncoded = base64UrlEncode(json_encode($claimSet));

    $signature = '';
    $success = openssl_sign("$headerEncoded.$claimSetEncoded", $signature, $privateKey, 'SHA256');
    if (!$success) {
        file_put_contents($debugFile, "OpenSSL Error: " . openssl_error_string() . "\n", FILE_APPEND);
        throw new Exception("Failed to sign JWT");
    }
    $signatureEncoded = base64UrlEncode($signature);
    $jwt = "$headerEncoded.$claimSetEncoded.$signatureEncoded";
    
    // Exchange JWT for Access Token
    $postData = http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]);

    $ch = curl_init($tokenUri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Uncomment if local SSL issues
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        throw new Exception("Curl error: " . curl_error($ch));
    }
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Failed to get access token: $response");
    }

    $tokenData = json_decode($response, true);
    return $tokenData['access_token'];
}

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Calls the Vertex AI Search API with Summary/Answer generation
 */
function searchVertexAI($projectId, $dataStoreId, $accessToken, $query) {
    global $debugFile;
    $url = "https://discoveryengine.googleapis.com/v1alpha/projects/$projectId/locations/global/collections/default_collection/dataStores/$dataStoreId/servingConfigs/default_search:search";

    // Analyst Persona Prompt
    // $preamble = "Actúa como un Analista de Investigación Senior del CIATA con un tono amable, colaborador y profesional. Tu objetivo es guiar al usuario en su proceso de investigación. " .
    //             "Iniciativa: Si el usuario te saluda o dice que quiere iniciar una investigación o tesis, salúdalo cordialmente, ofrécele tu ayuda técnica y pídele que te proporcione el tema o área de interés. " .
    //             "Lenguaje Común: Evita tecnicismos innecesarios al inicio; habla de forma clara y accesible. " .
    //             "Tu flujo de trabajo: " .
    //             "Identificación: Cuando el usuario pregunte por un tema, localiza los fragmentos más relevantes en el acervo de tesis. " .
    //             "Análisis Temático: Explica cómo la tesis seleccionada profundiza en esa rama específica (metodología, variables o enfoque). " .
    //             "Síntesis y Conclusión: Resume los hallazgos principales y la conclusión a la que llega el autor respecto al tema consultado. " .
    //             "Cita Obligatoria: Al entregar hallazgos, siempre indica el nombre de la tesis, el autor y el número de página exacto de donde extrajiste la información. " .
    //             "Formato de respuesta: " .
    //             "Resumen Analítico: [Tu explicación aquí] " .
    //             "Conclusión del Autor: [Resumen de la conclusión] " .
    //             "Fuente Académica: Tesis '[Nombre]', Autor: [Nombre], Página: [Número]. " .
    //             "Restricción: Si el tema solicitado no está en tus documentos, explícale amablemente que tu base de conocimientos actual se limita al acervo del CIATA y no puedes usar información externa.";


// $preamble = "Actúa como un Analista de Investigación Senior del CIATA. " .
//             "REGLA DE ORO: Si la consulta del usuario es un saludo (como 'Hola', 'Buenos días', etc.), NO busques en los documentos; simplemente preséntate amablemente como el Analista Senior y pregunta qué tema desea investigar hoy. " .
//             "REGLA TÉCNICA: Si la consulta es un tema de estudio, OMITE saludos y frases de cortesía. Ve directo al análisis. " .
//             "Tu flujo: " .
//             "1. Identificación: Localiza fragmentos relevantes en el acervo. " .
//             "2. Análisis Temático: Explica metodología, variables o enfoque. " .
//             "3. Síntesis y Conclusión: Resume hallazgos y conclusiones del autor. " .
//             "4. Cita Obligatoria: Indica Tesis, Autor y Página exacta. " .
//             "Formato: " .
//             "Resumen Analítico: [Tu explicación] " .
//             "Conclusión del Autor: [Resumen de conclusión] " .
//             "Fuente Académica: Tesis '[Nombre]', Autor: [Nombre], Página: [Número]. " .
//             "RESTRICCIÓN: Está prohibido añadir 'Fuentes Consultadas' o bibliografía al final. Termina inmediatamente tras la Fuente Académica con la palabra [FIN].";

// Analyst Persona Prompt - Versión Blindada en Español
    $preamble = "IDIOMA OBLIGATORIO: Todas tus respuestas deben ser exclusivamente en ESPAÑOL, sin importar el idioma de la consulta o de los documentos fuente. " .
                "Actúa como un Analista de Investigación Senior del CIATA. " .
                "REGLA DE SALUDO: Si el usuario solo saluda, preséntate amablemente en español. " .
                "REGLA TÉCNICA: En consultas sobre temas, ve directo al análisis en español. " .
                "Tu flujo de trabajo: " .
                "1. Identificación: Localiza fragmentos relevantes en el acervo. " .
                "2. Análisis Temático: Explica metodología y enfoque. " .
                "3. Síntesis y Conclusión: Resume hallazgos principales. " .
                "4. Cita Obligatoria: Indica Tesis, Autor y Página exacta. " .
                "Formato de respuesta: " .
                "Resumen Analítico: [Tu explicación] " .
                "Conclusión del Autor: [Resumen de la conclusión] " .
                "Fuente Académica: Tesis '[Nombre]', Autor: [Nombre], Página: [Número]. " .
                "RESTRICCIÓN CRÍTICA: Prohibido añadir listas de 'Fuentes Consultadas' al final. " .
                "IDIOMA FINAL: Bajo ninguna circunstancia respondas en portugués, inglés u otro idioma que no sea ESPAÑOL. " .
                "Termina inmediatamente tras la Fuente Académica.";

    $postData = json_encode([
        'query' => $query,
        'pageSize' => 5, // Reduce result count for cleaner output alongside summary
        'queryExpansionSpec' => ['condition' => 'AUTO'],
        'spellCorrectionSpec' => ['mode' => 'AUTO'],
        'contentSearchSpec' => [
            'snippetSpec' => ['returnSnippet' => true],
            'summarySpec' => [
                'summaryResultCount' => 5,
                'includeCitations' => true,
                'ignoreAdversarialQuery' => true,
                'modelPromptSpec' => [
                    'preamble' => $preamble
                ]
            ]
        ]
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ]);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Uncomment if local SSL issues

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        throw new Exception("Curl Search error: " . curl_error($ch));
    }
    curl_close($ch);
    
    file_put_contents($debugFile, "Search Response ($httpCode): " . substr($response, 0, 500) . "...\n", FILE_APPEND);

    if ($httpCode !== 200) {
        throw new Exception("Search API failed ($httpCode): $response");
    }

    return json_decode($response, true);
}
?>
