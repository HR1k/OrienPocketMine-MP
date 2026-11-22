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

namespace pocketmine\network\mcpe;

use PHPUnit\Framework\TestCase;
use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\ServerJoinInformation;

final class StartGameManagedChatPreviewTest extends TestCase{

	public function testOriginalNgJoinInfoConstructorAndProtocolBytes() : void{
		$info = new ServerJoinInformation(null, null, null);

		$legacy = new ByteBufferWriter();
		$info->write($legacy, ProtocolInfo::PROTOCOL_1_26_60);
		self::assertSame("\x00\x00\x00", $legacy->getData());

		$preview = new ByteBufferWriter();
		$info->write($preview, ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		self::assertSame("\x00\x00\x00\x00", $preview->getData());
		self::assertNull($info->getManagedChatEnabled());

		$decoded = ServerJoinInformation::read(new ByteBufferReader($preview->getData()), ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		self::assertNull($decoded->getManagedChatEnabled());
	}

	public function testNgDoesNotAdvertiseUnsupportedPreviewVersion() : void{
		self::assertSame(2225, ProtocolInfo::CURRENT_PROTOCOL);
		self::assertNotContains(2259, ProtocolInfo::ACCEPTED_PROTOCOL);
	}
}
