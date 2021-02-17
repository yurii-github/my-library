<?php
/*
 * My Book Library
 *
 * Copyright (C) 2014-2021 Yurii K.
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

namespace App\Actions;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Translation\Translator;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Validator;

/**
 * @property-read Translator $translator
 * @property-read Manager $db
 */
trait WithValidateTrait
{
    /**
     * @param array $post
     * @param array $rules
     * @throws \Illuminate\Validation\ValidationException
     * @return array
     */
    protected function validate(array $post, array $rules): array
    {
        $validator = new Validator($this->translator, $post, $rules);
        $validator->setPresenceVerifier(new DatabasePresenceVerifier($this->db->getDatabaseManager()));
        return $validator->validate();
    }
}