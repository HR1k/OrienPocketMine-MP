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
use pocketmine\data\bedrock\ItemTagDowngrader;
use pocketmine\data\bedrock\ItemTagToIdMap;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

final class PreviewProtocolMappingsTest extends TestCase{

	public function testPreviewMappingsAreAvailableForDirectServerSmokeTest() : void{
		self::assertSame(2225, ProtocolInfo::CURRENT_PROTOCOL);
		self::assertContains(2259, ProtocolInfo::ACCEPTED_PROTOCOL);
		self::assertSame(291, ItemTranslator::getItemSchemaId(2259));
		self::assertSame(
			ItemTypeDictionaryFromDataHelper::loadFromProtocolId(2225),
			ItemTypeDictionaryFromDataHelper::loadFromProtocolId(2259)
		);
		self::assertSame(
			BlockTranslator::loadFromProtocolId(2225),
			BlockTranslator::loadFromProtocolId(2259)
		);
	}

	public function testPreview2259ItemTagsAndRecipeDowngraderInitialize() : void{
		// Regression: a 2259 player previously crashed when the crafting cache constructed ItemTagDowngrader.
		$oldTags = ItemTagToIdMap::getInstance(ProtocolInfo::PROTOCOL_1_26_60);
		$previewTags = ItemTagToIdMap::getInstance(ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		self::assertSame([], $oldTags->diff($previewTags));
		self::assertSame([], $previewTags->diff($oldTags));

		$downgrader = ItemTagDowngrader::getInstance(ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26);
		self::assertSame(ProtocolInfo::PROTOCOL_1_26_70_PREVIEW_26, $downgrader->getProtocolId());
	}
}
