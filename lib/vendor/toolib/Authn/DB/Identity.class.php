<?php
/*
 *  This file is part of PHPLibs <http://phplibs.kmfa.net/>.
 *  
 *  Copyright (c) 2010 < squarious at gmail dot com > .
 *  
 *  PHPLibs is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  PHPLibs is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with PHPLibs.  If not, see <http://www.gnu.org/licenses/>.
 *  
 */


namespace toolib\Authn\DB;

require_once __DIR__ . '/../Identity.class.php';

/**
 * @brief Implementation of identity for \\toolib\\Authn\\DB\\Backend.
 */
class Identity implements \toolib\Authn\Identity
{
    private $record;

    private $id;

    private $authority;

    /**
     * @brief The object is constructed by \toolib\Authn\DB\Backend
     * @param $id The unique id of this identity.
     * @param $authority The Authn_Backend_DB that created this identity.
     * @param $record The database record of this user.
     */
    public function __construct($id, Backend $authority, $record)
    {
        $this->id = $id;
        $this->record = $record;
        $this->authority = $authority;
    }

    public function id()
    {
        return $this->id;
    }

    /**
     * @brief Reset password of this identity
     * @param $password The new password to be set for this identity
     * @return
     *  - @b true If the password was changed succesfully.
     *  - @b false on any kind of error.
     */
    public function resetPassword($password)
    {   
        return $this->authority->resetPassword($this->id(), $password);
    }
    
    /**
     * @brief Get the database record of this user
     * @return \toolib\DB\Record
     */
    public function getRecord()
    {
        return $this->record;
    }
    
}
