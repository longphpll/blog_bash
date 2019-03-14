<?php
/**
 * Created by PhpStorm.
 * User: userYang
 * Date: 2018/8/13
 * Time: 11:16
 */

namespace app\improve\controller;

use app\improve\model\BaseDb as BaseDbModel;
use app\improve\model\UserDb;
use Db;
use app\improve\validate\BaseValidate;
use app\improve\model\MarkerDb;
use think\Cache;
use Redis;

use base_frame\RedisBase;
use tool\Communal;
use tool\BaseDb;
use tool\Error;

/*无人机模块*/

class MarkerController extends RedisBase
{
    /*已改 Lxl*/
    //无人机上报数据
    function addMarker()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'region|区域' => 'require|number',
            'data|数据'   => 'require'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $marker_array = [];//定义一个标记数组
        if (Helper::lsWhere($data, 'data') && stripos($data['data'], '_') !== false) {
            $attr                                 = explode('_', $data['data']);
            $marker_array['phone']                = substr($attr[19], 0, 11);
            $marker_array['drone_lat']            = $attr[0]; //飞机经度
            $marker_array['drone_lng']            = $attr[1]; //飞机纬度
            $marker_array['drone_height']         = $attr[2];//飞机高度
            $marker_array['target_lat_lng']       = $attr[3];//目标点经纬度
            $marker_array['target_size']          = $attr[4];  //目标点个数
            $marker_array['marker_time']          = $attr[5]; //标记时间
            $marker_array['weather']              = $attr[6]; //天气情况
            $marker_array['pm25']                 = $attr[7]; //pm2.5
            $marker_array['pm10']                 = $attr[8]; //pm10
            $marker_array['tvoc']                 = $attr[9]; //TVOC
            $marker_array['jiaquan']              = $attr[10]; //甲醛
            $marker_array['wendu']                = $attr[11];  //温度
            $marker_array['shidu']                = $attr[12];  //湿度
            $marker_array['side_length']          = $attr[13];  //边长
            $marker_array['area']                 = $attr[14];  //面积
            $marker_array['round_length']         = $attr[15]; //周长
            $marker_array['type']                 = $attr[16]; //标注类型 0点  1线 2面
            $marker_array['marker_position_desc'] = $attr[17]; //标记点位置描述
            $marker_array['marker_desc']          = $attr[18];//描述
            $marker_array['img_name']             = $attr[19]; //图片名
            $marker_array['yaw']                  = $attr[20]; //方向角
            $marker_array['gim_bal_yaw']          = $attr[21]; //云台方向角
            $marker_array['gim_bal_pitch']        = $attr[22]; //云台角度
            $marker_array['drone_type']           = $attr[23]; //飞机型号
            $marker_array['status']               = 1;
            $marker_array['create_time']          = date('Y-m-d H:i:s');
            $marker_array['update_time']          = $marker_array['create_time'];

            if (Helper::lsWhere($data, 'region')) $marker_array['region'] = $data['region'];
            if (Helper::lsWhere($marker_array, 'phone')) {
                $user = UserDb::userInfo($marker_array['phone']);

                if (!empty($user)) {
                    if (Helper::lsWhere($marker_array, 'region')) $marker_array['region'] = $user[1]['region']; //区域编号
                    $marker_array['user_name']   = $user[1]['name']; //用户名称
                    $marker_array['region_name'] = $user[1]['region_name'];
                }
            }
        }

        if (Helper::lsWhere($data, 'image')) {
            $image = $data['image'];
        }
        unset($data['images']);

        $marker = Db::table('improve_marker')->insertGetId($marker_array);

        //图片上传
        if (!empty($image)) {
            $path = Db::table('improve_uploads')->where('id', $image)->field('path')->find();

            if (empty($path)) return Communal::return_Json(Error::error('未找到相应数据'));

            $record = [
                'path' => $path['path']
            ];
            $a      = Db::table('improve_marker')->where('id', $marker)->update($record);
            if ($a < 1) return Communal::return_Json(Error::error('图片添加失败'));
        }

        $dbRes = empty($marker) ? Error::error('添加错误') : Communal::success('添加成功,记录id为：' . $marker);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    // 无人机上报数据详情
    function getMarker()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'id' => 'require|number|min:1',
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $result = MarkerDb::query($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    // 无人机上报数据列表
    function listMarker()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'per_page'        => 'require|number|max:50|min:1',
            'current_page'    => 'require|number|min:1',
            'start_time|开始时间' => 'dateFormat:Y-m-d',
            'end_time|结束时间'   => 'dateFormat:Y-m-d'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $result = MarkerDb::ls($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    //查询用户轨迹记录
    public function getUavrLocus()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'playback_time|回放时间' => 'require|dateFormat:Y-m-d'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $result = MarkerDb::getUvaLocusQueryList($data);

        return Communal::return_Json($result);
    }

}