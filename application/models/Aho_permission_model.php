<?php
/**
 * Name:    Aho Auth
 * Author:  Dewa Andhika Putra
 *          dewaandhika18@gmail.com
 *          @dwzzzl
 *
 * Created:  2019.01.25
 *
 * Requirements: PHP 7.1 or above
 *
 * @package    aho-auth
 * @author     Dewa Andhika Putra
 * @link       http://github.com/dwzzzl/aho-auth
 * @since      Version 0.1.0
 */
 
defined('BASEPATH') or exit('No direct access script allowed');

class Aho_permission_model extends CI_Model {

    /**
     * Tables
     * 
     * @var array
     */
    public $tables = array();

    /**
     * User identity column
     * 
     * @var string
     */
    public $identity_column;

    /**
     * @var array
     */
    public $join = array();

    public function __construct()
    {
        parent::__construct();

        $this->load->library('message');

        $this->tables = $this->config->item('tables', 'aho_config');
        $this->identity_column = $this->config->item('identity_column', 'aho_config');
        $this->join = $this->config->item('join', 'aho_config');
    }

    /**
     * Get permissions list
     *
     * @return CI_DB_Result
     */
    public function permissions()
    {
        return $this->db->get($this->tables['permissions']);
    }

    /**
     * Add permission(s)
     *
     * @param   array|string    $name   Permission name
     * @return  mixed           Insert id or affected rows. But FALSE on failure.
     */
    public function add_permission($name, $desc = NULL)
    {
        $insert = is_array($name) ? 'insert_batch' : 'insert';
        $data = is_array($name) ? $name : ['name' => $name, 'description' => $desc];

        return $this->db
                    ->{$insert}($this->tables['permissions'], $data)
                    ->affected_rows() > 0;
    }

    /**
     * Remove permission(s)
     *
     * @param   array|int   $permission_ids Permission IDs
     * @return  bool
     */

    public function remove_permission($permission_id)
    {
        $where = is_array($permission_id) ? 'where_in' : 'where';
        
        return $this->db
                    ->{$where}('permission_id', $group_id)
                    ->delete($this->tables['permissions']);
    }

    /**
     * Change group permissions
     *
     * @param   int         $group_id       Group ID
     * @param   array|int   $permission_ids Permission IDs. 
     * @return  bool
     */

    public function allow_group($group_id, $permission_id)
    {
        $insert = is_array('name') ? 'insert_batch' : 'insert'; 
        $data = array();
        if(is_array($permission_id))
        {
            $batch = array();
            foreach ($permission_id as $pid) 
            {
                $batch['group_id'] = $group_id;
                $batc['permission_id'] = $pid;
                array_push($data, $batch);
            }
        }
        else
        {
            $data = array(
                'group_id' => $group_id,
                'permission_id' => $permission_id
            )
        }
        $this->remove_group_permissions($group_id);

        
        return $this->db
                    ->{$insert}($this->tables['group_permissions'], $batch)
                    ->affected_rows() > 0;
    }

    /**
     * Remove permission from group
     *
     * @param int $user_id  Group ID
     * @param int $group_id Permission IDs. 
     * @return  bool
     */

    public function deny_group($group_id, $permission_id)
    {
        $this->db
             ->where(array(
                'group_id' => $group_id, 
                'permission_id' => $group_id
             ));
        return $this->db->delete($this->tables['group_permissions']);
    }

    /**
     * Remove all permissions from group
     *
     * @param   int     $user_id    User ID 
     * @return  bool
     */

    public function remove_group_permissions($group_id)
    {
        return $this->db->where('group_id', $group_id)->delete($this->tables['group_permissions']);
    }
}
