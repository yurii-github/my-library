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

/**
 * @var \yii\web\View $this
 * @var string $content
 * @var bool $result
 */

use yii\helpers\Html;

?>
<style>
    pre.migration {
        background-color: rgba(42, 53, 207, 0.3);;
        padding: 0.5rem;
    }
</style>
<div style="margin: auto; width: 60%">
    <?php if ($result): ?>
        It looks like migration was successful. You can go to <?php echo Html::a('My Library', ['site/index']); ?>.
    <?php else: ?>
        It looks like migration has failed. Please see migration details below for any vital information!
    <?php endif; ?>
    <hr/>
    <pre class="migration">
        <?php echo $content; ?>
    </pre>
    <hr/>
    <p style="font-size: smaller">
        NOTE! You can always run migration process manually by executing next command in console:
    <pre class="migration" style="font-size: smaller;">./yii migrate/up</pre>
    </p>
</div>

