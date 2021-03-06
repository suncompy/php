<?php
/**
* @todo : sphinx实时索引curd操作类
* @created :Wed Nov 10 10:38:27 CST 2010
* @email：blvming@gmail.com
* @website:http://www.sphinxsearch.org
* @author blvming
* @version 1.0
* @example 
*/

class SphinxRt
{
    private $_link; //sphinx 连接池
    protected $_field = array(); //当前索引的字段属性
    protected $_sql = array(); //sql表达式
    protected $queryStr = ''; //查询的sql

    public $rt = '' ; //當前索引
    public $error = ''; //最后的错误信息

    public $debug = false; //调试状态

    //构造函数
    public function __construct($config)
    {
        try {
            $this->_link = mysqli_connect("{$config['host']}:{$config['port']}");
            if(!$this->_link)
            {
                throw  new Exception('sphinx 实时索引服务器连接失败！');
            }
            if($config['rt'] !='')
            {
                $this->rt = $this->_sql['rt'] = $config['rt'];
            }
        }
        catch (Exception $e)
        {
            $this->error = $e->getMessage();          
        }
    }

    /**
      +----------------------------------------------------------
      * @todo 设置索引表
      * @access public 
      * @param param
      * @return void
      +----------------------------------------------------------
     */     
    public  function rt($rt)
    {
        $this->_sql['rt'] = $this->rt = $rt;
        return $this;
    }

    /**
      +----------------------------------------------------------
      * @todo where 匹配条件.注意:这里一定要主动加上where 关键词 不能出现这样的情况 where 1
      * @access public 
      * @param $where
      * @return void
      +----------------------------------------------------------
     */     
    public  function where($where)
    {
        $this->_sql['where'] = $where;
        return $this;
    }

    /**
         +----------------------------------------------------------
         * @todo limit
         * @access public 
         * @param param
         * @return void
         +----------------------------------------------------------
        */     
    public  function limit($limit)
    {
        $this->_sql['limit'] = $limit;
        return $this;
    }

    /**
            +----------------------------------------------------------
            * @todo option 评分权值设定等
            * @access public 
            * @param param
            * @return void
            +----------------------------------------------------------
           */     
    public  function option($option)
    {
        $this->_sql['option'] = $option;
        return $option;
    }
    /**
            +----------------------------------------------------------
            * @todo field
            * @access public 
            * @param param
            * @return void
            +----------------------------------------------------------
           */     
    public  function field($field)
    {
        $this->_sql['field'] = $field;
        return $this;
    }

    /**
               +----------------------------------------------------------
               * @todo order
               * @access public 
               * @param param
               * @return void
               +----------------------------------------------------------
              */     
    public  function order($order)
    {
        $this->_sql['order'] = $order;
        return $this;
    }
    /**
  +----------------------------------------------------------
  * @todo group
  * @access public 
  * @param param
  * @return void
  +----------------------------------------------------------
 */     
    public  function group($group,$withGroup)
    {
        $this->_sql['group'] = $group;
        if($group)
        {
            $this->_sql['withGroup'] = $withGroup;
        }
        return $this;
    }

    /**
      +----------------------------------------------------------
      * @todo 检索数据，并对数据进行排序，过滤，评分设定等
      * @access public 
      * @param param
      * @example select * from rt where match('keyword') group by gid WITHIN GROUP ORDER BY @weight DESC
      *          order by gid desc limit 0,1 option ranker=bm25,max_matches=3,field_weights=(title=10,content=3);
      * @return array
      +----------------------------------------------------------
     */     
    public  function search()
    {
        $field = $where = $groupSql = $orderSql = $limitSql = $optionSql = '';
        //排序
        if(!empty($this->_sql['order']))
        {
            $orderSql = ' ORDER BY '.$this->_sql['order'];
        }
        //分组聚合
        if(!empty($this->_sql['group']))
        {
            $groupSql = ' GROUP BY '.$this->_sql['group'];
            //组内排序
            if ($this->_sql['withGroup']!='') {
                $groupSql .= ' WITHIN GROUP ORDER BY '.$this->_sql['withGroup'];
            }
        }
        //附加选项
        if(!empty($this->_sql['option']))
        {
            $optionSql = ' OPTION '.$this->_sql['option'];
        }
        //数量限制
        if(!empty($this->_sql['limit']))
        {
            $limitSql = 'LIMIT '.$this->_sql['limit'];
        }
        //字段
        if(empty($this->_sql['field']))
        {
            $field = '*';
        }
        else
        {
            $field= $this->_sql['field'];
        }

        if(!empty($this->_sql['where']))
        {
            $where = 'WHERE '.$this->_sql['where'];
        }
        else
        {
            $where ='';
        }

        $this->queryStr = sprintf("SELECT %s FROM %s %s %s %s %s %s",$field,$this->_sql['rt'],$where,$groupSql,$orderSql,$limitSql,$optionSql);

        $rs = $this->query();

        if($rs)
        {
            $resArr = array();
            $resArr['result'] = array();
            while ($row = mysqli_fetch_assoc($rs)) {
                $resArr['result'][] = $row;
            }
            $resArr['meta'] = $this->getMeta();
            return $resArr;
        }
        return false;
    }


