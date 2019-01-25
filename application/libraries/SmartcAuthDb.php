<?php
/**
 * Name:    Smartc Auth
 * Author:  Dewa Andhika Putra
 *          dewaandhika18@gmail.com
 *          @dwzzzl
 *
 * Created:  12.01.2018
 *
 * Description:  Smartc Auth adalah sebuah library authentication yang terinspirasi dari library Ion Auth dengan
 * beberapa penambahan fitur.
 *
 * Special Thanks to: Ben Edmunds
 *
 * Requirements: PHP5 or above
 *
 * @package    Smartc-Auth-CodeIgniter
 * @author     Dewa Andhika Putra
 * @link       http://github.com/dwzzzl/Smartc-Auth-CodeIgniter
 * @version    0.1.0
 */

defined('BASEPATH') OR exit('No direct script access allowed');

namespace dwzzzl\SmartcAuth;

class SmartcAuthDb {
    /**
     * Set
     * 
     * @var array
     */
    public $_auth_set = array();

    /**
     * Where
     * 
     * @var array
     */
    public $_auth_where = array();

    /**
     * Where in
     * 
     * @var array
     */
    public $_auth_where_in = array();

    /**
     * Select
     * 
     * @var array
     */
    public $_auth_select = array();

    /**
     * Limit
     * 
     * @var string
     */
    public $_auth_limit = NULL;

    /**
     * Offset
     * 
     * @var string
     */
    public $_auth_offset = NULL;

    /**
     * Order by
     * 
     * @var string
     */
    public $_auth_order_by = NULL;

    /**
     * Order
     * 
     * @var string
     */
    public $_auth_order = NULL;

    /**
     * Group by
     * 
     * @var array
     */
    public $_auth_group_by = array();

    /**
     * Response
     * 
     * @var string
     */
    protected $response = NULL;

    public function __construct()
    {
        $this->load->database();
    }

    /**
     * @param string $select
     * @return static
     */
    public static function select($select)
    {
        $this->_auth_select[] = $select;
        return $this;
    }

    /**
     * @param array|string $where 
     * @param null|string $value 
     * @return static
     */
    public static function where($where, $value = NULL)
    {
        if( ! is_array($where))
        {
            $where = array($where => $value);
        }
        array_push($this->_auth_where, $where);
        return $this;
    }

    /**
     * @param string $where 
     * @param array $value 
     * @return static
     */
    public static function where_in($where, $value = array())
    {
        $this->_auth_where_in[] = array('key' => $where, 'value' => $value);
        return $this;
    }

    /**
     * @param array|string $column 
     * @param null|string $value 
     * @return static
     */
    public static function set($column, $value = NULL)
    {
        if( ! is_array($column))
        {
            $column = array($column => $value);
        }
        array_push($this->_auth_set, $column);
        return $this;
    }

    /**
     * @param int $limit 
     * @return static
     */
    public static function limit($limit)
    {
        $this->_auth_limit = $limit;
        return $this;
    }

    /**
     * @param int $offset 
     * @return static
     */
    public static function offset($offset)
    {
        $this->_auth_offset = $offset;
        return $this;
    }

    /**
     * @param string $group_by
     * @return static
     */
    public static function group_by($group_by)
    {
        $this->_auth_group_by[] = $group_by;
        return $this;
    }

    /**
     * @param string $column 
     * @param string $order 
     * @return static
     */
    public static function order_by($column, $order = 'desc')
    {
        $this->_auth_order_by = $column;
        $this->_auth_order = $order;
        return $this;
    }

    /**
     * @return object|mixed
     */
    public static function row()
    {
        return $this->response->row();
    }

    /**
     * @return array|mixed
     */
    public static function row_array()
    {
        return $this->response->row_array();
    }

    /**
     * @return mixed
     */
    public static function result()
    {
        return $this->response->result();
    }

    /**
     * @return array|mixed
     */
    public static function result_array()
    {
        return $this->response->result_array();
    }

    /**
     * @return void
     */
    public static function free_result()
    {
        $this->response->free_result();
    }

