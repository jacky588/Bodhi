<?php

class Mysql
{

    private $host;

    private $user;

    private $password;

    private $charset;

    private $database;

    /**
     * 新建数据库连接对象，测试数据库连接
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $charset
     * @param string $database
     */
    function __construct($host, $user, $password, $charset, $database)
    {
        $link = mysqli_connect($host, $user, $password) or die('数据库连接失败<br />ERROR ' . mysqli_connect_errno() . ':' . mysqli_connect_error());
        $char = mysqli_set_charset($link, $charset) or die('charset设置错误，请输入正确的字符集名称<br />ERROR ' . mysqli_errno($link) . ':' . mysqli_error($link));
        $db = mysqli_select_db($link, $database) or die('未找到数据库，请输入正确的数据库名称<br />ERROR ' . mysqli_errno($link) . ':' . mysqli_error($link));
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->charset = $charset;
        $this->database = $database;
        mysqli_close($link);
    }

    /**
     * 连接数据库
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $charset
     * @param string $database
     * @return object 连接标识符
     */
    private function connect($host, $user, $password, $charset, $database)
    {
        $link = mysqli_connect($this->host, $this->user, $this->password);
        mysqli_set_charset($link, $this->charset);
        mysqli_select_db($link, $this->database);
        return $link;
    }

    /**
     * 增加数据
     *
     * @param array $data
     * @param string $table
     * @return boolean
     */
    public function insert($data, $table)
    {
        $link = $this->connect($this->host, $this->user, $this->password, $this->charset, $this->database);
        $keys = join(',', array_keys($data));
        $vals = "'" . join("','", array_values($data)) . "'";
        $query = "INSERT INTO {$table}({$keys}) VALUES({$vals})";
        $result = mysqli_query($link, $query) or die('插入数据出错，请检查！<br />ERROR ' . mysqli_errno($link) . ':' . mysqli_error($link));
        if ($result) {
            $id = mysqli_insert_id($link);
        } else {
            $id = false;
        }
        mysqli_close($link);
        return $id;
    }

    /**
     * 删除数据
     *
     * @param string $table
     * @param string $where
     * @return boolean
     */
    public function delete($table, $where = null)
    {
        $link = $this->connect($this->host, $this->user, $this->password, $this->charset, $this->database);
        $where = $where ? ' WHERE ' . $where : '';
        $query = "DELETE FROM {$table}{$where}";
        $result = mysqli_query($link, $query) or die('删除数据出错，请检查！<br />ERROR ' . mysqli_errno($link) . ':' . mysqli_error($link));
        if ($result) {
            $row = mysqli_affected_rows($link);
        } else {
            $row = false;
        }
        mysqli_close($link);
        return $row;
    }

    /**
     * 修改数据
     *
     * @param array $data
     * @param string $table
     * @param string $where
     * @return boolean
     */
    public function update($data, $table, $where = null)
    {
        $link = $this->connect($this->host, $this->user, $this->password, $this->charset, $this->database);
        $set = '';
        foreach ($data as $key => $val) {
            $set .= "{$key}='{$val}',";
        }
        $set = trim($set, ',');
        $where = $where ? ' WHERE ' . $where : '';
        $query = "UPDATE {$table} SET {$set}{$where}";
        $result = mysqli_query($link, $query) or die('数据修改错误，请检查！<br />ERROR ' . mysqli_errno($link) . ':' . mysqli_error($link));
        if ($result) {
            $row = mysqli_affected_rows($link);
        } else {
            $row = false;
        }
        mysqli_close($link);
        return $row;
    }

    /**
     * 查询指定记录
     *
     * @param string $query
     * @param string $result_type
     * @return array|boolean
     */
    public function select_one($query, $result_type = MYSQLI_ASSOC)
    {
        $link = $this->connect($this->host, $this->user, $this->password, $this->charset, $this->database);
        $result = mysqli_query($link, $query) or die('查询语句错误，请检查！<br />ERROR ' . mysqli_errno($link) . ':' . mysqli_error($link));
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result, $result_type);
        } else {
            $row = false;
        }
        mysqli_free_result($result);
        mysqli_close($link);
        return $row;
    }

    /**
     * 查询所有记录
     *
     * @param string $query
     * @param string $result_type
     * @return array|boolean
     */
    public function select_all($query, $result_type = MYSQLI_ASSOC)
    {
        $link = $this->connect($this->host, $this->user, $this->password, $this->charset, $this->database);
        // $query = "SELECT * FROM {$table}";
        $result = mysqli_query($link, $query) or die('查询语句错误，请检查！<br />ERROR ' . mysqli_errno($link) . ':' . mysqli_error($link));
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_array($result, $result_type)) {
                $rows[] = $row;
            }
        } else {
            $rows = false;
        }
        mysqli_free_result($result);
        mysqli_close($link);
        return $rows;
    }

    /**
     * 得到表中记录数
     *
     * @param string $table
     * @return number|boolean
     */
    public function get_total_rows($table)
    {
        $link = $this->connect($this->host, $this->user, $this->password, $this->charset, $this->database);
        $query = "SELECT COUNT(*) AS totalRows FROM {$table}";
        $result = mysqli_query($link, $query);
        if ($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
        } else {
            $row['totalRows'] = false;
        }
        mysqli_close($link);
        return $row['totalRows'];
    }
}