    /**
      +----------------------------------------------------------
      * @todo 添加索引，注意，这里的添加并未考虑并发操作，可能在sphinx端会出现id冲突
      * @access public 
      * @param mixed $data  插入的数据
      * @return bool
      +----------------------------------------------------------
     */     
    public  function insert($data,$lastId=0)
    {
        if(!empty($data))
        {
            if($lastId===0)
            {
                $lastId = $this->getLastId();
            }

            $fields = $values = '';
            foreach ($data as $k=>$v) {
                $fields .= ','.$k;
                $values .= ",'".$v."'";
            }
            $this->queryStr = "insert into {$this->_sql['rt']} (id".$fields.") values ($lastId {$values})";
            return $this->query();
        }
        $this->error = '插入数据不能为空';
        return false;
    }
    /**
      +----------------------------------------------------------
      * @todo 批量插入数据
      * @access public 
      * @param mixed $datas
      * @param boolean $asStr 是否使用逗号分隔的方式一次性插入
      * @return void
      +----------------------------------------------------------
     */     
    public  function insertAll($datas,$asStr=true)
    {
        if(!empty($datas))
        {
            $fields = 'id'; //字段
            $values ='';    //值
            $lastId = $this->getLastId();
            $i = 0;
            foreach ($datas as $k=>$v) {
                //一次性插入数据，格式化
                if($asStr)
                {
                    $values .=',('.($i+$lastId);
                    foreach ($v as $kk=>$va) {
                        //属性字段
                        if($i==0)
                        {
                            $fields .= ','.$kk;
                        }
                        $values .= ",'".$va."'";
                    }
                    $i++;
                    $values .= ')';
                }
                else
                {
                    $this->insert($v,$lastId);
                }
            }

            //批量数据sql格式化
            if($asStr)
            {
                $values = ltrim($values,',');
                $this->queryStr = sprintf("insert into {$this->_sql['rt']}(%s) values %s",$fields,$values);
                return $this->query();
            }

        }
        else
        {
            $this->error = '无效数据！';
            return false;
        }

    }


    /**
     +----------------------------------------------------------
     * @todo 更新索引数据
     * @access public 
     * @param mixed $data 要更新的数据
     * @param int  $id  更新条件id
     * @return bool
     +----------------------------------------------------------
     */     
    public  function update($data,$id)
    {
        if(!empty($data) || $id>0)
        {
            $set = [];
            foreach ($data as $k=>$v) {
                $set[] = $k.' = '.$v;
            }
            $set_str = join(' , ', $set);
            $this->queryStr = "update {$this->_sql['rt']} set $set_str where id = $id";
            return $this->query();
        }
        $this->error = '无效更新数据！';
        return false;

    }

    /**
      +----------------------------------------------------------
      * @todo 条件删除索引，如，根据外部id删除
      * @access public 
      * @param $condition
      * @return void
      +----------------------------------------------------------
     */     
    public  function delBy($condition)
    {
        $rs = $this->where($condition)->search();

        if($rs)
        {
            foreach ($rs as $v) {
                if($v['id']) $idArr[] = $v['id'];
            }
            $this->delete($idArr);
            return true;
        }
        return false;
    }


    /**
    +----------------------------------------------------------
    * @todo 删除索引数据
    * @access public 
    * @param mixed $id 
    * @return void
    +----------------------------------------------------------
   */     
    public  function delete($id)
    {
        if(is_array($id) && count($id)>=1)
        {
            $id_str = join(',', $id);
            $this->queryStr = sprintf("delete from %s where id in (%s)",$this->_sql['rt'],$id_str);
            $rs = $this->query();
        }
        else
        {
            $this->queryStr = sprintf("delete from %s where id=%d",$this->_sql['rt'],$id);
            $rs =  $this->query();
        }

        return $rs;
    }
    /**
      +----------------------------------------------------------
      * @todo 清空表
      * @access public 
      * @return bool
      +----------------------------------------------------------
     */     
    public  function truncate()
    {
        $lastId = $this->getLastId();
        for ($i=1;$i<=$lastId;$i++)
        {
            $this->delete($i);
        }
        return true;
    }


