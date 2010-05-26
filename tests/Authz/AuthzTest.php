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


require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) .  '/../path.inc.php';

class Authz_AuthzTest extends PHPUnit_Framework_TestCase
{
    public function roleFeeder()
    {
        $roles = new Authz_Role_FeederInstance();
        $roles->add_role('@game');
        $roles->add_role('@video');
        $roles->add_role('@user', array('@game', '@video'));
        $roles->add_role('@web-user');
        $roles->add_role('@web-admin', '@web-user');
        $roles->add_role('@fs-admin');
        $roles->add_role('@logger');
        $roles->add_role('@admin', array('@user', '@web-admin', '@fs-admin'));
        return $roles;
    }
    
    public function testSetGet()
    {
        $list = Authz::get_resource_list();
        $this->assertType('Authz_ResourceList', $list);
        $list2 = new Authz_ResourceList();
        Authz::set_resource_list($list2);
        $this->assertSame(Authz::get_resource_list(), $list2);
        $this->assertNotSame($list, $list2);

        
        $roles1 = $this->roleFeeder();
        $roles2 = $this->roleFeeder();
        $this->assertNotSame($roles1, $roles2);
        
        Authz::set_role_feeder($roles1);
        $this->assertSame(Authz::get_role_feeder(), $roles1);

        Authz::set_role_feeder($roles2);
        $this->assertSame(Authz::get_role_feeder(), $roles2);
    }
    
    /**
     * @depends testSetGet
     */
    public function testIsAllowed()
    {
        Authz::set_resource_list($list = new Authz_ResourceList());
        $dir = $list->add_resource('directory');
        Authz::allow('directory', null, 'read');
        Authz::deny('directory', null, 'write');
        Authz::allow('directory', 'admin', 'write');
        Authz::allow('directory', 'user', 'list');

        $file = $list->add_resource('file', 'directory');
        Authz::allow('file', 'user', 'execute');
        Authz::deny('file', null, 'list');
        
        Authz::allow(array('file', '/') , null, 'list');

        $this->assertFalse(Authz::is_allowed('directory', null, 'unknown'));
        $this->assertTrue(Authz::is_allowed('directory', null, 'read'));
        $this->assertFalse(Authz::is_allowed('directory', null, 'write'));
        $this->assertFalse(Authz::is_allowed('directory', null, 'execute'));
        $this->assertFalse(Authz::is_allowed('directory', null, 'list'));
        $this->assertTrue(Authz::is_allowed('directory', 'admin', 'write'));
        $this->assertFalse(Authz::is_allowed('directory', 'admin', 'execute'));
        $this->assertFalse(Authz::is_allowed('directory', 'admin', 'list'));
        $this->assertFalse(Authz::is_allowed('directory', 'user', 'execute'));
        $this->assertTrue(Authz::is_allowed('directory', 'user', 'list'));
        
        $this->assertFalse(Authz::is_allowed('file', null, 'unknown'));
        $this->assertTrue(Authz::is_allowed('file', null, 'read'));
        $this->assertFalse(Authz::is_allowed('file', null, 'write'));
        $this->assertFalse(Authz::is_allowed('file', null, 'execute'));
        $this->assertFalse(Authz::is_allowed('file', null, 'list'));
        $this->assertTrue(Authz::is_allowed('file', 'admin', 'write'));
        $this->assertFalse(Authz::is_allowed('file', 'admin', 'execute'));
        $this->assertFalse(Authz::is_allowed('file', 'admin', 'list'));
        $this->assertTrue(Authz::is_allowed('file', 'user', 'execute'));
        $this->assertFalse(Authz::is_allowed('file', 'user', 'list'));
        $this->assertFalse(Authz::is_allowed(array('file', 'unknown'), 'user', 'list'));
        $this->assertTrue(Authz::is_allowed(array('file', '/'), 'user', 'list'));
    }
    
    public function testGetResource()
    {
        Authz::set_resource_list($list = new Authz_ResourceList());
        $dir = $list->add_resource('directory');
        Authz::allow('directory', null, 'read');
        Authz::deny('directory', null, 'write');
        Authz::allow('directory', 'admin', 'write');
        Authz::allow('directory', 'user', 'list');

        $file = $list->add_resource('file', 'directory');
        Authz::allow('file', 'user', 'execute');
        Authz::deny('file', null, 'list');
        $root = $file->get_instance('/');
        
        $this->assertSame($file, Authz::get_resource('file'));
        $this->assertSame($dir, Authz::get_resource('directory'));
        
        $this->assertSame($root, Authz::get_resource(array('file', '/')));
        
        $this->assertFalse(Authz::get_resource(array('unknown', '/')));
        $this->assertFalse(Authz::get_resource('unknown'));
    }
}
?>
