<?php

/*
 * Test bootstrap for the pinned NG BedrockProtocol fork.
 * Composer does not autoload vendor dependency test fixtures.
 */

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/nethergamesmc/bedrock-protocol/tests/phpunit/TestPacket.php';