    /**
     * Get
     * @param string $table 
     * @param int|null $limit 
     * @param int|null $offset 
     * 
     * @return static
     */
    public static function get($table, $limit = NULL, $offset = NULL)
    {
        if (isset($this->_auth_select) && ! empty($this->_auth_select))
        {
            foreach ($this->_auth_select as $select) 
            {
                $this->db->select($select);
            }

            // Reset the variable
            $this->_auth_select = array();
        }
        else
        {
            // default selects
            $this->db->select("{$table}.*");
        }

        if (isset($this->_auth_where) && ! empty($this->_auth_where))
        {
            foreach ($this->_auth_where as $where) 
            {
                $this->db->where($where);
            }
            $this->_auth_where = array();
        }

        if (isset($this->_auth_where_in) && ! empty($this->_auth_where_in))
        {
            foreach ($this->_auth_where_in as $where) 
            {
                $this->db->where_in($where['key'], $where['value']);
            }
            $this->_auth_where_in = array();
        }

        if ( ! empty($this->_auth_group_by))
        {
            $this->db->group_by($group_by);
            $this->_auth_group_by = array();
        }

        if (isset($limit))
        {
            $this->_auth_limit = $limit;
        }

        if (isset($offset))
        {
            $this->_auth_offset = $offset;
        }

        if (isset($this->_auth_limit) && isset($this->_auth_offset))
        {
            $this->db->limit($this->_auth_limit);
            $this->db->offset($this->_auth_offset);

            $this->_auth_limit  = NULL;
            $this->_auth_offset = NULL;
        }
        elseif (isset($this->_auth_limit)) 
        {
            $this->db->limit($this->_auth_limit);

            $this->_auth_limit = NULL;
        }

        if (isset($this->_auth_order_by) && isset($this->_auth_order))
        {
            $this->db->order_by($this->_auth_order_by, $this->_auth_order);

            $this->_auth_order    = NULL;
            $this->_auth_order_by = NULL;
        }

        $this->response = $this->db->get($table);
        return $this;
    }

    /**
     * Add bulk data
     * 
     * @param string $table 
     * @param array $data 
     * 
     * @return int|bool Return the affected rows or FALSE on failure
     */
    public static function add_bulk($table, $data = array())
    {
        // Initialize batch variable
        $batch = array();

        if (isset($this->_auth_set) && ! empty($this->_auth_set))
        {
            foreach ($this->_auth_set as $set_data) 
            {
                $batch[] = $set_data;
            }
            $this->_auth_set = array();
        }

        // If there is value in $data, we use it
        if ( ! empty($data))
        {
            $batch = $data;
        }

        $this->db->trans_begin();

        $affected_rows = $this->db->insert_batch($table, $batch);

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        unset($batch);

        return $affected_rows;
    }

    /**
     * Add
     * 
     * @param string $table 
     * @param array  $data
     * @return int|bool
     */
    public static function add($table, $data = NULL)
    {
        if (isset($this->_auth_set) && ! empty($this->_auth_set))
        {
            foreach ($this->_auth_set as $set_data) 
            {
                $this->db->set($set_data);
            }
            $this->_auth_set = array();
        }

        if ( ! empty($data)) 
        {
            $this->db->set($data);
        }

        // Begin the transaction
        $this->db->trans_begin();

        $this->db->insert($table);

        $id = $this->db->insert_id();

        // If there is no insert_id, then the return value will be affected_rows()
        if($id === 0)
        {
            $id = $this->db->affected_rows();
        }

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return $id;
    }

    /**
     * update
     * 
     * @param string $table 
     * @return bool
     */
    public static function update($table)
    {
        if (isset($this->_auth_set) && ! empty($this->_auth_set))
        {
            foreach ($this->_auth_set as $data) 
            {
                $this->db->set($data);
            }
            $this->_auth_set = array();
        }

        if (isset($this->_auth_where) && ! empty($this->_auth_where))
        {
            foreach ($this->_auth_where as $where) 
            {
                $this->db->where($where);
            }
            $this->_auth_where = array();
        }

        if (isset($this->_auth_where_in) && ! empty($this->_auth_where_in))
        {
            foreach ($this->_auth_where_in as $where) 
            {
                $this->db->where_in($where['key'], $where['value']);
            }
            $this->_auth_where_in = array();
        }

        $this->db->trans_begin();

        $this->db->update($table);

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return TRUE;
    }

    /**
     * Delete
     * 
     * @param string $table 
     * @return bool
     */
    public static function delete($table)
    {
        if (isset($this->_auth_where) && ! empty($this->_auth_where))
        {
            foreach ($this->_auth_where as $where) 
            {
                $this->db->where($where);
            }
            $this->_auth_where = array();
        }

        if (isset($this->_auth_where_in) && ! empty($this->_auth_where_in))
        {
            foreach ($this->_auth_where_in as $where) 
            {
                $this->db->where_in($where['key'], $where['value']);
            }
            $this->_auth_where_in = array();
        }

        $this->db->trans_begin();

        $this->db->delete($table);

        if($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return TRUE;
    }
}