<?php

use Illuminate\Support\Facades\App;

if (!function_exists('asset_path')) {
  function asset_path($path)
  {
    return App::environment('local') ? asset($path) : secure_asset($path);
  }
}

if (!function_exists('format_execution_time')) {
    /**
     * Format execution time in seconds to minutes and seconds
     *
     * @param float $seconds
     * @return string
     */
    function format_execution_time($seconds)
    {
        $minutes = floor($seconds / 60);
        $seconds = floor(fmod($seconds, 60));
        return $minutes . 'min ' . $seconds . 'sec';
    }
}