<?php
header('Access-Control-Allow-Origin: *');

$envs = [
    "PAA_DB_HOSTNAME" => getenv("USE_LOCALHOST_DB") === "1" ? "localhost" : "postgres"
    ,"PAA_DB_PORT" =>  getenv("USE_LOCALHOST_DB") === "1" ? "5532" : "5432"
    ,"PAA_DB_DATABASE" => "paa_dev"
    ,"PAA_DB_USERNAME" => "paa"
    ,"PAA_DB_PASSWORD" => "mysoooosecretpassword"
    ,"PAA_DB_ADMIN_DATABASE" => "postgres"
    ,"PAA_DB_ADMIN_USERNAME" => "postgres"
    ,"PAA_DB_ADMIN_PASSWORD" => "mysecretpassword"
    ,"PAA_DEFAULT_USER_PASSWORD" => "secretpassword"
    ,"PAA_SESSION_SAVE_HANDLER" => "redis"
    ,"PAA_SESSION_SAVE_PATH" => "tcp://redis:6379"
    ,"REDIS_URL"             => "tcp://redis:6379"
    ,"SENDGRID_API_KEY"      => "<put-api-key-here>"
    ,"FROM_EMAIL"            => "noreply@portactivity-staging.fi"
    ,"ERROR_EMAIL"           => "" // put email here to get emails from errors
    ,"BASE_URL"              => "http://localhost:3000"
    #JUST FAKE KEYS HERE, USE ONLY WHILE DEVELOPING
    ,"PRIVATE_KEY_JSON"      => json_encode(file_get_contents(__DIR__ . "/../../private.pem"))
    ,"PUBLIC_KEY_JSON"       => json_encode(file_get_contents(__DIR__ . "/../../public.pem"))
    ,"VIS_AGENT_API_URL"     => "http://host.docker.internal:8888/api.php/"
    ,"NAMESPACE"             => "gavle"
    ,"LANGUAGE"              => "en"
    ,"PORT_CALL_TEMPLATE_RAUMA" =>
        implode(";", explode("\n", file_get_contents(__DIR__ . "/port_call_template_rauma.txt")))
    ,"PORT_CALL_TEMPLATE_GAVLE" =>
        implode(";", explode("\n", file_get_contents(__DIR__ . "/port_call_template_gavle.txt")))
    ,"RTA_POINT_COORDINATES" => "12.345,67.89"
    ,"VIS_SERVICE_INSTANCE_URN" => 'urn:mrn:stm:service:instance:sma:vis:dummy'
    ,"TIMESTAMP_CLUSTER_AGENT_API_URL" => ""
    ,"VIS_PORT_NAME" => "Port of Gävle"
    ,"VIS_PORT_UNLOCODE" => "SEGVX"
    ,"RTA_POINT_LOCATION_NAME" => "pilot boarding area"
    ,"VISIBLE_UNLOCODES" => "SEGVX,SEKAS"
    ,"VIS_SYNC_POINT_LAT" => 60.70978
    ,"VIS_SYNC_POINT_LON" => 17.62353
    ,"VIS_SYNC_POINT_RADIUS" => 1852.0
    ,"JIT_ETA_FORM_URL" => "http://localhost:8888/rta/"
    ,"PORT_DEFAULT_TIME_ZONE" => "Europe/Helsinki"
    ,"REDIS_RECENT_LOGS_URL" => "tcp://redis:6379"
    ,"LOG_LIMIT" => "500000"
    ,"MAP_DEFAULT_COORDINATES" => "60.699083,17.328354"
    ,"MAP_DEFAULT_ZOOM" => "6"
    ,"PORT_CALL_MASTER_SOURCE" => ""
    ,"PORT_CALL_MASTER_START_BUFFER_DURATION" => "PT6H"
    ,"PORT_CALL_MASTER_END_BUFFER_DURATION" => "PT12H"
];

foreach ($envs as $k => $v) {
    putenv("$k=$v");
};
