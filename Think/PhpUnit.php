<?php
/**
 * User: Administrator
 * Date: 2015/9/10
 * Time: 11:10
 */
namespace WanRen\Think;

use WanRen\Think\Response;

trait PhpUnit
{

    /** @var  TPMocker */
    protected static $app;
    /** @var  Controller */
    protected static $controller;

    public static function setController($controller )
    {
        self::$controller=$controller;
    }

    /**
     * @param string $name   控制器方法名
     * @param array  $params 控制器参数列表
     *
     * @return mixed
     * @throws \Exception
     */
    public static function execAction($name,$params=array())
    {
        $controller = self::getController();
        if (method_exists($controller,$name)) {
            self::$app->setActionName($name);
            try{
                ob_start();
                call_user_func_array(array($controller,$name),$params);
                return ob_get_clean();
            }catch (Response $e){
                ob_end_clean();
                return $e->getMessage();
            }
        }else{
            throw new \Exception($name.'方法不存在');
        }

    }

    public static function getController()
    {
        $controller ="\\".MODULE_NAME."\\Controller\\".CONTROLLER_NAME.C("DEFAULT_C_LAYER");
        if (class_exists( $controller )) {
            return new $controller;
        }
    }

}