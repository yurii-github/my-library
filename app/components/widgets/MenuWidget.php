<?php
/*
 * My Book Library
 *
 * Copyright (C) 2014-2019 Yurii K.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses
 */

namespace app\components\widgets;

use yii\base\Widget;

class MenuWidget extends Widget
{
	public $items = [];

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		if (!is_array($this->items)) {
			throw new \yii\base\Exception('MenuWidget: items must be array');
		}

		$items = [];

		foreach ($this->items as $item) {
		  if (is_array($item)) {
		    $items[] = $item;
		  }
		}

		return $this->render('menu', ['items' => $items]);
	}
}
