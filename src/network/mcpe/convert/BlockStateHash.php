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

use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use pocketmine\nbt\tag\CompoundTag;
use function ksort;
use function ord;
use function strlen;
use const SORT_STRING;

/**
 * Mojang's stable block runtime ID: FNV-1a 32 over a little-endian NBT compound
 * containing the block name and its alphabetically ordered state properties.
 * The world-data "version" tag is not part of the hash.
 */
final class BlockStateHash{
	private function __construct(){}

	public static function hash(BlockStateData $state) : int{
		if($state->getName() === "minecraft:unknown"){
			return -2;
		}
		$properties = $state->getStates();
		ksort($properties, SORT_STRING);
		$states = CompoundTag::create();
		foreach($properties as $name => $value){
			$states->setTag($name, $value);
		}

		$nbt = CompoundTag::create()
			->setString("name", $state->getName())
			->setTag("states", $states);
		$bytes = (new LittleEndianNbtSerializer())->write(new TreeRoot($nbt));
		$hash = 0x811c9dc5;
		for($i = 0, $length = strlen($bytes); $i < $length; ++$i){
			$hash = (($hash ^ ord($bytes[$i])) * 0x01000193) & 0xffffffff;
		}
		return $hash >= 0x80000000 ? $hash - 0x100000000 : $hash;
	}
}
