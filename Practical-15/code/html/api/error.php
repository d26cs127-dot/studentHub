<?php
function api_error(string $message,int $status=400): void { http_response_code($status); echo json_encode(['success'=>false,'message'=>$message]); exit; }
