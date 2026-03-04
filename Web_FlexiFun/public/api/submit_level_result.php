<?php
// FlexiFun/public/api/submit_level_result.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../../private/db.php';

// 1) API KEY
$clientKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($clientKey !== API_KEY) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

// 2) Ler JSON
$rawBody = (string) file_get_contents('php://input');
$data = json_decode($rawBody, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Base
$pacienteId = $data['pacienteId'] ?? null;
$nivelId    = $data['nivelId'] ?? null;
$durationMs = $data['durationMs'] ?? null;

if ($pacienteId === null || $nivelId === null || $durationMs === null) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing fields (pacienteId, nivelId, durationMs)']);
    exit;
}

$pacienteId = (int)$pacienteId;
$nivelId = (int)$nivelId;
$durationMs = (int)$durationMs;

if (!in_array($nivelId, [1,2,3], true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid nivelId']);
    exit;
}

// ---- nível 1/2 ----
$forcaMax   = $data['forcaMax'] ?? null;
$threshold  = $data['threshold'] ?? 300;
$coinForces = $data['coinForces'] ?? null;

// ---- nível 3 ----
$sampleRateHz      = $data['sampleRateHz'] ?? null;
$baselineMd        = $data['baselineMd'] ?? null;
$flexDeltaMax      = $data['flexDeltaMax'] ?? null;
$mdRawSamples      = $data['mdRawSamples'] ?? null;
$mdDeltaSamples    = $data['mdDeltaSamples'] ?? null;
$jumpSampleIndices = $data['jumpSampleIndices'] ?? null;
$trunks            = $data['trunks'] ?? null;

// preparar campos DB
$passou = 1;
$score = 0;
$reps = null;

$forcaMaxDb = null;
$flexaoMaxDb = null;

// raw_data comum
$raw = ['durationMs' => $durationMs];

if ($nivelId === 1 || $nivelId === 2) {

    if ($forcaMax === null || !is_array($coinForces)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Missing fields for nivel 1/2 (forcaMax, coinForces)']);
        exit;
    }

    if (count($coinForces) !== 10) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'coinForces must have 10 values']);
        exit;
    }

    $forcaMaxDb = (float)$forcaMax;
    $threshold  = (int)$threshold;
    $coinForces = array_map('intval', $coinForces);

    $raw['threshold']  = $threshold;
    $raw['coinForces'] = $coinForces;

    $score = 10;
    $reps = 10;

} else {
    // -------- NÍVEL 3 --------
    if ($flexDeltaMax === null || !is_array($mdRawSamples) || !is_array($mdDeltaSamples)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Missing fields for nivel 3 (flexDeltaMax, mdRawSamples, mdDeltaSamples)']);
        exit;
    }

    $flexaoMaxDb = (int)$flexDeltaMax;

    $raw['sampleRateHz'] = $sampleRateHz !== null ? (float)$sampleRateHz : 50.0;
    $raw['baselineMd']   = $baselineMd !== null ? (int)$baselineMd : 0;

    $raw['mdRawSamples']   = array_map('intval', $mdRawSamples);
    $raw['mdDeltaSamples'] = array_map('intval', $mdDeltaSamples);

    if (is_array($jumpSampleIndices)) {
        $raw['jumpSampleIndices'] = array_map('intval', $jumpSampleIndices);
    } else {
        $raw['jumpSampleIndices'] = [];
    }

    if ($trunks !== null) {
        $score = (int)$trunks;
        $reps  = (int)$trunks;
    } else {
        $score = 0;
        $reps = null;
    }
}

try {
    // validar paciente + fisio
    $stmt = $pdo->prepare("
        SELECT fisioterapeuta_id
        FROM paciente
        WHERE user_id = :pid
        LIMIT 1
    ");
    $stmt->execute([':pid' => $pacienteId]);
    $fisioId = $stmt->fetchColumn();

    if ($fisioId === false) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Paciente inexistente']);
        exit;
    }

    // validar nivel existe
    $stmt = $pdo->prepare("SELECT 1 FROM nivel WHERE id = :nid LIMIT 1");
    $stmt->execute([':nid' => $nivelId]);
    if (!$stmt->fetchColumn()) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Nivel inexistente']);
        exit;
    }

    $pdo->beginTransaction();

    // criar sessao
    $stmt = $pdo->prepare("
        INSERT INTO sessao (paciente_id, fisioterapeuta_id, iniciou_em, terminou_em, notas)
        VALUES (:pid, :fid, NOW(), NOW(), NULL)
    ");
    $stmt->execute([
        ':pid' => $pacienteId,
        ':fid' => $fisioId !== null ? (int)$fisioId : null,
    ]);

    $sessaoId = (int)$pdo->lastInsertId();

    // inserir resultado_nivel
    $stmt = $pdo->prepare("
        INSERT INTO resultado_nivel
            (sessao_id, nivel_id, passou, score, forca_max, flexao_max, tempo_reacao_ms, repeticoes, raw_data, criado_em)
        VALUES
            (:sid, :nid, :passou, :score, :fmax, :xmax, :tms, :reps, :raw, NOW())
    ");
    $stmt->execute([
        ':sid'    => $sessaoId,
        ':nid'    => $nivelId,
        ':passou' => $passou,
        ':score'  => $score,
        ':fmax'   => $forcaMaxDb,
        ':xmax'   => $flexaoMaxDb,
        ':tms'    => $durationMs,
        ':reps'   => $reps,
        ':raw'    => json_encode($raw, JSON_UNESCAPED_UNICODE),
    ]);

    $pdo->commit();

    echo json_encode(['ok' => true, 'sessaoId' => $sessaoId, 'nivelId' => $nivelId]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}