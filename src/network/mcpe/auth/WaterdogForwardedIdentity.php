<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\auth;

use function filter_var;
use function is_string;
use function preg_match;
use function strcmp;
use function strlen;
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_NO_PRIV_RANGE;
use const FILTER_VALIDATE_IP;

/**
 * WaterdogPE authenticates players at the edge and signs a downstream login.
 * The trust boundary is a single explicitly configured private IPv4 peer, NOT
 * the presence of the Waterdog_Auth claim. Never publish a backend using this mode.
 * @internal
 */
final class WaterdogForwardedIdentity{

	/**
	 * @param array<string, mixed> $claims
	 * @throws \InvalidArgumentException if the trusted proxy omits or sends invalid identity claims
	 */
	public static function read(string $peerIp, string|false $trustedIp, array $claims) : ?string{
		if(
			$trustedIp === false ||
			$trustedIp !== $peerIp ||
			filter_var($trustedIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false ||
			filter_var($trustedIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE) !== false
		){
			return null;
		}

		$xuid = $claims["Waterdog_XUID"] ?? null;
		if(
			($claims["Waterdog_Auth"] ?? null) !== true ||
			!is_string($xuid) ||
			preg_match('/^[1-9][0-9]{0,19}$/D', $xuid) !== 1 ||
			(strlen($xuid) === 20 && strcmp($xuid, "18446744073709551615") > 0)
		){
			throw new \InvalidArgumentException("Trusted WaterdogPE did not provide a valid authenticated Xbox identity");
		}

		return $xuid;
	}
}