    /**
      +----------------------------------------------------------
      * @todo 获取总记录
      * @access public 
      * @param param
      * @return void
      +----------------------------------------------------------
     */     
    public  function countAll()
    {
        $this->queryStr = "SELECT * FROM {$this->_sql['rt']} limit 0";
        $this->query();
        $meta = $this->getMeta();
        if($meta)
        {
            return  $meta['total_found'];
        }
        return false;
    }

    /**
      +----------------------------------------------------------
      * @todo 获取当前最大值id，实现如mysql的auto_increment功能
      * @access public 
      * @param param
      * @return void
      +----------------------------------------------------------
     */     
    public  function getLastId()
    {
        $this->queryStr = "select * from {$this->_sql['rt']} order by id desc limit 1";
        $rs = $this->query();

        //若存在值，则取最大id的值，否则为1
        $row = mysqli_fetch_assoc($rs);
        if($row)
        {
            $lastId = $row['id']+1;
        }
        return $lastId?$lastId:1;

    }

    /**
         +----------------------------------------------------------
         * @todo 获取查询状态值
         * @access protected 
         * @param param
         * @return array();
         +----------------------------------------------------------
        */     
    protected  function getMeta()
    {
        $metaSql = "show meta";
        $meta = mysqli_query($this->_link, $metaSql);
        while ($row = mysqli_fetch_assoc($meta)) {
            $metaArr[$row['Variable_name']] = $row['Value'];
        }
        return $metaArr;
    }

    /**
      +----------------------------------------------------------
      * @todo 根据id获取记录
      * @access public 
      * @param int $id
      * @return array
      +----------------------------------------------------------
     */     
    public  function getById($id)
    {
        if($id>0)
        {
            $sql = "'select * from $this->rt where id=".$id;
            $rs = mysqli_query($this->_link, $sql);
            $row = mysqli_fetch_assoc($rs);
            return $row;
        }
        return false;
    }

    /**
      +----------------------------------------------------------
      * @todo 获取索引的字段值，前提条件是索引服务器中必须至少一个值，暂时没有api显示可以直接像mysql 的语句 desc table 来获取索引的字段;
      * @access public
      * @param param
      * @return void
      +----------------------------------------------------------
     */     
    public  function _getField($rt)
    {
        $rt = $rt?$rt:$this->rt;
        $this->queryStr = "select * from {$rt} limit 1";
        $res = $this->query();
        if($res)
        {
            $row = mysqli_fetch_assoc($res);
            $field = array_keys($row);
            unset($field[1]); //去掉weight，这个字段是sphinx的权重值
            return $field;
        }
        else
        {
            $this->error = '实时索引'.$rt.'没有任何记录，无法获取索引字段';
            return false;
        }
    }

    /**
      +----------------------------------------------------------
      * @todo mysql查询
      * @access public 
      * @param param
      * @return void
      +----------------------------------------------------------
     */     
    public  function query($sql = '')
    {
        if($sql == '')
        {
            $sql = $this->queryStr;
        }
        if(!$this->_link) $this->triggerDebug($this->debug);
        
        $rs = mysqli_query($this->_link, $sql);
        if(!$rs) $this->error = mysqli_error($this->_link);
        $this->triggerDebug($this->debug);
        return $rs;
    }

    /**
      +----------------------------------------------------------
      * @todo 获取错误信息
      * @access public       
      * @return string
      +----------------------------------------------------------
     */     
    public  function getError()
    {
        return $this->error;
    }

    /**
         +----------------------------------------------------------
         * @todo 获取最后的sql语句
         * @access public 
         * @param param
         * @return string
         +----------------------------------------------------------
        */     
    public  function getLastSql()
    {
        return $this->queryStr;
    }

    /**
      +----------------------------------------------------------
      * @todo 触发错误信息
      * @access public 
      * @param param
      * @return void
      +----------------------------------------------------------
     */     
    public  function triggerDebug($debugMode=false)
    {
        if($debugMode)
        {
            $debugInfo = debug_backtrace();

            $errorStr = 'file：'.$debugInfo[0]['file'];
            $errorStr .= '<br />line:'.$debugInfo[0]['line'];
            $errorStr .= '<br />sql:'.$debugInfo[0]['object']->queryStr;
            $errorStr .= '<br />error:<font color="red">'.$debugInfo[0]['object']->error.'</font>';

            if($debugInfo[0]['object']->error!='')die($errorStr);
            echo ($errorStr);
        }
        return ;
    }

}
?>