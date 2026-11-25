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
use pocketmine\block\VanillaBlocks;
use pocketmine\crafting\ExactRecipeIngredient;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\recipe\StringIdMetaItemDescriptor;
use function array_unshift;
use function array_values;

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

	public function testPreviewHashCacheSurvivesPaletteMutation() : void{
		// Use an isolated dictionary: NG's BlockPalette replaces these fields via reflection.
		$source = BlockTranslator::loadFromProtocolId(ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		$dictionary = clone $source->getBlockStateDictionary();
		$translator = new BlockTranslator($dictionary, \pocketmine\world\format\io\GlobalBlockStateHandlers::getSerializer(), true);
		$stone = BlockStateData::current("minecraft:stone", []);
		$stoneHash = $translator->stateDataToNetworkId($stone);
		self::assertNotNull($stoneHash);
		$oldIndex = $dictionary->lookupStateIdFromData($stone);
		self::assertNotNull($oldIndex);
		$oldMeta = $translator->getMetaFromNetworkId($stoneHash);
		self::assertSame("minecraft:stone", $translator->networkIdToCurrentStateData($stoneHash)?->getName());

		$custom = new BlockStateDictionaryEntry("orien:palette_test", [], 7, null);
		$states = array_values($dictionary->getStates());
		array_unshift($states, $custom);
		$updated = new BlockStateDictionary($states);
		foreach(["states", "stateDataToStateIdLookup", "idMetaToStateIdLookupCache"] as $propertyName){
			$property = new \ReflectionProperty($dictionary, $propertyName);
			$property->setValue($dictionary, $property->getValue($updated));
		}
		(new \ReflectionProperty($translator, "fallbackStateId"))->setValue($translator, $dictionary->lookupStateIdFromData($translator->getFallbackStateData()));

		self::assertSame($oldIndex + 1, $dictionary->lookupStateIdFromData($stone));
		self::assertSame($stoneHash, $translator->stateDataToNetworkId($stone));
		self::assertSame("minecraft:stone", $translator->networkIdToCurrentStateData($stoneHash)?->getName());
		self::assertSame($oldMeta, $translator->getMetaFromNetworkId($stoneHash));
		$customHash = $translator->stateDataToNetworkId($custom->generateStateData());
		self::assertNotNull($customHash);
		self::assertSame("orien:palette_test", $translator->networkIdToCurrentStateData($customHash)?->getName());
		self::assertSame(7, $translator->getMetaFromNetworkId($customHash));
	}

	public function testPreviewRecipesResolveHashedBlockIdsBackToPaletteMeta() : void{
		$ingredient = new ExactRecipeIngredient(VanillaBlocks::STONE()->asItem());
		$legacy = TypeConverter::getInstance(ProtocolInfo::PROTOCOL_1_26_60)->coreRecipeIngredientToNet($ingredient)->getDescriptor();
		$preview = TypeConverter::getInstance(ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26)->coreRecipeIngredientToNet($ingredient)->getDescriptor();

		self::assertInstanceOf(StringIdMetaItemDescriptor::class, $legacy);
		self::assertInstanceOf(StringIdMetaItemDescriptor::class, $preview);
		self::assertSame($legacy->getId(), $preview->getId());
		self::assertSame($legacy->getMeta(), $preview->getMeta());
	}
}
