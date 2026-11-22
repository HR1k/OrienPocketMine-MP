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
use pocketmine\network\mcpe\protocol\ChatPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\chat\ChatFormatting;
use pocketmine\network\mcpe\protocol\types\chat\SignedChat;

final class ChatProtocolPreviewTest extends TestCase{

	public function testSignedChatPacketRoundTrip() : void{
		$signed = new SignedChat("key", "sender", "id", "hello", "", "Player", "signature");
		$packet = ChatPacket::create($signed, "room", new ChatFormatting("[VIP] ", null, null, null));
		$out = new ByteBufferWriter();
		$packet->encode($out, ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		$decoded = new ChatPacket();
		$decoded->decode(new ByteBufferReader($out->getData()), ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);

		self::assertSame("room", $decoded->getRoomId());
		self::assertSame("id", $decoded->getSignedChat()->getMessageId());
		self::assertSame("signature", $decoded->getSignedChat()->getSignature());
		self::assertSame("[VIP] ", $decoded->getFormatting()?->getNamePrefix());
	}

	public function testChatIsAvailableForPreviewClients() : void{
		self::assertInstanceOf(ChatPacket::class, PacketPool::getInstance()->getPacketById(361));
		self::assertContains(2259, ProtocolInfo::ACCEPTED_PROTOCOL);
	}
}
