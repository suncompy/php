<?php
namespace app\crowd\controller;

use app\crowd\model\CrowdBack;
use think\Request;
use app\common\controller\NoAuth;

class CrowdHome extends NoAuth
{

    /**
     * @function auction 详情
     * @author ljx
     */
    public function detail($id_param = 0)
    {
        $id_request = valueRequest('id', 0);
        $id = $id_request ? $id_request : $id_param;
        if (! empty($id)) {
            $model = new CrowdBack();
            $wdata = array(
                'id' => $id,
                'accesstoken' => $this->request->header('accesstoken')
            );
            $rowData = $model->getRow($wdata, array(), false);
            if (! empty($rowData)) {
                return array(
                    'data' => $rowData
                );
            } else {
                $this->_error('详情不存在,请稍后再试', 500);
            }
        } else {
            $this->_error('参数id不能为空', 400);
        }
    }
}


















