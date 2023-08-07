<?php
function get_aws_creds() {
    return array(
        'key' => getenv('ACCESS_KEY_ID'),
        'secret' => getenv('SECRET_ACCESS_KEY'),
        'region' => getenv('REGION')
    );
}

header('Content-Type: application/json');
echo json_encode(get_aws_creds());
?>
