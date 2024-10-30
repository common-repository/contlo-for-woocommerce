<?php

if (!defined('ABSPATH')) {
    exit;
}

class ContloLogger
{

    function __construct()
    {
        global $wpdb;
        global $charset_collate;
        $table_name = $wpdb->prefix . 'contlo_logs';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `date` datetime,
                `domain` longtext CHARACTER SET utf8,
                `type` varchar(10) CHARACTER SET utf8,
                `message` longtext CHARACTER SET utf8,
                PRIMARY KEY (`id`)
                )$charset_collate;";
        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }


    public static function debug($message)
    {
        ContloLogger::write_log("DEBUG", $message);
    }

    public static function info($message)
    {
        ContloLogger::write_log("INFO", $message);
    }

    public static function error($message)
    {
        ContloLogger::write_log("ERROR", $message);
    }

    private static function write_log($type, $message)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'contlo_logs';
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $log_status = get_option('logs_enabled');
        if ($count > 9999 || $log_status == 'DISABLED') {
            return;
        }

        $wpdb->insert(
            $table_name,
            array(
                'domain' => parse_url(get_home_url())['host'],
                'type' => $type,
                'date' => current_time('mysql', 1),
                'message' => $message,
            )
        );
    }


    public static function contlo_get_logs()
    {
        global $wpdb;
        return $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'contlo_logs order by id DESC');
    }

    public static function delete_logs()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'contlo_logs';
        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query($sql);
    }
}

?>