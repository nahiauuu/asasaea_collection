<?php

/**
 * "clustal_api.php"  ---> éste file es el backend PHP para la App ClustalOmega (ex 1)
 * 
 * Funciones:
 *  1-> Obtiene secuencias FASTA desde UniProt y PDB (evita CORS del navegador)
 *  2-> Ejecuta clustalo localmente si está instalado (DEBAJO ESTÁ EL COMANDO PA INSTALARLO CON SUDO)
 *  3-> Si no está instalado, hace un fallback automático a la API pública de EBI (que antes he probado y no iba)
 * 
 * 
 * Ejecutar el siguiente comando pa instalar ClustalOmega en el servidor (necesita SSH):
 *  >sudo apt-get install -y clustalo
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// ── CONFIG ────────────────────────────────────────────────────────────────────
// Ruta al binario clustalo. Cambia esto si está en otro lugar.
$CLUSTALO_PATH = trim(shell_exec('which clustalo 2>/dev/null') ?? '');
if (empty($CLUSTALO_PATH)) $CLUSTALO_PATH = '/usr/local/bin/clustalo'; // fallback

$EBI_EMAIL = 'student@example.com'; // Requerido por la API de EBI

// ── HELPERS ───────────────────────────────────────────────────────────────────
function jsonError($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}

function jsonOk($data) {
    echo json_encode(['ok' => true] + $data);
    exit;
}

// Hace una petición HTTP con cURL y devuelve el body
function httpGet($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_USERAGENT      => 'DBW-ClustalApp/1.0',
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) return ['ok' => false, 'error' => "cURL error: $err"];
    if ($httpCode < 200 || $httpCode >= 300) return ['ok' => false, 'error' => "HTTP $httpCode"];
    return ['ok' => true, 'body' => $body];
}

function httpPost($url, $fields) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($fields),
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_USERAGENT      => 'DBW-ClustalApp/1.0',
        CURLOPT_HTTPHEADER     => ['Accept: text/plain'],
    ]);
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) return ['ok' => false, 'error' => "cURL error: $err"];
    if ($httpCode < 200 || $httpCode >= 300) return ['ok' => false, 'error' => "HTTP $httpCode - $body"];
    return ['ok' => true, 'body' => $body];
}

// ── ROUTING ───────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Only POST allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['action'])) jsonError('Missing action');

$action = $input['action'];

// ── ACTION: fetch_uniprot ─────────────────────────────────────────────────────
if ($action === 'fetch_uniprot') {
    $ids = $input['ids'] ?? [];
    if (empty($ids)) jsonError('No UniProt IDs provided');
    
    $fasta = '';
    foreach ($ids as $id) {
        $id = trim($id);
        if (empty($id)) continue;
        $res = httpGet("https://www.uniprot.org/uniprot/{$id}.fasta");
        if (!$res['ok']) jsonError("UniProt ID not found: $id");
        $fasta .= $res['body'] . "\n";
    }
    jsonOk(['fasta' => trim($fasta)]);
}

// ── ACTION: fetch_pdb ─────────────────────────────────────────────────────────
if ($action === 'fetch_pdb') {
    $ids = $input['ids'] ?? [];
    if (empty($ids)) jsonError('No PDB IDs provided');
    
    $fasta = '';
    foreach ($ids as $id) {
        $id = strtoupper(trim($id));
        if (empty($id)) continue;
        $res = httpGet("https://www.rcsb.org/fasta/entry/{$id}");
        if (!$res['ok']) jsonError("PDB ID not found: $id");
        $fasta .= $res['body'] . "\n";
    }
    jsonOk(['fasta' => trim($fasta)]);
}

// ── ACTION: align ─────────────────────────────────────────────────────────────
if ($action === 'align') {
    $fasta  = $input['fasta']  ?? '';
    $outfmt = $input['outfmt'] ?? 'clustal';
    $order  = $input['order']  ?? 'aligned';

    if (empty($fasta)) jsonError('No FASTA sequences provided');

    // Validar que haya al menos 2 secuencias
    $seqCount = substr_count($fasta, '>');
    if ($seqCount < 2) jsonError('At least 2 sequences are required for alignment');

    // ── MAPA DE FORMATOS (compartido por clustalo local y EBI) ───────────────
    // Convierte el nombre del frontend al valor que aceptan clustalo y EBI
    $outfmtMap = [
        'clustal'   => 'clustal',
        'fasta'     => 'fa',        // EBI y clustalo usan 'fa', no 'fasta'
        'msf'       => 'msf',
        'phylip'    => 'phylip',
        'selex'     => 'selex',
        'stockholm' => 'stockholm',
        'vienna'    => 'vienna',
    ];
    $outfmtValue = $outfmtMap[$outfmt] ?? 'clustal';

    // ── OPCIÓN A: clustalo local ──────────────────────────────────────────────
    global $CLUSTALO_PATH;
    $clustaloAvailable = file_exists($CLUSTALO_PATH) && is_executable($CLUSTALO_PATH);

    if ($clustaloAvailable) {
        // Crear fichero temporal de entrada
        $tmpIn  = tempnam(sys_get_temp_dir(), 'clustal_in_');
        $tmpOut = tempnam(sys_get_temp_dir(), 'clustal_out_');
        file_put_contents($tmpIn, $fasta);

        // Opciones de orden
        $orderFlag = ($order === 'input') ? '--output-order=input-order' : '--output-order=tree-order';

        // Ejecutar clustalo
        $cmd = escapeshellcmd($CLUSTALO_PATH)
             . ' -i ' . escapeshellarg($tmpIn)
             . ' -o ' . escapeshellarg($tmpOut)
             . ' --outfmt=' . escapeshellarg($outfmtValue)
             . ' ' . $orderFlag
             . ' --force 2>&1';

        $output = shell_exec($cmd);

        if (!file_exists($tmpOut) || filesize($tmpOut) === 0) {
            @unlink($tmpIn);
            @unlink($tmpOut);
            jsonError('clustalo failed: ' . ($output ?? 'unknown error'));
        }

        $result = file_get_contents($tmpOut);
        @unlink($tmpIn);
        @unlink($tmpOut);

        jsonOk(['result' => $result, 'engine' => 'local']);
    }

    // ── OPCIÓN B: API de EBI (fallback si clustalo no está instalado) ─────────
    global $EBI_EMAIL;

    // Mapeo formato → tipo de resultado EBI (para recoger el resultado)
    $ebiTypeMap = [
        'clustal'   => 'aln-clustal',
        'fasta'     => 'aln-fasta',
        'msf'       => 'aln-msf',
        'phylip'    => 'aln-phylip',
        'selex'     => 'aln-selex',
        'stockholm' => 'aln-stockholm',
        'vienna'    => 'aln-vienna',
    ];
    $ebiType = $ebiTypeMap[$outfmt] ?? 'aln-clustal';

    // Enviar job a EBI
    $submitRes = httpPost('https://www.ebi.ac.uk/Tools/services/rest/clustalo/run', [
        'email'    => $EBI_EMAIL,
        'sequence' => $fasta,
        'outfmt'   => $outfmtValue,  // usa el valor convertido ('fa' en vez de 'fasta', etc.)
        'order'    => $order,
        'stype'    => 'protein',
    ]);

    if (!$submitRes['ok']) jsonError('EBI submission failed: ' . $submitRes['error']);

    $jobId = trim($submitRes['body']);
    if (empty($jobId)) jsonError('EBI returned empty job ID');

    // Devolver jobId al frontend para que haga polling
    jsonOk(['jobId' => $jobId, 'ebiType' => $ebiType, 'engine' => 'ebi']);
}

// ── ACTION: ebi_status ────────────────────────────────────────────────────────
if ($action === 'ebi_status') {
    $jobId = $input['jobId'] ?? '';
    if (empty($jobId)) jsonError('No jobId provided');

    $res = httpGet("https://www.ebi.ac.uk/Tools/services/rest/clustalo/status/{$jobId}");
    if (!$res['ok']) jsonError('EBI status check failed: ' . $res['error']);

    jsonOk(['status' => trim($res['body'])]);
}

// ── ACTION: ebi_result ────────────────────────────────────────────────────────
if ($action === 'ebi_result') {
    $jobId   = $input['jobId']   ?? '';
    $ebiType = $input['ebiType'] ?? 'aln-clustal';
    if (empty($jobId)) jsonError('No jobId provided');

    $res = httpGet("https://www.ebi.ac.uk/Tools/services/rest/clustalo/result/{$jobId}/{$ebiType}");
    if (!$res['ok']) jsonError('EBI result fetch failed: ' . $res['error']);

    jsonOk(['result' => $res['body']]);
}

jsonError('Unknown action');
