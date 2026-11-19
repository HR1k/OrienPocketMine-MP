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
use pocketmine\inventory\CreativeInventory;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

/**
 * Regression test for unmapped blockstates (e.g. minecraft:bamboo_fence)
 * crashing the server while preparing the creative inventory on login.
 */
class CreativeInventoryConversionTest extends TestCase{

	/**
	 * @doesNotPerformAssertions
	 */
	public function testAllCreativeEntriesAndGroupIconsConvertForProtocol2225() : void{
		$converter = TypeConverter::getInstance(ProtocolInfo::PROTOCOL_1_26_60);

		foreach(CreativeInventory::getInstance()->getAllEntries() as $entry){
			$converter->coreItemStackToNet($entry->getItem());
			$group = $entry->getGroup();
			if($group !== null){
				$converter->coreItemStackToNet($group->getIcon());
			}
		}
	}
}
