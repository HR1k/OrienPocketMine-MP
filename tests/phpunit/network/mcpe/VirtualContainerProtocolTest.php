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
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\VirtualContainerCloseCause;
use pocketmine\network\mcpe\protocol\types\inventory\VirtualContainerCloseKind;
use pocketmine\network\mcpe\protocol\VirtualContainerClosePacket;
use pocketmine\network\mcpe\protocol\VirtualContainerOpenPacket;

/**
 * Exercises the locked Preview BedrockProtocol dependency without enabling Preview logins.
 */
final class VirtualContainerProtocolTest extends TestCase{

	public function testPreviewPacketsRegisteredWithoutChangingCurrentProtocol() : void{
		self::assertSame(2225, ProtocolInfo::CURRENT_PROTOCOL);
		self::assertContains(2259, ProtocolInfo::ACCEPTED_PROTOCOL);
		self::assertInstanceOf(VirtualContainerOpenPacket::class, PacketPool::getInstance()->getPacketById(366));
		self::assertInstanceOf(VirtualContainerClosePacket::class, PacketPool::getInstance()->getPacketById(367));
	}

	public function testVirtualContainerOpenRoundTrip() : void{
		$packet = VirtualContainerOpenPacket::create(7, 300, "Preview", [new ItemStackWrapper(0, ItemStack::null())]);
		$writer = new ByteBufferWriter();
		$packet->encode($writer, ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		$decoded = new VirtualContainerOpenPacket();
		$decoded->decode(new ByteBufferReader($writer->getData()), ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		self::assertSame(7, $decoded->getContainerId());
		self::assertSame(300, $decoded->getSessionId());
		self::assertSame("Preview", $decoded->getTitle());
		self::assertCount(1, $decoded->getSlots());
	}

	public function testVirtualContainerCloseRoundTrip() : void{
		$packet = VirtualContainerClosePacket::create(300, VirtualContainerCloseKind::CLOSED, VirtualContainerCloseCause::USER_CLOSED);
		$writer = new ByteBufferWriter();
		$packet->encode($writer, ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		$decoded = new VirtualContainerClosePacket();
		$decoded->decode(new ByteBufferReader($writer->getData()), ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		self::assertSame(300, $decoded->getSessionId());
		self::assertSame(VirtualContainerCloseKind::CLOSED, $decoded->getKind());
		self::assertSame(VirtualContainerCloseCause::USER_CLOSED, $decoded->getCause());
	}

	public function testOlderProtocolRejectsVirtualContainerPacket() : void{
		$packet = VirtualContainerOpenPacket::create(7, 1, "Not supported", [new ItemStackWrapper(0, ItemStack::null())]);
		$writer = new ByteBufferWriter();
		$this->expectException(PacketDecodeException::class);
		$packet->encode($writer, ProtocolInfo::PROTOCOL_1_26_60);
	}
}
