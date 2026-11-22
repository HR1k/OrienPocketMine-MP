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
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\SetPassengerOfBlockPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\EmoteType;
use pocketmine\network\mcpe\protocol\types\PassengerOfBlockData;
use Ramsey\Uuid\Uuid;
use function strlen;
use function substr;

final class PassengerAndSoundPreviewIntegrationTest extends TestCase{

	public function testPassengerBlockWireLayoutAndOldVersion() : void{
		$data = new PassengerOfBlockData(
			new BlockPosition(1, 64, 1),
			new Vector3(0.0, 0.0, 0.0),
			0.0,
			0.0,
			EmoteType::STANDING,
			12345678,
			Uuid::fromString("11223344-5566-7788-99aa-bbccddeeff00")
		);
		$packet = SetPassengerOfBlockPacket::create(111, $data);
		$old = new ByteBufferWriter();
		$packet->encode($old, ProtocolInfo::PROTOCOL_1_26_60);
		$new = new ByteBufferWriter();
		$packet->encode($new, ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		self::assertSame(strlen($old->getData()) + 24, strlen($new->getData()));
		self::assertSame($old->getData(), substr($new->getData(), 0, strlen($old->getData())));

		$decoded = new SetPassengerOfBlockPacket();
		$decoded->decode(new ByteBufferReader($new->getData()), ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		self::assertSame(12345678, $decoded->getPassengerData()?->getBlockType());
		self::assertSame("11223344-5566-7788-99aa-bbccddeeff00", $decoded->getPassengerData()?->getPackUuid()?->toString());
	}

	public function testPlaySoundPreviewOptionsDoNotChangeLegacyEncoding() : void{
		$packet = PlaySoundPacket::create("random.click", 0.0, 64.0, 0.0, 1.0, 1.0, 0, false, null, null);
		$packet->setStartDelaySeconds(1.5);
		$packet->setAttachedEntityRuntimeId(1234);
		$old = new ByteBufferWriter();
		$packet->encode($old, ProtocolInfo::PROTOCOL_1_26_60);
		$new = new ByteBufferWriter();
		$packet->encode($new, ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		self::assertSame($old->getData(), substr($new->getData(), 0, strlen($old->getData())));

		$decoded = new PlaySoundPacket();
		$decoded->decode(new ByteBufferReader($new->getData()), ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		self::assertEqualsWithDelta(1.5, $decoded->getStartDelaySeconds(), 0.00001);
		self::assertSame(1234, $decoded->getAttachedEntityRuntimeId());
	}

	public function testPreview2259StillNotEnabled() : void{
		self::assertSame(2225, ProtocolInfo::CURRENT_PROTOCOL);
		self::assertNotContains(2259, ProtocolInfo::ACCEPTED_PROTOCOL);
	}
}
