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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class WaterdogForwardedIdentityTest extends TestCase{

	public function testTrustedProxyPreservesXuidAsString() : void{
		self::assertSame("2533274790123456", WaterdogForwardedIdentity::read("172.20.0.2", "172.20.0.2", [
			"Waterdog_Auth" => true,
			"Waterdog_XUID" => "2533274790123456"
		]));
	}

	/**
	 * @return list<array{string, string|false}>
	 */
	public static function untrustedPeers() : array{
		return [
			["172.20.0.2", false],
			["172.20.0.3", "172.20.0.2"],
			["172.20.0.2", "172.20.0.0/16"],
			["8.8.8.8", "8.8.8.8"],
			["127.0.0.1", "127.0.0.1"],
			["::1", "::1"]
		];
	}

	#[DataProvider("untrustedPeers")]
	public function testUntrustedPeerCannotForwardIdentity(string $peer, string|false $trusted) : void{
		self::assertNull(WaterdogForwardedIdentity::read($peer, $trusted, [
			"Waterdog_Auth" => true,
			"Waterdog_XUID" => "2533274790123456"
		]));
	}

	/**
	 * @return list<array{array<string, mixed>}>
	 */
	public static function invalidClaims() : array{
		return [
			[[]],
			[["Waterdog_XUID" => "2533274790123456"]],
			[["Waterdog_Auth" => false, "Waterdog_XUID" => "2533274790123456"]],
			[["Waterdog_Auth" => "true", "Waterdog_XUID" => "2533274790123456"]],
			[["Waterdog_Auth" => true]],
			[["Waterdog_Auth" => true, "Waterdog_XUID" => 2533274790123456]],
			[["Waterdog_Auth" => true, "Waterdog_XUID" => ""]],
			[["Waterdog_Auth" => true, "Waterdog_XUID" => "0"]],
			[["Waterdog_Auth" => true, "Waterdog_XUID" => "01234"]],
			[["Waterdog_Auth" => true, "Waterdog_XUID" => "-1234"]],
			[["Waterdog_Auth" => true, "Waterdog_XUID" => "1234\n"]],
			[["Waterdog_Auth" => true, "Waterdog_XUID" => "18446744073709551616"]]
		];
	}

	/**
	 * @param array<string, mixed> $claims
	 */
	#[DataProvider("invalidClaims")]
	public function testTrustedProxyInvalidClaimsAreRejected(array $claims) : void{
		$this->expectException(\InvalidArgumentException::class);
		WaterdogForwardedIdentity::read("172.20.0.2", "172.20.0.2", $claims);
	}

	public function testUnsigned64BitBoundary() : void{
		self::assertSame("18446744073709551615", WaterdogForwardedIdentity::read("172.20.0.2", "172.20.0.2", [
			"Waterdog_Auth" => true,
			"Waterdog_XUID" => "18446744073709551615"
		]));
	}
}
