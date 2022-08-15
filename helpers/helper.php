<?php

if (!function_exists('get_file_data')) {
    /**
     * @param $file
     * @param $convert_to_array
     * @return bool|mixed
     * @author TrinhLe
     */
    function get_file_data($file, $convert_to_array = true)
    {
        $file = \Illuminate\Support\Facades\File::get($file);
        if (!empty($file)) {
            if ($convert_to_array) {
                return json_decode($file, true);
            } else {
                return $file;
            }
        }

        return $convert_to_array ? [] : null;
    }
}
