<?php
// language.php - Language Handling
function getCurrentLanguage() {
    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Configuration
    $config = require 'lanconfig.php';
    $supported_languages = $config['supported_languages'];
    $default_language = $config['default_language'];

    // Check if language is passed via GET parameter
    if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $supported_languages)) {
        $_SESSION['lang'] = $_GET['lang'];
    }

    // Use session language or default
    $lang = $_SESSION['lang'] ?? $default_language;

    // Validate language
    return in_array($lang, array_keys($supported_languages)) ? $lang : $default_language;
}

function getTranslations($lang = null) {
    if ($lang === null) {
        $lang = getCurrentLanguage();
    }

    $translation_file = __DIR__ . "/lang/{$lang}.php";
    
    // Fallback to default language if translation file doesn't exist
    if (!file_exists($translation_file)) {
        $config = require 'config.php';
        $lang = $config['default_language'];
        $translation_file = __DIR__ . "/lang/{$lang}.php";
    }

    return require $translation_file;
}