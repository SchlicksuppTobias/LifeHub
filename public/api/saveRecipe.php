<?php

header('Content-Type: application/json');

echo json_encode(['success' => true, 'message' => var_dump($_POST)]);