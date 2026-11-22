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
use pocketmine\network\mcpe\protocol\TextPacket;
use function strlen;

final class TextPacketQueuePreviewTest extends TestCase{

	public function testTranslatedPopupAddsQueueByteOnlyForPreview() : void{
		$packet = TextPacket::translatedPopup("orien.notice", ["New"]);
		$packet->queue = true;

		$legacyWriter = new ByteBufferWriter();
		$packet->encode($legacyWriter, ProtocolInfo::PROTOCOL_1_26_60);
		$previewWriter = new ByteBufferWriter();
		$packet->encode($previewWriter, ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);

		self::assertSame(strlen($legacyWriter->getData()) + 1, strlen($previewWriter->getData()));

		$decoded = new TextPacket();
		$decoded->decode(new ByteBufferReader($previewWriter->getData()), ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		self::assertTrue($decoded->queue);
		self::assertSame("orien.notice", $decoded->message);
		self::assertSame(["New"], $decoded->parameters);
	}

	public function testRawChatIsUnchanged() : void{
		$packet = TextPacket::raw("hello");

		$legacyWriter = new ByteBufferWriter();
		$packet->encode($legacyWriter, ProtocolInfo::PROTOCOL_1_26_60);
		$previewWriter = new ByteBufferWriter();
		$packet->encode($previewWriter, ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);

		self::assertSame($legacyWriter->getData(), $previewWriter->getData());
	}

	public function testPreviewAcceptedForTesting() : void{
		self::assertSame(2225, ProtocolInfo::CURRENT_PROTOCOL);
		self::assertContains(2259, ProtocolInfo::ACCEPTED_PROTOCOL);
	}
}
