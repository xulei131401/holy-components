<?php

namespace component;

/**
 * 业务的配置类
 * Class Config
 * @package component
 */
class Enumerate
{
    const DELETED_UNDO = 1;       //正常
    const DELETED_DO = 2;         //删除

    const DELETED    = 'deleted';           //删除状态字段

    const PRIMARY_KEY = 'id';               //全局数据库主键字段
    const CREATED_AT = 'created_at';        //创建时间字段
    const UPDATED_AT = 'updated_at';        //更新时间字段
    const DELETED_AT = 'deleted_at';        //删除时间字段

    const CREATED_BY = 'created_by';        //创建者字段
    const UPDATED_BY = 'updated_by';        //更新者字段
    const DELETED_BY = 'deleted_by';        //删除者字段


    const RESTFUL_DATA_KEY = 'data';        //接口被包裹的数据字段名，以后可以统一修改，前端也要定义好
    const RESTFUL_WRAP_KEY = 'result';      //接口被包裹的数据字段名，以后可以统一修改，前端也要定义好
    const RESTFUL_EXTEND_KEY = 'extend';    //接口统计数据扩展字段名，以后可以统一修改，前端也要定义好
    const RESTFUL_SUMMARY_KEY = 'summary';  //接口统计数据扩展字段名，以后可以统一修改，前端也要定义好

    const DB_SORT_DESC = 'desc';               //0是降序
    const DB_SORT_ASC = 'asc';                //1是升序


    const SORT_NAME = 'sort';               //排序字段
    const ASC_NAME = 'asc';                 //排序数字

    const PAGE_NUM_NAME = 'pn';
    const PAGE_NAME = 'p';
    const PAGE_TOTAL_NAME = 'total';
    const DEFAULT_PAGE_NUM = 20;            //默认查询条数为20
    const MAX_PAGE_NUM = 20000;             //最大查询结果为20000
    const DEFAULT_PAGE = 1;                 //默认是第几页

    //自定义LOG
    const DEFAULT_LOG = 'LOG';                              //自定义log配置
    const DEFAULT_LOG_DEFAULT = 'default';                  //默认配置
    const DEFAULT_LOG_SAVE_PATH = 'save_path';              //自定义log保存路径

    //话费多相关
    const HUAFEIDUO_KEY = 'HUAFEIDUO';
    const HUAFEIDUO_API_KEY = 'API_KEY';
    const HUAFEIDUO_SECRET_KEY = 'SECRET_KEY';
    const HUAFEIDUO_GATE_WAY = 'GATE_WAY';
    const HUAFEIDUO_NOTIFY_URL = 'NOTIFY_URL';

    //腾讯短信SDK配置
    const SMS = 'SMS';
    const SMS_AGENT = 'AGENT';
    const SMS_TECENT = 'TECENT';
    const SMS_TECENT_APP_ID = 'APP_ID';
    const SMS_TECENT_APP_KEY = 'APP_KEY';

}