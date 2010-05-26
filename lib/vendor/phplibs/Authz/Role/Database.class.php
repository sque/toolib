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


require_once dirname(__FILE__) . '/../Role.class.php';

class Authz_Role_Database implements Authz_Role
{
    protected $options;
    
    protected $name;
    
    public function __construct($name, $options)
    {
        $this->name = $name;
        $this->options = $options;
    }
    
    protected function has_parents_ability()
    {
        if (($this->options === null) || ($this->options['parents_query'] === null))
            return false;
        return true;
    }
    
    public function get_name()
    {
        return $this->name;
    }
        
    public function get_parents()
    {
        if (! $this->has_parents_ability())
            return array();
            
        $result = $this->options['parents_query']->execute($this->get_name());

        $parents = array();
        foreach($result as $record)
        {   
            $parent_name = $record->{$this->options['parent_name_field']};
            if ($this->options['parent_name_filter_func'])
                $parent_name = call_user_func($this->options['parent_name_filter_func'], $parent_name);
            $parents[] = new Authz_Role_Database(
                $parent_name , null);
        }

        return $parents;
    }

    public function has_parent($parent)
    {      
        foreach($this->get_parents() as $p)
            if ($p->get_name() == $parent)
                return true;
        return false;
    }   
}
?>
