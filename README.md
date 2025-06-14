# 说明

- 帮助ThinkPHP3.2项目实施phpunit单元测试。
- 本项目复刻于 [snowair/think-phpunit](https://github.com/snowair/think-phpunit), 做了部分修改。
- 本项目提交到GitHub之后，会自动同步更新到packagist.org。

# 安装和配置

在项目根目录下, 打开`composer.json`，添加以下内容:

``` json
{
  "autoload": {
    "classmap": ["Application","ThinkPHP/Library"]
  },
  "require-dev": {
    "wanren/tp32-phpunit-tool": "dev-master"
  }
}
```

首次安装:

```
$ composer install
```

更新:

```
$ composer update
```

当你向项目中增加了新的class以后，需要重建autoload:

```
$ composer dump-autoload
```

# 使用

安装好以后, 你就可以为项目中任何一个类创建单元测试类了:

### 创建单元测试类

think-phpunit的单元测试类遵循以下规则:

1. 所有的测试类都要在 `setUpBeforeClass` 静态方法中创建出模拟app实例.

2. 如果测试的是控制器类，那么测试类要 `use \Think\Phpunit;`. 测试其他类不需要这条

测试文件组织比较自由，只要能保证phpunit能在执行时载入它即可. 我把测试文件放在了项目根目录下的 test 文件夹

对于TP项目而言, 最困难的地方在于控制器类的测试, think-phpunit 将一切简化到一行:

使用控制器测试类的 `execAction($action_name)` 执行控制器action, 它会返回action执行产生的所有输出供你做断言.

下面是一个简单的测试控制器方法输出的示例测试文件,其他类型的类就不做介绍了:

```
<?php
namespace Home\Controller;

class IndexControllerTest extends \PHPUnit_Framework_TestCase
{
    use \Think\PhpUnit; // 只有控制器测试类才需要它

    static public function setUpBeforeClass()
    {
        // 下面四行代码模拟出一个应用实例, 每一行都很关键, 需正确设置参数
        self::$app = new \Think\PhpunitHelper();
        self::$app->setMVC('domain.com','Home','Index');
        self::$app->setTestConfig(['DB_NAME'=>'test', 'DB_HOST'=>'127.0.0.1',]); // 一定要设置一个测试用的数据库,避免测试过程破坏生产数据
        self::$app->start();
    }

    /**
     * 控制器action输出测试示例
     */
    public function testIndex()
    {
        $output = $this->execAction('index');
        $this->assertEquals('hello world',$output);
    }
}
```

```
<?php
namespace Home\Model;

class UserModelTest extends \PHPUnit_Framework_TestCase
{

    static public function setUpBeforeClass()
    {
        // 下面四行代码模拟出一个应用实例, 每一行都很关键, 需正确设置参数
        self::$app = new \Think\PhpunitHelper();
        self::$app->setMVC('domain.com','Home','Index');
        self::$app->setTestConfig(['DB_NAME'=>'test', 'DB_HOST'=>'127.0.0.1',]); // 一定要设置一个测试用的数据库,避免测试过程破坏生产数据
        self::$app->start();
    }

    /**
     * 模型类方法测试示例
     */
    public function testGetUsername()
    {
        $Model = new UserModel();
        $user_name  = $Model->getUsername(1);
        $this->assertEquals('admin',$user_name);
    }
}
```

# 写出对测试友好的方法

要写出对测试友好的类, 只需要遵循两个简单的原则:

1. 不用使用 exit 语句, 因为他会终结一切, 包括测试.
2. 不要直接使用 header 函数, 因为phpunit输出在先.
3. 尽量不要使用常量, 因为常量的值无法改变, 这意味着如果两个测试方法如果需要不同的常量值, 你将无法办到.
    * TP中以下常量往往会限制单元测试: `IS_POST`,`IS_AJAX`,`ACTION_NAME`,`__ACTION__` 等.

## 关于 header 函数

由于 phpunit 在启动后就已经产生了自己的输出, 所以被测的方法中不能直接使用 `header()` 函数, 否则会抛出错误.

所以代码中使用了header函数的地方需要调整,有以下两种方式调整:

```
headers_sent() or header(''); // 推荐: 发送前判断是否已经产生过输出
@header(''); // 强制屏蔽错误
```

## 关于exit

由于exit 会中断一切执行, 所以 phpunit 无法测试使用了exit语句的方法 .

如果你的方法中直接或间接用到了exit, 最佳的办法就是重构去除exit, 让程序能正常终结, 而不要强制终结.

# 执行测试

假设你创建了一个测试类 `./test/IndexControllerTest.php`,

## 在命令行执行测试

只需要**cd**进入你的项目目录, 然后执行

```
vendor/bin/phpunit  path-to-your-test-class-file 
```

例如:

```
~ $ cd project
~/project $ vendor/bin/phpunit ./test/IndexControllerTest.php 
```

## PHPStorm中执行测试

新版的PHPStorm是一个强大的IDE, 可以很方便地自动发现单元测试类和方法。

