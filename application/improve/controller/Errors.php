<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 10:52
 */

namespace app\improve\controller;

class Errors
{
    const PARAMS_ERROR = "params_error";
    const DB_ERROR = "db_error";
    const FILE_ROOT_PATH = ROOT_PATH . DS . 'public' . DS . 'file';
    const USER_ADD = "tel already exists";
    const SAVE_FILE_ERROR = "save file error";
    const IS_NOT_I = "u are not task founder";
    const TASK_STATUS_ERROR_THREE = "task status is not 2";
    const VERSION_CODE_IS_NULL = "version_code is null";
    const NEW_VERSION_NOT_FIND = "new version not find";	
    //任务管理 
    const TASK_ASSGINER_ERROR = [false, ["任务指派数量错误", "task assginer error"]];
    const TASK_STATUS_ERROR_TWO = [false, ["该任务正在执行中", "task status is  1"]];
    const TASL_STATUS_NO_RELEASE = [false, ["该任务不在发布中", "task status is not 0"]];
    const TASL_STATUS_NO_IMPLEMENT = [false, ["该任务不在执行中", "task status is not 1"]];
    const TASL_STATUS_REFUSE = [false, ["该任务已被拒绝", "task status has refused"]];
    const NO_RELEASE = [false, ["您不是任务发布人", "you are not founder"]];
    const NO_INCIDENT = [false, ["您不是任务接收人", "you are not receiver"]];
	const ASSIGN_ERROR = [false, ["您不是任务指派人", "u are not be assign"]];
    const TASK_EXPIRED = [false, ["任务已过期", "task expired"]];
    const TASK_CANCELED = [false, ["任务已取消", "task expired"]];
    const NON_EXPIRED_TASK = [false, ["该任务不是过期任务", "task expired"]];
    const TASK_HAS_RELEASE = [false, ["该任务已发布,无法重新发布", "task has release"]];
    const TASK_HAS_CANCELED = [false, ["该任务已取消，无法重新发布", "task has canceled"]];
    const TASK_IS_IMPLEMENT = [false, ["该任务正在发布中，无法重新发布", "task is implement"]];
    const TASK_HAS_RECEIVERD = [false, ["该任务已被接收", "task has receiverd"]]; 
    const TASK_HAS_FINISHED = [false, ["该任务已完成,无法重新发布", "task has finished"]]; 
    const CANCEL_ERROR = [false, ["取消任务失败", "cancel error"]];
    const REFUSE_ERROR = [false, ["拒绝任务失败", "refuse error"]];
    const FEEDBACK_ERROR = [false, ["反馈任务失败", "cancel error"]];
	//文件
    const FILE_SAVE_ERROR = [false, ["文件保存失败", "file_save_error"]];
    const NO_FILE = [false, ["找不到文件", "no file"]];
    const HAS_NO_FILE = [false, ["上传的文件未找到", "has no file"]];
    const MAX_FILE_SIZE = [false, ["文件大小请不要超过100M", "max fileSize 100M"]];
	const FILE_TYPE_ERROR = [false, ["文件格式错误"]];
    const ATTACH_NOT_FIND = [false, ["找不到附件", "attach not find"]];
    const FILE_NAME_HAS_EXIST = [false, ["请勿上传同名文件", "file name has exist"]];  
    //用户管理 
    const DATA_NOT_AUDIT = [false, ["没有待审核的数据", "data not audit"]];
    const DATA_NOT_REFUSED = [false, ["没有已拒绝的数据", "data not refused"]];
	//用户登录 
	const FORBIDDEN_STATUS = [false, ["该账户已停用, 请联系相关管理员", "In account forbidden, please contact the administrator"]];
	const UNREGISTERED = [false, ["该账户不存在, 请先注册", "In account unregistered"]];
    const EXAMINE_STATUS = [false, ["账号审核中，请联系相关管理员", "In account audit, please contact the administrator"]];
	const LOGIN_ERROR = [false, ["您输入的手机号或密码错误", "tel or pwd error"]];
	const LOGIN_TEL_ERROR = [false, ["您输入的手机号不存在", "tel  error"]];
    const VERIFY_CODE_ERROR = [false, ["验证码错误", "Verification code error"]];
	//操作
	const DATA_NOT_FIND = [false, ["未找到相应数据", "data not find"]];
    const ADD_ERROR = [false, ["添加错误", "insert error"]];
    const ADD_RECORD_ERROR = [false, ["记录添加错误", "insert record error"]];
	const UPDATE_ERROR = [false, ["修改错误", "update error"]];
	const DELETE_ERROR = [false, ["删除错误", "delete error"]];
	const EXAMINE_ERROR = [false, ["审核失败", "examine error"]];
	const DATA_HAS_DELETED = [false, ["该数据已删除", "data has deleted"]]; 
    const DATA_HAS_EXIST = [false, ["该数据已存在", "data has exist"]];	
    const TYPE_ERROR = [false, ["类型错误", "type error"]];	
    const FORMAT_ERROR = [false, ["请求数据格式错误", "format error"]];	
	//图片
    const IMAGE_COUNT_ERROR = [false, ["图片不能超过六张"]];
    const IMAGE_NOT_FIND = [false, ["没有图片", "image not find"]];
    const IMAGE_FILE_SIZE_ERROR = [false, ["大小不能超过5M"]];
    const IMAGES_INSERT_ERROR = [false, ["图片添加失败"]];
	const NO_IMAGES_DELETED = [false, ["删除的图片没有找到", "no deleted img"]];
    //文档
    const DOC_COUNT_ERROR = [false, ["只能上传单个文件"]];
    const DOC_NOT_FIND = [false, ["没有文件", "image not find"]];
    const DOC_FILE_SIZE_ERROR = [false, ["文件大小不能超过10M"]];
    const DOC_INSERT_ERROR = [false, ["文件添加失败"]];
	//权限
    const LIMITED_AUTHORITY = [false, ["你不是管理,也不是本人", "u are not a manager or not an adder"]];
	const AUTH_PREMISSION_EMPTY = [false, ["没有权限", "premission empty"]];
    const AUTH_PREMISSION_REJECTED = [false, ["您没有权限管理该区域，请选择您所在区域范围", "premission rejected"]];
	const AUTH_PREMISSION_LEVEL = [false, ["管理员等级必须大于县级", "premission rejected"]];
	const AUT_LOGIN = [false, ["身份验证删除失败", "auth delete failed"]];
	const AUTH_FAILED = [false, ["身份认证失败,请重新登录", "auth not find in db"]];
    const AUTH_EXPIRED = [false, ["身份认证过期，请重新登录", "auth expired"]];
	//其他
    const ADD_NUMBER_ERROR = [false, ["生成固定标准地编号错误", "add number error"]];
    const DEADLINE_ERROR = [false, ["截止时间要大于目前时间", "deadline must > create_time"]]; 
    //物种数据库
    const HAS_EXIT_PEST = [false, ["该生物信息已录入", "has exit pest"]]; 
    const HAS_EXIT_PLANT = [false, ["该树种已关联", "has exit plant"]]; 
    const HAS_LOCALED = [false, ["已有本地化信息", "has localed"]]; 
    const LOCAL_ERROR = [false, ["本地化失败", "local error"]];
    const NO_FIND_PLANTS = [false, ["寄主树种未找到","no find plants"]];
    const DELETE_PLANTS_ERROR = [false, ["寄主树种删除错误","delete plants error"]];
    //诱捕器管理
    const LABEL_ERROR = [false, ["生成标签错误", "label error"]];  
    const LABEL_HAS_EXIT = [false, ["该诱捕器已生成标签", "label has exit"]];  
    const LABEL_NO_EXIT = [false, ["请确定所选数据是否已生成标签", "label no exit"]];  
    const SELECT_LABEL_DATA = [false, ["请勾选需要导出的数据", "select label data"]];
    //松材线虫病--外业调查
    const DELETE_RECORD_ERROR = [false, ["删除样本记录错误", "delete record error"]];
    const UPDATE_RECORD_ERROR = [false, ["修改样本记录错误", "delete record error"]];
    //固定标准地调查
    const TENSE_RECORD_ADD_ERROR = [false, ["虫态记录插入错误", "tense record add error"]];
    const TENSE_RECORD_UPDATE_ERROR = [false, ["修改样本记录错误", "delete record update error"]];


    static function Error($toC, $toU = '程序出错', $isOk = false)
    {
        return [$isOk, [$toC]];
    }

    static function validateError($toC)
    {
        return [false, [$toC, $toC]];
    }
}

?>