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
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

final class PreviewBlockHashTest extends TestCase{

	public function testKnownStoneHash() : void{
		self::assertSame(-2144268767, BlockStateHash::hash(BlockStateData::current("minecraft:stone", [])));
	}

	public function testUnknownBlockUsesReservedHash() : void{
		self::assertSame(-2, BlockStateHash::hash(BlockStateData::current("minecraft:unknown", [])));
	}

	public function testPreviewTranslatorUsesHashesButLegacyUsesPaletteIndices() : void{
		$legacy = BlockTranslator::loadFromProtocolId(ProtocolInfo::PROTOCOL_1_26_60);
		$preview = BlockTranslator::loadFromProtocolId(ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);

		self::assertFalse($legacy->usesHashedNetworkIds());
		self::assertTrue($preview->usesHashedNetworkIds());
		self::assertNotSame($legacy, $preview);

		$stone = BlockStateData::current("minecraft:stone", []);
		$paletteId = $legacy->getBlockStateDictionary()->lookupStateIdFromData($stone);
		self::assertNotNull($paletteId);
		self::assertSame($paletteId, $legacy->stateDataToNetworkId($stone));
		self::assertSame(-2144268767, $preview->stateDataToNetworkId($stone));
		self::assertSame("minecraft:stone", $preview->networkIdToCurrentStateData(-2144268767)?->getName());
	}

	public function testPreviewHashesRoundTripForPaletteEntries() : void{
		$preview = BlockTranslator::loadFromProtocolId(ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		$states = $preview->getBlockStateDictionary()->getStates();
		self::assertNotSame([], $states);
		foreach($states as $index => $entry){
			if($index > 128){
				break;
			}
			$state = $entry->generateStateData();
			$hash = $preview->stateDataToNetworkId($state);
			self::assertNotNull($hash);
			self::assertTrue($state->equals($preview->networkIdToCurrentStateData($hash) ?? throw new \RuntimeException("Block lookup failed")));
		}
	}
}
