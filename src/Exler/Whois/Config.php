<?php

namespace Exler\Whois;

class Config
{
    private static $data = [];

    /** 
     * Returns cached data and loads data from JSON
     * if there is no cache.
     * @param string $name
     * @return array|mixed
     */
    public static function load(string $name): array
    {
        if (!isset(self::$data[$name]))
            self::$data[$name] = self::loadJson($name);

        return self::$data[$name];
    }

    /** 
     * Loads data from a JSON file.
     * @param string $name
     * @return array|mixed
     */
    public static function loadJson(string $name): array
    {
        $json = file_get_contents(__DIR__ . "/Configs/$name.json");
        return json_decode($json, true);
    }
}
