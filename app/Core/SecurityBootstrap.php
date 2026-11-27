<?php

declare(strict_types=1);

require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/AccessControl.php';
require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Services/AccountSecurityService.php';
require_once __DIR__ . '/../Services/WorkflowSecurityService.php';

Security::sendHeaders();
Security::startSecureSession();
