<?php

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
