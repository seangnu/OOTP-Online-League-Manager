<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Load_tables_permissions extends Migration {
	
	public function up() 
	{
		$prefix = $this->db->dbprefix;
		
		$data = array(
			'name'        => 'OOTPOL.Site.Manage' ,
			'description' => 'Manage OOTP Online Settings and Content' 
		);
		$this->db->insert("{$prefix}permissions", $data);
		
		$permission_id = $this->db->insert_id();
		
		// change the roles which don't have any specific login_destination set
		$this->db->query("INSERT INTO {$prefix}role_permissions VALUES(1, ".$permission_id.")");
		
		// News Articles
        $this->dbforge->add_field('`id` int(11) NOT NULL AUTO_INCREMENT');
        $this->dbforge->add_field('`name` varchar(255) NOT NULL');
        $this->dbforge->add_field("`required` tinyint(1) NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("`modified_on` int(11) NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("`modified_by` int(11) NOT NULL DEFAULT '-1'");

        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('sql_tables');

        $this->db->query("INSERT INTO {$prefix}sql_tables VALUES(1, 'cities',0,0,0)");
        $this->db->query("INSERT INTO {$prefix}sql_tables VALUES(2, 'games',0,0,0)");

        // News Articles
        $this->dbforge->add_field('`id` int(11) NOT NULL AUTO_INCREMENT');
        $this->dbforge->add_field("`table_id` tinyint(1) NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("`version_min` int(11) NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("`version_max` int(11) NOT NULL DEFAULT '0'");

        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('list_tables_versions');

        $this->db->query("INSERT INTO {$prefix}list_tables_versions VALUES(1, 1, 10, 100)");
        $this->db->query("INSERT INTO {$prefix}list_tables_versions VALUES(2, 2, 10, 100)");

        $data = array(
			'name'        => 'OOTPOL.SQL.Manage' ,
			'description' => 'Manage SQL Loading and Settings' 
		);
		$this->db->insert("{$prefix}permissions", $data);
		
		$permission_id = $this->db->insert_id();
		
		// change the roles which don't have any specific login_destination set
		$this->db->query("INSERT INTO {$prefix}role_permissions VALUES(1, ".$permission_id.")");
		
		// Team owners
        $this->dbforge->add_field('`id` int(11) NOT NULL AUTO_INCREMENT');
        $this->dbforge->add_field("`league_id` int(11) NOT NULL DEFAULT '100'");
        $this->dbforge->add_field("`team_id` int(11) NOT NULL DEFAULT '-1'");
        $this->dbforge->add_field("`user_id` int(11) NOT NULL DEFAULT '-1'");
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('teams_owners');
		
	}
	
	//--------------------------------------------------------------------
	
	public function down() 
	{
		$prefix = $this->db->dbprefix;
		
		$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name = 'OOTPOL.Site.Manage'");
		foreach ($query->result_array() as $row)
		{
			$permission_id = $row['permission_id'];
			$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
		}
		//delete the role
		$this->db->query("DELETE FROM {$prefix}permissions WHERE (name = 'OOTPOL.Site.Manage')");

		$this->dbforge->drop_table('sql_tables');
        $this->dbforge->drop_table('list_tables_versions');
        $this->dbforge->drop_table('teams_owners');

        $query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name = 'OOTPOL.SQL.Manage'");
		foreach ($query->result_array() as $row)
		{
			$permission_id = $row['permission_id'];
			$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
		}
		//delete the role
		$this->db->query("DELETE FROM {$prefix}permissions WHERE (name = 'OOTPOL.SQL.Manage')");

	}
	
	//--------------------------------------------------------------------
	
}