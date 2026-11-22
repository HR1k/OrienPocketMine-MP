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

namespace pocketmine\network\mcpe\convert;

use PHPUnit\Framework\TestCase;
use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pocketmine\block\VanillaBlocks;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;

final class PreviewHashedBlockItemDropTest extends TestCase{

	public function test2259BlockItemDropDescriptorMatchesServerInventoryStack() : void{
		$converter = TypeConverter::getInstance(ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		$serverStack = $converter->coreItemStackToNet(VanillaBlocks::STONE()->asItem());
		self::assertSame(-2144268767, $serverStack->getBlockRuntimeId());

		$writer = new ByteBufferWriter();
		CommonTypes::putNetworkItemStackDescriptor($writer, ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26, new ItemStackWrapper(1, $serverStack));
		$clientStack = CommonTypes::getNetworkItemStackDescriptor(new ByteBufferReader($writer->getData()), ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26)->getItemStack();

		self::assertSame($serverStack->getId(), $clientStack->getId());
		self::assertSame($serverStack->getMeta(), $clientStack->getMeta());
		self::assertSame($serverStack->getCount(), $clientStack->getCount());
		self::assertSame($serverStack->getBlockRuntimeId(), $clientStack->getBlockRuntimeId());
	}

	public function test2225KeepsOriginalItemDescriptorEncoding() : void{
		$converter = TypeConverter::getInstance(ProtocolInfo::PROTOCOL_1_26_60);
		$serverStack = $converter->coreItemStackToNet(VanillaBlocks::STONE()->asItem());
		$writer = new ByteBufferWriter();
		CommonTypes::putNetworkItemStackDescriptor($writer, ProtocolInfo::PROTOCOL_1_26_60, new ItemStackWrapper(1, $serverStack));
		$clientStack = CommonTypes::getNetworkItemStackDescriptor(new ByteBufferReader($writer->getData()), ProtocolInfo::PROTOCOL_1_26_60)->getItemStack();
		self::assertSame($serverStack->getBlockRuntimeId(), $clientStack->getBlockRuntimeId());
	}
}
