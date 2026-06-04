<?php
/**
 * api/prenota_turno.php — Alias per api/turni.php (POST).
 * Redirect interno per chiarezza semantica nel form HTML.
 */
declare(strict_types=1);

// Passa tutto a turni.php che gestisce sia GET che POST
require __DIR__ . '/turni.php';
